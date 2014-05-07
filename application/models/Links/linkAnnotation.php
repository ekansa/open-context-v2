<?php


/*this class stores information annotating Open Context data items with
 *entities from outside vocabularies
*/
class Links_linkAnnotation {
    
	 public $db;
	 
    /*
     General data
    */
	 public $hashID;
	 public $uuid;
    public $subjectURI;
	 public $subjectType;
	 public $projectUUID;
	 public $sourceID;
	 public $predicateURI;
	 public $objectURI;
	 public $creatorUUID; // itentifier of a persons responsible for making the annotation
	 public $updated;
	 
	 public $lookUpLabels = true; //look up the labels to the linked entities
	 
	 const SKOScloseMatch = "http://www.w3.org/2004/02/skos/core#closeMatch";
	 const DCtermsCreator = "http://purl.org/dc/terms/creator";
	 const DCtermsContributor = "http://purl.org/dc/terms/contributor";
	 
	 public $expectedSchema = array("uuid" => array("types" => "xsd:string", "blankOK" => false),
											  "subjectType" => array("types" => "OCitemType", "blankOK" => false),
											  "projectUUID" => array("types" => "xsd:string", "blankOK" => false),
											  "sourceID" => array("types" => "xsd:string", "blankOK" => true),
											  "predicateURI" => array("types" => "xsd:string", "blankOK" => false),
											  "objectURI" => array("types" => "xsd:string", "blankOK" => false),
											  "creatorUUID" => array("types" => "xsd:string", "blankOK" => true)
											  );
	 
	 public $expectedDeleteSchema = array("uuid" => array("types" => "xsd:string", "blankOK" => false),
													  "hashID" => array("types" => "xsd:string", "blankOK" => false));
	 
	  //get data from database
    function getByUUID($uuid, $predicateURI = false, $objectURI = false){
        
        $uuid = $this->security_check($uuid);
        $output = false; //not found
        
        $db = $this->startDB();
        
		  $predicateTerm = "";
		  $objectTerm = "";
		  if($predicateURI != false){
				$predicateTerm = " AND predicateURI = '$predicateURI ' ";
		  }
		  if($objectURI != false){
				$objectTerm = " AND objectURI = '$objectURI ' ";
		  }
		  
        $sql = 'SELECT *
                FROM link_annotations
                WHERE uuid = "'.$uuid.'"
					 '.$predicateTerm.' 
					 '.$objectTerm.'
                LIMIT 1';
		
        $result = $db->fetchAll($sql, 2);
        if($result){
            $output = $result[0];
				$this->hashID = $result[0]["hashID"];
				$this->uuid = $uuid;
				$this->projectUUID = $result[0]["projectUUID"];
				$this->sourceID = $result[0]["sourceID"];
				$this->subjectType = $result[0]["subjectType"];
				$this->predicateURI = $result[0]["predicateURI"];
				$this->objectURI = $result[0]["objectURI"];
				$this->creatorUUID = $result[0]["creatorUUID"];
				$this->updated = $result[0]["updated"];
				//$this->getItemData($uuid);
		  }
        return $output;
    }
	 
	 
	 //get all annotations data from database
    function getAnnotationsByUUID($uuid, $predicateURI = false, $objectURI = false){
        
        $uuid = $this->security_check($uuid);
        $output = false; //not found
        
        $db = $this->startDB();
        
		  $predicateTerm = "";
		  $objectTerm = "";
		  if($predicateURI != false){
				$predicateTerm = " AND predicateURI = '$predicateURI ' ";
		  }
		  if($objectURI != false){
				$objectTerm = " AND objectURI = '$objectURI ' ";
		  }
		  
        $sql = 'SELECT *
                FROM link_annotations
                WHERE uuid = "'.$uuid.'"
					 '.$predicateTerm.' 
					 '.$objectTerm.'
                ';
		
		
        $result = $db->fetchAll($sql, 2);
        if($result){
				$output = array();
				$uriObj = new infoURI;
            foreach($result as $row){
					 $actRecord = $row;
					 if($this->lookUpLabels){
						  $actRecord["subjectLabel"] = false;
						  $actRecord["predicateLabel"] = false;
						  $actRecord["objectLabel"] = false;
						  $sRes = $uriObj->lookupOCitem($uuid, $row["subjectType"]);
						  if(is_array($sRes)){
								$actRecord["subjectLabel"] = $sRes["label"];
						  }
						  $pRes = $uriObj->lookupURI($row["predicateURI"]);
						  if(is_array($pRes)){
								$actRecord["predicateLabel"] = $pRes["label"];
						  }
						  $pRes = $uriObj->lookupURI($row["objectURI"]);
						  if(is_array($pRes)){
								$actRecord["objectLabel"] = $pRes["label"];
						  }
					 }
					 $output[] = $actRecord; 
				}
		  }
        return $output;
    }
	 
	 
	 
	 
	 
	 
	 //checks to see if the uuid is a DC creator
	 function DCcreatorCheck($uuid){
		  return $this->getByUUID($uuid, self::SKOScloseMatch, self::DCtermsCreator);
	 }
	 
	 //checks to see if the uuid is a DC contributor
	 function DCcontributorCheck($uuid){
		  return $this->getByUUID($uuid, self::SKOScloseMatch, self::DCtermsContributor);
	 }
	 
	 
	 
	 
	 
	 //saves linking relations that we've assigned to standard Dublin core creator / contributor roles
	 function annotateStandardDClinks(){
		  
		  $projectUUIDs = array("0");
		  $predicateObj = new OCitems_Predicate;
		  
		  $relToCreator = array("Principle Investigator",
				 "Directed by",
				 "Director",
				 "Editor",
				 "Co-Editor");
		  
		  
		  foreach($relToCreator  as $relationLabel){
				$predData = $predicateObj->getByLabel($relationLabel, $projectUUIDs, "link");
				if(is_array($predData)){
					 $data = array("uuid" => $predicateObj->uuid,
										"subjectType" => "predicates",
										"projectUUID" => $predicateObj->projectUUID,
										"sourceID" => $predicateObj->sourceID,
										"predicateURI" => self::SKOScloseMatch,
										"objectURI" => self::DCtermsCreator,
										"creatorUUID" => false,
										);
					 $this->createRecord($data);
				}
		  }
		  
		  
		  $relToContributor = array("Observer",
				     "Creator",
				     "Principle Author / Analyst",
				     "Editor",
				     "Curator",
				     "o_Creator",
				     "Illustrator",
				     "Recorded by",
				     "Analyst",
				     "Photographed by",
				     "Catalogued by",
				     "Excavated by",
					 "Area supervisor"
					 );
		  
		  foreach($relToContributor as $relationLabel){
				$predData = $predicateObj->getByLabel($relationLabel, $projectUUIDs, "link");
				if(is_array($predData)){
					 $data = array("uuid" => $predicateObj->uuid,
										"subjectType" => "predicates",
										"projectUUID" => $predicateObj->projectUUID,
										"sourceID" => $predicateObj->sourceID,
										"predicateURI" => self::SKOScloseMatch,
										"objectURI" => self::DCtermsContributor,
										"creatorUUID" => false,
										);
					 $this->createRecord($data);
				}
		  }
		  
	 }
	 
	 
	 
	 
	 function makeHashID($uuid, $predicateURI, $objectURI){
		  $uuid = trim($uuid);
		  $predicateURI = trim($predicateURI);
		  $objectURI = trim($objectURI);
		  return sha1($uuid."_".$predicateURI."_".$objectURI);
	 }
	 
	 
	 //create a dublin core contributor record
	 function createDCcontributorRecord($data){
		  $data["predicateURI"] = self::DCtermsContributor;
		  return $this->createRecord($data);
	 }
	 
	 //create a dublin core contributor record
	 function createDCcreatorRecord($data){
		  $data["predicateURI"] = self::DCtermsCreator;
		  return $this->createRecord($data);
	 }
	 
	 
	 //adds an item to the database, returns its uuid if successful
	 function createRecord($data = false){
		 
		  $db = $this->startDB();
		  $success = false;
		  if(!is_array($data)){
				$data = array("uuid" => $this->uuid,
								  "subjectType" => $this->subjectType,
								  "projectUUID" => $this->projectUUID,
								  "sourceID" => $this->sourceID,
								  "predicateURI" => $this->predicateURI,
								  "objectURI" => $this->objectURI,
								  "creatorUUID" => false
								  );	
		  }
		  
		  $data["hashID"] = $this->makeHashID($data["uuid"], $data["predicateURI"], $data["objectURI"]);
	 
		  try{
				$db->insert("link_annotations", $data);
				$success = $data["hashID"];
		  } catch (Exception $e) {
				$success = false;
		  }
		  return $success;
	 }
	 
	 
	 //adds an item to the database, returns its uuid if successful
	 function deleteRecord($whereData = false){
		 
		  $db = $this->startDB();
		  $success = false;
		  if(!is_array($whereData)){
				$where = array();
				$where[] = "uuid = '".$this->uuid."' ";
				$where[] = "hashID = '".($this->makeHashID($this->uuid, $this->predicateURI, $this->objectURI))."' ";
		  }
		  else{
				$where = array();
				foreach($whereData as $fieldKey => $value){
					 $where[] = $fieldKey." = '$value' ";
				}
		  }
		  
		  try{
				$db->delete("link_annotations", $where);
				$success = true;
		  } catch (Exception $e) {
				$success = false;
		  }
		  return $success;
	 }
	 
	 
	 
    function security_check($input){
        $badArray = array("DROP", "SELECT", " ", "--", "DELETE", "INSERT", "UPDATE", "ALTER", "=");
        foreach($badArray as $bad_word){
            if(stristr($input, $bad_word) != false){
                $input = str_ireplace($bad_word, "XXXXXX", $input);
            }
        }
        return $input;
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
