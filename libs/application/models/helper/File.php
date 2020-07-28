<?
/**
 * Třída pro praci se soubory
 * @package helper
 */
 
class helper_File {
    		
	static function importImage($image, $addTo, $view, $save = true){		
		if($image){
			$reg = Zend_Registry::getInstance();
			$addToNode = $reg->tree->getNodeById($addTo);
			if(!$addToNode){
		   		die('neplatny uzel');
		   	}
		   	$addToPath = $addToNode->path;
			
		   	$fullpath = self::copyTo($image, $addToPath);
		   
		   	if(!$fullpath){
		   		return '';
		   	}
		   
		   	
			$input = new stdClass();
			
			$input->pageTitle = content_SFSFile::getFileWithouExtension(content_SFSFile::getFileName($fullpath));
			
			$newNode = Node::init('ITEM', 	$addTo, $input, $view);
			$newNode->path = $fullpath;
			$ctype = 'SFSFile';		
			$content = Content::init($ctype, $input, $view->acl);
			$content->state = 'PUBLISHED';
			$content->properties[0]->value = stripslashes($fullpath);
			if($save){
				self::save($newNode, $content); // !!!!!!!
			}
			return $reg->config->sfFolder . '/' . content_SFSFile::getSFSPath($newNode->nodeId, $newNode->path) . ';' . $newNode->title;
		}
	}
	
	function copyTo($target, $destionation){	
				
		if(!content_SFSFile::isMovable($target)){ // !!!!!!!
			$reg = Zend_Registry::getInstance();
			
			$sfRoot = $reg->config->fsRoot;  
			$entry = content_SFSFile::getFileName($target);
			 
			$entry = Utils::generatePathName($entry,'.-_', $destionation . '/');
			$entry = str_replace('_', '-', $entry);
			
			//e('!!!! - ' . $sfRoot . $target . '  x-x ' . $sfRoot . $destionation . '/' . $entry);			
			copy( $sfRoot . $target, $sfRoot . $destionation . '/' . $entry ); // !!!!!!!
			
			return $destionation . '/' . $entry;
		} else {
			e('!!! ' . $target);
		}
	}
		
	function save($newNode, $content){		
		$reg = Zend_Registry::getInstance();		
		$err2 = $content->save();	
    	$reg->tree->addNode($newNode, false, false);
    	$reg->tree->pareNodeAndContent($newNode->nodeId, $content->id, $content->_name);    		    	
    	//parent::audit($newNode->title, $newNode->nodeId);
	}
}
?>
