<?
/**
 * Třída reprezentuje typ obsahu - Přehled podstránek
 * @see model_Content
 * @package content
 */
 
class content_OverviewIntranet extends content_Overview {
    
    public function __construct()
    {   
    	parent::__construct();
    	$this->userName = 'Úvodní stránka intranetu';
    	    	
    	foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){
				$property->value = 'OverviewIntranet';
			}
		} 
		       
    }
    		
    function show($view, $node){    	
    	$template = $this->getPropertyByName('pathToTemplate')->value . '.phtml';    	
    	$view->pageText = $view->content->getPropertyValue('html');
    	
    	$moduleIntranet = new module_Intranet();
    	$view->files = $moduleIntranet->getLastAddedFiles(true, 5);
	
    	return $view->render(Zend_Registry::getInstance()->config->view->overviewsDir . $template);
    	 
    }
	
}
?>
