<?php
/* This class creates a compact XML representation of
 * the JSON-LD item to store in the database without annoying escape characters
 * 
 */

class Edit_Types  {
    
	 public $db; //database connection object
	 public $baseURL;
	 
	 const stringLiteral = "xsd:string"; 
	 const integerLiteral = "xsd:integer"; //numeric
	 const decimalLiteral = "xsd:double"; //numeric
	 const booleanLiteral = "xsd:boolean"; //numeric
	 const dateLiteral = "xsd:date";
	 const typeObject = "type";
	 
	 //converts types into string values for a given predicate
	 function convertTypePredicateToString($predicateUUID, $useTypeNoteAsString = false){
		  
		  $db = $this->startDB();
		 
		  $output = array();
		  $output["typesChanged"] = 0;
		  
		  
		  $ocGenObj = new OCitems_General;
		  $stringObj = new OCitems_String;
		  $assertionsObj = new OCitems_Assertions;
		  $ocTypeObj = new OCitems_Type;
		  $allTypes = $ocTypeObj->getByPredicateUUID($predicateUUID);
		  if(is_array($allTypes)){
				foreach($allTypes as $row){
					 $uuid = $row["uuid"];
					 $projectUUID = $row["projectUUID"];
					 $sourceID = $row["sourceID"];
					 $label = $row["label"];
					 $contentUUID = $row["contentUUID"];
					 
					 if($contentUUID != false){
						  //now update the assertions to point to indicate that the objects of assertions are strings
						  $assertionChange = $assertionsObj->updateObjectUUIDtoString($uuid, $contentUUID, $predicateUUID);
						  if(isset($assertionChange["errors"])){
								$output["errors"][] = array("typeUUID" => $uuid, "error" => $assertionChange);
						  }
					 }
					 else{
						  $output["errors"][] = array("typeUUID" => $uuid, "error" => "could not make a stringUUID");
					 }
				}
				
				if(!isset($output["errors"])){
					 $predicateObj = new OCitems_Predicate;
					 $predicateObj->updatePredicateDataType($predicateUUID, self::stringLiteral);
				}
		  }
		  
		  return $output;
	 }
	 
	 
	 
	 function convertStringPredicateToType($predicateUUID){
		  
		  $db = $this->startDB();
		 
		  $output = array();
		  $output["typesChanged"] = 0;
		  
		  
		  $ocGenObj = new OCitems_General;
		  $stringObj = new OCitems_String;
		  $assertionsObj = new OCitems_Assertions;
		  $ocTypeObj = new OCitems_Type;
		  $assertions = $assertionsObj->getByPredicateUUID($predicateUUID);
		  if(is_array($assertions)){
				foreach($assertions as $row){
					 $contentUUID
					 $projectUUID = $row["projectUUID"];
					 $sourceID = $row["sourceID"];
					 $contentUUID = $row["objectUUID"];
					 $objectType = $row["objectType"];
					 
					 $typeUUID = false;
					 $typeRes = $ocTypeObj->getByPredicateContentUUIDs($predicateUUID, $contentUUID);
					 if(is_array($typeRes)){
						  $typeUUID = $typeRes["uuid"];
					 }
					 else{
						  $content = false;
						  $stringExists = $stringObj->getByUUID($contentUUID);
						  if(is_array($stringExists)){
								$content = $stringExists["content"];
								
								$typeData = array("projectUUID" => $projectUUID,
														"sourceID" => $sourceID,
														"predicateUUID" => $predicateUUID,
														"rank" => false,
														"label" => $content,
														"contentUUID" => $contentUUID
														)
								
								$typeUUID = $ocTypeObj->createRecord($typeData);
						  }
						  else{
								$output["errors"][] = array("objectUUID" => $contentUUID, "error" => "Can't find content");
						  }
					 }
					 
					 if($typeUUID != false){
						  
						  //now update the assertions to point to indicate that the objects of assertions are strings
						  $assertionChange = $assertionsObj->updateObjectUUID($contentUUID, $typeUUID, self::typeObject);
						  if(!isset($assertionChange["errors"])){
								$output["typesChanged"]++;
								$ocTypeObj->deleteByUUID($uuid);
						  }
						  else{
								$output["errors"][] = array("typeUUID" => $uuid, "error" => $assertionChange);
						  }
					 }
					 else{
						  $output["errors"][] = array("typeUUID" => $uuid, "error" => "could not make a stringUUID");
					 }
				}
				
				if(!isset($output["errors"])){
					 $predicateObj = new OCitems_Predicate;
					 $predicateObj->updatePredicateDataType($predicateUUID, self::stringLiteral);
				}
		  }
		  
		  return $output;
	 }
	 
	 
	 
	 
	 
	 
	 function textSnippet($text, $maxLength = 100, $suffix = "..."){
		  $actLen = $maxLength;
		  $snippetDone = false;
		  while(!$snippetDone){
				$snippet = substr($text, 0, $actLen);
				$lastChar = substr($snippet, -1);
				if($lastChar == " " || $lastChar == "."){
					 $snippet = substr($text, 0, $actLen - 1);
					 $snippet .= $suffix;
					 $snippetDone = true;
					 break;
				}
				else{
					 $actLen = $actLen - 1;
				}
		  }
		  
		  return $snippet;
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
