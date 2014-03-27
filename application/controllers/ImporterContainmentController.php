<?php
/** Zend_Controller_Action */
require_once 'Zend/Controller/Action.php';

ini_set("memory_limit", "1024M");
// set maximum execution time to no limit
ini_set("max_execution_time", "0");

class ImporterContainmentController extends Zend_Controller_Action
{
    function init(){
		  Zend_Loader::loadClass('Importer_FieldLinks');
		  Zend_Loader::loadClass('Importer_Subjects');
		  Zend_Loader::loadClass('Importer_UploadTab');
		  
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
	 
	 
	 private function error405($expectedMethod){
		  header('HTTP/1.0 405 Method Not Allowed');
		  header('Content-Type: application/json; charset=utf8');
		  $output = array("result" => false,
								"errors" => array("Need to use the HTTP $expectedMethod method."),
								"requestParams" => $this->_request->getParams()
								);
		  $genObj = new OCitems_General;
		  echo $genObj->JSONoutputString($output);
	 }
	 
	 private function error400($errors){
		  header('HTTP/1.0 400 Bad Request');
		  header('Content-Type: application/json; charset=utf8');
		  $output = array("result" => false,
								"errors" => $errors,
								"requestParams" => $this->_request->getParams()
								);
		  $genObj = new OCitems_General;
		  echo $genObj->JSONoutputString($output);
	 }
	 
	 public function getContainmentFieldsAction(){
		  $requestParams =  $this->_request->getParams();
		  $this->_helper->viewRenderer->setNoRender();
		  
		  $fieldLinksObj = new Importer_FieldLinks;

		  if(!isset($requestParams["sourceID"])){
				$errors = array("Need 'sourceID' parameter");
				$this->error400($errors);
		  }
		  else{
				$genObj = new OCitems_General;
				$fieldLinksObj = new Importer_FieldLinks;
				$fieldLinksObj->sourceID = $requestParams["sourceID"];
				$result = $fieldLinksObj->getContainmentFields();
				$output = array("result" => $result,
										  "errors" => false,
										  "requestParams" => $requestParams);
				header('Content-Type: application/json; charset=utf8');
				echo $genObj->JSONoutputString($output);
		  }
	 }
	 
	 public function addFieldContainmentAction(){
		  $requestParams =  $this->_request->getParams();
		  $this->_helper->viewRenderer->setNoRender();
		  
		  if(!$this->getRequest()->isPost()){
				$this->error405("POST"); //not a post, throw an error
				exit;
		  }
		  else{
				$genObj = new OCitems_General;
				$fieldLinksObj = new Importer_FieldLinks;
				$data = $genObj->validateInput($requestParams, $fieldLinksObj->expectedContainSchema);
				if(!$data){
					 $this->error400($genObj->errors); //throw an error explaining what was expected
					 exit;
				}
				else{
					 $result = $fieldLinksObj->addContainmentLink($data);
					 
					 if(isset($requestParams["json"])){
						  $output = array("result" => $result,
												"errors" => $fieldLinksObj->errors,
												"requestParams" => $requestParams);
						  header('Content-Type: application/json; charset=utf8');
						  echo $genObj->JSONoutputString($output);
					 }
					 else{
						  header("Location: ../editorial/annotations?uuid=".$data["uuid"]);
						  echo "POSTed data";
					 }
				}
		  }
	 }
	 
	 public function processContainmentAction(){
		  $requestParams =  $this->_request->getParams();
		  $this->_helper->viewRenderer->setNoRender();
		  
		  if(!$this->getRequest()->isPost()){
				$this->error405("POST"); //not a post, throw an error
				exit;
		  }
		  else{
				$genObj = new OCitems_General;
				$impSubjectsObj = new Importer_Subjects;
				$data = $genObj->validateInput($requestParams, $impSubjectsObj->expectedProcessSchema );
				if(!$data){
					 $this->error400($genObj->errors); //throw an error explaining what was expected
					 exit;
				}
				else{
					 $result = $impSubjectsObj->process($data);
					 
					 if(isset($requestParams["json"])){
						  $output = array("result" => $result,
												"errors" => $impSubjectsObj->errors,
												"requestParams" => $requestParams);
						  header('Content-Type: application/json; charset=utf8');
						  echo $genObj->JSONoutputString($output);
					 }
					 else{
						  header("Location: ../editorial/annotations?uuid=".$data["uuid"]);
						  echo "POSTed data";
					 }
				}
		  }
	 }
	 
}

