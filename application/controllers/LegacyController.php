<?php
/** Zend_Controller_Action */
require_once 'Zend/Controller/Action.php';

ini_set("memory_limit", "1024M");
// set maximum execution time to no limit
ini_set("max_execution_time", "0");

class LegacyController extends Zend_Controller_Action
{
    
	 
	 public function subjectJsonAction(){
		  
		  $this->_helper->viewRenderer->setNoRender();
		  
		  Zend_Loader::loadClass('XMLjsonLD_Item');
		  Zend_Loader::loadClass('XMLjsonLD_XpathBasics');
		  Zend_Loader::loadClass('XMLjsonLD_CompactXML');
		  Zend_Loader::loadClass('XMLjsonLD_LegacyClass');
		  Zend_Loader::loadClass('OCitems_Predicate');
		  Zend_Loader::loadClass('OCitems_Geodata');
		  Zend_Loader::loadClass('OCitems_Chronodata');
		  Zend_Loader::loadClass('OCitems_LegacyIDs');
		  Zend_Loader::loadClass('OCitems_General');
		  Zend_Loader::loadClass('OCitems_DataCache');
		  
		  if(isset($_GET["uuid"])){
				$uuid = $_GET["uuid"];
		  }
		  else{
				$uuid = "34050A02-CEF3-40AF-81BC-AB2FF5506878";
		  }
		  $uri = "http://opencontext/subjects/".$uuid;
		  
		  $jsonLDObj = new XMLjsonLD_Item;
		  //$jsonLDObj->showRawObsData = true;
		  $xpathsObj = new XMLjsonLD_XpathBasics;
		  $jsonLDObj = $xpathsObj->URIconvert($uri , $jsonLDObj);
		  $jsonLDObj->uri = $uri;
		  $JSONld = $jsonLDObj->makeJSON_LD();
		  if(!isset($_GET["xml"])){
				$jsonString = json_encode($JSONld,  JSON_PRETTY_PRINT);
				$dataCacheObj = new OCitems_DataCache;
				$data = array("uuid" => $uuid, "content" => $jsonString);
				//$ok = $dataCacheObj->createRecord($data);
				$ok = true;
				if(!$ok){
					 $output = Zend_Json::encode(array("error" => $dataCacheObj->error));
				}
				else{
					 $output = $jsonString;
				}
				header('Content-Type: application/json; charset=utf8');
				//echo Zend_Json::encode($output,  JSON_PRETTY_PRINT);
				echo $output;
		  }
		  else{
			  $compactObj = new XMLjsonLD_CompactXML;
			  $doc = $compactObj->makeCompactXML($JSONld);
			  $xmlString = $doc->saveXML();
			  header('Content-Type: application/xml; charset=utf8');
			  echo $xmlString;
		  }
	 }
	 
	 
	 
	 public function mediaJsonAction(){
		  
		  $this->_helper->viewRenderer->setNoRender();
		  
		  Zend_Loader::loadClass('XMLjsonLD_Item');
		  Zend_Loader::loadClass('XMLjsonLD_XpathBasics');
		  Zend_Loader::loadClass('XMLjsonLD_CompactXML');
		  Zend_Loader::loadClass('XMLjsonLD_LegacyClass');
		  Zend_Loader::loadClass('OCitems_Predicate');
		  Zend_Loader::loadClass('OCitems_Geodata');
		  Zend_Loader::loadClass('OCitems_Chronodata');
		  Zend_Loader::loadClass('OCitems_LegacyIDs');
		  Zend_Loader::loadClass('OCitems_General');
		  Zend_Loader::loadClass('XMLjsonLD_LegacyMimeType');
		  Zend_Loader::loadClass('OCitems_MediaFile');
		  
		  $jsonLDObj = new XMLjsonLD_Item;
		  //$jsonLDObj->showRawObsData = true;
		  $xpathsObj = new XMLjsonLD_XpathBasics;
		  $uri = "http://opencontext/media/FF9CAF01-5765-4F0D-2B5F-4BF750452A13";
		  $jsonLDObj = $xpathsObj->URIconvert($uri , $jsonLDObj);
		  $jsonLDObj->uri = $uri;
		  $JSONld = $jsonLDObj->makeJSON_LD();
		  if(!isset($_GET["xml"])){
				
				header('Content-Type: application/json; charset=utf8');
				//echo Zend_Json::encode($output,  JSON_PRETTY_PRINT);
				echo json_encode($JSONld,  JSON_PRETTY_PRINT);
		  }
		  else{
			  $compactObj = new XMLjsonLD_CompactXML;
			  $doc = $compactObj->makeCompactXML($JSONld);
			  $xmlString = $doc->saveXML();
			  header('Content-Type: application/xml; charset=utf8');
			  echo $xmlString;
		  }
	 }
	 
	 
	 public function documentJsonAction(){
		  
		  $this->_helper->viewRenderer->setNoRender();
		  
		  Zend_Loader::loadClass('XMLjsonLD_Item');
		  Zend_Loader::loadClass('XMLjsonLD_XpathBasics');
		  Zend_Loader::loadClass('XMLjsonLD_CompactXML');
		  Zend_Loader::loadClass('XMLjsonLD_LegacyClass');
		  Zend_Loader::loadClass('OCitems_Predicate');
		  Zend_Loader::loadClass('OCitems_Geodata');
		  Zend_Loader::loadClass('OCitems_Chronodata');
		  Zend_Loader::loadClass('OCitems_LegacyIDs');
		  Zend_Loader::loadClass('OCitems_General');
		  Zend_Loader::loadClass('XMLjsonLD_LegacyMimeType');
		  Zend_Loader::loadClass('OCitems_MediaFile');
		  
		  $jsonLDObj = new XMLjsonLD_Item;
		  //$jsonLDObj->showRawObsData = true;
		  $xpathsObj = new XMLjsonLD_XpathBasics;
		  $uri = "http://opencontext/documents/78AA0116-6D4F-453E-8E6E-CB6446E063E4";
		  $jsonLDObj = $xpathsObj->URIconvert($uri , $jsonLDObj);
		  $jsonLDObj->uri = $uri;
		  $JSONld = $jsonLDObj->makeJSON_LD();
		  if(!isset($_GET["xml"])){
				
				header('Content-Type: application/json; charset=utf8');
				//echo Zend_Json::encode($output,  JSON_PRETTY_PRINT);
				echo json_encode($JSONld,  JSON_PRETTY_PRINT);
		  }
		  else{
			  $compactObj = new XMLjsonLD_CompactXML;
			  $doc = $compactObj->makeCompactXML($JSONld);
			  $xmlString = $doc->saveXML();
			  header('Content-Type: application/xml; charset=utf8');
			  echo $xmlString;
		  }
	 }
	 
	 
	 public function convertSubjectsAction(){
		  
		  $this->_helper->viewRenderer->setNoRender();
		  
		  Zend_Loader::loadClass('XMLjsonLD_Item');
		  Zend_Loader::loadClass('XMLjsonLD_XpathBasics');
		  Zend_Loader::loadClass('XMLjsonLD_CompactXML');
		  Zend_Loader::loadClass('XMLjsonLD_LegacyClass');
		  Zend_Loader::loadClass('XMLjsonLD_LegacySave');
		  
		  Zend_Loader::loadClass('OCitems_General');
		  Zend_Loader::loadClass('OCitems_Predicate');
		  Zend_Loader::loadClass('OCitems_Geodata');
		  Zend_Loader::loadClass('OCitems_Chronodata');
		  Zend_Loader::loadClass('OCitems_LegacyIDs');
		  Zend_Loader::loadClass('OCitems_Type');
		  Zend_Loader::loadClass('OCitems_String');
		  Zend_Loader::loadClass('OCitems_Assertions');
		  Zend_Loader::loadClass('OCitems_Manifest');
		  Zend_Loader::loadClass('OCitems_DataCache');
		  
		  
		  $legacySaveObj = new XMLjsonLD_LegacySave;
		  if(isset($_GET["maxLimit"])){
				$legacySaveObj->maxLimit = true;
		  }
		  
		  $listURL = "http://opencontext/sets/.json?recs=100";
		  $legacySaveObj->retrieveBaseSubjectURI = "http://opencontext/subjects/";
		  $legacySaveObj->JSONlist($listURL);
		  $output = array("done" => $legacySaveObj->doneURIs, "errors" => $legacySaveObj->errors);
		  header('Content-Type: application/json; charset=utf8');
		  echo json_encode($output,  JSON_PRETTY_PRINT);
	 }
	 
	 
	 public function convertRemoteAction(){
		  
		  $this->_helper->viewRenderer->setNoRender();
		  
		  $url = "http://oc2/legacy/convert-subjects?maxLimit=1";
		  $continue = true;
		  while($continue){
				$continue = false;
				$url = "http://oc2/legacy/convert-subjects?maxLimit=1";
				@$jsonResponse = file_get_contents($url);
				if($jsonResponse){
					 $jsonArray = Zend_Json::decode($jsonResponse);
					 if(is_array($jsonArray)){
						  if($jsonArray["done"] > 0){
								$continue = true;
						  }
					 }
				}
				else{
					 sleep(1);
					 $url = "http://oc2/legacy/convert-subjects?maxLimit=1";
					 @$jsonResponse = file_get_contents($url);
					 if(is_array($jsonArray)){
						  if($jsonArray["done"] > 0){
								$continue = true;
						  }
					 }
				}
				
		  }
		 
		  header('Location: http://oc2/legacy/convert-remote');
		  exit;
		  
		  
	 }
	 
	  public function convertToDoListAction(){
		  
		  $this->_helper->viewRenderer->setNoRender();
		  
		  Zend_Loader::loadClass('XMLjsonLD_Item');
		  Zend_Loader::loadClass('XMLjsonLD_XpathBasics');
		  Zend_Loader::loadClass('XMLjsonLD_CompactXML');
		  Zend_Loader::loadClass('XMLjsonLD_LegacyClass');
		  Zend_Loader::loadClass('XMLjsonLD_LegacySave');
		  Zend_Loader::loadClass('XMLjsonLD_LegacyMimeType');
		  
		  Zend_Loader::loadClass('OCitems_General');
		  Zend_Loader::loadClass('OCitems_Predicate');
		  Zend_Loader::loadClass('OCitems_Geodata');
		  Zend_Loader::loadClass('OCitems_Chronodata');
		  Zend_Loader::loadClass('OCitems_LegacyIDs');
		  Zend_Loader::loadClass('OCitems_Type');
		  Zend_Loader::loadClass('OCitems_String');
		  Zend_Loader::loadClass('OCitems_Assertions');
		  Zend_Loader::loadClass('OCitems_Manifest');
		  Zend_Loader::loadClass('OCitems_DataCache');
		  Zend_Loader::loadClass('OCitems_MediaFile');
		  Zend_Loader::loadClass('OCitems_Identifiers');
		  Zend_Loader::loadClass('OCitems_Document');
		  Zend_Loader::loadClass('OCitems_Person');
		  
		  Zend_Loader::loadClass('Links_linkAnnotation');
		  Zend_Loader::loadClass('Links_tempDC');
		  
		  $legacySaveObj = new XMLjsonLD_LegacySave;
		  $legacySaveObj->retrieveBaseSubjectURI = "http://opencontext/subjects/";
		  $legacySaveObj->retrieveBaseMediaURI = "http://opencontext.org/media/";
		  $legacySaveObj->retrieveBaseDocURI = "http://opencontext.org/documents/";
		  $legacySaveObj->retrieveBasePersonURI = "http://opencontext.org/persons/";
		  $legacySaveObj->retrieveBaseProjectURI = "http://opencontext.org/projects/";
		  
		  $legacySaveObj->toDoList("project");
		  $output = array("done" => $legacySaveObj->doneURIs, "existing" => $legacySaveObj->existingURIs, "errors" => $legacySaveObj->errors);
		  header('Content-Type: application/json; charset=utf8');
		  echo json_encode($output,  JSON_PRETTY_PRINT);
	 }
	 
	 
	 public function cacheViewAction(){
		  
		  $this->_helper->viewRenderer->setNoRender();
		  
		  Zend_Loader::loadClass('OCitems_DataCache');
		  if(isset($_GET["uuid"])){
				$uuid = $_GET["uuid"];
		  }
		  else{
				$uuid = "FC96A49E-FE12-488B-4EFF-02D4E147B885";
		  }
		 
		  $dataCacheObj = new OCitems_DataCache;
		  $dataCacheObj->getByUUID($uuid);
		  
		  header('Content-Type: application/json; charset=utf8');
		  echo $dataCacheObj->content;
	 }
   
}

