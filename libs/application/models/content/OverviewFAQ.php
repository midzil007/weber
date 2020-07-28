<?
/**
 * Třída reprezentuje typ obsahu - FAQ
 * @see model_Content
 * @package content
 */
 
class content_OverviewFAQ extends content_Overview {
    
    public function __construct()
    {   
    	parent::__construct();
    	$this->allowablePages = array(
    		'FAQ'
    	);
    	
    	$this->userName = 'FAQ - často kladené otázky';
    	    	
    	foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){
				$property->value = 'FAQ';
			}
		} 		       
    }
    
    function show($view, $node){    	
    	$template = $this->getPropertyByName('pathToTemplate')->value . '.phtml';
    	$view->pageText = $view->content->getPropertyValue('html');
    	$view->node = $node;
    	$view->content = $this; 
    	$view->children = $node->getChildren();    	
    	return $view->render(Zend_Registry::getInstance()->config->view->overviewsDir . $template);
    	 
    }
    
}
?>
