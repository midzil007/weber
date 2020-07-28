<?
class content_OverviewDownloads extends content_Overview {
    
    public function __construct()
    {   
    	parent::__construct();
    	$this->allowablePages = array(
    	
    	);
    	    	
    	$this->userName = 'Ke stažení'; 
		$this->properties = array();
    	$this->properties[] = new ContentProperty('pathToTemplate','Hidden','Simple');          
        $this->properties[] = new ContentProperty('html','Wysiwyg','', array(),array(), array(), true);    
        
    	foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){
				$property->value = 'Downloads';
			}
		} 
		
		 

		//$this->properties[] = new ContentProperty('fArchiv','MultiFileSelect','', array(), array(), array('showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 30, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 4014 ));        
		//$this->properties[] = new ContentProperty('fTech','MultiFileSelect','', array(), array(), array('showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 30, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 4015 ));
		$this->properties[] = new ContentProperty('files','MultiFileSelect','', array(), array(), array('showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 30, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 4012 ));
		    
		                 
        $this->initProperies();      
    } 
          
    function show($view, $node){    	
    	$template = $this->getPropertyByName('pathToTemplate')->value . '.phtml';
    	$view->pageText = $this->getPropertyValue('html');
    	/*
    	$view->children = $node->getChildren();
    				
    	$view->disableH1 = 1;
    	    	
    	$view->salonsSelect = array();
    	$ch = array(); 
    	foreach ($view->children as $salon){
    		$ch[] = $salon;
    		$s = $salon->getPublishedContent();		
    		if($s){
    			$city = trim($s->getPropertyValue('mesto'));
    			$view->salonsSelect[$city] = $city; 
    		}     		
    	}    	  
    	helper_Sorting::$sortPropertyName = 'mesto';
    	usort($view->children, array(helper_Sorting, "cmp_variable"));   
    	
    	uksort($view->salonsSelect, array(helper_Sorting, "cmp_title2")); 
    	    */ 	
    	return $view->render(Zend_Registry::getInstance()->config->view->overviewsDir . $template);
    }
    	
}
?>
