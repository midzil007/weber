<?
/**
 * Třída reprezentuje typ obsahu - Přehled podstránek
 * @see model_Content
 * @package content
 */
 
class content_OverviewFiles extends content_Overview {
    
    public function __construct()
    {   
    	parent::__construct();
    	
    	$this->userName = 'Přehled souborů';
    	    	
    	foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){
				$property->value = 'FileList';
			}
		} 		
		
		$this->properties[] = new ContentProperty('files','MultiFileSelect','', array(), array(), array('showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 30, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 288 ));        
    }
    
    function show($view, $node){    	
    	$template = $this->getPropertyByName('pathToTemplate')->value . '.phtml';  
    	$view->content = $node->getPublishedContent();
    	$view->pageText = $view->content->getPropertyValue('html');
    	$view->node = $node;
    	$view->documents = $view->content->getFilesNames('files');  
    	return $view->render(Zend_Registry::getInstance()->config->view->overviewsDir . $template);
    	 
    }
   
}
?>
