<?php
  date_default_timezone_set("America/New_York");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/user.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/album.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/resumepage.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/api/resume.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/backend/session_handler.php");
  new SessionHandler();
	session_start();
  util_session_check();
  
  $newcurrent = -1;
  $json = array();
	if(isset($_SESSION["LAST_ACTIVITY"]) && isset($_GET["act"])){
		$uname = $_SESSION["username"];
    
		if(strcmp($_GET["act"], "update") == 0){
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				$newcurrent = $_POST["currentresume"]; 	//resume to set as current
				$todelete = isset($_POST["deleteresume"]) ? $_POST["deleteresume"] : array();			      //resumes to delete
				$titles = $_POST["title"];
				$types = $_POST["resType"];
				$ids = $_POST["id"];    //album ids of all resumes displayed
       
        $resume_tbl = new ResumeTable();
        $resume_tbl->updateResumeTitles($ids, $titles);
        $resume_tbl->updateResumeTypes($ids, $types);
		
				$album_tbl = new AlbumTable();
				$user_tbl = new UserTable();
				$respage_tbl = new ResumePageTable();
        
        //TODO delete resume pdf file
        $numdeleted = 0;
				for($i = 0; $i < sizeof($todelete); $i++){
					if($todelete[$i] != $newcurrent){	//dont delete the album that's set as the new current!
						$album_tbl->deleteAlbum($todelete[$i]);
						$respage_tbl->deleteResumePagesByAlbum($todelete[$i]);
            $resume_tbl->deleteResumeByColumn("album_id", $todelete[$i]);
            if($todelete[$i]) { util_rrmdir("../users/".$_SESSION["username"]."/albums/".$todelete[$i]); }
            $numdeleted++;
          }
				}
        
        //check if all resumes have been deleted, if so set the template as the main resume
        if($numdeleted == (sizeof($ids) - 1))
          $newcurrent = $ids[sizeof($ids) - 1];
        
				//update info in the user table
				if($newcurrent > -1){
					//look up the first page of the new resume
					$pages = $respage_tbl->getResumePagesByColumn("album_id", $newcurrent);
					$resumepage = $pages[0];
					
					$user_tbl->updateUser($_SESSION["username"], array("resume_album", "resumepage"), array($newcurrent, $resumepage["id"]));
					$_SESSION["resume_album"] = $newcurrent;
					$_SESSION["resumepage"] = $resumepage["id"];
				}        
        
        $json["resumes"] = fetchResumes();
        $json["status"] = "Your resumes have been updated. Hooray! Return to your <a href='/".$_SESSION["username"]."'>Profile</a>.";
        $json["code"] = 1;
        
        //clean up table objects
        unset($resume_tbl);
				unset($album_tbl);
				unset($user_tbl);
				unset($respage_tbl);
        
			}
      else{
        $json["status"] = "No changes were made.";
        $json["code"] = 0;
      }
		}
    else if(strcmp($_GET["act"], "show") == 0){
      $json["resumes"] = fetchResumes();
      $json["status"] = "These are your resumes as of ".date('l, F j');
      $json["code"] = 1;
    }
    //uploaded a new resume
    else if(strcmp($_GET["act"], "upload") == 0){
      $res_name = null;
			$res_size = null;
      $json["code"] =0;
      
      if(isset($_FILES["resFile"])){
        $res_name = $_FILES["resFile"]["tmp_name"];
	      $res_size = $_FILES["resFile"]["size"];
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if($res_name) {$res_type = finfo_file($finfo, $res_name);}
        finfo_close($finfo);
        
        //current resume info
        $ralbum_id = $_SESSION["resume_album"]; 
        $page_id = $_SESSION["resumepage"];

        if( $res_size > $UTIL_PDF_LIM ){ $json["status"]= "Resumes are limited to 300KB.";}
        else if	( !is_uploaded_file($res_name) || !in_array($res_type, $util_allowed_doc_types) ){ 
          $json["status"] = "Improper Resume Format.";
        }
        else{
          $album_tbl = new AlbumTable();
          $page_tbl = new ResumePageTable();
          $resume_tbl = new ResumeTable();
          $user_tbl = new UserTable();
          
          //an album is used to group the images of resume pages together
          //create a new resume album
          $values = array(
            $_SESSION["id"],
            1,	
            "new resume album",
            1,
            date("Y-m-d H:i:s")
          );
          
          $ralbum_id = $album_tbl->newAlbum($values);
          
          //create the directory to store the new resume album
          $path = $_SERVER["DOCUMENT_ROOT"]."/users/".$uname."/albums/".$ralbum_id;
          $json["foo"] = $uname;
          mkdir($path, 0775);

          //convert the pdf's pages into images and store them in the new album directory
          //pages are limited to the first 5
          system("convert -density 200 ".$res_name."[0-4] -geometry 970 ".$path."/%d.jpg", $result);
          $filecount = count(glob($path."/*.jpg"));
  
          //save the uploaded pdf file to the users directory for later use
					//TODO make this part prettier
          $abs_path = util_tempnam_safe($_SERVER["DOCUMENT_ROOT"]."/users/".$uname."/resumes/", ".pdf");
					$filename = preg_split("/\//", $abs_path);
					$filename = $filename[sizeof($filename) - 1];
					$pdf_path = "users/".$uname."/resumes/".$filename;
					//end TODO

          move_uploaded_file($res_name, $abs_path);
          
          //update length of the album
          $album_tbl->updateAlbum($ralbum_id, array("length"), array($filecount));
 
          //the resumepage is used to link tags to a page within a resume
          //insert the first page, and save the id value
          $values = array(
            $_SESSION["id"],
            $ralbum_id,
            0,
            $pdf_path
          );
          $page_id = $page_tbl->newResumePage($values);
  
          //insert the remaining pages	
          //TODO streamline this into 1 query
          for($i = 1; $i < $filecount; $i++){
            $values = array(
              $_SESSION["id"],
              $ralbum_id,
              $i,
              $pdf_path
            );          
            $page_tbl->newResumePage($values);
          }

          //create an entry in the resume table: the resume table will be used to perform searches on resumes as a whole
          $values = array(
            $pdf_path,
            1,
            "New Resume",
            $_SESSION["id"],
            $ralbum_id,	
            date("Y-m-d H:i:s")
          );
          $resume_tbl->newResume($values);
        
          $_SESSION["resume_album"] = $ralbum_id;
          $_SESSION["resumepage"] = $page_id;
          
          //sql querries, ASSEMBLE! update database with new data
          $fields = array(
            "resume_album",
            "resumepage");
          $values = array(
            $ralbum_id,
            $page_id);
          $user_tbl->updateUser($uname, $fields, $values);

          $json["resumes"] = fetchResumes();
          $json["status"] = "Resume Uploaded.";
          $json["code"] = 1;       
          
          //clean up table objects
          unset($album_tbl);
          unset($page_tbl);
          unset($resume_tbl);
          unset($user_tbl);
        }
      }
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
  
  function fetchResumes(){
   	$user_tbl = new UserTable();

    $json = array();
    //grab all resume data of the user
		$resumedata = $user_tbl->getUserResumes($_SESSION["id"]);

    //parse the xml list of resume types
    $xmlfile = "../system/xml/resumetypes.xml";
    $parsedxml = util_xmlfile_to_array($xmlfile);
    $type_array = array();
    foreach($parsedxml as $entry){
      if(strcmp($entry["tag"], "RESUMETYPE") == 0)
        array_push($type_array, trim($entry["value"]));
    }
    
    foreach($resumedata as $item){
      $options = "";
      $checked = "";
      $resdata = array();
      $resdata["owner"] = $_SESSION["username"];
      $resdata["album_id"] = $item["album_id"];
      $resdata["title"] = $item["title"];
      $resdata["path"] = $item["pdf_path"];
      $resdata["created"] = $item["created"];
      $resdata["pages"] = $item["length"];
      $resdata["iscurrent"] = 0;
      $resdata["type"] = $item["type"];
      $resdata["alltypes"] = $type_array;
     
      if($item["pdf_path"] && $item["album_id"] == $_SESSION["resume_album"])
        $resdata["iscurrent"] = 1;
        
      array_push($json, $resdata);
   	}
    unset($user_tbl);
    return $json;
  }
?>
