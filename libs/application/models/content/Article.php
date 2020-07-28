<?
/**
 * Třída reprezentuje Článek
 * @see model_Content
 * @package content
 */
 
class content_Article extends content_HtmlFile {
    
	public $fotoFullName = 'pFull';
	public $fotoShowName = 'pShow';
    public $fotoThumbName = 'pThumb';
    public $fotoMiniName3 = 'pMini';
    public $fotoCropMini3Name  = 'cThumb';  
    
    public function __construct($config = array())
    { 
    	parent::__construct();
    	$this->_name =  get_class($this);  
    	$this->userName = 'Článek';      	    	
    	// $this->properties[] = new ContentProperty('dateShow','TextDate',date('y/m/d'), array('required'=>'true', 'invalidMessage' => "Nesprávný formát", 'value'=>date('y/m/d')));       	       
   		
    	$this->properties[] = new ContentProperty('dateShow','TextDate','', array('required'=>'true', 'invalidMessage' => "Nesprávný formát", 'value'=>''), array(), array(), false); 
    	//$this->properties[] = new ContentProperty('tags', 'ItemsStack','', array(),array(), array(), true);       
    	    	  
    	$this->properties[] = new ContentProperty('photos','MultiFileSelect','', array(), array(), array('showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 60, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 3378 ));   
    	    
    	$this->properties[] = new ContentProperty('files','MultiFileSelect','', array(), array(), array('showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 10, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 3379 )); 
    }  
 	
    function show($view, $node){
    	$template = parent::getContentTemplate();	    	 
    	$view->content = $this;
    	$view->node = $node;    
    	$view->photos = $this->getFilesNames('photos'); 
    	   
    	 
    	$view->html = $this->getPropertyValue('html');    
    	$view->documents = $this->getFilesNames('files'); 
    	$view->date = $this->getPropertyValue('dateShow');      
    	    	
    	   
    	    
    	return $view->render(Zend_Registry::getInstance()->config->view->contentsDir . $template);
    }
    
    function getPretext($wrapInP = false, $more = '', $moreUrl = '', $length = 100){
    	$html = helper_FrontEnd::reduceText(strip_tags($this->getPropertyValue('html')), $length, false, true) ;        	 
    	if($more){
    		// $more =' <a href="' . $moreUrl . '" class="more">&nbsp;</a>';
    		$more =' <a href="' . $moreUrl . '" class="more">&raquo;</a>';   
    	} 
    	 
    	if($html){
    		return '<p>' . $html . $more . '</p>';
    	} else {
    		return '';  
    	}
    }
    
    function showAdmin($view){    	
    	parent::showAdminInit($view);
    	
  		 
    	$d = $this->getPropertyByName('dateShow')->value;
    	$this->getPropertyByName('dateShow')->value = $d?$d:date('Y-m-d');
    	    	 
    	
    	parent::renderAdmin($view);
    	
    }
         
    function createFiles($subdomain = false){
    	$settings = Zend_Registry::getInstance()->settings;
    	 
    	     	 
    	$this->createPropertyThumbs(
    		array(
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
    				'height' => 53,    
    				'autosize' => false 
    			),
    			array(
    				'name' => $this->fotoCropMini3Name, 
    				'crop_width' => 90,    
    				'crop_height' => 90,      
    				'autosize' => false
    			), 
    		), 
    		'photos'
    	);
    	 
    }
    
    function getRSSImages(){ 
    	$photos = $this->getFilesNames('photos'); 
    	$photos = helper_FrontEnd::getResizedPhotos($photos, $this->fotoMiniName3, $this->fotoFullName, 0, 3);   
    	return $photos;   
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
