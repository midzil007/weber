<?
/**
 * Třída reprezentuje základní typ obsahu - HTML soubor.
 * @see model_Content
 * @package content
 */
 
class content_ImageWithHtml extends content_HtmlFile {
    
	public $fotoFullName = 'rFull';
    public $fotoThumbName = 'rThumb';
    
    public function __construct()
    {
    	parent::__construct();
    	$this->_name =  get_class($this);  
    	$this->containsFiles = true;
    	$this->nodeTitle = 'title';    	
    	$this->userName = 'Obrázek s popisem';
    	$this->properties[] = new ContentProperty('file','FileSelect','', array(),array(), array(), true);              	
    	
        $this->initProperies();
    }
	      
    function createFiles(){
       	$autosize = false;
    	$this->createPropertyThumbs(
    		array(
    			array(
    				'name' => $this->fotoFullName, 
    				'width' => 700,
    				'height' => 500,
    				'autosize' => $autosize
    			),
    			array(
    				'name' => $this->fotoThumbName, 
    				'width' => 300,
    				'autosize' => true
    			)
    		), 
    		'file'
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
