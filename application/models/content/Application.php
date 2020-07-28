<?
/**
 * Třída reprezentuje typ obsahu - Přehled podstránek
 * @see model_Content
 * @package content
 */
 
class content_Application extends Content {
    
	public $searchableCols = array();
	
    public function __construct()
    {
    	$this->allowablePages = Content::getContentTypesKeys();
    	$this->_name =  get_class($this);  
    	$this->vars = array('id', 'localId', 'dateCreate', 'owner', 'dateModif', 'modifiedBy', 'state', 'properties' );
    	$this->userName = 'Aplikace';
        $this->properties[] = new ContentProperty('pathToApp','Text','', array(),array(), array(), true);        
         
        $this->initProperies();
        
    }
    
    function show($view, $node){ 
    	    	
    	$path = $this->getPropertyByName('pathToApp')->value;
    	$template = $path . '.phtml';    	
    	//$view->pageText = $node->description;        	
    	switch ($path){
    		case 'Photogallery':
    			$view->isPopoupPage = true;
    			break;
    	}

    	$view->node = $node; 
    	
    	return $view->render(Zend_Registry::getInstance()->config->view->appDir . $template);
    }
    
    function renderAdmin($view){
    	parent::renderAdmin($view);
    }
    
    function showAdmin($view){
    	parent::showAdminInit($view);    	
    	parent::renderAdmin($view);
    }
	
}
?>
