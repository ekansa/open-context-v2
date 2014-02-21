<?php


/*this class manages the table uploaded by the importer
*/
class Importer_UploadTab {
    
	 public $db;
	 
	 public $sourceID; //table name
	 public $projectUUID;
	 public $label;
	 public $filename;
	 public $rootUUID; //uuid of the root subject item
	 public $licenseURI; //uri for the copyright license
	 public $note;
	 public $updated;
	 
	 public $tabFieldArray = array(); //array of the table fields as field-number (key), field_label (value) pairs
	 
	 
	 public $sheet; //data table being uploaded
	 public $numColumns; //number of collumns
	 public $numRows; //number of rows
	 public $headerAliasRow;
	 public $headerRow;
	 public $dataRecords; 
	 
	 const fieldLabelPrefix = "field_";
	 
	 
	 function getBySourceID($sourceID){
		  
		  $db = $this->startDB();
        
        $sql = 'SELECT *
					 FROM imp_uploadtabs
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
	 
	 
	 
	 
	 
	 
	 //cleanup function to trim the uploaded data of leading and trailing white spaces
	 function trimWhiteSpaces(){
		  $db = $this->startDB();
		  $sourceID = $this->sourceID;
		  foreach($this->tabFieldArray as $fieldNumber => $fieldLabel){
				$sql = "UPDATE ".$sourceID." SET ".$fieldLabel." = TRIM(".$fieldLabel."); ";
				$db->query($sql);
		  }
	 }
	 
	 
	 function queryByFieldArray($fieldArray, $idStart, $batchSize, $where = "1"){
		  
		  $db = $this->startDB();
		  
		  if(!in_array("id", $fieldArray)){
				$fieldArray[] = "id";
		  }
		  
		  $fields = implode(", ", $fieldArray);
		  $sql = "SELECT  $fields FROM ".$this->sourceID." WHERE ".$where." LIMIT $idStart, $batchSize; ";
		  
		  $result = $db->fetchAll($sql, 2);
        
		  return $result;
	 }
	 
	 
	 
	 
	 //make numeric field array into an array with full field labels needed for querying
	 function makeFieldArray($fieldNumberArray, $tableName = false){
		  $fieldArray = false;
		  if(is_array($fieldNumberArray)){
				$fieldArray = array();
				if($tableName != false){
					 $tableName = $tableName.".";
				}
				else{
					 $tableName = "";
				}
				
				foreach($fieldNumberArray as $fieldNum){
					 $fieldArray[] = $tableName.self::fieldLabelPrefix.$fieldNum;
				}
		  }
		  return $fieldArray;
	 }
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 function loadData()
    {
        //1.  initialize error reporting settings:
        error_reporting(E_ALL ^ E_NOTICE); // supresses the "null value in position X" messages:
        
        //2.  parse excel file:
        $data = new SpreadsheetExcelReader();
        $data->setOutputEncoding('UTF-8');
        $data->read($this->filename);
        //dump column formats here:
               
        /*Zend_Debug::dump($data->dateFormats);        
        Zend_Debug::dump($data->formatRecords); 
        Zend_Debug::dump($data);*/
        
        
        
        //3.  populate private variables:
        $this->sheet = $data->sheets[0];
        $this->numColumns = $this->sheet['numCols'];
        $this->numRows = $this->sheet['numRows'];
        
		  //4.  populate column header and header aliases:                     
		  for ($i = 1; $i <= $this->numColumns; $i++)
		  {
			  if(strlen($this->sheet['cells'][1][$i])<1){
					$this->headerAliasRow[$i-1] = "Blank_".$i; //avoid error if field heading is blank
			  }
			  else{
					$this->headerAliasRow[$i-1]  = $this->sheet['cells'][1][$i];
			  }
			  $this->headerRow[$i-1]       = self::fieldLabelPrefix . $i;
		  }
         
		  //5.  populate data:
		  for ($i = 2; $i <= $this->numRows; $i++) // iterate through each row (starting with the second row)         
		  {
			  $dataRecord = array();            
			  for ($j = 1; $j <= $this->numColumns; $j++){ //iterate through each column
					//@$cleanData = utf8_encode($this->_sheet['cells'][$i][$j]);
					//$cleanData = mb_convert_encoding($this->_sheet['cells'][$i][$j], 'UTF8');
					$cleanData = ($this->sheet['cells'][$i][$j]);
					if(!$cleanData){
						 $cleanData = $this->sheet['cells'][$i][$j];
					}
					$dataRecord[$j-1] = $cleanData;
			  }
			  $this->dataRecords[$i-2] = $dataRecord;
		  }
    }
    
    /**-------------------------------------------------------------------------
     * function commitDataToDB()
     * -------------------------------------------------------------------------
     * Takes the data from the $_headerRow, $_headerAliasRow, and $_dataRecords
     * arrays and populates the relevant database tables:
     * - field_summary
     * - file_summary
     * - dynamic table that's created on-the-fly
     */
    function commitDataToDB()
    {
        $db = $this->startDB();
		  
        //1.  dynamically create a new table:
        $fieldsToCreate = implode(" text,", $this->headerRow) ." text CHARACTER SET utf8";
        $schemaSql = "CREATE TABLE ".$this->sourceID."  (
					id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
					$fieldsToCreate
					)ENGINE=MyISAM DEFAULT CHARSET=utf8;
					";
        //todo:  add the indexes when you create the table.
        //echo $schemaSql;
        $db->query($schemaSql);
        
        //insure happy UTF-8 world
        $alterSQL = "ALTER TABLE ".$this->sourceID." DEFAULT CHARACTER SET utf8 COLLATE  utf8_general_ci;";
        $db->query($alterSQL);
        
        //3.  insert the newly generated fields (and their aliases) into the field_summary table:
        for ($i = 0; $i < $this->numColumns; $i++){
            
				$this->createFieldRecord($i+1, $this->headerAliasRow[$i]);
            if($i<20){
                $indexSQL = "CREATE INDEX fInd_".($i+1)." ON ".$this->sourceID."(".$this->headerRow[$i]."(10));";
                $db->query($indexSQL);
            }
        }
        
        //4.  insert the data records into the new table:
		  // iterate through each row (starting with the second row)    
        for ($i = 0; $i < sizeof($this->dataRecords); $i++){
            $data = array();
            for ($j = 0; $j < $this->numColumns; $j++){ //iterate through each column
                $data[$this->headerRow[$j]] = $this->dataRecords[$i][$j];
				}
				$db->insert($this->sourceID, $data);
        }
        return $this->sourceID;
    }
	 
	 
	 //creates a record for a field
	 function createFieldRecord($fieldNumber, $label){
		  $data = array("projectUUID" => $this->projectUUID,
							 "sourceID" => $this->sourceID,
							 "fieldNumber" => $fieldNumber,
							 "label" => $label,
							 "itemType" => false
							 );
		  
		  try{
				$db->insert("imp_fields", $data);
				$success = true;
		  } catch (Exception $e) {
				$success = false;
		  }
		  return $success;
	 }
	 
	 
	 
	 
    
    /**-------------------------------------------------------------------------
     * function generateSourceID()
     * -------------------------------------------------------------------------
     * Generates and arbitrary table name.
     */
    function generateSourceID(){
        $endTab = md5($this->filename);
        $endTab = substr($endTab,0,9);
        
        $projectNumber = $this->getProjectNumber($this->projectUUID);
		  $projTableNum = $this->getProjectTableNumber($this->projectUUID);
        return "z_" . $projectNumber . "_" . $projTableNum . "_".$endTab;
    }
    
    
	 //get the project number, in order of publication date.
	 function getProjectNumber($projectUUID){
		  
		  $output = 1; //default
		  $db = $this->startDB();
		  $sql = "SELECT uuid FROM oc_manifest WHERE itemType = 'project' ORDER BY published; ";
		  
		  $result = $db->fetchAll($sql, 2);
        if($result){
				$i = 1;
				foreach($result as $row){
					 if($row["uuid"] == $projectUUID){
						  $output = $i;
						  break;
					 }
				$i++;
				}
		  }
		  return $output;
	 }
	 
	 //get the project number, in order of publication date.
	 function getProjectTableNumber($projectUUID){
		  
		  $output = 1; //default
		  $db = $this->startDB();
		  $sql = "SELECT sourceID FROM imp_uploadtabs WHERE projectUUID = '$projectUUID' ORDER BY created; ";
		  
		  $result = $db->fetchAll($sql, 2);
        if($result){
				$output = count($result) + 1;
		  }
		  return $output;
	 }
	 
	 
	 
	 
	 //adds an item to the database, returns its sourceID if successful
	 function createRecord($data = false){
		 
		  $db = $this->startDB();
		  $success = false;
		  if(!is_array($data)){
				
				$data = array("sourceID" => $this->sourceID,
								  "projectUUID" => $this->projectUUID,
								  "label" => $this->label,
								  "filename" => $this->filename,
								  "rootUUID" => $this->rootUUID,
								  "licenseURI" => $this->licenseURI,
								  "note" => $this->note
								  );	
		  }
		  else{
				if(!isset($data["sourceID"])){
					 $data["sourceID"] = false;
				}
		  }
		  
	 	  if(!$data["sourceID"]){
				$data["sourceID"] = $this->generateSourceID();
		  }

		  try{
				$db->insert("imp_lookups", $data);
				$success = $data["sourceID"];
		  } catch (Exception $e) {
				$success = false;
		  }
		  return $success;
	 }
	 
	 
	 
	 //deletes an upload table and records about it
	 function deleteUpload($sourceID){
		  
		  $db = $this->startDB();
		  $where = "sourceID = '$sourceID' ";
		  $db->delete("imp_fields", $where);
		  $db->delete("imp_fieldlinks", $where);
		  $db->delete("imp_uploadtabs", $where);
		  $sql = "DROP TABLE IF EXISTS ".$sourceID."; ";
		  $db->query($sql);
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
