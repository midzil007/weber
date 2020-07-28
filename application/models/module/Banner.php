<?

/**
 * Třída reprentuje zalogovaneho uzivatele webu
 */
class module_Banner {
	
	private $extension, $url, $target;
	
	public function __construct($filepath, $url, $target = '_self', $isFullPath = false) {
		$this->file = $filepath;
		$url = str_replace('http://http://', 'http://', $url); 
		$this->url = $url;
		$this->target = $target;
		$this->isFullPath = $isFullPath;
		$this->extension = content_SFSFile::getFileExtension($filepath);
	} 
	 
	function render($view){
		
		$view->bannerExtension = $this->extension;
		$view->bFile = $view->bFileOrig =  $this->file;
		$view->bUrl = false;
		//if(!$view->disablelink){
			$view->bUrl = $this->url;
		//}
		$view->bTarget = $this->target;
		
		if(!$this->isFullPath){
			$view->bFile = content_SFSFile::getFileFullPath($view->bFile);    
		} 
							
				
		switch ($this->extension){
			case 'swf':
					
				if(!$view->swfParser){
					$view->swfParser = new flash_swfheader();			
				}
				
				if(strpos($view->bFileOrig, $view->config->htdocsRoot) === 0){
					$fullPath = $view->bFileOrig;				
				} else {
					$fullPath = content_SFSFile::getSFSAbsolutePath($view->bFileOrig);
				}
				 
				$view->swfParser->loadswf($fullPath) ;
				//[compressed] => 1 [version] => 9 [size] => 22129 [width] => 250 [height] => 250 [valid] => 1 [fps] => Array ( [0] => 0 [1] => 21 ) [frames] => 1
				
				$view->bVersion = $view->swfParser->version;				
				/*
				if(!$view->bVersion){
					e($view->bVersion);
					e($view->swfParser); 
				}
				*/ 
				$view->bVersion = $this->bVersion?$this->bVersion:9;     
				

				$view->bWidth = $view->swfParser->width;
				$view->bHeight = $view->swfParser->height;
				$view->bSrc = str_replace($view->config->htdocsRoot, '', content_SFSFile::getFileWithouExtension($view->bFileOrig));

				$view->bSrc = content_SFSFile::getFileFullPath($view->bSrc);   
				$view->bSrc = str_replace('.swf', '', $view->bSrc);    
				
				$bannerXhtml = $view->render('templates/Modules/Banners/BannerFlash.phtml');
				break;
				
			default: 
			case 'gif':
			case 'png':
			case 'jpg':
			
				$bannerXhtml = $view->render('templates/Modules/Banners/Banner.phtml');
				break;			
		}
		return $bannerXhtml;
		
	}

}
?>