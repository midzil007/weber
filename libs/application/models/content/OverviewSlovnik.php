<?
/**
 * Třída reprezentuje typ obsahu - Přehled podstránek
 * @see model_Content
 * @package content
 */
 
class content_OverviewSlovnik extends content_Overview {
    
    public function __construct()
    {   
    	parent::__construct();
    	$this->userName = 'Kávový slovník';
    	    	
    	foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){
				$property->value = 'Slovnik';
			} 
		}     
		
		$this->allowablePages = array(  
    		'Slovo'     
    	); 
    	
    	$this->properties[] = new ContentProperty('rimgs','MultiFileSelect','', array(), array(), array('inputText' => 'odkaz', 'showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 7, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 3242)); 
		
    	
    } 
        
    function show($view, $node){    
    	$view->txtPage = true;    	
    	$template = $this->getPropertyByName('pathToTemplate')->value . '.phtml';
    	$view->content = $this;  
    	// $view->obsahClass = 'txtContent'; 
    	//$view->pageText = $node->description; 
    	$view->pageText = $view->content->getPropertyValue('html');
    	$children = $node->getChildren('ITEM');   
    	$view->list = array();
    	foreach ($children as $ch){
    		$f = mb_substr($ch->title, 0,1,'utf-8');
    		$view->list[$f][] = $ch; 
    	}
    	
    	
		$view->rimgs = ($view->content->getFilesNames('rimgs'));  
		
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
