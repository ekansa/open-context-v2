<?php


//this class interacts with the database for accessing and changing Predicates
//oc_predicates are defined in different projects
class OCitems_Chronodata {
    
	 public $db;
	 
    /*
     General item metadata
    */
    public $uuid;
    public $projectUUID;
    public $startLC; //earliest start (no confidence)
	 public $startC; // start with confidence
	 public $endC; //end with confidence
	 public $endLC; //latest end (no confidence)
	 public $note; //note about the chronology
    public $updated;
    
   
   
    //get data from database
    function getByUUID($uuid){
        
        $uuid = $this->security_check($uuid);
        $output = false; //not found
        
        $db = $this->startDB();
        
        $sql = 'SELECT *
                FROM oc_geodata
                WHERE uuid = "'.$uuid.'"
                LIMIT 1';
		
        $result = $db->fetchAll($sql, 2);
        if($result){
            $output = $result[0];
				$this->uuid = $uuid;
				$this->projectUUID = $result[0]["project_id"];
				$this->startLC = $result[0]["startLC"];
				$this->startC = $result[0]["startC"];
				$this->endC = $result[0]["endC"];
				$this->endLC = $result[0]["endLC"];
				$this->note = $result[0]["note"];
				$this->updated = $result[0]["updated"];
				//$this->getItemData($uuid);
		  }
        return $output;
    }
    
	 
	 //adds an item to the database
	 function createItem($data = false){
		 
		  $db = $this->startDB();
		  $success = false;
		  if(!is_array($data)){
				$data = array("uuid" => $this->uuid,
								  "project_id" => $this->projectUUID,
								  "startLC" => $this->startLC,
								  "startC" => $this->startC,
								  "endC" => $this->endC,
								  "endLC" => $this->endLC,
								  "note" => $this->note
								  );	
		  }
		  
		  $data = $this->dataValidate($data);
		  if(is_array($data)){
		  
				try{
					 $db->insert("oc_chronology", $data);
					 $success = true;
				} catch (Exception $e) {
					 $success = false;
				}
		  
		  }
		  return $success;
	 }
	 
	 
	 //a few checks to make sure we're getting good geospatial data
	 function dataValidate($data){
		  if($data["startLC"] <= $data["startC"] && $data["startC"] <= $data["endC"] && $data["endC"] <= $data["endLC"]){
				return $data;
		  }
		  else{
				return false;
		  }
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
