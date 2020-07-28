<?php

class module_Advertising_PPC
{
	public $_tableName = 'module_Advertising_PPC';
	
	function __construct(){
		$this->db =  Zend_Registry::getInstance()->db;
        $this->session = Zend_Registry::getInstance()->session;
	}
	
	function addClient($name, $email, $url, $price, $from, $too, $domain = ''){		
		$this->db->insert(
			$this->_tableName,
			array(
				'clientEmail' => $email,
				'clientName' => $name,
				'clientUrl' => $url,
				'price' => $price,
				'from' => $from, 
				'too' => $too,
				'domain' => $domain
			)
		);
	
		$id = $this->db->lastInsertId();
		return $id;
	}
	
	function editClient($id, $name, $email, $url, $price, $from, $too, $domain = ''){		
		$this->db->update(
			$this->_tableName,
			array(
				'clientEmail' => $email,
				'clientName' => $name,
				'clientUrl' => $url,
				'price' => $price,
				'from' => $from, 
				'too' => $too,
				'domain' => $domain
			),
			$this->db->quoteInto(" id = ? ", $id)
		);
	   
		$id = $this->db->lastInsertId();
		return $id;
	}
	
	function getClientById($id){
		$select =  $this->db->select();
		$select->from($this->_tableName, array( 'id', 'clientEmail', 'clientName', 'clientUrl', 'price', 'from', 'too', 'domain'));    
				
		$select->where('`id` = ?', $id); 
				 
		// e($select->__toString()); 
		$client = $this->db->fetchRow($select);
		 
		return $client; 
	}
	
	function getClients($active = true, $domain = 0, $sort = 'clientName', $sortType = 'ASC'){ 
		$select =  $this->db->select();
		$select->from($this->_tableName, array( 'id', 'clientEmail', 'clientName', 'clientUrl', 'price', 'from', 'too', 'domain'));    
			
		if($active){ 
			$select->where('`from` <= ?', new Zend_Db_Expr('NOW()'));
			$select->where('`too` >= ?', new Zend_Db_Expr('NOW()')); 
		}
		 
		if($domain){
			$select->where('`domain` = ?', $domain);
		} 
		
		$sortType = $sortType?$sortType:'ASC';
		$select->order($sort . ' ' . $sortType);
		
		// e($select->__toString());  
		$clients = $this->db->fetchAll($select);
		
		return $clients; 
	}
	
	function getClientsFE($active = true, $domain = 0){		
		$clients = $this->getClients($active, $domain);
		$nc = array();
		foreach ($clients as $client){
			$nc[$client['clientName']] = array(
				'url' => $client['clientUrl'],
				'price' => $client['price']
			); 
		}
					
		return $nc;  
	}
	
	
	function getBannerStats($ident){
		return $this->db->fetchRow("SELECT shown, clicked from `" . $this->_tableName."` WHERE advertIdent=:ident", array('ident' => $ident));
	}
}