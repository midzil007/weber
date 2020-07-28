<?php

class module_Advertising_AdvertStats
{
	public $_tableName = 'module_Advertising_Stats';
	
	function __construct(){
		$this->db =  Zend_Registry::getInstance()->db;
        $this->session = Zend_Registry::getInstance()->session;
	}
	
	function bannerAction($ident, $increase){
		$id = $this->db->fetchOne("SELECT id from `" . $this->_tableName."` WHERE advertIdent=:ident", array('ident' => $ident));
		if(!$id){
			$this->db->insert(
				$this->_tableName,
				array(
					'advertIdent' => $ident
				)
			);
			$id = $this->db->lastInsertId();
		}
		
		$where = $this->db->quoteInto('id = ?', $id);
		$this->db->update(
			$this->_tableName,
			array( $increase => new Zend_Db_Expr($increase . ' + 1')),
			$where
		);		
	}
	
	function getBannerStats($ident){
		return $this->db->fetchRow("SELECT shown, clicked from `" . $this->_tableName."` WHERE advertIdent=:ident", array('ident' => $ident));
	}
}