<?php


//this class interacts with the database exporting data tables in a format easy for
//python to consume for open context
//oc_predicates are defined in different projects
class Exporter_Export {
    
	 public $db;
	 
	 public $typeFields = array("subjectType", "objectType", "itemType");
	 
	 public $typeMappings = array("subject" => "subjects",
								  "document" => "documents",
								  "type" => "types",
								  "person" => "persons",
								  "project" => "projects",
								  "table" => "tables",
								  "predicate" => "predicates"
								  );
	 
	 public $mappings = array("hashID" => "hash_id",
							  "subjectType" => "subject_type",
							  "projectUUID" => "project_uuid",
							  "sourceID" => "source_id",
							  "obsNode" => "obs_node",
							  "obsNum" => "obs_num",
							  "predicateUUID" => "predicate_uuid",
							  "objectUUID" => "object_uuid",
							  "objectType" => "object_type",
							  "dataNum" => "data_num",
							  "dataDate" => "data_date",
							  "startLC" => "start_lc",
							  "startC" => "start_c",
							  "endC" => "end_c",
							  "endLC" => "endLC",
							  "itemType" => "item_type",
							  "geoJSON" => "geo_json",
							  "stableID" => "stable_id",
							  "stableType" => "stable_type",
							  "oldUUID" => "old_uuid",
							  "newUUID" => "new_uuid",
							  "classURI" => "class_uri",
							  "desPredicateUUID" => "des_predicate_uuid",
							  "recordUpdated" => "record_updated",
							  "mediaType" => "media_type",
							  "mimeTypeURI" => "mime_type_uri",
							  "thumbMimeURI" => "thumb_mime_uri",
							  "thumbURI" => "thumb_uri",
							  "previewMimeURI" => "preview_mime_uri",
							  "previewURI" => "preview_uri",
							  "fullURI" => "full_uri",
							  "foafType" => "foaf_type",
							  "archaeoMLtype" => "archaeoml_type",
							  "dataType" => "data_type",
							  "contentUUID" => "content_uuid",
							  "predicateURI" => "predicate_uri",
							  "objectURI" => "object_uri",
							  "creatorUUID" => "creator_uuid",
							  "altLabel" => "alt_label",
							  "vocabURI" => "vocab_uri",
							  "type" => "ent_type",
							  "parentURI" => "parent_uri",
							  "childURI" => "child_uri"
							  );
	 
	 //tables that are needed for export, with the field of their last update
	 public $tables = array("link_annotations" => array("time" => "updated", "sort" => "hashID"),
							"link_dcmetadata"  => array("time" => "updated", "sort" => "term"),
							"link_entities"  => array("time" => "updated", "sort" => "uri"),
							"link_hierarchies"  => array("time" => "updated", "sort" => "hashID"),
							"oc_assertions"  => array("time" => "updated", "sort" => "hashID"),
							"oc_chronology"  => array("time" => "updated", "sort" => "uuid"),
							"oc_documents"  => array("time" => "updated", "sort" => "uuid"),
							"oc_geodata"  => array("time" => "updated", "sort" => "uuid"),
							"oc_identifiers"  => array("time" => "updated", "sort" => "uuid"),
							"oc_legacyids"  => array("time" => "updated", "sort" => "oldUUID"),
							"oc_manifest" => array("time" => "recordUpdated", "sort" => "uuid"),
							"oc_mediafiles" => array("time" => "updated", "sort" => "uuid"),
							"oc_persons" => array("time" => "updated", "sort" => "uuid"),
							"oc_predicates" => array("time" => "updated", "sort" => "uuid"),
							"oc_projects" => array("time" => "updated", "sort" => "uuid"),
							"oc_strings" => array("time" => "updated", "sort" => "uuid"),
							"oc_subjects" => array("time" => "updated", "sort" => "uuid"),
							"oc_types" => array("time" => "updated", "sort" => "uuid")
							);
	 
	 
	 
	 //lists tables and their last update time
	 function getLastUpdates(){
		  $db = $this->startDB();
		  $output = array();
		  foreach($this->tables as $tableKey => $fieldSettings){
			   $updateField = $fieldSettings["time"];
			   $sql = "SELECT max(".$updateField.") as lastUpdate FROM ".$tableKey. " WHERE 1;";
			   $result = $db->fetchAll($sql, 2);
			   $lastUpdate = $result[0]["lastUpdate"];
			   if($lastUpdate == null){
					$lastUpdate = false;
			   }
			   $output[] = array("table" => $tableKey, "lastUpdate" =>$lastUpdate);
		  }
		  
		  return $output;
	 }
	 
	 //counts the number of records in a table from a certain time or later
	 function getLastUpdateCount($actTable, $minDate){
		  $db = $this->startDB();
		  $tables = $this->tables;
		  $output = false;
		  if(array_key_exists($actTable, $tables)){
			   $sortField =  $tables[$actTable]["sort"];
			   $updateField = $tables[$actTable]["time"];
			   $sql = "SELECT count(*) as recordCount FROM $actTable WHERE $updateField >= '$minDate' ;";
			   $result = $db->fetchAll($sql, 2);
			   $output = array("table" => $actTable, "recordCount" => $result[0]["recordCount"] + 0);
		  }
		  
		  return $output;
	 }
	 
	 //returns record from a table
	 function getRecords($actTable, $minDate, $start = 0, $numRecords = 100){
		  
		  $db = $this->startDB();
		  $typeFields = $this->typeFields;
		  $typeMappings = $this->typeMappings;
		  $mappings = $this->mappings;
		  $tables = $this->tables;
		  $output = false;
		  if(array_key_exists($actTable, $tables)){
			   $sortField =  $tables[$actTable]["sort"];
			   $updateField = $tables[$actTable]["time"];
			   $sql = "SELECT * FROM $actTable WHERE $updateField >= '$minDate' ORDER BY $sortField LIMIT $start, $numRecords";
			   
			   $i = $start + 1;
			   $result = $db->fetchAll($sql, 2);
			   if($result){
					$output = array();
					foreach($result as $row){
						 $activeRecord = array();
						 foreach($row as $fieldKey => $value){
							  $value = trim($value);
							  if(array_key_exists($fieldKey, $mappings)){
								   $useFieldKey = $mappings[$fieldKey];
							  }
							  else{
								   $useFieldKey = $fieldKey;
							  }
							  if(in_array($fieldKey, $typeFields)){
								   if(array_key_exists($value, $typeMappings)){
										$value = $typeMappings[$value];
								   }
							  }
							  
							  $activeRecord[$useFieldKey] =  $value;
						 }
						 
						 $output[$i] = $activeRecord; 
					$i++;
					}
			   }
		  }
		  
		  return $output;
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
