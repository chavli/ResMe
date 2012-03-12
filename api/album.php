<?php
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/credentials.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");

	class AlbumTable{
		private $pdo = null; //prepared data object
		private $stmt_newalbum = null;

		//dynamically built
		private $stmt_deletealbum = null;
		private $stmt_getalbum = null;		
		private $stmt_updatealbum = null; 
		
		//used for logging
		const TAG = "[API:album]";

		function __construct(){
			//prepare connection
			global $dbhost, $dbname, $dbuser, $dbpass;
			$this->pdo = new PDO('mysql:host='.$dbhost.';dbname='.$dbname, $dbuser, $dbpass);
			
			//prepare static statements
			$this->stmt_newalbum = $this->pdo->prepare('insert into album () values (null, :owner_id, :length, :title, :isResume, :time)');

		}

		function __destruct(){
			$this->pdo = null;
		}

		//insert a new album and return its id or FALSE on failure
    /*album_data needs to contain the following items in the following order
    *[0] - integer owner_id, id of the user that created this album
    *[1] - integer length, number of pictures in this album
    *[2] - text title, title of the album
    *[3] - tinytext isResume, does the album represent a resume
    *[4] - time time, timestamp of when the album was created
    */
		public function newAlbum($album_data){
			$retval = false;
      if(sizeof($album_data) == 5){
				$this->stmt_newalbum->bindParam(':owner_id', $album_data[0]);
				$this->stmt_newalbum->bindParam(':length', $album_data[1]);
				$this->stmt_newalbum->bindParam(':title', $album_data[2]);
				$this->stmt_newalbum->bindParam(':isResume', $album_data[3]);
				$this->stmt_newalbum->bindParam(':time', $album_data[4]);
				if($this->stmt_newalbum->execute())
					$retval = $this->pdo->lastInsertId();
				else
					util_log(self::TAG."(newAlbum)".implode($this->stmt_newalbum->errorInfo()));
			}
			return $retval;
		}
    
		//update the album that matches the id with album_data. 
		//returns false on failure.
    public function updateAlbum($album_id, $fields, $album_data){
			//dynamically build the prepared statement
			//update album set [new info] where id = album_id
			$args = array();
			$ps = "update album set ";
			for($i = 0; $i < sizeof($fields); $i++){
				$ps .= $fields[$i]."=:".$fields[$i];
				$ps .= ($i == sizeof($fields) - 1) ? "" : ", ";
				$args[":".$fields[$i]] = $album_data[$i];
			}
			$ps .= " where id = :album_id";
			$args[":album_id"] = $album_id;
		
			$this->stmt_updatealbum = $this->pdo->prepare($ps);
			if(!($retval = $this->stmt_updatealbum->execute($args)))
				util_log(self::TAG."(updateAlbum)".implode($this->stmt_updatealbum->errorInfo()));
			return $retval;
    }
  
		//returns all data related to an album or FALSE on failure
		public function getAlbum($album_id){
      $results = $this->getAlbumsByColumn("id", $album_id);
      return (sizeof($results) >= 1) ? $results[0] : false;
    }  
   
	 	//returns all the rows that match the given value for the given column
		//or an empty array.
    public function getAlbumsByColumn($column, $value){
			//general statement: select * from album where ? = ?;
			$ps = "select * from album where `".$column."` = :value";
			$this->stmt_getalbum = $this->pdo->prepare($ps);
			$this->stmt_getalbum->bindParam(":value", $value);

      $results = array();
			if($this->stmt_getalbum->execute())
				$results = $this->stmt_getalbum->fetchAll();
			else
				util_log(self::TAG."(getAlbumsByColumn)".implode($this->stmt_getalbum->errorInfo()));

			return $results;	
    }
    
		//deletes an album by id, returns false on failure
		public function deleteAlbum($album_id){
      return $this->deleteAlbumsByColumn("id", $album_id);
		}
    
		//deletes album(s) that match the given value in the given column
    public function deleteAlbumsByColumn($column, $value){
			//general statement: delete from album where ? = ?;
			$ps = "delete from album where `".$column."` = :value";
			$this->stmt_deletealbum = $this->pdo->prepare($ps);
			$this->stmt_deletealbum->bindParam(":value", $value);

			if(!($retval = $this->stmt_deletealbum->execute()))
				util_log(self::TAG."(deleteAlbumsByColumn)".implode($this->stmt_deletealbum->errorInfo()));
			return $retval;
    }
	}
?>
