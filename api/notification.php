<?php
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/credentials.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");

	class NotificationTable{
		private $pdo = null;
		private $stmt_newnotification = null;

		//dynamically created
		private $stmt_getnotifications = null;
		
		//used for logging
		const TAG = "[API:notification]";

		function __construct(){
			global $dbhost, $dbname, $dbuser, $dbpass;
			$this->pdo = new PDO("mysql:host=".$dbhost.";dbname=".$dbname, $dbuser, $dbpass);

			//prepare static statements
			$this->stmt_newnotification = $this->pdo->prepare("insert into notification () values (null, :data, :created, :type, :from, :to, :deleteonread, :deleteonexpire)");

		}

		function __destruct(){
			$this->pdo = null;
		}
    
		//insert a new notification and return the new id or FALSE on failure
    /*comment_data needs to contain the following items in the following order
    *[0] - blob data, the notification (text usually)
    *[1] - datetime created, timestamp of when the notification was created
    *[2] - integer type, notification type
    *[3] - text from, username of sender
    *[4] - text to, username of receiver
    *[5] - tinyint(1) deleteonread, if 1 delete notification on read
    *[6] - tinyint(1) deleteonexpire, if 1 delete notification after a set amount of time
    */
		public function newNotification($notification_data){
			$retval = false;
      if(sizeof($notification_data) == 7){
				$this->stmt_newnotification->bindParam(":data", $notification_data[0]);
				$this->stmt_newnotification->bindParam(":created", $notification_data[1]);
				$this->stmt_newnotification->bindParam(":type", $notification_data[2]);
				$this->stmt_newnotification->bindParam(":from", $notification_data[3]);
				$this->stmt_newnotification->bindParam(":to", $notification_data[4]);
				$this->stmt_newnotification->bindParam(":deleteonread", $notification_data[5]);
				$this->stmt_newnotification->bindParam(":deleteonexpire", $notification_data[6]);
				if($this->stmt_newnotification->execute())
					$retval = $this->pdo->lastInsertId();
				else
					util_log(self::TAG."(newNotification)".implode($this->stmt_newnotification->errorInfo()));
			}
			return $retval;
		}
	
    //get a notification based on id. returns false if id doesn't match anything
		public function getNotification($notification_id){
      $results = $this->getNotificationsByColumn("id", $notification_id);
      return (sizeof($results) >= 1) ? $results[0] : false;
    }
  
    //return all notifications for that have the given value in the given column. return an empty array otherwise
		public function getNotificationsByColumn($column, $value){
			//general statement: select * from notification where ? = ? order by created desc
			$ps = "select * from notification where `".$column."` = :value order by created desc";
			$this->stmt_getnotifications = $this->pdo->prepare($ps); 
			$this->stmt_getnotifications->bindParam(":value", $value);

			$results = array();
			if($this->stmt_getnotifications->execute())
				$results = $this->stmt_getnotifications->fetchAll();
			else
				util_log(self::TAG."(getNotificationsByColumn)".implode($this->stmt_getnotifications->errorInfo()));
			return $results;
		}
	}
?>
