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
		  Zend_Loader::loadClass('Links_linkEntity');
		  Zend_Loader::loadClass('Links_tempDC');
		  Zend_Loader::loadClass('infoURI');
    }
	 
	 
	 private function error405($expectedMethod){
		  header('HTTP/1.0 405 Method Not Allowed');
		  header('Content-Type: application/json; charset=utf8');
		  $output = array("errors" => array("Need to use the HTTP $expectedMethod method."),
								"requestParams" => $this->_request->getParams()
								);
		  $genObj = new OCitems_General;
		  echo $genObj->JSONoutputString($output);
	 }
	 
	 private function error400($errors){
		  header('HTTP/1.0 400 Bad Request');
		  header('Content-Type: application/json; charset=utf8');
		  $output = array("errors" => $errors,
								"requestParams" => $this->_request->getParams()
								);
		  $genObj = new OCitems_General;
		  echo $genObj->JSONoutputString($output);
	 }
	 
	 
	 public function getAnnotationsAction(){
		  $requestParams =  $this->_request->getParams();
		  $this->_helper->viewRenderer->setNoRender();
		  
		  $genObj = new OCitems_General;
		  $linkAnnotObj = new Links_linkAnnotation;
		  if(!isset($requestParams["uuid"])){
				$errors = array("Need 'uuid' parameter");
				$this->error400($errors);
		  }
		  else{
				$result = $linkAnnotObj->getAnnotationsByUUID($requestParams["uuid"]);
				$output = array("response" => $result,
										  "errors" => false,
										  "requestParams" => $requestParams);
				header('Content-Type: application/json; charset=utf8');
				echo $genObj->JSONoutputString($output);
		  }
	 }
	 
	 
	 public function addAnnotationAction(){
		  $requestParams =  $this->_request->getParams();
		  $this->_helper->viewRenderer->setNoRender();
		  
		  if(!$this->getRequest()->isPost()){
				$this->error405("POST"); //not a post, throw an error
				exit;
		  }
		  else{
				$genObj = new OCitems_General;
				$linkAnnotObj = new Links_linkAnnotation;
				$data = $genObj->validateInput($requestParams, $linkAnnotObj->expectedSchema);
				if(!$data){
					 $this->error400($genObj->errors); //throw an error explaining what was expected
					 exit;
				}
				else{
					 $ok = $linkAnnotObj->createRecord($data);
					 $output = array("response" => $ok,
										  "errors" => false,
										  "data" => $data);
					 header('Content-Type: application/json; charset=utf8');
					 echo $genObj->JSONoutputString($output);
				}
		  }
	 }
}

