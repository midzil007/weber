<?
/**
 * Třída reprezentuje Článek
 * @see model_Content
 * @package content
 */
 
class content_About extends content_HtmlFile {
    
    public function __construct($config = array())
    {
    	parent::__construct();
    	$this->_name =  get_class($this);  
    	$this->userName = 'O portálu';    	 
    	$this->properties[] = new ContentProperty('podminky','Wysiwyg','', array(),array(), array(), true);          	    	 

    }
    
    function show($view, $node){
    	$template = parent::getContentTemplate();	
    	$template2 = 'AboutRight.phtml';
    	
    	$view->content = $this; 
    	$view->node = $node;   
    	
    	$view->html = $this->getPropertyByName('html')->value;   
    	$view->podminky = $this->getPropertyByName('podminky')->value;    	
    	
    	$pageLeft = $view->render(Zend_Registry::getInstance()->config->view->contentsDir . $template);
    	$pageRight = $view->render(Zend_Registry::getInstance()->config->view->contentsDir . $template2);
    	
    	Instance::setPages($view, $pageLeft, $pageRight);
    }
    
}
?>
