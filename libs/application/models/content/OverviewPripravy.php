<?
/**
 * Třída reprezentuje typ obsahu - Přehled podstránek
 * @see model_Content
 * @package content
 */
 
class content_OverviewPripravy extends content_Overview {
    
    public function __construct()
    {   
    	parent::__construct();
    	$this->allowablePages = array(
    		'Priprava'  
    	);
    	    	
    	$this->userName = 'Přehled přípav';
    	    	
    	foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){
				$property->value = 'Pripravz';
			}
		} 
    }
    
    function show($view, $node){
    	$template = $this->getPropertyByName('pathToTemplate')->value . '.phtml';
    	$view->content = $node->getPublishedContent();
    	
    	$view->pageText = $view->content->getPropertyValue('html');
    	//e($node);
    	$view->children = $node->getChildren('ITEM'); 
    	
    	$aTipy = new module_Tipy();
    	$view->tipy = $aTipy->getTipy(52840, 'dateCreate', 'Desc',0,2); 
    	$view->recepty = $aTipy->getTipy(52839, 'dateCreate', 'Desc',0,2); 
    	
    	return $view->render(Zend_Registry::getInstance()->config->view->overviewsDir . $template);
    	 
    	
    }
    	
} 
?>
