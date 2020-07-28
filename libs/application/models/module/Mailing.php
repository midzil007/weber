<?
class module_Mailing {
	 
	private $base = 'http://www.kavablack.cz/newsletter';
	private $imgagesBase = 'http://www.kavablack.cz/';
		 
	private $_tableNameUsers = 'MailingList';       
	private	$_tableNameEmails = 'MailingEmails';     
	private	$_tableNameRedirects = 'MailingRedirects'; 
	
	private	$_tableNameRedirectsDetails = 'MailingRedirectsStats'; 
	private	$_tableNameOpenedDetails = 'MailingOpenedStats';  
	 
	
	
	
	private $addRedirectsAndStats = true; 
		
	public function __construct() {
		 
		     
        $this->db =  Zend_Registry::getInstance()->db;
	} 
	
	function getUsersCountByGroup($gn){ 
		return $this->db->fetchOne(" SELECT count(*) FROM " . $this->_tableNameUsers . " WHERE source = ?", $gn);
	}
	
	function getGroupsSelect(){ 
		$g = array();
		$names =  $this->db->fetchAll(" SELECT DISTINCT  `source` FROM " . $this->_tableNameUsers . "");
		foreach ($names as $data){
			$g[$data['source']] = $data['source'];
		}
		return $g; 
	}
	
	function logoutUser($email, $id){ 
		
		$this->db->delete(			
			$this->_tableNameUsers, 
			$this->db->quoteInto(" id= ? ", $id) . ' AND ' . $this->db->quoteInto(" email = ? ", $email)
		);   
	}
		
	function br2nl($string){  
	  return preg_replace('/<br\\\\s*?\\/??>/i', "\\n\\r", $string);
	}
	
	function fixEmailText($text,$strTags = false){
		$text = str_replace("<p>","",$text );
		$text = str_replace("</p>","<br/>",$text );
		$text = str_replace("<br>","<br/>",$text);
		$text = str_replace("<br />","<br/>",$text);
		$text =  $this->br2nl($text);
		if($strTags){
			$text = strip_tags($text);	
		}
		return $text;		
	}
	
	function parseRecipients($recipients){
		$recipients = explode(',', $recipients);
		$rc = array();
		foreach ($recipients as $r){
			list($email, $name) = explode('/', $r);
			if($email){
				$rc[trim($email)] = trim($name);
			} 
		}
		return $rc;  
	} 
	
	function fixUrls($text){  
		 
		$base = $this->imgagesBase;   
		$text = str_replace('//', '/', $text);  
		$text = str_replace('http:/', 'http://', $text);   
		$text = str_replace('"/data', '"' . $base . '/data', $text);
		$text = str_replace('"/images/nl', '"' . $base . '/images/nl', $text);
		$text = str_replace('"/data/nlimages', '"' . $base . '/data/nlimages', $text);
		$text = str_replace('href="/', 'href="' . $base . '/', $text);  
		
		$text = str_replace('src="/', 'src="' . $base . '/', $text);     
		  
		$text = str_replace('<h1>', '<h1 style="color:#e00000; margin:7px 0px 2px 0px;">', $text); 
		$text = str_replace('<h2>', '<h2 style="color:#e00000; margin:7px 0px 2px 0px;">', $text);  
		$text = str_replace('<h3>', '<h3 style="color:#e00000; margin:7px 0px 2px 0px;">', $text);
		$text = str_replace('<h4>', '<h4 style="color:#e00000; margin:7px 0px 2px 0px;">', $text);
		$text = str_replace('<h5>', '<h5 style="color:#e00000; margin:7px 0px 2px 0px;">', $text);
		$text = str_replace('> <', '><', $text);
		
		$text = str_replace('<a ', '<a style="color:#e00000;" ', $text);
 
		
		
		//  pr($text); 
		return $text; 		
	}
	
	function getUsersByGroup($gn){ 
		return $this->db->fetchAll(" SELECT id, email FROM " . $this->_tableNameUsers . " WHERE source = ?", $gn);
	}
	
	function getRecipients($groups){
		$recipients = array();
		foreach ($groups as $ident){
			$all = $this->getUsersByGroup($ident);
			foreach ($all as $d){
				$recipients[$d['email']] = $d['id'];
			} 
		}
		return $recipients;
	}
	
	function delete($input ){ 
		$groups = array();
		foreach ($input->group as $ident => $ison){
			//if($ison > 0){
				$groups[] = $ident;
			//}
		} 
		  
		foreach ($groups as $gname){
			$this->db->delete(	 		
				$this->_tableNameUsers,
				$this->db->quoteInto(" source = ? ", $gname)
			);   
		}
		 
		return array(1, 'Smazáno');
	}
	
	function sendEmails($input ){ 
		$emailText = $input->content;
		$additionalRecievers = $input->additionalRecievers;
		$mailSender = $input->mailSender;
		$mailSenderName = $input->mailSenderName;
		$subject = $input->mailHead;
		$utm_source = $input->utm_source;
		$utm_medium = $input->utm_medium;
		$utm_campaign = $input->utm_campaign;
		
		$groups = array();
		foreach ($input->group as $ident => $ison){
			//if($ison > 0){
				$groups[] = $ident;
			// }
		}
		$additionalRecieversA2 = array();
		if($additionalRecievers){
			$additionalRecievers = str_replace(';', ',', $additionalRecievers);
			$additionalRecievers = str_replace(' ', '', $additionalRecievers); 
			$additionalRecieversA = explode(',', $additionalRecievers);
			$additionalRecieversA2 = array();
			foreach ($additionalRecieversA as $emailadress){
				$additionalRecieversA2[$emailadress] = 0;
			}
		}
		
 
		//  e($groups); e($emailText ); e($subject); e($mailSender); e($additionalRecievers); die();
		
		if((!count($groups) && !strlen($additionalRecievers)) || strlen($emailText) < 20 || !$subject || !$mailSender){
			return array(0, 'Neplatný email.');
		} 
		
		//  $utmString = '';
		if($utm_source){
			$utmString = 'utm_source=' . $utm_source . '&utm_medium=' . $utm_medium . '&utm_campaign=' . $utm_campaign . '';
		}
		   
		$recipients = $this->getRecipients($groups); 
		$recipients = array_merge($recipients, $additionalRecieversA2);  
		$recipientsCount = count($recipients);  
		$emailText = $this->fixUrls($emailText);
		
		$emailId = $this->addSentEmail($emailText, $groups, $additionalRecievers);
		
		//  pr($emailText); die();  
		
		
		$emailText = $this->addUtm($emailText, $utmString, $emailId); 
		  
		 
		// pr($emailText); die(); 
		  
		
		$name = '';
		foreach ($recipients as $uemail => $id){  
			if(!$uemail){ continue; } 
			$emailText2 = $emailText;
			$logoutUrl = $this->base . '?noNews=1&u=';   
			$logout = ''; 
				
			if($id){    
				$logouturl = $logoutUrl  . urlencode(base64_encode($uemail.'__' . $id));
				$logout = '<a href="' . $logouturl . '">' . $logouturl . '</a>'; 
				if(!strpos($emailText2, '%%logout%%')){ 
					$emailText2 .= '<p>Odhlásit se můžete zde: %%logout%%</p>';   
				}  
				 
				$user = $id;
				
			} else {    
				$logout = $user = ''; 
			}
			 
			$emailText2 = str_replace('%%logout%%', $logout, $emailText2);     
			$emailText2 = str_replace('uxuxux', $user, $emailText2);     
			     
			$this->sendEmail($uemail, $name, $subject, $emailText2, $recipientsCount, $mailSender, $mailSenderName); 
		}    
		   
		return array(1, 'E-mail odeslán.');  
	}
	  
	function getSentEmails($params){
		return $this->db->fetchAll(
			"SELECT id, emailText, sentAt, sentTo 
			FROM $this->_tableNameEmails  
			ORDER BY id DESC 
			" 
		); 
	}
	
	function addSentEmail($emailText, $groups, $emails = array()){
		$groupSelected = '';
		if(count($groups)){
			$groupSelected = implode(', ', $groups);
		}
		
		if(count($groups) && $emails){
			$groupSelected .= ' / ';
		}
		
		if($emails){
			$groupSelected .= str_replace(',', ', ', $emails);     
		} 
		
		$this->db->insert(
			$this->_tableNameEmails,
			array(
				'emailText' => $emailText,
				'sentTo' => $groupSelected,
				'sentAt' => new Zend_Db_Expr('NOW()')  
			)
		); 
		
		return $this->db->lastInsertId();
	}
	
	function getOrigUrl($redirId){
		$redirId = intval($redirId);  
		$this->saveClick($redirId);
		return $this->db->fetchOne(
			"SELECT link FROM $this->_tableNameRedirects WHERE ident = ?",
			$redirId
		);
	}
	
	function saveClick($redirId){ 
		$where = $this->db->quoteInto('ident = ?', $redirId);
	 	$data = array('clicked' => new Zend_Db_Expr('clicked + 1'));
		$this->db->update(
			$this->_tableNameRedirects,
			$data, 
			$where);
	}
	
	function saveOpenAction($redirId){ 
		$where = $this->db->quoteInto('ident = ?', $redirId);
	 	$data = array('openedNumber' => new Zend_Db_Expr('openedNumber + 1'));
		$this->db->update(
			$this->_tableNameEmails,
			$data, 
			$where
		);
	}
	
	function saveOpen($redirId, $userId){ 
	
		$userId = $userId?$userId:0;
		$this->db->insert(
			$this->_tableNameOpenedDetails,
			array(
				'link' => $url,
				'emailId' => $emailId,
				'clicked' => 0 
			)
		);
	}
	
	function saveRedir($redirId, $userId){  
	
		$userId = $userId?$userId:0;
		$this->db->insert(
			$this->_tableNameRedirectsDetails,
			array(
				'link' => $url,
				'emailId' => $emailId,  
				'clicked' => 0 
			)
		);
	}
	
	function getRedirUrlAndSave($url, $emailId){ 
		$this->db->insert(
			$this->_tableNameRedirects,
			array(
				'link' => $url,
				'emailId' => $emailId,
				'clicked' => 0 
			)
		); 
		
		$redirId = $this->db->lastInsertId();
		
		return $this->base . '?redir=' . $redirId;
	}
	
	function addUtm($emailText, $utmString, $emailId){
		$newParts = array();
		$parts = explode('href="', $emailText);
		foreach ($parts as $part){
			$islink = strpos($part, 'http://') === 0; 
			if($islink){
				$url = $urlOrig =  current(explode('"', $part)); 
 				
				
				if(strpos($url, '#')){
					list($url, $add) = explode('#', $url);
					$add = '#' . $add; 
				} else {
					$add = '';
				} 
				if(strpos($url, '?')){
					$url .= '&u=uxuxux&';
				} else { 
					$url .= '?u=uxuxux&'; 
				}				
				 
				
				$url = $url . $utmString . $add;
				 
				//$part = str_replace($urlOrig, $url, $part); 
				if($this->addRedirectsAndStats){ 
					$redirUrl = $this->getRedirUrlAndSave($url, $emailId);
					$part = $redirUrl . mb_substr($part, mb_strlen($urlOrig, 'utf8'), 1000000, 'utf8');
				} else {
					$part = $url . mb_substr($part, mb_strlen($urlOrig, 'utf8'), 1000000, 'utf8');
				} 
			} else {    
				 
			}
			$newParts[] = $part;
		}
		$emailText = implode('href="', $newParts); 
		return $emailText;
	}
	
	function sendEmail($recipientEmail, $recipientName, $subject, $text, $usersCount, $mailSender, $mailSenderName){
		//	pr(func_get_args());
		//e('posilam na ' . $recipientEmail);  
		$emailFrom = $mailSender; 
		$emailFromName = $mailSenderName;  
		$mail = new Email();	
		// $mail->setHeaderEncoding(Zend_Mime::ENCODING_BASE64);    
 		 
		$emailText = $text;   
		
		
		$text = Helpers::prepareEmail($mail, $emailText, false, false, 'e00000', '000000', 'ffffff');			
		
		$mail->setBodyText(strip_tags($emailText)); 
		$mail->setBodyHtml(($text));		      
		$mail->setSubject($subject);  
		$mail->setFrom($emailFrom, $emailFromName);	 
		
	 	$mail->addTo($recipientEmail, $recipientName);   
	 	/*
		$mail->addTo('www@bestof.cz', 'Misan');    
		$mail->addTo('info@bestof.cz', 'Hranicka');    
		$mail->addTo('loucka@eportaly.cz', 'Zednik');          
		$mail->addTo('orlik.michal@gmail.com', 'mitch');  
		$mail->addTo('nosil@eportaly.cz', 'Mikrob');      
		   */
		// $mail->send();  
		
	 	// pr($mail); die(); 
				 
		if($usersCount <= 5){
			$mail->send();
		} else { 	  
			$mail->addToQueue();  
		}	
	}
	
	function userExists($email){ 
		return $this->db->fetchOne("SELECT email from `" . $this->_tableNameUsers ."` WHERE email = :u", array('u' => $email));  
	}
	
	function importFromCSV($groupSelected, $groupName){
		/*
		$groupExists = $this->getUsersCountByGroup($group);
		if($groupExists){
			$source = $group;
		} else {
			$groupIdent = Utils::generatePathNameSimple($group);
		}
		*/
		if($groupName){
			$groupSelected = $groupName;
		}  
		if ($_FILES['csv']['size'] > 0) {  
		    //get the csv file 
		    $file = $_FILES[csv][tmp_name]; 
		    $handle = fopen($file,"r");  
		     
		    //loop through the csv file and insert into database 
		    do { 
		    	$mm = explode(',', $data[0]);
		    	if(!is_array($mm)){
		    		$mm = array($mm);
		    	}
		    	foreach ($mm as $e){
		    		$e = trim($e);
			        if (strpos($e, '@') && !$this->userExists($e)) {  
			        	$this->db->insert(
							$this->_tableNameUsers,
							array( 
								'email' => $e,
								'source' => $groupSelected,
								'added' => new Zend_Db_Expr('NOW()')  
							)
						);       
			        }  
		    	}
		    } while ($data = fgetcsv($handle,1000,",","'"));  
			return array(1, 'Import proběhl v pořádku');
		} else {
			return array(0, 'Chyba importu'); 
		}		
	}
	
	function exportToCsv($input){  
		
		 
		foreach ($input->group as $ident => $ison){
			//if($ison > 0){
				$group  = $ident;
				break;
			//}
		}  
		
		$name = Utils::generatePathNameSimple($group) . '.csv';
		 
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=' . $name);
		
		// create a file pointer connected to the output stream
		$output = fopen('php://output', 'w');
		  
		// output the column headings
		fputcsv($output, array('E-maily'));  
		
		$rows = $this->db->fetchAll("SELECT email from `" . $this->_tableNameUsers ."` WHERE source = :u", array('u' => $group));
		foreach ($rows as $data){	 
			fputcsv($output, $data); 
		} 
		die(); 		
	}   
}	
?>