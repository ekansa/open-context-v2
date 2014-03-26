<?php


//this class interacts with the database for accessing and changing Predicates
//oc_predicates are defined in different projects
class OCitems_Assertions {
    
	 public $db;
	 public $contexts; //array of parent items
	 public $recurseCount = 0;
	 
	 const containsPredicate = "oc-gen:contains";
	 
	 const stringLiteral = "xsd:string"; 
	 const integerLiteral = "xsd:integer"; //numeric
	 const decimalLiteral = "xsd:double"; //numeric
	 const booleanLiteral = "xsd:boolean"; //numeric
	 const dateLiteral = "xsd:date";
	 const typeObject = "type";
	 
    //get data from database
    function getByUUID($uuid, $visibileOnly = true){
        
        $uuid = $this->security_check($uuid);
        $output = false; //not found
        
        $db = $this->startDB();
        
		  if($visibileOnly){
				$visibilityCond = " AND visibility = 1 ";
		  }
		  else{
				$visibilityCond = "";
		  }
		  
        $sql = 'SELECT *
                FROM oc_assertions
                WHERE uuid = "'.$uuid.'"
					 '.$visibilityCond.' 
					 ORDER BY sort
                ';
		
        $result = $db->fetchAll($sql, 2);
        if($result){
            $output = $result;
		  }
        return $output;
    }
    
	 
	 
	 //make an array of parent items from the database, defaults to making URIs of these
    function getParentsByChildUUID($uuid, $recursive = true, $makeURIs = true, $visibileOnly = true){
        
		  $ocGenObj = new OCitems_General;
		  $this->recurseCount++;
        $uuid = $this->security_check($uuid);
        $db = $this->startDB();
        
		  if($visibileOnly){
				$visibilityCond = " AND visibility = 1 ";
		  }
		  else{
				$visibilityCond = "";
		  }
		  
		  
		  
        $sql = 'SELECT uuid AS parentUUID, subjectType, obsNode
                FROM oc_assertions
                WHERE objectUUID = "'.$uuid.'" AND predicateUUID = "'.self::containsPredicate.'"
					 '.$visibilityCond.'
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
	 
	 
	 //get an assertion by it's hashID
	 function getByHashID($hashID){
		  $output = false;
		  $db = $this->startDB();
		  $sql = "SELECT * FROM oc_assertions WHERE hashID = '$hashID' LIMIT 1; ";
		  $result = $db->fetchAll($sql, 2);
		  
        if($result){
				$output = $result[0];
		  }
		  
		  return $output;
	 }
	 
	 
	 
	 //get assertions that have a given Object UUID
	 function getByObjectUUID($objectUUID, $predicateUUIDs = false){
		  
		  $output = false;
		  $db = $this->startDB();
		  
		  $ocGenObj = new OCitems_General;
		  $predicateClause = "";
		  if($predicateUUIDs != false){
				$predicateCond = $ocGenObj->makeORcondition($predicateUUIDs, "predicateUUID");
				if($predicateCond != false){
					 $predicateClause = " AND ($predicateCond) ";
				}
		  }
		  
		  $sql = "SELECT * FROM oc_assertions WHERE objectUUID = '$objectUUID' $predicateClause ";
		  $result = $db->fetchAll($sql, 2);
		  
        if($result){
				$output = $result;
		  }
		  return $output;
	 }
	 
	 
	 //get assertions that have a given Predicate UUID(s)
	 function getByPredicateUUID($predicateUUIDs){
		  
		  $output = false;
		  $db = $this->startDB();
		  
		  $ocGenObj = new OCitems_General;
		  $predicateCond = $ocGenObj->makeORcondition($predicateUUIDs, "predicateUUID");
		  
		  $sql = "SELECT * FROM oc_assertions WHERE $predicateCond ";
		  $result = $db->fetchAll($sql, 2);
		  
        if($result){
				$output = $result;
		  }
		  return $output;
	 }
	 
	 
	 
	 function updateObjectUUIDtoString($oldObjectUUID, $newObjectUUID, $predicateUUIDs = false){
		  
		  $ocGenObj = new OCitems_General;
		  
		  $output = $this->updateObjectUUID($oldObjectUUID, $newObjectUUID, $ocGenObj->getStringType(), false, false, $predicateUUIDs);
		  return $output;
	 }
	 
	 
	 
	 //change an object of an assertion.
	 function updateObjectUUID($oldObjectUUID, $newObjectUUID, $newObjectType = false, $newDataNum = false, $newDataDate = false, $predicateUUIDs = false){
		  $output = false;
		  
		  $doUpdate = true;
		  if($newObjectType != false){
				$doUpdate = $this->validateObjectType($newObjectType);
		  }
		  
		  if($doUpdate){
				$db = $this->startDB();
				$output = array();
				$output["done"] = 0;
				$assertions = $this->getByObjectUUID($oldObjectUUID, $predicateUUIDs);
				if(is_array($assertions)){
					 foreach($assertions as $aOld){
						  $oldHashID = $aOld["hashID"];
						  $uuid = $aOld["uuid"];
						  $obsNum = $aOld["obsNum"];
						  $predicateUUID = $aOld["predicateUUID"];
						  if(!$newObjectType){
								$newObjectType = $aOld["objectType"]; // don't change the object type
						  }
						  
						  $where = "hashID = '$oldHashID' ";
						  $newHashID = $this->makeHashID($uuid, $obsNum, $predicateUUID, $newObjectUUID, $newDataNum, $newDataDate);
						  $data = array("hashID" => $newHashID,
											 "objectUUID" => $newObjectUUID,
											 "objectType" =>  $newObjectType
											 );
								
						  if($newObjectType == self::decimalLiteral || $newObjectType == self::integerLiteral || $newObjectType == self::booleanLiteral){
								$data["dataNum"] = $newDataNum;
						  }
						  elseif($newObjectType == self::dateLiteral){
								$data["dataDate"] = $newDataDate;
						  }
						  
						  $doUpdate = true;
						  if($newHashID != $oldHashID){
								if($this->getByHashID($newHashID)){
									 $doUpdate = false;
									 $output["errors"][] = array("oldHashID" => $oldHashID, "newHashID" => $newHashID, "note" => "new hashID already exists!");
								}
						  }
						  
						  if($doUpdate){
								$db->update("oc_assertions", $data, $where);
								$output["done"]++;
						  }
					 }
				}
		  }
		  return $output;
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
				
				if($this->validateAssertionTypes($data["subjectType"], $data["objectType"])){
					 try{
						  $db->insert("oc_assertions", $data);
						  $success = true;
					 } catch (Exception $e) {
						  //echo (string)$e;
						  //die;
						  $success = false;
					 }
				}
		  }
		  
		  return $success;
	 }
	 
	 
	 function validateAssertionTypes($subjectType, $objectType){
		  $valid = false;
		  if($this->validateSubjectType($subjectType)){
				if($this->validateObjectType($objectType)){
					 $valid = true;
				}
		  }
		  return $valid;
	 }
	 
	 
	 
	 
	 //validate a subject type of an assertion
	 function validateSubjectType($subjectType){
		  
		  $ocGenObj = new OCitems_General;
		  $itemTypes = $ocGenObj->getItemTypes();
		  if(in_array($subjectType, $itemTypes)){
				$valid = true;
		  }
		  else{
				$valid = false;
		  }
		  
		  return $valid;
	 }
	 
	 
	 //validate an object type
	 function validateObjectType($objectType){
		  
		  $ocGenObj = new OCitems_General;
		  $itemTypes = $ocGenObj->getItemTypes();
		  $dataTypes = $ocGenObj->getDataTypes();
		  
		  if(in_array($objectType, $itemTypes)){
				$valid = true;
		  }
		  elseif(in_array($objectType, $dataTypes)){
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
