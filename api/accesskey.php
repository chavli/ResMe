<?php
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/credentials.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
	
	class KeyTable{
		private $pdo = null; //prepared data object, for mysql connection
		private $stmt_iskey = null;
		private $stmt_deletekey = null;
		private $stmt_getkey = null;
		private $stmt_updatekey = null;

		//used for logging
		const TAG = "[API:key]";

		function __construct(){
			//prepare connection
			global $dbhost, $dbname, $dbuser, $dbpass;
   		$this->pdo = new PDO('mysql:host='.$dbhost.';dbname='.$dbname, $dbuser, $dbpass);
			
			//prepare statements
			$this->stmt_iskey = $this->pdo->prepare("select * from accesskey where passkey = ?");
			$this->stmt_deletekey = $this->pdo->prepare("delete from accesskey where passkey = ?");
		}

		function __destruct(){
			$this->pdo = null;
		}

		//looks up key and checks if it exists. returns false if it doesnt exist
		public function isKey($key){
			$retval = false;
			if($this->stmt_iskey->execute(array($key)))
				$retval =  $this->stmt_iskey->fetch();
			else
				util_log(self::TAG."(isKey)".implode($this->stmt_iskey->errorInfo()));

			return $retval;
		}
		
		/*
		*	get a row of key data based on it's id. returns false on failure
		*/
		public function getKey($key_id){
			$results = $this->getKeysByColumn("id", $key_id);
			return (sizeof($results) >= 1) ? $results[0] : false; 
		}
		
		/*
		*	returns all key rows which have the given value in the given column, 
		*	or an empty array on failure.
		*/
		public function getKeysByColumn($field, $value){
			$results = array();
			$ps = "select * from accesskey where `".$field."`= :value";	
			$this->stmt_getkey = $this->pdo->prepare($ps);
			$this->stmt_getkey->bindParam(":value", $value);
			if($this->stmt_getkey->execute())
				$results = $this->stmt_getkey->fetchAll();
			else
				util_log(self::TAG."(getKeysByColumn)".implode($this->stmt_getkey->errorInfo()));

			return $results;
		}
		
		/*
		*	update all the columns specified in fields with their respective data in
		*	key_data in the row with id = key_id.
		*	returns true on success, false on failure
		*/
		public function updateKey($key_id, $fields, $key_data){
			return $this->updateKeysByColumn("id", $key_id, $fields, $key_data);
		}

		/*
		*	update all the columns specified in fields with their respective data in
		*	key_data in all rows where column is equal to value,
		*	return true on success, false on failure
		*/
		public function updateKeysByColumn($column, $value, $fields, $key_data){
			$args = array();
			$ps = "update accesskey set ";
			for($i = 0; $i < sizeof($fields); $i++){
				$ps .= $fields[$i]."=:".$fields[$i];
				$ps .= ($i == sizeof($fields) - 1) ? " " : ", ";
				$args[":".$fields[$i]] = $key_data[$i];
			}
			$ps .= "where `".$column."` = :value";	
			$args[":value"] = $value;

			$this->stmt_updatekey = $this->pdo->prepare($ps);
			if(!($retval = $this->stmt_updatekey->execute($args)))
				util_log(self::TAG."(updateKeysByColumn)".implode($this->stmt_updatekey->errorInfo()));
			
			return $retval;
		}

		//removes a key from the table. returns FALSE on failure
		public function deleteKey($key){
			$key_data = $this->getKeysByColumn("passkey", $key);
			$key_data = $key_data[0];

			$retval = false;

			//remove key if it has nore more uses
			if(--$key_data["remaining"] <= 0){
				if(!($retval = $this->stmt_deletekey->execute(array($key))))
					util_log(self::TAG."(deleteKey)".implode($this->stmt_deletekey->errorInfo()));
			}
			else
				$retval = $this->updateKeysByColumn("passkey", $key, array("remaining"), array($key_data["remaining"]));

			return $retval;
		}
	}
?>

