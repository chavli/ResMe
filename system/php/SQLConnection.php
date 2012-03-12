<?php
	require($_SERVER["DOCUMENT_ROOT"]."/system/php/credentials.php");
	class SQLConnection{
		private $sql_link = null;
		private $cur_db = '';

		#connect to sql server and set current database. returns FALSE on failure
		public function establish(){
			global $dbhost, $dbuser, $dbpass, $dbname;
			$this->sql_link = $this->connect($dbhost, $dbuser, $dbpass);
			if(!$this->sql_link)
				return $this->sql_link;
			
			$retval = $this->setDB($dbname);
			return $retval;
		}

		private function connect($host, $user, $pass){
			return mysql_connect($host, $user, $pass);
		}

		public function setDB($name){
			$this->cur_db = $name;
			return mysql_select_db($name);
		}

		public function disconnect(){
			//if($this->sql_link){ mysql_close($this->sql_link); }
		}

		public function getLink(){
			return $this->sql_link;
		}

		
		public function selectAll($tbl, $where=""){
			return $this->select_query($tbl, array('*'), $where);
		}
	
		//create and send a select query. Returns FALSE on failure.
		public function select_query($tbl, array $tbl_col, $where=""){
			$query = "select ";
				for($i = 0; $i < sizeof($tbl_col); $i += 1){
					$query .= $tbl_col[$i];
					$query .= ($i == sizeof($tbl_col) - 1) ? "" : ",";
				}
			$query .= " from ".$this->cur_db.".".$tbl;

			if($where != ""){
				$query .= " ".$where;
			}
			$query .=";";
			$result = mysql_query($query) or die(mysql_error()); 
			return $result; 
		}

		public function insertAll($tbl, array $col_val){
			return $this->insert_query($tbl, array(""), $col_val);
		}

		//create and send an insert query. Returns FALSE on failure
		public function insert_query($tbl, array $tbl_col, array $col_val){
			if(sizeof($tbl_col) != sizeof($col_val) && $tbl_col[0] != "")
				return false;
			$query = "insert into ".$this->cur_db.".".$tbl." (";
			for($i = 0; $i < sizeof($tbl_col); $i += 1){
				$query .= $tbl_col[$i];
				$query .= ($i == sizeof($tbl_col) - 1) ? "" : ",";
			}
			$query .= ") values (";
			for($i = 0; $i < sizeof($col_val); $i += 1){
				$query .= $col_val[$i];
				$query .= ($i == sizeof($col_val) - 1) ? "" : ",";
			}
			$query .= ");";
			$result = mysql_query($query) or die(mysql_error()); 
			return $result; 
		}
		
		//create and send an update query. Returns FALSE on failure.
		public function update_query($tbl, array $tbl_col, array $col_val, $where=""){
			if(sizeof($tbl_col) != sizeof($col_val))
				return false;
			
			$query = "update ".$this->cur_db.".".$tbl." set ";
			for($i = 0; $i < sizeof($tbl_col); $i += 1){
				$query .= $tbl_col[$i]."=".$col_val[$i];
				$query .= ($i == sizeof($tbl_col) - 1) ? "" : ",";
			}
			
			if($where != ""){
				$query .= " ".$where;
			}

			$query .= ";";
			$result = mysql_query($query) or die(mysql_error()); 
			return $result; 
		}
		
		private function quotify($var){
			return "'".$var."'";
		}
		
	}
	
?>

