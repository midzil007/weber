<?
/**
 * Třída reprezentuje typ obsahu - Pouze popis stránky
 * @see model_Content
 * @package content
 */
 
class content_Overview extends Content {
    
    public function __construct($initProperties = true)
    {
    	$this->allowablePages = Content::getContentTypesKeys();
    	
    	$this->_name =  get_class($this);  
    	$this->userName = 'Pouze popis stránky';
        $this->searchableCols = array('html');
        
    	$this->properties[] = new ContentProperty('pathToTemplate','Hidden','Simple');          
        $this->properties[] = new ContentProperty('html','Wysiwyg','', array(),array(), array(), true);            
        
    	// $this->properties[] = new ContentProperty('tags', 'ItemsStack','', array(),array(), array(), true);  
    	
        if($initProperties)  {
        	$this->initProperies(); 
        }
    } 
    
    function show($view, $node){ 
    	
		$view->txtPage = true;     	
    	$template = $this->getPropertyByName('pathToTemplate')->value . '.phtml';
    	$view->content = $this; 
    	$view->node = $node;   
    	$view->pageText = $view->content->getPropertyValue('html');
    	$view->children = helper_FrontEnd::checkChildren($node->getChildren(), 0);
    // 	$view->obsahClass = 'txtContent';  
    	return $view->render(Zend_Registry::getInstance()->config->view->overviewsDir . $template);
    }
    
	function showAdmin($view){    	
    	parent::showAdminInit($view);
    	$this->initOptions($view);    	    	 
    	parent::renderAdmin($view);
    	
    }
        
    function initOptions($view){    	
    	$reg = Zend_Registry::getInstance();
    	$tags = new module_Tags();   	 
    	$this->getPropertyByName('tags')->options = $tags->getUsedTagsSelect();    
    }
    
	function afterUpdate(){
		if($this->id == '10679')
		{  
			$PDF = new module_RenderPDF();          	  
			$PDF->generateAndSave(false,$this->getPropertyValue('html'),'obchodni_podminky.cz');  
		} 
		     
    	$mTags = new module_Tags();  
    	$mTags->buildTagsList(array_keys(Zend_Registry::getInstance()->config->hasTags->toArray()));   
    }
    
    function renderAdmin($view){
    	parent::renderAdmin($view);
    }
    
    	
}
?>
