<?php


//this class interacts with the database for accessing and changing Predicates
//oc_predicates are defined in different projects
class OCitems_Manifest {
    
	 public $db;
	 
    /*
     General data
    */
    public $uuid;
    public $projectUUID;
    public $sourceID;
	 public $itemType;
	 public $repo;
	 public $classURI;
	 public $label;
	 public $desPropUUID; //UUID for the descriptive labeling property
	 public $views;
	 public $indexed;
	 public $vcontrol;
	 public $archived;
	 public $published;
	 public $revised;
	 public $recordUpdated;
	 
   
    //get data from database
    function getByUUID($uuid){
        
        $uuid = $this->security_check($uuid);
        $output = false; //not found
        
        $db = $this->startDB();
        
        $sql = 'SELECT *
                FROM oc_manifest
                WHERE uuid = "'.$uuid.'"
                LIMIT 1';
		
        $result = $db->fetchAll($sql, 2);
        if($result){
            $output = $result[0];
				$this->uuid = $uuid;
				$this->projectUUID = $result[0]["project_id"];
				$this->sourceID = $result[0]["source_id"];
				$this->itemType = $result[0]["itemType"];
				$this->repo = $result[0]["repo"];
				$this->classURI = $result[0]["classURI"];
				$this->label = $result[0]["label"];
				$this->desPropUUID = $result[0]["desPropUUID"];
				$this->views = $result[0]["views"];
				$this->indexed = $result[0]["indexed"];
				$this->vcontrol = $result[0]["vcontrol"];
				$this->archived = $result[0]["archived"];
				$this->published = $result[0]["published"];
				$this->revised = $result[0]["revised"];
				$this->recordUpdated = $result[0]["recordUpdated"];
		  }
		  return $output;
    }
    
	 
	 
	 //adds an item to the database, returns its uuid if successful
	 function createRecord($data){
		 
		  $db = $this->startDB();
		  $success = false;
		  
		  if(!isset($data["published"])){
				$data["published"] = date("Y-m-d H:i:s");
		  }
		  
		  $data["revised"] = date("Y-m-d H:i:s");
		  
		  foreach($data as $key => $value){
				if(is_array($value)){
					 echo print_r($data);
					 die;
				}
				if($value == NULL){
					 $data[$key] = false;
				}
		  }
		  
		  try{
				$db->insert("oc_manifest", $data);
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
