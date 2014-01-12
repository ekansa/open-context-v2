<?php


//this class interacts with the database for accessing and changing Predicates
//oc_predicates are defined in different projects
class OCitems_LegacyIDs {
    
	 public $db;
	 
    /*
     General data
    */
    public $oldUUID;
	 public $newUUID;
	 public $type;
    public $updated;
   
	 
   
    //get data from database
    function getByOldUUID($oldUUID){
        
        $oldUUID = $this->security_check($oldUUID);
        $output = false; //not found
        
        $db = $this->startDB();
        
        $sql = 'SELECT *
                FROM oc_legacyids
                WHERE oldUUID = "'.$oldUUID.'"
                LIMIT 1';
		
        $result = $db->fetchAll($sql, 2);
        if($result){
            $output = $result[0];
				$this->oldUUID = $oldUUID;
				$this->newUUID = $result[0]["newUUID"];
				$this->type = $result[0]["type"];
				$this->updated = $result[0]["updated"];
		  }
        return $output;
    }
    
	 
	 //adds an item to the database, returns true if successful
	 function createRecord($data){
		 
		  $db = $this->startDB();
		  $success = false;
		  
		  foreach($data as $key => $value){
				if(is_array($value)){
					 echo print_r($data);
					 die;
				}
		  }
		  
		  try{
				$db->insert("oc_legacyids", $data);
				$success = true;
		  } catch (Exception $e) {
				$success = false;
		  }
		  return $success;
	 }
	 
	 
    function security_check($input){
        $badArray = array("DROP", "SELECT", "#", "--", "DELETE", "INSERT", "UPDATE", "ALTER", "=");
        foreach($badArray as $bad_word){
            if(stristr($input, $bad_word) != false){
                $input = str_ireplace($bad_word, "XXXXXX", $input);
            }
        }
        return $input;
    }
    

    function startDB(){
		  if(!$this->db){
				$db = Zend_Registry::get('db');
				$this->setUTFconnection($db);
				$this->db = $db;
		  }
		  else{
				$db = $this->db;
		  }
		  
		  return $db;
	 }
	 
	 function setUTFconnection($db){
		  $sql = "SET collation_connection = utf8_unicode_ci;";
		  $db->query($sql, 2);
		  $sql = "SET NAMES utf8;";
		  $db->query($sql, 2);
    }
   
    
}
