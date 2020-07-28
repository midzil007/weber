<?
/**
 * Třída reprezentuje typ obsahu - Přehled podstránek
 * @see model_Content
 * @package content
 */
 
class content_OverviewNews extends content_OverviewList {
    
    public function __construct()
    {   
    	parent::__construct(); 	
    	$this->allowablePages = array(
    		'News'
    	);
    	$this->userName = 'Výpis aktualit';
    	foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){
				$property->value = 'News';
			}
		}
		
    }
    
    function show($view, $node){    	 
    	$template = $this->getPropertyByName('pathToTemplate')->value . '.phtml';
    	$view->pageText = $view->html = $this->getPropertyValue('html');
    	$view->content = $node->getPublishedContent();
    	$view->children = $node->getChildren();
    	return $view->render(Zend_Registry::getInstance()->config->view->overviewsDir . $template);
    	 
    }
    
}
?>