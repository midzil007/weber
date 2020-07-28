<?
/**
 * Třída reprezentuje základní typ obsahu - HTML soubor.
 * @see model_Content
 * @package content
 */
 
class content_HelpFile extends Content {
    
    public function __construct($config = array())
    {
    	$this->_name =  get_class($this);  
    	$this->vars = array('id', 'localId', 'dateCreate', 'owner', 'dateModif', 'modifiedBy', 'state', 'properties' );
    	$this->userName = 'Stránka nápovědy';
        $this->properties[] = new ContentProperty('html','Wysiwyg','');           	
    }
    
    function show($view, $node){
    	$template = parent::getContentTemplate();    	
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
