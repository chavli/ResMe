<?php
	date_default_timezone_set('America/New_York');
  require_once($_SERVER["DOCUMENT_ROOT"]."/api/user.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/backend/session_handler.php");
  new SessionHandler();
	session_start();
  util_session_check();
  
  $json = array();
	if(isset($_SESSION["LAST_ACTIVITY"]) && isset($_GET['act'])){
		$uname = $_SESSION["username"];
    //update info with new info(and check if there is new info)
    if(strcmp($_GET['act'], "update") == 0){

      if($_SERVER['REQUEST_METHOD'] == 'POST'){ //make sure something was posted
        //calculate the new number representing selected professions
        $prof_val = 0;
        if(isset($_POST['profs'])){
          foreach($_POST['profs'] as $val)
            $prof_val += pow(2,$val);
        }
        //sanitize user input, update session info, and store in DB
        //NOTE: null 2nd arg means the strings will only be escaped, sql i
				//sanitation is taken care of by the api layer
        $_SESSION["email"] = util_sanit_str($_POST["email"], null);
        $_SESSION["firstname"] =  util_sanit_str($_POST["first"], null);
        $_SESSION["lastname"] = util_sanit_str($_POST["last"], null);
        $_SESSION["mainphone"] = util_sanit_str($_POST["mphone"], null);
        $_SESSION["cellphone"] = util_sanit_str($_POST["cphone"], null);
        $_SESSION["officephone"] = util_sanit_str($_POST["ophone"], null);
        $_SESSION["profs"] = $prof_val;
        
        //format names correctly
        $_SESSION['firstname'] = ucwords(strtolower($_SESSION['firstname']));
        $_SESSION['lastname'] = ucwords(strtolower($_SESSION['lastname']));
        //sql querries, ASSEMBLE! update database with new data
        $fields = array(
          "email", 
          "firstname", 
          "lastname",
          "mainphone",
          "cellphone",
          "officephone",
          "profs");
        $values = array(
          $_SESSION["email"],
          $_SESSION["firstname"],
          $_SESSION["lastname"],
          $_SESSION["mainphone"],
          $_SESSION["cellphone"],
          $_SESSION["officephone"],
          $prof_val);
        
        //update user info
        $user_tbl = new UserTable();
        $user_tbl->updateUser($uname, $fields, $values);
        unset($user_tbl);
        
        $json["status"] = "Profile Updated! Return to <a href='/".$_SESSION["username"]."'>Profile</a>.";
        $json["code"] = 1;          
      }
      else{
        $json["status"] = "Submission Error.";
        $json["code"] = 0;
      }
    }
	}
	else{
    $json["status"] = "Access Denied.";
    $json["code"] = 0;
	}  
  
	echo json_encode($json);
?>
