<?php


/*this class creates subjects items
*/
class Importer_Subjects {
    
	 public $db;
	 public $projectUUID;
	 public $sourceID;
	 
	 public $nextBatch = 0;
	 public $bankPlaceHolders = true;
	 public $containmentFields; //start of the containment tree
	 
	 public $errors;
	 
	 public $expectedProcessSchema = array("sourceID" => array("types" => "xsd:string", "blankOK" => false),
														"projectUUID" => array("types" => "xsd:string", "blankOK" => false),
														  "startID" => array("types" => "xsd:integer", "blankOK" => false)
													  );
	 
	 const blankPlaceHolder = "[BLANK]";
	 const hiearchyDelim = "/"; //delimiter for making hierarchy paths
	 const substituteDelim = ":"; //character to substitute so as not to screw up hiearchy paths
	 const defaultBatchSize = 25;
	 
	 function process($inputData){
		  
		  $output = false;
		  $this->sourceID = $inputData["sourceID"];
		  $this->projectUUID = $inputData["projectUUID"];
		  $startID = $inputData["startID"];
		  $batchSize = self::defaultBatchSize;
		  
		  $fieldLinksObj = new Importer_FieldLinks;
		  $fieldLinksObj->sourceID = $this->sourceID;
		  $containmentFields = $fieldLinksObj->getContainmentFields();
		  
		  if(is_array($containmentFields)){
				$db = $this->startDB();
				
				$uploadTabObj = new Importer_UploadTab;
				$uploadTabObj->sourceID = $this->sourceID;
				
				$containFieldLabels = $uploadTabObj->makeFieldArray($containmentFields);
				$result = $uploadTabObj->queryByFieldArray($containFieldLabels, $startID, $batchSize);
				if($result){
					 foreach($result as $row){
						  $id = $row["id"];
						  unset($row["id"]);
						  foreach($row as $fieldKey => $value){
								
								
								
						  }
					 }
					 $output = $result;
				}
		  }
		  
		  return $output;
	 }
	 
	 
	 //gets the containment fields in order of parent to child. 
	 function getContainmentFields(){
		  
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
					 }	 
				}
		  }
	 }
	 
	 
	 
	 function noteErrors($errors){
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
