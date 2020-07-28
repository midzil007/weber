<?
/**
 * Třída reprezentuje typ obsahu - Přehled podstránek
 * @see model_Content
 * @package content
 */
 
class content_OverviewList extends content_Overview {
    
    public function __construct()
    {   
    	parent::__construct();
    	$this->userName = 'Přehled podstránek';
    	    	
    	foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){
				$property->value = 'SimpleList';
			}
		}     
    } 
        
    function show($view, $node){    
    	$view->txtPage = true;    	
    	$template = $this->getPropertyByName('pathToTemplate')->value . '.phtml';
    	$view->content = $this; 
    	// $view->obsahClass = 'txtContent'; 
    	//$view->pageText = $node->description;
    	
    	if($node->nodeId == 3801){      
    		helper_FrontEnd::redirect('/e-shop/kavy',302);
    	}
    	
    	$view->pageText = $view->content->getPropertyValue('html');
    	$view->children = $node->getChildren('BOTH');
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
