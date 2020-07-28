<?
class content_OverviewZnacky extends content_Overview { 
     
    public function __construct()
    {   
    	parent::__construct();
    	$this->allowablePages = array(
    	);
    	
    	$this->allowableOverviews = array(
    		'OverviewZnacka' 
    	);
    	    	 
    	$this->userName = 'Značky';
    	    	
    	foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){ 
				$property->value = 'Znacky'; 
			}
		} 		  
    }
    
    function show($view, $node){    	
    	$template = 'Znacky.phtml'; 
    	$view->pageText = $view->content->getPropertyValue('html');
    	$view->node = $node;    	
    	$view->html = $this->getPropertyValue('html');      
    	$view->children = $node->getChildren('FOLDER'); 
    	return $view->render(Zend_Registry::getInstance()->config->view->overviewsDir . $template);
    } 	
	 
}
?>
