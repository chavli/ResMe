<?php
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/credentials.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
  
	class CommentTable{
		private $pdo = null;
		private $stmt_newcomment = null;
		private $stmt_getresumecomments = null;

		//dynamically created
		private $stmt_getcomments = null;

		//used for logging
		const TAG = "[API:comment]";

		function __construct(){
			global $dbhost, $dbname, $dbuser, $dbpass;
			$this->pdo = new PDO('mysql:host='.$dbhost.';dbname='.$dbname, $dbuser, $dbpass);

			//prepare static statements
			$this->stmt_newcomment = $this->pdo->prepare("insert into comment () values (null, :dest, :source, :comment, :time, :resume_album)");
			$this->stmt_getresumecomments = $this->pdo->prepare("select * from comment where dest = ? and resume_album = ? order by time");
		}

		function __destruct(){
			$this->pdo = null;
		}

		//insert a new comment and return the id of the new comment or FALSE on failure
    /*comment_data needs to contain the following items in the following order
    *[0] - tinytext dest, username of the recipient
    *[1] - tinytext source, username of the commentor
    *[2] - text comment, the comment
    *[3] - datetime time, when the comment was posted
    *[4] - integer resume_album, id of the resume album this comment belongs to
    */
		public function newComment($comment_data){
			$retval = false;
      if(sizeof($comment_data) == 5){
				//bind variables to new comment statement
				$this->stmt_newcomment->bindParam(":dest", $comment_data[0]);	
				$this->stmt_newcomment->bindParam(":source", $comment_data[1]);	
				$this->stmt_newcomment->bindParam(":comment", $comment_data[2]);	
				$this->stmt_newcomment->bindParam(":time", $comment_data[3]);	
				$this->stmt_newcomment->bindParam(":resume_album", $comment_data[4]);	
				if($this->stmt_newcomment->execute())
					$retval = $this->pdo->lastInsertId();
				else
					util_log(self::TAG."(newComment)".implode($this->stmt_newcomment->errorInfo()));
			}
			return $retval;
		}
		
		//gets the comment row which has an id that matches comment_id, or false
    public function getComment($comment_id){
      $results = $this->getCommentsByColumn("id", $comment_id);
      return (sizeof($results) >= 1) ? $results[0] : false;
    }
 
 		//returns an array of all rows that match the given value in the given column    
		public function getCommentsByColumn($column, $value){
			//general statement: select * from comment where ? = ? order by time
			$ps = "select * from comment where `".$column."` = :value order by time";
			$this->stmt_getcomments = $this->pdo->prepare($ps);
			$this->stmt_getcomments->bindParam(":value", $value);
			
			$results = array();
			if($this->stmt_getcomments->execute())
				$results = $this->stmt_getcomments->fetchAll();
			else
				util_log(self::TAG."(getCommentsByColumn)".implode($this->stmt_getcomments->errorInfo()));
			return $results;	
    }
  
    //return all comments for a resume or an empty array
		public function getResumeComments($username, $resume_id){
			$results = array();
			if($this->stmt_getresumecomments->execute(array($username, $resume_id)))
				$results = $this->stmt_getresumecomments->fetchAll();
			else
				util_log(self::TAG."(getResumeComments)".implode($this->stmt_getresumecomments->errorInfo()));
			return $results;
		}
	}
?>
