<?php
/**
 *
 * @author Jakub Kratena
 * 18.11.2012
 *
 * trida pro praci se znackami (vyrobci)
 */
class module_Eshop_Znacka
{
	private $db;
	private $_tableName = "module_eshop_Znacky";

    function __construct()
    {
        $this->db =  Zend_Registry::getInstance()->db;
    }

    /**
     * @return vsechny znacky z db
     */
    public function getZnacky()
    {
		$select = $this->db->select();
		$select->from($this->_tableName);
		$stmt = $this->db->query($select);
		$result = $stmt->fetchAll();
		return $result;
    }
}