<?php
	require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/credentials.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");

	class SubmissionTable{
		private $pdo = null;
		
		//static statements
		private $stmt_newsubmission = null;
		private $stmt_latestsubmissions = null;
		private $stmt_sortedsubmissions = null; //not used yet

		//dynamic statements
		private $stmt_getsubmission = null;
		private $stmt_deletesubmission = null;
		private $stmt_updatesubmission = null;

		//used for logging
		const TAG = "[API:submission]";

		function __construct(){
			global $dbhost, $dbname, $dbuser, $dbpass;
			$this->pdo = new PDO("mysql:host=".$dbhost.";dbname=".$dbname, $dbuser, $dbpass);

			//prepare static statements
			$this->stmt_newsubmission = $this->pdo->prepare("insert into submission () values (null, :data, :title, :description, :type, :category, :upvotes, :downvotes, :submitter, :time, :overflow)");

		}
		
		function __destruct(){
			$this->pdo = null;
		}

		/* creates a new entry in the submission table. $sub_data must and only contain the following 
		*elements in the following order:
		*[0]: blob data - what this submission represents
		*[1]: string title - user created title
		*[2]: string description - user created description
		*[3]: int type - bitmap stating the type of data stored in 'data'
		*[4]: int category - bitmap describing what categories this submission belongs to
		*[5]: int upvotes - number of users that like this post
		*[6]: int downvotes - number of users that dislike this post
		*[7]: string submitter - username of person that created this submission
		*[8]: datetime time - time of submission
		*[9]: blob overflow - used to hold misc data
		*/
		public function newSubmission($sub_data){
			$retval = false;
			if(sizeof($sub_data) == 10){
				$this->stmt_newsubmission->bindParam(":data", $sub_data[0]);
				$this->stmt_newsubmission->bindParam(":title", $sub_data[1]);
				$this->stmt_newsubmission->bindParam(":description", $sub_data[2]);
				$this->stmt_newsubmission->bindParam(":type", $sub_data[3]);
				$this->stmt_newsubmission->bindParam(":category", $sub_data[4]);
				$this->stmt_newsubmission->bindParam(":upvotes", $sub_data[5]);
				$this->stmt_newsubmission->bindParam(":downvotes", $sub_data[6]);
				$this->stmt_newsubmission->bindParam(":submitter", $sub_data[7]);
				$this->stmt_newsubmission->bindParam(":time", $sub_data[8]);
				$this->stmt_newsubmission->bindParam(":overflow", $sub_data[9]);
				if($this->stmt_newsubmission->execute())
					$retval = $this->pdo->lastInsertId();
				else
					util_log(self::TAG."(newSubmission)".implode($this->stmt_newsubmission->errorInfo()));
			}
			return $retval;
		}
		
		//get a submission row based on id, returns false on failure
		public function getSubmission($submission_id){
			$results = $this->getSubmissionsByColumn("id", $submission_id);
			return (sizeof($results) >= 1) ? $results[0] : false; 
		}
		
		//return the last, or up to,  N($amount) submissions 
		//or an empty array on failure
		//amount should be hard coded, and never user entered
		public function getLatestSubmissions($amount){
			$ps = "select * from submission order by time desc limit ".$amount;
			$this->stmt_latestsubmissions = $this->pdo->prepare($ps);
			
			$results = array();
			if($this->stmt_latestsubmissions->execute())
				$results = $this->stmt_latestsubmissions->fetchAll();
			else
				util_log(self::TAG."(getLatestSubmissions)".implode($this->stmt_latestsubmissions->errorInfo()));
			return $results;
		}

		//returns an array of submission rows  which have the given value in the given column.
		//returns an empty array if no matches are found
		public function getSubmissionsByColumn($column, $value){
			//dynamically create statement
			$results = array();
			
			//general statement: select * from submission where ? = ? 
			$ps = "select * from submission where `".$column."` = :value order by time desc";
			$this->stmt_getsubmission = $this->pdo->prepare($ps);
			$this->stmt_getsubmission->bindParam(":value", $value);
			if($this->stmt_getsubmission->execute())
				$results = $this->stmt_getsubmission->fetchAll();
			else
				util_log(self::TAG."(getSubmissionsByColumn)".implode($this->stmt_getsubmission->errorInfo()));
			
			return $results;
		}
		
		//deletes the submission row with the given id
		//returns true on success, false on failure
		public function deleteSubmission($submission_id){
			return $this->deleteSubmissionByColumn("id", $submission_id);
		}
		
		//deletes all submission rows which have the given value in the given column
		//returns true on success, false on failure
		public function deleteSubmissionByColumn($column, $value){
			$ps = "delete from tag where `".$column."` = :value";
			$this->stmt_deletesubmission = $this->pdo->prepare($ps);
			$this->stmt_deletesubmission->bindParam(":value", $value);
			
			if(!($retval = $this->stmt_deletesubmission->execute()))
				util_log(self::TAG."(deleteSubmissionsByColumn)".implode($this->stmt_deletesubmission->errorInfo()));
			return $retval;
		}
		
		//change the number of likes a submission has. magnitude can be positive or
		//negative depending on how the submission is judged
		//return false on failure, the number of likes on success
		public function judgeSubmission($submission_id, $magnitude){
			$retval = false;
			$submission = $this->getSubmission($submission_id);
			if($submission){
				$retval = $submission["upvotes"] + $magnitude;
				
				//check to make sure retval isnt < 0
				$retval = ($retval < 0) ? 0 : $retval;

				$this->updateSubmission($submission_id, array("upvotes"), array($retval));
			}

			return $retval;
		}

		//update the submission row in the given fields with the given values
		public function updateSubmission($submission_id, $fields, $values){
			$args = array();
			$ps = "update submission set ";
			for($i = 0; $i < sizeof($fields); $i++){
				$ps .= $fields[$i]."=:".$fields[$i];
				$args[":".$fields[$i]] = $values[$i];
				$ps .= ($i == sizeof($fields) - 1) ? "" : ", ";
			}
			$ps .= " where id = :id";
			$args[":id"] = $submission_id;
			
			$this->stmt_updatesubmission = $this->pdo->prepare($ps);
			if(!($retval = $this->stmt_updatesubmission->execute($args)))
				util_log(self::TAG."(updateSubmission)".implode($this->stmt_updatesubmission->errorInfo()));
			return $retval;
		}

	}

?>
