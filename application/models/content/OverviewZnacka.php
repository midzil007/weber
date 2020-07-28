<?
class content_OverviewZnacka extends content_Overview {
    
    public function __construct() 
    {   
    	parent::__construct();
    	  	 
    	$this->userName = 'Značka'; 

    	$this->properties[] = new ContentProperty('url','Text','', array(), array(), array(), false); 
    	
        $this->properties[] = new ContentProperty('html2','Wysiwyg','', array(),array(), array(), true);       

    // 	$this->properties[] = new ContentProperty('logoTop','FileSelect','', array(), array(), array('showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 30, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 51772 ));
    	$this->properties[] = new ContentProperty('logoMain','FileSelect','', array(), array(), array('showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 30, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 51772 ));
    //	$this->properties[] = new ContentProperty('inTop','Select','', array(), array(1 => 'ANO', 0 => 'NE'), array(), false); 
		 
    	foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){  
				$property->value = 'znacka'; 
			}
		} 		       
    } 
    
    function show($view, $node){    	
    	$template = $this->getPropertyByName('pathToTemplate')->value . '.phtml';
    	$view->pageText = $view->content->getPropertyValue('html');   
    	$view->pageText2 = $view->content->getPropertyValue('html2');   
    	$view->purl = $view->content->getPropertyValue('url');   
    	$view->node = $node; 
    	$view->content = $this;
    	return $view->render(Zend_Registry::getInstance()->config->view->overviewsDir . $template);
    }
    	
}
?>
