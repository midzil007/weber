<?
class content_Video extends content_HtmlFile{
	
	public $fotoFullName = 'pFull';
	public $fotoShowName = 'pShow';
    public $fotoThumbName = 'pThumb';
    public $fotoMiniName3 = 'pMini';
    public $fotoCropMini3Name  = 'cThumb';  
    public $imageConf = array();
      
    
    public function __construct($config = array())
    { 
    	parent::__construct();
    	$this->_name =  get_class($this);       
    	$this->userName = 'Video'; 
		
		$this->imageConf = array(
   			'photos' => array(
    			array(
    				'name' => $this->fotoThumbName, 
    				'width' => 188, 
    				'height' => 142,
    				'autosize' => false
    			), 
    			array(
    				'name' => $this->fotoShowName, 
    				'width' => 420,
    				'height' => 315,      
    				'autosize' => false
    			), 
    			array(
    				'name' => $this->fotoFullName, 
    				'width' => 760,  
    				'height' => 550,  
    				'autosize' => false 
    			), 
    			array(
    				'name' => $this->fotoMiniName3, 
    				'width' => 500,     
    				'height' => 80,    
    				'autosize' => false 
    			),
    			array(
    				'name' => $this->fotoCropMini3Name, 
    				'crop_width' => 90,    
    				'crop_height' => 90,      
    				'autosize' => false
    			), 
    		)
    	);
		       	    	
    	// $this->properties[] = new ContentProperty('dateShow','TextDate',date('y/m/d'), array('required'=>'true', 'invalidMessage' => "Nesprávný formát", 'value'=>date('y/m/d')));       	       
   		
    	$this->properties[] = new ContentProperty('videoUrl','text','');
    	$this->properties[] = new ContentProperty('videoUrlImg','hidden','');     
		$this->properties[] = new ContentProperty('photos','MultiFileSelect','', array(), array(), array('showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 60, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 3378 )); 
    	//$this->properties[] = new ContentProperty('tags', 'ItemsStack','', array(),array(), array(), true);       
    }
    
     function show($view, $node){
     	$template = parent::getContentTemplate();	 
     	$view->content = $this;
		$view->mVideos = new module_Videos();     
    	$view->node = $node;    
    	$view->html = $this->getPropertyValue('html');   
    	$view->videoUrlImg = $this->getPropertyValue('videoUrlImg'); 
    	return $view->render(Zend_Registry::getInstance()->config->view->contentsDir . $template); 
     }
 	
 	function createFiles($subdomain = false){
    	$settings = Zend_Registry::getInstance()->settings;
    	  
    	$this->createPropertyThumbs(
    		$this->imageConf['photos'],
    		'photos'
    	);
    	 
    }  
 	
 	
    function onSave(){
    	$this->createFiles();	
    	$AE = new AutoEmbed();  
     	$AE->parseUrl($this->insertData['videoUrl']);  
     	$this->insertData['videoUrlImg'] =$AE->getImageURL(); 
    }
 	function onUpdate(){
 		$this->createFiles();	  
    	$AE = new AutoEmbed();  
     	$AE->parseUrl($this->updateData['videoUrl']);  
     	$this->updateData['videoUrlImg'] =$AE->getImageURL(); 
    }
}  
?>