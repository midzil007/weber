<?
  class content_OverviewVideos extends content_Overview { 
    public function __construct()
    {   
    	parent::__construct();
    	$this->allowablePages = array(
    		'Video'
    	);
    	    	
    	$this->userName = 'Přehled videií';
    	
    foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){
				$property->value = 'Video';
			}
		}	  
		 
    } 
     
  
    
    function show($view, $node){    	
    	$template = $this->getPropertyByName('pathToTemplate')->value . '.phtml';
    	$view->content = $this; 
    	$view->pageText = $view->content->getPropertyValue('html');
    	$view->node = $node;
		$view->mVideos = new module_Videos();    
    	return $view->render(Zend_Registry::getInstance()->config->view->overviewsDir . $template);
    	 
    }
        	 
}?>
