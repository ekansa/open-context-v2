<?php
/* This class maps old legacy class names to URIs
 * 
 */

class XMLjsonLD_LegacyMimeType {
    
	 public $db; //database connection object
	 
	 
	 
	 public $mimeTypeArray = array("image/jpeg" => "http://purl.org/NET/mediatypes/image/jpeg",
											 "jpeg" => "http://purl.org/NET/mediatypes/image/jpeg",
											 "image/tiff" => "http://purl.org/NET/mediatypes/image/tiff",
											 "image/png" => "http://purl.org/NET/mediatypes/image/png",
											 "image/gif" => "http://purl.org/NET/mediatypes/image/gif",
											 "gif" => "http://purl.org/NET/mediatypes/image/gif",
											 
											 "application/pdf" => "http://purl.org/NET/mediatypes/application/pdf",
											 "pdf" => "http://purl.org/NET/mediatypes/application/pdf",
											 
											 "video/x-msvideo" => "http://purl.org/NET/mediatypes/video/x-msvideo",
											 "video/mp4" => "http://purl.org/NET/mediatypes/video/mp4",
											 );
	 
	 
	 public $extensionArray = array("jpg" => "http://purl.org/NET/mediatypes/image/jpeg",
											  "psd" => "http://purl.org/NET/mediatypes/image/vnd.adobe.photoshop",
											  "gif" => "http://purl.org/NET/mediatypes/image/gif",
											  "kml" => "http://purl.org/NET/mediatypes/application/vnd.google-earth.kml+xml",
											  "kmz" => "http://purl.org/NET/mediatypes/application/vnd.google-earth.kmz",
												"pdf" => "http://purl.org/NET/mediatypes/application/pdf",
												"tif" =>  "http://purl.org/NET/mediatypes/image/tiff",
												"tiff" => "http://purl.org/NET/mediatypes/image/tiff",
												"avi" => "http://purl.org/NET/mediatypes/video/x-msvideo"
											  );
	 
	 
	 //look up the MimeType URI for a given mimetype and / or resourceURI
	 function getMimeTypeURI($legacyMimeType, $resourceURI = false){
		  $output = false;
		  $legacyMimeType = strtolower($legacyMimeType);
		  if($legacyMimeType != false){
				foreach($this->mimeTypeArray as $legacyKey => $uri){
					 if($legacyMimeType == $legacyKey){
						  $output = $uri;
						  break;
					 }
				}
		  }
		  if($resourceURI !=false && !$output){
				$resEx = explode(".", $resourceURI);
				$resExtension = $resEx[count($resEx) - 1];
				$resExtension = strtolower($resExtension);
				foreach($this->extensionArray as $exentionKey => $uri){
					 if($resExtension == $exentionKey){
						  $output = $uri;
						  break;
					 }
				}
		  }
		  
		  return $output;		  
	 }
	 
	 
	 //look up the MimeType URI for a given mimetype and / or resourceURI
	 function getGeneralMediaType($mimeTypeURI){
		  $output = false;
		  if($mimeTypeURI != false){
				$uriEx = explode("/", $mimeTypeURI);
				$output = $uriEx[count($uriEx) - 2]; //second from the last part of the URI is the general media type
		  }
		  
		  return $output;		  
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
