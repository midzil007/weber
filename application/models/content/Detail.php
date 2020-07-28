<?
/**
 * Třída reprezentuje Článek
 * @see model_Content
 * @package content
 */
 
class content_Detail extends Content {
   
	public $fotoFullName = 'dFull';
    public $fotoThumbName = 'dThumb';
                
    public function __construct($config = array())
    {
    	$this->_name =  get_class($this);  
    	$this->userName = 'Kuchyňský detail';   	  
    	$this->properties[] = new ContentProperty('photo','FileSelect','');
    	$this->properties[] = new ContentProperty('cz_html','Wysiwyg',''); 
    }
        
    function createFiles(){
    	    	
    	$this->createPropertyThumbs(
    		array(
    			array(
    				'name' => $this->fotoFullName, 
    				'width' => 500,
    				'autosize' => true
    			),
    			array(
    				'name' => $this->fotoThumbName, 
    				'width' => 200,
    				'autosize' => true
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
