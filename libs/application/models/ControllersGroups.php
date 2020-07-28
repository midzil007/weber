<?php

class ControllersGroups
{     
    protected $_name = 'ControllersGroups'; 
    public function getGroupAccessibleControllers($groupname)
    {	
    	$this->db =  Zend_Registry::getInstance()->db;  
		return $this->db->fetchAll("SELECT * FROM $this->_name WHERE `group` = ?", $groupname);
    }     
} 