<?php
/* This class creates files based on data saved for export.
 * It makes csv, zip (with csv), and gzipped csv files.
 * 
 */

class XMLjsonLD_XpathBasics  {
    
	 public $db; //database connection object
	 public $itemXML;
	 
	 public $projectUUID; //current project ID
	 public $sourceID; //current sourceID
	 
	 public $dataInserts; //save data to the database
	 
	 const subjectBaseURI = "http://opencontext.org/subjects/";
	 
	 const predicateBaseURI = "http://opencontext.org/predicates/";
	 const propertyBaseURI = "http://opencontext.org/properties/";
	 const projectBaseURI = "http://opencontext.org/projects/";
	 
	 const integerLiteral = "xsd:integer";
	 const decimalLiteral = "xsd:double";
	 const booleanLiteral = "xsd:boolean";
	 const dateLiteral = "xsd:date";
	 const stringLiteral = "xsd:string";
	 
	 
	 function URIconvert($uri, $LinkedDataItem){
		  
		  $genObj = new OCitems_General;
		  $LinkedDataItem->itemType =  $genObj->itemTypeFromURI($uri);
		  
		  if(!stristr($uri, ".xml")){
				$uri = $uri.".xml";
		  }
		  @$itemXMLstring = file_get_contents($uri);
		  if($itemXMLstring != false){
				
				$xpathObj = new XMLjsonLD_XpathBasics;
				if(stristr($uri, "subjects")){
					 $itemXML = simplexml_load_string($itemXMLstring);
					 $itemXML->registerXPathNamespace("arch", OpenContext_OCConfig::get_namespace("arch", "spatial"));
					 $itemXML->registerXPathNamespace("oc", OpenContext_OCConfig::get_namespace("oc", "spatial"));
					 $itemXML->registerXPathNamespace("dc", OpenContext_OCConfig::get_namespace("dc"));
					 $itemXML->registerXPathNamespace("gml", OpenContext_OCConfig::get_namespace("gml"));
					
					 $LinkedDataItem = $this->XMLsubjectItemBasics($LinkedDataItem, $itemXML);
					 $LinkedDataItem = $this->XMLtoContextData($LinkedDataItem, $itemXML);
					 $LinkedDataItem = $this->XMLtoChildren($LinkedDataItem, $itemXML);
				}
				elseif(stristr($uri, "media") || stristr($uri, "documents")){
					 $itemXML = simplexml_load_string($itemXMLstring);
					 $itemXML->registerXPathNamespace("oc", OpenContext_OCConfig::get_namespace("oc", "media"));
					 $itemXML->registerXPathNamespace("arch", OpenContext_OCConfig::get_namespace("arch", "media"));
					 $itemXML->registerXPathNamespace("dc", OpenContext_OCConfig::get_namespace("dc"));
					 $itemXML->registerXPathNamespace("gml", OpenContext_OCConfig::get_namespace("gml"));
					
					 $LinkedDataItem = $this->XMLmediaItemBasics($LinkedDataItem, $itemXML);
				}
				elseif(stristr($uri, "projects")){
					 $itemXML = simplexml_load_string($itemXMLstring);
					 $itemXML->registerXPathNamespace("oc", OpenContext_OCConfig::get_namespace("oc", "project"));
					 $itemXML->registerXPathNamespace("arch", OpenContext_OCConfig::get_namespace("arch", "project"));
					 $itemXML->registerXPathNamespace("dc", OpenContext_OCConfig::get_namespace("dc"));
					 $itemXML->registerXPathNamespace("gml", OpenContext_OCConfig::get_namespace("gml"));
					 $LinkedDataItem = $this->XMLprojectItemBasics($LinkedDataItem, $itemXML);
				}
				elseif(stristr($uri, "persons")){
					 $itemXML = simplexml_load_string($itemXMLstring);
					 $itemXML->registerXPathNamespace("oc", OpenContext_OCConfig::get_namespace("oc", "person"));
					 $itemXML->registerXPathNamespace("arch", OpenContext_OCConfig::get_namespace("arch", "person"));
					 $itemXML->registerXPathNamespace("dc", OpenContext_OCConfig::get_namespace("dc"));
					 $itemXML->registerXPathNamespace("gml", OpenContext_OCConfig::get_namespace("gml"));
					 $LinkedDataItem = $this->XMLpersonItemBasics($LinkedDataItem, $itemXML);
				}
				
				$LinkedDataItem = $this->XMLtoLocation($LinkedDataItem, $itemXML);
				$LinkedDataItem = $this->XMLtoChronology($LinkedDataItem, $itemXML);
				$LinkedDataItem = $this->XMLbasicItemMetadata($LinkedDataItem, $itemXML);
				$LinkedDataItem = $this->XMLtoObservationsData($LinkedDataItem, $itemXML);
				return $LinkedDataItem;
		  }
		  else{
				return false;
		  }
	 }
	
	 
	 
	 function XMLbasicItemMetadata($LinkedDataItem, $itemXML){
		  
		  // get the publication date (the date items are added to Open Context).
		  if(!$LinkedDataItem->published){
				foreach($itemXML->xpath("//oc:metadata/oc:pub_date") as $published) {
					 // Format the date as UTC (Solr requires this) 
					 $published = date("Y-m-d", strtotime($published));
					 $LinkedDataItem->published = $published;
				}
		  }
		  if(!$LinkedDataItem->published){
				foreach($itemXML->xpath("//oc:metadata/dc:date") as $published) {
					 // Format the date as UTC (Solr requires this) 
					 $published = date("Y-m-d", strtotime($published));
					 $LinkedDataItem->published = $published;
				}
		  }
		  
		  $contributors = array();
		  foreach($itemXML->xpath("//oc:metadata/dc:contributor/@href") as $xRes) { 
				$contributors[] = (string)$xRes;
				$LinkedDataItem->contributors = $contributors;
		  }
		  
		  $creators = array();
		  foreach($itemXML->xpath("//oc:metadata/dc:creator/@href") as $xRes) { 
				$creators[] = (string)$xRes;
				$LinkedDataItem->creators = $creators;
		  }
		  
		  foreach($itemXML->xpath("//oc:metadata/oc:copyright_lic/@href") as $xRes) { 
				$LinkedDataItem->license = (string)$xRes;
		  }
		  
		  if(!$LinkedDataItem->license){
				$LinkedDataItem->license  = "http://creativecommons.org/licenses/by/4.0/";
		  }
		  
		  return $LinkedDataItem;
	 }
	
	 function XMLsubjectItemBasics($LinkedDataItem, $spatialItem){
		
		  // get the item's UUID
		  foreach($spatialItem->xpath("//arch:spatialUnit/@UUID") as $spaceid) {
				$UUID = (string)$spaceid;
				$LinkedDataItem->uuid = $UUID; // add it to the Open Contex item
		  }
	  
		  // get the item_label
		  foreach ($spatialItem->xpath("//arch:spatialUnit/arch:name/arch:string") as $item_label) {
			  $item_label = (string)$item_label;
			  $item_label = trim($item_label);
			  $LinkedDataItem->label  = $item_label;
			  
		  }//end loop for item labels
	  
		  foreach($spatialItem->xpath("//arch:spatialUnit/@ownedBy") as $projUUID) {
			  $projUUID = (string)$projUUID;
			  $LinkedDataItem->projectUUID  = $projUUID;
			  $LinkedDataItem->projectURI = self::projectBaseURI.$projUUID;
			  $this->projectUUID =  $projUUID;
		  }
	  
		  // get the item class
		  foreach ($spatialItem->xpath("//arch:spatialUnit/oc:item_class/oc:name") as $item_class) {
			  $item_class = (string)$item_class;
			  $LinkedDataItem->assignSubjectClass($item_class);
		  }
	  
		  return $LinkedDataItem;
	
	}//end reindex function
	
	
	
	function XMLmediaItemBasics($LinkedDataItem, $mediaItem){
		
		  // get the item's UUID
		  foreach($mediaItem->xpath("//arch:resource/@UUID") as $media_id) {
				$UUID = (string)$media_id;
				$LinkedDataItem->uuid = $UUID; // add it to the Open Contex item
		  }
		
		  //get item types
		  foreach($mediaItem->xpath("//arch:resource/@type") as $media_type) {
				$media_type = strtolower($media_type);
				$LinkedDataItem->mediaType = $media_type;
		  }
		
		  // get the publication date (the date items are added to Open Context).
		  foreach($mediaItem->xpath("//arch:resource/arch:DublinCoreMetadata/arch:Date") as $published) {
				// Format the date as UTC (Solr requires this) 
			  $published = date("Y-m-d\TH:i:s\Z", strtotime($published));
			  $LinkedDataItem->published = $published;
		  }
		  
		  // get the item_label
		  foreach ($mediaItem->xpath("//arch:resource/arch:name/arch:string") as $item_label) {
			  $item_label = (string)$item_label;
			  $item_label = trim($item_label);
			  $LinkedDataItem->label  = $item_label;
		  }//end loop for item labels
	
		  foreach($mediaItem->xpath("//arch:resource/@ownedBy") as $projUUID) {
				$projUUID = (string)$projUUID;
				$LinkedDataItem->projectUUID  = $projUUID;
				$LinkedDataItem->projectURI = self::projectBaseURI.$projUUID;
				$this->projectUUID =  $projUUID;
		  }

		  //for documents / diaries
		  if ($mediaItem->xpath("//arch:internalDocument/arch:string")) {
				$LinkedDataItem->itemType = "document";
				$mediaItem->registerXPathNamespace("xhtml", OpenContext_OCConfig::get_namespace("xhtml"));
				if($mediaItem->xpath("//arch:internalDocument/arch:string/xhtml:div")){
					 foreach ($mediaItem->xpath("//arch:internalDocument/arch:string/xhtml:div") as $divNote) {
						 $docContent = $divNote->asXML();
						 $LinkedDataItem->documentContents = $docContent; //add notes
					 }
				}
				else{
					 foreach ($mediaItem->xpath("//arch:internalDocument/arch:string") as $docContent) {
						 $docContent = (string)$docContent;
						 $LinkedDataItem->documentContents = $docContent; //add notes
					 }
				}
		  }
		  
		  if ($mediaItem->xpath("//arch:externalFileInfo")) {
				//media resource
				$legacyMimeTypeObj = new XMLjsonLD_LegacyMimeType;
				$mediaFileObj = new OCitems_MediaFile;
				
				$LinkedDataItem->mimeTypeURI = false;
				$legacyMimeType = false;
				if($mediaItem->xpath("//arch:externalFileInfo/arch:fileFormat")){
					 foreach ($mediaItem->xpath("//arch:externalFileInfo/arch:fileFormat") as $xpathRes) {
						 $legacyMimeType = (string)$xpathRes;
					 }
				}
				foreach ($mediaItem->xpath("//arch:externalFileInfo/arch:resourceURI") as $xpathRes) {
					 $LinkedDataItem->fullURI = (string)$xpathRes;
					 $LinkedDataItem->mimeTypeURI = $legacyMimeTypeObj->getMimeTypeURI($legacyMimeType, $LinkedDataItem->fullURI);
					 $LinkedDataItem->mediaType = $legacyMimeTypeObj->getGeneralMediaType($LinkedDataItem->mimeTypeURI);
					 $LinkedDataItem->fileSize = $mediaFileObj->remote_filesize($LinkedDataItem->fullURI);
				}
				foreach ($mediaItem->xpath("//arch:externalFileInfo/arch:previewURI") as $xpathRes) {
					 $LinkedDataItem->previewURI = (string)$xpathRes;
					 $LinkedDataItem->previewMimeURI = $legacyMimeTypeObj->getMimeTypeURI(false, $LinkedDataItem->previewURI);
				}
				foreach ($mediaItem->xpath("//arch:externalFileInfo/arch:thumbnailURI") as $xpathRes) {
					 $LinkedDataItem->thumbURI = (string)$xpathRes;
					 $LinkedDataItem->thumbMimeURI = $legacyMimeTypeObj->getMimeTypeURI(false, $LinkedDataItem->thumbURI);
				}
				
		  }
		  
		  return 	$LinkedDataItem;
	}//end function



	function XMLprojectItemBasics($LinkedDataItem, $itemXML){
		
		  // get the item's UUID
		  foreach($itemXML->xpath("//arch:project/@UUID") as $media_id) {
				$UUID = (string)$media_id;
				$LinkedDataItem->uuid = $UUID; // add it to the Open Contex item
		  }

		  // get the item_label
		  foreach ($itemXML->xpath("//arch:project/arch:name/arch:string") as $item_label) {
			  $item_label = (string)$item_label;
			  $item_label = trim($item_label);
			  $LinkedDataItem->label  = $item_label;
		  }//end loop for item labels
	
		  foreach($itemXML->xpath("//arch:project/@ownedBy") as $projUUID) {
			  $projUUID = (string)$projUUID;
			  $LinkedDataItem->projectUUID  = $projUUID;
			  $LinkedDataItem->projectURI = self::projectBaseURI.$projUUID;
			  $this->projectUUID =  $projUUID;
		  }
	
		  foreach ($itemXML->xpath("//oc:manage_info/oc:projGeoPoint") as $projectGeo) {
			  $projectGeo = (string)$projectGeo;
			  $geoArray = explode(" ", $projectGeo);
			  $LinkedDataItem->latitude = $geoArray[0];
			  $LinkedDataItem->longitude =$geoArray[1];  //lat, lon
		  }
	
		  if($itemXML->xpath("//oc:metadata/dc:identifier/@type")){
				
				$idObj = new OCitems_Identifiers;
				$stableType = false;
				foreach ($itemXML->xpath("//oc:metadata/dc:identifier/@type") as $stableType) {
					 $stableType = (string)$stableType;
					 
					 foreach ($itemXML->xpath("//oc:metadata/dc:identifier[@type = '$stableType']") as $stableID) {
						  $stableID = (string)$stableID;
						  $data = array("uuid" => $LinkedDataItem->uuid,
											 "projectUUID" => $LinkedDataItem->projectUUID,
											 "itemType" => "project",
											 "stableID" => $stableID,
											 "stableType" => $stableType
											 );
						  
						  $idObj->createRecord($data);
					 }//end loop for item labels
				}
		  }
	
		  $DCobj = new Links_tempDC;
		  foreach ($itemXML->xpath("//oc:metadata/dc:subject") as $subject) {
				$subject = (string)$subject;
				$subject = strtolower($subject);
				if(strstr($subject, ",")){
					 $subjects = explode(",", $subject);
				}
				else{
					 $subjects = array($subject);
				}
				foreach($subjects as $actSubject){
					 $actSubject = trim($subject);
					 if(strlen( $actSubject)>1){
						  $data = array("term" =>  $actSubject, "type" => false, "uri" => false);
						  $DCobj->createRecord($data);
					 }
				}
		  }
		  foreach ($itemXML->xpath("//oc:metadata/dc:coverage") as $subject) {
				$subject = (string)$subject;
				$subject = strtolower($subject);
				if(strstr($subject, ",")){
					 $subjects = explode(",", $subject);
				}
				else{
					 $subjects = array($subject);
				}
				foreach($subjects as $actSubject){
					 $actSubject = trim($subject);
					 if(strlen( $actSubject)>1){
						  $data = array("term" =>  $actSubject, "type" => false, "uri" => false);
						  $DCobj->createRecord($data);
					 }
				}
		  }
	
	
		  return 	$LinkedDataItem;
	}//end function


	
	/*
	This function gets information from person items
	*/
	function XMLpersonItemBasics($LinkedDataItem, $itemXML){
		
		  // get the item's UUID
		  foreach($itemXML->xpath("//arch:person/@UUID") as $media_id) {
				$UUID = (string)$media_id;
				$LinkedDataItem->uuid = $UUID; // add it to the Open Contex item
		  }
		
		
		  // get the item_label
		  foreach ($itemXML->xpath("//arch:person/arch:name/arch:string") as $item_label) {
			  $item_label = (string)$item_label;
			  $item_label = trim($item_label);
			  $LinkedDataItem->label  = $item_label;
		  }//end loop for item labels
	
	
		  if($itemXML->xpath("//arch:personInfo/arch:lastName")){
				foreach ($itemXML->xpath("//arch:personInfo/arch:lastName") as $lastName) {
					$lastName = (string)$lastName;
					$lastName = trim($lastName);
					if(strlen($lastName)>1){
						$LinkedDataItem->surname = $lastName;
					}
				}//end loop for item labels
		  }
		  
		  if($itemXML->xpath("//arch:personInfo/arch:firstName")){
				foreach ($itemXML->xpath("//arch:personInfo/arch:firstName") as $firstName) {
					 $firstName = (string)$firstName;
					 $firstName = trim($firstName);
					 if(strlen($firstName)>1){
						 $LinkedDataItem->givenName = $firstName;
					 }
				}//end loop for item labels
		  }
		 
	
		  foreach($itemXML->xpath("//arch:person/@ownedBy") as $projUUID) {
			  $projUUID = (string)$projUUID;
			  $LinkedDataItem->projectUUID  = $projUUID;
			  $LinkedDataItem->projectURI = self::projectBaseURI.$projUUID;
			  $this->projectUUID =  $projUUID;
		  }
		  
		  
		  if($itemXML->xpath("//oc:metadata/oc:links")){
				
				$idObj = new OCitems_Identifiers;
				foreach ($itemXML->xpath("//oc:metadata/oc:links/oc:link") as $stableURI) {
					 $stableURI = (string)$stableURI;
					 $stabEx = explode("/", $stableURI);
					 $stableID = $stabEx[count($stabEx)-1];
					 
					 $data = array("uuid" => $LinkedDataItem->uuid,
										"projectUUID" => $LinkedDataItem->projectUUID,
										"itemType" => "person",
										"stableID" => $stableID,
										"stableType" => "orcid"
										);
					 
					 $idObj->createRecord($data);
				}//end loop for item labels
		  }
		  
		  
	
		return 	$LinkedDataItem;
	 }//end function

	 
	 function XMLtoContextData($LinkedDataItem, $itemXML){
		
		  $contextArray = array();
		  if (!$itemXML->xpath("//oc:context/oc:tree")) {
				$actTree = array();
				//$actTree["id"] = "_:"."default";
				$actTree["@list"][] = array("id" => self::subjectBaseURI."root");  // note: variable $default_context_path used later in abreviated Atom feed
				$contextArray["default"] = $actTree;  // note: variable $default_context_path used later in abreviated Atom feed
		  }
		  
		  if ($itemXML->xpath("//arch:spatialUnit/oc:context/oc:tree[@id='default']")) {
				$actTree = array();
				//$actTree["id"] = "_:"."default";
				
				foreach ($itemXML->xpath("//arch:spatialUnit/oc:context/oc:tree[@id='default']") as $default_tree) {
					 if($default_tree->xpath("oc:parent/@href")){
						  
						  foreach ($default_tree->xpath("oc:parent/@href") as $pathItem) {
								$pathItem = (string)$pathItem;
								//$actTree["@list"][] = array("id" => $pathItem);
								$actTree[] = array("id" => $pathItem);
								//$actTree[] = $pathItem;
						  }
					 }
					 else{
						  foreach ($default_tree->xpath("oc:parent/oc:id") as $pathItem) {
								$pathItem = (string)$pathItem;
								//$actTree["@list"][] = array("id" => self::subjectBaseURI.$pathItem);
								$actTree[] = array("id" => self::subjectBaseURI.$pathItem);
								//$actTree[] = $pathItem;
						  }
					 }
					 break;
				}
				$contextArray["default"] = $actTree;
		  }//end condition with default context tree
		  
		  
		  
		  // Get the additional context paths
		  // first check for the presence of additional paths
		  if ($itemXML->xpath("//arch:spatialUnit/oc:context/oc:tree[not(@id='default')]")) {
	  
				$treeCount = 1; //differentiate between different context trees
				foreach ($itemXML->xpath("//arch:spatialUnit/oc:context/oc:tree[not(@id='default')]") as $non_default_tree) {
					
					 $treeID = false;
					 foreach ($non_default_tree->xpath("@id") as $treeID) {
						  $treeID = (string)$treeID;
					 }
					 
					 if(!$treeID){
						  $treeID = $treeCount;
					 }
					
					 if($non_default_tree->xpath("oc:parent/@href")){
						  foreach ($non_default_tree->xpath("oc:parent/@href") as $alt_path_item) {
								$alt_path_item = (string)$alt_path_item;
								$contextArray[$treeID][] = array("id" => $alt_path_item);
						  }
					 }
					 else{
						  foreach ($non_default_tree->xpath("oc:parent/oc:id") as $alt_path_item) {
								$alt_path_item = (string)$alt_path_item;
								$contextArray[$treeID][] = array("id" => self::subjectBaseURI.$alt_path_item);
						  }
					 }
					
				$treeCount++;
				}
		  
		  }//end condition with another context tree
  
		  $LinkedDataItem->contexts = $contextArray;
		  return $LinkedDataItem;
	 }//return function
	 
	 function XMLtoChildren($LinkedDataItem, $itemXML){
		  
		  $contextArray = array();
		  
		  if ($itemXML->xpath("//arch:spatialUnit/oc:children/oc:tree[@id='default']")) {
				$actTree = array();
				//$actTree["id"] = "_:"."default";
				
				foreach ($itemXML->xpath("//arch:spatialUnit/oc:children/oc:tree[@id='default']") as $default_tree) {
					 if($default_tree->xpath("oc:child/@href")){
						  
						  foreach ($default_tree->xpath("oc:child/@href") as $pathItem) {
								$pathItem = (string)$pathItem;
								//$actTree["@list"][] = array("id" => $pathItem);
								$actTree[] = array("id" => $pathItem);
								//$actTree[] = $pathItem;
						  }
					 }
					 else{
						  foreach ($default_tree->xpath("oc:child/oc:id") as $pathItem) {
								$pathItem = (string)$pathItem;
								//$actTree["@list"][] = array("id" => self::subjectBaseURI.$pathItem);
								$actTree[] = array("id" => self::subjectBaseURI.$pathItem);
								//$actTree[] = $pathItem;
						  }
					 }
					 break;
				}
				$contextArray["default"] = $actTree;
		  }//end condition with default context tree
		  
		  
		  
		  // Get the additional context paths
		  // first check for the presence of additional paths
		  if ($itemXML->xpath("//arch:spatialUnit/oc:children/oc:tree[not(@id='default')]")) {
	  
				$treeCount = 1; //differentiate between different context trees
				foreach ($itemXML->xpath("//arch:spatialUnit/oc:children/oc:tree[not(@id='default')]") as $non_default_tree) {
					
					 $treeID = false;
					 foreach ($non_default_tree->xpath("@id") as $treeID) {
						  $treeID = (string)$treeID;
					 }
					 
					 if(!$treeID){
						  $treeID = $treeCount;
					 }
					
					 if($non_default_tree->xpath("oc:child/@href")){
						  foreach ($non_default_tree->xpath("oc:child/@href") as $alt_path_item) {
								$alt_path_item = (string)$alt_path_item;
								$contextArray[$treeID][] = array("id" => $alt_path_item);
						  }
					 }
					 else{
						  foreach ($non_default_tree->xpath("oc:child/oc:id") as $alt_path_item) {
								$alt_path_item = (string)$alt_path_item;
								$contextArray[$treeID][] = array("id" => self::subjectBaseURI.$alt_path_item);
						  }
					 }
					
				$treeCount++;
				}
		  
		  }//end condition with another context tree
  
		  $LinkedDataItem->children = $contextArray;
		  return $LinkedDataItem;
	 }
	 
	 
	 //get geospatial information for the item
	 function XMLtoLocation($LinkedDataItem, $itemXML){
		 
		  if($itemXML->xpath("//oc:geo_reference")) {
				
				$lat = false;
				$lon = false;
				$refUUID = false;
				foreach ($itemXML->xpath("//oc:geo_reference/oc:geo_lat") as $geoNode) {
					 $lat = (string)$geoNode;
					 $lat = $lat + 0;
				}
				foreach ($itemXML->xpath("//oc:geo_reference/oc:geo_long") as $geoNode) {
					 $lon = (string)$geoNode;
					 $lon = $lon + 0;
				}
				if($itemXML->xpath("//oc:geo_reference/oc:metasource/oc:sourceID")){
					 foreach ($itemXML->xpath("//oc:geo_reference/oc:metasource/oc:sourceID") as $geoNode) {
						  $refUUID = (string)$geoNode;
					 }
				}
				else{
					 foreach ($itemXML->xpath("//oc:geo_reference/oc:metasource/oc:source_id") as $geoNode) {
						  $refUUID = (string)$geoNode;
					 }
				}
				
				if($refUUID != false){
					 $data = array("uuid" => $refUUID,
								  "projectUUID" => $this->projectUUID,
								  "ftype" => "point",
								  "latitude" => $lat,
								  "longitude" => $lon
								  );
						  
						  $geoObj = new OCitems_Geodata;
						  $geoObj->createRecord($data);
				}
				
				if($refUUID != false && $lat != false && $lon != false){
					 $geospaceArray = array("lat" => $lat,
											 "lon" => $lon,
											 "refURI" => self::subjectBaseURI.$refUUID );
					 
					 $LinkedDataItem->geospace = $geospaceArray;
				}
				
		  }
		 
		  return $LinkedDataItem;
	 }
	 
	 
	 //get geospatial information for the item
	 function XMLtoChronology($LinkedDataItem, $itemXML){
		 
		  if($itemXML->xpath("//oc:chrono")) {
				
				$tStart = false;
				$tEnd = false;
				$refUUID = false;
				foreach ($itemXML->xpath("//oc:chrono/oc:time_start") as $tNode) {
					 $tStart = (string)$tNode;
					 $tStart = $tStart + 0;
				}
				foreach ($itemXML->xpath("//oc:chrono/oc:time_finish") as $tNode) {
					 $tEnd  = (string)$tNode;
					 $tEnd  = $tEnd  + 0;
				}
				if($itemXML->xpath("//oc:chrono/oc:metasource/oc:sourceID")){
					 foreach ($itemXML->xpath("//oc:chrono/oc:metasource/oc:sourceID") as $tNode) {
						  $refUUID = (string)$tNode;
					 }
				}
				else{
					 foreach ($itemXML->xpath("//oc:chrono/oc:metasource/oc:source_id") as $tNode) {
						  $refUUID = (string)$tNode;
					 }
				}
				
				if($refUUID != false){
					 $data = array("uuid" => $refUUID,
							 "projectUUID" => $this->projectUUID,
							 "startLC" => $tStart,
							 "startC" => $tStart,
							 "endC" => $tEnd,
							 "endLC" => $tEnd
							 );
					 
					 $chronoObj = new OCitems_Chronodata;
					 $chronoObj->createItem($data);
				}
				
				if($refUUID != false && $tStart != false && $tStart != false){
					 $chronologyArray = array(	"startLC" => $tStart,
														  "startC" => $tStart,
														  "endC" => $tEnd,
														  "endLC" => $tEnd,
											 "refURI" => self::subjectBaseURI.$refUUID );
					 
					 $LinkedDataItem->chronology =  $chronologyArray;
				}
				
		  }
		 
		  return $LinkedDataItem;
	 }
	 
	 function XMLtoObservationsData($LinkedDataItem, $itemXML){
		  $observations = array();
		  if($itemXML->xpath("//arch:observations/arch:observation")) {
				$countedObsNumber = 1;
				foreach($itemXML->xpath("//arch:observations/arch:observation") as $obsNode){
					 if($obsNode->xpath("@obsNumber")) {
						  foreach($obsNode->xpath("@obsNumber") as $obsNumberNode){
								$obsNumber = (string)$obsNumberNode;
								$obsNumber = $obsNumber + 0;
						  }
					 }
					 else{
						  $obsNumber = $countedObsNumber;
					 }
					 
					 $obsSource = false;
					 if($obsNode->xpath("oc:obs_metadata/oc:source")) {
						  foreach($obsNode->xpath("oc:obs_metadata/oc:source") as $obsSourceNode){
								$obsSource = (string)$obsSourceNode;
								$this->sourceID = $obsSource;
						  }
					 }
					 
					 if($obsNumber <1 || $obsNumber >=100){
						  $observations[$obsNumber]["status"] = "inactive";
					 }
					 else{
						  $observations[$obsNumber]["status"] = "active";
					 }
					 $observations[$obsNumber]["sourceID"] = $obsSource;
					 $observations[$obsNumber]["properties"] = $this->XMLtoObsProperties($obsNode);
					 $observations[$obsNumber]["notes"] = $this->XMLtoObsNotes($obsNode);
					 $observations[$obsNumber]["links"] = $this->XMLtoObsLinks($obsNode);
					 $countedObsNumber++;
				}
		  }
		  else{
				$observations[1]["sourceID"] = false;
				$observations[1]["status"] = "active";
				$observations[1]["properties"] = $this->XMLtoObsProperties($itemXML, "//");
				$observations[1]["notes"] = $this->XMLtoObsNotes($itemXML, "//");
				$observations[1]["links"] = $this->XMLtoObsLinks($itemXML, "//");
		  }
		  
		  $LinkedDataItem->observations = $observations;
		  return $LinkedDataItem;
	 }
	 
	 
	 //get properties for a given observation node
	 function XMLtoObsProperties($obsXMLnode, $xpathPrefix = ""){
		  $properties = false;
		  if($obsXMLnode->xpath($xpathPrefix."arch:properties/arch:property")) {
				$properties = array();
				$varUUID = false;
				foreach($obsXMLnode->xpath($xpathPrefix."arch:properties/arch:property") as $propNode){
					 $actProperty = array();
					 $actProperty["varLabel"] = false;
					 $actProperty["type"] = false;
					 $showStringLiteral = true;
					 foreach($propNode->xpath("arch:variableID") as $xpathRes) {
						  $varUUID = (string)$xpathRes;
						  if(strlen($varUUID)>1){
								$varUUID = $this->idUpdate($varUUID);
								$varURI = self::predicateBaseURI.$varUUID;
						  }
						  else{
								$varUUID = false;
						  }
					 }
					 foreach($propNode->xpath("oc:propid") as $xpathRes) {
						  $propID = (string)$xpathRes;
						  $propID = $this->idUpdate($propID);
						  $actProperty["propUUID"] =  $propID;
					 }
					 $varType = false;
					 foreach($propNode->xpath("oc:var_label/@type") as $xpathRes) {
						  $varType = (string)$xpathRes;
						  $varType = strtolower($varType);
					 }
					 $value = "";
					 foreach($propNode->xpath("oc:show_val") as $xpathRes) {
						  $value = (string)$xpathRes;
						  $actProperty["value"] = $value;
						  $showStringLiteral = true;
					 }
					 foreach($propNode->xpath("oc:var_label") as $xpathRes) {
						  $varLabel = (string)$xpathRes;
						  $actProperty["varLabel"] = $varLabel;
					 }
					 foreach($propNode->xpath("arch:valueID") as $xpathRes) {
						  $valueID = (string)$xpathRes;
						  $valueID = $this->idUpdate($valueID);
						  $actProperty["valueID"] = $valueID;
					 }
					 
					 if($propNode->xpath("arch:integer")){
						  foreach($propNode->xpath("arch:integer") as $xpathRes) {
								$intVal = (string)$xpathRes;
								if(is_numeric($value)){
									 $actProperty[self::integerLiteral] = $intVal + 0;
									 $actProperty["type"] = self::integerLiteral;
									 $varType = "integer";
									 $showStringLiteral = false;
								}
						  }
					 }
					 if($propNode->xpath("arch:decimal")){
						  foreach($propNode->xpath("arch:decimal") as $xpathRes) {
								$decVal = (string)$xpathRes;
								if(is_numeric($value)){
									 $actProperty[self::decimalLiteral] = $decVal + 0;
									 $actProperty["type"] = self::decimalLiteral;
									 $varType = "decimal";
									 $showStringLiteral = false;
								}
						  }
					 }
					 if($propNode->xpath("arch:date")){
						  $showStringLiteral = false;
						  foreach($propNode->xpath("arch:date") as $xpathRes) {
								$dateVal = (string)$xpathRes;
								$actProperty[self::dateLiteral] = $dateVal;
								$actProperty["type"] = self::dateLiteral;
						  }
					 }
					 
					 if($varType == "ordinal" || $varType == "nominal" ){
						  $showStringLiteral = false;
						  if($propNode->xpath("oc:propid/@href")) {
								foreach($propNode->xpath("oc:propid/@href") as $xpathRes) {
									 $actProperty["id"] =  (string)$xpathRes;
								}
						  }
						  else{
								foreach($propNode->xpath("oc:propid") as $xpathRes) {
									 $propID = (string)$xpathRes;
									 $actProperty["id"] =  self::propertyBaseURI.$propID;
								}
						  }
					 }
					 if(stristr($varType, "calend")){
						  $cal_test_string = str_replace("/", "-", $value);
						  if (($timestamp = strtotime($cal_test_string)) === false) {
								$calendardTest = false;
						  }
						  else{
								$calendardTest = true;
						  }
						  if($calendardTest){
								$valueDate = date("Y-m-d", strtotime($cal_test_string));
								$actProperty[self::dateLiteral] = $valueDate;
								$actProperty["type"] = self::dateLiteral;
								$showStringLiteral = false;
						  }
					 }
					 elseif($varType == "boolean"){
						  $boolVal = strtolower($value);
						  if($boolVal == "yes" || $boolVal == "true" || $boolVal == "1"){
								$actProperty[self::booleanLiteral] = true;
								$actProperty["type"] = self::booleanLiteral;
								$showStringLiteral = false;
						  }
						  else{
								$actProperty[self::booleanLiteral] = false;
								$actProperty["type"] = self::booleanLiteral;
								$showStringLiteral = false;
						  }
					 }
					 
					 if($showStringLiteral){
						  $actProperty[self::stringLiteral] = $value;
						  $actProperty["type"] = self::stringLiteral;
					 }
					 
					 if($varUUID != false){
						  $properties[$varURI][] = $actProperty;
					 }
					 if(count($properties)>=3){
						  //break;
					 }
				}
				
		  }
		  
		  
		  return $properties;
	 }
	 
	 //get notes for a given observation node
	 function XMLtoObsNotes($obsXMLnode, $xpathPrefix = ""){
		  $notes = false;
		  if($obsXMLnode->xpath($xpathPrefix."arch:notes/arch:note")) {
				$stringNote = false;
				$obsXMLnode->registerXPathNamespace("xhtml", OpenContext_OCConfig::get_namespace("xhtml"));
				if($obsXMLnode->xpath($xpathPrefix."arch:notes/arch:note/arch:string/xhtml:div")){
					 foreach ($obsXMLnode->xpath($xpathPrefix."arch:notes/arch:note/arch:string/xhtml:div") as $divNote) {
						 $stringNote = $divNote->asXML();
						 $notes[] = $stringNote;
					 }
				}
				else{
					 foreach ($obsXMLnode->xpath($xpathPrefix."arch:notes/arch:note/arch:string") as $note) {
						 $stringNote = (string)$note;
						 $notes[] = $stringNote;
					 }
				}
		  }
		  return $notes;
	 }
	 
	 //get links for a given observation node
	 function XMLtoObsLinks($obsXMLnode, $xpathPrefix = ""){
		  $links = false;
		  if($obsXMLnode->xpath($xpathPrefix."arch:links//oc:link")) {
				$links = false;
				foreach ($obsXMLnode->xpath($xpathPrefix."arch:links//oc:link") as $linkNode) {
					 $href = false;
					 if($linkNode->xpath("@href")){
						  foreach($linkNode->xpath("@href") as $xRes){
								$href = (string)$xRes;
						  }
					 }
					 $relationLabel = false;
					 if($linkNode->xpath("oc:relation")){
						  foreach($linkNode->xpath("oc:relation") as $xRes){
								$relationLabel = (string)$xRes;
								$relationLabel = trim($relationLabel);
						  }
					 }
					 
					 $predicateURI = $this->makeGetLinkPredicateURI($relationLabel);
					 $links[$predicateURI][] = $href;
				}
		  }
		  return $links;
	 }
	 
	 //location reference
	 
	 //get a URI equivalent for the current relationship
	 function makeGetLinkPredicateURI($relationLabel){
		  $uri = false;
		  $projectUUIDs = array("0", $this->projectUUID);
		  
		  $predicateObj = new OCitems_Predicate;
		  $predData = $predicateObj->getByLabel($relationLabel, $projectUUIDs, "link");
		  
		  if(is_array($predData)){
				$uri = self::predicateBaseURI.$predicateObj->uuid;
		  }
		  else{
				//gotta make it!
				$genObj = new OCitems_General;
				$newUUID = $genObj->generateUUID();
				$sourceID = $this->sourceID;
				if(!$sourceID){
					 $sourceID = false;
				}
				
				$data = array("uuid" => $newUUID,
								  "projectUUID" => $this->projectUUID,
								  "sourceID" => $sourceID,
								  "archaeoMLtype" => "link",
								  "dataType" => "id",
								  "label" => $relationLabel,
								  "created" => date("Y-m-d")
								  );
				
				$ok = $predicateObj->createRecord($data);
				
				if($ok){
					 $uri = self::predicateBaseURI.$newUUID;
				}
		  }
		  
		  
		  return $uri;
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
