<?php


//this class interacts with the database for accessing and changing Predicates
//oc_predicates are defined in different projects
class OCitems_Property {
    
	 public $db;
	 
    /*
     General data
    */
    public $uuid;
	 public $hashID;
    public $projectUUID;
    public $sourceID;
	 public $predicateUUID;
	 public $rank; //rank of the property, useful for ordinal values
	 public $label;
	 public $note;
    public $updated;
	 
   
    //get data from database
    function getByUUID($uuid){
        
        $uuid = $this->security_check($uuid);
        $output = false; //not found
        
        $db = $this->startDB();
        
        $sql = 'SELECT *
                FROM oc_properties
                WHERE uuid = "'.$uuid.'"
                LIMIT 1';
		
        $result = $db->fetchAll($sql, 2);
        if($result){
            $output = $result[0];
				$this->uuid = $uuid;
				$this->hashID = $result[0]["hashID"];
				$this->projectUUID = $result[0]["project_id"];
				$this->sourceID = $result[0]["source_id"];
				$this->predicateUUID = $result[0]["predicateUUID"];
				$this->rank = $result[0]["rank"];
				$this->label = $result[0]["label"];
				$this->note  = $result[0]["note"];
				$this->updated = $result[0]["updated"];
		  }
        return $output;
    }
    
	 
	 function makeHashID($predicateUUID, $label){
		  $label= trim($label);
		  return sha1($predicateUUID." ".$label);
	 }
	 
	 
	 function getByPropLabel($predicateUUID, $propLabel){
		  
		  $db = $this->startDB();
		  
		  $hashID = $this->makeHashID($predicateUUID, $propLabel);
		  $output = false;
		  $sql = "SELECT * FROM oc_properties WHERE hashID = '$hashID' LIMIT 1; ";
		  
		  $result = $db->fetchAll($sql, 2);
        if($result){
            $output = $result[0];
				$this->uuid = $result[0]["uuid"];
				$this->hashID = $result[0]["hashID"];
				$this->projectUUID = $result[0]["project_id"];
				$this->sourceID = $result[0]["source_id"];
				$this->predicateUUID = $result[0]["predicateUUID"];
				$this->rank = $result[0]["rank"];
				$this->label = $result[0]["label"];
				$this->note  = $result[0]["note"];
				$this->updated = $result[0]["updated"];
				//$this->getItemData($uuid);
		  }
        return $output;
	 }
	 
	 
	 
	 
	 //adds an item to the database, returns its uuid if successful
	 function createRecord($data = false){
		 
		  $db = $this->startDB();
		  $success = false;
		  if(!is_array($data)){
				
				$data = array("uuid" => $this->uuid,
								  "project_id" => $this->projectUUID,
								  "source_id" => $this->sourceID,
								  "predicateUUID" => $this->predicateUUID,
								  "rank" => $this->rank,
								  "label" => $this->label,
								  "note" => $this->note
								  );	
		  }
		  else{
				if(!isset($data["uuid"])){
					 $data["uuid"] = false;
				}
		  }
		  
	 	  if(!$data["uuid"]){
				$ocGenObj = new OCitems_General;
				$data["uuid"] = $ocGenObj->generateUUID();
		  }
		  
		  $data["hashID"] = $this->makeHashID($data["predicateUUID"], $data["label"]);
	 
		  foreach($data as $key => $value){
				if(is_array($value)){
					 echo print_r($data);
					 die;
				}
		  }
	 
		  try{
				$db->insert("oc_properties", $data);
				$success = $data["uuid"];
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
