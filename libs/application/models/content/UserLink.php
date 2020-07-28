<?
/**
 * Třída reprezentuje základní typ obsahu - HTML soubor.
 * @see model_Content
 * @package content
 */
 
class content_UserLink extends Content {
    
    public function __construct($config = array())
    {
    	$this->_name =  get_class($this);  
    	$this->nodeTitle = 'title';    	
    	$this->searchableCols = array('info');	
    	
    	$this->userName = 'Odkaz';   
    	$this->properties[] = new ContentProperty('url','Text','');  
    	$this->properties[] = new ContentProperty('logo','FileSelect','');  
        $this->properties[] = new ContentProperty('info','Wysiwyg','');        
               	
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
