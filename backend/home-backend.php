<?php
	date_default_timezone_set("America/New_York");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/notification.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/submission.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/user.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/resume.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/bitwise.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/backend/session_handler.php");
  new SessionHandler();
	session_start();
  util_session_check();

	$json = array();
	if(isset($_SESSION["LAST_ACTIVITY"]) && isset($_GET["act"])){
		//fetch all the info to be displayed on an user's homepage

		//get a user's personal notifications
		if(strcmp($_GET["act"], "show") == 0 ){
			$json["firstname"] = $_SESSION["firstname"];
			$json["lastname"] = $_SESSION["lastname"];
			
			$noti_tbl = new NotificationTable();
			//user notifications
			$notedata = $noti_tbl->getNotificationsByColumn("to", $_SESSION["username"]);
    	unset($noti_tbl);
		
			$user_notes = array();
			foreach($notedata as $item)
				array_push($user_notes, array("type" => intval($item["type"]), "created" => $item["created"], "from" => $item["from"], "data" => $item["data"]));	
			
			$json["notifications"] = $user_notes;
			$json["name"] = $_SESSION["firstname"];
			$json["code"] = 1;
			$json["status"] = "Success";
		}
		//get global notifications
		else if(strcmp($_GET["act"], "global") == 0 ){
	  	$notifications = util_xmlfile_to_array($util_notes_xml);
			
			if(sizeof($notifications) > 0){
				$global_notes = array(); //contains all global notifications
				$single = array();	//contains data about a single global notification
				//parse the xml array
			  foreach($notifications as $entry){
					//the date value comes before the message value
			    if(strcmp($entry["tag"], "DATE") == 0)
						$single["date"] = $entry["value"];
			    else if(strcmp($entry["tag"], "MESSAGE") == 0){
						$single["message"] = $entry["value"];
						//at this point i assume that the date and message fields are in the array and related
						//so i add it to the global notification array
						array_push($global_notes, $single);
					}
		  	}
			}
			$json["global"] = $global_notes;
			$json["name"] = $_SESSION["firstname"];
			$json["code"] = 1;
			$json["status"] = "Success";
		}
		
		//fetch all the resumes to display in explore resumes tab
		else if(strcmp($_GET["act"], "explore") == 0 ){
			$json["code"] = 1;
			$popular = array();
			$newest = array();
			$related = array();
			
			//get popular resumes
			$resume_tbl = new ResumeTable();
			$results = $resume_tbl->getTopResumes(5);

			$entry = array();	
			foreach($results as $result){
				$entry["username"] = $result["username"];
				$entry["firstname"] = $result["firstname"];
				$entry["lastname"] = $result["lastname"];
				$entry["title"] = $result["title"];
				$entry["upvotes"] = $result["upvotes"];
				$entry["album_id"] = $result["album_id"];
				array_push($popular, $entry);
			}
			
			$results = $resume_tbl->getNewestResumes(5);
			$entry = array();
			foreach($results as $result){
				$entry["username"] = $result["username"];
				$entry["firstname"] = $result["firstname"];
				$entry["lastname"] = $result["lastname"];
				$entry["title"] = $result["title"];
				$entry["created"] = $result["created"];
				$entry["album_id"] = $result["album_id"];
				array_push($newest, $entry);
			}


			$json["popular"] = $popular;
			$json["newest"] = $newest;
			$json["status"] = "Resumes Fetched.";

			unset($resume_tbl);
		}
		
		//submit an article
		else if(strcmp($_GET["act"], "submit") == 0 ){
			$json["code"] = 0;

			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				//check if the url is valid
				if(isset($_POST["url"]) && util_url_exists($_POST["url"])){
				
					//figure out categories
					$categories = 0;
					if(isset($_POST["jobs"]))
						$categories |= $bm_jobs;
					if(isset($_POST["business"]))
						$categories |= $bm_business;
					if(isset($_POST["economy"]))
						$categories |= $bm_economy;
					if(isset($_POST["advice"]))
						$categories |= $bm_advice;
					
					//prepare the data to be uploaded
					$data = array(
						$_POST["url"],
						$_POST["title"],
						$_POST["description"],
						0,
						$categories,
						0,
						0,
						$_SESSION["username"],
						date("Y-m-d H:i:s"),
						0
					);

					//add row to table
					$subm_tbl = new SubmissionTable();
					if($subm_tbl->newSubmission($data)){
						$json["code"] = 1;
						$json["status"] = "Article Submitted.";
					}
					else
						$json["status"] = "Database Error.";
				}	
				//invalid URL
				else
					$json["status"] = "Invalid URL.";
			}
			else
				$json["status"] = "Submission Error.";
		}
		//return last N user submitted articles
		else if(strcmp($_GET["act"], "articles") == 0 ){
			$subm_tbl = new SubmissionTable();
			$raw_articles = $subm_tbl->getLatestSubmissions(10);
			
			$user_votes = unserialize($_SESSION["votes"]);

			$articles = array();
			//translate type int to a string
			foreach($raw_articles as $article){
				$category = $article["category"];
				$scategory = array();
				if($category & $bm_jobs)
					array_push($scategory, "Jobs");
				if($category & $bm_business)
					array_push($scategory, "Business");
				if($category & $bm_economy)
					array_push($scategory, "Economy");
				if($category & $bm_advice)
					array_push($scategory, "Advice");
				
				$article["category_strings"] = $scategory;
				$article["judged"] = in_array($article["id"], $user_votes["up"]) ? 1 : 0;
				array_push($articles, $article);
			}
			
			unset($subm_tbl);

			$json["articles"] = $articles;
			$json["length"] = sizeof($articles);
			$json["code"] = 1;
			$json["status"] = "Articles Fetched.";
		}

		//compile all the data related to the user's stack and return all the info
		//needed to display their stack
		else if(strcmp($_GET["act"], "stack") == 0 ){
			$raw_stack = unserialize($_SESSION["stack"]);	
			$user_tbl = new UserTable();
			$stack = array();
			
			//make this more SQL friendly
			foreach($raw_stack as $id){
				$user_data = $user_tbl->getUser($id);
				$user_arr = array(
					"id" => $id,
					"username" => $user_data["username"],
					"firstname" => $user_data["firstname"],
					"lastname" => $user_data["lastname"],
					"resume_album" => $user_data["resume_album"]
				);
				array_push($stack, $user_arr);
			}
			$json["code"] = 1;
			$json["status"] = "Stack Compiled";
			$json["stack"] = $stack;
			unset($user_tbl);
		}
		//add the id of the article the user approved to their list of approved
		//articles
		else if(strcmp($_GET["act"], "judge") == 0 ){
			if(isset($_GET["id"])){
				$raw_votes = unserialize($_SESSION["votes"]);
				$delta = 0;

				//add or remove the article id
				if(!in_array($_GET["id"], $raw_votes["up"])){
					$article_id = $_GET["id"];
					$raw_votes["up"][$article_id] = $article_id;
					$json["display"] = "Unapprove";	//new value to display
					$delta = 1;
				}
				else{
					$article_id = $_GET["id"];
					unset($raw_votes["up"][$article_id]);
					$json["display"] = "Approve";	//new value to display
					$delta = -1;
				}
				$subm_tbl = new SubmissionTable();
				$likes = $subm_tbl->judgeSubmission($_GET["id"], $delta);
				unset($subm_tbl);

				$_SESSION["votes"] = serialize($raw_votes);
				
				if($likes >= 0){
					$json["code"] = 1;
					$json["likes"] = $likes;
					$json["status"] = "Judging Approved.";
				}
				else{
					$json["code"] = 0;
					$json["status"] = "Judging Failed.";
				}
			}
			else{
				$json["code"] = 0;
				$json["status"] = "Unknown Article.";
			}
		}
		else if(strcmp($_GET["act"], "disapprove") == 0 ){}
		else{
			$json["code"] = 0;
			$json["status"] = "Unknown Request";
		}
	}
	else{
		$json["code"] = 0;
		$json["status"] = "Access Denied.";
  }

	echo json_encode($json);
?>

