<?php
/**  Po davkach rozesila emaily. */

class module_NewsSender {	
	function __construct() {
		$this->db = Zend_Registry::getInstance()->db;	
		$this->_tableName = 'module_NewsSender';
	}
	
	function addNewsToQueue(){
		
		$articlesToAdd = $this->getNewArticles();
		if(!count($articlesToAdd)){
			return;
		}
		$webUsers = new module_WebUsers();		
		$allUsers = $webUsers->getUsers(null, false, 'id', 'asc', array(), $limitStart = 0, $limitCount = 10000, true);
		
		foreach ($articlesToAdd as $art){
			$this->addToSendingList($art, $allUsers);
			$this->db->insert(
				$this->_tableName,
				array(
					'newsId' => $art['id']
				)
			);
		}		
	}
	
	function getNewArticles(){
		$articlesModule = new module_Articles();	
		$all = $articlesModule->getArticles('dateShow', 'Desc', 5, 1);	
		foreach ($all as $i => $article){
			$sent = $this->db->fetchOne("SELECT `newsId` from `" . $this->_tableName ."` WHERE newsId = :id", array('id' => $article['id']));
			if($sent){
				unset($all[$i]);
			}
		}
		return $all;
	}
		
			
	function addToSendingList($article, $users){
		
		$desc = $article['pretext']?$article['pretext']:helper_FrontEnd::reduceText(strip_tags($article['html']), 50);
		$url = 'http://www.okultweb.cz' . Utils::getPath($article['path']);
		$emailText2 = '
		<p>Na základě Vaší žádosti Vám zasíláme informaci, že na <a href="http://www.okultweb.cz" target="_blank">www.okultweb.cz</a> byl přidán článek.</p>
		<strong>'. $article['title'] . '</strong>
		<p>' . $desc .' <a href="' . $url . '" target="_blank">zobrazit článek &raquo;</a></p>
				
		<br /><br />
		OKULTweb.cz – esoterický portál<br />
		<a href="http://www.okultweb.cz" target="_blank">www.okultweb.cz</a>
		';
		$sender = 'OKULTweb.cz - esoterický portál';
		$senderURL = 'noreply@okultweb.cz';
		
		$emailText = Helpers::prepareEmail(
			$mail,
			$emailText2
		);
		
		
		foreach ($users as $user){			
			if(!$user->email){ continue; }
									
			$mail = new Email();					
			$mail->setBodyText(strip_tags(urldecode($emailText2)));
			$mail->setBodyHtml(urldecode($emailText));			
				
			$mail->setSubject('Nový článek na OKULTweb.cz');
						
			$mail->setFrom($senderURL, $sender);		
	
			$mail->addTo($user->email, $user->getName(true));
			//$mail->addTo('orlik.michal@gmail.com', 'Michal Orlik');
			//$mail->addTo('hanzal@astroweb.cz', 'Pavel Hanzal');
			
			//$mail->send();
			//die();
			//e($mail); die();
			$mail->addToQueue();			
		}
		
	}
}


?>