<?

class module_SimpleUpload
{		
	
	function __construct() {
		
	}
	
	function uploadTo($uploadDir, $inputName, $filename = '', $filenamePrefix = '', $returnFinalLocation = false, $validExt = array()){  
		$status = $filepath = 0;
		$upload =  new HTTP_Upload("cz");	
		
		$file = $upload->getFiles($inputName);	
		
		//$file->setValidExtensions($validExt, 'allow'); 
		
		
		if ($file->isValid()) {
			$file->dirToUpload = $uploadDir;
			$file->setName('safe');
			$file->upload['ext'] = strtolower($file->upload['ext']);
			
			$pPath = $uploadPath;		
			if($uploadPath == '/'){
				$pPath = '';
			}	
					
			if($filename){
				$file->upload['name'] = $filename;
			}
			
			if($filenamePrefix){
				$file->upload['name'] = $filenamePrefix . $file->upload['name'];
			}
			
			$moved = $file->moveTo($uploadDir);				

			if (!PEAR::isError($moved)) {			    			
				$status = 1;
				$err = 'Soubor úspěšně nahrán.';	
			} else {
				$err = $moved->getMessage();
			}
			
						
			// web file url
			$filepath = $uploadDir . '/' . $file->upload['name'];	
			$conf = Zend_Registry::getInstance()->config;	
			if(strpos($filepath, $conf->htdocsRoot) === 0){
				$filepath = substr($filepath, strlen($conf->htdocsRoot));	
			}
			
		} elseif ($file->isMissing()) {	
			$err = 'Prosím vyberte soubor který chcete nahrát.';			
		} elseif ($file->isError()) {
			$err = $file->errorMsg();				
		}
		
		if($returnFinalLocation && !$err){
			$filepath = $uploadDir . '/' .  $moved;
		}
		$filepath = str_replace('//', '/', $filepath);
		unset($upload);
		return array($status, $err, $filepath);
	}
				
    
}

?>