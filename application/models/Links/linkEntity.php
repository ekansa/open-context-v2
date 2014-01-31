<?php


/*this class stores information annotating Open Context data items with
 *entities from outside vocabularies
*/
class Links_linkEntity {
    
	 public $db;
	 
	 public $uri;
	 public $label;
	 public $altLabel;
	 public $vocabURI;
	 public $vocabLabel;
	 public $vocabAltLabel;
	 
	 function getByURI($uri){
		  $output = false;
		  $uri = $this->security_check($uri);
		  
		  $db = $this->startDB();
        
        $sql = 'SELECT *
                FROM link_entities
                WHERE uri = "'.$uri.'"
                LIMIT 1';
		
        $result = $db->fetchAll($sql, 2);
        if($result){
            $output = $result[0];
				if(!$this->uri){
					 $this->uri = $uri;
					 $this->label = $result[0]["label"];
					 $this->altLabel = $result[0]["altLabel"];
					 $this->vocabURI = $result[0]["vocabURI"];
					 $output["vocabLabel"] = false;
					 $output["vocabAltLabel"] = false;
					 if($uri != $this->vocabURI){
						  $vocabRes = $this->getByURI($this->vocabURI); //get labels for the vocabulary
						  if(is_array($vocabRes)){
								$this->vocabLabel = $vocabRes["label"];
								$this->vocabAltLabel = $vocabRes["altLabel"];
								$output["vocabLabel"] = $this->vocabLabel;
								$output["vocabAltLabel"] = $this->vocabAltLabel;
						  } 
					 }
				}
		  }
        return $output;
	 }
	 
	 
	 
    function security_check($input){
        $badArray = array("DROP", "SELECT", "#", "--", "DELETE", "INSERT", "UPDATE", "ALTER", "=");
        foreach($badArray as $bad_word){
            if(stristr($input, $bad_word) != false){
                $input = str_ireplace($bad_word, "XXXXXX", $input);
            }
        }
        return $input;
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
