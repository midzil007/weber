<?
/**
 * Třída reprezentuje základní typ obsahu - HTML soubor.
 * @see model_Content
 * @package content
 */
 
class content_HtmlFileWithFiles extends content_HtmlFile {
    
    public function __construct()
    {
    	parent::__construct();
    	$this->_name =  get_class($this);  
    	$this->containsFiles = true;
    	$this->nodeTitle = 'title';    	
    	$this->userName = 'Článek s možností připojení souborů (výzva)';
    	$this->properties[] = new ContentProperty('files','MultiFileSelect','', array(),array(), array(), true);              	
    	
        $this->initProperies();
    }
	    
    function show($view, $node){
    	$template = parent::getContentTemplate();    	
    	
    	$view->content = $this; 
    	$view->node = $node;    	    	
    	
    	$view->html = $this->getPropertyByName('html')->value;     	 
    	//$view->docFiles = $this->getFilesNames();   	
    	
    	
    	return $view->render(Zend_Registry::getInstance()->config->view->contentsDir . $template);
    }
    
    function renderAdmin($view){
    	parent::renderAdmin($view);
    }
        
    function showAdmin($view){
    	parent::showAdminInit($view);
    	parent::renderAdmin($view);
    	
    }
}
?>
