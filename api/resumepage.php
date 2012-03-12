<?php
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/credentials.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");

	class ResumePageTable{
		private $pdo = null;
		private $stmt_newpage = null;

		//dynamically built
		private $stmt_getpages = null;
		private $stmt_deletepage = null;
		private $stmt_updatepage = null;	

		//used for logging
		const TAG = "[API:resumepage]";

		function __construct(){
			global $dbhost, $dbname, $dbuser, $dbpass;
			$this->pdo = new PDO("mysql:host=".$dbhost.";dbname=".$dbname, $dbuser, $dbpass);

			//prepare static statements
			$this->stmt_newpage = $this->pdo->prepare("insert into resumepage () values (null, :owner_id, :album_id, :page, :pdf_name)");
		}

		function __destruct(){
			$this->pdo = null;
		}

    /* the page_data array argument needs to contain the following elements in the following order
    * [0] - int owner_id, id of the user that owns this album
    * [1] - int album_id, album the image of this page is in
    * [2] - int page, page within the resume
    * [3] - text pdf_name, the resume's pdf file
    */
    public function newResumePage($page_data){
			$retval = false;
      if(sizeof($page_data) == 4){
				//bind variables
				$this->stmt_newpage->bindParam(":owner_id", $page_data[0]);
				$this->stmt_newpage->bindParam(":album_id", $page_data[1]);
				$this->stmt_newpage->bindParam(":page", $page_data[2]);
				$this->stmt_newpage->bindParam(":pdf_name", $page_data[3]);
				if($this->stmt_newpage->execute())
					$retval = $this->pdo->lastInsertId();
				else
					util_log(self::TAG."(newResumePage)".implode($this->stmt_newpage->errorInfo()));
			}
			return $retval;
    }

		//inserts a new resumepage and returns its ID or FALSE on failure
		public function newEmptyResumePage(){
      return $this->newResumePage(array("0","0","-1",""));
		}
		
		//get a resume page based on id. return false if id doesnt match anything
    public function getResumePage($page_id){
      $results = $this->getResumePagesByColumn("id", $page_id);
      return (sizeof($results) >= 1) ? $results[0] : false;
    }

		//returns the resumepages for the album with album_id or FALSE on failure
		public function getResumePagesByColumn($column, $value){
			//general statement: select * from resumepage where ? = ? order by page asc
			$ps = "select * from resumepage where `".$column."` = :value order by page asc";
			$this->stmt_getpages = $this->pdo->prepare($ps);
			$this->stmt_getpages->bindParam(":value", $value);

			$results = array();
			if($this->stmt_getpages->execute())
				$results = $this->stmt_getpages->fetchAll();
			else
				util_log(self::TAG."(getResumePagesByColumn)".implode($this->stmt_getpages->errorInfo()));
			
			return $results;
		}
    
    //update info about a resume page, return false on failure
		public function updateResumePage($page_id, $fields, $page_data){
			//dynamically create the update prepared statement
			//general structure: update resumepage set ? where id = ?
			$ps = "update resumepage set ";
			for($i = 0; $i < sizeof($fields); $i++){
				$ps .= $fields[$i]."=:".$fields[$i];
				$ps .= ($i == sizeof($fields) - 1) ? "" : ", ";

				//dict that maps new data to placeholders in the statement (:foo => data)
				$args[":".$fields[$i]] = $page_data[$i];
			}
			$ps .= " where id = :page_id";
			$args[":page_id"] = $page_id;

			$this->stmt_updatepage = $this->pdo->prepare($ps);
			if(!($retval = $this->stmt_updatepage->execute($args)))
				util_log(self::TAG."(updateResumePage)".implode($this->stmt_updatepage->errorInfo()));

			return $retval;
		}
  	
		//delete a resumepage by id, returns false on failure
    public function deleteResumePage($page_id){
      return $this->deleteResumePagesByColumn("id", $page_id);
    }

    //delete resume pages associated with a user. return FALSE on failure
    public function deleteResumePagesByOwner($owner_id){
      return $this->deleteResumePagesByColumn("owner_id", $owner_id);
    }

    //delete resume pages associated with an album. return FALSE on failure
    public function deleteResumePagesByAlbum($album_id){
      return $this->deleteResumePagesByColumn("album_id", $album_id);
    }
    
    //delete resumepages with value in column
    public function deleteResumePagesByColumn($column, $value){
			//general statement: delete from resumepage where ? = ?
			$ps = "delete from resumepage where `".$column."` = :value";
			$this->stmt_deletepage = $this->pdo->prepare($ps);
			$this->stmt_deletepage->bindParam(":value", $value);
			
			if(!($retval = $this->stmt_deletepage->execute()))
				util_log(self::TAG."(deleteResumePagesByColumn)".implode($this->stmt_deletepage->errorLog()));

			return $retval;
    }
	}
?>
