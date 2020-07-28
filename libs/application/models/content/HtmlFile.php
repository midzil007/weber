<?
/**
 * Třída reprezentuje základní typ obsahu - HTML soubor.
 * @see model_Content
 * @package content
 */
 
class content_HtmlFile extends Content {
    
    public function __construct($config = array())
    {
    	$this->_name =  get_class($this);  
    	$this->vars = array('id', 'localId', 'dateCreate', 'owner', 'dateModif', 'modifiedBy', 'state', 'properties' );
    	$this->userName = 'HTML soubor';
        $this->properties[] = new ContentProperty('html','Wysiwyg','', array(),array(), array(), true);           
        $this->searchableCols = array('html');		
        
        $this->initProperies();
        
    }
    
    function show($view, $node){
    	$template = parent::getContentTemplate(); 
    	$view->content = $this; 
    	$view->node = $node;   
    	   	
    	$view->html = $this->getPropertyByName('html')->value;     	 	
    	return $view->render(Zend_Registry::getInstance()->config->view->contentsDir . $template);
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
