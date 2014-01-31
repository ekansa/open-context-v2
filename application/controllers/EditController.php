<?php
/** Zend_Controller_Action */
require_once 'Zend/Controller/Action.php';

ini_set("memory_limit", "1024M");
// set maximum execution time to no limit
ini_set("max_execution_time", "0");

class EditController extends Zend_Controller_Action
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
		  Zend_Loader::loadClass('OCitems_Property');
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
    }
	 
	 
	 private function error405($expectedMethod){
		  header("HTTP/1.0 405 Method Not Allowed");
		  $output = array("error" => "Need to use the HTTP $expectedMethod method.",
								"requestParams" => $this->_request->getParams()
								);
		  $genObj = new OCitems_General;
		  echo $genObj->JSONoutputString($output);
	 }
	 
	 
	 public function addAnnotationAction(){
		  $requestParams =  $this->_request->getParams();
		  $this->_helper->viewRenderer->setNoRender();
		  
		  if(!$this->getRequest()->isPost()){
				//not a post
				$this->error405("POST");
				exit;
		  }
		  else{
				
		  }
	 }
}

