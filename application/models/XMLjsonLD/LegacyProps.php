<?php
/* This class creates a compact XML representation of
 * the JSON-LD item to store in the database without annoying escape characters
 * 
 */

class XMLjsonLD_LegacyProps  {
    
	 public $db; //database connection object
	 public $baseURL;
	 
	 function convertLongProps(){
		  
		  $stringObj = new OCitems_String;
		  $db = $this->startDB();
		 
		  $output = array();
		  $output["count"] = 0;
		  //get old  geospatial data
		  $sql = "SELECT * 
		  FROM  oc_types 
		  WHERE CHAR_LENGTH( label ) >= 199
		  ";
		  
		  $result = $db->fetchAll($sql, 2);
        if($result){
				foreach($result as $row){
					 $uuid = $row["uuid"];
					 $propUUID = $this->originalID($row["uuid"]);
					 $projectUUID = $row["projectUUID"];
					 $sourceID = $row["sourceID"];
					 
					 $url = $this->baseURL.$propUUID.".json";
					 @$jsonData = file_get_contents($url);
					 if($jsonData){
						  $json = json_decode($jsonData, true);
						  if(is_array($json)){
								
								$where = "uuid = '$uuid' ";
								$content = $json["value"];
								
								$contentUUID = false;
								$stringExists = $stringObj->getByContent($content, $projectUUID);
								if(is_array($stringExists)){
									 $contentUUID = $stringExists["uuid"];
								}
								else{
									 $stringData = array("projectUUID" => $projectUUID,
																"sourceID" => $sourceID,
																"content" => $content);
									 $contentUUID = $stringObj->createRecord($stringData);
								}
								
								if($contentUUID != false){
									 $where = "uuid = '$uuid' ";
									 $data = array("label" => $this->textSnippet($content),
														"contentUUID" => $contentUUID);
									 $db->update("oc_types", $data, $where);
									 $output["count"]++;
									 $output[$uuid] = $data;
								}
						  }
					 }
				}
		  }
		  
		  return $output;
	 }
	 
	 function addContentUUIDs(){
		  
		  $stringObj = new OCitems_String;
		  $db = $this->startDB();
		 
		  $output = array();
		  $output["count"] = 0;
		  //get old  geospatial data
		  $sql = "SELECT * 
		  FROM  oc_types 
		  WHERE contentUUID = ''
		  ";
		  
		  $result = $db->fetchAll($sql, 2);
        if($result){
				foreach($result as $row){
					 $uuid = $row["uuid"];
					 $projectUUID = $row["projectUUID"];
					 $sourceID = $row["sourceID"];
					 
					 $content = $row["label"];
					 
					 $contentUUID = false;
					 $stringExists = $stringObj->getByContent($content, $projectUUID);
					 if(is_array($stringExists)){
						  $contentUUID = $stringExists["uuid"];
					 }
					 else{
						  $stringData = array("projectUUID" => $projectUUID,
													 "sourceID" => $sourceID,
													 "content" => $content);
						  $contentUUID = $stringObj->createRecord($stringData);
					 }
					 
					 if($contentUUID != false){
						  $where = "uuid = '$uuid' ";
						  $data = array("contentUUID" => $contentUUID);
						  $db->update("oc_types", $data, $where);
						  $output["count"]++;
						  //$output[$uuid] = $data;
					 }
				}
		  }
		  
		  return $output;
	 }
	 
	 
	 function updateDuplicateProps(){
		  $db = $this->startDB();
		 
		  $assertionsObj = new OCitems_Assertions;
		  $output = array();
		  
		  $sql = "SELECT uuid, predicateUUID, contentUUID, CONCAT(predicateUUID, ' ', contentUUID) as varVal, count(uuid) as uuidCount
					 FROM oc_types
					 WHERE 1
					 GROUP BY varVal
					 ORDER BY uuidCount DESC
					 LIMIT 15;";
		  
		  $result = $db->fetchAll($sql, 2);
        if($result){
				foreach($result as $row){
					 $goodUUID =  $row["uuid"];
					 $predicateUUID = $row["predicateUUID"];
					 $contentUUID = $row["contentUUID"];
					 
					 $sql = "SELECT uuid FROM oc_types WHERE predicateUUID = '$predicateUUID' AND contentUUID = '$contentUUID' ;";
					 $resultB = $db->fetchAll($sql, 2);
					 if($resultB){
						  foreach($result as $rowB){
								if($rowB["uuid"] != $goodUUID){
									 $badUUID = $rowB["uuid"];
									 $res = $assertionsObj->updateObjectUUID($badUUID, $goodUUID);
									 $output[$goodUUID][] = array("badUUID" => $badUUID, "update-result" => $res);
									 
									 if(!isset($res["errors"])){
										  $where = "uuid = '$badUUID' ";
										  $db->delete("oc_types", $where);
									 }
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
