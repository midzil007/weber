<?
class module_Customer{
	
	public $alkoholOptions = array(
		1 => 'vůbec ne',
		'příležitostně',
		'hodně'
	);
	
	public $cigaretyOptions = array(
		1 => 'vůbec ne',
		'příležitostně',
		'hodně'
	);
	
	
	public $langOptions = array(
		1 => 'Čeština',
		'English'
	);
	 
	public $pohlaviOptions2 = array(
		1 => 'muže',
		'ženu'
	);
	
	public function __construct($username, $data = false, $id = 0, $email = '') {
		$this->_tableName = 'module_Customer';
		$this->_tableNameFavourites = 'module_CustomerFavourites'; 
        $this->_tableNameInvitation = 'module_CustomerInvitation'; 
        if(!$data){
        	if($username){
	        	$data = Zend_Registry::getInstance()->db->fetchRow(
		    		'SELECT * FROM `' . $this->_tableName . '` WHERE login = ?', 
		    		array($username)   
		    	);
        	} elseif ($id){
        		$data = Zend_Registry::getInstance()->db->fetchRow(
		    		'SELECT * FROM `' . $this->_tableName . '` WHERE id = ?', 
		    		array($id)   
		    	);   
        	} elseif ($email){
        		$data = Zend_Registry::getInstance()->db->fetchRow(
		    		'SELECT * FROM `' . $this->_tableName . '` WHERE email = ?', 
		    		array($email)   
		    	);  
        	}
        } 
        Helpers::setValuesToObject($this, $data);   
	}
	
	function db(){
		return Zend_Registry::getInstance()->db;
	}
	
	
	public function setMailing($email){
		$db = Zend_Registry::getInstance()->db;
		$isExist = $db->fetchOne("select id from MailingList where email =?",$email);
		if(!$isExist)
			{
				$data['email'] = $email;
				$data['source'] = 'registrovaní z eshopu';
				$db->insert('MailingList',$data);
			}
	}
	   
	
	function isFavourite($rId){ 
		return $this->db()->fetchOne("SELECT cId FROM `" . $this->_tableNameFavourites . "` WHERE cId = " . $this->id . " AND rId = ?", $rId); 
	} 
	 
	function getFavourites(){
		return $this->db()->fetchAll("SELECT rId FROM `" . $this->_tableNameFavourites . "` WHERE cId = " . $this->id . " ");  
	}
	
	function setFavourite($rId){
		 $this->db()->query("INSERT INTO " . $this->_tableNameFavourites . "(cId, rId) VALUES ( $this->id, $rId)"); 
	}
	
	function removeFavourite($rId){ 
		$this->db()->query("DELETE FROM " . $this->_tableNameFavourites . " WHERE cId = " . $this->id . " AND rId = ?", $rId); 
	}
			
	
	function getPoints($mPoints){
		return $mPoints->getCount($this->username); 
	}
	
	function changeStatus($status = 0){
		$mCustomers = new module_Customers();
		$mCustomers->changeStatus($status,$this->email);
	}
	
	function getDiscussionName(){
		if($this->username2){ 
			$name = $this->username2; 
		} else {
			$name = $this->getName();
		} 
		return $name;
	}
	
	function getName(){   
		$name = '';
		if($this->prefix){
			$name = $this->prefix . '. ';   
		}
		$name .= $this->jmeno  . ' ' . $this->prijmeni;
		return $name;
	}
	
	
	function getNameStatic($username){
		return $username;
	}
		
	function setIP(){ 
		$data = array(
			'IP' => Utils::getIP(),
			'lastLogged' => new Zend_Db_Expr('NOW()')
		);
		//e($data); die();
		
		Zend_Registry::getInstance()->db->update(
			$this->_tableName,
			$data, 
			"id = $this->id" 
		);
	}
	
	function getBPPoints($mBPPoints){ 
		return $mBPPoints->getCount($this->id); 
	}
	
	
	function updateBPPoints(){  
		$points = new module_PointsBP();
		$data = array(
			'pointsBP' => $points->getCount($this->id)  
		);		
		Zend_Registry::getInstance()->db->update(
			$this->_tableName,
			$data,
			"id = $this->id" 
		);
	}
	
	function updatePoints(){  
		$points = new module_Points();
		$data = array(
			'points' => $points->getCount($this->username)
		);		
		Zend_Registry::getInstance()->db->update(
			$this->_tableName,
			$data,
			"id = $this->id" 
		);
	}
	
	function updateGSM($gsm){  
		if($gsm && $this->id){  
			$data = array( 
				'gsm' => $gsm
			);		
			Zend_Registry::getInstance()->db->update(
				$this->_tableName,
				$data,
				"id = $this->id" 
			);  
			$this->gsm = $gsm;
		}  
	}
	
	
	
	function forcedStorno(){
		$data = array(
			'fstorno' => new Zend_Db_Expr('fstorno + 1')
		);
		//e($data); die();
		
		Zend_Registry::getInstance()->db->update(
			$this->_tableName,
			$data, 
			"id = $this->id"  
		);
	}
}	
?>