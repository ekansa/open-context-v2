<?php


//this class interacts with the database for accessing and changing Predicates
//oc_predicates are defined in different projects
class OCitems_Predicate {
    
	 public $db;
	 
    /*
     General item metadata
    */
    public $uuid;
	 public $uri;
    public $projectUUID;
    public $sourceID;
    public $label;
	 public $created;
    public $updated;
    public $data;
	 
	 /*
	 Predicate specific metadata
	 */
	 public $archaeoMLtype;
	 public $dataType;
	 
	 /*
	 Administrative
	 */
	 public $repo; //repository, used for keeping data in Github 
    public $viewCount;
   
    const itemType = "predicates"; //Open Context itemtype
   
	 
	
   
    //get data from database
    function getByUUID($uuid){
		  
        $ocGenObj = new OCitems_General;
        $uuid = $this->security_check($uuid);
        $output = false; //not found
        
        $db = $this->startDB();
        
        $sql = 'SELECT *
                FROM oc_predicates
                WHERE uuid = "'.$uuid.'"
                LIMIT 1';
		
        $result = $db->fetchAll($sql, 2);
        if($result){
           
				$this->uuid = $uuid;
				$this->projectUUID = $result[0]["projectUUID"];
				$this->sourceID = $result[0]["sourceID"];
				$this->archaeoMLtype = $result[0]["archaeoMLtype"];
				$this->dataType = $result[0]["dataType"];
				$this->label = $result[0]["label"];
				$this->created = $result[0]["created"];
				$this->updated = $result[0]["updated"];
				$this->uri = $ocGenObj->generateItemURI($this->uuid, self::itemType);
				$result[0]["itemType"] = self::itemType;
				$result[0]["uri"] = $this->uri;
				$output = $result[0];
		  }
        return $output;
    }
    
	 
	 function getByLabel($label, $projectUUIDs = false, $archaeoMLtypes = false, $dataTypes = false){
		  
		  $db = $this->startDB();
		  $ocGenObj = new OCitems_General;
		  
		  $conditions = " label = '$label' ";
		  $projConds = $ocGenObj->makeORcondition($projectUUIDs, "projectUUID");
		  if($projConds != false){
				$conditions .= " AND ($projConds) ";
		  }
		  $archConds = $ocGenObj->makeORcondition($archaeoMLtypes, "archaeoMLtype");
		  if($archConds != false){
				$conditions .= " AND ($archConds) ";
		  }
		  $typeConds = $ocGenObj->makeORcondition($dataTypes, "dataType");
		  if($typeConds != false){
				$conditions .= " AND ($typeConds) ";
		  }
		  
		  $output = false;
		  $sql = "SELECT * FROM oc_predicates WHERE $conditions LIMIT 1; ";
		  
		  $result = $db->fetchAll($sql, 2);
        if($result){
            $output = $result[0];
				$this->uuid = $result[0]["uuid"];
				$this->projectUUID = $result[0]["projectUUID"];
				$this->sourceID = $result[0]["sourceID"];
				$this->archaeoMLtype = $result[0]["archaeoMLtype"];
				$this->dataType = $result[0]["dataType"];
				$this->label = $result[0]["label"];
				$this->created = $result[0]["created"];
				$this->updated = $result[0]["updated"];
				//$this->getItemData($uuid);
		  }
        return $output;
	 }
	 
	 
	 function updatePredicateDataType($uuid, $newDataType){
		  $db = $this->startDB();
		  $success = false;
		  
		  if($this->validateDataType($newDataType)){
				$where = "uuid = '$uuid' ";
				$data = array("dataType" => $newDataType);
				$db->update("oc_predicates", $data, $where);
				$success = true;
		  }
		  
		  return $success;
	 }
	 
	 
	 
	 //adds an item to the database
	 function createRecord($data = false){
		 
		  $db = $this->startDB();
		  $success = false;
		  if(!is_array($data)){
				$data = array("uuid" => $this->uuid,
								  "projectUUID" => $this->projectUUID,
								  "sourceID" => $this->sourceID,
								  "archaeoMLtype" => $this->archaeoMLtype,
								  "dataType" => $this->dataType,
								  "label" => $this->label,
								  "created" => date("Y-m-d")
								  );	
		  }
		  
		  foreach($data as $key => $value){
				if(is_array($value)){
					 echo print_r($data);
					 die;
				}
		  }
		  
		  if($this->validateDataType($data["dataType"])){
				try{
					 $db->insert("oc_predicates", $data);
					 $success = true;
				} catch (Exception $e) {
					 $success = false;
				}
		  }
		  
		  return $success;
	 }
	 
	 
	 
	 //validate a data type
	 function validateDataType($dataType){
		  
		  $ocGenObj = new OCitems_General;
		  $dataTypes = $ocGenObj->getDataTypes();
		  
		  if(in_array($dataType, $dataTypes)){
				$valid = true;
		  }
		  else{
				$valid = false;
		  }
		  
		  return $valid;
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
