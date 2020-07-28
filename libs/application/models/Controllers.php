<?php
class Controllers {    	
    function __construct(){
		$this->db =  Zend_Registry::getInstance()->db;
		
	}
    public function getControllers()
    {	
		$controllers = array();
		foreach ($this->db->fetchAll('SELECT name, fullname FROM Controllers') as $c){
			$controllers[$c['name']] = $c['fullname'];
    	}
		return $controllers;
    } 
} 