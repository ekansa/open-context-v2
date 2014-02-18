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
					 $note = $row["note"];
					 if(strlen($note) < 1){
						  $note = $label;
					 }
					 if($useTypeNoteAsString){
						  $content = $note;
					 }
					 else{
						  $content = $label;
					 }
					 
					 $stringUUID = false;
					 $stringExists = $stringObj->getByContent($content, $projectUUID);
					 if(is_array($stringExists)){
						  $stringUUID = $stringExists["uuid"];
						  //string already exists in the string table.
						  
					 }
					 else{
						  //string does not exist in the string table, add it
						  
						  $stringData = array("uuid" => $uuid,
													 "projectUUID" => $projectUUID,
													 "sourceID" => $sourceID,
													 "content" => $content
													 );
						  $stringOK = $stringObj->createRecord($stringData);
						  if($stringOK != false){
								$stringUUID = $uuid;
						  }
					 }
					 
					 if($stringUUID != false){
						  //now update the assertions to point to indicate that the objects of assertions are strings
						  $assertionChange = $assertionsObj->updateObjectUUIDtoString($uuid, $stringUUID, $predicateUUID);
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
					 $uuid = $row["uuid"];
					 $projectUUID = $row["projectUUID"];
					 $sourceID = $row["sourceID"];
					 $stringUUID = $row["objectUUID"];
					 $objectType = $row["objectType"];
					 
					 $content = false;
					 
					 $stringExists = $stringObj->getByUUID($stringUUID);
					 if(is_array($stringExists)){
						  $content = $stringExists["content"];
					 }
					 
					 if( $content != false){
						  
						  $note = false;
						  if(strlen($content)<100){
								$label = $content;
						  }
						  else{
								$label = $this->textSnippet($content);
						  }
						  
						  
						  //now update the assertions to point to indicate that the objects of assertions are strings
						  $assertionChange = $assertionsObj->updateObjectUUIDtoString($uuid, $stringUUID, $predicateUUID);
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
