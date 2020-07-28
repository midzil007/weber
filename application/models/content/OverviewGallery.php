<?
class content_OverviewGallery extends content_Overview { 
    
	
	public $fotoFullName = 'pFull';
	public $fotoShowName = 'pShow';
    public $fotoThumbName = 'pThumb';
    public $fotoMiniName3 = 'pMini';  
    
    public function __construct()
    {   
    	parent::__construct();
    	$this->allowablePages = array(
    	);
    	    	 
    	$this->userName = 'Galerie';
    	    	
    	foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){ 
				$property->value = 'Gallery';
			}
		} 		  
		  
		$this->properties[] = new ContentProperty('asGallery', 'Select','', array(),array(1 => 'Galerie', 2 => 'Pás fotek'), array(), true);  
    	
		// $this->properties[] = new ContentProperty('tags', 'ItemsStack','', array(),array(), array(), true);  
    	      	
    	$this->properties[] = new ContentProperty('photos','MultiFileSelect','', array(), array(), array('showUrl' => 1, 'showAlt' => 1,'showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 60, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 4195 ));   
    	$this->properties[] = new ContentProperty('files','MultiFileSelect','', array(), array(), array('showUrl' => 1, 'showAlt' => 1,'showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 30, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 4195 )); 
    	    	  
    }  
    
    function show($view, $node){    	
    	$template = 'Gallery.phtml'; 
    	$view->pageText = $view->content->getPropertyValue('html');
    	$view->node = $node;
    	
    	// $this->createFiles();	     
    	 
     	$view->photos = $this->getFilesNames('photos'); 
    	
    	$view->html = $this->getPropertyValue('html');    
    	$view->documents = $this->getFilesNames('files');  
    	$view->children = $node->getChildren('FOLDER'); 
    	
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
    
	     	
function createFiles($subdomain = false){
    	$settings = Zend_Registry::getInstance()->settings;
    	 
    	     	 
    	$this->createPropertyThumbs(
    		array(
    			array(
    				'name' => $this->fotoThumbName, 
    				'width' => 200,    
    				'height' => 150,
    				'autosize' => false 
    			),
    			array(
    				'name' => $this->fotoMiniName3, 
    				'width' => 111,    
    				'height' => 250,   
    				'autosize' => false 
    			),
    			array(
    				'name' => $this->fotoFullName, 
    				'width' => 950,  
    				'height' => 600,       
    				'autosize' => false 
    			) 
    		),  
    		'photos'   
    	);   
    	   
    }
     
    function onSave(){ 
    	$this->createFiles();	    	
    	parent::onSave();
    }
        
    function onUpdate(){  
    	
    	$this->createFiles(); 
    	
    	parent::onUpdate();
    }
            
    function onDelete(){
    	parent::onDelete();
    }
}
?>
