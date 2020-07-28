<?php

class module_WebUsersBase
{   
	function __construct(){
		$this->db =  Zend_Registry::getInstance()->db;
        $this->session = Zend_Registry::getInstance()->session;
        $this->_tableName = 'module_WebUsers';
	}
        
    public function getUser($userId)
    {	    	        
		$select =  $this->db->select();
		$select->from($this->_tableName, array( '*' ));    
				
		$select->where('id = ?', $userId);
		return $this->db->fetchRow($select);
		
    }
    
    public function updateUserBE($id, $data)
    {	
    	unset($data['id']);
    	
    	$where = $this->db->quoteInto(' id = ? ', $id);
    	
		$this->db->update(
			$this->_tableName,
			$data,
			$where
		);
    }
    
    public function deleteUser($userId)
    {	
    	$this->db->delete(			
			$this->_tableName,
			$this->db->quoteInto(" id= ? ", $userId)
		);
		
		//return $this->adapter->fetchAll($select);
		
    }
    
    
    public function getUsers( $sectorFilter = null, $regionFilter = null, $sort = 'fullname', $sortType = 'Desc')
    {
    	$select =  $this->db->select();
		$select->from($this->_tableName, array( 'id', 'fullname', 'email', 'region', 'sector'));
    	    	
		if($sectorFilter != 'allsectors' && $sectorFilter){				
			$select->where('sector = ?', $sectorFilter);
		} 
		
		if($regionFilter != 'allregions' && $regionFilter){				
			$select->where('region = ?', $regionFilter);
		} 
		
		$select->where('active = ?', '1');
		
		$sortType = $sortType?$sortType:'Asc';
		$select->order($sort . ' ' . $sortType);
		//e($select->__toString());
		$all = $this->db->fetchAll($select);
		//pr(module_WebUser::$sector);
		$users = array();
		foreach ($all as $id => $u){
			$users[$id] = $u;
			$users[$id]['sector'] = module_WebUser::$sector[$u['sector']];
			$users[$id]['region'] = module_WebUser::$region[$u['region']];
		}
		return $users;
		
    }
    
    
    public function loginUser($username,$pass, $goto = '/web/uzivatel/profil')
    {	
    	$userData = $this->db->fetchRow(
    		'SELECT * FROM `' . $this->_tableName . '` WHERE username = ? AND password = ? AND active = ?', 
    		array($username, $pass, '1')
    	);
		if(is_array($userData)){	
			$user = new module_WebUser($userData['username']);
			$this->session->loggedWebUser = true;
			$this->session->webUser = $user;
			
			Helpers::redirect($goto);
		} else {
			return array(0, 'Přihlášení se nezdařilo, zkontrolujte zadávané údaje.');
		}
    }
    
    public function logout()
    {
    	$this->session->loggedWebUser = false;
		$this->session->webUser = null;
    	Helpers::redirect('/');
    }
    
    public function confirmEmail($email)
    {
    	$err = $this->checkConfirmPage($email);			
		if(!$err){ // ok
			
			$data = array(
				'active' => '1'
			);
			$where = $this->db->quoteInto('email = ?', $email);
			
			$this->db->update($this->_tableName, $data, $where);
		
			return array(1, 'Ověření proběhlo úspěšně, mužete se přihlásit.');
		} else {
			return array(0, $err);
		}
    	//Helpers::redirect('/web/uzivatel/profil.html');
    	
    }
        
    public function registerUser($postData)    
    {	
    	$this->input = $postData;
    	$err = $this->checkRegistrationPage();	
		
		if(!$err){ // ok	
			$username = Utils::generatePathNameSimple($this->input->fullname);
			$password = Utils::generatePassword();
    		$this->db->insert(
    			$this->_tableName,
    			array(
    				'fullname' =>  $this->input->fullname,
    				'username' => $username,
    				'password' => $password,
    				'email' => $this->input->email,
    				'kraj' => $this->input->region
    			)
			);
			
					
			$mail = new Email();	
			
			$emailText = Helpers::prepareEmail(
				$mail,
				'
					<h2 style="color:#0f4096; font-size: 12px;">Potvrzení Vaší registrace</h2>
					<p>
					Ke svému účtu se nyní můžete přihlásit pomocí tohoto jména a hesla: 
					<p>
					<table style="font-size: 11px; color: #708490;">
						<tr><td>přihlašovací (uživatelské) jméno:</td><td><b>' . $username . '</b></td></tr>
						<tr><td>přihlašovací heslo:</td><td><b>' . $password . '</b></td></tr>
					</table>				
					<p>
					<p>Nejprve je ale nutné potvrdit správnost emailu, a to kliknutím na tento odkaz: 
					<a href="' . Utils::getWebUrl() . '/web/uzivatel/profil.html?confirm=' . $this->input->email .'">potvrzuji registraci</a> <br/><br/>
					Pokud se Vám odkaz neotvírá zkopírujte tento odkaz do příkazového řádku prohlížeče <br />
					' . Utils::getWebUrl() . '/web/uzivatel/prihlaseni-uzivatele?confirm=' . $this->input->email .'
				'
			);
			
			$mail->setBodyText(strip_tags(urldecode($emailText)));
			$mail->setBodyHtml(urldecode($emailText));			
			$mail->setSubject('Potvrzení Vaší registrace');		
			
			$s = Zend_Registry::getInstance()->settings;
			$mail->setFrom($s->getSettingValue('outcommingEmail'), $s->getSettingValue('outcommingEmailName'));	
			$mail->addTo($this->input->email, $this->input->fullname);
			
			try {
				$mail->send();
			} catch (Exception $e) {
			    return array(0, 'Email se nepodařilo odeslat, prosím kontaktujte administrátora.');
		    }
				
			return array(1, 'Registrace proběhla úspěšně, přihlašovací údaje Vám byly zaslány na email.');
		} else {
			return array(0, $err);
		}
    }
    
    public function updateUser($postData)    
    {	
    	$this->input = $postData;
    	$err = $this->checkChangePage();	
		
		if(!$err){ // ok	
			
			$data = array(
				'fullname' =>  $this->input->fullname,				
				'email' => $this->input->email,
				'sex' => $this->input->sex,
				'company' => $this->input->company,
				'region' => $this->input->region,
				'sector' => $this->input->sector,
				'adress' => $this->input->adress,
				'id' => $this->input->id,
			);
			if($this->input->password){
				$data['password'] = $this->input->password;
			}
			
			$where = $this->db->quoteInto('id = ?', $this->input->id);
    		$this->db->update(
    			$this->_tableName,
    			$data,
    			$where
			);
			
			$this->session->webUser = Helpers::initFormValuesFromObject($data);
			
							
			return array(1, 'Údaje změněny');
		} else {
			return array(0, $err);
		}
    }
    
    
    public function sendLoginInfo($postData)    
    {	
    	$this->input = $postData;
    	$err = $this->checkForgottenPage();	
		
		if(!$err){ // ok	
			
			$data = $this->db->fetchRow('SELECT username, password FROM `module_WebUsers` WHERE email = ?', $this->input->email);
			$username2 = $data['username'];
			$password2 = $data['password'];
			
			$mail = new Email();				
			$emailText = Helpers::prepareEmail(
				$mail,
				'
					<h2 style="color:#0f4096; font-size: 12px;">Vaše přihlašovací údaje</h2>
					<table style="font-size: 11px; color: #708490;">
						<tr><td>přihlašovací (uživatelské) jméno:</td><td>' . $username2 . '</td></tr>
						<tr><td>přihlašovací heslo:</td><td>' . $password2 . '</td></tr>
					</table>				
					<p>
				'
			);
			
			$mail->setBodyText(strip_tags(urldecode($emailText)));
			$mail->setBodyHtml(urldecode($emailText));			
			$mail->setSubject('Zapomenuté heslo');		
			$s = Zend_Registry::getInstance()->settings;
			$mail->setFrom($s->getSettingValue('outcommingEmail'), $s->getSettingValue('outcommingEmailName'));	
			$mail->addTo($this->input->email, $this->input->fullname);
			
			try {
				$mail->send();
			} catch (Exception $e) {
			    return array(0, 'Email se nepodařilo odeslat, prosím kontaktujte administrátora.');
		    }
				
			return array(1, 'Přihlašovací údaje Vám byly zaslány na email.');
		} else {
			return array(0, $err);
		}
    }
    
    public function checkRegistrationPage(){   	
    	
    	do{	
			if(!$this->input->fullname || !$this->input->region || !$this->input->email){
				$err = "Zadejte prosím všechny povinné položky.";			   
			    break;
			}	
			$validator = new Zend_Validate_EmailAddress();			
			if (!$validator->isValid($this->input->email)) {			    
				$err = current($validator->getMessages());			   
			    break;
			}
			// neexistuje uy email ?
			$e = $this->db->fetchOne('SELECT email FROM `module_WebUsers` WHERE email = ?', $this->input->email);
			if($e){
				$err = 'Email ' . $e . ' již existuje.';			   
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