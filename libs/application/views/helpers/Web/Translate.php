<?php
/**
 * CMS
 * *
 */

class Web_View_Helper_Translate
{   
	public $view;
	
	public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
    
    public function Translate($key, $lang = '')
    {     	
    	if($lang){
    		
    	} else {
    		$lang = $this->view->languages->language?$this->view->languages->language:'cz';    
    	} 
    	$t = $this->view->FETranslation[$key][$lang];
    	return $t?$t:$this->view->FETranslation[$key][$this->view->languages->defaultLanguage];
    }
}
