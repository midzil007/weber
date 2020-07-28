<?
/**
 * Třída reprezentuje typ obsahu - Přehled podstránek
 * @see model_Content
 * @package content
 */
 
class content_OverviewContact extends content_OverviewList {
    
    public function __construct()  
    {   
    	parent::__construct(); 
    	
    	$this->userName = 'Kontakty'; 
    	
    	$this->properties[] = new ContentProperty('adresa','Text');   
    	$this->properties[] = new ContentProperty('cemail','Text'); 
    	$this->properties[] = new ContentProperty('cjmeno','Text'); 
    		
    	foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){
				$property->value = 'Contact';
			}
		}
    } 
    
    function show($view, $node){    	 
    	$view->txtPage = true;     
    	$template = $this->getPropertyByName('pathToTemplate')->value . '.phtml';
    	$view->pageText = $view->html = $this->getPropertyValue('html');
    	$view->content = $this; 
    	// $view->children = $node->getChildren();
    	return $view->render(Zend_Registry::getInstance()->config->view->overviewsDir . $template);
    	 
    }
    
}
?>