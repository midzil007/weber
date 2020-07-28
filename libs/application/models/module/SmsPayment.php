<?php

class module_SmsPayment
{   
	function __construct(){
		$this->db =  Zend_Registry::getInstance()->db;   
		$this->_tableName = 'module_SmsPayment';   
	}   
    	
	function addCode($username = ''){		
		$code = $this->db->fetchOne("SELECT max(id) + 1 from `" . $this->_tableName . "`");
		$code = $code?$code:1000;
		$data = array(
			'username' => $username,
			'code' => $code
		);
		$this->db->insert($this->_tableName, $data);
		return $code;
	}
	
	function isCodeValid($code, $username = ''){
		return $this->db->fetchOne("SELECT id from `" . $this->_tableName . "` WHERE code = :code AND username = :username AND used = '0'", array('code' => $code, 'username' => $username));		
	}
		
    public function setCodeAsUsed($code){	 		
		$where = $this->db->quoteInto("code = ?", $code);
		$this->db->update(
			$this->_tableName,
			array(
				'used' => '1'
			),
			$where
		);
    }
    
}