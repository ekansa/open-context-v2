<?php
/* This class maps old legacy class names to URIs
 * 
 */

class XMLjsonLD_LegacyClass {
    
	 public $db; //database connection object
	 
	 
	 
	 public $classArray = array("Coin" => "http://opencontext.org/vocabularies/oc-general/cat-0009",
                    "Pottery" => "http://opencontext.org/vocabularies/oc-general/cat-0010",
                    "Glass" => "http://opencontext.org/vocabularies/oc-general/cat-0011",
                    "Groundstone" => "http://opencontext.org/vocabularies/oc-general/cat-0012",
                    "Small Find" => "http://opencontext.org/vocabularies/oc-general/cat-0008",
                    "Arch. Element" => "http://opencontext.org/vocabularies/oc-general/cat-0013",
                    "Objects" => "http://opencontext.org/vocabularies/oc-general/cat-0008",
                    
                    "Animal Bone" => "http://opencontext.org/vocabularies/oc-general/cat-0015",
                    "Shell" => "http://opencontext.org/vocabularies/oc-general/cat-0016",
                    "Non Diag. Bone" => "http://opencontext.org/vocabularies/oc-general/cat-0017",
                    "Human Bone" => "http://opencontext.org/vocabularies/oc-general/cat-0018",
                    "Plant Remains" => "http://opencontext.org/vocabularies/oc-general/cat-0019",
						  "Patients" => "http://opencontext.org/vocabularies/oc-general/cat-0037", //human subject
                    
                    "Feature" => "http://opencontext.org/vocabularies/oc-general/cat-0025",
                    "Lot" => "http://opencontext.org/vocabularies/oc-general/cat-0028",
                    "Locus" => "http://opencontext.org/vocabularies/oc-general/cat-0027",
                    "Context" => "http://opencontext.org/vocabularies/oc-general/cat-0024",
                    "Sequence" => "http://opencontext.org/vocabularies/oc-general/cat-0036",
                    "Basket" => "http://opencontext.org/vocabularies/oc-general/cat-0029",
                    "Excav. Unit" => "http://opencontext.org/vocabularies/oc-general/cat-0026",
                    "Stratum" => "http://opencontext.org/vocabularies/oc-general/cat-0038",
                    
						  "Survey Unit" => "http://opencontext.org/vocabularies/oc-general/cat-0021",
                    "Trench" => "http://opencontext.org/vocabularies/oc-general/cat-0031",
                    "Square" => "http://opencontext.org/vocabularies/oc-general/cat-0034",
                    "Area" => "http://opencontext.org/vocabularies/oc-general/cat-0030",
                    "Operation" => "http://opencontext.org/vocabularies/oc-general/cat-0032",
                    "Field Project" => "http://opencontext.org/vocabularies/oc-general/cat-0033",
                    "Mound" => "http://opencontext.org/vocabularies/oc-general/cat-0041",
						  
						  "Hosptial" => "http://opencontext.org/vocabularies/oc-general/cat-0040",
						  
						  "Sample" => "http://opencontext.org/vocabularies/oc-general/cat-0043",
						  "Reference Collection" => "http://opencontext.org/vocabularies/oc-general/cat-0045",
						  
						  "Region" => "http://opencontext.org/vocabularies/oc-general/cat-0046",
                    "Site" => "http://opencontext.org/vocabularies/oc-general/cat-0022"
                    );
	 
	 
	 
	 
	 //look up the class URI for a given class name
	 function getClassURI($class){
		  
		  $classArray = $this->classArray;
		  if(array_key_exists($class, $classArray)){
				return $classArray[$class];
		  }
		  else{
				$classLC = strtolower($class);
				$found = false;
				foreach($classArray as $classKey => $uri){
					 if(strtolower($classKey) == $classLC ){
						  $found =  $uri;
					 }
				}
				return $found;
		  }
		  
	 }
	 
	 
	 
	 function startDB(){
		  if(!$this->db){
				$db = Zend_Registry::get('db');
				$this->setUTFconnection($db);
				$this->db = $db;
		  }
		  else{
				$db = $this->db;
		  }
		  
		  return $db;
	 }
	 
	 function setUTFconnection($db){
		  $sql = "SET collation_connection = utf8_unicode_ci;";
		  $db->query($sql, 2);
		  $sql = "SET NAMES utf8;";
		  $db->query($sql, 2);
    }
	 

    
}  
