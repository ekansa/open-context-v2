<?php


/*this class stores information annotating Open Context data items with
 *entities from outside vocabularies
*/
class Links_tempDC {
    
	 public $db;
	 
	 
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
				$db->insert("link_dcmetadata", $data);
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
