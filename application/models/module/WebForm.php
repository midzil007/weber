<?php

class module_WebForm
{   
	function __construct($postData){
		$this->db =  Zend_Registry::getInstance()->db;
        $this->session = Zend_Registry::getInstance()->session;
        $this->languages = Zend_Registry::getInstance()->languages;
        $this->_tableName = 'module_Contacts';
        $this->secCode = date('Ym');
        $this->input = $postData;
        $this->fullname = $this->input->fullname;
        $this->email = $this->input->email;
        $this->subject = $this->input->subject;
	}   
	
	private function checkEmail()
	{
		do{		
				
			if(!$this->input->fullname || !$this->input->email || !$this->input->subject || !$this->input->message || !$this->input->phone){
				$err = "Zadejte prosím všechny povinné položky.";
				break;
			}
				
			return false;
		} while (false);
		return $err;	
	}
	
	public function sendQuery()
	{	
		$err = $this->checkEmail();
		if(!$err){
			$text = 'Zpráva: '.$this->input->message;
			if($this->input->phone)
			{
				$text .= '<br /> TELEFON: '.$this->input->phone;
			}
			if($this->input->referal)
			{
				$text .= '<br /> URL: '.$this->input->referal;
			}
			$mail = new Email();
			$emailText = Helpers::prepareEmail(
					$mail,
					$text,
					false,
					true,
					$conf->instance->mailHeadColor,
					$conf->instance->mailTextColor
			);
			$mail->setBodyText(strip_tags(urldecode($text)));
			$mail->setBodyHtml(urldecode($emailText));
			$mail->setSubject($this->input->subject);
			$s = Zend_Registry::getInstance()->settings;
			if(!$emailTo){
				$emailTo = $s->getSettingValue('outcommingEmail');
			}
			if(!$emailToName){
				$emailToName = $s->getSettingValue('outcommingEmailName');
			}
			if($this->input->error){
				$mail->addTo($emailTo, $emailToName);
			}
			else{
				$mail->addTo('nosil@eportaly.cz', 'Michal Nosil');
			}     
			
			$mail->setFrom($this->input->email, $this->input->fullname);
			try {
				$mail->send();	
			} catch (Exception $e) {
				// pr($mail);    
			    return array(0, 'Email se nepodařilo odeslat, prosím kontaktujte administrátora.');
		    }
				return array(1, 'Email úspěšně odeslán, budeme Vás kontaktovat.');
		} else {
			return array(0, $err);
		}
	}
        
    public function addUser($postData)    
    {	
    	unset($this->input->send);
    	$sc = $this->secCode;
    	unset($this->input->$sc);
    	
		$this->db->insert(
			$this->_tableName,
			array(
				'fullname' =>  $this->fullname,
				'email' => $this->email,
				'data' => serialize($postData)
			)
		);
    } 
    
    function addAttachments($mail){
    	$att = array();
    	if(count($_FILES)){
    		foreach ($_FILES as $file){    	
    			if($file['tmp_name']){
	    			$at = $mail->createAttachment(file_get_contents($file['tmp_name']));
	    			$at->type        = Utils::getMimeTypeByExtension(content_SFSFile::getFileExtension($file['name'])); 
					$at->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;  
					$at->encoding    = Zend_Mime::ENCODING_BASE64;
					$at->filename    = $file['name'];
    			}
    		} 
    	}     	  
    } 
    
    public function sendEmail($text, $mandatory = array(), $emailTo = '', $emailToName = '', $emailCC = '', $emailCCName = '', $emailCC2 = '', $emailCCName2 = '' )    
    {
    	  
			
    	$err = $this->checkForm($mandatory);	
	
		if(!$err){ // ok	
		
			$conf = Zend_Registry::getInstance()->config;			
			$mail = new Email();				
			$emailText = Helpers::prepareEmail(
				$mail,
				$text,
				false,
				true,
				$conf->instance->mailHeadColor,				
				$conf->instance->mailTextColor
			); 
			
			$mail->setBodyText(strip_tags(urldecode($text)));
			$mail->setBodyHtml(urldecode($emailText));			 
			$mail->setSubject("Kontakní formulář - JURA");		
			$s = Zend_Registry::getInstance()->settings;
			if(!$s){
				$s = new Settings();
			}
			 
			if(!$emailTo){
				$emailTo = $s->getSettingValue('outcommingEmail'); 
			} 
			
			if(!$emailToName){
				$emailToName = $s->getSettingValue('outcommingEmailName');
			}
			 
			$mail->addTo("obchod@specshop.cz", $emailToName); 	
			if($emailCC){
				$mail->addCc($emailCC, $emailCCName);    
			}
			if($emailCC2){  
				$mail->addCc($emailCC2, $emailCCName2);    
			}
			  
			
			// $mail->addBcc('orlik.michal@gmail.com', 'Michal Orlik');	   
			   
			/* 
			if($_SERVER['REMOTE_ADDR'] == '217.195.175.136'){
				//  $mail->addTo('martin@balint.cz');    
				$mail->addBcc('orlik.michal@gmail.com', 'Michal Orlik');	   
				$mail->addTo('www@bestof.cz', 'Michal Orlik');	   
			} else {  
				$mail->addTo($emailTo, $emailToName); 	   
			}
			*/  
			$mail->setFrom($this->email, $this->fullname);	
			$mail->addBcc('michal.nosil@gmail.com', 'Michal ');	   		
			
			//Attachments 
			//$this->addAttachments($mail);
			       
			try {  
				$mail->send();	 
			} catch (Exception $e) {
				//pr($e);  
				
			    return array(0, 'Email se nepodařilo odeslat, prosím kontaktujte administrátora.');
		    }
				
			return array(1, 'Email úspěšně odeslán, budeme Vás kontaktovat.');
		} else {
			return array(0, $err);
		}
    }
    
    public function sendEmailToFriend($text)    
    {	
    	$err = $this->checkForm2();	
		
		if(!$err){ // ok	
						
			$mail = new Email();				
			$emailText = Helpers::prepareEmail(
				$mail,
				$text				
			);
			
			$mail->setBodyText(strip_tags(urldecode($text))); 
			$mail->setBodyHtml(urldecode($emailText));			
			$mail->setSubject($this->subject);		
			
			$mail->addTo($this->input->email2, $this->input->jmeno2);				
			$mail->setFrom($this->email, $this->fullname);
			
			//$mail->addTo('orlik.michal@gmail.com', 'Michal Orlik');	
						
			try {
				$mail->send();
			} catch (Exception $e) {
			    return array(0, 'Email se nepodařilo odeslat, prosím kontaktujte administrátora.');
		    }
				
			return array(1, 'Email úspěšně odeslán.'); 
		} else {
			return array(0, $err);
		}
    }
    
     public function sendEmailToFriendOnlyEmail($text)    
    {	
    	$err = $this->checkForm3();	
		
		if(!$err){ // ok	
						
			$mail = new Email();				
			$emailText = Helpers::prepareEmail(
				$mail,
				$text				
			);
			
			$mail->setBodyText(strip_tags(urldecode($emailText)));
			$mail->setBodyHtml(urldecode($emailText));			
			$mail->setSubject($this->subject);		
			
			$s = Zend_Registry::getInstance()->settings;
			$mail->addTo($this->input->email);				
			$mail->setFrom($s->getSettingValue('outcommingEmail'), $emailToName = $s->getSettingValue('outcommingEmailName'));
			
		
			//$mail->addTo('orlik.michal@gma		
			try {
				$mail->send();
			} catch (Exception $e) {
			    return array(0, 'Email se nepodařilo odeslat, prosím kontaktujte administrátora.');
		    }
				
			return array(1, 'Email úspěšně odeslán.');
		} else {
			return array(0, $err);
		}
    }
    
    public function sendQuestion($text)    
    {	
    	$err = $this->checkForm4();	
		
		if(!$err){ // ok	
						
			$mail = new Email();				
			$emailText = Helpers::prepareEmail(
				$mail,
				$text				
			);
			
			$mail->setBodyText(strip_tags(urldecode($emailText)));
			$mail->setBodyHtml(urldecode($emailText));			
			$mail->setSubject($this->subject);		
			
			$s = Zend_Registry::getInstance()->settings;
			//$mail->addTo($s->getSettingValue('contactEmail'), $emailToName = $s->getSettingValue('contactEmailName'));				
			$mail->setFrom($s->getSettingValue('outcommingEmail'), $emailToName = $s->getSettingValue('outcommingEmailName'));
			$mail->addTo('orlik.michal@gmail.com', 'Michal Orlik');	
		
			//$mail->addTo('orlik.michal@gma		
			try {
				$mail->send();
			} catch (Exception $e) {
			    return array(0, 'Email se nepodařilo odeslat, prosím kontaktujte administrátora.');
		    }
				
			return array(1, 'Email úspěšně odeslán, budeme Vás kontaktovat.');
		} else {
			return array(0, $err);
		}
    }
    
    public function checkForm($mandatory = array()){  
    	
    	do{	
    		$sc = $this->secCode;
			if($this->input->$sc != $this->secCode){
				$err = "Zadejte prosím kontrolní číslo.";			   
			    break;
			}
			
			if(count($mandatory)){
				foreach ($this->input as $name => $val){
					if(in_array($name, $mandatory) && $val == ''){
						$err = "Zadejte prosím všechny povinné položky.";			   		   
				   		break 2;
					}
				}
			} else {
				if(!$this->input->fullname || !$this->input->email){
					$err = "Zadejte prosím všechny povinné položky.";			   		   
				    break;
				}	
			}
			
			if($this->input->email){
				$validator = new Zend_Validate_EmailAddress();			
				if (!$validator->isValid($this->input->email)) {			    
					$err = current($validator->getMessages());			   
				    break;
				}
			}
			
			return false;
		} while (false);			
		return $err;
    }
    
   public function checkForm2(){   
    	do{	
    		$sc = $this->secCode;
			if($this->input->$sc != $this->secCode){
				$err = "Zadejte prosím kontrolní číslo.";			   
			    break;
			}
			
			if(!$this->input->fullname || !$this->input->email){
				$err = "Zadejte prosím všechny povinné položky.";			   		   
			    break;
			}	
			
			return false;
		} while (false);			
		return $err;
    }
    
    public function checkForm3(){ 
    	  
    	do{	
    		$sc = $this->secCode;
			if($this->input->$sc != $this->secCode){
				$err = "Zadejte prosím kontrolní číslo.";			   
			    break;
			}
			
			if(!$this->input->email){
				$err = "Zadejte prosím email.";			   		   
			    break;
			}	
			
			return false;
		} while (false);			
		return $err;
    }
    
    public function checkForm4(){ 
    	  
    	do{	
    		$sc = $this->secCode;
			if($this->input->$sc != $this->secCode){
				$err = "Zadejte prosím kontrolní číslo.";			   
			    break;
			}
			
			if(!$this->input->phone){
				$err = "Zadejte prosím telefon.";			   		   
			    break;
			}	
			
			return false;
		} while (false);			
		return $err;
    }
    
}