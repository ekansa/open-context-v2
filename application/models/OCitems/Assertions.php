<?php


//this class interacts with the database for accessing and changing Predicates
//oc_predicates are defined in different projects
class OCitems_Assertions {
    
	 public $db;
	 
	 
    //get data from database
    function getByUUID($uuid){
        
        $uuid = $this->security_check($uuid);
        $output = false; //not found
        
        $db = $this->startDB();
        
        $sql = 'SELECT *
                FROM oc_assertions
                WHERE uuid = "'.$uuid.'"
					 ORDER BY sort
                ';
		
        $result = $db->fetchAll($sql, 2);
        if($result){
            $output = $result;
		  }
        return $output;
    }
    
	 //generate a hashID for the item
	 function makeHashID($uuid, $obsNum, $predicateUUID, $objectUUID, $dataNum = false, $dataDate = false){
		  return sha1($uuid." ".$obsNum." ".$predicateUUID." ".$objectUUID." ".$dataNum." ".$dataDate);
	 }
	 
	 
	 
	 //adds an item to the database
	 function createRecord($data){
		 
		  $db = $this->startDB();
		  $success = false;
		  if(is_array($data)){
				
				$data["hashID"] =  $this->makeHashID($data["uuid"], $data["obsNum"], $data["predicateUUID"], $data["objectUUID"], $data["dataNum"], $data["dataDate"]);
				$data["created"] = date("Y-m-d H:i:s");
				
				
				foreach($data as $key => $value){
					 if(is_array($value)){
						  echo print_r($data);
						  die;
					 }
				}
				
				
				try{
					 $db->insert("oc_assertions", $data);
					 $success = true;
				} catch (Exception $e) {
					 //echo (string)$e;
					 //die;
					 $success = false;
				}
				
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
