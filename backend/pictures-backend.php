<?php
  date_default_timezone_set("America/New_York");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/user.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/album.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/resumepage.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/backend/session_handler.php");
  new SessionHandler();
	session_start();
  util_session_check();
  
  $json = array();
	if(isset($_SESSION["LAST_ACTIVITY"]) && isset($_GET["act"])){
		$uname = $_SESSION["username"];
    
		if(strcmp($_GET["act"], "update") == 0){
      $json["code"] = 0;
      if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $user_tbl = new UserTable();          
        $album_tbl = new AlbumTable();
        
				$newcurrent = $_POST["currentpicture"]; 	//resume to set as current
				$todelete = isset($_POST["deletepicture"]) ? $_POST["deletepicture"] : array();  //pictures to delete

        //delete profile pictures
				for($i = 0; $i < sizeof($todelete); $i += 1){
					if($todelete[$i] != $newcurrent){	//dont delete the album that's set as the new current!
            if($todelete[$i]) { unlink($_SERVER["DOCUMENT_ROOT"]."/users/".$_SESSION["username"]."/albums/".$_SESSION["profile_album"]."/".$todelete[$i]); }
          }
				}
        
        //update user's current picture
        $user_tbl->updateUser($_SESSION["username"], array("current_profile_picture"), array($newcurrent));
        $_SESSION["current_profile_picture"] = $newcurrent;

        $json["owner"] = $_SESSION["username"];
        $json["album_id"] = $_SESSION["profile_album"];
        $json["current_picture"] = $_SESSION["current_profile_picture"];
        $json["pictures"] = scandir($_SERVER["DOCUMENT_ROOT"]."/users/".$_SESSION["username"]."/albums/".$_SESSION["profile_album"]);     
        $json["status"] = "Your profile picture has been updated! Return to your <a href='/".$_SESSION["username"]."'>Profile</a>.";
        $json["code"] = 1;
        
        unset($user_tbl);
        unset($album_tbl);
      }
      else{
        $json["status"] = "No changes were made.";
      }
		}
    
    
    else if(strcmp($_GET["act"], "show") == 0){
      $user_tbl = new UserTable();
      $album_tbl = new AlbumTable();

      //grab the id of the user's profile picture album and the length
      $json["owner"] = $_SESSION["username"];
      $json["album_id"] = $_SESSION["profile_album"];
      $json["current_picture"] = $_SESSION["current_profile_picture"];
      $json["pictures"] = scandir($_SERVER["DOCUMENT_ROOT"]."/users/".$_SESSION["username"]."/albums/".$_SESSION["profile_album"]);
      
      $json["status"] = "These are your profile pictures as of ".date('l, F j');
      $json["code"] = 1;
      
      unset($user_tbl);
      unset($album_tbl);
    }
    //uploaded a new resume
    else if(strcmp($_GET["act"], "upload") == 0){
      $json["code"] = 0;
      
      $album_tbl = new AlbumTable();
      $user_tbl = new UserTable();
      
      if(isset($_FILES["picFile"])){
        $pro_name = $_FILES["picFile"]["tmp_name"];
        $pro_size = $_FILES["picFile"]["size"];
        
        //determine file type based on contents of file. (the mime-type header can be faked)
        //http://www.php.net/manual/en/intro.fileinfo.php
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if($pro_name) {$pro_type = finfo_file($finfo, $pro_name);}
        finfo_close($finfo);

        //current image albums	
        $palbum_id = $_SESSION["profile_album"]; 
        
        //check if file meets requirements
        if ($pro_size > $UTIL_IMG_LIM){ $json["status"] = "Profile pictures are limited to 1MB.";}
        else if( !is_uploaded_file($pro_name) || !in_array($pro_type, $util_allowed_img_types)){
          $json["status"] = "The image type of your profile picture is not supported.";
        }
        else{
          if( getimagesize($pro_name) ){
            //get profile image album info
            $palbumdata = $album_tbl->getAlbum($palbum_id);
            ++$palbumdata["length"];
            
            $ppath = util_tempnam_safe($_SERVER["DOCUMENT_ROOT"]."/users/".$_SESSION["username"]."/albums/".$palbum_id."/", ".jpg");
            $pro_image = explode("/", $ppath);
            $pro_image = $pro_image[sizeof($pro_image) - 1];  //get the filename
            
            //convert the image type to jpg. the [0] is to limit image conversion to the first frame. (avoid animated gifs)
            //-background white -flatten: any transparent aspect of the raw image becomes white in the converted image
            system("convert ".$pro_name."[0] -background white -flatten ".$ppath);
  
            //update database with album info
            $album_tbl->updateAlbum($palbum_id, array("length"), array($palbumdata["length"]));
            $user_tbl->updateUser($_SESSION["username"],  array("current_profile_picture"), array($pro_image));
            $_SESSION["current_profile_picture"] = $pro_image;
            
            //returned json
            $json["owner"] = $_SESSION["username"];
            $json["album_id"] = $palbum_id;
            $json["album_length"] = $palbumdata["length"];
            $json["current_picture"] = $_SESSION["current_profile_picture"];
            $json["pictures"] = scandir($_SERVER["DOCUMENT_ROOT"]."/users/".$_SESSION["username"]."/albums/".$_SESSION["profile_album"]);            
            $json["status"] = "Image Uploaded Successfully";
            $json["code"] = 1;                      
          }
          else
            $json["status"] = "Invalid Image File.";
        }
      }
      unset($album_tbl);
      unset($user_tbl);
    }
    else{
      $json["status"] = "Request Error.";
      $json["code"] = 0;
    }
	}
	else{
    $json["status"] = "Access Denied.";
    $json["code"] = 0;		
  } 
  
  //print json
	echo json_encode($json);
?>
