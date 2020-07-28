<?
/**
 * Třída reprezentuje novinku
 * @see model_Content
 * @package content
 */
 
class content_Album extends content_HtmlFile {
    	
	public  $imageName = 'aPhoto';
	
    public function __construct($config = array())
    {
    	parent::__construct();
    	$this->userName = 'Album';       
    	$this->properties[] = new ContentProperty('cover1','FileSelect','');   
    	$this->properties[] = new ContentProperty('cover2','FileSelect','');   
    	$this->properties[] = new ContentProperty('songs','MultiFileSelect','', array(), array(), array('inputText' => 'Název'));    
    }      
    
    function createFiles(){
    
    	$this->createPropertyThumbs(
    		array(
    			array(
    				'name' => $this->imageName, 
    				'width' => 370,
    				'height' => 350,
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
