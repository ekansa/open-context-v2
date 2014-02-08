<?php
/** Zend_Controller_Action */
require_once 'Zend/Controller/Action.php';

class SubjectsController extends Zend_Controller_Action
{
    
	 function init(){
		  Zend_Loader::loadClass('OCitems_Item');
		  Zend_Loader::loadClass('OCitems_Manifest');
		  Zend_Loader::loadClass('OCitems_DataCache');
		  Zend_Loader::loadClass('OCitems_General');
    }
	 
	 public function jsonShortAction(){
		  
		  $requestParams =  $this->_request->getParams();
		  $this->_helper->viewRenderer->setNoRender();
		  
		  Zend_Loader::loadClass('OCitems_Item');
		  Zend_Loader::loadClass('OCitems_Manifest');
		  Zend_Loader::loadClass('OCitems_DataCache');
		  
		  if(isset($requestParams["uuid"])){
				$uuid = $requestParams["uuid"];
		  }
		  
		  $genObj = new OCitems_General;
		  $genObj->startClock();
		  $itemObj = new OCitems_Item;
		  $ok = $itemObj->getShortByUUID($uuid);
		  
		  header('Content-Type: application/json; charset=utf8');
		  $output = $itemObj->shortJSON;
		  $output = $genObj->documentElapsedTime($output);
		  echo $genObj->JSONoutputString($output);
	 }
	 
	 public function jsonLongAction(){
		  
		  $requestParams =  $this->_request->getParams();
		  $this->_helper->viewRenderer->setNoRender();
		  
		  Zend_Loader::loadClass('OCitems_Item');
		  Zend_Loader::loadClass('OCitems_Manifest');
		  Zend_Loader::loadClass('OCitems_DataCache');
		  Zend_Loader::loadClass('OCitems_Manifest');
		  Zend_Loader::loadClass('OCitems_DataCache');
		  Zend_Loader::loadClass('OCitems_Assertions');
		  Zend_Loader::loadClass('OCitems_String');
		  Zend_Loader::loadClass('OCitems_Geodata');
		  Zend_Loader::loadClass('OCitems_Chronodata');
		  Zend_Loader::loadClass('OCitems_Predicate');
		  Zend_Loader::loadClass('OCitems_Type');
		  Zend_Loader::loadClass('OCitems_MediaFile');
		  Zend_Loader::loadClass('OCitems_Identifiers');
		  Zend_Loader::loadClass('Links_linkAnnotation');
		  Zend_Loader::loadClass('Links_linkEntity');
		  Zend_Loader::loadClass('infoURI');
		  
		  if(isset($requestParams["uuid"])){
				$uuid = $requestParams["uuid"];
		  }
		  
		  $genObj = new OCitems_General;
		  $genObj->startClock();
		  $itemObj = new OCitems_Item;
		  $ok = $itemObj->getLongByUUID($uuid);
		  
		  header('Content-Type: application/json; charset=utf8');
		  $output = $itemObj->longJSON;
		  $output = $genObj->documentElapsedTime($output);
		  echo $genObj->JSONoutputString($output);
	 }
   
	
	 public function jsonGenShortAction(){
		  
		  $requestParams =  $this->_request->getParams();
		  $this->_helper->viewRenderer->setNoRender();
		  
		  Zend_Loader::loadClass('OCitems_Item');
		  Zend_Loader::loadClass('OCitems_Manifest');
		  Zend_Loader::loadClass('OCitems_DataCache');
		  Zend_Loader::loadClass('OCitems_Assertions');
		  Zend_Loader::loadClass('OCitems_String');
		  Zend_Loader::loadClass('OCitems_Geodata');
		  Zend_Loader::loadClass('OCitems_Chronodata');
		  Zend_Loader::loadClass('OCitems_Predicate');
		  Zend_Loader::loadClass('OCitems_Identifiers');
		  Zend_Loader::loadClass('Links_linkAnnotation');
		  Zend_Loader::loadClass('Links_linkEntity');
		  Zend_Loader::loadClass('infoURI');
		  
		  
		  if(isset($requestParams["uuid"])){
				$uuid = $requestParams["uuid"];
		  }
		  
		  $genObj = new OCitems_General;
		  $genObj->startClock();
		  $itemObj = new OCitems_Item;
		  $output = $itemObj->generateShortByUUID($uuid);
		  
		  header('Content-Type: application/json; charset=utf8');
		  $output = $genObj->documentElapsedTime($output);
		  echo $genObj->JSONoutputString($output);
	 }
	 
}

