<?php
  date_default_timezone_set("America/New_York");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/user.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/album.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/resume.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/resumepage.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/notification.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/bitwise.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/backend/session_handler.php");
  new SessionHandler();
	session_start();
  util_session_check();
		
  $json = array();
  if (isset($_SESSION["LAST_ACTIVITY"]) && isset($_GET["act"])) {
		if(strcmp($_GET["act"], "upload") == 0){
      if(isset($_FILES["vidFile"]["tmp_name"])){
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $vid_type = finfo_file($finfo, $_FILES["vidFile"]["tmp_name"]);
        finfo_close($finfo);

        //file validation
        if(!is_uploaded_file($_FILES["vidFile"]["tmp_name"])){
          $json["code"] = $UTIL_FFORM_ERR;
          $json["status"] = "Invalid File: ".$_FILES["vidFile"]["name"];
        }
        else if($_FILES["vidFile"]["size"] > $UTIL_VID_LIM){
          $json["code"] = $UTIL_FSIZE_ERR;
          $json["status"] = "Video exceeds size limit (50MB).";
        }
        else if(!in_array($vid_type, $util_allowed_vid_types)){
          $json["code"] = $UTIL_FTYPE_ERR;
          $json["status"] = "Invalid video format: ".$_FILES["vidFile"]["name"];
        }
       	else{ 
          $path_parts = pathinfo($_FILES["vidFile"]["name"]);
          $ext = ".".$path_parts["extension"];
					$path = util_tempnam_safe($_SERVER["DOCUMENT_ROOT"]."/users/".$_SESSION["username"]."/uploads/" ,$ext);
          //upload the file
          move_uploaded_file($_FILES["vidFile"]["tmp_name"], $path);
          $json["code"] = 1;
          $json["status"] = "Video Successfully Uploaded.";
          $json["path"] =  $path;
        }
      }
      else{
        $json["code"] = $UTIL_FMISS_ERR;
        $json["status"] = "Invalid File Selected.";
      }
    }
  }
  if(strcmp($_GET["act"], "show") == 0)
    $json = fetchProfile();
	else if(strcmp($_GET["act"], "addstack") == 0 && isset($_GET["id"])){
		$stack = unserialize($_SESSION["stack"]);
		$new_id = $_GET["id"];
		$stack[$new_id] = $new_id;
		$_SESSION["stack"] = serialize($stack);

		//create the notification for this event
		$noti_tbl = new NotificationTable();
		$post_date = date("Y-m-d H:i:s");
		$values = array(
			"",		//no metadata
			$post_date,	//date
			$UTIL_ADDSTACK_NOTE,	//notificaton type (added to stack)
			$_SESSION["username"],	//sender
			$_GET["owner"],	//receiver
			0, //delete on read
			1	//delete on expire
		);
		$noti_tbl->newNotification($values);

    $json = fetchProfile();
	}
	else if(strcmp($_GET["act"], "delstack") == 0 && isset($_GET["id"])){
		$stack = unserialize($_SESSION["stack"]);
		$new_id = $_GET["id"];
		unset($stack[$new_id]);
		$_SESSION["stack"] = serialize($stack);
    $json = fetchProfile();
	}
	else if(strcmp($_GET["act"], "judge") == 0 && isset($_GET["id"])){
		$resume_id = $_GET["id"];
		$resume_likes = unserialize($_SESSION["resume_likes"]);
		$delta = 0;

		//add or remove the resume id
		if(in_array($resume_id, $resume_likes)){
			unset($resume_likes[$resume_id]);
			$delta = -1;
			$json["approved"] = 0;
		}
		else{
			$resume_likes[$resume_id] = $resume_id;
			$delta = 1;
			$json["approved"] = 1;
		}
		
		//create the notification for this event
		$noti_tbl = new NotificationTable();
		$post_date = date("Y-m-d H:i:s");
		$values = array(
			"",		//no metadata
			$post_date,	//date
			$UTIL_APPRES_NOTE,	//notificaton type (approval)
			$_SESSION["username"],	//sender
			$_GET["owner"],	//receiver
			0, //delete on read
			1	//delete on expire
		);
		$noti_tbl->newNotification($values);

		unset($noti_tbl);

		//update the data in the resume table for this resume
		$resume_tbl = new ResumeTable();
		$result = $resume_tbl->judgeResume($resume_id, $delta);
		if($result >= 0){
    	$json["code"] = 1;
    	$json["status"] = "upvote success.";
		}
		else{
    	$json["code"] = 0;
    	$json["status"] = "upvote error";
		}
		unset($resume_tbl);

		$_SESSION["resume_likes"] = serialize($resume_likes);
	}
	else{
    $json["code"] = 0;
    $json["status"] = "Access Denied.";
  }
  
  echo json_encode($json);
  
  function fetchProfile(){
    require_once("../system/php/bitwise.php");
    $json = array();
    //fetch all the data to be displayed on the profile page
    if(isset($_GET["uname"])){
			$user_tbl = new UserTable();
			$album_tbl = new AlbumTable();
			$resume_tbl = new ResumeTable();
			$respage_tbl = new ResumePageTable();
       
			$uname = $_GET["uname"];
       
      //populate userdata
      if(isset($_SESSION["LAST_ACTIVITY"]) && strcmp($_SESSION["username"], $uname) == 0){
        $userdata = $_SESSION;
        $json["isOwner"] = 1;
      }
      else{
				$userdata = $user_tbl->getUsersByColumn("username", $uname);
        //check if username was valid
        if(sizeof($userdata) == 0){
          $json["code"] = 0;
          $json["status"] = "This user does not exist.";
          return $json;
        }
				$userdata = $userdata[0];
        $json["isOwner"] = 0;
      }
			
			//from utilities.php
			global $bm_isowner, $bm_profileaccess, $bm_commentaccess, $bm_downloadable, $bm_stackable, $shift_resme;

      //calculate access permissions
      $usr_perms = (isset($_SESSION["LAST_ACTIVITY"]) && strcmp($_SESSION["username"], $uname) == 0) ? ($userdata["perms"] + $bm_isowner) : $userdata["perms"];
       
      //name and profile picture are public
			$palbumdata = $album_tbl->getAlbum($userdata["profile_album"]);
      $json["name"] = $userdata["firstname"]." ".$userdata["lastname"];
      $json["picture"] = "/users/".$userdata["username"]."/albums/".$userdata["profile_album"]."/".$userdata["current_profile_picture"];
         
			if(isset($_SESSION["LAST_ACTIVITY"]) && (($usr_perms >> $shift_resme) & ($bm_profileaccess)) || ($usr_perms & ($bm_profileaccess + $bm_isowner))){
        //fetch image albums for profile picture and current resume
				$ralbumdata = $album_tbl->getAlbum($userdata["resume_album"]);
      	$resumedata = $resume_tbl->getResumeByColumn("album_id", $userdata["resume_album"]); 
				$resumedata = $resumedata[0];
	
        //build json data
        $json["owner"] = $userdata["username"];
        $json["id"] = $userdata["id"];
        $json["resumepage"] = $userdata["resumepage"];
				$json["resume_id"] = $resumedata["id"];
        $json["email"] = $userdata["email"];
        $json["mainphone"] = "";
        $json["cellphone"] = "";
        $json["officephone"] = "";

				//check optional data
				if(isset($userdata["mainphone"]))
          $json["mainphone"] = $userdata["mainphone"];
				if(isset($userdata["cellphone"]))
         	$json["cellphone"] = $userdata["cellphone"];
				if(isset($userdata["officephone"]))
         	$json["officephone"] = $userdata["officephone"];
         
        //grab resume images
        $pages = array();
				$page = $respage_tbl->getResumePagesByColumn("album_id", $userdata["resume_album"]);
				$pdfpath = "";
        foreach($page as $item){
          $pdfpath = $item["pdf_name"];
          $path = "/users/".$userdata["username"]."/albums/".$item["album_id"]."/".$item["page"].".jpg";
          array_push($pages, array("id" => $item["id"], "src" => $path));
        }
        
        //comment access?
        $json["comments"] = 0;
        if(isset($_SESSION["LAST_ACTIVITY"]) && (($usr_perms >> $shift_resme) & $bm_commentaccess) || ($usr_perms & ($bm_commentaccess + $bm_isowner)))
          $json["comments"] = 1;
         
				//download PDF access
				if(isset($_SESSION["LAST_ACTIVITY"]) && (($usr_perms >> $shift_resme) & $bm_downloadable) || ($usr_perms & ($bm_downloadable + $bm_isowner)))
					$json["pdfpath"] = $pdfpath;
				
				//check if stackable, and if user has approved it yet
				$json["stackable"] = 0;
				if(isset($_SESSION["LAST_ACTIVITY"])){
					$stack = unserialize($_SESSION["stack"]);
					$resume_likes = unserialize($_SESSION["resume_likes"]);

					if(!isset($stack[$userdata["id"]]) && (($usr_perms >> $shift_resme) & ($bm_stackable + $bm_isowner)))
						$json["stackable"] = 1;

					if(in_array($resumedata["id"], $resume_likes))
						$json["approved"] = 1;
					else
						$json["approved"] = 0;
				}

				$json["pages"] = $pages;
        $json["code"] = 1;
        $json["status"] = "Profile Fetched.";          
      }
      else{
        $json["code"] = 0;
        $json["status"] = "This profile has been set to private.";
      }
		
      //clean up tables
      unset($user_tbl);
			unset($album_tbl);
			unset($resume_tbl);
			unset($respage_tbl);
    }
    else{
      $json["code"] = 0;
      $json["status"] = "Access Denied.";
    }
		//print_r($json);
    return $json;
  }
?>
