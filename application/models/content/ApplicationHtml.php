<?
/**
 * Třída reprezentuje typ obsahu - Přehled podstránek
 * @see model_Content
 * @package content
 */
 
class content_ApplicationHtml extends content_HtmlFile {
    
	public $searchableCols = array();
	
    public function __construct()
    {
    	
    	$this->allowableOverviews = array(
    		'OverviewZnacka'  
    	);
    	
    	$this->_name =  get_class($this);  
    	$this->vars = array('id', 'localId', 'dateCreate', 'owner', 'dateModif', 'modifiedBy', 'state', 'properties' );
    	$this->userName = 'Aplikace s HTML obsahem';
        $this->properties[] = new ContentProperty('pathToApp','Text','', array(),array(), array(), true);  
        $this->properties[] = new ContentProperty('html','Wysiwyg','', array(),array(), array(), true);  
        $this->allowablePages = Content::getContentTypesKeys();
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
    	$view->html = $this->getPropertyByName('html')->value;
    	$view->children = helper_FrontEnd::checkChildren($node->getChildren('BOTH'), 0); 
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
