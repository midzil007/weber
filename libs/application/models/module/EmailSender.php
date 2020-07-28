<?php
/**  Po davkach rozesila emaily. */

class module_EmailSender {
	public  $timeDelay = 1; // s  
	public $packSize = 150; // kolik mailu nejvyse zaroven poslat.    
	 
	function __construct() {
		$this->db = Zend_Registry::getInstance()->db;	
		$this->_tableName = 'MailsQueue';
	} 
	
	function clear() {
		$where = $this->db->quoteInto('process_state = ?', -1);
		$this->db->delete($this->_tableName, $where);
	}
	
	function sendPack($_packSize = 0, $_delay = 0) {
		if($_packSize == 0){
			$_packSize = $this->packSize;
		}
		
		if($_delay == 0){ 
			$_delay = $this->timeDelay; 
		}
		
		$select =  "
    		SELECT * 
			FROM `" . $this->_tableName . "`
			WHERE process_state > 0 AND send_at <= now() 
			ORDER BY id desc 
			LIMIT $_packSize 		
		";// BY RAND()      
			//e($select);			 
		$mails =  $this->db->fetchAll($select);
	 
		foreach($mails as $mail) {	
			$email = unserialize($mail['email']);
			try {
				
				$email->send();	
				$data = array(
					'process_state' => -1
				);
				$where = $this->db->quoteInto('id = ?', $mail['id']);			
				
				$this->db->update($this->_tableName, $data, $where); 			 
			} catch (Exception $e) { 
				
				$data = array(
					'process_state' => ($mail['process_state'] - 1)
				);
				$where = $this->db->quoteInto('id = ?', $mail['id']);								
				$this->db->update($this->_tableName, $data, $where);
				echo $e->getMessage(); 
				//  Utils::debug($e); die(); 
			}
				
			///sleep($_delay);			
			//usleep(5000);  
		}		  
		//  die('ok');
	}
} 


?>