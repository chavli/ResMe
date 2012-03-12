<?php
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/credentials.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
	
	class ResumeTable{
		private $pdo = null;
		private $stmt_newresume = null;
		private $stmt_gettopN = null;
		
		//dynamically created
		private $stmt_getresumes = null;
		private $stmt_deleteresume = null;
		private $stmt_updateresume = null;
		private $stmt_updatetitles = null;
		private $stmt_updatetypes = null;

		//used for logging
		const TAG = "[API:resume]";

		function __construct(){
			global $dbhost, $dbname, $dbuser, $dbpass;
			$this->pdo = new PDO("mysql:host=".$dbhost.";dbname=".$dbname, $dbuser, $dbpass); 
			
			//prepare static statements
			$this->stmt_newresume = $this->pdo->prepare("insert into resume () values (null, :pdf_path, :type, :title, :owner_id, :album_id, :upvotes, :created)");
			$this->stmt_gettopN = $this->pdo->prepare("select * from `resume` as `left`".
				" join (select `id`, `username`, `firstname`, `lastname` from `user`) as `right` on".
				" `left`.owner_id = `right`.id where `upvotes` > 0 order by `upvotes`".
				" desc limit 0, :max");

			$this->stmt_getnewestN = $this->pdo->prepare("select * from `resume` as `left`".
				" join (select `id`, `username`, `firstname`, `lastname` from `user`) as `right` on".
				" `left`.owner_id = `right`.id order by `created` desc limit 0, :max");
		}

		function __destruct(){
			$this->pdo = null;
		}

		//insert a row into the resume table, return the id or FALSE on failure
    /*resume_data needs to contain the following items in the following order
    *[0] - text pdf_path, filepath of where the pdf file is on the server
    *[1] - integer type, resume type
    *[2] - text title, title of resume
    *[3] - interger owner_id, id of the user that owns this resume
    *[4] - integer album_id, id of the album that contains the images of this resume
    *[5] - datetime created, timestamp of when the resume was uploaded
    */
		public function newResume($resume_data){
			$retval = false;
			if(sizeof($resume_data) == 6){
				$this->stmt_newresume->bindParam(":pdf_path", $resume_data[0]);
				$this->stmt_newresume->bindParam(":type", $resume_data[1]);
				$this->stmt_newresume->bindParam(":title", $resume_data[2]);
				$this->stmt_newresume->bindParam(":owner_id", $resume_data[3]);
				$this->stmt_newresume->bindParam(":album_id", $resume_data[4]);
				$this->stmt_newresume->bindParam(":created", $resume_data[5]);
				
				$zero = 0;
				$this->stmt_newresume->bindParam(":upvotes", $zero);
				if($this->stmt_newresume->execute())
					$retval = $this->pdo->lastInsertId();
				else
					util_log(self::TAG."(newResume)".implode($this->stmt_newresume->errorInfo()));
			}
			return $retval;
		}
    
    //delete a resume by id or return FALSE on failure
		public function deleteResume($resume_id){
      return $this->deleteResumeByColumn("id", $resume_id);
		}
		
		//deletes resumes that match the given value in the given column
		//return false on failure, true on success
		public function deleteResumeByColumn($column, $value){
			//$this->stmt_deleteresume = $this->pdo->prepare("delete from resume where ? = ?");
			$ps = "delete from resume where `".$column."` = :value";
			$this->stmt_deleteresume = $this->pdo->prepare($ps);
			$this->stmt_deleteresume->bindParam(":value", $value);
			
			if(!($retval = $this->stmt_deleteresume->execute()))
				util_log(self::TAG."(deleteResumeByColumn)".implode($this->stmt_deleteresume->errorInfo()));

			return $retval;
		}

    //returns FALSE on update failure
    public function updateResumeTitles($album_ids, $titles){
			//set up the prepared statement
			//general statement: update resume set title = case album_id :cases end where album_id in (:ids);
			$args = array();
			$ps = "update resume set title = case album_id";
			$cases = "";

			//create the cases
			for($i = 0; $i < sizeof($titles); $i++){
				$ps .= " when :case".$i." then :value".$i;
				$args[":case".$i] = $album_ids[$i];	$args[":value".$i] = $titles[$i];
				$cases .= ($i == sizeof($titles) - 1) ? ":case".$i : ":case".$i.", ";
			}
			$ps .= " end where album_id in (".$cases.")";
			$this->stmt_updatetitles = $this->pdo->prepare($ps);

			if(!($retval = $this->stmt_updatetitles->execute($args)))
				util_log(self::TAG."(updateResumeTitles)".implode($this->stmt_updatetitles->errorInfo()));

			return $retval;
    }
    
    //returns FALSE on update failure   
    public function updateResumeTypes($resume_ids, $types){
			//setup the prepared statement
			//general statement: update resume set type = case album_id :cases end where album_id in (:ids);
			$args = array();
			$ps = "update resume set type = case album_id";
			$cases = "";

			//create the cases
			for($i = 0; $i < sizeof($types); $i++){
				$ps .= " when :case".$i." then :value".$i;
				$args[":case".$i] = $resume_ids[$i];	$args[":value".$i] = pow(2, $types[$i]);
				$cases .= ($i == sizeof($types) - 1) ? ":case".$i : ":case".$i.", ";
			}
			$ps .= " end where album_id in (".$cases.")";
			$this->stmt_updatetypes = $this->pdo->prepare($ps);	

			if(!($retval = $this->stmt_updatetypes->execute($args)))
				util_log(self::TAG."(updateResumeTypes)".implode($this->stmt_updatetypes->errorInfo()));

			return $retval;
    }
		

		/*
		*	changes the number of "upvotes" for a resume with resume_id by delta. 
		*
		*	returns false on failure, the number of likes on success
		*/
		public function judgeResume($resume_id, $delta){
			$resume = $this->getResume($resume_id);
			$retval = false;
			if($resume){
				$retval = $resume["upvotes"] + $delta;

				//make sure retval isnt < 0
				$retval = ($retval < 0) ? 0 : $retval;

				$this->updateResume($resume_id, array("upvotes"), array($retval));	
			}

			return $retval;
		}
	
		/*
		*	return all data associted with a resume with resume_id or false
		*	on failure
		*/
    public function getResume($resume_id){
      $resume = $this->getResumeByColumn("id",  $resume_id);
      return (sizeof($resume) >= 1) ? $resume[0] : false;    
    }
		
		//return all resumes that have value in their column
    public function getResumeByColumn($column, $value){
			//general statement: select * from resume where ? = ?
			$ps = "select * from resume where `".$column."` = :value";
			$this->stmt_getresumes = $this->pdo->prepare($ps);
			$this->stmt_getresumes->bindParam(":value", $value);

			$results = array();
			if($this->stmt_getresumes->execute())
				$results = $this->stmt_getresumes->fetchAll();
			else
				util_log(self::TAG."(getResumeByColumn)".implode($this->stmt_getresumes->errorInfo()));

			return $results;
    }

		/*
		*	fetch the N most liked resumes (with at least 1 vote). return a partial
		*	, or empty, array if N resumes don't satisfy the request.
		*/
		public function getTopResumes($n){
			$results = array();
			$this->stmt_gettopN->bindParam(":max", $n, PDO::PARAM_INT);
			if($this->stmt_gettopN->execute())
				$results = $this->stmt_gettopN->fetchAll();
			else
				util_log(self::TAG."(getTopResumes)".implode($this->stmt_gettopN->errorInfo()));

			return $results;
		}
	
		/*
		*	fetch the N newest resumes return a partial, or empty, array if N resumes
		* don't satisfy the request.
		*/
		public function getNewestResumes($n){
			$results = array();
			$this->stmt_getnewestN->bindParam(":max", $n, PDO::PARAM_INT);
			if($this->stmt_getnewestN->execute())
				$results = $this->stmt_getnewestN->fetchAll();
			else
				util_log(self::TAG."(getNewestResumes)".implode($this->stmt_getnewestN->errorInfo()));

			return $results;
		}	

		/*
		* updates a resume with the new values in the new fields by id
		*/
		public function updateResume($resume_id, $fields, $resume_data){
			return $this->updateResumeByColumn("id", $resume_id, $fields, $resume_data);
		}

		/*
		*	updates all resumes with value in column with the new data in their 
		*	respective fields.
		*
		*	returns true on success, false on failure
		*/
		public function updateResumeByColumn($column, $value, $fields, $resume_data){
			$ps = "update resume set ";
			$args = array();
			for($i = 0; $i < sizeof($fields); $i++){
				$ps .= $fields[$i]." = :".$fields[$i];
				$ps .= ($i == sizeof($fields) - 1) ? "" : "," ;
				$args[":".$fields[$i]] = $resume_data[$i];
			}
			$ps .= " where `".$column."`= :value";
			$args[":value"] = $value;

			$this->stmt_updateresume = $this->pdo->prepare($ps);
			if(!($retval = $this->stmt_updateresume->execute($args)))
				util_log(self::TAG."(updateResumeByColumn)".implode($this->stmt_updateresume->errorInfo()));
			
			return $retval;
		}

  }
?>
