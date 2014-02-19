<?php


//this class interacts with the database for accessing and changing Predicates
//oc_predicates are defined in different projects
class OCitems_Type {
    
	 public $db;
	 
    /*
     General data
    */
    public $uuid;
	 public $uri;
	 public $hashID;
    public $projectUUID;
    public $sourceID;
	 public $predicateUUID;
	 public $rank; //rank of the property, useful for ordinal values
	 public $label;
	 public $contentUUID; //uuid of the content
	 public $content; //string content (for long properties)
    public $updated;
	 
	 const itemType = "type"; //open context itemtype
   
    //get data from database
    function getByUUID($uuid){
        
		  $ocGenObj = new OCitems_General;
        $uuid = $this->security_check($uuid);
        $output = false; //not found
        
        $db = $this->startDB();
        
        $sql = 'SELECT ot.uuid, ot.hashID, ot.projectUUID, ot.sourceID, ot.predicateUUID,
					 ot.rank, ot.label, ot.contentUUID, os.content, ot.updated
                FROM oc_types AS ot
					 JOIN oc_strings AS os ON ot.contentUUID = os.uuid
                WHERE ot.uuid = "'.$uuid.'"
                LIMIT 1';
		
        $result = $db->fetchAll($sql, 2);
        if($result){
            
				$this->uuid = $uuid;
				$this->hashID = $result[0]["hashID"];
				$this->projectUUID = $result[0]["projectUUID"];
				$this->sourceID = $result[0]["sourceID"];
				$this->predicateUUID = $result[0]["predicateUUID"];
				$this->rank = $result[0]["rank"];
				$this->label = $result[0]["label"];
				$this->contentUUID  = $result[0]["content"];
				$this->content  = $result[0]["contentUUID"];
				$this->updated = $result[0]["updated"];
				$this->uri = $ocGenObj->generateItemURI($this->uuid, self::itemType);
				$result[0]["itemType"] = self::itemType;
				$result[0]["uri"] = $this->uri;
				$output = $result[0];
		  }
        return $output;
    }
    
	 //get the properties for an item
	 function getByPredicateUUID($predicateUUID, $requestParams = false){
		  
		  $ocGenObj = new OCitems_General;
        $predicateUUID = $this->security_check($predicateUUID);
        $output = false; //not found
        
        $db = $this->startDB();
		  
		  $getAnnotations = false;
		  $labelTerm = "";
        if(is_array($requestParams)){
				$label = $ocGenObj->checkExistsNonBlank("q", $requestParams);
				if($label != false){
					 $labelTerm = " AND (ot.label LIKE '%".addslashes($label)."%') ";
				}
				$gAnnot = $ocGenObj->checkExistsNonBlank("getAnnotations", $requestParams);
				if($gAnnot != false){
					 $getAnnotations = true; 
				}
		  }
		  
		  
        $sql = 'SELECT ot.uuid, ot.hashID, ot.projectUUID, ot.sourceID, ot.predicateUUID,
					 ot.rank, ot.label, ot.contentUUID, os.content, ot.updated
                FROM oc_types AS ot
					 JOIN oc_strings AS os ON ot.contentUUID = os.uuid
                WHERE ot.predicateUUID = "'.$predicateUUID.'"
					 '.$labelTerm.'
					 ORDER BY rank, label
					 ';
		
        $result = $db->fetchAll($sql, 2);
        if($result){
            $output = array();
				
				foreach($result as $row){
					 $row["itemType"] = self::itemType;
					 $row["uri"] = $ocGenObj->generateItemURI($row["uuid"], self::itemType);
					 if($getAnnotations){
						  $linkAnnotObj = new Links_linkAnnotation;
						  $row["annotations"] = $linkAnnotObj->getAnnotationsByUUID($row["uuid"]);
						  unset($linkAnnotObj);
					 }
					 $output[] = $row;
				}
		  }
        return $output;
	 }
	 
	 
	 
	 function makeHashID($predicateUUID, $contentUUID){
		  $label= trim($label);
		  return sha1($predicateUUID." ".$contentUUID);
	 }
	 
	 
	 //get a type record by it's content, predicateUUID and it's project
	 function getByContent($content, $predicateUUID, $projectUUID){
		  
		  $db = $this->startDB();
		  $output = false;
		  $contentUUID = $this->getContentStringUUID($content, $projectUUID);
		  if( $contentUUID != false){
				$output = $this->getByPredicateContentUUIDs($predicateUUID, $contentUUID);
		  }
        return $output;
	 }
	 
	 
	 //get a type record by it's predicate and it's content UUIDs
	 function getByPredicateContentUUIDs($predicateUUID, $contentUUID){
		  $output = false;
		  
		  $hashID = $this->makeHashID($predicateUUID, $contentUUID);
			  
		  $sql = "SELECT ot.uuid, ot.hashID, ot.projectUUID, ot.sourceID, ot.predicateUUID,
				ot.rank, ot.label, ot.contentUUID, os.content, ot.updated
				FROM oc_types AS ot
				JOIN oc_strings AS os ON ot.contentUUID = os.uuid
				WHERE ot.hashID = '$hashID' LIMIT 1; ";
		  
		  $result = $db->fetchAll($sql, 2);
		  if($result){
				
				$this->uuid = $result[0]["uuid"];
				$this->hashID = $result[0]["hashID"];
				$this->projectUUID = $result[0]["projectUUID"];
				$this->sourceID = $result[0]["sourceID"];
				$this->predicateUUID = $result[0]["predicateUUID"];
				$this->rank = $result[0]["rank"];
				$this->label = $result[0]["label"];
				$this->contentUUID = $result[0]["contentUUID"];
				$this->content  = $result[0]["content"];
				$this->updated = $result[0]["updated"];
				
				$output = $result[0];
		  }
		  
		  return $output;
	 }
	 
	 
	 function getContentStringUUID($content, $projectUUID){
		  $output = false;
		  $stringObj = new OCitems_String;
		  $stringExists = $stringObj->getByContent($content, $projectUUID);
		  if(is_array($stringExists)){
				$output = $stringExists["uuid"];
		  }
		  return $output;
	 }
	 
	 function getMakeContentStringUUID($content, $projectUUID, $sourceID){
		  $contentUUID = $this->getContentStringUUID($content, $projectUUID);
		  if(!$contentUUID){
				$stringObj = new OCitems_String;
				$stringData = array(		"projectUUID" => $projectUUID,
												"sourceID" => $sourceID,
												"content" => $content);
				
				$contentUUID = $stringObj->createRecord($stringData);
		  }
		  return $contentUUID;
	 }
	 
	 
	 
	 
	 //adds an item to the database, returns its uuid if successful
	 function createRecord($data = false){
		 
		  $db = $this->startDB();
		  $success = false;
		  if(!is_array($data)){
				
				$data = array("uuid" => $this->uuid,
								  "projectUUID" => $this->projectUUID,
								  "sourceID" => $this->sourceID,
								  "predicateUUID" => $this->predicateUUID,
								  "rank" => $this->rank,
								  "label" => $this->label,
								  "contentUUID" => $this->contentUUID
								  );	
		  }
		  else{
				if(!isset($data["uuid"])){
					 $data["uuid"] = false;
				}
		  }
		  
	 	  if(!$data["uuid"]){
				$ocGenObj = new OCitems_General;
				$data["uuid"] = $ocGenObj->generateUUID();
		  }
		  
		  if(!isset($data["contentUUID"])){
				$data["contentUUID"] = false;
		  }
		  if($data["contentUUID"]){
				$data["contentUUID"] = $this->getMakeContentStringUUID($data["label"], $data["projectUUID"], $data["sourceID"]);
		  }
		  if(strlen($data["label"]>=199)){
				$data["label"] = $this->textSnippet($data["label"]);
		  }
		  
		  $data["hashID"] = $this->makeHashID($data["predicateUUID"], $data["contentUUID"]);
	 
		  foreach($data as $key => $value){
				if(is_array($value)){
					 echo print_r($data);
					 die;
				}
		  }
	 
		  try{
				$db->insert("oc_types", $data);
				$success = $data["uuid"];
		  } catch (Exception $e) {
				$success = false;
		  }
		  return $success;
	 }
	 
	 
	 
	 function textSnippet($text, $maxLength = 110, $suffix = "..."){
		  $actLen = $maxLength;
		  $snippetDone = false;
		  while(!$snippetDone){
				$snippet = substr($text, 0, $actLen);
				$lastChar = substr($snippet, -1);
				if($lastChar == " " || $lastChar == "." || $lastChar == "," || $lastChar == ":" || $lastChar == ";"){
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
	 

	 function updateHashIDs(){
		  
		  //a maintenance query to keep hashIDs in synch with the content
		  $db = $this->startDB();
		  
		  $sql = 'UPDATE oc_types 
					 SET hashID = SHA1(CONCAT(predicateUUID, " ", contentUUID))
					 WHERE 1;';
		  
		  $db->query($sql);
	 }
	 
	 
	 
    function security_check($input){
        $badArray = array("DROP", "SELECT", "#", "--", "DELETE", "INSERT", "UPDATE", "ALTER", "=");
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
