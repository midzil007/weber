<?
/**
 * Třída reprezentuje novinku
 * @see model_Content
 * @package content
 */
 
class content_NewsWithPhoto extends content_News {
    
	public  $imageName = 'nPhoto';
	public  $imageNameB = 'nPhotoB';
	public  $imageNameC = 'nPhotoC';
	
    public function __construct($config = array())
    {
    	parent::__construct();
    	$this->userName = 'Novinka s možností připojení souboru';       
    	$this->properties[] = new ContentProperty('image','FileSelect','');   
    }  

	function show($view, $node){
    	$template = parent::getContentTemplate();	
    	$view->html = $this->getPropertyByName('html')->value;     
    	$view->date = Utils::formatDate($this->getPropertyByName('dateShow')->value);     
    	$view->nPhoto = helper_FrontEnd::showPhoto($this->getPropertyValue('image'), $this->imageNameC);  		 	
    	    	
    	$view->eventsModule = new module_Events();
    	$this->inputGet->y = $this->inputGet->y?$this->inputGet->y:date('Y');  
		$view->events = $view->eventsModule->getEvents( null, 'startAt', 'Asc', true );
		$view->eventsClose = $view->eventsModule->getEvents( 'future', 'startAt', 'Asc', true );
		 
    	return $view->render(Zend_Registry::getInstance()->config->view->contentsDir . $template);
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
    				'width' => 107,
    				'height' => 80,
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
    		'image'   
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
