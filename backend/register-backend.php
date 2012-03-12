<?php
	date_default_timezone_set('America/New_York');
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/user.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/accesskey.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/backend/session_handler.php");
  new SessionHandler();
	session_start();
 
  $json = array();
  //server side string and account validation, werd 
  if(isset($_GET["act"]) && strcmp($_GET["act"], "validate") == 0){
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
   		
			//sanitize inputs
 			$_POST["n_first"] = util_sanit_str($_POST["n_first"], null);
      $_POST["n_last"] = util_sanit_str($_POST["n_last"], null);
      $_POST["n_mail"] = util_sanit_str($_POST["n_mail"], null);
      $_POST["n_name"] = util_sanit_str($_POST["n_name"], null);
      $_POST["n_key"] = util_sanit_str($_POST["n_key"], null);

			$json["status"] = validate_info($_POST);


      //if error, print it. otherwise, create new account!
      if(strlen($json["status"]) != 0){
        $json["code"] = 0;
      }
      else{
        //everything is ok, create session data and create the new account
        $_SESSION["firstname"] = $_POST["n_first"]; 
        $_SESSION["lastname"] = $_POST["n_last"]; 
        $_SESSION["username"] = $_POST["n_name"];
        $_SESSION["email"] = $_POST["n_mail"]; 
        $_SESSION["password"] = $_POST["n_pass"]; 
        if(isset($_POST["profs"]))
          $_SESSION["profs"] = $_POST["profs"]; 
        
        $json["status"] = "Success";
        $json["code"] = 1;
      }
    }
    else{
      $json["status"] = "Please fill in all fields.";
      $json["code"] = 0;
    }
  }
  else{
    $json["status"] = "Access Denied.";
    $json["code"] = 0;
  }

	//print the json
  echo json_encode($json);

  //maybe move this function to utilities?
  function validate_info($userdata){
		$user_tbl = new UserTable();
		$key_tbl = new KeyTable();

    //make sure fields are filled out
    if(
      strlen($userdata["n_first"]) == 0 ||
      strlen($userdata["n_last"]) == 0 ||
      strlen($userdata["n_name"]) == 0 ||
      strlen($userdata["n_pass"]) == 0 ||
      strlen($userdata["n_cpass"]) == 0
    )
      return "Please fill in all fields.";

		//check if passwords match
    if(strcmp($userdata["n_pass"], $userdata["n_cpass"]) != 0){
      return "Password fields don't match.";
		}
    
    //check if email is valid
    if(!preg_match("/[a-z0-9\.]+@[a-z0-9]+\.[a-z]+$/i", $userdata["n_mail"]))
      return "Invalid E-Mail.";
      
    //make sure first and last name only contain alpha-chars
    if(preg_match("/[^a-zA-Z]/", $userdata["n_first"]) || preg_match("/[^a-zA-Z]/", $userdata["n_last"]))
      return "First and Last name can only contain letters.";
    
    //check if username only contains alpha-numeric characters
    if(preg_match("/[^a-zA-Z0-9\.]/", $userdata["n_name"]))
      return "Username can only contain letters, numbers, and '.'";
    
    //check if username is already taken
		$check = $user_tbl->getUsersByColumn("username", $userdata["n_name"]); 
    if(sizeof($check) > 0)
      return "Username is taken";
    
    //check password for illegal symbols
    if(preg_match("/[^a-zA-Z0-9\!\#\&\_\?]/", $userdata["n_pass"]))
      return "Passwords can only contain letters, numbers, and ! # & _ ?";

		//check if the key is valid
		$keydata = $key_tbl->isKey($userdata["n_key"]);
		if(!isset($keydata["passkey"])){
			return "Invalid Alpha Key.";
		}
		//remove the key
		else{
			$key_tbl->deleteKey($userdata["n_key"]);
		}
    //clean up table objects
    unset($user_tbl);
    unset($key_tbl);
    
    return "";
  }
?>
