<?
/**
 * Třída reprezentuje typ obsahu - Přehled podstránek
 * @see model_Content
 * @package content
 */
 
class content_OverviewFaq extends content_Overview {
    
    public function __construct()
    {   
    	parent::__construct();
    	$this->userName = 'Přehled FAQ';
    	    	
    	foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){
				$property->value = 'Faq';
			}
		}     
		
		$this->allowablePages = array(
    		'Faq'    
    	);
    	
    } 
        
    function show($view, $node){    
    	$view->txtPage = true;    	
    	$template = $this->getPropertyByName('pathToTemplate')->value . '.phtml';
    	$view->content = $this;  
    	// $view->obsahClass = 'txtContent'; 
    	//$view->pageText = $node->description; 
    	$view->pageText = $view->content->getPropertyValue('html'); 
    	
    	$view->children = $menu = helper_FrontEnd::checkChildren($node->getChildren('ITEM'));
    	
    	return $view->render(Zend_Registry::getInstance()->config->view->overviewsDir . $template); 
    } 
    
    /*  
    function showAdmin($view){    	
    	 
    	if($view->node->nodeId == 3801){	  
	    	$this->allowableOverviews = array(
	    		'OverviewProducts'
	    	);  
    	}    
    	  
    	parent::showAdminInit($view); 
    	parent::renderAdmin($view);   	
    } 
    */   
    	
    
}
?>
