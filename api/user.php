<?php
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/credentials.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
  
	class UserTable{
		private $pdo = null;
		private $stmt_newuser  = null;
		private $stmt_getcurrentresume  = null;
		private $stmt_getuserresumes = null;
		private $stmt_searchusers  = null;

		//dynamically created
		private $stmt_getusers  = null;
		private $stmt_getuserfield  = null;
		private $stmt_updateuser  = null;

		//used for logging
		const TAG = "[API:user]";

		function __construct(){
			global $dbhost, $dbname, $dbuser, $dbpass;
			$this->pdo = new PDO("mysql:host=".$dbhost.";dbname=".$dbname, $dbuser, $dbpass);

			//prepare static statements
			$this->stmt_newuser = $this->pdo->prepare("insert into user () values (null,".
				" :username, :password, :email, :firstname, :lastname, :profile_album,".
				" :current_profile_picture, :resume_album, :resumepage, :mainphone,".
				" :cellphone, :officephone, :profs, :perms)"
			);
			$this->stmt_getcurrentresume  = $this->pdo->prepare("select firstname, lastname,".
				" username, resume_album, perms, length, title from user join album on".
				" user.resume_album = album.id where user.username= ?"
			);
			$this->stmt_getuserresumes  = $this->pdo->prepare("select * from (select id,".
				" length from album where owner_id = :id and isResume) as `right` join (".
				"select * from resume where owner_id = :id) as `left` on right.id = left.album_id".
				" order by created desc"
			);
			$this->stmt_searchusers  = $this->pdo->prepare("select * from (select username,".
				" firstname, lastname, resume_album, profile_album, current_profile_picture from user where profs & :pmask > 0 and".
				" perms & :ressch = :ressch) as A join (select * from resume where type &".
				" :tmask > 0) as B where A.resume_album = B.album_id"
			);
		}

		function __destruct(){
			$this->pdo = null;
		}
		
		//insert a new user and return its id or FALSE on failure
    /*user_data needs to contain the following items in the following order
    *[0] - text username, user's username
    *[1] - text password, user's password (encrypted)
    *[2] - text email, user's email
    *[3] - text firstname, user's firstname
    *[4] - text lastname, user's lastname
    *[5] - integer profile_album, id of the album that holds the user's profile pictures
    *[6] - text current_profile_picture, path of the current image
    *[7] - integer resume_album, id of the album that holds the images of the current resume
    *[8] - integer resumepage, id of the current resume page
    *[9] - text mainphone, user's main contact number
    *[10] - text cellphone, user's cellphone number
    *[11] - text officephone, user's business phone
    *[12] - integer profs, a number representing the professions the user is in
    *[13] - interger perms, a number representing a user's permission settings
    *
		*				returns the id of the new user row on success or false on failure
		*/
	 	public function newUser($user_data){
			$retval = false;
			if(sizeof($user_data) == 14){
				$this->stmt_newuser->bindParam(":username", $user_data[0]);
				$this->stmt_newuser->bindParam(":password", $user_data[1]);
				$this->stmt_newuser->bindParam(":email", $user_data[2]);
				$this->stmt_newuser->bindParam(":firstname", $user_data[3]);
				$this->stmt_newuser->bindParam(":lastname", $user_data[4]);
				$this->stmt_newuser->bindParam(":profile_album", $user_data[5]);
				$this->stmt_newuser->bindParam(":current_profile_picture", $user_data[6]);
				$this->stmt_newuser->bindParam(":resume_album", $user_data[7]);
				$this->stmt_newuser->bindParam(":resumepage", $user_data[8]);
				$this->stmt_newuser->bindParam(":mainphone", $user_data[9]);
				$this->stmt_newuser->bindParam(":cellphone", $user_data[10]);
				$this->stmt_newuser->bindParam(":officephone", $user_data[11]);
				$this->stmt_newuser->bindParam(":profs", $user_data[12]);
				$this->stmt_newuser->bindParam(":perms", $user_data[13]);
				if($this->stmt_newuser->execute())
					$retval = $this->pdo->lastInsertId();
				else
					util_log(self::TAG."(newUser)".implode($this->stmt_newuser->errorInfo()));
			}
			return $retval;
		}

		//updates a user's permissions, returns FALSE if the update failed.
		public function updateUserPermissions($username, $perms){
			return $this->updateUser($username, array("perms"), array($perms));
		}

		//updates a user's info with new info, returns FALSE if the update failed
		public function updateUser($username, $fields, $user_data){
			//dynamically prepare statement
			//general statement: update user set :values where username = :name;
			$args = array();
			$ps = "update user set ";
			for($i = 0; $i < sizeof($fields); $i++){
				$ps .= $fields[$i]."=:".$fields[$i];
				$args[":".$fields[$i]] = $user_data[$i];
				$ps .= ($i == sizeof($fields) - 1) ? "" : ", ";
			}
			$ps .= " where username = :name";
			$args[":name"] = $username;
			
			$this->stmt_updateuser = $this->pdo->prepare($ps);
			if(!($retval = $this->stmt_updateuser->execute($args)))
				util_log(self::TAG."(updateUser)".implode($this->stmt_updateuser->errorInfo()));
			
			return $retval;
		}
	
		//returns user permissions in an array or FALSE on failure
		public function getUserPermissions($username){
      return $this->getUserField($username, "perms");
		}

    //return the user's password hash or false on failure
		public function getUserHash($username){
      return $this->getUserField($username, "password");
		}
    
    //return the album_id of the user's profile picture album or false on failure
    public function getUserProfileAlbumId($username){
      return $this->getUserField($username, "profile_album");
    }
    
    //return the column value for a given user or false
    public function getUserField($username, $field){
			//general statement: select :column from user where username = :name
			$ps = "select `".$field."` from user where username = :name";
			$this->stmt_getuserfield = $this->pdo->prepare($ps);
			$this->stmt_getuserfield->bindParam(":name", $username);

			$retval = false;
			if($this->stmt_getuserfield->execute()){
				$retval = $this->stmt_getuserfield->fetch();
				$retval = $retval[$field];
			}
			else
				util_log(self::TAG."(getUserField)".implode($this->stmt_getuserfield->errorInfo()));
			
			return $retval;
    }
    
		//returns the info about the given user's current resume, as an array, or an
		//empty array on failure
		public function getUserCurrentResume($username){
			$results = array();
			if($this->stmt_getcurrentresume->execute(array($username)))
				$results = $this->stmt_getcurrentresume->fetch();
			else
				util_log(self::TAG."(getUserCurrentResume)".implode($this->stmt_getcurrentresume->errorInfo()));

			return $results;
		}
	
		//returns all data related to a user or FALSE on failure
		public function getUser($user_id){
      $user = $this->getUsersByColumn("id", $user_id);
      return (sizeof($user) >= 1) ? $user[0] : false;
    }
    
    //return all user rows that match the given value in the given column.
		//returns an empty array if there are no matches
    public function getUsersByColumn($column, $value){
			//general statement: select * from user where ? = ?
			$ps = "select * from user where `".$column."` = :value";
			$this->stmt_getusers = $this->pdo->prepare($ps);
			$this->stmt_getusers->bindParam(":value", $value);

			$results = array();
     	if($this->stmt_getusers->execute())
				$results = $this->stmt_getusers->fetchAll();
			else
				util_log(self::TAG."(getUsersByColumn)".implode($this->stmt_getusers->errorInfo()));

			return $results;
    }

    //return all resumes for a given user. return an empty array on failure
		public function getUserResumes($user_id){
			$results = array();
			$this->stmt_getuserresumes->bindParam(":id", $user_id);
			$this->stmt_getuserresumes->bindParam(":id", $user_id);
			if($this->stmt_getuserresumes->execute())
				$results = $this->stmt_getuserresumes->fetchAll();
			else
				util_log(self::TAG."(getUserResumes)".implode($this->stmt_getuserresumes->errorInfo()));

			return $results;
		}
    
    //return all users that are in the specified professions and resume type or an empty array
		public function getUsersByProfAndType($prof_mask, $type_mask){
  		require("../system/php/bitwise.php");
      $results = array();
			$this->stmt_searchusers->bindParam(":pmask", $prof_mask);
			$this->stmt_searchusers->bindParam(":tmask", $type_mask);
			$this->stmt_searchusers->bindParam(":ressch", $bm_issearchable);
			if($this->stmt_searchusers->execute())
				$results = $this->stmt_searchusers->fetchAll();
			else
				util_log(self::TAG."(getUsersByProfAndType)".implode($this->stmt_searchusers->errorInfo()));

			return $results;
		}
	}
?>
