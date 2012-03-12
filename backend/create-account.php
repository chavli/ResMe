<?php
	date_default_timezone_set("America/New_York");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/resumepage.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/user.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/album.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/resume.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/backend/session_handler.php");
  new SessionHandler();
	session_start();
  
  if($_SESSION && !isset($_SESSION["LAST_ACTIVITY"])){
    //dont do anything if user isnt redirected to this page
    if(isset($_GET["act"]) && strcmp($_GET["act"], "create") == 0){
      /* by the time user-inputted data reaches this point, it has been
      sanitized and validated by register.php */

      /*format the user's name correctly(first letter capitalized, remaining letters lowercase)*/
      $_SESSION['firstname'] = ucwords(strtolower($_SESSION['firstname']));
      $_SESSION['lastname'] = ucwords(strtolower($_SESSION['lastname']));

      $ufirst = $_SESSION['firstname'];
      $ulast = $_SESSION['lastname'];
      $uname =  $_SESSION['username'];
      $umail =  $_SESSION['email'];
      $upass =  $_SESSION['password'];
      

      //this shouldnt be kept in session data after we're done using it
      unset($_SESSION['password']);
      /* calculate profession value */
      $prof_val = 0;
			if(isset($_SESSION['profs']))
      	foreach($_SESSION['profs'] as $val)
        	$prof_val += pow(2,$val);
      $_SESSION['profs'] = $prof_val;

      //create a directory for the new user                           
      $udir = $_SERVER["DOCUMENT_ROOT"].'/users/'.$uname."/";
      mkdir($udir, 0775);                                       
    
      /*IMPORTANT
      *the password is encrypted using the blowfish algorithm
      *http://www.schneier.com/blowfish.html
      *http://en.wikipedia.org/wiki/Blowfish_(cipher)#Cryptanalysis_of_Blowfish
      *
      *the Twofish algorithm is supposedly better, but i haven't found
      *a PHP implementation
      *
      *To use the blowfish algorithm you have to pass $2a$N$ as the salt 
      *argument for crypt(). N represents the log2 value of the number
      *of rounds the password goes through the blowfish algorithm. 
      *Right now I use N = 5 (32 rounds), N = 10 is suggested but takes 
      *a few seconds to calculate. N >= 16 is unbreakable (aside from brute force)
      *but that takes significantly more time and cpu cycles
      *
      *
      *NOTE: if your system doesn't support blowfish, the default php
      *encryption method will be used (usually MD5). The final version
      *should only use blowfish/twofish
      *
      *NOTE: using blowfish means we can't issue password reminders.
      *We would have to ask users to create a new password.
      */
      if(CRYPT_BLOWFISH == 1){		//this is a PHP constant
        $fishes = crypt($upass, "$2a$5$");
      }
      //NOTE: remove this once dedicated server is setup
      else{
        $fishes = crypt($upass);//MD5	
      }
      //end remove

			$respage_tbl = new ResumePageTable();
			$user_tbl = new UserTable();
			$album_tbl = new AlbumTable();
			$resume_tbl = new ResumeTable();
			$meta_tbl = new UserMetaTable(); 
     	
			$lastId = $respage_tbl->newEmptyResumePage();

      //if we get the id successfully, create a user with the retrieved resumepage
      if($lastId){
        $_SESSION["resumepage"] = $lastId;

        /*IMPORTANT
        *The privacy/permission settings for each user are stored in a bitfield
				*using unsigned mysql MEDIUMINTs. MEDIUMINTs are 16 bits wide. The 
				*following specification uses 15 of those bits:
        *
        *The bitfields are defined as follows:
        *|15|14|13|12|11|10|9|8|7|6|5|4|3|2|1|0|
				*						
				*[0-5] - public access bits
				*	0		- profile page access
				*	1		-	comment page access
				*	2		- show resume tags
				*	3		- allow feedback(comments)
				*	4		-	downloadable (allow people to save PDF)
				*	5		-	stackable (allow people to bookmark your resume)
				*[6-11]	- resme access bits
				*	6		- profile page access
				*	7		-	comment page access
				*	8		- show resume tags
				*	9		- allow feedback(comments)
				*	10	-	downloadable (allow people to save PDF)
				*	11	-	stackable (allow people to bookmark your resume)
				*[12] - unused
				*[13-15] - flags
				*	13	- seachable (allow profile to show up in resme searches)
				*	14	- indexed	(allow profile to show up in search engines)
				*	15	-	owner	(set if the owner is viewing their own profile)
				*
        *
        *the default privacy settings are set to:
        * public access:
				*		view profile page				1
				*		no comment page access	0
				*		show resume tags				1
				*		disable comments				0
				*		not downloadable				0
				*		not stackable						0
				*	resme access:
				*		view profile page				1
				*		view comments page			1
				*		show resume tags				1
				*		allow comments					1
				*		downloadable						1
				*		stackable								1
				*
				*	unused										0
				*	flags
				*		searchable							1
				*		indexed									0
				*		owner										0
        *	
        *	0010111111000101 = 12229
        *
        */
        $_SESSION["perms"] = 12229;

        //create query
        $ins_vals = array(
          $uname,
          $fishes,
          $umail,
          $ufirst,
          $ulast,
          0,
          "",
          0,
          $lastId,"","","",$prof_val, $_SESSION["perms"]
        );
				$_SESSION["id"] = $user_tbl->newUser($ins_vals);

        //create initial profile image album and resume album
        //profile picture album
				$palbum_id = $album_tbl->newAlbum(array($_SESSION["id"], 1, "profile image album", 0, date("Y-m-d H:i:s")));

        //initial resume album
				$ralbum_id = $album_tbl->newAlbum(array($_SESSION["id"], 1, "first resume album", 1, date("Y-m-d H:i:s")));

        /*initial user folder file structure
        *	
        *users
        *	|-><username>
        *		|->albums
        *			|-><palbum_id>
        *			|-><ralbum_id>
        *		|->resumes
        *		|->uploads	
        */
        mkdir($udir."albums", 0775);
        mkdir($udir."uploads", 0775);
        mkdir($udir."resumes", 0775);
        
        mkdir($udir."albums/".$palbum_id, 0775);
        mkdir($udir."albums/".$ralbum_id, 0775);
        //end file structure creation

        //filepath of first profile picture
        $propath = util_tempnam_safe($udir."albums/".$palbum_id."/", ".jpg");
        $pro_image = explode("/", $propath);
        $pro_image = $pro_image[sizeof($pro_image) - 1];  //get the filename
        
        //filepath of first resume picture
        $resdir = $udir."albums/".$ralbum_id;	

        //update user data with new album info
				$user_tbl->updateUser($uname, array("profile_album", "current_profile_picture", "resume_album"), array($palbum_id, $pro_image, $ralbum_id));
				$respage_tbl->updateResumePage($_SESSION["resumepage"], array("owner_id", "album_id", "page"), array($_SESSION["id"], $ralbum_id, 0));

				//create resume entry
				$values = array(
					"",
					0,
					"template",
					$_SESSION["id"],
					$ralbum_id,
					date("Y-m-d H:i:s")
				);
				$resume_tbl->newResume($values);
      
        copy($_SERVER["DOCUMENT_ROOT"]."/system/images/default_pic.jpg", $propath);
        copy($_SERVER["DOCUMENT_ROOT"]."/system/images/resume.jpg", $resdir."/0.jpg"); 

        $_SESSION["profile_album"] = $palbum_id;
        $_SESSION["current_profile_picture"] = $pro_image;
        $_SESSION["resume_album"] = $ralbum_id;

				//create user metadata
				$stack = array();	//used to store favorited resumes
				$votes = array("up" => array(), "down" => array());	//used to store how the user votes
				$resume_likes = array();	//used to store the list of resume ids a user likes

				$meta_tbl->newUserMeta(array($_SESSION["username"], $_SESSION["id"], serialize($stack), serialize($votes), serialize($resume_likes)));
			
				$_SESSION["stack"] = serialize($stack);
				$_SESSION["votes"] = serialize($votes);
				$_SESSION["resume_likes"] = serialize($resume_likes);

        //mark user as logged in and redirect to their profile page
        $_SESSION['LAST_ACTIVITY'] = time();

        //clean up table objects
        unset($respage_tbl);
        unset($user_tbl);
        unset($album_tbl);
        unset($resume_tbl);
        unset($meta_tbl);
        
        header("Location: /".$uname);
      }
      else{
        //clean up table objects
        unset($respage_tbl);
        unset($user_tbl);
        unset($album_tbl);
        unset($resume_tbl);
        
        //this should never be executed.
        header("Location: /fatalcrash.php");
      }
    }
  }
?>
