<?php
/* This class creates files based on data saved for export.
 * It makes csv, zip (with csv), and gzipped csv files.
 * 
 */

class XMLjsonLD_Item  {
    
	 public $db; //database connection object
	 
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
	 
	 //class, usually used with subjects items
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
	 
	 //persons specific
	 public $surname; //person's last name
	 public $givenName; //persons first name
	 public $foafType = "foaf:Person"; 

	 public $contexts; //context array (for subjects)
	 public $children; //children array (for subjects)
	 public $observations; //observation array (has observation metadata, properties, notes, links, and linked data)
	 public $geospace; //array of geospatial data for the item
	 public $chronology; //array of chronological information for the item
	 
	 public $showRawObsData = false;
	 const localBaseURI = "http://opencontext/";
	 const officalBaseURI = "http://opencontext.org/";
	 
	 
	 function makeJSON_LD(){
		  
		  $this->uri = $this->validateURI($this->uri);
		  
		  $JSON_LD = array();
		  
		  $JSON_LD["@context"] = array(
				"types" => "@type",
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
		  $JSON_LD["label"] = trim($this->label);
		  $JSON_LD["uuid"] = $this->uuid;
		  //$JSON_LD["oc-gen:projectUUID"] = $this->projectUUID;
		  
		  if($this->itemClassURI){
				$JSON_LD["rdfs:type"][] = array("id" => $this->itemClassURI);
		  }
		 
		  if(is_array($this->contexts)){
				
				$contextTree = 1;
				foreach($this->contexts as $treeKey => $contextList){
					 
					 $actTree = array(	"id" => "#context-path-".$contextTree,
												"oc-gen:path-des" => $treeKey,
												"oc-gen:has-path-items" => $contextList
												);
					 
					 $JSON_LD["oc-gen:has-context-path"][] = $actTree;
					 
					 if($treeKey == "default"){
						  foreach($contextList as $actContext){
								
								//$JSON_LD["oc-gen:has-main-context"][] = $actContext;
						  }
					 }
					 else{
						  foreach($contextList as $actContext){
								$JSON_LD["oc-gen:has-alt-context"][] = $actContext;
						  }
						  $contextTree++;
					 }
					 
				}
		  }
		  
		  if(is_array($this->children)){
				
				$contextTree = 1;
				foreach($this->children as $treeKey => $childrenList){
					 
					 $actTree = array(	"id" => "#contents-".$contextTree,
												"oc-gen:path-des" => $treeKey,
												"oc-gen:contains" => $childrenList
												);
					 
					 $JSON_LD["oc-gen:has-contents"][] = $actTree;
					 
					 if($treeKey == "default"){
						  foreach($contextList as $actContext){
								
								//$JSON_LD["oc-gen:has-main-context"][] = $actContext;
						  }
					 }
					 else{
						  foreach($contextList as $actContext){
								$JSON_LD["oc-gen:has-alt-contents"][] = $actContext;
						  }
						  $contextTree++;
					 }
					 
				}
		  }
		  
		  if(is_array($this->observations)){
				$propNum = 1;
				$vars = array();
				$links = array();
				foreach($this->observations as $obsNumKey => $observation){
					 $obsNode = "#obs-".$obsNumKey;
					 $actObsOutput = array("id" => $obsNode,
												"oc-gen:sourceID" => $observation["sourceID"],
												"oc-gen:obsStatus" => $observation["status"]);
					 
					 if(isset($observation["properties"])){
						  if(is_array($observation["properties"])){
								
								foreach($observation["properties"] as $varURI => $varValues){
									 foreach($varValues as $values){
										  if(isset($values["id"])){
												$actType = "@id";
										  }
										  else{
												$actType = $values["types"];
										  }
										  /*
										  if(!$actType){
												echo $this->uuid;
												echo print_r($observation);
												die;
										  }
										  */
										  
										  if(!array_key_exists($varURI, $vars)){
												$actVarNumber = count($vars) + 1;
												$vars[$varURI] = array("types" => $actType, "abrev" => "var-".$actVarNumber);
										  }
									 }
								}
								
								foreach($vars as $varURI => $varArray){
									 $abrev = $varArray["abrev"];
									 $JSON_LD["@context"][$abrev] = array("@id" => $varURI,
																						"@type" => $varArray["types"]);
								
								}
								
								foreach($observation["properties"] as $varURI => $varValues){
									 $abrev = $vars[$varURI]["abrev"];
									 foreach($varValues as $values){
										  
										  if(isset($values["id"])){
												//$outputValue = $values["id"];
												$outputValue = array("id" => $values["id"]);
										  }
										  else{
												$actType = $values["types"];
												$outputValue = $values[$actType];
										  }
										  
										  //$actObsOutput[$varURI][] =  $outputValue;
										  $actObsOutput[$abrev][] =  $outputValue;
										  $propNum++;
									 }
								}
						  }
					 }
					 
					 if(isset($observation["notes"])){
						  if(is_array($observation["notes"])){
								$actObsOutput["oc-gen:has-note"] = $observation["notes"];
						  }
					 }
					 if(isset($observation["links"])){
						  if(is_array($observation["links"])){
								foreach($observation["links"] as $predicateKey => $objectURIs){
									 if(!array_key_exists($predicateKey, $links)){
										  $actLinkNumber = count($links) + 1;
										  $links[$predicateKey] = array("types" => "@id", "abrev" => "link-".$actLinkNumber);
									 }
								}
								
								foreach($links as $predicateKey => $linkArray){
									 $abrev = $linkArray["abrev"];
									 $JSON_LD["@context"][$abrev] = array("@id" => $predicateKey,
																						"@type" => $linkArray["types"]);
								}
								
								foreach($observation["links"] as $predicateKey => $objectURIs){
									 $abrev = $links[$predicateKey]["abrev"];
									 foreach($objectURIs as $objectURI){
										  //$actObsOutput[$abrev][] = $objectURI;
										  $actObsOutput[$abrev][] = array("id" => $objectURI);
									 }
								}
								
						  }
					 }
					 $JSON_LD["oc-gen:has-obs"][] = $actObsOutput;
				}
		  }
		 
		  if($this->itemType == "subjects"){
				if(is_array($this->geospace)){
					 $geospace = $this->geospace;
					 $JSON_LD["oc-gen:locationRef"][] = array("id" => $geospace["refURI"]);
				}
				if(is_array($this->chronology)){
					 $chronology = $this->chronology;
					 $JSON_LD["oc-gen:chronoRef"][] = array("id" => $chronology["refURI"]);
				}
		  }
		 
		  if($this->showRawObsData){
				$JSON_LD["rawobs"] = $this->observations;
		  }
		  $JSON_LD["dc-terms:published"] = $this->published;
		  $JSON_LD["dc-terms:license"][] = array("id" => $this->license);
		  if(is_array($this->creators)){
				foreach($this->creators as $pURI){
					 $JSON_LD["dc-terms:creator"][] = array("id" => $pURI);
				}
		  }
		  if(is_array($this->contributors)){
				foreach($this->contributors as $pURI){
					 $JSON_LD["dc-terms:contributor"][] = array("id" => $pURI);
				}
		  }
		  
		  if($this->fullURI){
				$JSON_LD["@context"]["dcat"] = "http://www.w3.org/ns/dcat#";
				
				$JSON_LD["oc-gen:has-primary-file"][] = array("id" => $this->fullURI,
																		  "dc-terms:hasFormat" => $this->mimeTypeURI,
																		  "dcat:size" => $this->fileSize +0
																		  );
		  }
		  
		  if($this->previewURI){
				$JSON_LD["oc-gen:has-preview-file"][] = array("id" => $this->previewURI,
																		  "dc-terms:hasFormat" => $this->previewMimeURI
																		  );
		  }
		  
		  if($this->thumbURI){
				$JSON_LD["oc-gen:has-thumb-file"][] = array("id" => $this->thumbURI,
																		  "dc-terms:hasFormat" => $this->thumbMimeURI
																		  );
		  }
		  
		  if($this->documentContents){
				$JSON_LD["oc-gen:has-content"] = $this->documentContents;
		  }
		  
		  if($this->itemType == "persons"){
				$JSON_LD["@context"]["foaf"] = "http://xmlns.com/foaf/spec/";
				$JSON_LD["rdfs:type"][] = array("id" => $this->foafType);
				$JSON_LD["foaf:familyName"] = $this->surname;
				$JSON_LD["foaf:givenName"] = $this->givenName;
		  }
		 
		  
		  $JSON_LD["dc-terms:isPartOf"][] = array("id" => $this->projectURI);
		  return $JSON_LD;
	 }
	 
	 //validates URIs
	 function validateURI($uri){
		  
		  $uri = str_replace(self::localBaseURI, self::officalBaseURI, $uri);
		  $uri = str_replace(".xml", "", $uri);
		  return $uri;
	 }
	 
	 
	 function assignSubjectClass($item_class){
		  
		  $legClassObj = new XMLjsonLD_LegacyClass;
		  $classURI = $legClassObj->getClassURI($item_class);
		  if($classURI){
				$this->itemClassURI = $classURI;
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
