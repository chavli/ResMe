<?php
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/credentials.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
  
  class TagTable{
 		private $pdo = null;
		private $stmt_newtag = null;
		
		//dynamically created
		private $stmt_gettags = null;
		private $stmt_deletetag = null;
		
		//used for logging
		const TAG = "[API:tag]";

    function __construct(){
    	global $dbhost, $dbname, $dbuser, $dbpass;
			$this->pdo = new PDO("mysql:host=".$dbhost.";dbname=".$dbname, $dbuser, $dbpass);

			//prepare static statements
			$this->stmt_newtag = $this->pdo->prepare("insert into tag () values (null, :resumepage, :x, :y, :width, :height, :data, :type)");
		}

    function __destruct(){
			$this->pdo = null;
    }
    
    //insert a new tag and return the new id or FALSE on failure
    /*tag_data needs to contain the following items in the following order
    *[_] - this is some strange element automatically placed in the array... maybe an id of some sort
    *[0] - integer resumepage, id of the resumepage this tag belongs to
    *[1] - integer x, upper right corner x position
    *[2] - integer y, upper left corner y position
    *[3] - integer width, width of height in pixels
    *[4] - integer height, height of tag in pixels
    *[5] - blob data, the data the tag holds
    *[6] - integer type, tag type (text, image, video, audio, etc)
    */
    public function newTag($tag_data){
			$retval = false;
      if(sizeof($tag_data) == 8){
				$this->stmt_newtag->bindParam(":resumepage", $tag_data[0]);
				$this->stmt_newtag->bindParam(":x", $tag_data[1]);
				$this->stmt_newtag->bindParam(":y", $tag_data[2]);
				$this->stmt_newtag->bindParam(":width", $tag_data[3]);
				$this->stmt_newtag->bindParam(":height", $tag_data[4]);
				$this->stmt_newtag->bindParam(":data", $tag_data[5]);
				$this->stmt_newtag->bindParam(":type", $tag_data[6]);
				if($this->stmt_newtag->execute())
					$retval = $this->pdo->lastInsertId();
				else
					util_log(self::TAG."(newTag)".implode($this->stmt_newtag->errorInfo()));
			}
			return $retval;
    }
   		
		//get a tag by its ID value. returns false if no tag is found
		public function getTag($tag_id){
			$results = $this->getTagsByColumn("id", $tag_id);
			return (sizeof($results) >= 1) ? $results[0] : false;
		}

    //get all tags that match the given value in the given column, or an empty array
		public function getTagsByColumn($column, $value){
			//general statement: select * from tag where ? = ?
			$ps = "select * from tag where `".$column."` = :value";
			$this->stmt_gettags = $this->pdo->prepare($ps);
			$this->stmt_gettags->bindParam(":value", $value);

			$results = array();
			if($this->stmt_gettags->execute())
				$results = $this->stmt_gettags->fetchAll();
			else
				util_log(self::TAG."(getTagsByColumn)".implode($this->stmt_gettags->errorInfo()));
			return $results;
    }
    
    //delete the tag with the given tag id. returns false on failure
  public function deleteTag($tag_id){
      return $this->deleteTagByColumn("id", $tag_id);
    }
    
    //delete tags that match the given value in the given column, returns false on failure
    public function deleteTagByColumn($column, $value){
			//general statement: delete from tag where ? = ?
			$ps = "delete from tag where `".$column."` = :value";
			$this->stmt_deletetag = $this->pdo->prepare($ps);
			$this->stmt_deletetag->bindParam(":value", $value);

			if(!($retval = $this->stmt_deletetag->execute()))
				util_log(self::TAG."(deleteTagByColumn)".implode($this->stmt_deletetag->errorInfo()));
			
			return $retval;
    }
  }
?>
