<?php
  date_default_timezone_set('America/New_York');
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/user.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/backend/session_handler.php");
  new SessionHandler();
	session_start();
  util_session_check();

	if($_SERVER['REQUEST_METHOD'] == 'GET'){
		$pmask = 0;	//profession mask
		$tmask = 0; //resume type mask
		
		if(isset($_GET['profs']) && !in_array(-1, $_GET['profs'])){
			foreach($_GET['profs'] as $prof)
				$pmask += pow(2, $prof);
		}
		else
			$pmask = PHP_INT_MAX;
			
		if(isset($_GET['restypes']) && !in_array(-1, $_GET['restypes'])){
			foreach($_GET['restypes'] as $type)
				$tmask += pow(2, $type);
		}
		else
			$tmask = PHP_INT_MAX;

  	$user_tbl = new UserTable(); 
   	$userdata = $user_tbl->getUsersByProfAndType($pmask, $tmask); 
    unset($user_tbl);

    //only return the info (represented as JSON) search.php needs
    $users_array = array(); $i = 0;
    foreach($userdata as $item){
      $user_array = array(
        "username" => $item["username"], 
        "firstname" => $item["firstname"],
        "lastname" => $item["lastname"],
        "profile_picture" => "/users/".$item["username"]."/albums/".$item["profile_album"]."/".$item["current_profile_picture"],
        "thumbnail" => "/users/".$item["username"]."/albums/".$item["resume_album"]."/0.jpg"
      );
      $users_array[$i++] = $user_array;
    }
		
    echo json_encode($users_array);
  }
?>
