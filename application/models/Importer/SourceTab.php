<?php


/*this class manages the tables for import (stored in refine)
*/
class Importer_SourceTab {
    
	 public $db;
	 
	 public $sourceID; //table name
	 public $projectUUID;
	 public $label;
	 public $filename;
	 public $rootUUID; //uuid of the root subject item
	 public $licenseURI; //uri for the copyright license
	 public $note;
	 public $updated;
	 
	 public $totalRowCount; //number of rows
	 public $dataRecords; 
	 
	 function getBySourceID($sourceID){
		  
		  $db = $this->startDB();
        
        $sql = 'SELECT *
					 FROM imp_sourcetabs
                WHERE sourceID = "'.$sourceID.'"
                LIMIT 1';
		
        $result = $db->fetchAll($sql, 2);
        if($result){
            
				$this->sourceID = $result[0]["sourceID"];
				$this->projectUUID = $result[0]["projectUUID"];
				$this->label = $result[0]["label"];
				$this->filename  = $result[0]["filename"];
				$this->rootUUID  = $result[0]["rootUUID"];
				$this->licenseURI  = $result[0]["licenseURI"];
				$this->note  = $result[0]["note"];
				$this->updated = $result[0]["updated"];
				$output = $result[0];
		  }
        return $output;
    }
	 
	 
	 function createRecord($data){
		  
		  
		  
		  
		  
		  
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
