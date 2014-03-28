<?php
/*
This class calls the Open Refine API

I'm including it, since it may be useful to adapt to other projects

*/
class Importer_Refine  {
    
	 public $db;
	 public $projectUUID;
	 public $sourceID;
	 public $refineProjectID;
	 public $localTableID; 
	 
	 const baseRefineURL = "http://127.0.0.1:3333";
	 const sourceIDprefix = "zref_";
	 
	 public $expectedLoadFieldSchema = array(
											  "projectUUID" => array("type" => "xsd:string", "blankOK" => false),
											  "refineProjectID" => array("type" => "xsd:string", "blankOK" => true)
											  );
	 
	 
	 
	 function loadRefineData($clearTableFirst = true){
		  
		  $output = array();
		  $output["count"] = 0;
		  $db = $this->startDB();
		  
		  if($clearTableFirst){
				$sql = "TRUNCATE TABLE ".$this->localTableID;
				$db->query($sql, 2);
		  }
		  
		  $start = 0;
		  $limit = 500;
		 
		  $done = false;
		  
		  $model = $this->getModelData();
		  if(is_array($model)){
				$fieldIndexCellIndex = array();
				$fieldIndex = 1;
				foreach($model["columnModel"]["columns"] as $col){
					 $fieldIndexCellIndex[$fieldIndex] = $col["cellIndex"];
					 $fieldIndex++;
				}
				
				$recordCount = 0;
				while(!$done){
					 $rowData = $this->getRowsData($start, $limit);
					 if(is_array($rowData)){
						  $filtered = $rowData["filtered"];
						  if(isset($rowData["rows"])){
								$start = $start + $limit;
								$output["count"]++;
								foreach($rowData["rows"] as $row){
									 $data = array();
									 $cells = $row["cells"];
									 
									 foreach($fieldIndexCellIndex as $fieldIndex => $cellIndex){
										  $fieldName = "field_".$fieldIndex;
										  $cell = $cells[$cellIndex];
										  $data[$fieldName] = "";
										  if(isset($cells[$cellIndex])){
												if(is_array($cell)){
													 $data[$fieldName] = $cell["v"];
												}
										  }
									 }
									 $data["id"] = $row["i"]+1;
									 
									 try{
										  $db->insert($this->localTableID, $data);
									 }catch (Exception $e) {
										  //$done = true;
									 }
									 
									 if($row["i"] >= $rowData["filtered"] -1){
										  $done = true;
										  break;
									 }
									 
									 $recordCount++;
									 if($recordCount>= $rowData["filtered"]){
										  $done = true;
										  break;
									 }
								} 
						  }
						  else{
								$done = true;
						  }
					 }
				}//end loop
		  
		  }//end case where we have a model array
	 }
	 
	 
	 //loads or updates the data model from Refine to Open Context
	 function loadUpdateModel($requestParams){
		  $output = false;
		  $this->projectUUID = $requestParams["projectUUID"];
		  $this->refineProjectID = $requestParams["refineProjectID"];
		  $model = $this->getModelData(); //gets the project model (schema) from Refine
		  if(is_array($model)){
				$this->sourceID = $this->refineProjectIDtoSourceID($this->refineProjectID);
				$fieldsObj = new Importer_Fields;
				$fieldsObj->projectUUID = $this->projectUUID;
				$fieldsObj->sourceID = $this->sourceID;
				$output = $fieldsObj->loadUpdateRefineModel($model);
		  }//end case with a model;
		  
		  return $output;
	 }
	 
	 
	 //get the model (fields, their order) for the Refine project
	 function getModelData(){
		  $model = false;
		  $url = self::baseRefineURL."/command/core/get-models?project=".$this->refineProjectID;
		  @$modelString = file_get_contents($url);
		  if($modelString){
				$model = json_decode($modelString, true);
		  }
		  return $model;
	 }
	 
	 
	 //get the model (fields, their order) for the Refine project
	 function getRowsData($start, $limit){
		  $rowData = false;
		  $url = self::baseRefineURL."/command/core/get-rows?project=".$this->refineProjectID;
		  $url .= "&start=".$start."&limit=".$limit;
		  @$rowString = file_get_contents($url);
		  if($rowString){
				$rowData = json_decode($rowString, true);
		  }
		  return $rowData;
	 }
	 
	 
	 //get a list of projects from Refine
	 function getProjectsData(){
		  $data = false;
		  $url = self::baseRefineURL."/command/core/get-all-project-metadata";
		  @$jsonString = file_get_contents($url);
		  if($jsonString){
				$data = json_decode($jsonString, true);
		  }
		  return $data;
	 }
	 
	 //reorder projects by modified time
	 function reorderProjectData($data){
		  $output = false;
		  if(isset($data["projects"])){
				$tArray = array();
				$output = array();
				foreach($data["projects"] as $projKey => $parray){
					 $mod = strtotime($parray["modified"]);
					 $tArray[$projKey] = $mod;
				}
				arsort($tArray); //sorts by time, high to low
				foreach($tArray as $projKey => $mod){
					 $output["projects"][$projKey] = $data["projects"][$projKey];
				}
		  }
		  return $output;
	 }
	 
	 
	 
	 
	 
	 //convert an open-context source ID to a Refine project ID
	 function sourceIDtoRefineProjectID($sourceID){
		  $output = false;  
		  if(strstr($sourceID, self::sourceIDprefix)){
				$output = str_replace(self::sourceIDprefix, "", $sourceID);
		  }
		  return $output;
	 }
	 
	 //convert a Refine project ID to an Open Context sourceID
	 function refineProjectIDtoSourceID($refineProjectID){
		  return self::sourceIDprefix.$refineProjectID;
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
