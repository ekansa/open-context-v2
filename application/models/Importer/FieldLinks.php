<?php


/*this class creates subject items
*/
class Importer_FieldLinks {
    
	 public $db;
	 public $projectUUID;
	 public $sourceID;
	 
	 public $containmentFields; //start of the containment tree
	 public $errors;
	 
	 const containsPredicate = "oc-gen:contains";
	 const describesPredicate = "oc-gen:describes";
	 
	 public $expectedContainSchema = array("sourceID" => array("type" => "xsd:string", "blankOK" => false),
														"projectUUID" => array("type" => "xsd:string", "blankOK" => false),
													  "subjectField" => array("type" => "xsd:string", "blankOK" => false),
													  "objectField" => array("type" => "xsd:string", "blankOK" => false)
													  );
	 
	 
	 function getFieldRelations(){
		  $output = false;
		  $db = $this->startDB();
		  
		  $sql = "SELECT * FROM imp_fieldlinks WHERE sourceID = '".$this->sourceID."' ORDER BY predicateUUID, subjectField, objectField, objectType; ";
		  $result = $db->fetchAll($sql, 2);
        if($result){
				$output = $result;
		  }
		  return $result;
	 }
	 
	 
	 
	 //gets the containment fields in order of parent to child. 
	 function getContainmentFields(){
		  
		  $output = false;
		  $db = $this->startDB();
		  
		  $sql = "SELECT * FROM imp_fieldlinks WHERE sourceID = '".$this->sourceID."' AND predicateUUID = '".self::containsPredicate."' ; ";
		  
		  $result = $db->fetchAll($sql, 2);
        if($result){
				$pairs = array();
				$children = array();
				foreach($result as $row){
					 $subjectField = $row["subjectField"];
					 $objectField = $row["objectField"];
					 $children[] = $objectField;
					 $pairs[$subjectField] = $objectField;
				}
				
				$rootParentField = false;
				foreach($result as $row){
					 $subjectField = $row["subjectField"];
					 $objectField = $row["objectField"];
					 if(!in_array($subjectField, $children)){
						  if(!$rootParentField){
								$rootParentField = $subjectField;
						  }
						  else{
								$this->noteErrors("Multiple root parent fields.");
						  }
					 }
				}
				
				if(!$rootParentField){
					 $this->noteErrors("Cannot find a root parent field.");
				}
				else{
					 $containmentFields = array();
					 $containmentFields[] = $rootParentField;
					 $done = false;
					 $actParentField = $rootParentField;
					 while(!$done){
						  if(array_key_exists($actParentField, $pairs)){
								$containmentFields[] = $pairs[$actParentField];
								$actParentField = $pairs[$actParentField];
						  }
						  else{
								$done = true;
						  }
					 }
					 
					 if(!$this->errors){
						  $this->containmentFields = $containmentFields;
						  $output = $containmentFields;
					 }	 
				}
		  }
		  
		  return $output;
	 }
	 
	 
	 //adds a containment relationship link, after doing some validation tests to make sure it's ok
	 function addContainmentLink($inputData){
		  
		  $this->sourceID = $inputData["sourceID"];
		  $this->projectUUID = $inputData["projectUUID"];
		  $parentField = $inputData["subjectField"];
		  $childField = $inputData["objectField"];
		  
		  $output = false;
		  $db = $this->startDB();
		  
		  $createLink = true;
		  if($parentField == $childField){
				$createLink = false; //cannot link to yourself!
				$this->noteErrors("Attempt to link the same field to itself");
		  }
		  else{
				$childFieldChildren = $this->getContainmentChildren($childField , true);
				if(is_array($childFieldChildren)){
					 if(in_array($parentField, $childFieldChildren)){
						  $createLink = false; //parentField in the list of children; gonna make a circular hierarchy, don't do it
						  $this->noteErrors("Attempt to make a circular hierarchy");
					 }
				}
				$childExists = $this->getContainmentChildren($parentField, false);
				if(is_array($childExists)){
					 $createLink = false; //parentField is already a parent of another field
					 $this->noteErrors("This field already contains a child field");
				}
		  }
		  
		  if($createLink){
				
				$data = array("projectUUID" => $this->projectUUID,
								  "sourceID" => $this->sourceID,
								  "subjectField" => $parentField,
								  "predicateUUID" => self::containsPredicate,
								  "objectField" => $childField);
				
				$output = $this->createRecord($data);
		  }
		  
		  return $output;
	 }
	 
	 
	 // get the next child or all the children field for a given item
	 function getContainmentChildren($parentField, $recursive = false){
		  $output = false;
		  $db = $this->startDB();
		  $sql = "SELECT * FROM imp_fieldlinks WHERE subjectField = $parentField AND predicateUUID = '".self::containsPredicate."'; ";
		  $result = $db->fetchAll($sql, 2);
        if($result){
				$output = array();
				foreach($result as $row){
					 $childField = $row["objectField"];
					 $output[] = $childField;
					 if($recursive){
						  $rOutput = $this->getContainmentChildren($childField, $recursive);
						  if(is_array($rOutput)){
								foreach($rOutput as $rChField){
									 $output[] = $rChField;
								}
						  }
					 }
				}
		  }
		  return $output;
	 }
	 
	 
	 
	 function makeHashID($subjectField, $predicateUUID, $objectField, $objectUUID){
		  return sha1($this->sourceID."_".$subjectField."_".$predicateUUID."_".$objectField."_".$objectUUID);
	 }
	 
	 
	 
	 //creates a record for a field
	 function createRecord($data){
		  $db = $this->startDB();
		  
		  if(!isset($data["objectField"])){
				$data["objectField"] = false;
		  }
		  if(!isset($data["objectUUID"])){
				$data["objectUUID"] = false;
		  }
		  if(!isset($data["objectType"])){
				$data["objectType"] = false;
		  }
		  
		  $data["hashID"] = $this->makeHashID($data["subjectField"], $data["predicateUUID"], $data["objectField"], $data["objectUUID"]);

		  try{
				$db->insert("imp_fieldlinks", $data);
				$success = true;
		  } catch (Exception $e) {
				$success = false;
		  }
		  return $success;
	 }
	 
	 
	 //records arrt of error messages
	 function noteErrors($errors){
		  if(!is_array($errors)){
				if(strlen($errors)>1){
					 $errors = array($errors);
				}
		  }
		  if(is_array($errors)){
				if(count($errors)>0){
					 if(!is_array($this->errors)){
						  $this->errors = $errors;
					 }
					 else{
						  $allErrors = $this->errors;
						  foreach($errors as $newError){
								$allErrors[] = $newError;
						  }
						  $this->errors = $allErrors;
					 }
				}
		  }
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
