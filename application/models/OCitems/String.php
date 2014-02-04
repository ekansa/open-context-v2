<?php


//this class interacts with the database for accessing and changing Predicates
//oc_predicates are defined in different projects
class OCitems_String {
    
	 public $db;
	 
    /*
     General data
    */
    public $uuid;
	 public $hashID;
    public $projectUUID;
    public $sourceID;
    public $updated;
    public $content;
	 
   
    //get data from database
    function getByUUID($uuid){
        
        $uuid = $this->security_check($uuid);
        $output = false; //not found
        
        $db = $this->startDB();
        
        $sql = 'SELECT *
                FROM oc_strings
                WHERE uuid = "'.$uuid.'"
                LIMIT 1';
		
        $result = $db->fetchAll($sql, 2);
        if($result){
            $output = $result[0];
				$this->uuid = $uuid;
				$this->hashID = $result[0]["hashID"];
				$this->projectUUID = $result[0]["projectUUID"];
				$this->sourceID = $result[0]["sourceID"];
				$this->content = $result[0]["content"];
				$this->updated = $result[0]["updated"];
		  }
        return $output;
    }
    
	 
	 function updateHashIDs(){
		  
		  //a maintenance query to keep hashIDs in synch with the content
		  $db = $this->startDB();
		  
		  $sql = 'UPDATE oc_strings SET hashID = CAST(SHA1(CONCAT(CAST(projectUUID AS CHAR CHARACTER SET utf8), "_", content)) AS CHAR CHARACTER SET ascii)
		  WHERE hashID != CAST(SHA1(CONCAT(CAST(projectUUID AS CHAR CHARACTER SET utf8), "_", content)) AS CHAR CHARACTER SET ascii) ;';
		  
		  $db->query($sql);
	 }
	 
	 
	 
	 function makeHashID($content, $projectUUID){
		  
		  $content = trim($content);
		  return sha1($projectUUID."_".$content);
	 }
	 
	 
	 
	 function getByContent($content, $projectUUIDs){
		  
		  $db = $this->startDB();
		  $ocGenObj = new OCitems_General;
		  
		  if(is_array($projectUUIDs)){
				$hashArray = array();
				foreach($projectUUIDs as $projectID){
					 $hashArray[] = $this->makeHashID($content, $projectID);
				}
		  }
		  else{
				$hashArray = array();
				$hashArray[] = $this->makeHashID($content, $projectUUIDs);
		  }
		  
		  $hashConds = $ocGenObj->makeORcondition($hashArray, "hashID");
		  if($hashConds != false){
				$conditions = "($hashConds) ";
		  }
		  $projConds = $ocGenObj->makeORcondition($projectUUIDs, "projectUUID");
		  if($projConds != false){
				$conditions .= " AND ($projConds) ";
		  }
		  
		  $output = false;
		  $sql = "SELECT * FROM oc_strings WHERE $conditions LIMIT 1; ";
		  
		  $result = $db->fetchAll($sql, 2);
        if($result){
            $output = $result[0];
				$this->uuid = $result[0]["uuid"];
				$this->hashID = $result[0]["hashID"];
				$this->projectUUID = $result[0]["projectUUID"];
				$this->sourceID = $result[0]["sourceID"];
				$this->projectUUID = $result[0]["projectUUID"];
				$this->sourceID = $result[0]["sourceID"];
				$this->content = $result[0]["content"];
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
								  "projectUUID" => $this->projectUUID,
								  "sourceID" => $this->sourceID,
								  "content" => $this->content
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
		  
		  $data["hashID"] = $this->makeHashID($data["content"], $data["projectUUID"]);
	 
		  foreach($data as $key => $value){
				if(is_array($value)){
					 echo print_r($data);
					 die;
				}
		  }
	 
	 
		  try{
				$db->insert("oc_strings", $data);
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
