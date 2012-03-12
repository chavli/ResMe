<?php
  date_default_timezone_set("America/New_York");
	require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/SQLConnection.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/backend/session_handler.php");
	new SessionHandler();
	session_start();
	util_session_check();
	//check if a file has been submitted 
	if(isset($_SESSION["LAST_ACTIVITY"])){
		$sql_conn = new SQLConnection;
		$sql_conn->establish();

		if(isset($_FILES['userfile']['tmp_name'])){
      $album_id = 0;
			//loop through files and make sure each one is ok
      //for errors, $album_id will store the error code and $final_file_count will store the error message
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
			for($i=0; $i < count($_FILES['userfile']['tmp_name']);$i++){
        $img_type = finfo_file($finfo, $_FILES['userfile']['tmp_name'][$i]);
        //check if the file was uploaded by our server
				if(!is_uploaded_file($_FILES['userfile']['tmp_name'][$i])){
					$album_id = $UTIL_FFORM_ERR;
					$final_file_count = "Invalid File: ".$_FILES['userfile']['name'][$i];
					$i = count($_FILES['userfile']['tmp_name']);
				}
        //check if the file is an image
				else if( !getimagesize($_FILES['userfile']['tmp_name'][$i]) || !in_array($img_type, $util_allowed_img_types)){
					$album_id = $UTIL_FTYPE_ERR;
					$final_file_count = "Invalid Image Format: ".$_FILES['userfile']['name'][$i];
					$i = count($_FILES['userfile']['tmp_name']);
				}
        //check if the image file is too large
				else if($_FILES['userfile']['size'][$i] > $UTIL_IMG_LIM){
					$album_id = $UTIL_FSIZE_ERR;
					$final_file_count = "Image exceeds limit (1MB): ".$_FILES['userfile']['name'][$i];
					$i = count($_FILES['userfile']['tmp_name']);
				}
			}
      finfo_close($finfo);
      
      if($album_id > -1){
        //create a new album
        $values = array(
          "null",
          $_SESSION["id"],
          1,	
          "'new tag album'",
          0,
          "'".date("Y-m-d H:i:s")."'"
        );
        $sql_conn->insertAll("album", $values);
        //fetch the id of the new album
        $qra = mysql_query("select LAST_INSERT_ID() as lastid;") or die(mysql_error());
        $row = mysql_fetch_array($qra);
        $album_id = $row["lastid"];
        
        //set up the new album's directory
        mkdir("users/".$_SESSION["username"]."/albums/".$album_id, 0775);

        // loop through the array of files.  
        $final_file_count = 0;
        for($i=0; $i < count($_FILES['userfile']['tmp_name']);$i++){
           //NOTE: for loop here to copy over all page images
          $rpath = "users/".$_SESSION["username"]."/albums/".$album_id."/".$i.".jpg";
          
          //convert all the images to jpgs
          system("convert ".$_FILES['userfile']['tmp_name'][$i]."[0] -background white -flatten ".$rpath);
          $final_file_count++;
        }

        $qra2 = mysql_query("update album set length=".$final_file_count." where id=".$album_id.";") or die(mysql_error());
      }
		}
    else{
      $album_id = $UTIL_FMISS_ERR;
      $final_file_count = "No Images Selected.";
    }
    
		echo $album_id.",".$final_file_count;
	}
?>
