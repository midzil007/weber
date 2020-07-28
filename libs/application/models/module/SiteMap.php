<?php

class module_SiteMap
{    
    function __construct()
    {    
    	$this->tree =  Zend_Registry::getInstance()->tree;
        $this->db =  Zend_Registry::getInstance()->db;
    }
	    
    public function generate(){
    	return $this->tree->getNodeById(1)->getChildren('FOLDER', true);
    }       
    
}