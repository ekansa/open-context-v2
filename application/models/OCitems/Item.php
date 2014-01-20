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

	 public $contexts; //context array (for subjects)
	 public $children; //children array (for subjects)
	 public $observations; //observation array (has observation metadata, properties, notes, links, and linked data)
	 public $geospace; //array of geospatial data for the item
	 public $chronology; //array of chronological information for the item
	 
	 
   
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
				
				$output = $JSON_LD;
		  }
		  
		  return $output;
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
