<?php
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/credentials.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
  
	class SessionTable{
		private $pdo = null;
		
		//static statements
		private $stmt_newsession = null;
		private $stmt_getsession = null;
		private $stmt_deletesession = null;

		//dynamic statements
		private $stmt_updatesession = null;
		
		//used for logging
		const TAG = "[API:session]";

		function __construct(){
			global $dbhost, $dbname, $dbuser, $dbpass;
			$this->pdo = new PDO("mysql:host=".$dbhost.";dbname=".$dbname, $dbuser, $dbpass);		
			
			//prepare static statements
			$this->stmt_newsession = $this->pdo->prepare("insert into session () values (null, :session_id, :session_data, :expire, :is_active)");
			$this->stmt_getsession = $this->pdo->prepare("select * from session where `session_id` = :session_id");
			$this->stmt_getsessiondata = $this->pdo->prepare("select session_data from session where `session_id` = :session_id");
			$this->stmt_deletesession = $this->pdo->prepare("delete from session where `session_id` = :session_id ");
			$this->stmt_gcsession = $this->pdo->prepare("delete from session where `expire` < UNIX_TIMESTAMP()");
		}

		function __destruct(){
		}

		
		/*
		* Insert a new row of session data into the session table.
		* This function requires the id for the new session.
		* Returns the row id (different from session id) on success, false on
		*	failure.
		*
		*/
		function newSession($session_id){
			$retval = false;
			$data = "";
			$expire = time() + get_cfg_var("session.gc_maxlifetime") - 1;
			$active = false;
			$this->stmt_newsession->bindParam(":session_id", $session_id);
			$this->stmt_newsession->bindParam(":session_data", $data);
			$this->stmt_newsession->bindParam(":expire", $expire);
			$this->stmt_newsession->bindParam(":is_active", $active);
			if($this->stmt_newsession->execute())
				$retval = $this->pdo->lastInsertId();
			else
				util_log(self::TAG."(newSession)".implode($this->stmt_newsession->errorInfo()));
			return $retval;
		}

		/*
		* a convenience method that just calls getSession to check if a session
		* exists.
		* returns true if found, false if not found
		*/
		function hasSession($session_id){
			$result = $this->getSession($session_id);
			return ($result) ? true : false;
		}

		/*
		* get a row of session info by its session id. return false on failure
		*/
		function getSession($session_id){
			$retval = false;
			$this->stmt_getsession->bindParam(":session_id", $session_id);
			if($this->stmt_getsession->execute())
				$retval = $this->stmt_getsession->fetch();
			else
				util_log(self::TAG."(getSession)".implode($this->stmt_getsession->errorInfo()));
			
			return $retval;
		}

		/*
		* return the session data associated with a session_id or false
		*/
		function getSessionData($session_id){
			$retval = false;
			$this->stmt_getsessiondata->bindParam("session_id", $session_id);
			if($this->stmt_getsessiondata->execute())
				$retval = $this->stmt_getsessiondata->fetch();
			else
				util_log(self::TAG."(getSessionData)".implode($this->stmt_getsessiondata->errorInfo()));

			return $retval["session_data"];
		}

		/*
		* delete a row from the session table based on the session id (not row id)
		* returns true on success, false on failure
		*/
		function deleteSession($session_id){
			$this->stmt_deletesession->bindParam(":session_id", $session_id);
			
			if(!($retval = $this->stmt_deletesession->execute()))
				util_log(self::TAG."(deleteSession)".implode($this->stmt_deletesession->errorInfo()));

			return $retval;
		}

		/*
		*	delete all expired sessions
		*/
		function garbageCollectSessions(){
			if(!($retval = $this->stmt_gcsession->execute()))
				util_log(self::TAG."(garbageCollectSessions)".implode($this->stmt_gcsession->errorInfo()));
			return $retval;
		}

		/*
		* update fields with the given values in the row that matches session_id
		* return true on success, false on failure
		*/
		function updateSession($session_id, $fields, $sess_data){
			$args = array();
			$retval = false;
			$ps = "update session set ";
			for($i = 0; $i < sizeof($fields); $i++){
				$ps .= $fields[$i]."=:".$fields[$i];
				$args[":".$fields[$i]] = $sess_data[$i];
				$ps .= ($i == sizeof($fields) - 1) ? "" : ",";
			}
			$ps .= " where `session_id` = :session_id";
			$args[":session_id"] = $session_id;
			
			$this->stmt_updatesession = $this->pdo->prepare($ps);
			if(!($retval = $this->stmt_updatesession->execute($args)))
				util_log(self::TAG."(updateSession)".implode($this->stmt_updatesession->errorInfo()));
			
			return $retval;
		}

	}
?>

