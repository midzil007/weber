<?
/**
 * Třída reprezentuje základní typ obsahu - Hyperlink
 * @see model_Content
 * @package content
 */
 
class content_Hyperlink extends Content {
    
    public function __construct($config = array())
    {    	
    	$this->_name =  get_class($this);  
    	$this->userName = 'Zástupce';
    	$this->searchableCols = array();
    	
        $this->properties[] = new ContentProperty('pageNodeId','PageSelect','');           
       	$this->properties[] = new ContentProperty('url','Text','');           
       	$this->properties[] = new ContentProperty('target','Select',0, array(), array('_self' => 'Stejného okna', '_blank' => 'Nového okna', '_parent' => 'Nadřazeného okna' ));
        		 
    }
    
    function show($view, $node){    	
    	if($url = $this->getPropertyValue('url')){
    		$pos1 = strpos($url, 'ftp://');
    		$pos2 = strpos($url, 'http://');
			if ($pos1 === false && $pos2 === false) {
				$url = 'http://' . $url;
			}			
    	} else {
    		$targetNode = Zend_Registry::getInstance()->tree->getNodeById($this->getPropertyByName('pageNodeId')->value);    	
    		$url = Utils::getFrontEndUrl($targetNode->path);
    	}
    	
    	header ('HTTP/1.1 301 Moved Permanently');
  		header ('Location: '. $url);
  		exit();
    }
    
    function renderAdmin($view){
    	parent::renderAdmin($view);
    }
    
    function showAdmin($view, $callRender = false){
    	parent::showAdminInit($view);    	
    	parent::renderAdmin($view);
    }
    			
}
?>
