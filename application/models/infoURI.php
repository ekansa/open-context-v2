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
	 
	 
	 
	 function checkIsURI($possURI){
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
		  
		  if(!$isURI){
				$possURI = false;
		  }
		  
		  return $possURI;
	 }
	 
	 //get the type of item for a URI identified entity 
	 function checkEntityType($possURI, $checkIfURI = true){
		  $entityType = false;
		  if($checkIfURI){
				$possURI = $this->checkIsURI($possURI);
		  }
		  if($possURI != false){
				$ocGenObj = new OCitems_General;
				$OCbaseURI = $ocGenObj->getCanonicalBaseURI();
				if(strstr($possURI, $OCbaseURI) && !strstr($possURI, $OCbaseURI."vocabularies") ){
					 //lookup an Open Context item
					 $entityType = "opencontext"; //an open context entity
				}
				else{
					 $entityType = "linked"; // a linked entity from another vocabulary
				}
		  }
		  return $entityType;
	 }
	 
	 //get information about en entity
	 function lookupURI($possURI){
		  
		  $entityType = $this->checkEntityType($possURI);
		  if($entityType == "opencontext"){
				$output = $this->lookupOCitemByURI($possURI);
		  }	
		  elseif($entityType == "linked"){
				//lookup an outside entity
				$linkEntityObj = new Links_linkEntity;
				$output = $linkEntityObj->getByURI($possURI);	
		  }
		  else{
				$output = false;
		  }
		  
		  return $output;
	 }
	 
	 
	 
	 function lookupOCitemByURI($uri){
		  $output = false;
		  $ocGenObj = new OCitems_General;
		  $itemType = $ocGenObj->itemTypeFromURI($uri);
		  $uuid = $ocGenObj->itemUUIDfromURI($uri);
		  $output = $this->lookupOCitem($uuid, $itemType);
		  
		  return $output;
	 }
	 
	 
	 function lookupOCitem($uuid, $itemType){
		  $output = false;
		  
		  if($itemType == "predicate"){
				$predicateObj = new OCitems_Predicate;
				$output = $predicateObj->getByUUID($uuid);
		  }
		  elseif($itemType == "type"){
				$ocTypeObj = new OCitems_Type;
				$output = $ocTypeObj->getByUUID($uuid);
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
