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
	 public $assertedPredicates; //array of predicates (variables + links) and their abbrevations. Useful for annotating them
	 public $assertedObjects; //array of objects indentified in observations about an item. Useful for annotation them. 
	 
	 public $geospace; //array of geospatial data for the item
	 public $chronology; //array of chronological information for the item
	 
	
	 public $addExternalEntityInfo = false; //add labeling and some other data about entities referenced by this item
	 public $infoURIs = array(); //uri keys with array of useful information, so we don't look up the same uri label multiple times.
	 
	 public $addSelfGeoJSONonly = false; //add GeoJSON data only if the record itself has geo data assigned (don't do an inference)
	 public $addPointFeaturesIfPolygon = true; //add a GeoJSON point feature even if an item has a polygon feature
	 
	 
	 
	 
	 /* Array of standard namespaces used for open context items
	 */
	 public $standardNamespaces = array("rdf" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
					 "rdfs" => "http://www.w3.org/2000/01/rdf-schema#",
					 "label" => "rdfs:label",
					 "xsd" => "http://www.w3.org/2001/XMLSchema#",
					 "skos" => "http://www.w3.org/2004/02/skos/core#",
					 "owl" => "http://www.w3.org/2002/07/owl#",
					 "dc-elems" => "http://purl.org/dc/elements/1.1/",
					 "dc-terms" => "http://purl.org/dc/terms/",
					 "uuid" => "dc-terms:identifier",
					 "bibo" => "http://purl.org/ontology/bibo/",
					 "foaf" => "http://xmlns.com/foaf/0.1/",
					 "cidoc-crm" => "http://www.cidoc-crm.org/cidoc-crm/",
					 "oc-gen" => "http://opencontext.org/vocabularies/oc-general/");
	 
	 
	 const skipInfoURIkey = "skip";
	 
	 const Predicate_hasContextPath = "oc-gen:has-context-path"; //has context
	 const Predicate_hasPathItems = "oc-gen:has-path-items"; //has parent context items
	 const Predicate_pathDes = "oc-gen:path-des"; //path has a description 
	 const nodePrefix_contextPath = "#context-path-"; //prefix for naming context path nodes
   
	 const Predicate_hasContents = "oc-gen:has-contents"; //has children items
	 const Predicate_contains = "oc-gen:contains"; //contains (list of child items)
	
	 const Predicate_locationRefLabel = "oc-gen:locationRefLabel"; //label of referenced location
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
	 const decimalLiteral = "xsd:double"; //numeric
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
	 
	 const Predicate_familyName = "foaf:familyName";
	 const Predicate_givenName = "foaf:givenName";
	 
	 const Predicate_geoPropRefType = "oc-gen:geoReferenceType"; //predicate for geo data assigned to the item iteself or inferred through containment
	 const nodePrefix_geoJSONfeature = "#geo-feature-"; //prefix for node ids for geoJSON features
	 const nodePrefix_geoJSONgeometry = "#geo-geometry-"; //prefix for node ids for geoJSON geometries
	 const nodePrefix_geoJSONproperties = "#geo-props-"; //prefix for node ids for geoJSON properties
	 const Predicate_geoPropSpecificity = "rdfs:comment"; //for notes about the specificity of a region
	 const Predicate_geoPropNote = "skos:note"; //for notes about a georeference
	 
	 
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
        
		  $this->addExternalEntityInfo = true;
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
	 
	 //make the long JSON from scratch, from a freshly gnerated short JSON
	 function getLongGeneratedByUUID($uuid){
		  
		  $this->addExternalEntityInfo = true;
		  $uuid = $this->security_check($uuid);
        $output = false; //not found
		  $manifestObj = new OCitems_Manifest;
		  $this->manifest = $manifestObj->getByUUID($uuid);
		  if(is_array($this->manifest)){
				$this->shortJSON = $this->generateShortByUUID($uuid);
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
					 
					 $getInfo = true;
					 if($this->addExternalEntityInfo){
						  if(isset($actVals[self::skipInfoURIkey])){
								$actVals = $actVals[self::skipInfoURIkey];
								$getInfo = false;
						  }
					 }
					 
					 if(!is_array($actVals)){
						  $newArrayNode[$key] = $actVals;
						  if($getInfo && ($key === "id" || $key === "@id")){ //only look at ID keys
								if(substr_count($actVals, ":") == 1){ //make sure their is only ":", otherwise not an ID to lookup 
									 $deRef = $this->getInfoForURI($actVals);
									 if(!is_array($deRef)){
										  $deRef = $uriObj->lookupURI($actVals);
									 }
									 if(is_array($deRef)){
										  if(isset($deRef["label"])){
												$newArrayNode["label"] = $deRef["label"];
												$this->noteLabelForURI($actVals, $deRef);
										  }
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
				
				if(strlen($manifestObj->classURI)>1){
					 $this->itemClassURI = $manifestObj->classURI;
				}
				
				$JSON_LD = array();
				$JSON_LD["@context"] = array(
					 "id" => "@id",
					 "type" => "@type");
				
				//add standard namespaces
				foreach($this->standardNamespaces as $abrevKey => $actURI){
					 $JSON_LD["@context"][$abrevKey] =  $actURI;
				}
				
				/*
				$JSON_LD["@context"]["skos:closeMatch"] = array("@type" => "@id");
				$JSON_LD["@context"]["skos:related"] = array("@type" => "@id");
				$JSON_LD["@context"]["rdfs:range"] = array("@type" => "@id");
				*/
				
				$JSON_LD["id"] = $this->uri;
				$JSON_LD["label"] = $this->label;
				$this->noteLabelForURI($this->uri, array("label" => $this->label));
				
				$JSON_LD["uuid"] = $this->uuid;
				if($this->itemClassURI){
					 $typeURI = $ocGenObj->makeURIfromAbbrev($this->itemClassURI);
					 $JSON_LD = $this->addTypesJSON($JSON_LD, $typeURI);
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
				
				$JSON_LD = $this->addGeoJSON($JSON_LD); //add geoJSON to the item
				
				$JSON_LD[self::Predicate_dcTermsPublished] = $this->published;
				$JSON_LD[self::Predicate_dcTermsIsPartOf][] = array("id" => $this->projectURI);
				
				$JSON_LD = $this->addSimpleAnnotationsJSON($JSON_LD);
				
				$output = $JSON_LD;
		  }
		  
		  return $output;
	 }
	 
	 function addTypesJSON($JSON_LD, $typeURI){
		  
		  $JSON_LD["oc-gen:type"][] = array("@id" => $typeURI);
		  
		  return $JSON_LD;
	 }
	 
	 //make the JSON for describing the item's context
	 function addContextsJSON($JSON_LD){
		  if(is_array($this->contexts)){
				foreach($this->contexts as $treeNodeID => $parentURIs){
					 $treeNodeEx = explode("-", $treeNodeID );
					 $treeNumber = $treeNodeEx[count($treeNodeEx)-1];
					 $contextNodeID = self::nodePrefix_contextPath.$treeNumber;
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
					 $this->geospace = $res;
					 $geoUse = array("id" => $this->uri);
					 $JSON_LD[self::Predicate_locationRef][] = $geoUse;
				}
		  }
		  else{
				$res = $chronoObj->getByUUID($this->uuid);
				$resArray[$this->uuid] = $res;
				if(is_array($res)){
					 $this->chronology = $res;
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
									 $this->geospace = $res;
									 $geoUse = array("id" => $parentURI);
								}
						  }
						  else{
								$res = $chronoObj->getByUUID($parentUUID);
								$resArray[$parentUUID] = $res;
								if(is_array($res)){
									 $this->chronology = $res;
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
				$assertedPredicates = array();
				$assertedObjects = array();
				
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
										  $vars[$predicateURI] = array("type" => $actType, "abrev" => $predicateShort, "uuid" => $row["predicateUUID"]);
									 }
									 else{
										  $predicateShort = $vars[$predicateURI]["abrev"];
									 }
								}
								else{
									 if(!array_key_exists($predicateURI, $links)){
										  $actLinkNumber = count($links) + 1;
										  $predicateShort = "link-".$actLinkNumber;
										  $links[$predicateURI] = array("type" => $actType, "abrev" => $predicateShort, "uuid" => $row["predicateUUID"]);
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
								$assertedObjects[$row["objectUUID"]] = $objectURI;
								$this->assertedObjects = $assertedObjects;
						  }
					 }
				}
				
				
				if(count($vars)>0){
					 foreach($vars as $predicateURIkey => $predArray){
						  $assertedPredicates[$predicateURIkey] = $predArray;
						  $predicateShort = $predArray["abrev"];
						  $JSON_LD["@context"][$predicateShort] = array("@id" => $predicateURIkey, "@type" => $predArray["type"]);
					 }
				}
				if(count($links)>0){
					 foreach($links as $predicateURIkey => $predArray){
						  $assertedPredicates[$predicateURIkey] = $predArray;
						  $predicateShort = $predArray["abrev"];
						  $JSON_LD["@context"][$predicateShort] = array("@id" => $predicateURIkey, "@type" => $predArray["type"]);
					 }
				}
				if(count($assertedPredicates)>0){
					 $this->assertedPredicates = $assertedPredicates;
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
				$persObj = new OCitems_Person;
				$pres = $persObj->getByUUID($this->uuid);
				if(is_array($pres)){
					 $JSON_LD["type"][] = array("id" => $persObj->foafType);
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
	 
	 
    
	 //adds GeoJSON organized geospatial data
	 function addGeoJSON($JSON_LD){
		  
		  if(is_array($this->geospace)){
				$geoSpace = $this->geospace;
				if($geoSpace["uuid"] == $this->uuid || !$this->addSelfGeoJSONonly){
					 
					 $itemGeoFeatures = array();
					 $itemGeoFeatures = $this->geoJSONaddReducedPrecisionGeoTile($geoSpace, $itemGeoFeatures);
					 $itemGeoFeatures = $this->geoJSONaddPolygon($geoSpace, $itemGeoFeatures);
					 $itemGeoFeatures = $this->geoJSONaddPoint($geoSpace, $itemGeoFeatures);
					 
					 $JSON_LD["@context"]["FeatureCollection"] = "http://geojson.org/geojson-spec.html#feature-collection-objects";
					 $JSON_LD["@context"]["Feature"] = "http://geovocab.org/spatial#Feature";
					 $JSON_LD["@context"]["features"] = "oc-gen:geojson-hasfeatures";
					 $JSON_LD["@context"]["properties"] = "oc-gen:geojson-hasproperties";
					 $JSON_LD["@context"]["geometry"] = "http://geovocab.org/geometry#geometry";
					 $JSON_LD["@context"]["Point"] = "http://geovocab.org/geometry#Point";
					 $JSON_LD["@context"]["Polygon"] = "http://geovocab.org/geometry#Polygon";
					 $JSON_LD["type"] = "FeatureCollection";
					 $JSON_LD["features"]= $itemGeoFeatures;
				}
		  }
		  
		  return $JSON_LD;
	 }
	 
	 
	 function geoJSONaddPolygon($geoSpace, $itemGeoFeatures){
	 	  
		  $geometryFound = false;
		  if(is_array($geoSpace["geomObj"])){
				if(isset($geoSpace["geomObj"]["geometry"])){
					 $geometryFound = $geoSpace["geomObj"]["geometry"];
				}
				else{
					 foreach($geoSpace["geomObj"] as $geoObj){
						  if(isset($geoObj["geometry"])){
								$geometryFound = $geoObj["geometry"];
								break;
						  }
					 }
				}
		  }
		  
		  if(is_array($geometryFound)){
				$activeFeatureNumber = count($itemGeoFeatures) + 1;
				$itemGeoFeature = array();
				$itemGeoFeature["id"] = self::nodePrefix_geoJSONfeature.$activeFeatureNumber;
				$itemGeoFeature["type"] = "Feature";
				$itemGeoFeature["geometry"] = $geometryFound;
				$itemGeoFeature["geometry"]["id"] = self::nodePrefix_geoJSONgeometry.$activeFeatureNumber;
				$itemGeoFeature["properties"] = $itemGeoFeature["properties"] = $this->geoJSONmakeProperties($geoSpace, "Polygon", $activeFeatureNumber);
				$itemGeoFeatures[] = $itemGeoFeature;
		  }

		  return $itemGeoFeatures;
	 }
	 
	 function geoJSONaddPoint($geoSpace, $itemGeoFeatures){
	 	  
		  $makePoint = true;
		  if(!$this->addPointFeaturesIfPolygon){
				if(is_array($geoSpace["geomObj"])){
					 if(isset($geoSpace["geomObj"]["geometry"])){
						  $makePoint = false;
					 }
					 else{
						  foreach($geoSpace["geomObj"] as $geoObj){
								if(isset($geoObj["geometry"])){
									 $makePoint = false;
									 break;
								}
						  }
					 }
				}
		  }
		  
		  if($makePoint){
				$activeFeatureNumber = count($itemGeoFeatures) + 1;
				$itemGeoFeature = array();
				$itemGeoFeature["id"] = self::nodePrefix_geoJSONfeature.$activeFeatureNumber;
				$itemGeoFeature["type"] = "Feature";
				$itemGeoFeature["geometry"]["type"] = "Point";
				$itemGeoFeature["geometry"]["id"] = self::nodePrefix_geoJSONgeometry.$activeFeatureNumber;
				$itemGeoFeature["geometry"]["coordinates"] = array($geoSpace["longitude"],
																				$geoSpace["latitude"]);
				$itemGeoFeature["properties"] = $itemGeoFeature["properties"] = $this->geoJSONmakeProperties($geoSpace, "Point", $activeFeatureNumber);
				$itemGeoFeatures[] = $itemGeoFeature;
		  }

		  return $itemGeoFeatures;
	 }
	 
	 
	 //generate a map tile polygon for a less specific point
	 function geoJSONaddReducedPrecisionGeoTile($geoSpace, $itemGeoFeatures){
		  
		  if($geoSpace["specificity"] < 0){
				
				$activeFeatureNumber = count($itemGeoFeatures) + 1;
				
				//item has reduced precision geographic data
				$itemGeoFeature = array();
				$itemGeoFeature["id"] = self::nodePrefix_geoJSONfeature.$activeFeatureNumber;
				$itemGeoFeature["type"] = "Feature";
				$itemGeoFeature["geometry"]["type"] = "Polygon";
				$itemGeoFeature["geometry"]["id"] = self::nodePrefix_geoJSONgeometry.$activeFeatureNumber;
				
				$coordinateArray = array();
				$polyArray = array();
				
				$geoObj = new GlobalMapTiles;
				$quadTreeTile = $geoObj->LatLonToQuadTree($geoSpace["latitude"], $geoSpace["longitude"], abs($geoSpace["specificity"]));
				$geoArray = $geoObj->QuadTreeToLatLon($quadTreeTile);
				
				$polyArray[] = array( $geoArray[1], $geoArray[0]);
				$polyArray[] = array( $geoArray[3], $geoArray[0]);
				$polyArray[] = array( $geoArray[3], $geoArray[2]);
				$polyArray[] = array( $geoArray[1], $geoArray[2]);
				$coordinateArray[] = $polyArray;
				$itemGeoFeature["geometry"]["coordinates"] = $coordinateArray;
				$itemGeoFeature["properties"] = $this->geoJSONmakeProperties($geoSpace, "Polygon", $activeFeatureNumber);
				$itemGeoFeatures[] = $itemGeoFeature;
		  }
		  
		  return $itemGeoFeatures;
	 }
	 
	 //make properties to describe a GeoJSON feature
	 function geoJSONmakeProperties($geoSpace, $featureType, $activeFeatureNumber){
		  
		  $ocGenObj = new OCitems_General;
		  $uriObj = new infoURI;
		  
		  $itemGeoProperties = array();
		  $itemGeoProperties["id"] = self::nodePrefix_geoJSONproperties.$activeFeatureNumber;
		  if($geoSpace["uuid"] != $this->uuid){
				$itemGeoProperties[self::Predicate_geoPropRefType] = "inferred";
				$sRes = $uriObj->lookupOCitem($geoSpace["uuid"], "subject");
				if(is_array($sRes)){
					 $itemGeoProperties[self::Predicate_locationRefLabel] = $sRes["label"];
				}
				$itemGeoProperties[self::Predicate_locationRef] = $ocGenObj->generateItemURI($geoSpace["uuid"], "subject");
		  }
		  else{
				$itemGeoProperties[self::Predicate_geoPropRefType] = "self";
		  }
		  
		  if($geoSpace["specificity"] < 0){
				if($featureType == "Polygon"){
					 $itemGeoProperties[self::Predicate_geoPropSpecificity] = "Item location available with intentionally reduced accuracy within this region";
				}
				else{
					 $itemGeoProperties[self::Predicate_geoPropSpecificity] = "Item location provided with intentionally reduced accuracy";
				}
		  }
		  else{
				$itemGeoProperties[self::Predicate_geoPropSpecificity] = "Item location provided to best current knowledge with no intentional reduction in accuracy";
		  }
		  
		  if(strlen($geoSpace["note"])>1){
				$itemGeoProperties[self::Predicate_geoPropNote] = $geoSpace["note"];
		  }
		   
		  return $itemGeoProperties;
	 }
	 
	 
	 
	 
	 
	 
	 //adds simple annotations to the data
	 function addSimpleAnnotationsJSON($JSON_LD){
		  
		  $ocGenObj = new OCitems_General;
		  $linkAnnotObj = new Links_linkAnnotation;
		  $linkAnnotObj->lookUpLabels = false; //don't look up the labels to linked entities
		  
		  $this->annotationEntities = array();
		  if(is_array($this->assertedPredicates)){
				foreach($this->assertedPredicates as $assertedPredicateURI => $predArray){
					 $predicateAbrev = $predArray["abrev"];
					 $uuid = $predArray["uuid"];
					 $actAnnotations = $linkAnnotObj->getAnnotationsByUUID($uuid);
					 if(is_array($actAnnotations)){
						  if($this->addExternalEntityInfo){
								$graphAnnotations = array("@id" => array(self::skipInfoURIkey => $assertedPredicateURI));
						  }
						  else{
								$graphAnnotations = array("@id" => $assertedPredicateURI);
						  }
						  foreach($actAnnotations as $actAnno){
								$predicate = $ocGenObj->abbreviateURI($actAnno["predicateURI"], $this->standardNamespaces);
								$object =  $ocGenObj->abbreviateURI($actAnno["objectURI"], $this->standardNamespaces);
								$graphAnnotations[$predicate][] = array("@id" => $object); //add the annotation to the predicate
						  }
						  
						  $JSON_LD["@graph"][] = $graphAnnotations;
					 }
				}
		  }
		  
		  if(is_array($this->assertedObjects)){
				foreach($this->assertedObjects as $uuid => $uri){
					 $actAnnotations = $linkAnnotObj->getAnnotationsByUUID($uuid);
					 if(is_array($actAnnotations)){
						  if($this->addExternalEntityInfo){
								$graphAnnotations = array("@id" => array(self::skipInfoURIkey => $uri));
						  }
						  else{
								$graphAnnotations = array("@id" =>  $uri);
						  }
						  foreach($actAnnotations as $actAnno){
								$predicate = $ocGenObj->abbreviateURI($actAnno["predicateURI"], $this->standardNamespaces);
								$object =  $ocGenObj->abbreviateURI($actAnno["objectURI"], $this->standardNamespaces);
								$graphAnnotations[$predicate][] = array("@id" => $object); //add the annotation to the predicate
						  }
						  
						  $JSON_LD["@graph"][] = $graphAnnotations;
					 }
				}
		  }
		  
		  return $JSON_LD;
	 }
	 
	 //note the label for a given uri in memory
	 function noteLabelForURI($uri, $info){	  
		  $infoURIs = $this->infoURIs;
		  $infoURIs[$uri] = $info;
		  $this->infoURIs = $infoURIs;
	 }
	 
	 //get the label for a given URI
	 function getInfoForURI($uri){
		  $info = false;
		  if(!is_array($uri) && $uri != null){
				$infoURIs = $this->infoURIs;
				if(array_key_exists($uri, $infoURIs)){
					 $info = $infoURIs[$uri];
				}
		  }
		  return $info;
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
