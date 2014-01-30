<?php


//this class is used to manage media files
class OCitems_MediaFile {
    
	 public $db;
	 
    /*
     General data
    */
    public $uuid;
    public $projectUUID;
    public $sourceID;
	 public $mediaType;
	 public $mimeTypeURI;
	 public $thumbMimeURI;
	 public $thumbURI;
	 public $previewMimeURI;
	 public $previewURI;
	 public $fullURI;
	 public $fileSize; //in bytes
	 public $HRfileSize; //human readable file size
    public $updated;
	 
   
    //get data from database
    function getByUUID($uuid){
        
        $uuid = $this->security_check($uuid);
        $output = false; //not found
        
        $db = $this->startDB();
        
        $sql = 'SELECT *
                FROM oc_mediafiles
                WHERE uuid = "'.$uuid.'"
                LIMIT 1';
		
        $result = $db->fetchAll($sql, 2);
        if($result){
            $output = $result[0];
				$this->uuid = $uuid;
				$this->projectUUID = $result[0]["project_id"];
				$this->sourceID = $result[0]["source_id"];
				$this->mediaType = $result[0]["mediaType"];
				$this->mimeTypeURI = $result[0]["mimeTypeURI"];
				$this->thumbMimeURI = $result[0]["thumbMimeURI"];
				$this->thumbURI = $this->uriValidate($result[0]["thumbURI"]);
				$this->previewMimeURI = $result[0]["previewMimeURI"];
				$this->previewURI = $this->uriValidate($result[0]["previewURI"]);
				$this->fullURI = $this->uriValidate($result[0]["fullURI"]);
				$this->fileSize = $result[0]["filesize"];
				$this->HRfileSize = $this->formatBytes($this->fileSize);
				$this->updated = $result[0]["updated"];
		  }
        return $output;
    }
	 
	 //only allows HTTP uris
	 function uriValidate($possURI){
		  if(substr($possURI, 0, 7) == "http://" || substr($possURI, 0, 8) == "https://"){
				return $possURI;
		  }
		  else{
				return false;
		  }
	 }
    
	 
	 function makeHashID($content, $project_id){
		  
		  $content = trim($content);
		  return sha1($project_id."_".$content);
	 }
	 
	 
	 //adds an item to the database, returns its uuid if successful
	 function createRecord($data = false){
		 
		  $db = $this->startDB();
		  $success = false;
		  if(!is_array($data)){
				
				$data = array(	"uuid" => $this->uuid,
									 "project_id" => $this->projectUUID,
									 "source_id" => $this->sourceID,
									 "mediaType" => $this->mediaType,
									 "mimeTypeURI" => $this->mimeTypeURI,
									 "thumbMimeURI" => $this->thumbMimeURI,
									 "thumbURI" => $this->thumbURI,
									 "previewMimeURI" => $this->previewMimeURI,
									 "previewURI" => $this->previewURI,
									 "fullURI" => $this->fullURI,
									 "filesize" => $this->fileSize,
									 "updated" => $this->updated
								  );	
		  }
		  
		  
		  foreach($data as $key => $value){
				if(is_array($value)){
					 echo print_r($data);
					 die;
				}
		  }
	 
		  if(!isset($data["filesize"])){
				$data["filesize"] = $this->remote_filesize($data["fullURI"]);
				$this->fileSize = $data["filesize"];
		  }
	 
	 
		  try{
				$db->insert("oc_mediafiles", $data);
				$success = $data["uuid"];
		  } catch (Exception $e) {
				
				echo print_r($e);
				die;
				$success = false;
		  }
		  return $success;
	 }
	 
	 
	 //use the HTTP HEAD request to get the filesize, checking also if it exists
	 public function remote_filesize($uri,$user='',$pw=''){
		  // start output buffering
		  ob_start();
		  // initialize curl with given uri
		  $ch = curl_init($uri);
		  // make sure we get the header
		  curl_setopt($ch, CURLOPT_HEADER, 1);
		  // make it a http HEAD request
		  curl_setopt($ch, CURLOPT_NOBODY, 1);
		  // if auth is needed, do it here
		  if (!empty($user) && !empty($pw))
		  {
				$headers = array('Authorization: Basic ' .  base64_encode($user.':'.$pw)); 
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		  }
		  $okay = curl_exec($ch);
		  curl_close($ch);
		  // get the output buffer
		  $head = ob_get_contents();
		  // clean the output buffer and return to previous
		  // buffer settings
		  ob_end_clean();
		 
		  // gets you the numeric value from the Content-Length
		  // field in the http header
		  $regex = '/Content-Length:\s([0-9].+?)\s/';
		  $count = preg_match($regex, $head, $matches);
		 
		  // if there was a Content-Length field, its value
		  // will now be in $matches[1]
		  if (isset($matches[1]))
		  {
				$size = $matches[1];
		  }
		  else
		  {
				$size = false;
		  }
		 
		  return $size;
	 }
	 
	 
	 //format filesize information as human readable.
	 public function formatBytes($bytes, $precision = 2) {
		  $units = array('B', 'KB', 'MB', 'GB', 'TB');
			
		  $bytes  = $bytes  +0;
		  $bytes = max($bytes, 0);
		  $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		  $pow = min($pow, count($units) - 1);
		
		  $bytes /= pow(1024, $pow);
		
		  return round($bytes, $precision) . ' ' . $units[$pow];
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
