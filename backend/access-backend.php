<?php
  date_default_timezone_set("America/New_York");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/user.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/bitwise.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/backend/session_handler.php");
  new SessionHandler();
	session_start();
  util_session_check();
    
  $json = array();
	if(isset($_SESSION["LAST_ACTIVITY"]) && isset($_GET["act"])){
		$user_tbl = new UserTable();	//aww yyea first use of the api
		if(strcmp($_GET["act"], "update") == 0){
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				//calculate new permission value
				$newperms = 0;

				/*NOTE: a pub/resval of 30 means all access */

				//public access values
				$newaccess = 0;
				if(isset($_POST["pubval"]))
					switch($_POST["pubval"]){
						case 10:	//profile only
							$newaccess |= $bm_profileaccess;
						break;
						case 20:	//profile + comments
						case 30:	//all access
							$newaccess |= ($bm_profileaccess + $bm_commentaccess);
						break;
					}
				if(isset($_POST["publicdownload"]) || $_POST["pubval"] == 30)
					$newaccess |= $bm_downloadable;
				if(isset($_POST["publicstack"]) || $_POST["pubval"] == 30)
					$newaccess |= $bm_stackable;
				if(!isset($_POST["publichidden"]) || $_POST["pubval"] == 30)
					$newaccess |= $bm_showtags;
				if(!isset($_POST["publiclocked"]) || $_POST["pubval"] == 30)
					$newaccess |= $bm_writable;
			
				//map public access values to permissions
				$newperms |= $newaccess;	

				//resme access values
				$newaccess = 0;
				if(isset($_POST["resval"]))
					switch($_POST["resval"]){
						case 10:	//profile only
							$newaccess |= $bm_profileaccess;
						break;
						case 20:	//profile + comments
						case 30:	//all access
							$newaccess |= ($bm_profileaccess + $bm_commentaccess);
						break;
					}
				if(isset($_POST["resmedownload"]) || $_POST["resval"] == 30)
					$newaccess |= $bm_downloadable;
				if(isset($_POST["resmestack"]) || $_POST["resval"] == 30)
					$newaccess |= $bm_stackable;
				if(!isset($_POST["resmehidden"]) || $_POST["resval"] == 30)
					$newaccess |= $bm_showtags;
				if(!isset($_POST["resmelocked"]) || $_POST["resval"] == 30)
					$newaccess |= $bm_writable;
				
				//shift resme access values and map to permissions
				$newaccess = ($newaccess << $shift_resme);
				$newperms |= $newaccess;

				//now set flags
				if(isset($_POST["publicSearch"]) && strcmp($_POST["publicSearch"], "yes") == 0)
					$newperms |= $bm_isindexed;
				if(isset($_POST["resmeSearch"]) && strcmp($_POST["resmeSearch"], "yes") == 0)
					$newperms |= $bm_issearchable;
			
				$user_tbl->updateUserPermissions($_SESSION["username"], $newperms);

        $json["status"] = "Privacy Settings Updated! Return to <a href='/".$_SESSION["username"]."'>Profile</a>.";
        $json["code"] = 1;
			}
      else{
        $json["status"] = "Submission Error.";
        $json["code"] = 0;
      }
		}
		else if(strcmp($_GET["act"], "show") == 0){
			$permissions  = $user_tbl->getUserPermissions($_SESSION["username"]);
      if($permissions){
        //parse out the bit values!!
				$access = array();

				$json["indexed"] = $permissions & $bm_isindexed;
				$json["searchable"] = $permissions & $bm_issearchable;
			
				//public user access
				if(($permissions & $bm_allaccess) == $bm_allaccess)
					$access["page_access"] = 30;
				else if(($permissions & $bm_pageaccess) == $bm_pageaccess)
					$access["page_access"] = 20;
				else if(($permissions & $bm_profileaccess) == $bm_profileaccess)
					$access["page_access"] = 10;
				else
					$access["page_access"] = 0;
				$access["showtags"] = $permissions & $bm_showtags;
				$access["writable"] = $permissions & $bm_writable;
				$access["downloadable"] = $permissions & $bm_downloadable;
				$access["stackable"] = $permissions & $bm_stackable;
				$json["public"] = $access;
	
				//shift bits and calculate resme user access
				$permissions = ($permissions >> $shift_resme);
				
				if(($permissions & $bm_allaccess) == $bm_allaccess)
					$access["page_access"] = 30;
				else if(($permissions & $bm_pageaccess) == $bm_pageaccess)
					$access["page_access"] = 20;
				else if(($permissions & $bm_profileaccess) == $bm_profileaccess)
					$access["page_access"] = 10;
				else
					$access["page_access"] = 0;
				$access["showtags"] = $permissions & $bm_showtags;
				$access["writable"] = $permissions & $bm_writable;
				$access["downloadable"] = $permissions & $bm_downloadable;
				$access["stackable"] = $permissions & $bm_stackable;
				$json["resme"] = $access;



        $json["status"] = "Your Privacy Settings as of ".date('l, F j');
        $json["code"] = 1;
      }
    }
    else{
      $json["status"] = "Permission Denied.";
      $json["code"] = 0;
    }
    //clean up table objects
    unset($user_tbl);
	}
	else{
    $json["status"] = "Access Denied.";
    $json["code"] = 0;
	}
  echo json_encode($json);
?>
