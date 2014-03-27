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
		  
		  $db = $this->startDB();
		  $model = $this->getModelData(); //gets the project model (schema) from Refine
		  if(is_array($model)){
				$output = array();
				$this->sourceID = $this->refineProjectIDtoSourceID($this->refineProjectID);
				$fieldNumber = 1;
				foreach($model["columnModel"]["columns"] as $col){
					 $sql = "SELECT *
					 FROM imp_fields
					 WHERE projectUUID = '".$this->projectUUID."'
					 AND sourceID = '".$this->sourceID."' 
					 AND originalName = '".$col["originalName"]."'
					 AND cellIndex = ".$col["cellIndex"]."
					 LIMIT 1
					 ";
					 
					 $result = $db->fetchAll($sql, 2);
					 if($result){
						  //this particular field already exists, time to update its fieldNumber, cell index, and current name
						  $fieldID = $result[0]["id"];
						  $ignore = $result[0]["ignore"];
						  
						  $where = "id = ".$fieldID;
						  
						  $data = array("fieldNumber" => $fieldNumber,
											 "cellIndex" => $col["cellIndex"],
											 "label" => $col["name"]
											 );
						  
						  if($ignore == 1){
								$data["ignore"] = 0; //turn a field marked for being ignored back on
						  }
						  
						  $db->update("imp_fields", $data, $where);
						  $output["updated"][] = $data;
					 }
					 else{
						  //add a new field
						  $data = array("projectUUID" => $this->projectUUID,
											 "sourceID" => $this->sourceID,
											 "fieldNumber" => $fieldNumber,
											 "cellIndex" => $col["cellIndex"],
											 "label" => $col["name"],
											 "originalName" => $col["originalName"]
											 );
						  
						  $db->insert("imp_fields", $data);
						  $output["inserted"][] = $data;
					 }
					 
				$fieldNumber++;
				}
				
				$output = $this->checkMissingFields($output); // check to see if any fields were deleted, and if so mark to be ignored
		  }//end case with a model;
		  
		  return $output;
	 }
	 
	 
	 function checkMissingFields($fieldData){
		  if(isset($fieldData["updated"])){
				$db = $this->startDB();
				$updatedFields = $fieldData["updated"];
				
				$sql = "SELECT *
					 FROM imp_fields
					 WHERE projectUUID = '".$this->projectUUID."'
					 AND sourceID = '".$this->sourceID."'
					 ";
				$result = $db->fetchAll($sql, 2);
				if($result){	 
					 foreach($result as $row){
						  $found = false;
						  foreach($updatedFields as $upField){
								if($upField["cellIndex"] == $row["cellIndex"]){
									 $found = true;
								}
						  }
						  
						  if(!$found){
								//a field that was originally loaded no longer exists, we need to mark it to be ignored
								$where = "id = ".$row["id"];
								$data = array("ignore" => 1);
								$db->update("imp_fields", $data, $where);
								$fieldData["ignoring"][] = $row;
						  }
					 }
				}
		  }
		  
		  return $fieldData;
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
