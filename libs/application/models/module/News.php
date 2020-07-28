<?php

class module_News
{   
	function __construct($domain = ''){
		$this->db =  Zend_Registry::getInstance()->db;
		$this->tableName = 'module_NewsOnEmail';
	}
	
	function isRegister($email)
	{
		return $this->db->fetchOne("select id from " . $this->tableName . "where email =?", array($email));	
	}
	
	function addReceiver($firstName, $lastName, $email)
	{
		$state = 0;
		if(!$this->isRegister($email)){
			$data = array(
			'firstname' => $firstName,
			'lastname' => $lastName,
			'email' =>$email,
			'date-added' =>  new Zend_Db_Expr('NOW()'));
			
			$this->db->insert(
			 $this->tableName,
			 $data
			);
		}		
	}
	
}
?>