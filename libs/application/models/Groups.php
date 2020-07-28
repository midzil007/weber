<?php

class Groups  
{     
    protected $_name = 'Groups'; 
     
    public function getGroups()
    {	
    	$this->db =  Zend_Registry::getInstance()->db;
    	
    	$g = array();
    	foreach($this->db->fetchAll("SELECT groupname, fullname FROM $this->_name  ") as $row){
    		$g[$row['groupname']] = $row['fullname'];    	  
    	} 
    	return  $g; 
    }
}