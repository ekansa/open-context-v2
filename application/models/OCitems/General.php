<?php


//this class interacts with the database for accessing and changing Predicates
//predicates are defined in different projects
class OCitems_General {
    
	 public $db;
	 
	 public $URIabbreviations = array("http://opencontext.org/vocabularies/oc-general/" => "oc-gen");
	 
	 public $typeURImappings = array("subjects" => "subject",
												"media" => "media",
												"documents" => "document",
												"projects" => "project",
												"persons" => "person",
												"properties" => "property",
												"predicates" => "predicate",
												"tables" => "table"
												);
	 
	 //convert an array into a well-formatted JSON string
	 function JSONoutputString($array){
		  return json_encode($array, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
		  //return json_encode($array, 0);
	 }
	 
	 //get the item UUID from a URI
	 function itemUUIDfromURI($uri){
		  $uriEx = explode("/", $uri);
		  return $uriEx[count($uriEx)-1];
	 }
	 
	 //get the item Type from a URI
	 function itemTypeFromURI($uri){
		  $output = false;
		  $uriEx = explode("/", $uri);
		  $typePart = $uriEx[count($uriEx)-2];
		  $typeURImappings = $this->typeURImappings;
		  if(array_key_exists($typePart, $typeURImappings)){
				$output =  $typeURImappings[$typePart];
		  }
		  return $output;
	 }
	 
	 
	 //convert common URIs to common prefixs
	 function abbreviateURI($uri, $prefixDelim = ":"){
		  if(stristr($uri, "http://") || stristr($uri, "https://")){
				foreach($this->URIabbreviations as $uriKey => $abrev){
					 $uri = str_replace($uriKey, $abrev.$prefixDelim, $uri );
				}
		  }
		  return $uri;
	 }
	 
	 
    function generateUUID()    {
        $rawid = strtoupper(md5(uniqid(rand(), true)));
		  $workid = $rawid;
		  $byte = hexdec( substr($workid,12,2) );
		  $byte = $byte & hexdec("0f");
		  $byte = $byte | hexdec("40");
		  $workid = substr_replace($workid, strtoupper(dechex($byte)), 12, 2);
			
		  // build a human readable version
		  $rid = substr($rawid, 0, 8).'-'
				 .substr($rawid, 8, 4).'-'
				 .substr($rawid,12, 4).'-'
				 .substr($rawid,16, 4).'-'
				 .substr($rawid,20,12);
					  
					  
					  // build a human readable version
					  $wid = substr($workid, 0, 8).'-'
				 .substr($workid, 8, 4).'-'
				 .substr($workid,12, 4).'-'
				 .substr($workid,16, 4).'-'
				 .substr($workid,20,12);
         
        return $wid;   
    }
	 
	 //make an or condition for a SQL query
	 function makeORcondition($valueArray, $field, $table = false){
		  
		  $allCond = false;
		  
		  if($valueArray != false){
				if(!is_array($valueArray)){
					 $valueArray = array(0 => $valueArray);
				}
				
				if(!$table){
					 $fieldPrefix = $field;
				}
				else{
					 $fieldPrefix = $table.".".$field;
				}
				
				foreach($valueArray as $value){
					 $actCond = "$fieldPrefix = '$value'";
					 if(!$allCond ){
						  $allCond  = $actCond;
					 }
					 else{
						  $allCond  .= " OR ".$actCond;
					 }
				}
		  }
		  return $allCond ;
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
    
}
