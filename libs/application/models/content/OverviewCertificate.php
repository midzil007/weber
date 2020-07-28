<?
class content_OverviewCertificate extends content_Overview {
    
    public function __construct()
    {   
    	parent::__construct();
    	$this->allowablePages = array(
    		'Certificate' 
    	);
    	    	
    	$this->userName = 'Certifikáty';
    	    	
    	foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){
				$property->value = 'Projects';
			}
		} 		       
    }
    
    function show($view, $node){    	
    	$template = $this->getPropertyByName('pathToTemplate')->value . '.phtml';
    	//$view->pageText = $view->content->getPropertyValue('html');
    	$mCer = new module_Certificates();
    	$articles = $mCer->getArticles('n.id', 'Desc', 0, 100, $node->nodeId);
		$view->certificates = $mCer->getArticlesAsNodes($view->tree, $articles);
    	//$view->children = helper_FrontEnd::checkChildren($node->getChildren('ITEM'));
    	
    	return $view->render(Zend_Registry::getInstance()->config->view->overviewsDir . $template);
    }
    	
}
?>
