<?php


//this class interacts with the database for accessing and changing Predicates
//oc_predicates are defined in different projects
class OCitems_Type {
    
	 public $db;
	 
    /*
     General data
    */
    public $uuid;
	 public $uri;
	 public $hashID;
    public $projectUUID;
    public $sourceID;
	 public $predicateUUID;
	 public $rank; //rank of the property, useful for ordinal values
	 public $label;
	 public $note;
    public $updated;
	 
	 const itemType = "type"; //open context itemtype
   
    //get data from database
    function getByUUID($uuid){
        
		  $ocGenObj = new OCitems_General;
        $uuid = $this->security_check($uuid);
        $output = false; //not found
        
        $db = $this->startDB();
        
        $sql = 'SELECT *
                FROM oc_types
                WHERE uuid = "'.$uuid.'"
                LIMIT 1';
		
        $result = $db->fetchAll($sql, 2);
        if($result){
            
				$this->uuid = $uuid;
				$this->hashID = $result[0]["hashID"];
				$this->projectUUID = $result[0]["projectUUID"];
				$this->sourceID = $result[0]["sourceID"];
				$this->predicateUUID = $result[0]["predicateUUID"];
				$this->rank = $result[0]["rank"];
				$this->label = $result[0]["label"];
				$this->note  = $result[0]["note"];
				$this->updated = $result[0]["updated"];
				$this->uri = $ocGenObj->generateItemURI($this->uuid, self::itemType);
				$result[0]["itemType"] = self::itemType;
				$result[0]["uri"] = $this->uri;
				$output = $result[0];
		  }
        return $output;
    }
    
	 //get the properties for an item
	 function getByPredicateUUID($predicateUUID, $requestParams = false){
		  
		  $ocGenObj = new OCitems_General;
        $predicateUUID = $this->security_check($predicateUUID);
        $output = false; //not found
        
        $db = $this->startDB();
		  
		  $getAnnotations = false;
		  $labelTerm = "";
        if(is_array($requestParams)){
				$label = $ocGenObj->checkExistsNonBlank("q", $requestParams);
				if($label != false){
					 $labelTerm = " AND (label LIKE '%".addslashes($label)."%') ";
				}
				$gAnnot = $ocGenObj->checkExistsNonBlank("getAnnotations", $requestParams);
				if($gAnnot != false){
					 $getAnnotations = true; 
				}
		  }
		  
		  
        $sql = 'SELECT *
                FROM oc_types
                WHERE predicateUUID = "'.$predicateUUID.'"
					 '.$labelTerm.'
					 ORDER BY rank, label
					 ';
		
        $result = $db->fetchAll($sql, 2);
        if($result){
            $output = array();
				
				foreach($result as $row){
					 $row["itemType"] = self::itemType;
					 $row["uri"] = $ocGenObj->generateItemURI($row["uuid"], self::itemType);
					 if($getAnnotations){
						  $linkAnnotObj = new Links_linkAnnotation;
						  $row["annotations"] = $linkAnnotObj->getAnnotationsByUUID($row["uuid"]);
						  unset($linkAnnotObj);
					 }
					 $output[] = $row;
				}
		  }
        return $output;
	 }
	 
	 
	 
	 function makeHashID($predicateUUID, $label){
		  $label= trim($label);
		  return sha1($predicateUUID." ".$label);
	 }
	 
	 
	 function getByLabel($predicateUUID, $typeLabel){
		  
		  $db = $this->startDB();
		  
		  $hashID = $this->makeHashID($predicateUUID, $typeLabel);
		  $output = false;
		  $sql = "SELECT * FROM oc_types WHERE hashID = '$hashID' LIMIT 1; ";
		  
		  $result = $db->fetchAll($sql, 2);
        if($result){
            $output = $result[0];
				$this->uuid = $result[0]["uuid"];
				$this->hashID = $result[0]["hashID"];
				$this->projectUUID = $result[0]["projectUUID"];
				$this->sourceID = $result[0]["sourceID"];
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
								  "projectUUID" => $this->projectUUID,
								  "sourceID" => $this->sourceID,
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
				$db->insert("oc_types", $data);
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
