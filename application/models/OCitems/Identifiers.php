<?php


//this class is used to manage stable identifiers for items
class OCitems_Identifiers {
    
	 public $db;
	 
	 public $IDtypeBaseURIs = array("doi" => "http://dx.doi.org/",
											  "ark" => "http://n2t.net/ark:/",
											  "orcid" => "http://orcid.org/"
											  );
   
	 public $IDtypePredicates = array("doi" => "http://www.w3.org/2002/07/owl#sameAs",
											  "ark" => "http://www.w3.org/2002/07/owl#sameAs",
											  "orcid" => "http://xmlns.com/foaf/0.1/isPrimaryTopicOf"
											  );
	
	
   //get data from database
    function getByUUID($uuid){
        
        $uuid = $this->security_check($uuid);
        $output = false; //not found
        
        $db = $this->startDB();
        
        $sql = 'SELECT *
                FROM oc_identifiers
                WHERE uuid = "'.$uuid.'"
					 ORDER BY stableType
                ';
		
        $result = $db->fetchAll($sql, 2);
        if($result){
				$output = $result;
		  }
        return $output;
    }
	 
	 
	 //get stable identifiers as linked data easy to use in JSON
	 function getStableLinksByUUID($uuid){
		  $output = false;
		  $rawResults = $this->getByUUID($uuid);
		  if(is_array($rawResults)){
				$output = array();
				foreach($rawResults as $row){
					 $output = $this->stableIDlinks($row["stableID"], $row["stableType"], $output);
				}
		  }
		  
		  return $output;  
	 }
	 
	 
	 //generates linked data with the appropriate predicate and full URI for a stable identifier
	 function stableIDlinks($stableID, $stableType, $outputArray = array()){
		  
		  $IDtypeBaseURIs = $this->IDtypeBaseURIs;
		  $IDtypePredicates = $this->IDtypePredicates;
		  
		  if(array_key_exists($stableType, $IDtypePredicates)){
				$predicate = $IDtypePredicates[$stableType];
				if(array_key_exists($stableType, $IDtypeBaseURIs)){
					 $uri =  $IDtypeBaseURIs[$stableType].$stableID;
					 $outputArray[$predicate][] = array("id" => $uri);
				}
		  }
		  
		  return $outputArray;
	 }
	 
    

	 //adds an item to the database, returns its uuid if successful
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
				$db->insert("oc_identifiers", $data);
				$success = $data["uuid"];
		  } catch (Exception $e) {
				
				//echo print_r($e);
				//die;
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
