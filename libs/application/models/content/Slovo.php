<?
/**
 * Třída reprezentuje Článek
 * @see model_Content
 * @package content
 */
 
class content_Slovo extends content_HtmlFile {
  
             
    public function __construct($config = array())
    {
    	parent::__construct();
    	$this->_name =  get_class($this);   
    	$this->userName = 'Slovo';    	    	

    }
    
   
    function show($view, $node){
    	$template = 'Slovo.phtml';	  
    	$view->node = $node;    	
    	
    	$view->html = $this->getPropertyByName('html')->value;  
    	    	
    	return $view->render(Zend_Registry::getInstance()->config->view->contentsDir . $template);
    } 
     
}

?>
