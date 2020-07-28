<?
class content_OverviewOdber extends content_Overview {
     
	public $fotoFullName = 'pFull';
	public $fotoShowName = 'pShow'; 
	public $fotoThumbName = 'pST';   
    
    public function __construct()
    {   
    	parent::__construct();
    	$this->allowablePages = array( 
    		'Product' 
    	);
    	
    	$this->allowableOverviews = array(
    		'OverviewProducts'
    	);  
    	
    	$this->userName = 'Produkty'; 
    	    	
    	foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){
				$property->value = 'Odber'; 
			}
		}  
		 
        $this->properties[] = new ContentProperty('htmlHP','Wysiwyg','', array(),array(), array(), true);    
		$this->properties[] = new ContentProperty('photo','FileSelect','', array(), array(), array('showSelectFile' => true, 'inputWidth' => '300',  'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 3849 ));   
    	$this->properties[] = new ContentProperty('mapa','Text','');   
    	  
    }
     
    function show($view, $node){    	
    	$template = $this->getPropertyByName('pathToTemplate')->value . '.phtml';
    	$view->pageText = $view->content->getPropertyValue('html'); 
    	$view->node = $node; 
    	$view->content = $this;   
	    	 
    	return $view->render(Zend_Registry::getInstance()->config->view->overviewsDir . $template);
    }
    
	function createFiles($subdomain = false){
    	$settings = Zend_Registry::getInstance()->settings;
    	 
    	     	 
    	$this->createPropertyThumbs(
    		array(
    			array(
    				'name' => $this->fotoThumbName, 
    				'width' => 237,    
    				'height' => 180,
    				'autosize' => false 
    			), 
    			array(
    				'name' => $this->fotoFullName, 
    				'width' => 400,  
    				'height' => 300,       
    				'autosize' => false 
    			) 
    		),  
    		'photo'   
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
