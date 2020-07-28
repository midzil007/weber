<?
  /**
	uzivatelsky editovatelne priplatky
	@author Jakub Kratena
  */
class module_Priplatky{ 
	
	private $_tableName = 'module_Priplatky';

	
	public function __construct() {
		$this->db =  Zend_Registry::getInstance()->db; 
	} 
	
	public function getPriplatky()
	{
		return $this->db->fetchAll("select * from `" . $this->_tableName ."`"); 
	}
	
	public function getPriplatekById($id)
	{
		return $this->db->fetchRow( 'select * from `'. $this->_tableName .'` where id = :id', array('id' => $id) );
	}
	
	/**
		zkontroluje vstup a ulozi / updatuje dopravu
		@return nic, nebo chybovou hlášku
	*/
	public function savePriplatek( $data )
	{
		$test = $this->checkInput($data);
		if($test)
			return $test;
		$c_data = array(
							'nazev' => $data->nazev,
							'cena' => $data->cena,
							);
				
		if(!$data->id)
			$this->db->insert( $this->_tableName , $c_data);
		else{
			$where = $this->db->quoteInto('id= ?', $data->id);
			$this->db->update($this->_tableName, $c_data, $where);
		}
		
	}
	
	// dodelat
	private function checkInput( $data )
	{
		if($data->od < 0)
			return 'pouze nezáporná čísla';
	}
	
		public function deleteById($id){
		$this->db->delete($this->_tableName, 'id = '.$id);
	}
	
	
	 
}	
?>
