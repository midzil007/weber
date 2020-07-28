<?
/**
 * Třída reprezentuje typ obsahu - Přehled podstránek
 * @see model_Content
 * @package content
 */
 
class content_OverviewReferences extends content_OverviewList {
    
    public function __construct()
    {   
    	parent::__construct(); 	
    	$this->allowablePages = array(
    		'Reference'
    	);
    	$this->userName = 'Reference';
    	foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){
				$property->value = 'References';
			}
		} 
		
    }
    
    function show($view, $node){    	
    	$template = $this->getPropertyByName('pathToTemplate')->value . '.phtml';
    	$view->pageText = $view->content->getPropertyValue('html');
    	$view->content = $node->getPublishedContent();
    	$view->children = $node->getChildren();
    	
    	
    	$view->mReferences = $references = new module_References();    	
    	
    	$view->ListingItemsCount =  $references->getReferencesCout('dateCreate', 'Desc', 0, 1, $node->nodeId);   
	    // LISTING 		    
		$view->listingPerPage = 10;    
		helper_Listing::init($view);	   
		 
    	$ref = $references->getReferences('dateCreate', 'Desc', $view->listingSQLStartPos, $view->listingPerPage, $node->nodeId);      
		$view->referencesNodes = $references->getReferencesAsNodes($view->tree, $ref);	  		  
		 
    	return $view->render(Zend_Registry::getInstance()->config->view->overviewsDir . $template);
    	 
    }
    
}
?>