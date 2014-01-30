<?php
/** Zend_Controller_Action */
require_once 'Zend/Controller/Action.php';

class DocumentsController extends Zend_Controller_Action
{
    
	 function init(){
		  Zend_Loader::loadClass('OCitems_Item');
		  Zend_Loader::loadClass('OCitems_Manifest');
		  Zend_Loader::loadClass('OCitems_DataCache');
		  Zend_Loader::loadClass('OCitems_General');
		  Zend_Loader::loadClass('OCitems_Document');
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
		  $itemObj = new OCitems_Item;
		  $ok = $itemObj->getShortByUUID($uuid);
		  
		  header('Content-Type: application/json; charset=utf8');
		  
		  echo $genObj->JSONoutputString($itemObj->shortJSON);
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
		  Zend_Loader::loadClass('OCitems_Property');
		  Zend_Loader::loadClass('OCitems_MediaFile');
		  Zend_Loader::loadClass('OCitems_Identifiers');
		  Zend_Loader::loadClass('Links_linkAnnotation');
		  Zend_Loader::loadClass('infoURI');
		  
		  if(isset($requestParams["uuid"])){
				$uuid = $requestParams["uuid"];
		  }
		  
		  $genObj = new OCitems_General;
		  $itemObj = new OCitems_Item;
		  $ok = $itemObj->getLongByUUID($uuid);
		  
		  header('Content-Type: application/json; charset=utf8');
		  
		  echo $genObj->JSONoutputString($itemObj->longJSON);
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
		  Zend_Loader::loadClass('OCitems_MediaFile');
		  Zend_Loader::loadClass('OCitems_Identifiers');
		  Zend_Loader::loadClass('Links_linkAnnotation');
		  Zend_Loader::loadClass('infoURI');
		  
		  
		  if(isset($requestParams["uuid"])){
				$uuid = $requestParams["uuid"];
		  }
		  
		  $genObj = new OCitems_General;
		  $itemObj = new OCitems_Item;
		  $generatedShortJSON = $itemObj->generateShortByUUID($uuid);
		  
		  header('Content-Type: application/json; charset=utf8');
		  
		  echo $genObj->JSONoutputString($generatedShortJSON);
	 }
	 
}

