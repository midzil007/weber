<?
/**
 * Třída reprezentuje DictionaryPhrase
 * @see model_Content
 * @package content
 */
 
class content_DictionaryPhrase extends content_HtmlFile {
    
    public function __construct()
    {
    	parent::__construct();
    	$reg = Zend_Registry::getInstance();
    	$this->_name =  get_class($this);  
    	$this->userName = 'Položka slovníku';    	    
    	$this->properties[] = new ContentProperty('showin','CheckboxGroup','', array(), $reg->config->subdomains);	        
    }
    
    function show($view, $node){
    	$template = parent::getContentTemplate();	
    	$view->html = $this->getPropertyByName('html')->value;        		 	
    	return $view->render(Zend_Registry::getInstance()->config->view->contentsDir . $template);
    }
    
    function showAdmin($view){
    	parent::showAdminInit($view);    	    	
    	parent::renderAdmin($view);    	
    }	
}
?>
