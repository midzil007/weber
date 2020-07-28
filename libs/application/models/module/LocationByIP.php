<?php

class module_LocationByIP 
{   
	public $_tableName = 'module_LocationByIP';
	
	function __construct(){
		$this->db =  Zend_Registry::getInstance()->db;
	}  
	
	function setMyLocation($location){		
		$userIP = Utils::getIP(); 
		$activeUntil  = mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"));
		$activeUntil  = date('Y-m-d', $activeUntil);
		$data = array(
			'IP' => $userIP,
			'location' => $requestData->domainName,
			'activeUntil' => date('Y-m-d', $activeUntil)
		); 

		$this->db->query(
			'REPLACE INTO ' . $this->_tableName . " VALUES ('$userIP', ?, '$activeUntil')", 
			$location 		 
		);      
	}
	
	function getMyLocation(){
		return $this->db->fetchOne(  
			'SELECT location FROM ' . $this->_tableName . " WHERE IP = ?", 
			Utils::getIP() 		 
		);  
	}
} 