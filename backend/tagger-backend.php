<?php
  date_default_timezone_set('America/New_York');
  require_once($_SERVER["DOCUMENT_ROOT"]."/api/tag.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/api/user.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/bitwise.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/backend/session_handler.php");
  new SessionHandler();
	session_start();
  util_session_check();
	
	//grab user permissions to see if tags are allowed to be accessed
	$user_tbl = new UserTable();
	$perms = $user_tbl->getUserPermissions($_GET["username"]);

	if(!isset($_SESSION["LAST_ACTIVITY"]))	//public users
		$access = ($perms & $bm_profileaccess) && ($perms & $bm_showtags);
	else
		$access = (($perms >> $shift_resme) & $bm_profileaccess) && (($perms >> $shift_resme) & $bm_showtags) || strcmp($_GET["username"], $_SESSION["username"]) == 0;
		
	unset($user_tbl);

  $json = array();
	$json["code"] = 0;

	if($access && isset($_GET["act"])){
		unset($_GET["username"]);
    $json["code"] = 1;
    $tag_tbl = new TagTable();
    //save a tag
    if(strcmp($_GET["act"], "save") == 0){
      unset($_GET["act"]);
      $json["lastID"] = $tag_tbl->newTag($_GET);
      $json["status"] = "Tag Saved.";
    }
    
    //load tags
    else if(strcmp($_GET["act"], "load") == 0){
      unset($_GET["act"]);
      $tags = array();
			$tagdata = $tag_tbl->getTagsByColumn("resumepage" , $_GET['resumepage']);
      foreach($tagdata as $tag){
        array_push($tags, array("id" => $tag['id'],
                                "x" => $tag['x'],
                                "y" => $tag['y'],
                                "width" => $tag['width'],
                                "height" => $tag['height'],
                                "data" => $tag['data'],
                                "type" => $tag['type']));
      }
      $json["tags"] = $tags;
      $json["status"] = "Tags Loaded.";
    }
    
    //delete tag
    else if(strcmp($_GET["act"], "delete") == 0){
      unset($_GET["act"]);
      $tag_tbl->deleteTag($_GET["id"]);
      //if it's a phototag, the album and pictures have to be deleted from the filesystem
      if($_GET['type'] == $UTIL_PIC_TAG)
        util_rrmdir($_SERVER["DOCUMENT_ROOT"]."/users/".$_SESSION["username"]."/albums/".$_GET["albumid"]); 
      else if($_GET['type'] == $UTIL_UVID_TAG)
        unlink($_SERVER["DOCUMENT_ROOT"].substr($_GET["data"], 0)); //ignore leading slash
      $json["status"] = "Tag Deleted.";
    }
    
    else{
      $json["status"] = "The server experienced a problem.";
      $json["code"] = 0;
    }
    unset($tag_tbl);

  }
	else
		$json["status"] = "Access Denied";

  echo json_encode($json);
?>
