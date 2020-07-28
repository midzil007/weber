<?
/**
 * Třída reprezentuje Článek
 * @see model_Content
 * @package content
 */
 
class content_Priprava extends content_HtmlFile {
    
	public $fotoFullName = 'pFull';
	public $fotoShowName = 'pShow';
    public $fotoThumbName = 'pThumb';
    public $fotoMiniName = 'pMini';
             
    public function __construct($config = array())
    {
    	parent::__construct();
    	$this->_name =  get_class($this);   
    	$this->userName = 'Příprava';    	    	
    	
    	
        $this->properties[] = new ContentProperty('pretext','Wysiwyg','', array(),array(), array(), true);        
        
    	$this->properties[] = new ContentProperty('photo','FileSelect','', array(), array(), array('showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 30, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 3376 ));
    	$this->properties[] = new ContentProperty('objem','Text'); 
    	$this->properties[] = new ContentProperty('hmotnost','Text'); 
    	$this->properties[] = new ContentProperty('hrubost','Text'); 
    	$this->properties[] = new ContentProperty('teplota','Text'); 
    	$this->properties[] = new ContentProperty('extrakce','Text'); 
    }
    
   
    function show($view, $node){
    	$template = 'Project.phtml';	
    	$view->photo = $this->getFilesNames('photo'); 
    	$view->node = $node;    	
    	
    	$view->html = $this->getPropertyByName('html')->value;  
    	    	
    	return $view->render(Zend_Registry::getInstance()->config->view->contentsDir . $template);
    }
    
   function createFiles(){
    	    	
    	$this->createPropertyThumbs(
    		array(
    			 array(
    				'name' => $this->fotoMiniName, 
    				'width' => 120,
    				'height' => 500,
    				'autosize' => false
    			),
    			array(
    				'name' => $this->fotoThumbName, 
    				'width' => 240,
    				'autosize' => false  
    			),
    			array(
    				'name' => $this->fotoShowName, 
    				'width' => 800,
    				'height' => 550,
    				'autosize' => false
    			),
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
