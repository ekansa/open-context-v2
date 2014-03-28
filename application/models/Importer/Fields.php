<?php


/*this class manages the tables for import (stored in refine)
*/
class Importer_Fields {
    
	 public $db;
	 public $sourceID; //table name
	 public $projectUUID;
	 public $fields; //array of fields
	 
	
	 
	 function getBySourceID($sourceID){
		  $db = $this->startDB();
        $sql = 'SELECT *
					 FROM imp_fields
                WHERE sourceID = "'.$sourceID.'"
                ORDER BY fieldNumber';
		
        $result = $db->fetchAll($sql, 2);
        if($result){
				$this->fields = $result;
		  }
        return $result;
    }
	 
	 
	 //loads the data from a refine model
	 function loadUpdateRefineModel($model){
		  $db = $this->startDB();
		  $output = array();
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
										"originalName" => $col["originalName"],
										"ignore" => 0
										);
					 
					 $db->insert("imp_fields", $data);
					 $output["inserted"][] = $data;
				}
				
		  $fieldNumber++;
		  }
		  
		  $output = $this->checkMissingFields($output); // check to see if any fields were deleted, and if so mark to be ignored
		  return $output; 
	 }
	 
	 //checks to see if some fields were removed from refine, if so mark them to be ignored
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
