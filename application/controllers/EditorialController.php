<?php
/** Zend_Controller_Action */
require_once 'Zend/Controller/Action.php';

ini_set("memory_limit", "1024M");
// set maximum execution time to no limit
ini_set("max_execution_time", "0");

class EditorialController extends Zend_Controller_Action
{
    function init(){
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
		  Zend_Loader::loadClass('Links_linkEntity');
		  Zend_Loader::loadClass('Links_tempDC');
		  Zend_Loader::loadClass('infoURI');
    }
	 
	 public function projectAction(){
		  
		  
	 }
	 
	 public function annotationsAction(){
		  $requestParams =  $this->_request->getParams();
		  $genObj = new OCitems_General;
		  $linkAnnotObj = new Links_linkAnnotation;
		  if(!isset($requestParams["uuid"])){
				$uuid = false;
				$result = false;
		  }
		  else{
				$uuid = $requestParams["uuid"];
				$result = $linkAnnotObj->getAnnotationsByUUID($requestParams["uuid"]);
		  }
		  $this->view->requestParams = $requestParams;
		  $this->view->uuid = $uuid;
		  $this->view->result = $result;
	 }
}

