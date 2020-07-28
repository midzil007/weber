<?
/**
 * Třída reprezentuje typ obsahu - Přehled podstránek
 * @see model_Content
 * @package content
 */
 
class content_OverviewLinks extends content_OverviewList {
    
    public function __construct()
    {   
    	parent::__construct(); 	
    	$this->allowablePages = array(
    		'UserLink'
    	);
    	$this->userName = 'Výpis užitečných odkazů';
    	foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){
				$property->value = 'Links';
			}
		}
		
    }
    
    function show($view, $node){    	
    	$template = $this->getPropertyByName('pathToTemplate')->value . '.phtml';
    	$view->pageText = $view->content->getPropertyValue('html');
    	$view->content = $this; 
    	$view->children = $node->getChildren();
    	return $view->render(Zend_Registry::getInstance()->config->view->overviewsDir . $template);
    	 
    }
    	
}
?>