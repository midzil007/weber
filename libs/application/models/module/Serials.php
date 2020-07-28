<?

class module_Serials
{		
	public $serials = false;
	public $subdomain = false;
	
	function __construct($subdomain = false) {
		$this->db = Zend_Registry::getInstance()->db;
		$this->_tableName = 'module_Serials';
		$this->subdomain = $subdomain; 
	} 

	function getSerials($allDomains = false){
		$this->serials = array();
		
		if($allDomains || !$this->subdomain){
			$w = 1;
		} else {
			$w = "`domain` = '$this->subdomain'";    
		}
		
		$all =  $this->db->fetchAll("SELECT id, title, domain FROM " . $this->_tableName . " WHERE $w");
		foreach ($all as $serial){
			$this->serials[$serial['id']][$serial['title']] = $serial; 
		} 
		return $this->serials;
	}  
	
	function getSerial($id){		
		return $this->db->fetchRow("SELECT id, title FROM " . $this->_tableName . " WHERE id = ?", $id); 
	}  
	  
    function serialExists($title, $serials = false){
    	if(!$serials && $this->serials === false){
    		$serials = $this->getSerials();
    	}
    	return $serials[$this->subdomain][$title]['id']; 
    } 
    
    function addSerial($title){
    	$parts = explode(' ', $title);
    	$num = array_shift($parts);
    	$pomlcka = array_shift($parts);
    	 
    	if(is_numeric($num) && $pomlcka == '-'){ // editace
    		$title = implode(' ', $parts);
    		$this->updateSerial($num, $title); 
    		return $num;
    	} else {
    		$id = $this->serialExists($title);
	    	if(!$id){
	    		$this->db->insert(
					$this->_tableName,
					array('title' => $title, 'domain' => $this->subdomain) 
				);
				return $this->db->lastInsertId($this->_tableName);
	    	}
	    	return $id;    
    	}
    	 	
    } 
    
    function updateSerial($id, $title){
    	$where = $this->db->quoteInto('id = ?', $id);
		$this->db->update(
			$this->_tableName,
			array( 'title' => $title),
			$where
		);
		return $id;
    }
    
    function getSerialsSelect($selected = 0){ 
    	if($this->serials === false){
    		$this->serials = $this->getSerials();
    	}
    	 
    	$select = array();
    	$select[] = ''; 
    	$selectedText = '';
    	if($this->subdomain){
	    	if(count($this->serials[$this->subdomain])){
	    		foreach ($this->serials[$this->subdomain] as $serial){  
		    		$v = $serial['id'] . ' - ' . $serial['title'];
		    		$select[$v] = $v;
		    		if($selected == $serial['id']){
		    			$selectedText = $v;
		    		}
		    	}   
	    	}
    	} else {
    		if(count($this->serials)){
	    		foreach ($this->serials as $serial){   
	    			$serial = current($serial); 
		    		$v = $serial['id'] . ' - ' . $serial['title']; 
		    		$select[$v] = $v;
		    		if($selected == $serial['id']){
		    			$selectedText = $v;
		    		}
		    	}   
	    	} 
    	}
    	return array($select, $selectedText);  
    }
}

?>