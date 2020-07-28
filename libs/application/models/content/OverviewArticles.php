<?
class content_OverviewArticles extends content_Overview {
    
    public function __construct()
    {   
    	parent::__construct();
    	$this->allowablePages = array(
    		'Article'
    	);
    	    	
    	$this->userName = 'Přehled článků';
    	    	
    	foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){
				$property->value = 'Articles';
			}
		} 		       
		  
		 
    }
     
    function show($view, $node){  
    	 	
    	$template = $this->getPropertyByName('pathToTemplate')->value . '.phtml';
    	$view->pageText = $view->content->getPropertyValue('html');
    	//$view->articlesNodes = $node->getChildren();   
    	
    	$view->ListingItemsCount =  $view->mArticles->getArticlesCout('dateShow', 'Desc', 0, 1, $node->nodeId);   
	    // LISTING 		  
		$view->listingPerPage = 7;    
		helper_Listing::init($view);	 
    	$articles = $view->mArticles->getArticles('dateShow', 'Desc', $view->listingSQLStartPos, $view->listingPerPage, $node->nodeId);
		$view->counts = count($articles);     	        
		$view->articlesNodes = $view->mArticles->getArticlesAsNodes($view->tree, $articles);	  	 
    	   	 
    	//$view->disableH1 = 1;
    	    	   	  
    	return $view->render(Zend_Registry::getInstance()->config->view->overviewsDir . $template); 
    }
        	 
} 
?>