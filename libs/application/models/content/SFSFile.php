<?
/**
 * Třída reprezentuje základní typ obsahu - HTML soubor.
 * @see model_Content
 * @package content
 */
 
class content_SFSFile extends Content {
    	
	public static $imageExtensions = array(
		'gif' => 'compuserve graphics interchange',
		'bmp' => 'bitmap',
		'dib' => 'bitmap',
		'jpg' => 'joint photographic experts group jfif format',
		'jpeg' => 'joint photographic experts group jfif format',
		'png' => 'portable network graphic',
		'psd' => 'adobe photoshop',
		'psp' => 'paint shop pro image',
		'tga' => 'truevision targa',
		'tif' => 'tagged interchange file format',
		'tiff' => 'tagged interchange file format',
		'pcx' => 'zsoft paintbrush',
		'pcc' => 'zsoft paintbrush',
		'rsr' => 'poser rsr image',
		'emf' => 'microsoft windows meta file ',
		'cel' => 'autodesk cel',
		'eps' => 'encapsulated postscript',
		'ico' => 'microsoft windows icon',
		'icl' => 'icon library file',
		'icb' => 'truevision images',
		'pic' => 'autodesk pic',
		'pcd' => 'kodak photo-cd',
		'crw' => 'digital camera raw format',
		'cut' => 'dr halo',
		'exr' => 'openexr high dynamic range',
		'flx' => 'lightwave flexible precision image',
		'hdr' => 'radiance high dynamic range',
		'pbm' => 'portable bitmap',
		'pgm' => 'portable greymap',
		'ppm' => 'portable pixelmap',
		'rle' => 'microsoft windows rle bitmap',
		'scr' => 'word 5x screen capture',
		'vda' => 'truevision images',
		'vst' => 'truevision images',
		'win' => 'truevision images'
	);
	
	public static $fileExtensions = array(
		'ppt' => 'Microsoft Office PowerPoint',
		'pptx' => 'Microsoft Office PowerPoint (2007)',
		'xls' => 'Microsoft Office Excel',
		'xlsx' => 'Microsoft Office Excel (2007)',
		'doc' => 'Microsoft Word',
		'docx' => 'Microsoft Word (2007)',
		'pdf' => 'Portable document format',
		'zip' => 'komprimovaný archiv ZIP',
		'rar' => 'komprimovaný archiv RAR',
		'jpg' => 'obrázek JPG',
		'gif' => 'obrázek GIF',
		'png' => 'obrázek',
		'bmp' => 'obrázek',
		'tif' => 'obrázek'
	);

    public function __construct($config = array())
    {
    	$this->searchableCols = array();	
    	$this->_name =  get_class($this);  
    	$this->vars = array('id', 'dateCreate', 'owner', 'dateModif', 'modifiedBy', 'state', 'properties' );
    	$this->userName = 'Soubor';
        $this->properties[] = new ContentProperty('fullpath','File','');   
        $this->properties[] = new ContentProperty('meta','Hidden','');     
             
    }
       
    function renderAdmin($view){
    	parent::renderAdmin($view);
    }
    
    function showAdmin($view, $callRender = false){
    	parent::showAdminInit($view);
    	
    	parent::renderAdmin($view); 
    }
    
    
    public static function getFileExtensionInfo($extenstion = null, $fullpath = null, $text = '') {
		if($fullpath){
			$extenstion = self::getFileExtension($fullpath);
		}
		return '<span class="info" title="' . (self::$fileExtensions[$extenstion]?self::$fileExtensions[$extenstion]:'soubor ' . $extenstion) . '">' . $text . ' '. $extenstion . '</span>';
	}
	
    public static function getFileExtension($fileName = '') {
    	
    	if(strpos($fileName, '.')){
			return strtolower(array_pop(explode('.', $fileName))); 
    	} else {
    		return '';
    	}
	}
	
	public static function getFileWithouExtension($fileName = '') {
		if(strpos($fileName, '.')){
			$nFilename = explode('.', $fileName);
			array_pop($nFilename);
			$path = implode('.', $nFilename);
		} else {
			$path = $fileName;
		}
		
		return $path;
	}
	
	public static function getFileName($fullpath) {    	
		$parts = explode('/', $fullpath);
		return  array_pop($parts);
	}
	
	//prevod na cestu id/name
	public static  function getSFSPath($nodeId, $fullpath) {    			
		return $nodeId . '/' . self::getFileName($fullpath);
	}
	
	// zpetny prevod na plnou cestu
	public static  function getSFSFullPath($path) {    		
		
		list($nodeId, $name, $size ) = self::parseSFSPath($path);	
		$size = str_replace('-jpg', '', $size); 
		 
		//  e($path); 
		$fnode = Zend_Registry::getInstance()->tree->getNodeById($nodeId);	
		 
		
		
		if(!$fnode){
			return null;
		} else {			
			$fullPath = $fnode->getTheRightContent()->getPropertyValue('fullpath'); 
			if($size){
				$fullPath = content_SFSFile::getGeneratedImagePathNew($fullPath) . '/' . $size . '/'. $name;					
			} 
			// e($size);  e($fullPath);e($path); die(); 	 
			return $fullPath;
		}
	}
	
	public static  function getSFSAbsolutePath($path) {    
		return Zend_Registry::getInstance()->config->fsRoot . self::getSFSFullPath($path);
	}	
	
	public static  function parseSFSPath($path) {    
		$conf = Zend_Registry::getInstance()->config;	
		if(strpos($path, $conf->sfFolder) === 0){
			$path = substr($path, strlen($conf->sfFolder) + 1);	
		}
		$parts = explode('/', $path);
		//pr($parts);
		if(count($parts) == 3){ // spec velikost fotky
			return array($parts[0], $parts[2], $parts[1]);
		} else {
			return array($parts[0], $parts[1], 0);
		}
	}
	
	public static  function removeSfFolderFromPath($path) {    
		return substr($path, strlen(Zend_Registry::getInstance()->config->sfFolder) + 1);
	}
		
	public static function getGeneratedImagePath($imagePath){
		return self::getFileWithouExtension($imagePath) 
			. '-' 
			. self::getFileExtension($imagePath);
	}
	 
	public static function getGeneratedImagePathNew($imagePath){
			$nFilename = explode('/', $imagePath);
			array_pop($nFilename); 
			$new = implode('/', $nFilename); 
			$new = str_replace('//', '/', $new); 
			return $new;
	}
	 
	
	// pro sablony - ikony atd
	public static  function getFileFullPath($path, $absolute = false ) {    	
		$conf = Zend_Registry::getInstance()->config;	
		$name = substr($path, strlen($conf->sfFolder) + 1);
		
		if($absolute){
			return  $conf->fsRoot . self::getSFSFullPath($name);
		} else {
			return $conf->sfFolder . self::getSFSFullPath($name);;
		}  
	}
    
	public static  function isSFSFile($path) {		
		if(strpos($path, Zend_Registry::getInstance()->config->sfFolder) === false){
			return false;
		} else {
			return true;
		}
	}	

	public static  function isResizableImage($path) {		
		$ext = self::getFileExtension($path);
		if($ext == 'jpg' || $ext == 'png'){
			return true;
		} else {
			return false;
		}
	} 
	
	public static  function getFileNodeId($path) {
		$path = self::removeSfFolderFromPath($path);
		list($nodeId, $name) = self::parseSFSPath($path);
		return $nodeId;
	}
	
	public static  function getSFSFile($path) {		
		return  array_pop($parts);
	}
		
	public function getThisFileExtension() {				
		return self::getFileExtension($this->getPropertyValue('fullpath'));
	}
	
	public function getFileFrontEndAvailableNodes(){
		$fileFrontEndAvailableNodes = array();
		foreach (Zend_Registry::getInstance()->config->contentTypes->toArray() as $name => $username){
			$contentName = 'content_' . $name;
			$content = new $contentName;
			if($content->containsFiles){
				$nodes = Zend_Registry::getInstance()->tree->getNodesWithContentType($contentName, true);
				if(count($nodes)){
					$fileFrontEndAvailableNodes = array_merge($fileFrontEndAvailableNodes, $nodes);
				}
			}
		}
		return $fileFrontEndAvailableNodes;
	}
	
	public static function getFileFromProperty($value){
		$value = '/data/sharedfiles/73846/bgr.jpg;bg';
		if(strpos($value, ';')){
			list($path, $showValue) = explode(';', $value);
		} else {
			$showValue = $path = $value;				
		}
		return array(
			'name' => $showValue,
			'path' => $path
		);
	}
		
	/*** PRESOUVANI A KOPORIROVANI */
	/**
	 * Zda můžeme přšsouvat - neexistuje uz v cílovém adresáři
	 *
	 */
	function isMovable($target){		
		return !file_exists(Zend_Registry::getInstance()->config->fsRoot . $target);
	}
	
	/**
	 * Přesune soubor a jeho náhledy do jiného adresáře
	 *
	 */
	function move($target){		
		if($this->isMovable($target)){
			$sfRoot = Zend_Registry::getInstance()->config->fsRoot;
			
			$source = $this->getPropertyValue('fullpath');
			$imagesDir = $sfRoot . self::getGeneratedImagePath($source);
			$imagesDirNew = $sfRoot . self::getGeneratedImagePath($target);
			if(!is_dir($imagesDirNew) && is_dir($imagesDir)){
				if(mkdir($imagesDirNew, 0777)){
					self::full_copy($imagesDir, $imagesDirNew);
				}
				if(is_dir($imagesDir)){
					Utils::deleteFolder($imagesDir);
				}
			}			
			self::full_copy($sfRoot . $source, $sfRoot . $target);
			unlink($sfRoot . $source);	
			$this->getPropertyByName('fullpath')->value = $target;
			$this->update();
		}
	}
	
	
	/**
	 * Zkopiruje cely adresar / soubor
	 *
	 * @param unknown_type $source
	 * @param unknown_type $target
	 */
	static function full_copy( $source, $target )
    {
        if ( is_dir( $source ) )
        {
            @mkdir( $target );
           
            $d = dir( $source );
           
            while ( FALSE !== ( $entry = $d->read() ) )
            {
                if ( $entry == '.' || $entry == '..' )
                {
                    continue;
                }
               
                $Entry = $source . '/' . $entry;           
                if ( is_dir( $Entry ) )
                {
                    full_copy( $Entry, $target . '/' . $entry );
                    continue;
                }
                copy( $Entry, $target . '/' . $entry );
            }
           
            $d->close();
        }else
        {
            copy( $source, $target );
        }
    }
    
	/**
	 * Vytvoří náhled obrázku pro admin
	 *
	 */
	function createFiles(){
    	$settings = Zend_Registry::getInstance()->settings;
    	$thumbWidth = $settings->getSettingValue('img_thumb_width');
    	$maxImageWidth = 1600;
    	    	    	
    	$filePath = $this->getPropertyValue('fullpath');
		$ext = content_SFSFile::getFileExtension($filePath);
				
		if($ext == 'jpg' || $ext == 'png'){
			$img = new Image($filePath);
			$img->generateThumbnail('sysThumb', $thumbWidth);
			$img->generateThumbnail(0, $maxImageWidth);
		}			
    } 
    
    function createFileThumbs($filePath, $imageProperties){
    	
    	$ext = content_SFSFile::getFileExtension($filePath);
    	
		if($ext != 'jpg' && $ext != 'png'){
			return; 
		}
		$img = new Image(content_SFSFile::getSFSFullPath($filePath));
		$fnid = content_SFSFile::getFileNodeId($filePath);
		$img->nid = $fnid; 
		
		$settings = Zend_Registry::getInstance()->settings;
		$thumbWidth = $settings->getSettingValue('img_thumb_width');
		$img->generateThumbnail('sysThumb', $thumbWidth);
		
		if(count($imageProperties[0])){   
			foreach ($imageProperties as $properties){      
				
				//if(!file_exists($img->fileDir . '/' .$properties['name'] . ".jpg")){    
					if($properties['crop_width'] > 0 && $properties['crop_height'] > 0){  
						$imageData = getimagesize($img->file);
						if($imageData[0] >= $imageData[1]){ // nasirku, ctverec	= resize na vysku a crop
							$width = 3000;
							$height = $properties['crop_height'];
							$autosize = false; 
							$img->generateThumbnail($properties['name'].'tmp', $width, $height, $autosize, $properties['watermark'], $properties['watermark2']);
						} else { // navysku = resize na sirku a crop
							$width = $properties['crop_width'];
							$height = 3000;
							$autosize = false;
							$img->generateThumbnail($properties['name'].'tmp', $width, $height, $autosize, $properties['watermark'], $properties['watermark2']);
						}
						
						$thumbFilePath = $img->fileDir . '/' .$properties['name'].'tmp' . "/" . $img->filename  ;
						$thumbFilePath2 = $img->fileDir . '/' .$properties['name'] .  "/" . $img->filename ;
						$imageData2 = getimagesize($thumbFilePath); 
 
						if($imageData2[0] <= $properties['crop_width'] &&  $imageData2[1] <= $properties['crop_height']){
							$img->generateThumbnail($properties['name'], $width, $height, $autosize, $properties['watermark'], $properties['watermark2']); 
						} else { 
							$thumb2 = new Thumbnail2($thumbFilePath);
							$thumb2->cropFromCenter($properties['crop_width'], $properties['crop_height']);
							$thumb2->save($thumbFilePath2, 90);
						}
						// unlink($thumbFilePath); 
					} else {      
						$img->generateThumbnail($properties['name'], $properties['width'], $properties['height'], $properties['autosize'], $properties['watermark'], $properties['watermark2'], $properties['crop_width'], $properties['crop_height']);
					}
				//} else {
				//	e($img->fileDir . '/' .$properties['name'] . ".jpg");
					//    die(); 
				//}  
			}
		} else {
			// e('generuju'); 
			$img->generateThumbnail($imageProperties['name'], $imageProperties['width'], $imageProperties['height'], $imageProperties['autosize'], $properties['watermark'], $properties['watermark2']);
		}
		 
 		  
    }
    
    function createGrayScaleFile($filePath, $fileName, $imageProperties = array()){    	
		$img = new Image(content_SFSFile::getSFSFullPath($filePath));
		$img->generateGrayscale($fileName);		
   }
    
    function updateNodesWithThisFile(){
    	$ref = new References($this);
    	$tree = Zend_Registry::getInstance()->tree;
    	    	
    	foreach ($ref->getNodeReferences() as $nodeId => $contentId){
    		$n = $tree->getNodeById($nodeId);
    		if($n){
	    		foreach ($n->getAllContents() as $content){
	    			if(method_exists($content, 'createFiles')){
	    				$content->createFiles();
	    			}
	    		}
    		}
    	}
    }
	
	function onSave() {
		$this->createFiles();
		$this->updateNodesWithThisFile();
		parent::onSave();		
	}
	
	function onUpdate(){
    	$this->createFiles();
    	$this->updateNodesWithThisFile();
    	parent::onUpdate();
    }
    
    function onDelete() {
		$path = $this->getPropertyValue('fullpath');
		$fullpath = Zend_Registry::getInstance()->config->fsRoot . $path;
		
		// test na ruzne velikosti fotek - pomocny adresar 
		$imagesDir = Zend_Registry::getInstance()->config->fsRoot . self::getGeneratedImagePath($path);
			
		if(is_dir($imagesDir)){
			Utils::deleteFolder($imagesDir);
		}
		
		if(is_file($fullpath)){
			unlink($fullpath);
		}
		parent::onDelete();
		
	}
	
	/* ADD */  
	function addFile($view, $uploadName, $addTo, $name = '', $addUnique = true, $returnFullPath = true){
		
		$uploadFile = $_FILES[$uploadName];
		 
		$upload = new module_SimpleUpload();		    				
		$config = Zend_Registry::getInstance()->config;
		$tree = Zend_Registry::getInstance()->tree;
		
		$addToNode = $tree->getNodeById($addTo);
		$images = array(
			'jpg', 'gif', 'png'
		);
		
		$transform = array(
			'gif', 'png' 
		); 
				
		$ext = strtolower(content_SFSFile::getFileExtension($uploadfileName));
		if($ext == 'jpeg'){
			$ext = 'jpg';
		}
		 
		$isImage = false;
		$savedAsjpg = false;
		if(in_array($ext, $images)){
			if(in_array($ext, $transform)){									
				$savedAsjpg = true;
			}
			$isImage = true;
		} else {
			// pdf doc 				
		}
		  
		if($addUnique){
			$filename = uniqid() . '_' . $uploadFile['name'];	
		}else {  
			$filename =  $uploadFile['name'];	
		}
		
		$filename = self::getFileWithouExtension($filename) . '.' . strtolower(self::getFileExtension($filename)); 
				  
		list($state, $messages, $filepath) = $upload->uploadTo($config->fsRoot . $addToNode->path, $uploadName, $filename, '', true); 
		  
		if($state){																						  
			
			if($savedAsjpg){
				$thumb = new Thumbnail2($config->htdocsRoot . $filepath);   
				$filepathOrig = $filepath;
				$filepath  = content_SFSFile::getFileWithouExtension($filepath). '.jpg'; 
				$thumb->showAsJPG($config->htdocsRoot . $filepath, 90);      
				unlink($config->htdocsRoot . $filepathOrig);	      		    
			}      
			
			$view->input->fullpath = substr($filepath, strlen('/data/sharedfiles'));			
			$view->input->state = 'PUBLISHED';			
			$view->input->owner = 'a';			 
			$file = helper_Nodes::initContent('SFSFile', $view->input, $view);	
			if($file->getPropertyValue('fullpath')){
				$nnode = helper_Nodes::addNodeWithContent($file, $addToNode->nodeId, $filename, $view, false, true); 
			}
			
			if($returnFullPath === true){
				return $filepath; 
			} elseif ($returnFullPath === 5){  
				return $config->sfFolder . '/' . content_SFSFile::getSFSPath($nnode->nodeId, $nnode->path); 
			} elseif ($returnFullPath === 10){   
				return $config->sfFolder . '/' . content_SFSFile::getSFSPath($nnode->nodeId, $nnode->path) . ';' . self::getFileWithouExtension($nnode->title);  
			} else {			  
				return $file->getPropertyValue('fullpath'); 
			}
		} 
	}
	
	function addFileFromFS($view, $addTo, $filePath, $fileName, $returnFullPath){
				    				
		$config = Zend_Registry::getInstance()->config;
		$tree = Zend_Registry::getInstance()->tree;
		
		$addToNode = $tree->getNodeById($addTo);
		$images = array(
			'jpg', 'gif', 'png' 
		);
		
		$transform = array(
			'gif', 'png' 
		); 
				  
		$ext = strtolower(content_SFSFile::getFileExtension($filePath));
		if($ext == 'jpeg'){
			$ext = 'jpg';
		}
		
		 
		$isImage = false;
		$savedAsjpg = false;
		if(in_array($ext, $images)){
			if(in_array($ext, $transform)){									
				$savedAsjpg = true;
			}
			$isImage = true;
		} else {
			// pdf doc 				
		}
		  
		if($addUnique){
			$filename = uniqid() . '_' . $fileName;	
		}else {  
			
		}
		
		
		 
		$fileName = self::getFileWithouExtension($fileName) . '.' . strtolower(self::getFileExtension($fileName)); 
		 
		
		$state = file_exists($config->htdocsRoot . $filePath);
		 
		e($state);
		e($fileName); 
		e($filePath);  
		
		if($state){		
			if($savedAsjpg){
				$thumb = new Thumbnail2($config->htdocsRoot . $filePath);   
				$filepathOrig = $filePath;
				$filePath  = content_SFSFile::getFileWithouExtension($filePath). '.jpg'; 
				$thumb->showAsJPG($config->htdocsRoot . $filePath, 90);      
				unlink($config->htdocsRoot . $filepathOrig);	      		    
			}      
			
			$view->input->fullpath = substr($filePath, strlen('/data/sharedfiles'));			
			$view->input->state = 'PUBLISHED';			
			$view->input->owner = 'a';			 
			$file = helper_Nodes::initContent('SFSFile', $view->input, $view);	
			
			if($file->getPropertyValue('fullpath')){
				$nnode = helper_Nodes::addNodeWithContent($file, $addToNode->nodeId, $fileName, $view, false, true); 
			}
			
			 
			if($returnFullPath === true){
				return $filePath;  
			} elseif ($returnFullPath === 5){   
				return $config->sfFolder . '/' . content_SFSFile::getSFSPath($nnode->nodeId, $nnode->path); 
			} elseif ($returnFullPath === 10){   
				return $config->sfFolder . '/' . content_SFSFile::getSFSPath($nnode->nodeId, $nnode->path) . ';' . self::getFileWithouExtension($nnode->title);  
			} else {			  
				return $file->getPropertyValue('fullpath'); 
			}
		} 
	}
}
?>
