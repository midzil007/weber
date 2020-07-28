<?
/**
 * Třída reprezentuje UserProfile
 * @see model_Content
 * @package content
 */
 
class content_UserProfile extends content_HtmlFile {  
       
    public function __construct($config = array())
    {
    	$this->allowablePages = Content::getContentTypesKeys();
    	parent::__construct();
    	$this->userName = 'Uživatelský profil';    
    	$this->properties = array();
    	$this->properties[] = new ContentProperty('html','Wysiwyg','');         
    	
    }
    
    function show($view, $node){    	
    	$template = parent::getContentTemplate();
    	$view->html = $this->getPropertyValue('html');
    	  
    	if($view->webUserLogged){ 
    	}  
    	
    	if(isset($view->inputGet->confirm)){
    		if($view->inputGet->x){ // firma
    			list($state, $messages) = $view->jobs->companies->confirmEmail($view->inputGet->confirm, $view->inputGet->x);
    		} else {
    			list($state, $messages) = $view->jobs->webUsers->confirmEmail($view->inputGet->confirm, $view->inputGet->y);
    		}
			
			Helpers::addMessage($state, $messages, $view);
		}
		     	 
    	if(isset($view->input->password) && isset($view->input->login)){
			list($state, $messages) =  $view->mCustomers->loginUser($view, $view->input->login, $view->input->password);
			Helpers::addMessage($state, $messages, $view); 
		}  
    	
    	$view->subTemplate = $view->config->view->contentsDir . $this->getName() . $view->type . ($view->webUserLogged?'Logged':'') . '.phtml';
	
    	if(isset($view->inputGet->logout)){			
			$view->mCustomers->logout();
    	}
    	return $view->render($view->config->view->contentsDir . $template);
    }
    
}
?>
