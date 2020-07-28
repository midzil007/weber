<?
/**
	
  */
class module_HistoryBasket {
	
	public function __construct($view) {
		$this->db = Zend_Registry::getInstance ()->db2;   
		$this->_tableName = 'module_HistoryBasket';
		$this->_tableNameCount = 'module_HistoryBasketCount';
	}
	
	public function getCount()
	{
		return $this->db->fetchOne('select count from '.$this->_tableNameCount);
	}
	
	public function incBasket($countInc = false)
	{
		$count = $this->getCount();
		if($countInc > 0)
		{
			$data['count'] =   $countInc + $count;
		}
		else{
			$data['count'] =   $count+1;
		}
		$this->db->update($this->_tableNameCount, $data);
	}
	
	
}

?>