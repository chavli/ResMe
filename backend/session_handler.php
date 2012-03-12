<?php
	require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/session.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/api/usermeta.php");

	class SessionHandler{
		private $session_table = null;

		function __construct(){
			//set session callback functions to custom functions
			session_set_save_handler(
				array($this, "sess_open"),
				array($this, "sess_close"),
				array($this, "sess_read"),
				array($this, "sess_write"),
				array($this, "sess_destroy"),
				array($this, "sess_gc")
			);

			$this->session_table = new SessionTable();
		}
		
		function __destruct(){
			//do something later maybe
		}
   
    //called by session_start()
    function sess_open($path, $name){
      //util_log("#sess_open: $path $name");
      return true;
    }
    
    //gets called when a script reaches the end
    function sess_close(){
      //util_log("#sess_close");
      return true;
    }

    //called right after session_start();
    function sess_read($sess_id){
      //util_log("#sess_read: $sess_id");
      if(!$this->session_table->hasSession($sess_id))
        $this->session_table->newSession($sess_id);
      
      //update the timestamp
      $expire = time() + get_cfg_var("session.gc_maxlifetime") - 1;
      $this->session_table->updateSession($sess_id, array("expire"), array($expire));
      $data = $this->session_table->getSessionData($sess_id);
      $data = preg_replace("/'/", "\"", $data);
			
			return $data;	
			//return $this->session_table->getSessionData($sess_id);
    }
    
    //called at the end of a page when session data needs to be written back
    function sess_write($sess_id, $sess_data){
      //util_log("#sess_write: $sess_id $sess_data");
      $retval = false;
      if($this->session_table->hasSession($sess_id)){
        $expire = time() + get_cfg_var("session.gc_maxlifetime") - 1;
        $retval = $this->session_table->updateSession($sess_id, array("session_data", "expire"), array($sess_data, $expire));
      }

      return $retval;
    }

    //called by session_destroy();
    function sess_destroy($sess_id){
      //util_log("#sess_destroy: $sess_id");

			//write back a users metadata to the usermeta table
      $sess_data = $this->session_table->getSessionData($sess_id);

			if($sess_data){
				//convert from string to array
				$sess_data = util_flatstr_to_array($sess_data); 
				$meta_tbl = new UserMetaTable();
				$fields = array("stack_data", "vote_data", "resume_data");
				$values = array($sess_data["stack"], $sess_data["votes"], $sess_data["resume_likes"]);
				$meta_tbl->updateUserMetaByColumn("user_id", $sess_data["id"], $fields, $values);
			}
			
			return $this->session_table->deleteSession($sess_id);
    }

    function sess_gc($value){
      //util_log("#sess_gc: $value");
      return $this->session_table->garbageCollectSessions();
    }
	}
?>
