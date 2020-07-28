<?
/**
 * Třída reprezentuje novinku
 * @see model_Content
 * @package content
 */
 
class content_NewsWithPhoto extends content_News {
    
	public  $imageName = 'nPhoto';
	
    public function __construct($config = array())
    {
    	parent::__construct();
    	$this->userName = 'Novinka s možností připojení souboru';       
    	$this->properties[] = new ContentProperty('image','FileSelect','');   
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
