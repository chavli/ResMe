<?php
	date_default_timezone_set('America/New_York');
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/user.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/comment.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/notification.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/resumepage.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/bitwise.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/backend/session_handler.php");
  new SessionHandler();
	session_start();
  util_session_check();
  
	$json = array();
	if(isset($_GET["uname"])){
		$user_tbl = new UserTable();
		$comment_tbl = new CommentTable();
		$noti_tbl = new NotificationTable();
		$page_tbl = new ResumePageTable();

  	$uid = $_GET["uname"];

    $userdata = $user_tbl->getUserCurrentResume($uid); 
    //check if the username, of the person receiving the comment, is valid
    if(!$userdata){
      $json["status"] = "Invalid User Name.";
      $json["code"] = 0;
    }
    //post a new comment
    else if(isset($_GET["act"]) && $_GET["act"] == "post"){
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
      	$post_date = date("Y-m-d H:i:s");
      
      	//check if it"s a registered comment or non-registered comment 
      	if(isset($_SESSION['username']))
        	$source = $_SESSION['username'];
      	else
        	$source = 'friendly';
      
      	//create sql query and send comment 
      	$values = array(
      		$uid,
      		$source,
      		$_POST['comment'],
      		$post_date,
					$userdata["resume_album"]
      	);
     		$comment_tbl->newComment($values);

				if(strcmp($uid, $source) != 0){
					//create the comment notification
					$values = array(
						$_POST['comment'],
						$post_date,
						$UTIL_CMT_NOTE,
						$source,
						$uid,
						0,
						1
					);
					$noti_tbl->newNotification($values);
				}
        $json["status"] = "Comment Posted.";
        $json["code"] = 1;
        $json["comment"] = $_POST['comment'];
        $json["date"] = $post_date;
        $json["from"] = $source;
			}
    }
    else if(isset($_GET["act"]) && strcmp($_GET["act"], "show") == 0){
      $json["status"] = "Access Denied.";
      $json["code"] = 0;
      $json["firstname"] = $userdata["firstname"];
      $json["lastname"] = $userdata["lastname"];
      
			//fetch permissions
      $usr_perms = (isset($_SESSION["username"]) && strcmp($_SESSION["username"], $uid) == 0) ? ($userdata["perms"] + $bm_isowner) : $userdata["perms"];
      
      //return comment data if permissions allow for it
      if(isset($_SESSION["LAST_ACTIVITY"]) && (($usr_perms >> $shift_resme) & $bm_commentaccess) || ($usr_perms & $bm_commentaccess)){
        $json["readonly"] = 1;
        //grab resume images
        $pages = array();
				$page = $page_tbl->getResumePagesByColumn("album_id", $userdata["resume_album"]);
				foreach($page as $item){
          $path = "/users/".$userdata["username"]."/albums/".$item["album_id"]."/".$item["page"].".jpg";
          array_push($pages, array("id" => $item["id"], "src" => $path));
        }
        $json["pages"] = $pages;
        
        //grab comments
        $comments = array();
				$commentdata = $comment_tbl->getResumeComments($uid, $userdata["resume_album"]);
        foreach($commentdata as $comment){
          array_push($comments, array("message" => $comment['comment'], "source" => $comment['source'], "time" => $comment['time']));
        }
				
        $json["comments"] = $comments;
        $json["status"] = "Comments Retrieved.";
        $json["code"] = 1;        
      }
      //allow for comment posts
      if(isset($_SESSION["LAST_ACTIVITY"]) && (($usr_perms >> $shift_resme) & $bm_writable) || ($usr_perms & ($bm_writable + $bm_isowner))){
        $json["readonly"] = 0;
      }
    }
    
    //clean up table objects
    unset($user_tbl);    
    unset($comment_tbl);    
    unset($noti_tbl);    
    unset($page_tbl);
  }
  else{
    $json["status"] = "Access Denied.";
    $json["code"] = 0;        
  }

  echo json_encode($json);
?>
