<?php
/* This class creates a compact XML representation of
 * the JSON-LD item to store in the database without annoying escape characters
 * 
 */

class XMLjsonLD_LegacyGeo  {
    
	 public $db; //database connection object
	 
	 
	 function convertOldGeo(){
		  
		  $db = $this->startDB();
		  $geoObj = new OCitems_Geodata;
		  
		  $output = array();
		  $output["count"] = 0;
		  //get old  geospatial data
		  $sql = "SELECT * FROM geo_space WHERE 1; ";
		  
		  $result = $db->fetchAll($sql, 2);
        if($result){
				foreach($result as $row){
					 $uuid = $this->idUpdate($row["uuid"]);	 
					 $projectUUID = $row["project_id"];
					 $sourceID = $row["source_id"];
					 $lat = $row["latitude"];
					 $lon = $row["longitude"];
					 $spec = $row["specificity"] + 0;
					 $note = $row["note"];
					 
					 $data = array("uuid" =>  $uuid,
							 "projectUUID" => $projectUUID,
							 "itemType" => "subjects",
							 "ftype" => "point",
							 "latitude" => $lat,
							 "longitude" => $lon,
							 "specificity" =>  $spec,
							 "note" => $note
							 );
					 
					 if($geoObj->createRecord($data)){
						  $output["count"]++;
					 }
					 else{
						  $output["errors"][] = $uuid;
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
