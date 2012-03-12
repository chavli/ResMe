<?php
	require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/credentials.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
  
	class UserMetaTable{
		private $pdo = null;

		//static statements 
		private $stmt_newmeta = null;
		
		//dynamic statements
		private $stmt_getmeta = null;
		private $stmt_deletemeta = null;
		private $stmt_updatemeta = null;
		
		//used for logging
		const TAG = "[API:usermeta]";

		function __construct(){
			global $dbhost, $dbname, $dbuser, $dbpass;
			$this->pdo = new PDO("mysql:host=".$dbhost.";dbname=".$dbname, $dbuser, $dbpass);
			
			//prepare static statements
			$this->stmt_newmeta = $this->pdo->prepare("insert into usermeta () values (null, :username, :user_id, :stack_data, :vote_data, :resume_data)");
		}

		function __destruct(){
			$this->pdo = null;
		}

		/* 
		* metadata must and only contain the following elements in the following
		*	order:
		*	[0] - string username: username of the user that owns this metadata
		*	[1] - integer id: id of the user that owns this metadata
		*	[2] - (serialized)mediumblob stack_data: an array that contains the ids of
		*				resumes a user has saved to thier stack
		*	[3] - (serialized)mediumblob vote_data: an array containing ids of
		*				submissions that the user approves
		*	[4] - (serialized)mediumblob resume_data: an array that contains the ids
		*				resumes a user approves of.
		*
		*	returns the id of the new usermeta row on success of false on failure
		*/
		function newUserMeta($metadata){
			$retval = false;
			if(sizeof($metadata) == 5){
				$this->stmt_newmeta->bindParam(":username", $metadata[0]);
				$this->stmt_newmeta->bindParam(":user_id", $metadata[1]);
				$this->stmt_newmeta->bindParam(":stack_data", $metadata[2]);
				$this->stmt_newmeta->bindParam(":vote_data", $metadata[3]);
				$this->stmt_newmeta->bindParam(":resume_data", $metadata[4]);

				if($this->stmt_newmeta->execute())
					$retval = $this->pdo->lastInsertId();	
				else
					util_log(self::TAG."(newUserMeta)".implode($this->stmt_newmeta->errorInfo()));
			}
			return $retval;
		}
		
		/* 
		*	get a usermeta row based on id. returns the row as an array or false on 
		*	failure
		*/
		function getUserMeta($meta_id){
			$results = $this->getUserMetaByColumn("id", $meta_id);
			return (sizeof($results) >= 1) ? $results[0] : false;
		}
		
		/*
		* return, as an array, all usermeta rows where the given value is in the 
		*	given column. or an empty array on failure
		*/
		function getUserMetaByColumn($column, $value){
			$results = array();
			//dynamically create statement
			$ps = "select * from usermeta where `".$column."`= :value";
			$this->stmt_getmeta = $this->pdo->prepare($ps);
			$this->stmt_getmeta->bindParam(":value", $value);
			if($this->stmt_getmeta->execute())
				$results = $this->stmt_getmeta->fetchAll();
			else
				util_log(self::TAG."(getUserMetaByColumn)".implode($this->stmt_getmeta->errorInfo()));

			return $results;
		}

		/*
		*	delete a row from usermeta based on it's id. returns true on success,
		*	false on failure
		*/
		function deleteUserMeta($meta_id){
			return $this->deleteUserMetaByColumn("id", $meta_id);
		}
		
		/*
		*	delete all rows from usermeta with the given value in a given column.
		*	returns true of success, false on failure
		*/
		function deleteUserMetaByColumn($column, $value){
			$ps = "delete from usermeta where `".$column."`= :value";
			$this->stmt_deletemeta = $this->pdo->prepare($ps);
			if(!($retval = $this->stmt_deletemeta->execute()))
				util_log(self::TAG."(deleteUserMetaByColumn)".implode($this->stmt_deletemeta->errorInfo()));
			
			return $retval;
		}

		/*
		* update all the columns specified in fields with their respective data in
		*	meta_data in the row with id = meta_id
		* returns true on success, false on failure
		*/
		function updateUserMeta($meta_id, $fields, $meta_data){
			return $this->updateUserMetaByColumn("id", $meta_id, $fields, $meta_data);
		}

		/*
		* update all the columns specified in fields with their respective data in
		*	meta_data in all rows where column is equal to value
		* returns true on success, false on failure
		*/
		function updateUserMetaByColumn($column, $value, $fields, $meta_data){
			$args = array();
			$ps = "update usermeta set ";
			for($i = 0; $i < sizeof($fields); $i++){
				$ps .= $fields[$i]."=:".$fields[$i];
				$ps .= ($i == sizeof($fields) - 1) ? " " : ", ";
				$args[":".$fields[$i]] = preg_replace("/\"/", "'", $meta_data[$i]);
			}	
			$ps .= "where `".$column."`= :value";
			$args[":value"] = preg_replace("/\"/", "", $value);

			$this->stmt_updatemeta = $this->pdo->prepare($ps);
			if(!($retval = $this->stmt_updatemeta->execute($args)))
				util_log(self::TAG."(updateUserMetaByColumn)".implode($this->stmt_updatemeta->errorInfo()));
			
			return $retval;
		}
	}
?>
