<?
/**
 * Třída pro praci se soubory
 * @package helper
 */
 
class helper_Nodes extends helper_File {
	public static $debug = true;
	
	static function initContent($ctype, $input, $view){
		$content = Content::init($ctype, $input, $view->acl);		
		/*
		foreach ($properties as $pname => $pvalue){
			$content->getPropertyByName($pname)->value = $pvalue;
		}
		*/
		return $content;
	}
    		
	static function addNode($ctype, $properties, $addTo, $nodeTite, $view, $debug = false){	
		
		$input = new stdClass();
		$input->pageTitle = $nodeTite;
		$input->state = 'PUBLISHED';
				
		// node
		$newNode = Node::init('ITEM', $addTo, $input, $view);
					
		//content    	
		$input = new stdClass();				
		$content = Content::init($ctype, $input, $view->acl);	
		
		foreach ($properties as $pname => $pvalue){
			$content->getPropertyByName($pname)->value = $pvalue;
		}
		
		if($debug){
			pr($newNode);
			pr($content);
		} else {
			self::save($view, $newNode, $content);
		}		
		return $newNode;
	}
	
	static function addNodeWithContent($content, $addTo, $nodeTite, $view, $debug = false, $isFile = false){	
		$session = Zend_Registry::getInstance()->session;
		$input = new stdClass();
		$input->pageTitle = $nodeTite;
				
		// node
		$newNode = Node::init('ITEM', $addTo, $input, $view);
			
		$content->id = $content->getNextContentId();
    	$content->localId = 1;
		$content->dateCreate = $content->dateModif = Utils::mkTime();
		
		if(!$session->user->username){
			$session->user->username = $session->webUser->username;
		}
		
		$session->user->username = $session->user->username?$session->user->username:'a';
		
		$content->owner = $content->modifiedBy = $session->user->username;		
		$content->state = $view->input->state?$view->input->state:'PUBLISHED';	
		
		if($isFile){
			$newNode->path = $content->getPropertyValue('fullpath'); 
		}
		
		if($debug){
			pr($newNode); 
			pr($content); 
		} else {
			self::save($view, $newNode, $content);
		}
		return $newNode;
	}
	
	function save($view, $newNode, $content){
		
		$err2 = $content->save();	
    	$view->tree->addNode($newNode, false, false);    	
    	$view->tree->pareNodeAndContent($newNode->nodeId, $content->id, $content->_name);    		    	
    	//parent::audit($newNode->title, $newNode->nodeId);
	}
}
?>
