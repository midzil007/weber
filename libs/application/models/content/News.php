<?
/**
 * Třída reprezentuje novinku
 * @see model_Content
 * @package content
 */
 
class content_News extends content_HtmlFile {
    
    public function __construct($config = array())
    {
    	parent::__construct();
    	$this->_name =  get_class($this);  
    	$this->userName = 'Novinka';    	    	
    	$this->properties[] = new ContentProperty('dateShow','TextDate',date('y/m/d'), array('required'=>'true', 'invalidMessage' => "Nesprávný formát", 'value'=>date('y/m/d')));       	       
    }
    
    function show($view, $node){
    	$template = parent::getContentTemplate();	
    	$view->html = $this->getPropertyByName('html')->value;     
    	$view->date = Utils::formatDate($this->getPropertyByName('dateShow')->value);    
    	$view->nPhoto = helper_FrontEnd::showPhoto($this->getPropertyValue('image'), $this->imageName);  		 	
    	    	
    	return $view->render(Zend_Registry::getInstance()->config->view->contentsDir . $template);
    }
    
    function showAdmin($view){
    	parent::showAdminInit($view);
    	$d = $this->getPropertyByName('dateShow')->value;
    	$this->getPropertyByName('dateShow')->value = $d?$d:date('Y-m-d');     	 
    	parent::renderAdmin($view);
    	
    }
}
?>
