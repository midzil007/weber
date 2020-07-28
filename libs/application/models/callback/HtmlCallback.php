<?
/**
 * Callback evidující v HTML obsahu obsažené reference na jiné stránky.
 */
class callback_HtmlCallback {
	
	public function __construct($content, $references) {
		$this->content = $content;
		$this->_tableName = 'References';
		//$this->db = Zend_Registry::getInstance()->db;
		$this->tree = Zend_Registry::getInstance()->tree;
		$this->references = $references;
	}
	
	public function call($matches) {
		
		$path = $matches[2];		
				
		$ext = content_SFSFile::getFileExtension($path);
		$ext = $ext?$ext:'html';
		
		switch ($ext){
			case 'html':
			case 'htm':
				$path = content_SFSFile::getFileWithouExtension($path);
				$node = $this->tree->getNodeByPath($path);
				break;
			
			default:
				if(content_SFSFile::isSFSFile($path)){
					$node = $this->tree->getNodeById(content_SFSFile::getFileNodeId($path));
				}
				break;
		}
				
		if ($node) {	
			$this->references->addReference(
				$this->content->getNodeId(), 
				$this->content->id,  
				$node->nodeId
			);			
		}
		return $matches[0];
	}
	
	public function images($matches) { 
		//pr($matches);   
		
		// <img width="200" height="133" align="right" alt="" class="fckImg clickableImage" src="/data/sharedfiles/6812/3612921-20070530-m-clinton-sm2.jpg" />
		// ismap 
		
		$xml = simplexml_load_string($matches[0]);
		$parts = array();  
		$parts[] = '<img'; 
		foreach($xml->attributes() as $a => $b) {
			$parts[] = $a . '="' . $b . '"';
		} 
		$parts[] = '/>';
		
		$src = $width = $height = '';
		foreach ($parts as $i => $part){ 
			$part = str_replace('"', '', $part);
			list($key, $val) = explode('=', $part);
			if($key == 'width'){
				$width = $val;
			}
			if($key == 'height'){ 
				$height = $val;
			}
			
			if($key == 'src'){
				$src = $val; 
				unset($parts[$i]); 
			}
			if($key == 'ismap'){
				unset($parts[$i]);  
			}
		}
		$imgName = 'wsi' . $width . 'x' . $height; 
		$widthF = 750;
		$heightF = 550;		
		$imgNameFull = 'wsi' . $widthF . 'x' . $heightF; 
		
		$properties = array(
    		'name' => $imgName,  
    		'width' => $width,
    		'height' => $height,     
    		'autosize' => false
    	);  
    	$properties2 = array(
    		'name' => $imgNameFull,  
    		'width' => $widthF,
    		'height' => $heightF,     
    		'autosize' => false 
    	);  
    	 
    	array_pop($parts); 
    	
    	$img = $matches[0];
    	
    	$conf = Zend_Registry::getInstance()->config;		 
		if($src && $height && $width){
			list($nodeId, $n) = content_SFSFile::parseSFSPath($src);
			$nodeId = $nodeId + 0; 
			
			if($nodeId && is_int($nodeId)){
				$photoPath = $conf->sfFolder . '/' . $nodeId . '/' . $imgName . '-' . content_SFSFile::getFileExtension($n) . '/' . $n;
				$photoPathFull = $conf->sfFolder . '/' . $nodeId . '/' . $imgNameFull . '-' . content_SFSFile::getFileExtension($n) . '/' . $n;
				
				content_SFSFile::createFileThumbs($src, $properties);
				content_SFSFile::createFileThumbs($src, $properties2);
				
				$photoPath = content_SFSFile::getFileFullPath($photoPath); // fullPATH FIX        
		    	$photoPathFull = content_SFSFile::getFileFullPath($photoPathFull); // fullPATH FIX    
		   		
		    	
				//$filePath = helper_FrontEnd::getPhoto() 
				
				
				   
				$parts[] = 'src="' . $photoPath . '"';
				$parts[] = 'ismap="' . $photoPathFull . '"';
				$parts[] = '/>';
				$img = implode(' ', $parts);    
			}
		} else {
			
		} 
		
		// pr($img); die(); 
		 
		return $img;
	}
	
	public function wrapImapges($matches) { 
				
		$xml = simplexml_load_string($matches[0]);
		$parts = array(); 
		$parts[] = '<img'; 
		foreach($xml->attributes() as $a => $b) {
			$parts[] = $a . '="' . $b . '"';
		}
		$parts[] = '/>';
		  
		$src = $width = $height = $bigImage = '';
		foreach ($parts as $i => $part){ 
			$part = str_replace('"', '', $part);
			list($key, $val) = explode('=', $part);
			if($key == 'width'){
				$width = $val;
			}
			if($key == 'height'){ 
				$height = $val;
			}
			
			if($key == 'src'){
				$src = $val; 
			}
			if($key == 'ismap'){
				$bigImage = $val;
				unset($parts[$i]);  
			}
			
			if($key == 'class'){
				unset($parts[$i]);  
			}
			
			if($key == 'alt'){
				$alt = $val; 
			}
			
		}
		
    	$img = $matches[0];
    		 
		if($bigImage){  
			$img = implode(' ', $parts);  
				
			$this->references->mbNo = $this->references->mbNo?$this->references->mbNo:0; 
			$this->references->mbNo += 1;   
			   
			$img = '    
			<span class="multiBoxDesc mb' . $this->references->mbNo . '"></span>     	     	  
			<a href="' . $bigImage . '" target="_blank" title="' . $alt . '" rel="[images]" id="mb' . $this->references->mbNo . '" class="mb">     
	    	' . $img . '  
	    	</a>   
			';
		} else { 
			
		} 
		
		// pr($img); die(); 
		 
		return $img;
	}
}
?>