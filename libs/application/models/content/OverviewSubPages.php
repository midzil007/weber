<?
/**
 * Třída reprezentuje typ obsahu - Přehled podstránek
 * @see model_Content
 * @package content
 */
 
class content_OverviewSubPages extends content_Overview {
    
    public function __construct()
    {   
    	parent::__construct();
    	$this->userName = 'Výpis podstránek';
    	    	
    	foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){
				$property->value = 'SubPagesFull';
			}
		} 		       
    }
    
    function show($view, $node){    	
    	$template = $this->getPropertyByName('pathToTemplate')->value . '.phtml';
    	$view->content = $this; 
    	$view->pageText = $view->content->getPropertyValue('html');
    	$view->node = $node;
    	return $view->render(Zend_Registry::getInstance()->config->view->overviewsDir . $template);
    	 
    }
}
?>
