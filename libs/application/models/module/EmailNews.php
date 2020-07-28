<?php

class module_EmailNews
{   
	function __construct($postData){
		$this->db =  Zend_Registry::getInstance()->db;    
		$this->input = $postData;
		$this->_tableName = 'module_EmailNews';   
		$this->filter = $this->createFilter();
	}   
      
	public function createFilter()    
    {	
    	$selected = helper_MultiSelect::getMultiSelectValuesFromArray($this->input->kategorie);
    	if(count($selected)){
    		return $selected;
    	} else {
    		return false;
    	}
    }
      
    public function getSignedUser($email)    
    {
    	$select =  $this->db->select();
		$select->from($this->_tableName, array( '*' ));    
				
		$select->where('email = ?', $email);
		return $this->db->fetchRow($select);
    }
    
    public function addUser($email)    
    {	 	
    	$err = $this->checkForm();	
    	if(!$err){ // ok	
			$this->db->insert(
				$this->_tableName,
				array(
					'email' => $email,
					'filter' => serialize($this->filter)
				)
			);
			
    		return array(1, 'Přihlášení k odběru novinek proběhlo v pořádku.');
		} else {
			return array(0, $err);
		}
    }
        
    public function updateUser($email)    
    {	 	
    	$err = $this->checkForm();	
    	if(!$err){ // ok	
    		$where = $this->db->quoteInto("email = ?", $email);
			$this->db->update(
				$this->_tableName,
				array(
					'filter' => serialize($this->filter)
				),
				$where
			);
			
    		return array(1, 'Nastavení novinek bylo změněno.');
		} else {
			return array(0, $err);
		}
    }
    
    public function sendEmails($text)    
    {	
    /*
			$mail = new Email();				
			$emailText = Helpers::prepareEmail(
				$mail,
				$text				
			);
			
			$mail->setBodyText(strip_tags(urldecode($emailText)));
			$mail->setBodyHtml(urldecode($emailText));			
			$mail->setSubject($this->subject);		
			$s = Zend_Registry::getInstance()->settings;
			//$mail->addTo($s->getSettingValue('contactEmail'), $s->getSettingValue('contactEmailName'));	
			
			$mail->setFrom($this->email, $this->fullname);
			
			$mail->addTo('orlik.michal@gmail.com', 'Michal Orlik');	
						
			try {
				$mail->send();
			} catch (Exception $e) {
			    return array(0, 'Email se nepodařilo odeslat, prosím kontaktujte administrátora.');
		    }
			*/	
    }
    
    public function checkForm(){   
    	do{				
			if(!$this->filter){
				$err = "Vyberte alespoň jednu kategorii.";			   		   
			    break;
			}	
			
			return false;
		} while (false);			
		return $err;
    }
    
   
    
}