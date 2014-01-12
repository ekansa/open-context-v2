<?php


//this class interacts with the database for accessing and changing Predicates
//oc_predicates are defined in different projects
class OCitems_DataCache {
    
	 public $db;
	 
    /*
     General data
    */
    public $uuid;
	 public $created;
    public $updated;
    public $compressed;
	 public $content;
	 public $error;
	 const compressionLevel = 9 ; //9 is maxiumum compression, 6 is the PHP default
	
    //get data from database
    function getByUUID($uuid){
        
        $uuid = $this->security_check($uuid);
        $output = false; //not found
        
        $db = $this->startDB();
        
        $sql = 'SELECT *
                FROM oc_datacache
                WHERE uuid = "'.$uuid.'"
                LIMIT 1';
		
        $result = $db->fetchAll($sql, 2);
        if($result){
            $output = $result[0];
				$this->uuid = $uuid;
				$this->created = $result[0]["created"];
				$this->compressed = $result[0]["compressed"];
				$this->content = gzuncompress($this->compressed);
				$result[0]["content"] = $this->content;
				$this->updated = $result[0]["updated"];
				$output = $result[0];
		  }
        return $output;
    }
    
	 //checks to see if an item exists
	 function getItemExists($uuid){
		  $data = false;
		  $db = $this->startDB();
		  
		  $uuid = $this->security_check($uuid);
		  
		  $sql = "SELECT uuid
		  FROM oc_datacache
        WHERE uuid = '".$uuid."'
		  LIMIT 1;
		  ";
		  
		  $result = $db->fetchAll($sql);
		  if($result){
				return true;
		  }
		  else{
				return false;
		  }
	 }
	 
	 //adds an item to the database, returns its uuid if successful
	 function createRecord($data = false){
		 
		  $db = $this->startDB();
		  $success = false;
		  if(!is_array($data)){
				$this->compressed = gzcompress($this->content, self::compressionLevel);
				$this->content = false; //no need to waste memory!
				
				$data = array("uuid" => $this->uuid,
								  "created" => $this->created ,
								  "compressed" => $this->compressed
								  );	
		  }
		  else{
				if(!isset($data["created"])){
					 $data["created"] = date("Y-m-d H:i:s");
				}
				if(!isset($data["compressed"]) && isset($data["content"])){
					 $data["compressed"] = gzcompress($data["content"], self::compressionLevel);
					 unset($data["content"]);
				}
				
		  }
		  
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
				$db->insert("oc_datacache", $data);
				$success = true;
		  } catch (Exception $e) {
				$this->error = (string)$e;
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
