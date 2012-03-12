<?php
  date_default_timezone_set("America/New_York");
  
  //some globals
	$util_allowed_doc_types = array("application/pdf");
  $util_allowed_vid_types = array("video/x-flv", "video/mpeg", "video/mp4");
  $util_allowed_img_types = array("image/jpeg", "image/jpg", "image/pjpeg", "image/bmp", "image/gif", "image/png", "image/x-png", "image/tiff");

	//resume tag types
  $UTIL_TXT_TAG = 1;
  $UTIL_PIC_TAG = 2;
  $UTIL_LVID_TAG = 3;	//linked vid
  $UTIL_UVID_TAG = 4;	//uploaded vid
  $UTIL_SND_TAG = 5;
  $UTIL_DOC_TAG = 6;

	//notification types
	$UTIL_CMT_NOTE = 1;
	$UTIL_ADDSTACK_NOTE = 2;	//add resume to stack
	$UTIL_APPRES_NOTE = 3;	//approve resume
	$UTIL_C_NOTE = 4;
	$UTIL_D_NOTE = 5;

  //file errors
  $UTIL_FTYPE_ERR = -1;	//file type error
  $UTIL_FSIZE_ERR = -2;	//file size error
  $UTIL_FFORM_ERR = -3;	//illegal file error
  $UTIL_FMISS_ERR = -4;	//missing file error
  $UTIL_FSERV_ERR = -5;	//server side error with file

	//file size limits
	$UTIL_PDF_LIM = 1048576;		//1MB
	$UTIL_IMG_LIM = 4194304; 		//4MB
  $UTIL_VID_LIM = 52428800; 	//50MB

	//xml filepaths
	$util_profs_xml = $_SERVER["DOCUMENT_ROOT"]."/system/xml/professions.xml";
	$util_notes_xml = $_SERVER["DOCUMENT_ROOT"]."/system/xml/globalnotifications.xml";
  
	/*return a randomize file name */
	function util_tempnam_safe($path, $ext){
		do{ 
			$filename = $path.mt_rand().$ext; 
			$fp = @fopen($filename, 'x'); 
		 }while(!$fp); 
		 fclose($fp); 
		 return $filename; 
	}

	/*sanitize a string.*/
	function util_sanit_str($str, $link){
		$retval = False;
		if($str){
			$str = trim($str);															//get rid of leading/trailing whitespace
			if($link)
				$str = mysql_real_escape_string($str, $link);	//prevent sql injections
			//$str = htmlentities($str);											//get rid of html code
			$retval = $str;
		}
		return $retval;
	}
  
  /*convenience function to sanitize an array. array passed by reference*/
  function util_sanit_array(&$data, &$link){
    for($i = 0; $i < sizeof($data); $i++)
      $data[$i] = util_sanit_str($data[$i], $link);
  }
  
  
  /*
   *  check if the session data has expired. a session is valid for 1 hour, if 
   * the timestamp isn't updated within that time, the sesson will be destroyed
   *	NOTE: maybe this can be integrated into system/php/session_handler.php
	 */
  function util_session_check(){
    if(isset($_SESSION["username"])){
      //sessions expire after 1 hour of inactivity
      if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 3600)) {
				unset($_SESSION['LAST_ACTIVITY']);
        session_destroy();   // destroy session data in storage
        session_unset();     // unset $_SESSION variable for the runtime
      }
      else{
        $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
      }
    }
    else{
      session_destroy();   // destroy session data in storage
      session_unset();     // unset $_SESSION variable for the runtime    
    }
  }
  
  /*
  * recursively remove a directory and all of it's contents
  */
  function util_rrmdir($path){
    if(is_dir($path)){
      $files = scandir($path);
      foreach ($files as $file){
        if($file != "." && $file != ".."){
          if(filetype($path."/".$file) == "dir")  //another directory
            util_rrmdir($path."/".$file);
          else  //a file!
            unlink($path."/".$file);
        }
      }
    }
		rmdir($path);
  }
  
  /*
  * parse a xml file and return its contents in an array
  */
  function util_xmlfile_to_array($path){
    $parsedxml = False;
    if(file_exists($path)){
      $xmlhandler = xml_parser_create();
      $filehandler = fopen($path, "r");
      $xmldata  = fread($filehandler, filesize($path));
      xml_parse_into_struct($xmlhandler, $xmldata, $parsedxml);
      fclose($filehandler);
      xml_parser_free($xmlhandler);
    }
    return $parsedxml;
  }

	/*
	* parse a string in flat text format (see below) into an array
	*	format:
	*		<key1>|<type1>:<length1>:<value1>;<key2>|<type2>:<length2>:<value2>;...
	*/
	function util_flatstr_to_array($flatstr){
		$patterns = array(
      "/^.:[0-9]+:/",	//get rid of type + length info (s:10:)
      "/^.:/",				//get rid of type info (i:)
      "/;$/",					//get rid of ending ;
      "/^\"a/",				//get rid of opening " when dealing with serialized arrays				
      "/}\"$/",				//get rid of close " when dealing with serialized arrays
      "/^\"/",				//get rid of opening "
      "/\"$/",				//get rid of closing "
			"/'/"
    );
    $replace = array("", "",	"",	"a", "}",	"'","'","\"");
    
		$sess_arr = array();
    while($break = strrpos($flatstr, "|")){
      $stop = strrpos($flatstr, ";", $break - strlen($flatstr));
      if(!$stop){ $stop = -1; } 
      $data = substr($flatstr, $stop + 1);
      $data = explode("|", $data);
      $value = preg_replace($patterns, $replace, $data[1]);
      $sess_arr[$data[0]] = $value; //data[0] contains the key
      $flatstr = substr($flatstr, 0, $stop + 1);
    }
		return $sess_arr;
	}

	/*
	* uses curl to check if a url is valid. valid meaning the link takes you to a
	*	website.
	* returns true if valid, false otherwise
	*/
	function util_url_exists($url){
		$retval = false;
		if($url){
			$curler = curl_init($url);
			curl_setopt($curler, CURLOPT_TIMEOUT, 5);
			curl_setopt($curler, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($curler, CURLOPT_RETURNTRANSFER, true);
			curl_exec($curler);
			$response = curl_getinfo($curler, CURLINFO_HTTP_CODE);
			util_log("http code: ".$response);
			curl_close($curler);
			if($response >= 200 && $response < 400)
				$retval = true;
		}
		return $retval;
	}

	/*
	* output a string to a log file
	*/
	function util_log($message){
		$fhandle = fopen($_SERVER["DOCUMENT_ROOT"]."/logs/util.log", "a");
		$retval = fwrite($fhandle, "[".date("m/d/Y H:i:s")."]: ".$message."\n"); 
		fclose($fhandle);
		return $retval;
	}

?>
