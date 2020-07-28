<?
/**
 * Třída reprezentuje Fotogalerii
 * @see model_Content
 * @package content
 */
 
class content_Photogallery extends content_HtmlFile {
    
	public $fotoFullName = 'fgFull';
    public $fotoThumbName = 'fgThumb';
    	
    public function __construct($config = array())
    {    	
    	parent::__construct();
    	$this->_name =  get_class($this);  
    	$this->userName = 'Fotogalerie';    	    		           
    	$this->properties[] = new ContentProperty('files','MultiFileSelect','', array(), array(), array('showSelectFile' => true, 'inputWidth' => '250', 'showUploadFile' => true, 'maxFiles' => 100 ));     
    }
    
    function show($view, $node){
    	$template = parent::getContentTemplate();
    	$view->content = $this; 
    	$view->node = $node;    	    	
    	$view->html = $this->getPropertyByName('html')->value;   
    	return $view->render(Zend_Registry::getInstance()->config->view->contentsDir . $template);
    }
    
    function showAdmin($view){
    	 
    	parent::showAdminInit($view);    	
    	parent::renderAdmin($view);
    	
    }
    
       
    function createFiles(){
    	$settings = Zend_Registry::getInstance()->settings;
    	$thumbWidth = $settings->getSettingValue('photogallery_thumb_width');
    	$fullWidth = $settings->getSettingValue('photogallery_full_width');
    	$autosize = $settings->getSettingValue('photogallery_resize_method');
    
    	$this->createPropertyThumbs(
    		array(
    			array(
    				'name' => $this->fotoFullName, 
    				'width' => $fullWidth,
    				'autosize' => true
    			),
    			array(
    				'name' => $this->fotoThumbName, 
    				'width' => $thumbWidth,
    				'autosize' => true
    			)
    		), 
    		'files'
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
