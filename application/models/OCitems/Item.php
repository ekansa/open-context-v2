<?php


//this class interacts with the database for accessing and changing Predicates
//oc_predicates are defined in different projects
class OCitems_Item {
    
	 public $db;
	 
    /*
     General data
    */
    public $manifest;
    public $shortJSON;
	 public $longJSON;
	 
	 
	 public $uri; //item URI
	 public $uuid; //uuid of the item
	 public $label; //main label of the object
	 public $itemType; //main type of Open Context item or resource (subject, media, document, person, project)
	 public $projectUUID; //uuid of the item's project
	 public $projectURI; //uri of the item's project
	 
	 public $published; //dublin core publication date
	 public $license; //copyright license
	 
	 public $contributors;
	 public $creators;
	 
	 //class, usually used with subject items
	 public $itemClassURI;  //any object URI of an RDF type predicate
	 
	 //media specific
	 public $mimeTypeURI; //mimetype for the full file
	 public $mediaType; //general media type for the full file
	 public $fullURI; //uri for the full file
	 public $fileSize; //file size of the full file
	 
	 public $thumbURI; //URI for the thumbnail file
	 public $thumbMimeURI; //mimetype for the preview file
	 
	 public $previewURI; //uri for the preview file
	 public $previewMimeURI; //mimetype for the preview file
	 
	 
	 //documents specific
	 public $documentContents;
	 
	 //person specific
	 public $surname; //person's last name
	 public $givenName; //persons first name

	 public $assertions; //raw array of assertions made on an item
	 public $contexts; //context array (for subjects)
	 public $children; //children array (for subjects)
	 public $observations; //observation array (has observation metadata, properties, notes, links, and linked data)
	 public $geospace; //array of geospatial data for the item
	 public $chronology; //array of chronological information for the item
	 
	 const Predicate_hasContextPath = "oc-gen:has-context-path"; //has context
	 const Predicate_hasPathItems = "oc-gen:has-path-items"; //has parent context items
	 const Predicate_pathDes = "oc-gen:path-des"; //path has a description 
	 const contextPathNodePrefix = "context-path-"; //prefix for naming context path nodes
   
	 const Predicate_hasContents = "oc-gen:has-contents"; //has children items
	 const Predicate_contains = "oc-gen:contains"; //contains (list of child items)
	
	 const Predicate_locationRef = "oc-gen:locationRef"; //location reference, points to URI of item (or parent context) providing locational data
	 const Predicate_chronoRef = "oc-gen:chronoRef"; //chronological reference, points to URI of item (or parent context) providing chronology data
	
	 const Predicate_hasObs = "oc-gen:has-obs"; //item has observations
	 const Predicate_sourceID = "oc-gen:sourceID"; //identifier for the observation source
	 const Predicate_obsStatus= "oc-gen:obsStatus"; //if the observation is current or deprecated
	 const Predicate_hasNote = "oc-gen:has-note"; //note about the observation
	 
	 const Predicate_dcTermsPublished = "dc-terms:published";
	 const Predicate_dcTermsCreator = "dc-terms:creator";
	 const Predicate_dcTermsContributor = "dc-terms:contributor";
	 const Predicate_dcTermsIsPartOf = "dc-terms:isPartOf";
	 
	 const stringLiteral = "xsd:string"; 
	 const integerLiteral = "xsd:integer"; //numeric
	 const decimalLiteral = "xsd:decimal"; //numeric
	 const booleanLiteral = "xsd:boolean"; //numeric
	 const dateLiteral = "xsd:date";
	 
	 const mediaCatPrefix = "dcat";
	 const mediaCatBaseURI = "http://www.w3.org/ns/dcat#";
	 const Predicate_fileSize = "dcat:size";
	 const Predicate_DCformat = "dc-terms:hasFormat";
	 const Predicate_hasPrimaryFile = "oc-gen:has-primary-file";
	 const Predicate_hasPreviewFile = "oc-gen:has-preview-file";
	 const Predicate_hasThumbFile = "oc-gen:has-thumb-file";
	 const Predicate_hasContent = "oc-gen:has-content";
	 
	 const foafPrefix = "foaf";
	 const foafBaseURI = "http://xmlns.com/foaf/0.1/";
	 const Predicate_familyName = "foaf:familyName";
	 const Predicate_givenName = "foaf:givenName";
	 
    //get data from database
    function getShortByUUID($uuid){
        
        $uuid = $this->security_check($uuid);
        $output = false; //not found
        
		  $manifestObj = new OCitems_Manifest;
		  $this->manifest = $manifestObj->getByUUID($uuid);
		  if(is_array($this->manifest)){
				$dataCacheObj = new OCitems_DataCache;
				$this->shortJSON = $dataCacheObj->getContentArrayByUUID($uuid);
				if(is_array($this->shortJSON)){
					 $manifestObj->addViewCount(); //add to the view count
					 $output = true;
				}
		  }
		  
        return $output;
    }
	 
	 //get data from database
    function getLongByUUID($uuid){
        
        $uuid = $this->security_check($uuid);
        $output = false; //not found
        
		  $manifestObj = new OCitems_Manifest;
		  $this->manifest = $manifestObj->getByUUID($uuid);
		  if(is_array($this->manifest)){
				$dataCacheObj = new OCitems_DataCache;
				$this->shortJSON = $dataCacheObj->getContentArrayByUUID($uuid);
				if(is_array($this->shortJSON)){
					 $manifestObj->addViewCount(); //add to the view count
					 $this->shortToLongJSON();
					 $output = true;
				}
		  }
		  
        return $output;
    }
	 
    
	 //convert short to long JSON, adding related data
	 function shortToLongJSON(){
		  $JSON_LD = false;
		  if(is_array($this->shortJSON)){
				$JSON_LD = $this->recursiveNodeExpand($this->shortJSON);
				$this->longJSON = $JSON_LD;
		  }
		  return $JSON_LD;
	 }
	 
	 
	 function recursiveNodeExpand($arrayNode){
		  $ocGenObj = new OCitems_General;
		  $uriObj = new infoURI;
		  $manifestObj = new OCitems_Manifest;
		  if(is_array($arrayNode)){
				$newArrayNode = array();
				foreach($arrayNode as $key => $actVals){
					 if(!is_array($actVals)){
						  $newArrayNode[$key] = $actVals;
						  if($key == "id" || $key == "@id"){
								$deRef = $uriObj->lookupURI($actVals);
								if(is_array($deRef)){
									 if(isset($deRef["label"])){
										  $newArrayNode["label"] = $deRef["label"];
									 }
								}
						  }
					 }
					 else{
						  $newActVals = $this->recursiveNodeExpand($actVals);
						  $newArrayNode[$key] = $newActVals;
					 }
				}
				unset($arrayNode);
				$arrayNode = $newArrayNode;
				unset($newArrayNode);
		  }
		  return $arrayNode;
	 }
	 
	 
	 
	 //generates a new short JSON-LD representation from database queries
	 function generateShortByUUID($uuid){
		  
		  $uuid = $this->security_check($uuid);
		  $output = false; //not found
		  $ocGenObj = new OCitems_General;
		  $manifestObj = new OCitems_Manifest;
		  $this->manifest = $manifestObj->getByUUID($uuid);
		  if(is_array($this->manifest)){
				$this->uuid = $manifestObj->uuid;
				$this->label = $manifestObj->label;
				$this->itemType = $manifestObj->itemType;
				$this->uri = $ocGenObj->generateItemURI($this->uuid, $this->itemType);
				$this->published = $manifestObj->published;
				$this->projectUUID = $manifestObj->projectUUID;
				$this->projectURI = $ocGenObj->generateItemURI($this->projectUUID, "project");
				
				$JSON_LD = array();
				$JSON_LD["@context"] = array(
					 "type" => "@type",
					 "id" => "@id",
					 "rdfs" => "http://www.w3.org/2000/01/rdf-schema#",
					 "dc-elems" => "http://purl.org/dc/elements/1.1/",
					 "dc-terms" => "http://purl.org/dc/terms/",
					 "uuid" => "dc-terms:identifier",
					 "bibo" => "http://purl.org/ontology/bibo/",
					 "label" => "http://www.w3.org/2000/01/rdf-schema#label",
					 "xsd" => "http://www.w3.org/2001/XMLSchema#",
					 "oc-gen" => "http://opencontext.org/vocabularies/oc-general/"
					 );
				
				
				
				$JSON_LD["id"] = $this->uri;
				$JSON_LD["label"] = $this->label;
				$JSON_LD["uuid"] = $this->uuid;
				if($this->itemClassURI){
					 $JSON_LD["rdfs:type"][] = array("id" => $this->itemClassURI);
				}
				
				
				$assertionsObj = new OCitems_Assertions;
				$assertionsObj->getParentsByChildUUID($uuid);
				$this->contexts = $assertionsObj->contexts; //array of containing contexts, if present
				$this->assertions = $assertionsObj->getByUUID($uuid);
				unset($assertionsObj);
				
				//$JSON_LD["rawcontexts"] = $this->contexts;
				$JSON_LD = $this->addContextsJSON($JSON_LD); //parent items (if any)
				$JSON_LD = $this->addContentsJSON($JSON_LD); //child items (if any)
				$JSON_LD = $this->addObservationsJSON($JSON_LD); //child items (if any)
				//$JSON_LD["assertions"] = $this->assertions;
				
				$JSON_LD = $this->addSpaceOrTimeRefJSON($JSON_LD, true); //location reference
				$JSON_LD = $this->addSpaceOrTimeRefJSON($JSON_LD, false); //chronology reference
				
				$JSON_LD = $this->addMediaJSON($JSON_LD); //add links to media files, if of media type
				$JSON_LD = $this->addDocumentJSON($JSON_LD); //add the document content
				$JSON_LD = $this->addPersonJSON($JSON_LD); //adds person specific information
				$JSON_LD = $this->addDCpeopleJSON($JSON_LD); //add creators and contributors
				$JSON_LD = $this->addStableIdentifiersJSON($JSON_LD); //add stable identifiers
				
				$JSON_LD[self::Predicate_dcTermsPublished] = $this->published;
				$JSON_LD[self::Predicate_dcTermsIsPartOf][] = array("id" => $this->projectURI);
				
				$output = $JSON_LD;
		  }
		  
		  return $output;
	 }
	 
	 
	 //make the JSON for describing the item's context
	 function addContextsJSON($JSON_LD){
		  if(is_array($this->contexts)){
				foreach($this->contexts as $treeNodeID => $parentURIs){
					 $treeNodeEx = explode("-", $treeNodeID );
					 $treeNumber = $treeNodeEx[count($treeNodeEx)-1];
					 $contextNodeID = self::contextPathNodePrefix.$treeNumber;
					 if($treeNumber == 1){
						  $pathDes = "default";
					 }
					 else{
						  $pathDes = "alternate";
					 }
					 
					 $actContextArray = array("id" => $contextNodeID,
													  self::Predicate_pathDes => $pathDes);
					 
					 foreach($parentURIs as $parentURI){
						  $actContextArray[self::Predicate_hasPathItems][] = array("id" => $parentURI);
					 }
					 
					 $JSON_LD[self::Predicate_hasContextPath][] = $actContextArray;
				}
		  }
		  return $JSON_LD;
	 }
	 
	 
	 //make the JSON for describing the item's context
	 function addSpaceOrTimeRefJSON($JSON_LD, $doSpace = true){
		  $geoUse = false;
		  $chronoUse = false;
		  $ocGenObj = new OCitems_General;
		  $geoObj = new OCitems_Geodata;
		  $chronoObj = new OCitems_Chronodata;
		 
		  if($doSpace){
				$res = $geoObj->getByUUID($this->uuid);
				$resArray[$this->uuid] = $res;
				if(is_array($res)){
					 $geoUse = array("id" => $this->uri);
					 $JSON_LD[self::Predicate_locationRef][] = $geoUse;
				}
		  }
		  else{
				$res = $chronoObj->getByUUID($this->uuid);
				$resArray[$this->uuid] = $res;
				if(is_array($res)){
					 $chronoUse = array("id" => $this->uri);
					 $JSON_LD[self::Predicate_chronoRef][] = $chronoUse;
				}
		  }
		  
		  if(!$geoUse && !$chronoUse && is_array($this->contexts)){
				foreach($this->contexts as $treeNodeID => $parentURIs){
					 foreach($parentURIs as $parentURI){
						  $parentUUID = $ocGenObj->itemUUIDfromURI($parentURI);
						  if($doSpace){
								$res = $geoObj->getByUUID($parentUUID);
								$resArray[$parentUUID] = $res;
								if(is_array($res)){
									 $geoUse = array("id" => $parentURI);
								}
						  }
						  else{
								$res = $chronoObj->getByUUID($parentUUID);
								$resArray[$parentUUID] = $res;
								if(is_array($res)){
									 $chronoUse = array("id" => $parentURI);
								}
						  }
					 }
					 
					 if($doSpace && is_array($geoUse)){
						  $JSON_LD[self::Predicate_locationRef][] = $geoUse;
						  break;
					 }
					 if(!$doSpace && is_array($chronoUse)){
						  $JSON_LD[self::Predicate_chronoRef][] = $chronoUse;
						  break;
					 }
				}
		  }
		  
		  return $JSON_LD;
	 }
	 
	 
	 //make the JSON for describing the item's contents
	 function addContentsJSON($JSON_LD){
		  
		  if(is_array($this->assertions)){
				$ocGenObj = new OCitems_General;
				$contents = array();
				foreach($this->assertions as $row){
					 if($row["predicateUUID"] == self::Predicate_contains){
						  $actContentsNodeID = $row["obsNode"];
						  $childURI = $ocGenObj->generateItemURI($row["objectUUID"], $row["objectType"]);
						  $contents[$actContentsNodeID][] = $childURI;
					 }
				}
				if($contents > 0){
					 foreach($contents as $treeNodeID => $childrenURIs){
						  
						  $treeNodeEx = explode("-", $treeNodeID );
						  $treeNumber = $treeNodeEx[count($treeNodeEx)-1];
						  if($treeNumber == 1){
								$pathDes = "default";
						  }
						  else{
								$pathDes = "alternate";
						  }
					 
						  $actContentArray = array("id" => $treeNodeID,
													  self::Predicate_pathDes => $pathDes);
					 
						  foreach($childrenURIs as $childURI){
								$actContentArray[self::Predicate_contains][] = array("id" => $childURI);
						  }	
					 
						  $JSON_LD[self::Predicate_hasContents][] = $actContentArray;
					 }
				}
	 
		  }
		  
		  return $JSON_LD;
	 }
	 
	 
	 //make the JSON for the item's observations
	 function addObservationsJSON($JSON_LD){
		  
		  if(is_array($this->assertions)){
				$ocGenObj = new OCitems_General;
				$stringObj = new OCitems_String;
				$linkAnnotObj = new Links_linkAnnotation;
				
				$vars = array();
				$links = array();
				$obsArray = array();
				$dcRels = array();
				$dcRels["creators"] = array();
				$dcRels["contributors"] = array();
				$dcCreators = array();
				$dcContributors = array();
				
				foreach($this->assertions as $row){
					 if($row["predicateUUID"] != self::Predicate_contains){
						  $obsNodeID = $row["obsNode"];
						  
						  if(!array_key_exists($obsNodeID, $obsArray)){
								if($row["obsNum"]>0){
									 $obsStatus = "active";
								}
								else{
									 $obsStatus = "inactive";
								}
								
								$obsArray[$obsNodeID] = array("id" => $obsNodeID,
																		self::Predicate_sourceID => $row["sourceID"],
																		self::Predicate_obsStatus => $obsStatus);
						  }
						  
						  
						  $objectURI = false;
						  $predicateURI = false;
						  $predicateShort = false;
						  if($row["predicateUUID"] == self::Predicate_hasNote){
								$predicateURI = self::Predicate_hasNote;
								$predicateShort = self::Predicate_hasNote;
								$actType = $row["objectType"];
						  }
						  else{
								$objectURI = $ocGenObj->generateItemURI($row["objectUUID"], $row["objectType"]);
								if(!$objectURI){
									 $actType = $row["objectType"];
								}
								else{
									 $actType = "@id";
								}
								$predicateURI = $ocGenObj->generateItemURI($row["predicateUUID"], "predicate");
								if($ocGenObj->classifyPredicateTypeFromObjectType($row["objectType"]) == "variable"){
									 if(!array_key_exists($predicateURI, $vars)){
										  $actVarNumber = count($vars) + 1;
										  $predicateShort = "var-".$actVarNumber;
										  $vars[$predicateURI] = array("type" => $actType, "abrev" => $predicateShort);
									 }
									 else{
										  $predicateShort = $vars[$predicateURI]["abrev"];
									 }
								}
								else{
									 if(!array_key_exists($predicateURI, $links)){
										  $actLinkNumber = count($links) + 1;
										  $predicateShort = "link-".$actLinkNumber;
										  $links[$predicateURI] = array("type" => $actType, "abrev" => $predicateShort);
										  if($linkAnnotObj->DCcreatorCheck($row["predicateUUID"])){
												$dcRels["creators"][] = $predicateURI;
										  }
										  if($linkAnnotObj->DCcontributorCheck($row["predicateUUID"])){
												$dcRels["contributors"][] = $predicateURI;
										  }
									 }
									 else{
										  $predicateShort = $links[$predicateURI]["abrev"];
									 }
								}
						  }
						  if(!$objectURI){
								
								if($actType == self::stringLiteral){
									 $stringObj->getByUUID($row["objectUUID"]); //look up the string associated with this value
									 $actValue = $stringObj->content;
									 $obsArray[$obsNodeID][$predicateShort][] = array("id" => "#string-".$row["objectUUID"], $actType => $actValue); //string has uuid to identify it
								}
								else{
									 if($actType == self::dateLiteral){
										  $actValue = $row["dataDate"]; //use the date literal
									 }
									 else{
										  $actValue = $row["dataNum"]+0; //use a numeric literal
									 }
									 $obsArray[$obsNodeID][$predicateShort][] = $actValue;
								}
						  }
						  else{
								$obsArray[$obsNodeID][$predicateShort][] = array("id" => $objectURI);
								if(in_array($predicateURI, $dcRels["creators"])){
									 $dcCreators[] = array("id" => $objectURI);
								}
								if(in_array($predicateURI, $dcRels["contributors"])){
									 $dcContributors[] = array("id" => $objectURI);
								}
						  }
					 }
				}
				
				if(count($vars)>0){
					 foreach($vars as $predicateURIkey => $predArray){
						  $predicateShort = $predArray["abrev"];
						  $JSON_LD["@context"][$predicateShort] = array("@id" => $predicateURIkey, "@type" => $predArray["type"]);
					 }
				}
				if(count($links)>0){
					 foreach($links as $predicateURIkey => $predArray){
						  $predicateShort = $predArray["abrev"];
						  $JSON_LD["@context"][$predicateShort] = array("@id" => $predicateURIkey, "@type" => $predArray["type"]);
					 }
				}
				
				if(count($obsArray)>0){
					 foreach($obsArray as $obsNodeKey => $observationData){
						  $JSON_LD[self::Predicate_hasObs][] = $observationData;
					 }
				}
				
				if(count($dcCreators)>0){
					 $this->creators = $dcCreators;
				}
				if(count($dcContributors)>0){
					 $this->contributors = $dcContributors;
				}
				
		  }
		  
		  return $JSON_LD;
	 }
	 
	 
	 //add media files
	 function addMediaJSON($JSON_LD){
		  if($this->itemType == "media"){
				$mediaFileObj = new OCitems_MediaFile;
				$media = $mediaFileObj->getByUUID($this->uuid);
				if(is_array($media)){
					 $JSON_LD["@context"][self::mediaCatPrefix] = self::mediaCatBaseURI;
					 if($mediaFileObj->fullURI){
						  $JSON_LD["@context"]["dcat"] = "http://www.w3.org/ns/dcat#";
						  
						  $JSON_LD["oc-gen:has-primary-file"][] = array("id" => $mediaFileObj->fullURI,
																					 "dc-terms:hasFormat" => $mediaFileObj->mimeTypeURI,
																					 "dcat:size" => $mediaFileObj->fileSize +0
																					 );
					 }
					 
					 if($mediaFileObj->previewURI){
						  $JSON_LD["oc-gen:has-preview-file"][] = array("id" => $mediaFileObj->previewURI,
																					 "dc-terms:hasFormat" => $mediaFileObj->previewMimeURI
																					 );
					 }
					 
					 if($mediaFileObj->thumbURI){
						  $JSON_LD["oc-gen:has-thumb-file"][] = array("id" => $mediaFileObj->thumbURI,
																					 "dc-terms:hasFormat" =>$mediaFileObj->thumbMimeURI
																					 );
					 }
					 
				}
		  }
		  return $JSON_LD;
	 }
	 
	 
	 //adds Dublin Core creator / contributor relations
	 
	 
	 //add document content
	 function addDocumentJSON($JSON_LD){
		  
		  if($this->itemType == "document"){
				$JSON_LD["@context"][self::foafPrefix] = self::foafBaseURI;
				$docObj = new OCitems_Document;
				$res = $docObj->getByUUID($this->uuid);
				if(is_array($res)){
					 $JSON_LD[self::Predicate_hasContent] = $docObj->content;
				}
		  }  
		  return $JSON_LD;
	 }
	 
	 
	 
	 //add some details about the person from the database, load in FOAF namespace
	 function addPersonJSON($JSON_LD){
		  
		  if($this->itemType == "person"){
				$JSON_LD["@context"][self::foafPrefix] = self::foafBaseURI;
				$persObj = new OCitems_Person;
				$pres = $persObj->getByUUID($this->uuid);
				if(is_array($pres)){
					 $JSON_LD["rdfs:type"][] = array("id" => $persObj->foafType);
					 $JSON_LD[self::Predicate_familyName] = $persObj->surname;
					 $JSON_LD[self::Predicate_givenName] = $persObj->givenName;
				}
		  }  
		  return $JSON_LD;
	 }
	 
	 
	 
	 function addDCpeopleJSON($JSON_LD){
		  if(is_array($this->creators)){
				$JSON_LD[self::Predicate_dcTermsCreator] = $this->creators;
		  }
		  if(is_array($this->contributors)){
				$JSON_LD[self::Predicate_dcTermsContributor] = $this->contributors;
		  }
		  return $JSON_LD;
	 }
	 
	 //add stable itentifiers 
	 function addStableIdentifiersJSON($JSON_LD){
		  $idObj = new OCitems_Identifiers;
		  $ids = $idObj->getStableLinksByUUID($this->uuid);
		  if(is_array($ids)){
				foreach($ids as $predicateKey => $objectIDs){
					 foreach( $objectIDs as $objItemArray){
						  $JSON_LD[$predicateKey][] = $objItemArray;
					 }
				}
		  }
		  return $JSON_LD;
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
