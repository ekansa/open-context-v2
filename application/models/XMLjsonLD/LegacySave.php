<?php
/* This class reads legacy ArchaeoML XML documents
 * and saves the data to the new relational data structure
 * 
 */

class XMLjsonLD_LegacySave  {
    
	 public $db; //database connection object
	 public $changedUUIDs;
	 public $assertionSort = 1; //sort order for assertions
	 public $doneURIs = 0;
	 public $existingURIs = 0;
	 public $errors;
	 public $retrieveBaseSubjectURI;
	 public $retrieveBaseMediaURI;
	 public $retrieveBaseDocURI;
	 public $retrieveBasePersonURI;
	 public $retrieveBaseProjectURI;
	 
	 public $maxLimit = false;
	 const maxComplete = 200;
	 const defaultSourceID = "ArchaeoML Doc";
	 
	 const stringLiteral = "xsd:string"; 
	 const integerLiteral = "xsd:integer"; //numeric
	 const decimalLiteral = "xsd:double"; //numeric
	 const booleanLiteral = "xsd:boolean"; //numeric
	 const dateLiteral = "xsd:date";
	 
	 const notePredicateUUID = "oc-gen:has-note";
	 const containsPredicateUUID = "oc-gen:contains";
	 
	 function  JSONlist($listURL){
		  
		  $lastIndexedPage = $this->getLastJSONpage($listURL);
		  if($lastIndexedPage != false){
				$listURL = $lastIndexedPage;
		  }
		  
		  $errors = array();
		  @$jsonString = file_get_contents($listURL);
		  if($jsonString){
				$api =  json_decode($jsonString, true);
				if(is_array($api)){
					 foreach($api["results"] as $record){
						  $uri = $record["uri"];
						  $itemUUID = $this->makeUUIDfromURI($uri);
						  $this->addSubjectItem($itemUUID);
					 }
					 $this->recordLastJSONpage($listURL);
					 if($api["paging"]["next"] != false){
						  $nextURL = $api["paging"]["next"];
						  unset($api);
						  if(!$this->maxLimit){
								$this->JSONlist($nextURL); // do this if there's no limit placed on processing lists
						  }
						  elseif($this->doneURIs <= self::maxComplete){
								$this->JSONlist($nextURL); //obey a limit
						  }
					 }
				}
				else{
					 $errors[] = "Error in reading List: ".$listURL;
				}
		  
		  }
		  else{
				$errors[] = "Could not get List: ".$listURL;
		  }
		  $this->noteErrors($errors);
	 }
	 
	 
	 //save the JSON page
	 function getLastJSONpage($listURL){
		  $output = false;
		  $db = $this->startDB();
		  
		  $page = 0;
		  $parseURL = parse_url($listURL);
		  if(isset($parseURL["query"])){
				$queryParams = $this->convertUrlQuery($parseURL["query"]);
				if(isset($queryParams["page"])){
					 $page = $queryParams["page"];
				}
		  }
		  
		  $sql = "SELECT * FROM oc_legacy_service WHERE page > $page ORDER BY page DESC LIMIT 1;";
		  $result = $db->fetchAll($sql, 2);
        if($result){
            $lastFound = $result[0]["lastIndexed"];
				if($listURL != $lastFound){
					 $output = $lastFound;
				}
		  }
		  return $output;
	 }
	 
	 //array of query params
	 function convertUrlQuery($query) { 
		  $queryParts = explode('&', $query); 
		  
		  $params = array(); 
		  foreach ($queryParts as $param) { 
				$item = explode('=', $param); 
				$params[$item[0]] = $item[1]; 
		  } 
		  
		  return $params; 
	 } 
	 
	 
	 //save the JSON page
	 function recordLastJSONpage($JSONurl){
		  $success = false;
		  $db = $this->startDB();
		  
		  $page = 0;
		  $parseURL = parse_url($JSONurl);
		  if(isset($parseURL["query"])){
				$queryParams = $this->convertUrlQuery($parseURL["query"]);
				if(isset($queryParams["page"])){
					 $page = $queryParams["page"];
				}
		  }
		  
		  $data = array("lastIndexed" => $JSONurl, "page" => $page);
		  try{
				$db->insert("oc_legacy_service", $data);
				$success = true;
		  } catch (Exception $e) {
				$success = false;
		  }
		  
		  return $success;
	 }
	 
	 
	 
	 function toDoList($type){
		  $db = $this->startDB();
		  
		  $sql = "SELECT * FROM oc_todo WHERE type = '$type' AND done = 0; ";
		  
		  $result = $db->fetchAll($sql, 2);
        if($result){
				foreach($result as $row){
					 $itemUUID = $row["uuid"];
					 $currentDone = $this->doneURIs;
					 if($type == "subject"){
						  $this->addSubjectItem($itemUUID);
					 }
					 if($type == "media"){
						  $this->addMediaItem($itemUUID);
					 }
					 if($type == "document"){
						  $this->addDocItem($itemUUID);
					 }
					 if($type == "person"){
						  $this->addPersonItem($itemUUID);
					 }
					 if($type == "project"){
						  $this->addProjectItem($itemUUID);
					 }
					 if($this->doneURIs > $currentDone){
						  $data = array("done" => 1);
						  $where = "uuid = '$itemUUID' ";
						  $db->update("oc_todo", $data, $where);
					 }
				}
		  }
	 }
	 
	 
	 function addToDoList($itemUUID, $type){
		  $db = $this->startDB();

		  $data = array("uuid" => $itemUUID, "type" => $type, "done" => 0);
		  $success = false;
		  try{
				$db->insert("oc_todo", $data);
				$success = true;
		  } catch (Exception $e) {
				$success = false;
		  }
		
		  return $success;
	 }
	 
	 //add subject item
	 function addSubjectItem($itemUUID){
		 
		  $this->changedUUIDs = false;
		  $doneURIs = $this->doneURIs;
		  $existingURIs = $this->existingURIs;
		  $errors = array();
		  $itemURL = $this->retrieveBaseSubjectURI.$itemUUID.".xml";
		  $output = false;
		  if(!$this->checkItemExits($itemUUID)){
				$db = $this->startDB();
				@$xmlString = file_get_contents($itemURL);
				if($xmlString != false){
					 
					 
					 $xmlString = str_replace('<?xml version="1.0"?>', '<?xml version="1.0" encoding="UTF-8" ?>', $xmlString);
					 
					 $xmlString = tidy_repair_string($xmlString,
										  array( 
												'doctype' => "omit",
												'input-xml' => true,
												'output-xml' => true 
										  ));
					 
					 $itemXML = simplexml_load_string($xmlString);
					 /*
					 if(!$itemXML){
						  echo "here";
						  $xmlString = tidy_repair_string($xmlString,
										  array( 
												'doctype' => "omit",
												'input-xml' => true,
												'output-xml' => true 
										  ));
						  
						  @$itemXML = simplexml_load_string($xmlString);
						  if(!$itemXML){
								echo "bad XML ";
								echo $xmlString ;
								die;
						  }
						  
					 }
					 */
					 
					 if($itemXML != false){
						  $jsonLDObj = new XMLjsonLD_Item;
						  $xpathsObj = new XMLjsonLD_XpathBasics;
						  $jsonLDObj = $xpathsObj->URIconvert($itemURL , $jsonLDObj);
						  $jsonLDObj->uri = $itemURL;
						  $jsonLDObj->uri = $jsonLDObj->validateURI($jsonLDObj->uri);
						  $this->assertionSort = 1;
						  $this->saveContainmentData($jsonLDObj);
						  $this->saveObservationData($jsonLDObj);
						 
						  if($this->changedUUIDs){
								//UUIDs changed (removed redundant information), parse XML again with updated UUIDs
								$this->changedUUIDs = false;
								unset($jsonLDObj);
								unset($xpathsObj);
								$xpathsObj = new XMLjsonLD_XpathBasics;
								$jsonLDObj = new XMLjsonLD_Item;
								$jsonLDObj = $xpathsObj->URIconvert($itemURL , $jsonLDObj);
								$jsonLDObj->uri = $itemURL;
								$jsonLDObj->uri = $jsonLDObj->validateURI($jsonLDObj->uri);
								$this->assertionSort = 1;
								$this->saveContainmentData($jsonLDObj);
								$this->saveObservationData($jsonLDObj);
						  }
						  
						  if(!$this->changedUUIDs){
								$this->addManifest($jsonLDObj);
						  }
						  else{
								$errors[] = "$itemURL has inconsistent UUIDs";
						  }
						  
						  unset($jsonLDObj);
						  unset($xpathsObj);
						  
						  $doneURIs++;
						  $this->doneURIs = $doneURIs;
						  $output = $itemURL;
					 }
					 else{
						  $errors[] = "$itemURL has bad XML";
					 }
				}
				else{
					 $errors[] = "$itemURL cannot be found";
				}
		  
				if(!$output){
					 $this->addToDoList($itemUUID, "subject");
				}
				
				$this->noteErrors($errors);
		  }
		  else{
				$existingURIs++;
				$this->existingURIs = $existingURIs;
		  }
		  
		  
		  return $output;
	 }
	 
	 
	 //add subject item
	 function addMediaItem($itemUUID){
		 
		  $this->changedUUIDs = false;
		  $doneURIs = $this->doneURIs;
		  $existingURIs = $this->existingURIs;
		  $errors = array();
		  $itemURL = $this->retrieveBaseMediaURI.$itemUUID.".xml";
		  $output = false;
		  if(!$this->checkItemExits($itemUUID)){
				$db = $this->startDB();
				@$xmlString = file_get_contents($itemURL);
				if($xmlString != false){
					 
					 /*
					 $xmlString = str_replace('<?xml version="1.0"?>', '<?xml version="1.0" encoding="UTF-8" ?>', $xmlString);
					 
					 $xmlString = tidy_repair_string($xmlString,
										  array( 
												'doctype' => "omit",
												'input-xml' => true,
												'output-xml' => true 
										  ));
					 
					 @$itemXML = simplexml_load_string($xmlString);
					 
					 if(!$itemXML){
						  echo "here";
						  $xmlString = tidy_repair_string($xmlString,
										  array( 
												'doctype' => "omit",
												'input-xml' => true,
												'output-xml' => true 
										  ));
						  
						  @$itemXML = simplexml_load_string($xmlString);
						  if(!$itemXML){
								echo "bad XML ";
								echo $xmlString ;
								die;
						  }
						  
					 }
					 */
					 
					 @$itemXML = simplexml_load_string($xmlString);
					 
					 if($itemXML != false){
						  $jsonLDObj = new XMLjsonLD_Item;
						  $xpathsObj = new XMLjsonLD_XpathBasics;
						  $jsonLDObj = $xpathsObj->URIconvert($itemURL , $jsonLDObj);
						  $jsonLDObj->uri = $itemURL;
						  $jsonLDObj->uri = $jsonLDObj->validateURI($jsonLDObj->uri);
						  $this->assertionSort = 1;
						  $this->saveContainmentData($jsonLDObj);
						  $this->saveObservationData($jsonLDObj);
						 
						  if($this->changedUUIDs){
								//UUIDs changed (removed redundant information), parse XML again with updated UUIDs
								$this->changedUUIDs = false;
								unset($jsonLDObj);
								unset($xpathsObj);
								$xpathsObj = new XMLjsonLD_XpathBasics;
								$jsonLDObj = new XMLjsonLD_Item;
								$jsonLDObj = $xpathsObj->URIconvert($itemURL , $jsonLDObj);
								$jsonLDObj->uri = $itemURL;
								$jsonLDObj->uri = $jsonLDObj->validateURI($jsonLDObj->uri);
								$this->assertionSort = 1;
								$this->saveContainmentData($jsonLDObj);
								$this->saveObservationData($jsonLDObj);
						  }
						  
						  if(!$this->changedUUIDs){
								$this->addManifest($jsonLDObj);
						  }
						  else{
								$errors[] = "$itemURL has inconsistent UUIDs";
						  }
						  
						  unset($jsonLDObj);
						  unset($xpathsObj);
						  
						  $doneURIs++;
						  $this->doneURIs = $doneURIs;
						  $output = $itemURL;
					 }
					 else{
						  $errors[] = "$itemURL has bad XML";
					 }
				}
				else{
					 $errors[] = "$itemURL cannot be found";
				}
		  
				if(!$output){
					 $this->addToDoList($itemUUID, "media");
				}
				
				$this->noteErrors($errors);
		  }
		  else{
				$existingURIs++;
				$this->existingURIs = $existingURIs;
		  }
		  
		  
		  return $output;
	 }
	 
	 //add document
	 function addDocItem($itemUUID){
		 
		  $this->changedUUIDs = false;
		  $doneURIs = $this->doneURIs;
		  $existingURIs = $this->existingURIs;
		  $errors = array();
		  $itemURL = $this->retrieveBaseDocURI.$itemUUID.".xml";
		  $output = false;
		  if(!$this->checkItemExits($itemUUID)){
				$db = $this->startDB();
				@$xmlString = file_get_contents($itemURL);
				if($xmlString != false){
					 
					 /*
					 $xmlString = str_replace('<?xml version="1.0"?>', '<?xml version="1.0" encoding="UTF-8" ?>', $xmlString);
					 
					 $xmlString = tidy_repair_string($xmlString,
										  array( 
												'doctype' => "omit",
												'input-xml' => true,
												'output-xml' => true 
										  ));
					 
					 @$itemXML = simplexml_load_string($xmlString);
					 
					 if(!$itemXML){
						  echo "here";
						  $xmlString = tidy_repair_string($xmlString,
										  array( 
												'doctype' => "omit",
												'input-xml' => true,
												'output-xml' => true 
										  ));
						  
						  @$itemXML = simplexml_load_string($xmlString);
						  if(!$itemXML){
								echo "bad XML ";
								echo $xmlString ;
								die;
						  }
						  
					 }
					 */
					 
					 @$itemXML = simplexml_load_string($xmlString);
					 
					 if($itemXML != false){
						  $jsonLDObj = new XMLjsonLD_Item;
						  $xpathsObj = new XMLjsonLD_XpathBasics;
						  $jsonLDObj = $xpathsObj->URIconvert($itemURL , $jsonLDObj);
						  $jsonLDObj->uri = $itemURL;
						  $jsonLDObj->uri = $jsonLDObj->validateURI($jsonLDObj->uri);
						  $this->assertionSort = 1;
						  $this->saveContainmentData($jsonLDObj);
						  $this->saveObservationData($jsonLDObj);
						 
						  if($this->changedUUIDs){
								//UUIDs changed (removed redundant information), parse XML again with updated UUIDs
								$this->changedUUIDs = false;
								unset($jsonLDObj);
								unset($xpathsObj);
								$xpathsObj = new XMLjsonLD_XpathBasics;
								$jsonLDObj = new XMLjsonLD_Item;
								$jsonLDObj = $xpathsObj->URIconvert($itemURL , $jsonLDObj);
								$jsonLDObj->uri = $itemURL;
								$jsonLDObj->uri = $jsonLDObj->validateURI($jsonLDObj->uri);
								$this->assertionSort = 1;
								$this->saveContainmentData($jsonLDObj);
								$this->saveObservationData($jsonLDObj);
						  }
						  
						  if(!$this->changedUUIDs){
								$this->addManifest($jsonLDObj);
						  }
						  else{
								$errors[] = "$itemURL has inconsistent UUIDs";
						  }
						  
						  unset($jsonLDObj);
						  unset($xpathsObj);
						  
						  $doneURIs++;
						  $this->doneURIs = $doneURIs;
						  $output = $itemURL;
					 }
					 else{
						  $errors[] = "$itemURL has bad XML";
					 }
				}
				else{
					 $errors[] = "$itemURL cannot be found";
				}
		  
				if(!$output){
					 $this->addToDoList($itemUUID, "document");
				}
				
				$this->noteErrors($errors);
		  }
		  else{
				$existingURIs++;
				$this->existingURIs = $existingURIs;
		  }
		  
		  
		  return $output;
	 }
	 
	 
	 function addPersonItem($itemUUID){
		 
		  $this->changedUUIDs = false;
		  $doneURIs = $this->doneURIs;
		  $existingURIs = $this->existingURIs;
		  $errors = array();
		  $itemURL = $this->retrieveBasePersonURI.$itemUUID.".xml";
		  $output = false;
		  if(!$this->checkItemExits($itemUUID)){
				$db = $this->startDB();
				@$xmlString = file_get_contents($itemURL);
				if($xmlString != false){
					 
					 
					 $xmlString = str_replace('<?xml version="1.0"?>', '<?xml version="1.0" encoding="UTF-8" ?>', $xmlString);
					 
					 $xmlString = tidy_repair_string($xmlString,
										  array( 
												'doctype' => "omit",
												'input-xml' => true,
												'output-xml' => true 
										  ));
					 
					 @$itemXML = simplexml_load_string($xmlString);
					 /*
					 if(!$itemXML){
						  echo "here";
						  $xmlString = tidy_repair_string($xmlString,
										  array( 
												'doctype' => "omit",
												'input-xml' => true,
												'output-xml' => true 
										  ));
						  
						  @$itemXML = simplexml_load_string($xmlString);
						  if(!$itemXML){
								echo "bad XML ";
								echo $xmlString ;
								die;
						  }
						  
					 }
					 */
					 
					 @$itemXML = simplexml_load_string($xmlString);
					 
					 if($itemXML != false){
						  $jsonLDObj = new XMLjsonLD_Item;
						  $xpathsObj = new XMLjsonLD_XpathBasics;
						  $jsonLDObj = $xpathsObj->URIconvert($itemURL , $jsonLDObj);
						  $jsonLDObj->uri = $itemURL;
						  $jsonLDObj->uri = $jsonLDObj->validateURI($jsonLDObj->uri);
						  $this->assertionSort = 1;
						  $this->saveContainmentData($jsonLDObj);
						  $this->saveObservationData($jsonLDObj);
						 
						  if($this->changedUUIDs){
								//UUIDs changed (removed redundant information), parse XML again with updated UUIDs
								$this->changedUUIDs = false;
								unset($jsonLDObj);
								unset($xpathsObj);
								$xpathsObj = new XMLjsonLD_XpathBasics;
								$jsonLDObj = new XMLjsonLD_Item;
								$jsonLDObj = $xpathsObj->URIconvert($itemURL , $jsonLDObj);
								$jsonLDObj->uri = $itemURL;
								$jsonLDObj->uri = $jsonLDObj->validateURI($jsonLDObj->uri);
								$this->assertionSort = 1;
								$this->saveContainmentData($jsonLDObj);
								$this->saveObservationData($jsonLDObj);
						  }
						  
						  if(!$this->changedUUIDs){
								$this->addManifest($jsonLDObj);
						  }
						  else{
								$errors[] = "$itemURL has inconsistent UUIDs";
						  }
						  
						  unset($jsonLDObj);
						  unset($xpathsObj);
						  
						  $doneURIs++;
						  $this->doneURIs = $doneURIs;
						  $output = $itemURL;
					 }
					 else{
						  $errors[] = "$itemURL has bad XML";
					 }
				}
				else{
					 $errors[] = "$itemURL cannot be found";
				}
		  
				if(!$output){
					 $this->addToDoList($itemUUID, "person");
				}
				
				$this->noteErrors($errors);
		  }
		  else{
				$existingURIs++;
				$this->existingURIs = $existingURIs;
		  }
		  
		  
		  return $output;
	 }
	 
	 
	 function addProjectItem($itemUUID){
		 
		  $this->changedUUIDs = false;
		  $doneURIs = $this->doneURIs;
		  $existingURIs = $this->existingURIs;
		  $errors = array();
		  $itemURL = $this->retrieveBaseProjectURI.$itemUUID.".xml";
		  $output = false;
		  if(!$this->checkItemExits($itemUUID)){
				$db = $this->startDB();
				@$xmlString = file_get_contents($itemURL);
				if($xmlString != false){
					 
					 
					 $xmlString = str_replace('<?xml version="1.0"?>', '<?xml version="1.0" encoding="UTF-8" ?>', $xmlString);
					 
					 $xmlString = tidy_repair_string($xmlString,
										  array( 
												'doctype' => "omit",
												'input-xml' => true,
												'output-xml' => true 
										  ));
					 
					 @$itemXML = simplexml_load_string($xmlString);
					 /*
					 if(!$itemXML){
						  echo "here";
						  $xmlString = tidy_repair_string($xmlString,
										  array( 
												'doctype' => "omit",
												'input-xml' => true,
												'output-xml' => true 
										  ));
						  
						  @$itemXML = simplexml_load_string($xmlString);
						  if(!$itemXML){
								echo "bad XML ";
								echo $xmlString ;
								die;
						  }
						  
					 }
					 */
					 
					 @$itemXML = simplexml_load_string($xmlString);
					 
					 if($itemXML != false){
						  $jsonLDObj = new XMLjsonLD_Item;
						  $xpathsObj = new XMLjsonLD_XpathBasics;
						  $jsonLDObj = $xpathsObj->URIconvert($itemURL , $jsonLDObj);
						  $jsonLDObj->uri = $itemURL;
						  $jsonLDObj->uri = $jsonLDObj->validateURI($jsonLDObj->uri);
						  $this->assertionSort = 1;
						  $this->saveContainmentData($jsonLDObj);
						  $this->saveObservationData($jsonLDObj);
						  $this->saveProjectDC($jsonLDObj);
						 
						  if($this->changedUUIDs){
								//UUIDs changed (removed redundant information), parse XML again with updated UUIDs
								$this->changedUUIDs = false;
								unset($jsonLDObj);
								unset($xpathsObj);
								$xpathsObj = new XMLjsonLD_XpathBasics;
								$jsonLDObj = new XMLjsonLD_Item;
								$jsonLDObj = $xpathsObj->URIconvert($itemURL , $jsonLDObj);
								$jsonLDObj->uri = $itemURL;
								$jsonLDObj->uri = $jsonLDObj->validateURI($jsonLDObj->uri);
								$this->assertionSort = 1;
								$this->saveContainmentData($jsonLDObj);
								$this->saveObservationData($jsonLDObj);
								$this->saveProjectDC($jsonLDObj);
						  }
						  
						  /*
						  if(!$this->changedUUIDs){
								$this->addManifest($jsonLDObj);
						  }
						  else{
								$errors[] = "$itemURL has inconsistent UUIDs";
						  }
						  
						  unset($jsonLDObj);
						  unset($xpathsObj);
						  
						  $doneURIs++;
						  $this->doneURIs = $doneURIs;
						  $output = $itemURL;
						  
						  */
					 }
					 else{
						  $errors[] = "$itemURL has bad XML";
					 }
				}
				else{
					 $errors[] = "$itemURL cannot be found";
				}
		  
				if(!$output){
					 $this->addToDoList($itemUUID, "project");
				}
				
				$this->noteErrors($errors);
		  }
		  else{
				$existingURIs++;
				$this->existingURIs = $existingURIs;
		  }
		  
		  
		  return $output;
	 }
	 
	 
	 
	 //adds the item to the Manifest list, and saves the cached data
	 function addManifest($LinkedDataItem){
		  
		  $ocGenObj = new OCitems_General;
		  
		  $data = array();
		  $data["uuid"] = $LinkedDataItem->uuid;
		  $data["projectUUID"] = $LinkedDataItem->projectUUID;
		  $data["sourceID"] = self::defaultSourceID;
		  $data["itemType"] = $LinkedDataItem->itemType;
		  $data["repo"] = false;
		  $data["classURI"] = $ocGenObj->abbreviateURI($LinkedDataItem->itemClassURI);
		  $data["label"] = trim($LinkedDataItem->label);
		  $data["desPropUUID"] = false;
		  $data["published"] = $LinkedDataItem->published;
		  
		  $manifestObj = new OCitems_Manifest;
		  $ok = $manifestObj->createRecord($data);
		  if($ok){
				
				if($LinkedDataItem->fullURI){
					 $this->mediaFileSave($LinkedDataItem); //save the media file.
				}
				if($LinkedDataItem->documentContents){
					 $this->docFileSave($LinkedDataItem); //save the document contents
				}
				if($LinkedDataItem->itemType == "person"){
					 $this->personFileSave($LinkedDataItem); //save the document contents
				}
				$JSONld = $LinkedDataItem->makeJSON_LD();
				/*
				$compactObj = new XMLjsonLD_CompactXML;
				$doc = $compactObj->makeCompactXML($JSONld);
				$xmlString = $doc->saveXML();
				*/
				$jsonString = $ocGenObj->JSONoutputString($JSONld);
				unset($JSONld);
				unset($data);
				$data = array();
				$data["uuid"] = $LinkedDataItem->uuid;
				$data["created"] = date("Y-m-d H:i:s");
				$data["content"] = $jsonString;
				
				$dataCacheObj = new OCitems_DataCache;
				$okCache = $dataCacheObj->createRecord($data);
				unset($dataCacheObj);
				if(!$okCache){
					 $errors = array();
					 $errors[] = "Count not cache ".$LinkedDataItem->uuid;
					 $this->noteErrors($errors);
				}
		  }
		  
	 }
	 
	 //media file save
	 function mediaFileSave($LinkedDataItem){
		  $mediaFileObj = new OCitems_MediaFile;
		  
		  $data = array();
		  $data["uuid"] = $LinkedDataItem->uuid;
		  $data["projectUUID"] = $LinkedDataItem->projectUUID;
		  $data["sourceID"] = self::defaultSourceID;
		  $data["mediaType"] = $LinkedDataItem->mediaType;
		  $data["mimeTypeURI"] = $LinkedDataItem->mimeTypeURI;
		  $data["thumbMimeURI"] = $LinkedDataItem->thumbMimeURI;
		  $data["thumbURI"] = $LinkedDataItem->thumbURI;
		  $data["previewMimeURI"] = $LinkedDataItem->previewMimeURI;
		  $data["previewURI"] = $LinkedDataItem->previewURI;
		  $data["fullURI"] = $LinkedDataItem->fullURI;
		  $data["fileSize"] = $LinkedDataItem->fileSize;
		 
		  $mediaFileObj->createRecord($data);
	 }
	 
	 //media file save
	 function docFileSave($LinkedDataItem){
		  $docObj = new OCitems_Document;
		  
		  $data = array();
		  $data["uuid"] = $LinkedDataItem->uuid;
		  $data["projectUUID"] = $LinkedDataItem->projectUUID;
		  $data["sourceID"] = self::defaultSourceID;
		  $data["content"] = $LinkedDataItem->documentContents;
		 
		  $docObj->createRecord($data);
	 }
	 
	 //media file save
	 function personFileSave($LinkedDataItem){
		  $persObj = new OCitems_Person;
		  
		  $data = array();
		  $data["uuid"] = $LinkedDataItem->uuid;
		  $data["projectUUID"] = $LinkedDataItem->projectUUID;
		  $data["sourceID"] = self::defaultSourceID;
		  $data["foafType"] = $LinkedDataItem->foafType;
		  $data["combined_name"] = $LinkedDataItem->label;
		  $data["given_name"] = $LinkedDataItem->givenName;
		  $data["surname"] = $LinkedDataItem->surname;
		 
		  $persObj->createRecord($data);
	 }
	 

	 //saves observation data
	 function saveObservationData($LinkedDataItem){
		  
		  if(is_array($LinkedDataItem->observations)){
				$projectUUID = $LinkedDataItem->projectUUID;
				$published = $LinkedDataItem->published;
				
				foreach($LinkedDataItem->observations as $obsNumKey => $observation){
					 $obsNode = "#obs-".$obsNumKey;
					 $sourceID = $observation["sourceID"];
					 if(isset($observation["properties"])){
						  if(is_array($observation["properties"])){
								foreach($observation["properties"] as $varURI => $actProps){
									 $firstLoop = true;
									 foreach($actProps as $actProperty){
										  $actProperty = $this->savePropertyText($actProperty, $projectUUID);
										  $this->saveProperty($varURI, $actProperty, $projectUUID);
										  if($firstLoop){
												$this->saveVarPredicate($varURI, $actProperty, $published, $projectUUID);
										  }
										  $firstLoop = false;
										  $this->savePropertyAssertion($LinkedDataItem, $obsNumKey, $obsNode, $sourceID, $varURI, $actProperty);
									 }
								}
						  }//end case with am array of properties
					 }//end case with properties
							
					 if(isset($observation["notes"])){
						  if(is_array($observation["notes"])){
								foreach($observation["notes"] as $textContent){
									 $stringUUID = $this->saveText($textContent, $projectUUID);
									 $this->saveNoteAssertion($LinkedDataItem, $obsNumKey, $obsNode, $sourceID, $stringUUID);
								}
						  }//end case with array of notes
					 }//end case with notes
					 
					 if(isset($observation["links"])){
						  if(is_array($observation["links"])){
								foreach($observation["links"] as $predicateURIkey => $objectURIs){
									 foreach($objectURIs as $objectURI){
										  $this->saveLinkAssertion($LinkedDataItem, $obsNumKey, $obsNode, $sourceID, $predicateURIkey, $objectURI);
									 }
								}
						  }
					 }
				}
		  }//end case where observations array exists
	 }
	 
	 //saves containment data
	 function saveContainmentData($LinkedDataItem){
		  
		  if(is_array($LinkedDataItem->contexts)){
				$contextTree = 1;
				foreach($LinkedDataItem->contexts as $treeKey => $contextList){
					 
					 $obsNumKey = $contextTree;
					 $obsNode = "#contents-".$contextTree;
					 $sourceID = self::defaultSourceID;
					 $predicateUUID = self::containsPredicateUUID;
					 if(is_array($contextList)){
						  if(count($contextList)>0){
								if(isset($contextList[count($contextList) - 1])){
									 $lastParent = $contextList[count($contextList) - 1];
									 $lastParentURI = $lastParent["id"];
									 $this->saveLinkAssertion($LinkedDataItem, $obsNumKey, $obsNode, $sourceID, $predicateUUID, $LinkedDataItem->uri, $lastParentURI);
								}
						  }
					 }
					 
					 $contextTree++;
				}
		  }
		  
		  if(is_array($LinkedDataItem->children)){
				$contextTree = 1;
				foreach($LinkedDataItem->children as $treeKey => $childrenList){
					 
					 $obsNumKey = $contextTree;
					 $obsNode = "#contents-".$contextTree;
					 $sourceID = self::defaultSourceID;
					 $predicateUUID = self::containsPredicateUUID;
					 if(is_array($childrenList)){
						  foreach($childrenList as $child){
								
								$childURI =  $child["id"];
								$this->saveLinkAssertion($LinkedDataItem, $obsNumKey, $obsNode, $sourceID, $predicateUUID, $childURI);
						  }
					 }
					 
					 $contextTree++;
				}
		  }
	 }
	 
	
	
	 //save a predicate record for a variable
	 function saveVarPredicate($varURI, $actProperty, $published, $projectUUID){
		  
		  $predicateObj = new OCitems_Predicate;
		  $predicateUUID = $this->makeUUIDfromURI($varURI);
		  if(!$predicateObj->getByUUID($predicateUUID)){
				
				$varLabel = $actProperty["varLabel"];
				$dataType = false;
				if(isset($actProperty["id"])){
					 $dataType = "uri";
				}
				else{
					 if(isset($actProperty["type"])){
						  $dataType = $actProperty["type"];
					 }
				}
				
				$data = array("uuid" => $predicateUUID,
								  "projectUUID" => $projectUUID,
								  "sourceID" => self::defaultSourceID,
								  "archaeoMLtype" => "variable",
								  "dataType" => $dataType,
								  "label" => $varLabel,
								  "created" => $published
								  );
				
				$ok = $predicateObj->createRecord($data);
		  }
	 }
	
	
	 //save a property record
	 function saveProperty($varURI, $actProperty, $projectUUID){
		  
		  $output = false;
		  if(isset($actProperty["id"])){
				$predicateUUID = $this->makeUUIDfromURI($varURI);
				$propUUID = $actProperty["propUUID"];
				$typeLabel = $actProperty["value"];
				
				$ocTypeObj = new OCitems_Type;
				$existUUID = $ocTypeObj->getByLabel($predicateUUID, $typeLabel);
				if(!$existUUID){
					 
					 $data = array("uuid" => $propUUID,
										"projectUUID" => $projectUUID,
										"sourceID" => self::defaultSourceID,
										"predicateUUID" => $predicateUUID,
										"label" => $typeLabel
										);
					 
					 if(strlen($typeLabel)>200){
						  $data["label"] = substr($typeLabel, 0, 200);
						  $data["note"] = $typeLabel;
					 }
					 
					 
					 $output = $ocTypeObj->createRecord($data);
				
				}
				else{
					 $this->registerUUIDchange($propUUID, $existUUID["uuid"], "property");
				}
		  }
		  return   $output;
	 }
	 
	 
	 //save text literal of an alphanumeric property
	 function savePropertyText($actProperty, $projectUUID){
		  
		  $output = false;
		  if(isset($actProperty["xsd:string"])){
				$textContent = $actProperty["xsd:string"];
				if(isset( $actProperty["valueID"])){
					 $textUUID = $actProperty["valueID"];
					 $actProperty["valueID"] = $this->saveText($textContent, $projectUUID, $textUUID);
				}
				else{
					 $actProperty["valueID"] = $this->saveText($textContent, $projectUUID);
				}
		  }
		  return   $actProperty;
	 }
	 
	 //save an assertion for the current property
	 function savePropertyAssertion($LinkedDataItem, $obsNumKey, $obsNode, $sourceID, $varURI, $actProperty){ 
		  if(!$this->changedUUIDs){
				$data = array();
				$data["uuid"] = $LinkedDataItem->uuid;
				$data["projectUUID"]= $LinkedDataItem->projectUUID;
				$data["sourceID"]= $sourceID;
				$data["subjectType"]= $LinkedDataItem->itemType;
				$data["obsNode"]= $obsNode;
				$data["obsNum"]= $obsNumKey;
				$data["sort"]= $this->assertionSort;
				$data["visibility"]= 0;
				$data["predicateUUID"] = $this->makeUUIDfromURI($varURI);
				$data["objectUUID"] = false;
				$data["dataNum"] = false;
				$data["dataDate"] = false;
				
				if(isset($actProperty["id"])){
					 $data["objectUUID"] = $actProperty["propUUID"];
					 $data["objectType"] = "property";
				}
				elseif(isset($actProperty["xsd:string"])){
					 if(!isset($actProperty["valueID"])){
						  $data["objectUUID"] = $this->saveText($actProperty["xsd:string"], $LinkedDataItem->projectUUID);
					 }
					 else{
						  $data["objectUUID"] = $actProperty["valueID"];
					 }
					
					 $data["objectType"] = $actProperty["type"];
				}
				else{
					 $data["objectType"] = $actProperty["type"];
					 if(array_key_exists(self::booleanLiteral, $actProperty)){
						  $data["dataNum"] = $actProperty[self::booleanLiteral];
					 }
					 elseif(array_key_exists(self::integerLiteral, $actProperty)){
						  $data["dataNum"] = $actProperty[self::integerLiteral];
					 }
					 elseif(array_key_exists(self::decimalLiteral, $actProperty)){
						  $data["dataNum"] = $actProperty[self::decimalLiteral];
					 }
					 elseif(array_key_exists(self::dateLiteral, $actProperty)){
						  $data["dataDate"] = $actProperty[self::dateLiteral];
					 }
				}
				
				$assertionsObj = new OCitems_Assertions;
				$assertionsObj->createRecord($data);
				$this->assertionSort++;
		  }
	 }
	 
	 
	 //save an assertion for the current property
	 function saveNoteAssertion($LinkedDataItem, $obsNumKey, $obsNode, $sourceID, $stringUUID){ 
		  if(!$this->changedUUIDs){
				$data = array();
				$data["uuid"] = $LinkedDataItem->uuid;
				$data["projectUUID"]= $LinkedDataItem->projectUUID;
				$data["sourceID"]= $sourceID;
				$data["subjectType"]= $LinkedDataItem->itemType;
				$data["obsNode"]= $obsNode;
				$data["obsNum"]= $obsNumKey;
				$data["sort"]= $this->assertionSort;
				$data["visibility"]= 0;
				$data["predicateUUID"] = self::notePredicateUUID;
				$data["objectUUID"] = $stringUUID;
				$data["objectType"] = self::stringLiteral;
				$data["dataNum"] = false;
				$data["dataDate"] = false;
				$assertionsObj = new OCitems_Assertions;
				$assertionsObj->createRecord($data);
				$this->assertionSort++;
		  }
	 }
	 
	 
	 //save an assertion for the current property
	 function saveLinkAssertion($LinkedDataItem, $obsNumKey, $obsNode, $sourceID, $predicateURI, $objectURI, $subjectURI = false){ 
		  if(!$this->changedUUIDs){
				$data = array();
				if(!$subjectURI){
					 $data["uuid"] = $LinkedDataItem->uuid;
					 $data["subjectType"]= $LinkedDataItem->itemType;
				}
				else{
					 $data["uuid"] = $this->makeUUIDfromURI($subjectURI);
					 $data["subjectType"]= $this->makeTypeFromURI($subjectURI);
				}
				$data["projectUUID"]= $LinkedDataItem->projectUUID;
				$data["sourceID"]= $sourceID;
				$data["obsNode"]= $obsNode;
				$data["obsNum"]= $obsNumKey;
				$data["sort"]= $this->assertionSort;
				$data["visibility"]= 0;
				$data["predicateUUID"] = $this->makeUUIDfromURI($predicateURI);
				$data["objectUUID"] = $this->makeUUIDfromURI($objectURI);
				$data["objectType"] = $this->makeTypeFromURI($objectURI);
				$data["dataNum"] = false;
				$data["dataDate"] = false;
				$assertionsObj = new OCitems_Assertions;
				$assertionsObj->createRecord($data);
				$this->assertionSort++;
		  }
	 }
	 
	 
	 
	 //save a text literal
	 function saveText($textContent, $projectUUID, $textUUID = false){
		  $textContent = trim($textContent);
		  $output = false;
		  $stringObj = new OCitems_String;
		  $existUUID = $stringObj->getByContent($textContent, $projectUUID);
		  if(!$existUUID){
				
				if(!$textUUID){
					 $genObj = new OCitems_General;
					 $textUUID = $genObj->generateUUID();
				}
				
				$data = array("uuid" => $textUUID,
								  "projectUUID" => $projectUUID,
								  "sourceID" => self::defaultSourceID,
								  "content" => $textContent
								  );
				
				$output = $stringObj->createRecord($data);
				
		  }
		  else{
				$output =  $existUUID["uuid"];
				if($textUUID != false){
					 $this->registerUUIDchange($textUUID,  $existUUID["uuid"], "string");
				}
		  }
		  
		  return   $output;
	 }
	 
	 
	 //register change in UUIDs
	 
	 function saveProjectDC($LinkedDataItem){
		  if($LinkedDataItem->itemType == "project"){
				$linkAnnotObj = new Links_linkAnnotation;
				if(is_array($LinkedDataItem->creators)){
					 foreach($LinkedDataItem->creators as $pURI){
						  $data = array("uuid" => $LinkedDataItem->uuid,
											 "subjectType" => $LinkedDataItem->itemType,
											 "projectUUID" => $LinkedDataItem->projectUUID,
											 "sourceID" => self::defaultSourceID,
											 "objectURI" => $pURI,
											 "creatorUUID" => false);
						  $linkAnnotObj->createDCcreatorRecord($data);
					 }
				}
				if(is_array($LinkedDataItem->contributors)){
					 foreach($LinkedDataItem->contributors as $pURI){
						  $data = array("uuid" => $LinkedDataItem->uuid,
											 "subjectType" => $LinkedDataItem->itemType,
											 "projectUUID" => $LinkedDataItem->projectUUID,
											 "sourceID" => self::defaultSourceID,
											 "objectURI" => $pURI,
											 "creatorUUID" => false);
						  $linkAnnotObj->createDCcontributorRecord($data);
					 }
				}
		  }
	 }
	 
	 
	 function registerUUIDchange($oldUUID, $newUUID, $type){
		  if($newUUID != false && $newUUID != $oldUUID){
				$data = array("oldUUID" => $oldUUID, "newUUID" => $newUUID, "type" => $type);
				$LegacyIDobj = new OCitems_LegacyIDs;
				$new = $LegacyIDobj->createRecord($data);
				if($new){
					 $this->changedUUIDs = true; //uuids changed
				}
		  }
	 }
	 
	 //make a UUID from a URI
	 function makeUUIDfromURI($uri){
		  if(stristr($uri, "http://")){
				$genObj = new OCitems_General;
				return  $genObj->itemUUIDfromURI($uri);
		  }
		  else{
				return $uri;
		  }
	 }
	 
	 //make an item Type from a URI
	 function makeTypeFromURI($uri){
		  if(stristr($uri, "http://")){
				$genObj = new OCitems_General;
				return  $genObj->itemTypeFromURI($uri);
		  }
		  else{
				return $uri;
		  }
	 }
	 
	 function noteErrors($errors){
		  if(is_array($errors)){
				if(count($errors)>0){
					 if(!is_array($this->errors)){
						  $this->errors = $errors;
					 }
					 else{
						  $allErrors = $this->errors;
						  foreach($errors as $newError){
								$allErrors[] = $newError;
						  }
						  $this->errors = $allErrors;
					 }
				}
		  }
	 }
	 
	 
	 function checkItemExits($itemUUID){
		  $manifestObj = new OCitems_Manifest;
		  
		  if(!$manifestObj->getByUUID($itemUUID)){
				return false;
		  }
		  else{
				return true;
		  }
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
