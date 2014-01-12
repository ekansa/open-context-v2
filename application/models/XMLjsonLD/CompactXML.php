<?php
/* This class creates a compact XML representation of
 * the JSON-LD item to store in the database without annoying escape characters
 * 
 */

class XMLjsonLD_CompactXML  {
    
	 public $db; //database connection object
	 public $compactXML;
	 public $JSONld;
	 
	 const compactXMLnamespace = "http://opencontext.org/schema/xml-ld";
	 const xhtmlNamespace = "http://www.w3.org/1999/xhtml";
	 
	 //take the compact XML string and make it a JSON-LD array
	 function makeJSONld($compactXMLstring){
		  $JSONld = array();
		  $xml = simplexml_load_string($compactXMLstring);
		  $xml->registerXPathNamespace("oc", self::compactXMLnamespace);
		  foreach($xml->xpath("/oc:root") as $XMLnode) {
				$JSONld = $this->JSONbuild($XMLnode);
		  }
		  $this->JSONld = $JSONld;
		  return  $JSONld;
	 }
	 
	 //recurive function to make the JSON-LD array
	 function JSONbuild($XMLnode){
		  
		  $XMLnode->registerXPathNamespace("oc", self::compactXMLnamespace);
		  if($XMLnode->xpath("oc:n")){
				$JSONnode = array();
				foreach($XMLnode->xpath("oc:n") as $subXMLnode) {
					 $subXMLnode->registerXPathNamespace("oc", self::compactXMLnamespace);
					 $key = false;
					 if( $subXMLnode->xpath("@key") ){
						  foreach($subXMLnode->xpath("@key") as $keyNode) {
								$key = (string)$keyNode;
						  }
					 }
	 				 if( $subXMLnode->xpath("oc:n") ){
						  $subJSON = $this->JSONbuild($subXMLnode);
					 }
					 elseif($subXMLnode->xpath("@xhtml")){
						  $subXMLnode->registerXPathNamespace("xhtml", self::xhtmlNamespace);
						  if($subXMLnode->xpath("xhtml:div")){
								foreach ($subXMLnode->xpath("xhtml:div") as $divNote) {
									 $stringNote = $divNote->asXML();
									 $subJSON = $stringNote; //add hhtml
								}
						  }
					 }
					 else{
						  $subJSON = (string)$subXMLnode;
					 }
					 
					 if($key != false){
						  $JSONnode[$key] = $subJSON;
					 }
					 else{
						  $JSONnode[] = $subJSON;
					 }
					 
				}
		  }
		 
		  return $JSONnode;
	 }
	 
	 
	 //convert JSON-LD array into a compact XML document
	 function makeCompactXML($JSONld) {
		  
		  $dom = $doc = new DOMDocument("1.0", "utf-8");
		  $doc->formatOutput = true;
		  $root = $doc->createElement("root");
		  $root->setAttribute("xmlns", self::compactXMLnamespace);
		  $doc->appendChild($root);
		  $this->compactXML = $doc;
		  $this->recursiveXML($JSONld, $root);
		  return $doc;
	 }
	 
	 //recursive function to convert nodes in the array to compact XML nodes
	 function recursiveXML($arrayNode, $actDomNode){
		  $doc = $this->compactXML;
		  if(is_array($arrayNode)){
				foreach($arrayNode as $key => $actVals){
					 $newActNode = $doc->createElement("n");
					 if(strlen($key)> 0 && !is_numeric($key)){
						  /*
						  $XMLkey = str_replace("@", "", $key);
						  if($XMLkey != $key){
								$newActNode->setAttribute("at", 1);
						  }
						  */
						  $XMLkey = $key;
						  $newActNode->setAttribute("key", $XMLkey);
					 }
					 $actDomNode->appendChild($newActNode);
					 if(is_array($actVals)){
						  $this->recursiveXML($actVals, $newActNode, $key);
					 }
					 else{
						  
						  @$xml = simplexml_load_string($actVals);
						  
						  if($xml){
								$newActNode->setAttribute("xhtml", true);
								$elementCC = $doc->createElement("div");
								$elementCC->setAttribute("xmlns", self::xhtmlNamespace);
								$contentFragment = $doc->createDocumentFragment();
								$contentFragment->appendXML($actVals);  // add the XHTML fragment
								$elementCC->appendChild($contentFragment);
								$newActNode->appendChild($elementCC);
						  }
						  else{
								$newActNodeText = $doc->createTextNode($actVals);
								$newActNode->appendChild($newActNodeText);
						  }
					 }
				}
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
