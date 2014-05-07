<?php
/* This class creates a compact XML representation of
 * the JSON-LD item to store in the database without annoying escape characters
 * 
 */

class XMLjsonLD_LegacyLinks  {
    
	 public $db; //database connection object
	 
	 const Pred_SKOScloseMatch = "http://www.w3.org/2004/02/skos/core#closeMatch";
	 const Pred_isAbout = "http://purl.obolibrary.org/obo/IAO_0000136"; //used for annotating what a measurement is about
	 const Pred_SKOSrelated = "http://www.w3.org/2004/02/skos/core#related"; //used for noting the type of measurement
	 const Pred_rdfsRange = "http://www.w3.org/2000/01/rdf-schema#range"; //used for units of measurement
	 
	 function convertTypeAnnotations(){
		  $output = array();
		  $linkAnnotObj = new Links_linkAnnotation;
		  $db = $this->startDB();
		  
		  //get old links
		  $sql = "SELECT * FROM linked_data WHERE (itemType = 'property' OR itemType = 'prop'); ";
		  
		  $result = $db->fetchAll($sql, 2);
        if($result){
				foreach($result as $row){
					 $uuid = $this->idUpdate($row["itemUUID"]);	 
					 $projectUUID = $row["fk_project_uuid"];
					 $sourceID = $row["source_id"];
					 $data = array("uuid" => $uuid,
									 "subjectType" => "types",
									 "projectUUID" => $projectUUID,
									 "sourceID" => $sourceID,
									 "predicateURI" => self::Pred_SKOScloseMatch ,
									 "objectURI" => $row["linkedURI"],
									 "creatorUUID" => false);
					 if($linkAnnotObj->createRecord($data)){
						  $output[] = array("uuid" => $uuid, "objectURI" => $row["linkedURI"]);
					 }
				}
		  }
		  
		  return $output;
	 }
	 
	 
	 function convertVariableTypeAnnotations(){
		  $output = array();
		  $linkAnnotObj = new Links_linkAnnotation;
		  $db = $this->startDB();
		  
		  //get old links
		  $sql = "SELECT * FROM linked_data WHERE itemType = 'variable'
		  AND linkedType = 'type'
		  ; ";
		  
		  $result = $db->fetchAll($sql, 2);
        if($result){
				foreach($result as $row){
					 $uuid = $this->idUpdate($row["itemUUID"]);	 
					 $projectUUID = $row["fk_project_uuid"];
					 $sourceID = $row["source_id"];
					 $data = array("uuid" => $uuid,
									 "subjectType" => "predicates",
									 "projectUUID" => $projectUUID,
									 "sourceID" => $sourceID,
									 "predicateURI" => self::SKOScloseMatch,
									 "objectURI" => $row["linkedURI"],
									 "creatorUUID" => false);
					 if($linkAnnotObj->createRecord($data)){
						  $output[] = array("uuid" => $uuid, "objectURI" => $row["linkedURI"]);
					 }
				}
		  }
		  
		  return $output;
	 }
	 
	 
	 function convertVariableConsistsOfAnnotations(){
		  $output = array();
		  $linkAnnotObj = new Links_linkAnnotation;
		  $db = $this->startDB();
		  
		  //get old links
		  $sql = "SELECT * FROM linked_data WHERE itemType = 'variable'
		  AND linkedType = 'consists of'
		  ; ";
		  
		  $result = $db->fetchAll($sql, 2);
        if($result){
				foreach($result as $row){
					 $uuid = $this->idUpdate($row["itemUUID"]);	 
					 $projectUUID = $row["fk_project_uuid"];
					 $sourceID = $row["source_id"];
					 $data = array("uuid" => $uuid,
									 "subjectType" => "predicates",
									 "projectUUID" => $projectUUID,
									 "sourceID" => $sourceID,
									 "predicateURI" => self::Pred_isAbout,
									 "objectURI" => $row["linkedURI"],
									 "creatorUUID" => false);
					 if($linkAnnotObj->createRecord($data)){
						  $output[] = array("uuid" => $uuid, "objectURI" => $row["linkedURI"]);
					 }
				}
		  }
		  
		  return $output;
	 }
	 
	 
	 function convertVariableRelatedAnnotations(){
		  $output = array();
		  $linkAnnotObj = new Links_linkAnnotation;
		  $db = $this->startDB();
		  
		  //get old links
		  $sql = "SELECT * FROM linked_data WHERE itemType = 'variable'
		  AND linkedType = 'Measurement type'
		  ; ";
		  
		  $result = $db->fetchAll($sql, 2);
        if($result){
				foreach($result as $row){
					 $uuid = $this->idUpdate($row["itemUUID"]);	 
					 $projectUUID = $row["fk_project_uuid"];
					 $sourceID = $row["source_id"];
					 $data = array("uuid" => $uuid,
									 "subjectType" => "predicates",
									 "projectUUID" => $projectUUID,
									 "sourceID" => $sourceID,
									 "predicateURI" => self::Pred_SKOSrelated,
									 "objectURI" => $row["linkedURI"],
									 "creatorUUID" => false);
					 if($linkAnnotObj->createRecord($data)){
						  $output[] = array("uuid" => $uuid, "objectURI" => $row["linkedURI"]);
					 }
				}
		  }
		  
		  return $output;
	 }
	 
	 
	 function convertVariableMeaurementUnitAnnotations(){
		  $output = array();
		  $linkAnnotObj = new Links_linkAnnotation;
		  $db = $this->startDB();
		  
		  //get old links
		  $sql = "SELECT * FROM linked_data WHERE itemType = 'variable'
		  AND linkedType = 'unit'
		  ; ";
		  
		  $result = $db->fetchAll($sql, 2);
        if($result){
				foreach($result as $row){
					 $uuid = $this->idUpdate($row["itemUUID"]);	 
					 $projectUUID = $row["fk_project_uuid"];
					 $sourceID = $row["source_id"];
					 $data = array("uuid" => $uuid,
									 "subjectType" => "predicates",
									 "projectUUID" => $projectUUID,
									 "sourceID" => $sourceID,
									 "predicateURI" => self::Pred_rdfsRange,
									 "objectURI" => $row["linkedURI"],
									 "creatorUUID" => false);
					 if($linkAnnotObj->createRecord($data)){
						  $output[] = array("uuid" => $uuid, "objectURI" => $row["linkedURI"]);
					 }
				}
		  }
		  
		  return $output;
	 }
	 
	 
	 
	 function idUpdate($uuid){
		  
		  $LegacyIDobj = new OCitems_LegacyIDs;
		  $updatedID = $LegacyIDobj->getByOldUUID($uuid);
		  if($updatedID){
				$uuid = $LegacyIDobj->newUUID;
		  }
		  return $uuid;
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
