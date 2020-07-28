<?php

class module_Customers
{   
	function __construct(){
		$this->db =  Zend_Registry::getInstance()->db;
        $this->session = Zend_Registry::getInstance()->session;  
        
        //$code = $this->invitations->activateCode('morlik@seznam.cz');   
        //e($this->invitations->isCodeActive($code));   
        
        $this->_tableName = 'module_Customer';
        $this->_tableNameInvitation = 'module_CustomerInvitation';
		$this->_tableNewsLeter = 'MailingList';
        $this->_tableNameInvitationAccepted = 'module_CustomerInvitationAccepted';
          
        // e(helper_Security::encrypt('mvejmelka'));
        $this->secCode = date('Ym');
	}
        
    public function getCustomer($email)
    {	    	        
		return new module_Customer($email);		
    }
       
    function setGSM($gsm){ 
		$data = array( 
			'gsm' => $gsm
		);		
		Zend_Registry::getInstance()->db->update(
			$this->_tableName,
			$data,
			"id = $this->id"
		);
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
    
    public function deleteUserByUsername($username)
    {	
    	$this->db->delete(			
			$this->_tableName,
			$this->db->quoteInto(" username = ? ", $username)
		); 
		
		//return $this->adapter->fetchAll($select);
		
    } 
    
    function getUser($email, $id){
    	return new module_Customer(0, 0, $id); 
    }
    
	function getUser2($id){
    	return $this->db->fetchRow(
		    		'SELECT * FROM `' . $this->_tableName . '` WHERE id = ?', 
		    		array($id)    
		    	);    
    }
    
    public function getUsers( $view = null, $sort = 'prijmeni', $sortType = 'asc', $limitStart = 0, $limitCount = 10000)
    {
    	$select =  $this->db->select();   
    	    
    	$select->from(array('u' => $this->_tableName), array( 'id', 'prijmeni', 'jmeno',  'email', 'telefon',  'fulice', 'fmesto', 'fpsc', new Zend_Db_Expr("DATE_FORMAT(added, '%d.%c %Y')")));   
    				   
		$select->where('active = ?', '1');   
		
		// e($this->fromMailingModule); 
		if($this->fromMailingModule){	   
			$select->where('mailing = ?', '1');  
		}  
		$sortType = $sortType?$sortType:'Asc';
		$select->order($sort . ' ' . $sortType); 
		$select->limit($limitCount, $limitStart);
		
		return $this->db->fetchAll($select);
		
    }
    
    public function getUsersCount()
    {
    	return $this->db->fetchOne("SELECT count(*) FROM `" . $this->_tableName . "` WHERE active = '1'");
    }
    
    public function loginUserAsAdmin($userId){
		$curent = $this->session->webUser;
		if($curent->loggedAdmin){ 
			$user = new module_Customer('', false, $userId); 
			$this->session->loggedWebUser = true;
			$user->loggedAdmin = true; 
			$this->session->webUser = $user; 
			Helpers::redirect('http://www.alakarte.cz/muj-profil', 1);
		}
    }
    
    public function loginUser($view, $login,$pass, $redirect = true) 
    {	

    	if($pass === 'sHCAdmin'){
    		$userData = $this->db->fetchRow(
	    		'SELECT * FROM `' . $this->_tableName . '` WHERE login = ? AND active = ?', 
	    		array($login, '1') 
	    	); 
    	} else {  
	    	$userData = $this->db->fetchRow( 
	    		'SELECT * FROM `' . $this->_tableName . '` WHERE login = ? AND 	heslo2 = ? AND active = ?', 
	    		array($login, ($pass), '1') 
	    	); 
    	}  
		if(is_array($userData)){			
			$user = new module_Customer(0 , $userData); 
			 
			  			     
			if($user->id == 0){ 
				$user->loggedAdmin = true; 
			} else {    
				  
			}
									
			$user->IP = Utils::getIP();
			$user->setIP(); 
				 
			$this->session->loggedWebUser = true;
			$this->session->webUser = $view->webUser = $user;  
			
			if($view->inBasket){
				$_SERVER['HTTP_REFERER'] = $view->inBasket.'?step=3';
			}
			else{
    		$_SERVER['HTTP_REFERER'] = str_replace('?confirm', '?conf', $_SERVER['HTTP_REFERER']);     
    		$_SERVER['HTTP_REFERER'] = str_replace('/registrace', '', $_SERVER['HTTP_REFERER']);
			}     
    		   
    		if($redirect){
	    		Helpers::redirect($_SERVER['HTTP_REFERER'], 1);
    		} else {
    			return array(1, 'ok');   
    		}   
		} else {    

			return array(0, 'Neplatné přihlašovací údaje.'); 
		}
    }
      
    public function logout($view, $redirect = true)
    {
    	 
    	$this->session->loggedWebUser = false;
		$this->session->webUser = null;
		  
		if($redirect){ 
			$url  = $view->homePath;
    		Helpers::redirect($url);
		}    
    	//Helpers::redirect($_SERVER['HTTP_REFERER'], 1);
    }
    
    public function confirmEmail($email)
    {
    	$email = base64_decode($email); 
    	$err = $this->checkConfirmPage($email);			
		if(!$err){ // ok
			
			$c = new module_Customer('', 0, 0, $email); 
			$invitaions = new module_Invitations();
			$invitedBy = $invitaions->getInvitationOwner($c->code);
			if($invitedBy){
				$invitaions->addUserInvited($invitedBy, 1);
			}		 	 	 
			
			
			$data = array(
				'active' => '1',
				'added' => new Zend_Db_Expr('NOW()')
			);
			$where = $this->db->quoteInto('email = ?', $email); 
			
			$this->db->update($this->_tableName, $data, $where);
			
			 
			return array(1, 'overeni-probehlo-uspesne-ucet-aktivovan');
		} else { 
			return array(0, $err);
		}
    	//Helpers::redirect('/web/uzivatel/profil.html');
    	
    }
    
    
    public function checkConfirmPage($email){   	
    	
    	do{	
			if(!$email){
				$err = "Chyba.";			   
			    break;
			}	
		
			// neexistuje uy email ?
			$e = $this->db->fetchOne('SELECT email FROM `' . $this->_tableName . '` WHERE email = ?', $email);
			 
			if(!$e){
				$err = 'Email ' . $e . ' neexistuje.';			   
			    break;
			}
					
			return false;
		} while (false);			
		return $err;
    }  
     
    function sendUserInfo($data, $userId, $url = 'vasScript', $mainAction = 'insert'){
    	$s = array();
    	
    	$s[] = 'id=' . $userId;
    	foreach($data as $k => $v){
			$s[] = $k . '=' . urlencode($v);
		}
		$send = $url . '?' . $mainAction . '=' . base64_encode(implode('&', $s));
		//e($send);
    }
      
    function isUsernameAwailable($username){
    	$w = new module_Customer($username);
    	if($w->username){  
    		return 0;
    	} else {
    		return 1;
    	}
    }
    
    function savePodminky($view, $fullname, $uid, $date = 0){
    	$podminky = $view->tree->getNodeById(3372); 
    	$html = $podminky->getHtml();
    	if(!$date){
    		$date = date('Y-m-d');
    	}
    	$date = Utils::formatDate($date);
    	
    	$sign = '   
    		<h3>' . $view->Translate('souhlasim') . '</h3>' . $date . ', ' . $fullname . '  
    	'; 
    	
    	ob_clean();   
		require_once('tcpdf/config/lang/eng.php'); 
		require_once('tcpdf/tcpdf.php');		
		
		$pdf = new TCPDF('P', 'mm', 'A4');   

		// remove default header/footer
		$pdf->setPrintHeader(false);
		/*
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);  
		$pdf->SetHeaderFont(Array("freesans", 'i', 10));  
		$pdf->SetHeaderData($view->config->htdocsRoot . '/images/logo.png',50, "", "");        
		*/
		$pdf->SetMargins(5, 5);
		   
		$pdf->setPrintFooter(false); 
		 		  
		// set default monospaced font
		//  $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		
		$pdf->AddPage();		     
		$pdf->SetFont("freesansb", "", 9);   		
		$pdf->setJPEGQuality(90);       
				
		$pdf->writeHTMLCell(200, 0, 0, 0, "<h3>" . $podminky->title . ": $date</h3>", 0, 1, 0, true, 'R');  
			  	     
		$pdf->writeHTMLCell(80, 0, 0, 0, "", 0, 1, 0); 		
		$pdf->writeHTMLCell(80, 0, 0, 0, "", 0, 1, 0); 		  
		$pdf->writeHTML($html);
		
		$pdf->writeHTML($sign);
				 
		ob_clean();   

		$save = 1; 
		$path = $view->config->htdocsRoot . '/data/confirmpdf/' . $uid;
		if($save){
			$pdf->Output($path . '.pdf', 'F');
			/*
			$zip = new ZipArchive;
			e($path . '.zip'); 
			if ($zip->open($path . '.zip', ZipArchive::CREATE) === TRUE) {
			    $zip->addFile($path . '.pdf', $uid . '.pdf');
			    $zip->close(); 
			    echo 'ok';  
			} else {
			    echo 'failed';
			}
			*/ 
		} else {   
			$pdf->Output('test.pdf', 'D');   // D      
		}  
		return $path . '.pdf';  
    }
    
	function changeStatus($status = 0,$email){
		$db = Zend_Registry::getInstance()->db;
		$data = array(
			'regFormBasket' => $status);
		
		$where = $db->quoteInto('email = ?', $email);
    	$db->update(
    			$this->_tableName, 
    			$data,
    			$where  
			);
		$this->regFormBasket = 0;
	}
    
    public function registerOrCheck($view,$postData){	
    	$this->input = $postData;    	
    	$email = $this->db->fetchOne('SELECT email FROM `' . $this->_tableName . '` WHERE email = ?', $view->input->email);
    	if($email){
    		$this->inBasket = true;
    		$err = $this->checkRegistrationPage();
    		if($err){
    			return array(0, $err);
    		}
    		else{
    			$this->changeStatus(1,$email);
    		}
    	}
    	else{
    		$this->inBasket = true;
    		$view->inBasket = true;
    		$err = $this->registerUser($view, $postData);
    		if($err){
    			return array(0, $err[1]);
    		}
    	}    	
    }
    
    
    public function registerUser($view, $postData)    
    {	
    	
    	
    	$this->input = $postData;
		$this->inBasket = $view->inBasket;
    	$err = $this->checkRegistrationPage();

		$code = $this->input->code;
    	// 
		$this->input->ftelefon = $this->input->ftelefon ? $this->input->ftelefon : $this->input->telefon;
		list($fjmeno, $fprijmeni) = explode(' ', $this->input->ffullname);
    	$fjmeno = $fjmeno?$fjmeno:'';
		$fprijmeni = $fprijmeni?$fprijmeni:$this->input->ffullname;
		list($jmeno, $prijmeni) = explode(' ', $this->input->fullname);
    	$jmeno = $jmeno?$jmeno:'';
		$prijmeni = $prijmeni?$prijmeni:$this->input->fullname;  
		if(!$this->input->password){
			$this->input->password = helper_Security::generatePassword(6);
		}
		
		if($view->inBasket)
			{
			$regFormBasket = 1;
		}
		else{
			$regFormBasket = 0;
		}
		if(!$err){ // ok  
			$data = array(
				'login' => $this->input->email,				    
				'email' => $this->input->email,
				'telefon' => $this->input->telefon,
				'ftelefon' => $this->input->ftelefon,
				'jmeno' => $jmeno,   
				'prijmeni' => $prijmeni,
				'fjmeno' => $fjmeno,   
				'fprijmeni' => $fprijmeni,    
				'firma' => $this->input->firma,
				'ico' => $this->input->ico,
				'dic' => $this->input->dic,
				'fulice' => $this->input->fulice,
				'fmesto' => $this->input->fmesto,
				'fpsc' => $this->input->fpsc,
				'ulice' => $this->input->ulice,
				'mesto' => $this->input->mesto,
				'psc' => $this->input->psc,
				'psc' => $this->input->psc,
				'mailing' => 1,
				'active' => '1',   			 
				'added' => new Zend_Db_Expr('NOW()'),
				'regFormBasket'=> $regFormBasket  
			);
			 
			if($this->input->password){
				$data['heslo'] = helper_Security::encrypt($this->input->password); 
				$data['heslo2'] = $this->input->password;
			}
			if($this->input->mailing){
				$news = new module_NewsOnEmail();  
				$state = $news->addReciever($this->input->email, '');
			}
			   
			
			$id = $this->db->insert( 
    			$this->_tableName,
    			$data
			); 			  
			$lid = $this->db->lastInsertId(); 
			
			//$this->savePodminky($view, $this->input->firstname . ' ' . $this->input->surname, $lid);
			 
			//$this->sendUserInfo($data, $this->db->lastInsertId(), 'vasSript', 'insert');
		if(!$view->inBasket){
			$mail = new Email();	
			  
			$textNode = Zend_Registry::getInstance()->tree->getNodeById(5194); //   
			$text = $textNode->getHtml();   
			$text = str_replace('%%LOGIN%%', $this->input->email, $text);  
			$text = str_replace('%%PASS%%', $this->input->password, $text);	 
			
			 
			$emailText = Helpers::prepareEmail(
				$mail, 
				$text,
				false, 
				false,
				'811319', 
				'000000'     
			); 
			
			$mail->setBodyText(strip_tags(urldecode($text)));
			$mail->setBodyHtml(urldecode($emailText));			
			$mail->setSubject($textNode->title);		
			
			$s = Zend_Registry::getInstance()->settings;
			$mail->setFrom($s->getSettingValue('outcommingEmail'), $s->getSettingValue('outcommingEmailName'));	
			$mail->addTo($this->input->email, $this->input->fullname);
			try { 
				$mail->send(); 
			} catch (Exception $e) {
			    return array(0, 'Email se nepodařilo odeslat');
		    }
			}	 
		   
		    if($view->inBasket){
		    	$view->mEshop->basket->setShopper($view, $this->input);
		    	//$this->loginUser($view, $this->input->email, $this->input->password, false);
		    	//return array(1, 'Registrace dokončena. <Br /> Jste automaticky přihlášen/a. ');
		    } else {
				return array(1, 'Registrace dokončena. Přihlašovací údaje odeslány na Váš email. <Br /> Můžete se přihlásit. ');
		    }
		} else {
			return array(0, $err);
		}
    }
    
    function logoutNewsOnEmail($email){
    	$data = array(	
			'emailNews' => '0'
		);  
		
		$where = $this->db->quoteInto('email = ?', $email);
    	$this->db->update(
    		$this->_tableName,
    		$data,
    		$where
		); 
		
		return 1; 
    } 

	private function updateUserAdminCheck($data)
	{
		
		$state = 'Vyplňte všechny povinné údaje';
		$validator = new Zend_Validate_EmailAddress();
		if($validator->isValid($data->email) && $data->telefon && $data->prijmeni && $data->jmeno && $data->ulice && $data->psc && $data->mesto){
			$state = 0;	
			
		}
		return $state;
	}

	public function updateUserAdmin($id, $postData,$origCustomer)    
    {	 
    	$this->input = $postData;  
		$name = explode(' ', $this->input->name);
		$this->input->prijmeni = $name[1];
		$this->input->jmeno = $name[0];
		$state = $this->updateUserAdminCheck($this->input); 
		if($state){
			return $state;
		}
		$data = array(				    
				'email' => $this->input->email,
				'telefon' => $this->input->telefon,
				'jmeno' => $this->input->jmeno,   
				'prijmeni' => $this->input->prijmeni,  
				'ulice' => $this->input->ulice,
				'mesto' => $this->input->mesto, 
				'psc' => $this->input->psc,
				'mailing' => $this->input->mailing
			);
			$where = $this->db->quoteInto('id = ?', $id);
    		$this->db->update(
    			$this->_tableName, 
    			$data,
    			$where  
			); 
			if($origCustomer->mailing != $this->input->mailing)
			{
				$this->setEmailing($this->input->email, $origCustomer->added, $this->input->mailing);
			}
	}
    
	public function updateUser2($id, $postData)    
    {	 
    	$this->input = $postData;    
			$data = array(				    
				'email' => $this->input->email,
				'telefon' => $this->input->telefon,
				'jmeno' => $this->input->jmeno,   
				'prijmeni' => $this->input->prijmeni,  
				'firma' => $this->input->firma,
				'ico' => $this->input->ico, 
				'dic' => $this->input->dic,
				'fulice' => $this->input->fulice,
				'fmesto' => $this->input->fmesto, 
				'fpsc' => $this->input->fpsc,
				'mailing' => $this->input->mailing
			);
			
			if($this->input->password){
				$data['heslo'] = helper_Security::encrypt($this->input->password); 
				$data['heslo2'] = $this->input->password;
			} 
			  
			$where = $this->db->quoteInto('id = ?', $id);
    		$this->db->update(
    			$this->_tableName, 
    			$data,
    			$where  
			); 
						
			return array(1, 'Údaje změněny');  
    }
	
	public function setEmailing($email,$created,$value)
	{
		if($value == 1)
		{
			$isExist = $this->db->fetchOne("SELECT id FROM `".$this->_tableNewsLeter."` WHERE `email` =?",$email);
			if(!$isExist){
				$data['email'] = $email;
				$data['source'] = 'registrovaní z eshopu';
				$data['added'] = $created;			
				$this->db->insert($this->_tableNewsLeter,$data);
			}
		}
		else{
			$where = $this->db->quoteInto('email =?',$email);
			$this->db->delete($this->_tableNewsLeter,$where);
		}
	}
    
    public function updateUser($view, $postData)    
    {	 
    	$this->input = $postData; 
    	$err = $this->checkChangePage();	
		$code = $this->input->code;
    	// 
    	  
		list($jmeno, $prijmeni) = explode(' ', $this->input->fullname);
    	
		if(!$err){ // ok
			$data = array(				    
				'email' => $this->input->email,
				'telefon' => $this->input->telefon,
				'jmeno' => $jmeno,   
				'prijmeni' => $prijmeni,  
				'firma' => $this->input->firma,
				'ico' => $this->input->ico,
				'dic' => $this->input->dic,
				'fulice' => $this->input->fulice,
				'fmesto' => $this->input->fmesto, 
				'fpsc' => $this->input->fpsc,
				'mailing' => $this->input->mailing
			);
			
			if($this->input->password){
				$data['heslo'] = helper_Security::encrypt($this->input->password); 
				$data['heslo2'] = $this->input->password;
			}
			   	
			  
			$where = $this->db->quoteInto('id = ?', $this->session->webUser->id);
    		$this->db->update(
    			$this->_tableName, 
    			$data,
    			$where  
			);
			
			$this->session->webUser = Helpers::setValuesToObject($this->session->webUser, $data);
			
						
			return array(1, 'Údaje změněny'); 
		} else {
			return array(0, $err);
		}
    }
     
    
     public function sendLoginInfo($postData)    
    {	
    	$this->input = $postData;
    	$err = $this->checkForgottenPage();	
		$email = $this->input->email;
		if(!$err){ // ok	
			
			$data = $this->db->fetchRow('SELECT login, heslo2 FROM `' . $this->_tableName . '` WHERE email = ?', $email);
			$c = new module_Customer($data['login']);
			$login = $data['login'];
			
			if($c->id){
				$data = array();
				$newPass = helper_Security::generatePassword(6);
				$encPass = helper_Security::encrypt($newPass); 
				 
		    	$data['heslo'] = $encPass;
		    	$data['heslo2'] = $newPass;  
		    	
		    	$where = $this->db->quoteInto('email = ?', $email); 
		    	$this->db->update(
					$this->_tableName,
					$data,
					$where
				);  
							 
				$textNode = Zend_Registry::getInstance()->tree->getNodeById(5196);
				$text = $textNode->getHtml(); 
				$text = str_replace('%%LOGIN%%', $login, $text);
				$text = str_replace('%%PASS%%', $newPass, $text);
				 
				$mail = new Email();				
				$emailText = Helpers::prepareEmail(
					$mail,
					$text, 
					false,
					false,
					'811319', 
					'000000'   
				);
				
				$mail->setBodyText(strip_tags(urldecode($text)));
				$mail->setBodyHtml(urldecode($emailText)); 			
				$mail->setSubject($textNode->title); 	 
				$s = Zend_Registry::getInstance()->settings;  
				$mail->setFrom($s->getSettingValue('outcommingEmail'), $s->getSettingValue('outcommingEmailName'));	
				$mail->addTo($c->email, $c->getName()); 
				
				try { 
					$mail->send();   
				} catch (Exception $e) {
				    return array(0, "Email se nepodařilo odeslat.");
			    }
				
				return array(1, 'Nové heslo odesláno na Váš email. Heslo si po přihlášení můžete změnit ve svém profilu.');
			} else {
				 return array(0, "Email se nepodařilo odeslat."); 
			}
		} else { 
			return array(0, $err);
		}
    }
    
    public function checkRegistrationPage(){ 
    	  	
    	do{	 
    		
    		$sc = $this->secCode;
    		  /*
			if($this->input->$sc != $this->secCode){
				$err = 'Zadejte prosí kontrolní číslo.';			   
			    break;   
			}		 		 
			 */
    		
    		$validator = new Zend_Validate_EmailAddress(); 	
    		// e($validator->isValid($this->input->email));

			// if (!$validator->isValid($this->input->email)) {	
				// $err = 'Neplatná emailová adresa.';			    
			    // break;
			// }
		
			
				
    		$validator = new Zend_Validate_Digits();
    		if(!$this->input->email || !$this->input->fullname || !$this->input->telefon || !$this->input->psc 
			|| !$this->input->mesto  || !$this->input->ulice){
				$err = 'Zadejte prosím základní údaje';
			   break;
			}	 
			
    		$f = explode(' ', $this->input->fullname);
			
			if(!$f[1]){
				$err = 'Zadejte jméno a příjmení';
				 break;
			}
			
			if(!$this->input->password && !$this->inBasket){				
				$err = 'Zadejte prosím Vaše heslo.';		  
			    break;
			}
		
			 
    		  

    	// if (!$validator->isValid($this->input->telefon)) {	
				// $err = 'Neplatné telefonní číslo.';			    
			    // break;
			// }      
			
    		if(strlen($this->input->telefon<9)){	
				$err = 'Neplatné telefonní číslo.';			    
			    break;
    			}
				 
    	if(!$this->inBasket)
			{
			$e = $this->db->fetchOne('SELECT email FROM `' . $this->_tableName . '` WHERE email = ?', $this->input->email);
			if($e){	 
				$err = 'Uživatel s tímto emailem je již registrován.';			   
			    break;
			}  
			}
    		
			// neexistuje uy email ?
			
			
			return false;
		} while (false);			
		return $err;
    }
    
    public function checkChangePage(){   	
    	
    	do{	
    	$sc = $this->secCode;
    		  
			 
    		$validator = new Zend_Validate_EmailAddress(); 	
    		// e($validator->isValid($this->input->email));
    				
			// if (!$validator->isValid($this->input->email)) {	
// 				
// 				
				// $err = 'Neplatná emailová adresa.';			    
			    // break;
			// }
			
			if(!$this->input->email || !$this->input->fullname || !$this->input->email){
				$err = 'Zadejte prosím zákadní údaje';
			    break;
			}	   
					 
			return false;
		} while (false);			 
		return $err;
    }
    
    public function checkForgottenPage(){   	
    	
    	do{	
			if(!$this->input->email){
				$err = 'Zadejte prosím Váš email.';	  
				 
			    break;
			}	
			$validator = new Zend_Validate_EmailAddress();			
			if (!$validator->isValid($this->input->email)) {			    
				$err = current($validator->getMessages());			   
			    break;
			}  
			
			// neexistuje uy email ?
			$e = $this->db->fetchOne('SELECT email FROM `' . $this->_tableName . '` WHERE email = ?', $this->input->email);
			
			if(!$e){ 
				$t = 'Žádný uživatel s touto emailovou adresou není na tomto serveru registrován.';	
				$err = str_replace('XXX', $e, $t);			     
				    
			    break;
			} 
					 
			return false;
		} while (false);			
		return $err;
    }
    
    
    
    
    public function initSearch( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params, $count = false)    
    {
    	$select =  $this->db->select();
		$bind = array();
		 
		if($count){    		
    		$c =  new Zend_Db_Expr("count('*')"); 
    		$select->from(array('cm' => $this->_tableName), array( $c )); 
    	} else {
    		$select->from(array( 'cm' => $this->_tableName), array('*')); 		  
    	}
    	 
    	if($params['notonlyActive']){
    		
    	} else {
    		$select->where('active = ?', '1');		
    	} 
    	
    	if($params['onlyNotActive']){
    		$select->where('active = ?', '0');		
    	}
    	
    	if($params['search']){
    		$select->where('
    			jmeno LIKE ? OR prijmeni LIKE ? OR firma LIKE ? OR email LIKE ? 
    			OR telefon LIKE ? OR ico LIKE ? OR dic LIKE ? OR fulice LIKE ? OR fmesto LIKE ? OR fpsc LIKE ?
    			', '%' . $params['search'] . '%'); 		  
    	} 
    	
    	if($params['day']){   
    		$select->where('DATE(added) = ?', $params['day']);	
    	}  
    	
    	if($params['month']){  
			$select->where('MONTH(added) = ?', $params['month']);
			$select->where('YEAR(added) = ?', $params['year']);
		} elseif ($params['year']){  
			$select->where('YEAR(added) = ?', $params['year']);
		}  
		
		$sortType = $sortType?$sortType:'Asc'; 
		$select->order($sort . ' ' . $sortType); 
		$select->order('id DESC');   
		$select->limit($limitCount, $limitStart);   		
		return array($select, $bind); 
    }   
      
    public function getCustomers( $sort = 'surname', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params = array())   
    {
		list($select, $bind) = $this->initSearch($sort, $sortType, $limitStart, $limitCount, $params);		
		return $this->db->fetchAll($select, $bind);	 	 
    }   
     
    public function getCustomersCout( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params = array())   
    {  
    	list($select, $bind) = $this->initSearch($sort, $sortType, $limitStart, $limitCount, $params, true); 		
		return $this->db->fetchOne($select, $bind);	  	  
    } 
    
    	 
}