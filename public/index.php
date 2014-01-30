<?php
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 1);
date_default_timezone_set('America/Los_Angeles');
// directory setup and class loading
set_include_path('.' . PATH_SEPARATOR . '../library/'
     . PATH_SEPARATOR . '../application/models'
     . PATH_SEPARATOR . get_include_path());

mb_internal_encoding( 'UTF-8' );
include 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('App_');
$autoloader->registerNamespace('OpenContext_');

//$registry = new Zend_Registry(array('index' => $value));
//Zend_Registry::setInstance($registry);


// load configuration
$config = new Zend_Config_Ini('../application/config.ini', 'general');
$registry = Zend_Registry::getInstance();
$registry->set('config', $config);

// setup database
$db = Zend_Db::factory($config->db->adapter,
$config->db->config->toArray());
Zend_Db_Table::setDefaultAdapter($db); 
Zend_Registry::set('db', $db);


// setup controller
$frontController = Zend_Controller_Front::getInstance();
$frontController->throwExceptions(true);
$frontController->registerPlugin(new Zend_Controller_Plugin_ErrorHandler(array(
    'module'     => 'error',
    'controller' => 'error',
    'action'     => 'error'
)));

// Custom routes
$router = $frontController->getRouter();


$subjectsViewRoute = new Zend_Controller_Router_Route('subjects/:uuid', array('controller' => 'subjects', 'action' => 'view'));
// Add it to the router
$router->addRoute('subjectView', $subjectsViewRoute); // html representation

//A longer version
$subjectsJSONlongRoute = new Zend_Controller_Router_Route_Regex('subjects/(.*)\.json',
                                                        array('controller' => 'subjects', 'action' => 'json-long'),
                                                        array(1 => 'uuid'), 'subjects/%s/');
// Add it to the router
$router->addRoute('subjectsJSONlong', $subjectsJSONlongRoute ); // long JSON representation

//A short, more normalized version from the cache
$subjectsJSONterseRoute = new Zend_Controller_Router_Route_Regex('subjects/(.*)\/short.json',
                                                        array('controller' => 'subjects', 'action' => 'json-short'),
                                                        array(1 => 'uuid'), 'subjects/%s/');
// Add it to the router
$router->addRoute('subjectsJSONterse', $subjectsJSONterseRoute ); // terse JSON representation

//A short, more normalized version, generated from the database
$subjectsJSONgenTerseRoute = new Zend_Controller_Router_Route_Regex('subjects/(.*)\/gen-short.json',
                                                        array('controller' => 'subjects', 'action' => 'json-gen-short'),
                                                        array(1 => 'uuid'), 'subjects/%s/');
// Add it to the router
$router->addRoute('subjectsJSONgenTerse', $subjectsJSONgenTerseRoute ); // terse JSON representation




$mediaViewRoute = new Zend_Controller_Router_Route('media/:uuid', array('controller' => 'media', 'action' => 'view'));
// Add it to the router
$router->addRoute('mediaView', $mediaViewRoute); // html representation

//A longer version
$mediaJSONlongRoute = new Zend_Controller_Router_Route_Regex('media/(.*)\.json',
                                                        array('controller' => 'media', 'action' => 'json-long'),
                                                        array(1 => 'uuid'), 'media/%s/');
// Add it to the router
$router->addRoute('mediaJSONlong', $mediaJSONlongRoute ); // long JSON representation

//A short, more normalized version from the cache
$mediaJSONterseRoute = new Zend_Controller_Router_Route_Regex('media/(.*)\/short.json',
                                                        array('controller' => 'media', 'action' => 'json-short'),
                                                        array(1 => 'uuid'), 'media/%s/');
// Add it to the router
$router->addRoute('mediaJSONterse', $mediaJSONterseRoute ); // terse JSON representation

//A short, more normalized version, generated from the database
$mediaJSONgenTerseRoute = new Zend_Controller_Router_Route_Regex('media/(.*)\/gen-short.json',
                                                        array('controller' => 'media', 'action' => 'json-gen-short'),
                                                        array(1 => 'uuid'), 'media/%s/');
// Add it to the router
$router->addRoute('mediaJSONgenTerse', $mediaJSONgenTerseRoute ); // terse JSON representation





$documentsViewRoute = new Zend_Controller_Router_Route('documents/:uuid', array('controller' => 'documents', 'action' => 'view'));
// Add it to the router
$router->addRoute('documentsView', $documentsViewRoute); // html representation

//A longer version
$documentsJSONlongRoute = new Zend_Controller_Router_Route_Regex('documents/(.*)\.json',
                                                        array('controller' => 'documents', 'action' => 'json-long'),
                                                        array(1 => 'uuid'), 'documents/%s/');
// Add it to the router
$router->addRoute('documentsJSONlong', $documentsJSONlongRoute ); // long JSON representation

//A short, more normalized version from the cache
$documentsJSONterseRoute = new Zend_Controller_Router_Route_Regex('documents/(.*)\/short.json',
                                                        array('controller' => 'documents', 'action' => 'json-short'),
                                                        array(1 => 'uuid'), 'documents/%s/');
// Add it to the router
$router->addRoute('documentsJSONterse', $documentsJSONterseRoute ); // terse JSON representation

//A short, more normalized version, generated from the database
$documentsJSONgenTerseRoute = new Zend_Controller_Router_Route_Regex('documents/(.*)\/gen-short.json',
                                                        array('controller' => 'documents', 'action' => 'json-gen-short'),
                                                        array(1 => 'uuid'), 'documents/%s/');
// Add it to the router
$router->addRoute('documentsJSONgenTerse', $documentsJSONgenTerseRoute ); // terse JSON representation





$personsViewRoute = new Zend_Controller_Router_Route('persons/:uuid', array('controller' => 'persons', 'action' => 'view'));
// Add it to the router
$router->addRoute('personsView', $personsViewRoute); // html representation

//A longer version
$personsJSONlongRoute = new Zend_Controller_Router_Route_Regex('persons/(.*)\.json',
                                                        array('controller' => 'persons', 'action' => 'json-long'),
                                                        array(1 => 'uuid'), 'persons/%s/');
// Add it to the router
$router->addRoute('personsJSONlong', $personsJSONlongRoute ); // long JSON representation

//A short, more normalized version from the cache
$personsJSONterseRoute = new Zend_Controller_Router_Route_Regex('persons/(.*)\/short.json',
                                                        array('controller' => 'persons', 'action' => 'json-short'),
                                                        array(1 => 'uuid'), 'persons/%s/');
// Add it to the router
$router->addRoute('personsJSONterse', $personsJSONterseRoute ); // terse JSON representation

//A short, more normalized version, generated from the database
$personsJSONgenTerseRoute = new Zend_Controller_Router_Route_Regex('persons/(.*)\/gen-short.json',
                                                        array('controller' => 'persons', 'action' => 'json-gen-short'),
                                                        array(1 => 'uuid'), 'persons/%s/');
// Add it to the router
$router->addRoute('personsJSONgenTerse', $personsJSONgenTerseRoute ); // terse JSON representation






$frontController->setControllerDirectory('../application/controllers');
try {
    $frontController->dispatch();

}catch (Exception $e){
    echo $e;
}