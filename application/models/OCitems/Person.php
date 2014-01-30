<?php


//this class is used to manage media files
class OCitems_Person {
    
	 public $db;
	 
    /*
     General data
    */
    public $uuid;
    public $projectUUID;
    public $sourceID;
	 public $foafType; //friend of a friend type
	 public $combinedName;
	 public $givenName;
	 public $surname;
	 public $midNames;
	 public $midInit;
	 public $initials;
    public $updated;
	 
   
    //get data from database
    function getByUUID($uuid){
        
        $uuid = $this->security_check($uuid);
        $output = false; //not found
        
        $db = $this->startDB();
        
        $sql = 'SELECT *
                FROM oc_persons
                WHERE uuid = "'.$uuid.'"
                LIMIT 1';
		
        $result = $db->fetchAll($sql, 2);
        if($result){
            $output = $result[0];
				$this->uuid = $uuid;
				$this->projectUUID = $result[0]["project_id"];
				$this->sourceID = $result[0]["source_id"];
				$this->foafType = $result[0]["foafType"];
				$this->combinedName = $result[0]["combined_name"];
				$this->givenName = $result[0]["given_name"];
				$this->surname = $result[0]["surname"];
				$this->midNames = $result[0]["mid_names"];
				$this->midInit = $result[0]["mid_init"];
				$this->initials = $result[0]["initials"];
				$this->updated = $result[0]["updated"];
		  }
        return $output;
    } 
    

	 //adds an item to the database, returns its uuid if successful
	 function createRecord($data = false){
		 
		  $db = $this->startDB();
		  $success = false;
		  if(!is_array($data)){
				
				$data = array(	"uuid" => $this->uuid,
									 "project_id" => $this->projectUUID,
									 "source_id" => $this->sourceID,
									 "foafType" => $this->foafType,
									 "combined_name" => $this->combinedName,
									 "given_name" => $this->givenName,
									 "surname" => $this->surname,
									 "mid_names" => $this->midNames,
									 "mid_init" => $this->midInit,
									 "initials" => $this->initials
								  );	
		  }
		  
		  
		  foreach($data as $key => $value){
				if(is_array($value)){
					 echo print_r($data);
					 die;
				}
				elseif($value == NULL){
					 $data[$key] = "";
				}
		  }
	 
	 
		  try{
				$db->insert("oc_persons", $data);
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
