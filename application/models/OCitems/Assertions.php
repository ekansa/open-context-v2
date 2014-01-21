<?php


//this class interacts with the database for accessing and changing Predicates
//oc_predicates are defined in different projects
class OCitems_Assertions {
    
	 public $db;
	 public $contexts; //array of parent items
	 public $recurseCount = 0;
	 
	 const containsPredicate = "oc-gen:contains";
	 
	 
    //get data from database
    function getByUUID($uuid){
        
        $uuid = $this->security_check($uuid);
        $output = false; //not found
        
        $db = $this->startDB();
        
        $sql = 'SELECT *
                FROM oc_assertions
                WHERE uuid = "'.$uuid.'"
					 ORDER BY sort
                ';
		
        $result = $db->fetchAll($sql, 2);
        if($result){
            $output = $result;
		  }
        return $output;
    }
    
	 
	 
	 //make an array of parent items from the database, defaults to making URIs of these
    function getParentsByChildUUID($uuid, $recursive = true, $makeURIs = true){
        
		  $ocGenObj = new OCitems_General;
		  $this->recurseCount++;
        $uuid = $this->security_check($uuid);
        $db = $this->startDB();
        
        $sql = 'SELECT uuid AS parentUUID, subjectType, obsNode
                FROM oc_assertions
                WHERE objectUUID = "'.$uuid.'" AND predicateUUID = "'.self::containsPredicate.'"
					 ORDER BY sort;
                ';
		
        $result = $db->fetchAll($sql, 2);
        if($result){
				$oldContain = $this->contexts;
            $newContain = array();
            $i = 1;
            foreach($result as $row){
                $newParentUUID = $row["parentUUID"];
					 if($makeURIs){
						  $newParent = $ocGenObj->generateItemURI($newParentUUID, $row["subjectType"]);
					 }
					 else{
						  $newParent = $newParentUUID;
						  
					 }
                $treeName = $row["obsNode"];
					 if(is_array($oldContain)){
						  foreach($oldContain as $treeNameKey => $treeItems){
								if($treeNameKey == $treeName){
									 $newContain[$treeName][0] =  $newParent;
									 $j = 1;
									 foreach($treeItems as $olderParentItem){
										  $newContain[$treeName][$j] = $olderParentItem;
										  $j++;
									 }
								}
								else{
									 $newContain[$treeNameKey] =  $treeItems;
								}
						  }
					 }
					 else{
						  $newContain[$treeName][0] =  $newParent;
					 } 
					 
					 $this->contexts = $newContain;
					 if($this->recurseCount > 20){
						  die;
					 }
					 else{
						  $this->getParentsByChildUUID($newParentUUID, $recursive, $makeURIs);
					 }
					 
					 $i++;
            }
		  }
        
    }
	 
	 //purge assertions that say something is contained within itself
	 function cleanSpaceHierarchy(){
		  $db = $this->startDB();
		  $sql = 'DELETE FROM oc_assertions WHERE uuid = objectUUID AND predicateUUID = "'.self::containsPredicate.'"; ';
		  $db->query($sql);
	 }
	 
	 //purge assertions that say something is contained within itself
	 function cleanMissingPredicates(){
		  $db = $this->startDB();
		  $sql = 'DELETE FROM oc_assertions WHERE predicateUUID = ""; ';
		  $db->query($sql);
	 }
	 
	 
	 //generate a hashID for the item
	 function makeHashID($uuid, $obsNum, $predicateUUID, $objectUUID, $dataNum = false, $dataDate = false){
		  return sha1($uuid." ".$obsNum." ".$predicateUUID." ".$objectUUID." ".$dataNum." ".$dataDate);
	 }
	 
	 
	 
	 //adds an item to the database
	 function createRecord($data){
		 
		  $db = $this->startDB();
		  $success = false;
		  if(is_array($data)){
				
				$data["hashID"] =  $this->makeHashID($data["uuid"], $data["obsNum"], $data["predicateUUID"], $data["objectUUID"], $data["dataNum"], $data["dataDate"]);
				$data["created"] = date("Y-m-d H:i:s");
				
				
				foreach($data as $key => $value){
					 if(is_array($value)){
						  echo print_r($data);
						  die;
					 }
				}
				
				
				try{
					 $db->insert("oc_assertions", $data);
					 $success = true;
				} catch (Exception $e) {
					 //echo (string)$e;
					 //die;
					 $success = false;
				}
				
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
