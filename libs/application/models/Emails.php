<?php

class Emails
{    
	public function __construct()
    {
        $this->_tableName = 'Mails';  
    }
    
    public function getEmails($sent = true, $filtr = null,  $sort = 'sender', $sortType = 'Desc')
    {
    	$select = Zend_Registry::getInstance()->db->select();
		$select->from($this->_tableName, array( 'id', 'head', 'sender', 'recipients', 'sendAt'));

		
    	
		if($sent){				
			$value = new Zend_Db_Expr(0);
			$coll = '`sendAt` > ?';
		} else {
			$value = new Zend_Db_Expr(0);
			$coll = '`sendAt` = ?';
		}
		
		$select->where($coll, $value);
				
		//return $this->fetchAll($where)->toArray();
		$sortType = $sortType?$sortType:'Asc';
		$select->order($sort . ' ' . $sortType);
		//e($select->__toString());	
		return Zend_Registry::getInstance()->db->fetchAll($select);
    }
    
    public function getEmail($mid)
    {
    	$select = Zend_Registry::getInstance()->db->select();
		$select->from($this->_tableName, array( 'id', 'head', 'sender', 'recipients', 'text', 'sendAt'));	
		$select->where('id = ?', $mid);
		//e($select->__toString());
		return Zend_Registry::getInstance()->db->fetchRow($select);
    }
    
    public function deleteEmail($mid)    
    {	
    	Zend_Registry::getInstance()->db->delete(			
			$this->_tableName,
			Zend_Registry::getInstance()->db->quoteInto(" id= ? ", $mid)
		);
		
		//return $this->adapter->fetchAll($select);
		
    }
	    
}