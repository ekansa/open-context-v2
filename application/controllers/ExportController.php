<?php
/** Zend_Controller_Action */
require_once 'Zend/Controller/Action.php';

ini_set("memory_limit", "1024M");
// set maximum execution time to no limit
ini_set("max_execution_time", "0");

class ExportController extends Zend_Controller_Action
{
    function init(){
		  Zend_Loader::loadClass('Exporter_Export');
		  Zend_Loader::loadClass('infoURI');
		  Zend_Loader::loadClass('OCitems_General');
    }
	
	//returns when all the tables where last updated
	public function lastUpdatesAction(){
		$requestParams =  $this->_request->getParams();
		$this->_helper->viewRenderer->setNoRender();
		$this->init(); //load classes
		
		$genObj = new OCitems_General;
		$genObj->startClock();
		$exportObj = new Exporter_Export;
		$lastUpdates = $exportObj->getLastUpdates();
		$output = array();
		$output["result"] = $lastUpdates;
		header('Content-Type: application/json; charset=utf8');
		$output = $genObj->documentElapsedTime($output);
		echo $genObj->JSONoutputString($output);
	}
	
	//gives a count of records after the last updated time
	public function tableRecordsCountAction(){
		$requestParams =  $this->_request->getParams();
		$this->_helper->viewRenderer->setNoRender();
		$this->init(); //load classes
		
		$genObj = new OCitems_General;
		$genObj->startClock();
		$exportObj = new Exporter_Export;
		$tableCount = $exportObj->getLastUpdateCount($requestParams["table"], $requestParams["after"]);
		$output = array();
		$output["result"] = $tableCount;
		header('Content-Type: application/json; charset=utf8');
		$output = $genObj->documentElapsedTime($output);
		echo $genObj->JSONoutputString($output);
	}
	
	//gives records for a table, with the fields mapped appropriately for POSTGRES
	public function tableRecordsMappedAction(){
		$requestParams =  $this->_request->getParams();
		$this->_helper->viewRenderer->setNoRender();
		$this->init(); //load classes
		
		$genObj = new OCitems_General;
		$genObj->startClock();
		$exportObj = new Exporter_Export;
		$tableRecords = $exportObj->getRecords($requestParams["table"], $requestParams["after"], $requestParams["start"]);
		$output = array();
		$output["result"] = $tableRecords;
		header('Content-Type: application/json; charset=utf8');
		$output = $genObj->documentElapsedTime($output);
		echo $genObj->JSONoutputString($output);
	}
}

