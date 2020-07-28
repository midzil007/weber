<?php

class module_FAQ 
{    
   function __construct()
    {
        $this->_tableName = 'module_WebActionsUsers';    
        $this->db =  Zend_Registry::getInstance()->db;
    }
        
    public function addNew($postData)    
    {	
    	$this->input = $postData;
    	$err = $this->checkPage();	
		
		if(!$err){ // ok	
								
			$mail = new Email();	
			
			$emailText = Helpers::prepareEmail(
				$mail,
				'
					<h2 style="color:#0f4096; font-size: 12px;">Nová otázka FAQ</h2>
					<p>
					Od uživatele '. $this->input->fullname .' (<a href="mailto:' . $this->input->email . '">' . $this->input->email . '</a>)
					<p>
					<b>Otázka</b>									
					<p>
					' . $this->input->question . '
					</p>
				'
			);
			
			$mail->setBodyText(strip_tags(urldecode($emailText)));
			$mail->setBodyHtml(urldecode($emailText));			
			$mail->setSubject('Nová otázka FAQ');		
			//$mail->setFrom('orlik.michal@gmail.com', 'adfad afd');	
			$s = Zend_Registry::getInstance()->settings;
			$mail->setFrom($s->getSettingValue('outcommingEmail'), $s->getSettingValue('outcommingEmailName'));	
			$mail->addTo($this->input->email, $this->input->fullname);
			
			try {
				$mail->send();
			} catch (Exception $e) {
			    return array(0, 'Email se nepodařilo odeslat, prosím kontaktujte administrátora.');
		    }
				
			return array(1, 'Váš dotaz byl úspěšně odeslán, budeme Vás kontaktovat.');
		} else {
			return array(0, $err);
		}
    }
    
      
    public function checkPage(){   	
    	
    	do{	
			if(!$this->input->fullname || !$this->input->question || !$this->input->email){
				$err = "Zadejte prosím všechny povinné položky.";			   
			    break;
			}	
			$validator = new Zend_Validate_EmailAddress();			
			if (!$validator->isValid($this->input->email)) {			    
				$err = current($validator->getMessages());			   
			    break;
			}
			
			return false;
		} while (false);			
		return $err;
    }
    
    public function checkChangePage(){   	
    	
    	do{	
			if(!$this->input->fullname || !$this->input->company || !$this->input->sex || !$this->input->region || !$this->input->sector || !$this->input->email){
				$err = "Zadejte prosím všechny povinné položky.";			   
			    break;
			}	
			$validator = new Zend_Validate_EmailAddress();			
			if (!$validator->isValid($this->input->email)) {			    
				$err = current($validator->getMessages());			   
			    break;
			}
			if($this->input->password || $this->input->password2){
				if($this->input->password != $this->input->password2){
					$err = "Nová hesla se neshodují.";			   
			    	break;
				}
			}
					
			return false;
		} while (false);			
		return $err;
    }
    
    public function checkForgottenPage(){   	
    	
    	do{	
			if(!$this->input->email){
				$err = "Zadejte prosím email.";			   
			    break;
			}	
			$validator = new Zend_Validate_EmailAddress();			
			if (!$validator->isValid($this->input->email)) {			    
				$err = current($validator->getMessages());			   
			    break;
			}
			// neexistuje uy email ?
			$e = $this->db->fetchOne('SELECT email FROM `module_WebUsers` WHERE email = ?', $this->input->email);
			
			if(!$e){
				$err = 'Email ' . $e . ' neexistuje.';			   
			    break;
			}
					
			return false;
		} while (false);			
		return $err;
    }
    
    public function checkConfirmPage($email){   	
    	
    	do{	
			if(!$email){
				$err = "Chyba.";			   
			    break;
			}	
		
			// neexistuje uy email ?
			$e = $this->db->fetchOne('SELECT email FROM `module_WebUsers` WHERE email = ?', $email);
			
			if(!$e){
				$err = 'Email ' . $e . ' neexistuje.';			   
			    break;
			}
					
			return false;
		} while (false);			
		return $err;
    }
    
}