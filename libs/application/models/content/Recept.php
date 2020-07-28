<?
/**
 * Třída reprezentuje základní typ obsahu - HTML soubor.
 * @see model_Content
 * @package content
 */
 
class content_Recept extends Content {
     
	public  $imageName = 'nPhoto';
	public  $imageNameB = 'nPhotoB';
	public  $imageNameC = 'nPhotoC';
	
	
    public function __construct($config = array()) 
    {
    	 
    	$this->_name =  get_class($this);    
    	$this->userName = 'Recept / tip';
        $this->properties[] = new ContentProperty('html','Wysiwyg','', array(),array(), array(), true);           
        $this->searchableCols = array('html');		
          
        $this->properties[] = new ContentProperty('photo','FileSelect','', array(), array(), array('showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 30, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 3376 ));
    	
        $this->initProperies();
        
    }
    
    function show($view, $node){
    	$template = parent::getContentTemplate();   
    	$view->content = $this; 
    	$view->node = $node;    
    	   	 
    	$view->nPhoto = helper_FrontEnd::showPhoto($this->getPropertyValue('photo'), $this->imageNameC);  		
    	 
    	$view->html = $this->getPropertyByName('html')->value;     	 	
    	return $view->render(Zend_Registry::getInstance()->config->view->contentsDir . $template);
    }
    
    function renderAdmin($view){
    	parent::renderAdmin($view);
    }
    
    function showAdmin($view, $callRender = false){    	
    	parent::showAdminInit($view);
    	parent::renderAdmin($view);  
    }
    
	function createFiles(){
    			
    	$settings = Zend_Registry::getInstance()->settings;
    	
    	/*
    	$autosize = $settings->getSettingValue('reality_resize_method');    	
    	$miniWidth = $settings->getSettingValue('reality_photos_mini_width');
    	*/
    	    	
    	$this->createPropertyThumbs(
    		array(
    			array(
    				'name' => $this->imageName, 
    				'width' => 150,  
    				'height' => 400,
    				'autosize' => true
    			),
    			array(
    				'name' => $this->imageNameB, 
    				'width' => 700,
    				'height' => 900,
    				'autosize' => false
    			),
    			array(
    				'name' => $this->imageNameC, 
    				'width' => 300, 
    				'height' => 900,
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
}
?>
