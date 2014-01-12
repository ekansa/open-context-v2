<?php


//this class interacts with the database for accessing and changing Predicates
//oc_predicates are defined in different projects
class OCitems_Geodata {
    
	 public $db;
	 
    /*
     General item metadata
    */
    public $uuid;
    public $projectUUID;
    public $path;
	 public $featureType;
    public $lat;
	 public $lon;
	 public $geoJSON;
	 public $geoObj;
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
				$this->path = $result[0]["path"];
				$this->featureType = $result[0]["ftype"];
				$this->lat = $result[0]["lat"];
				$this->lon = $result[0]["lon"];
				
				if(strlen($result[0]["geoJSON"])>0){
					 $geoObj = Zend_Json::decode($result[0]["geoJSON"]);
					 if(is_array($geoObj)){
						  $this->geoJSON = $result[0]["geoJSON"];
						  $this->geoObj = $geoObj;
					 }
				}
				
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
								  "path" => $this->path,
								  "ftype" => $this->featureType,
								  "lat" => $this->lat,
								  "lon" => $this->lon,
								  "geoJSON" => $this->geoJSON
								  );	
		  }
		  
		  $data = $this->dataValidate($data);
		  if(is_array($data)){
		  
				try{
					 $db->insert("oc_geodata", $data);
					 $success = true;
				} catch (Exception $e) {
					 $success = false;
				}
		  
		  }
		  return $success;
	 }
	 
	 
	 //a few checks to make sure we're getting good geospatial data
	 function dataValidate($data){
		  
		  if(isset($data["geoJSON"])){
				if(strlen($data["geoJSON"])<1){
					 $data["ftype"] = "point";
				}
				else{
					 $geoObj = Zend_Json::decode($data["geoJSON"]);
					 if(!is_array($geoObj)){
						  $data["ftype"] = "point";
					 }
					 
				}
		  }
		  else{
				$data["ftype"] = "point";
		  }
		  
		  return $data;
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
