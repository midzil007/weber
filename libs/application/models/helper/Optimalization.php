<?
/**
 * Třída pro praci s vystupy na webu
 * @package helper
 */
 
class helper_Optimalization {   	
	
	static function replaceSFSpathWithFullPath($text){		
		$text = preg_replace_callback('/(src|href|data|value)\s*=\s*"([^"]*)"/i', array(self, 'replaceSFSpathWithFullPathCallback'), $text); 				
		return $text; 
	}	
	 
	static function replaceSFSpathWithFullPathCallback($matches) {		
		$path = $matches[2];			 
		$ext = content_SFSFile::getFileExtension($path);
		$ext = $ext?$ext:'html';		
		switch ($ext){
			case 'html':
			case 'htm':
				break;			
			default:
				if(content_SFSFile::isSFSFile($path)){
					$path = content_SFSFile::getFileFullPath($path);  
				}
				break;
		}
		 
		return $matches[1] . '="' . $path . '"'; 
	}
}
?>
