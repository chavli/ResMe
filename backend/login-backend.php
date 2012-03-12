<?php
 	/*login-backend.php, this file handles authenticating users as the attempt to login*/
  date_default_timezone_set('America/New_York');
  require_once($_SERVER["DOCUMENT_ROOT"]."/api/user.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/api/usermeta.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/backend/session_handler.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php"); 
	new SessionHandler();
	session_start();

  //if user is logged in, send them to their profile
	$json = array();
  if(isset($_SESSION['LAST_ACTIVITY']) && !isset($_GET['act'])){
    header("Location: /".$_SESSION['username']);
  }
	else if(isset($_GET['act']) && strcmp($_GET['act'], "welcome") == 0 ){
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
      //don't do anything if any of these are empty
      if(strlen($_POST['uname']) > 0 && strlen($_POST['upass'])  > 0){	
       	$user_tbl = new UserTable();
				$meta_tbl = new UserMetaTable();
				
        //fetch user hash
				$hash = $user_tbl->getUserHash($_POST["uname"]);

        // Retrieve data from Query String
        if($hash){
            //see backend/create-account.php for info regarding crypt()
            if(crypt($_POST["upass"], $hash) == $hash){
							//fetch remaining user info
              $userdata = $user_tbl->getUsersByColumn("username", $_POST["uname"]);
							$userdata = $userdata[0];
							$metadata = $meta_tbl->getUserMetaByColumn("user_id", $userdata["id"]);
							$metadata = $metadata[0];

              //create session info
              $_SESSION['id'] = $userdata['id'];
              $_SESSION['username'] = $userdata['username'];
              $_SESSION['email'] = $userdata['email'];
              $_SESSION['firstname'] = $userdata['firstname'];
              $_SESSION['lastname'] = $userdata['lastname'];
              $_SESSION['profile_album'] = $userdata['profile_album'];
              $_SESSION['current_profile_picture'] = $userdata['current_profile_picture'];
              $_SESSION['resume_album'] = $userdata['resume_album'];
              $_SESSION['resumepage'] = $userdata['resumepage'];
              $_SESSION['mainphone'] = $userdata['mainphone'];
              $_SESSION['cellphone'] = $userdata['cellphone'];
              $_SESSION['officephone'] = $userdata['officephone'];
              $_SESSION['profs'] = $userdata['profs'];
              $_SESSION['perms'] = $userdata['perms'];
              $_SESSION['LAST_ACTIVITY'] = time();
						
							//metadata
							$_SESSION["stack"] = $metadata["stack_data"];
							$_SESSION["votes"] = $metadata["vote_data"];	//for articles
							$_SESSION["resume_likes"] = $metadata["resume_data"];	//id's of resumes the user likes

              $json["code"] = 1;
							$json["status"] = "Success";
            }
            else{
              $json["code"] = 0;
							$json["status"] = "Incorrect Username/Password";
            }
        }
        else{
	        $json["code"] = 0;
					$json["status"] = "Incorrect Username/Password";
        }
            
        //clean up table objects
        unset($user_tbl);
      }
			else{ 
       	$json["code"] = 0;
				$json["status"] = "Please fill in all fields.";
			}

		}
    else{
			$json["code"] = 0;
			$json["status"] = "Please try again.";
		}
    
		echo json_encode($json);
	}
  else if( isset($_GET['act']) && strcmp($_GET['act'], "goodbye") == 0 ){
    //double check session data
    if(isset($_SESSION["username"])){
      //destroy all data and logout!!!!
      session_destroy();
      session_unset();
    }
    header('Location: /');
  }
?>
