<?
class content_OverviewProjects extends content_Overview {
    
    public function __construct()
    {   
    	parent::__construct();
    	$this->allowablePages = array(
    		'Project' 
    	);
    	    	
    	$this->userName = 'Projekty';
    	    	
    	foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){
				$property->value = 'Projects';
			}
		} 		       
    }
    
    function show($view, $node){    	
    	$template = $this->getPropertyByName('pathToTemplate')->value . '.phtml';
    	$view->pageText = $view->content->getPropertyValue('html'); 
    	$view->children = helper_FrontEnd::checkChildren($node->getChildren('ITEM'));
    	
    	return $view->render(Zend_Registry::getInstance()->config->view->overviewsDir . $template);
    }
    	
}
?>
