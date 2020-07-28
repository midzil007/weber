<?
/**
 * Třída reprezentuje novinku
 * @see model_Content
 * @package content
 */
 
class content_Forum extends content_HtmlFile {
    
    public function __construct($config = array())
    {
    	parent::__construct();
    	$this->_name =  get_class($this);  
    	$this->userName = 'Fórum';  
    }
    
    function show($view, $node){
    	$template = parent::getContentTemplate();	
    	$view->html = $this->getPropertyByName('html')->value;     	 	
    	    	    	
    	return $view->render(Zend_Registry::getInstance()->config->view->contentsDir . $template);
    }
}
?>
