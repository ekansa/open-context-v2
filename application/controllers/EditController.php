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
				$output = array("result" => $result,
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
					 $result = $linkAnnotObj->createRecord($data);
					 if(isset($requestParams["returnAnnotations"])){
						  $result = $linkAnnotObj->getAnnotationsByUUID($requestParams["uuid"]); 
					 }
					 
					 if(isset($requestParams["json"])){
						  $output = array("result" => $result,
												"errors" => false,
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
	 
	 //deletes an annotation applied to an opencontext entity
	 public function deleteAnnotationAction(){
		  $requestParams =  $this->_request->getParams();
		  $this->_helper->viewRenderer->setNoRender();
		  
		  if(!$this->getRequest()->isPost()){
				$this->error405("POST"); //not a post, throw an error
				exit;
		  }
		  else{
				$genObj = new OCitems_General;
				$linkAnnotObj = new Links_linkAnnotation;
				$data = $genObj->validateInput($requestParams, $linkAnnotObj->expectedDeleteSchema);
				if(!$data){
					 $this->error400($genObj->errors); //throw an error explaining what was expected
					 exit;
				}
				else{
					 $result = $linkAnnotObj->deleteRecord($data);
					 if(isset($requestParams["returnAnnotations"])){
						  $result = $linkAnnotObj->getAnnotationsByUUID($requestParams["uuid"]); 
					 }
					 if(isset($requestParams["json"])){
						  $output = array("result" => $result,
												"errors" => false,
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
	 
	 
	 //gets some information about a URI identified entity
	 public function getEntityAction(){
		  $requestParams =  $this->_request->getParams();
		  $this->_helper->viewRenderer->setNoRender();
		  
		  $genObj = new OCitems_General;
		  $genObj->startClock();
		  $uriObj = new infoURI;
		  if(!isset($requestParams["uri"])){
				$errors = array("Need 'uri' parameter");
				$this->error400($errors);
		  }
		  else{
				$result = $pRes = $uriObj->lookupURI($requestParams["uri"]);
				$output = array("result" => $result,
										  "errors" => false,
										  "requestParams" => $requestParams);
				header('Content-Type: application/json; charset=utf8');
				$output = $genObj->documentElapsedTime($output);
				echo $genObj->JSONoutputString($output);
		  }
		  
	 }
	 
	 //adds an entity
	 public function addEntityAction(){
		  $requestParams =  $this->_request->getParams();
		  $this->_helper->viewRenderer->setNoRender();
		  
		  if(!$this->getRequest()->isPost()){
				$this->error405("POST"); //not a post, throw an error
				exit;
		  }
		  else{
				$genObj = new OCitems_General;
				$linkEntityObj = new Links_linkEntity;
				$data = $genObj->validateInput($requestParams, $linkEntityObj->expectedSchema);
				if(!$data){
					 $this->error400($genObj->errors); //throw an error explaining what was expected
					 exit;
				}
				else{
					 $ok = $linkEntityObj->createRecord($data);
					 $output = array("result" => $ok,
												"errors" => false,
												"data" => $data);
					 header('Content-Type: application/json; charset=utf8');
					 echo $genObj->JSONoutputString($output);
				}
		  }
	 }
	 
	 
	 //searches for URI identified entities, constrained by text in labels, optionally vocabularies
	 public function searchEntitiesAction(){
		  $requestParams =  $this->_request->getParams();
		  $this->_helper->viewRenderer->setNoRender();
		  
		  $genObj = new OCitems_General;
		  $genObj->startClock();
		  $linkEntityObj = new Links_linkEntity;
		  
		  if(!isset($requestParams["q"])){
				$errors = array("Need 'q' parameter");
				$this->error400($errors);
		  }
		  else{
				$label = $requestParams["q"];
				$result = $linkEntityObj->getByLabel($label, $requestParams);
				$output = array("result" => $result,
										  "errors" => false,
										  "requestParams" => $requestParams);
				header('Content-Type: application/json; charset=utf8');
				$output = $genObj->documentElapsedTime($output);
				echo $genObj->JSONoutputString($output);
		  }
		  
	 }
	 
	 //gets types used with a given predicate
	 public function predicateTypesAction(){
		  $requestParams =  $this->_request->getParams();
		  $this->_helper->viewRenderer->setNoRender();
		  
		  $genObj = new OCitems_General;
		  $genObj->startClock();
		  $ocTypeObj = new OCitems_Type;
		  
		  if(!isset($requestParams["predicateUUID"])){
				$errors = array("Need 'predicateUUID' parameter");
				$this->error400($errors);
		  }
		  else{
				$predicateUUID = $requestParams["predicateUUID"];
				$result = $ocTypeObj->getByPredicateUUID($predicateUUID, $requestParams);
				$output = array("result" => $result,
										  "errors" => false,
										  "requestParams" => $requestParams);
				header('Content-Type: application/json; charset=utf8');
				$output = $genObj->documentElapsedTime($output);
				echo $genObj->JSONoutputString($output);
		  }
		  
	 }
	 
	 
}

