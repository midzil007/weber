<?
/**
 * Třída reprezentuje Auto
 * @see model_Content
 * @package content
 */
 
class content_Reference extends Content {
    
	public $fotoFullName = 'pFull';
	public $fotoShowName = 'pShow';
    public $fotoThumbName = 'pThumb';
    public $fotoMiniName = 'pMini';
            
    public function __construct($config = array())
    {
    	$this->_name =  get_class($this);  
    	$this->userName = 'Reference';  
    	
    	$reg = Zend_Registry::getInstance();
    	   	
    	$this->properties[] = new ContentProperty('popis','Wysiwyg');   	   	 
    	$this->properties[] = new ContentProperty('url','Text');   	   	 
   	   	//$this->properties[] = new ContentProperty('showin','CheckboxGroup','', array(), $reg->config->subdomains);   	   	 
    	$this->properties[] = new ContentProperty('showin','MultiPageSelect', '', array(), array('root' => 0));  
    	$this->properties[] = new ContentProperty('dateShow','TextDate');
    	$this->properties[] = new ContentProperty('obrazky','MultiFileSelect','', array(), array(), array('showSelectFile' => true, 'inputWidth' => '250', 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => $reg->config->instance->referenceFilesNodeId ));  	    	
    	    	
    }
    
   	function showAdmin($view){
    	parent::showAdminInit($view);
    	
    	$d = $this->getPropertyByName('dateShow')->value;
    	$this->getPropertyByName('dateShow')->value = $d?$d:date('Y-m-d');
    	parent::renderAdmin($view);
    	
    }
    
    function show($view, $node){
    	$template = parent::getContentTemplate();	
    	$view->content = $this;
    	$view->node = $node;   
    	$view->obrazky = $this->getFilesNames('obrazky');
    	$view->html = $this->getPropertyValue('popis');   	
    	$view->back = true;    	    	  	
    	return $view->render(Zend_Registry::getInstance()->config->view->contentsDir . $template);
    }
    
   function createFiles(){
    	    	
    	$this->createPropertyThumbs(
    		array(
    			array(
    				'name' => $this->fotoThumbName, 
    				'width' => 148,
    				'height' => 92,
    				'autosize' => false
    			),
    			array(
    				'name' => $this->fotoShowName, 
    				'width' => 350,
    				'height' => 300,
    				'autosize' => false
    			),
    			array(
    				'name' => $this->fotoFullName, 
    				'width' => 750,
    				'height' => 500,
    				'autosize' => false
    			),
    			array(
    				'name' => $this->fotoMiniName, 
    				'width' => 70,
    				'height' => 52,
    				'autosize' => $autosize
    			)
    		), 
    		'obrazky'
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
