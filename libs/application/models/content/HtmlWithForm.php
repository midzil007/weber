<?
/**
 * Třída reprezentuje Článek
 * @see model_Content
 * @package content
 */
 
class content_HtmlWithForm extends content_HtmlFile {
    
    public function __construct($config = array())
    {
    	parent::__construct();
    	$this->userName = 'Html stránka s kontaktním formulářem';    	     	       
    }
    
    function show($view, $node){
    	$template = parent::getContentTemplate();	
    	    	
    	$view->html = $this->getPropertyByName('html')->value;   
    	    	
		if(isset($view->input->send)){
			$webForm = new module_WebForm($view->input);	
			
			$text = '
				<h2 style="color:#0f4096; font-size: 12px;">Dotaz z webu</h2>
				<table style="font-size: 11px; color: #708490;">
					<tr><td>Jméno:</td><td>' . $webForm->fullname . '</td></tr>
					<tr><td>Email:</td><td>' . $webForm->email . '</td></tr>
					<tr><td>Telefon:</td><td>' . $webForm->input->phone . '</td></tr>
					<tr><td>Zpráva:</td><td>' . nl2br($webForm->input->message) . '</td></tr>
				</table>				
				<p>
			';
			
			
			list($state, $messages) = $webForm->sendEmail($text);
			Helpers::addMessage($state, $messages, $view);
		}
		
		$view->formSubject = 'WWW: ' . $view->curentNode->title;
		

    	return $view->render(Zend_Registry::getInstance()->config->view->contentsDir . $template);
    }
    
}


?>
