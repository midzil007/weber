<?php

class module_SaveExtermalFiles
{
	const GA = 'http://www.google-analytics.com/analytics.js';
	const FB = 'https://connect.facebook.net/cs_CZ/all.js#xfbml=1&amp;appId=175907572462977';
	
	function __construct($domain = ''){
		$this->config =  Zend_Registry::getInstance()->config; 
		$this->saveExt();
	}
    
	private function saveExt()
	{
		$file = $this->config->htdocsRoot . '/js/analytics.js'; 
		file_put_contents($file, Utils::loadUrl(self::GA, 30));
		$file = $this->config->htdocsRoot . '/js/allFB.js'; 
		file_put_contents($file, Utils::loadUrl(self::FB, 30));  
		
	}
}