<?php
/* This class creates a compact XML representation of
 * the JSON-LD item to store in the database without annoying escape characters
 * 
 */

class XMLjsonLD_LegacyProps  {
    
	 public $db; //database connection object
	 public $baseURL;
	 
	 function convertLongProps(){
		  
		  $db = $this->startDB();
		 
		  $output = array();
		  $output["count"] = 0;
		  //get old  geospatial data
		  $sql = "SELECT * 
		  FROM  oc_types 
		  WHERE CHAR_LENGTH( label ) >= 100
		  LIMIT 10; ";
		  
		  $result = $db->fetchAll($sql, 2);
        if($result){
				foreach($result as $row){
					 $uuid = $row["uuid"];
					 $propUUID = $this->originalID($row["uuid"]);
					 
					 $url = $this->baseURL.$propUUID.".json";
					 @$jsonData = file_get_contents($url);
					 if($jsonData){
						  $json = json_decode($jsonData, true);
						  if(is_array($json)){
								
								$where = "uuid = '$uuid' ";
								$propValue = $json["value"];
								if(strlen($propValue)> 100){
									 $where = "uuid = '$uuid' ";
									 $data = array("label" => $this->textSnippet($propValue, 75),
														"note" => $propValue);
									 
									 $output[$uuid] = $data;
									 //$db->update("oc_types", $data, $where);
								}
								
						  }
					 }
				}
		  }
		  
		  return $output;
	 }
	 
	 
	 function convertLongPredicates(){
		  
		  $db = $this->startDB();
		 
		  $output = array();
		  $output["count"] = 0;
		  //get old  geospatial data
		  $sql = "SELECT predicateUUID, AVG( CHAR_LENGTH( label ) ) as AveLen, COUNT(uuid) as uuidCount 
				FROM  oc_types 
				WHERE 1
				GROUP BY predicateUUID
				ORDER BY  AveLen DESC";
		  
		  $result = $db->fetchAll($sql, 2);
        if($result){
				foreach($result as $row){
					 $uuid = $row["uuid"];
					 $propUUID = $this->originalID($row["uuid"]);
					 
					 $url = $this->baseURL.$propUUID.".json";
					 @$jsonData = file_get_contents($url);
					 if($jsonData){
						  $json = json_decode($jsonData, true);
						  if(is_array($json)){
								
								$where = "uuid = '$uuid' ";
								$propValue = $json["value"];
								if(strlen($propValue)> 100){
									 $where = "uuid = '$uuid' ";
									 $data = array("label" => $this->textSnippet($propValue, 75),
														"note" => $propValue);
									 
									 $output[$uuid] = $data;
									 //$db->update("oc_types", $data, $where);
								}
								
						  }
					 }
				}
		  }
		  
		  return $output;
	 }
	 
	 
	 
	 function originalID($uuid){
		  
		  $LegacyIDobj = new OCitems_LegacyIDs;
		  $updatedID = $LegacyIDobj->getByNewUUID($uuid);
		  if($updatedID){
				$uuid = $LegacyIDobj->oldUUID;
		  }
		  return $uuid;
	 }
	 
	 
	 
	 function idUpdate($uuid){
		  
		  $LegacyIDobj = new OCitems_LegacyIDs;
		  $updatedID = $LegacyIDobj->getByOldUUID($uuid);
		  if($updatedID){
				$uuid = $LegacyIDobj->newUUID;
		  }
		  return $uuid;
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
