<?
/**
 * Třída reprezentuje FAQ
 * @see model_Content
 * @package content
 */
 
class content_FAQ extends Content {
    
    public function __construct($config = array())
    {
    	$this->_name =  get_class($this);  
    	$this->userName = 'FAQ - často kladená otázka';
        $this->properties[] = new ContentProperty('question','Textarea','', array(),array(), array(), true);  
        $this->properties[] = new ContentProperty('answer','Wysiwyg','', array(),array(), array(), true);       
        
        $this->searchableCols = array('question', 'answer');	  	
        
        $this->initProperies();
    }
    
    function show($view, $node){
    	$template = parent::getContentTemplate();    	
    	$view->question = $this->getPropertyValue('question');   
    	$view->answer = $this->getPropertyValue('answer');     	 	
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
