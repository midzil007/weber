<?php

class Images
{    
	public function __construct()
    {
        $this->db = Zend_Registry::getInstance()->db;
        $this->tree = Zend_Registry::getInstance()->tree;
    }
    
    public function resizeTextImages($text){		    	
					 
		$htmlCallback = new callback_HtmlCallback($text, $this);	
		$text = preg_replace_callback('/(<img\s*\s*(.*?)\s*>\s*)/i', array($htmlCallback, 'images'), $text);					   
		 
		return $text;		 
	}
	  
	public static function makeClickable($text, $view){ 
		$htmlCallback = new callback_HtmlCallback($text, $view); 
		return preg_replace_callback('/(<img\s*\s*(.*?)\s*>\s*)/i', array($htmlCallback, 'wrapImapges'), $text);		
	} 
}