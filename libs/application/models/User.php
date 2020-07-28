<?

/**
 * Třída reprentuje zalogovaneho uzivatele
 */
class User{
		
	public static $sex = array(	
		'm' => 'Muž',
		'f' => 'Žena'
	);
	
	public function __construct($username = null, $id = false) {		
		$this->_tableName = $this->_tableName?$this->_tableName:'Users';
		if($username || $id){
			$this->initValues($username, $id);
		}
	}
	
	function initValues($username, $id = false){
		$db =  Zend_Registry::getInstance()->db;
		$select =  $db->select();
		$select->from($this->_tableName, array( '*'));    
				
		if($id){
			$select->where('id = ?', $id);
		} else {
			$select->where('username = ?', $username);
		}
		//		pr($select->__toString()); 
		$r = $db->fetchRow($select);		
		if(count($r) && is_array($r)){
			foreach ($r as $var => $value){     		
	    		$this->{$var} = $value;
	    	}
		}
	}
	

	public function __toString() {
		return $this->fullname;
	}

}
?>