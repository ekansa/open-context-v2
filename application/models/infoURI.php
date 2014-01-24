<?php


/*this class interacts with the database to get some
additional information about URI itentified entities
*/
class infoURI {
    
	 public $db;
	 
    /*
     General data
    */
    public $uri;
	 
	 
	 function lookupURI($possURI){
		  $output = false;
		  $ocGenObj = new OCitems_General;
		  $isURI = false;
		  if(substr($possURI, 0, 7) == "http://" || substr($possURI, 0, 8) == "https://"){
				$isURI = true;
		  }
		  else{
				foreach($ocGenObj->URIabbreviations as $baseKey => $abbrev){
					 $abbrev .= ":";
					 if(strstr($possURI, $abbrev)){
						  $possURI = str_replace($abbrev, $baseKey, $possURI);
						  $isURI = true;
					 }
				}
		  }
		  
		  if($isURI){
				$OCbaseURI = $ocGenObj->getCanonicalBaseURI();
				if(strstr($possURI, $OCbaseURI)){
					 //lookup an Open Context item
					 $output = $this->lookupOCitem($possURI);
				}
				else{
					 //lookup an outside entity
				}
		  }
		  
		  return $output;
	 }
	 
	 
	 
	 function lookupOCitem($uri){
		  $output = false;
		  $ocGenObj = new OCitems_General;
		  $itemType = $ocGenObj->itemTypeFromURI($uri);
		  $uuid = $ocGenObj->itemUUIDfromURI($uri);
		  if($itemType == "predicate"){
				$predicateObj = new OCitems_Predicate;
				$output = $predicateObj->getByUUID($uuid);
		  }
		  elseif($itemType == "property"){
				$propertyObj = new OCitems_Property;
				$output = $propertyObj->getByUUID($uuid);
		  }
		  else{
				$manifestObj = new OCitems_Manifest;
				$output = $manifestObj->getByUUID($uuid);
		  }
		  
		  return $output;
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
