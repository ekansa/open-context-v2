<?php
/** Zend_Controller_Action */
require_once 'Zend/Controller/Action.php';

ini_set("memory_limit", "1024M");
// set maximum execution time to no limit
ini_set("max_execution_time", "0");

class ImporterPrepareController extends Zend_Controller_Action
{
    function init(){
		  Zend_Loader::loadClass('Importer_FieldLinks');
		  Zend_Loader::loadClass('Importer_Refine');
		  Zend_Loader::loadClass('OCitems_General');
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
	 
	 public function loadRefineProjectSchemaAction(){
		  $requestParams =  $this->_request->getParams();
		  $this->_helper->viewRenderer->setNoRender();
		  
		  if(!$this->getRequest()->isPost()){
				$this->error405("POST"); //not a post, throw an error
				exit;
		  }
		  else{
				$genObj = new OCitems_General;
				$refineObj = new Importer_Refine;
				$data = $genObj->validateInput($requestParams, $refineObj->expectedLoadFieldSchema);
				if(!$data){
					 $this->error400($genObj->errors); //throw an error explaining what was expected
					 exit;
				}
				else{
					 $result = $refineObj->loadUpdateModel($data);
					 if(isset($requestParams["json"])){
						  $output = array("result" => $result,
												"errors" => false,
												"requestParams" => $requestParams);
						  header('Content-Type: application/json; charset=utf8');
						  echo $genObj->JSONoutputString($output);
					 }
					 else{
						  header("Location: ../importer-prepare/describe-fields?projectUUID=".$refineObj->projectUUID."&sourceID=".$refineObj->sourceID);
						  echo "POSTed data";
					 }
				}
		  
		  }
	 }
	 
	 
	 
	 
	 
	 public function formAction(){
		  
	 }
	 
}

