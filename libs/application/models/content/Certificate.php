<?
class content_Certificate extends Content {
	public $fotoFullName = 'pFull';
	public $cropThumbName = 'cThumb';
	
public function __construct($config = array())
    { 
    	$this->_name =  get_class($this);  
    	$this->userName = 'Certifikát';      	    	
    	$this->properties[] = new ContentProperty('html','Wysiwyg','', array(),array(), array(), true); 
   	   	//$this->properties[] = new ContentProperty('showin','CheckboxGroup','', array(), $reg->config->subdomains);
   	   	$this->properties[] = new ContentProperty('photo','MultiFileSelect','', array(), array(), array('showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 60, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 3573));   	   	 
    	//$this->properties[] = new ContentProperty('photo','FileSelect','', array(), array(), array('showSelectFile' => true, 'inputWidth' => '250', 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 3573 )); 
    	$this->initProperies();       
    }
	 
	 function show($view, $node){
       	$template = parent::getContentTemplate();	 
     	$view->content = $this;
    	$view->node = $node;     
    	$view->photo = $this->getFilesNames('photo');
    	$view->html = $this->getPropertyValue('html');
    	//$view->showPhoto = helper_FrontEnd::showPhoto($this->getPropertyValue('photo'), 'thumbFoto');        	    	  	
    	return $view->render(Zend_Registry::getInstance()->config->view->contentsDir . $template);
    }
	 	 
 	function createFiles(){	    	
    	$this->createPropertyThumbs(
    		array(
    			array(
    				'name' => $this->cropThumbName, 
    				'width' => 105,   
    				'height' => 150,     
    				'autosize' => false
    			), 
    			array(
    				'name' => $this->fotoFullName, 
    				'width' => 750,  
    				'height' => 600,       
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
