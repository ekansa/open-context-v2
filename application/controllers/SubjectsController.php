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
		  $itemObj = new OCitems_Item;
		  $ok = $itemObj->getShortByUUID($uuid);
		  
		  header('Content-Type: application/json; charset=utf8');
		  
		  echo $genObj->JSONoutputString($itemObj->shortJSON);
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

