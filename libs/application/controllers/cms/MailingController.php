<?php
/*
	Shared files
*/
class Cms_MailingController extends CmsController
{
	public function init()
	{				
		$this->fields = array('recipients', 'fck_mailContent', 'mailSender', 'mailHead');
		parent::init();
						
		if($this->doPageInit)
			$this->initPage();
		
	}
	
	private function initPage()
	{  		
    	$showGroup = $this->request->getParam('userGroup');
		$this->view->selectedGroup = $showGroup;
		$this->view->showMailing = true;
		$this->view->mailingContentTitle = 'Odeslaná pošta';
		$this->view->mailingContentHref = $this->view->url(array('controler' => 'mailing','action' => 'sentMails'));
		
		$this->view->title .= ' - Emaily';
		$this->view->selectedLeftHelpPane = false;
		   
		$this->view->showTree = false;
		
		$this->emails = new Emails(); 
		
		$this->view->leftColl = $this->view->render('parts/leftMailing.phtml');  
		 
		$this->view->mMailing = new module_Mailing(); 
		 
		$this->calledFrom = $this->view->calledFrom = $this->request->getParam('calledFrom');
		
	
		
		$this->input->content = $this->input->content?$this->input->content:$defaultTxt;
		 
	}
	 
	public function multiAction(){		
		parent::performMultiAction();
	}
			
	public function indexAction()
	{ 		
		// $type = 'txt'; 
		// $t = ' - EMAIL';
// 		
		// $mm = $this->view->mMailing = new module_Mailing();    
// 
// 		  
		// $this->sendAction(); 
				$mm = new module_Mailing();   
		$params = array();
		$sent = $mm->getSentEmails();
		//pr($sent);
		//$this->view->pageContent =  $this->view->render('/controls/admin/tabs/MailingHome.phtml');
		$this->view->input = $this->input;
		if($this->input->action){
			$this->input->tableaction = $this->input->action;
			parent::performMultiAction();
		}
		$getItems = $this->request->getParam('getItems');
		$isAjax = $this->request->getParam('ajax');
		$nodeId = $this->request->getParam('node');
		$nodeId = $nodeId?$nodeId:1;		
		$this->view->defaultSort = 'id';
		$this->view->defaultSortType = 'desc';
		
		$dg = new DataGrid('pagesTable');
		$dg->setRefresUrl($this->view->url(array('action' => 'index', 'getItems' => 1, 'ajax' => 1)))
			->setTitle('Výpis odeslaných newsletterů') 
			->setHeight(400) 
			->setItemsPerPageOptions('10, 15, 20, 30, 50, 500', 50) 
			; 
			
		$dg->setHeaders( 
			array(
				array('Předmět', 'subject', 100, 'true', 'left', 'false'),  
				array('Odesláno', 'sentAt', 70, 'true', 'left', 'false'),
				array('Příjemci', 'sentTo', 70, 'true', 'left', 'false'), 
				array('Odesláno uživatelem', 'username', 70, 'true', 'left', 'false')
				)
		)->setSearchableColls(   
			array(    
				array('Název', 'title', 'true') 
			)
		)->setButtons( 
			array(  
			)
		);
		
		if($getItems){  
		   	list($currentPage, $rp, $sortname, $sortorder, $query, $qtype, $start) = $dg->getParams('sentAt', 'desc');
			//pr($params);
			$rowsTotalCount = $mm->getSentEmailsCount($sortname, $sortorder, 0, 1, $params); 
			$orders = $mm->getSentEmails($sortname, $sortorder, $start, $rp, $params);
			
			$nOrders = array();
			$rowsFormated = array();
			//pr($orders);	
			foreach ($orders as $order){   
				  	$editUrl = '/?previewnews='.base64_encode($order['id'].'#'.$order['username']);
					//   pr($order); die();
				//	$order['path'] =  $cp->getDetailUrl2($this->view, $order['parent'], $order['id'], $order['title']);
					$nOrder = array(); 
					//$nOrder['subject'] = '<a target="_blank" href='.$editUrl.'>'.$order['subject'].'</a>';
					$nOrder['subject'] = '<a target="_blank" href='.$editUrl.'>'.$order['subject'].'</a>';  		
					$nOrder['sentAt'] =  Utils::formatDate($order['sentAt']);
					
					$nOrder['sentTo'] = $order['sentTo'];  
					$nOrder['username'] = $order['username'];  
				//	$nOrder['path'] = Utils::getFrontEndLink( $order['path'], false, '', false, 0, $this->view);

				    
				$entry = array(   
					'id'=>$order['id'],
					'cell' => $nOrder
				);
				$rowsFormated[] = $entry;
			}
			  
			if($isAjax){
				$dg->renderAjax($currentPage, 2, $rowsFormated);
				die();  
			};
		}

		$this->view->pageContent = $dg->render($this->view, 'controls/admin/lists/PagesStandard.phtml');
		parent::indexAction('controls/admin/Mailing.phtml'); 
	} 
	 
	public function sendAction()
	{  	
		  
		if(isset($this->input->sendMail)){  
			list($state, $messages) = $this->view->mMailing->sendEmails($this->input,$this->session->user->id);   
			//Helpers::addMessage($state, $messages, $this);   
			if($state){
				$this->addInfoInstantMessage($messages);
			} else {  
				$this->addErrorInstantMessage($messages);
			}
			
		} 
		  
		$this->view->input = $this->input; 
		
    	$this->view->pageContent =  $this->view->render('/controls/admin/tabs/MailingHome.phtml');
		parent::indexAction('controls/admin/Mailing.phtml'); 
	} 
		
		
	public function usersAction()  
	{  	 
		
		  
		if(isset($this->input->import_csv)){
			list($state, $messages) = $this->view->mMailing->importFromCSV($this->input->groupSelected, $this->input->groupName);
			Helpers::addMessage($state, $messages, $this);
		}  
		if(isset($this->input->delete)){
			list($state, $messages) = $this->view->mMailing->delete($this->input);  
			Helpers::addMessage($state, $messages, $this); 
		}    
		if(isset($this->input->export)){  
			ob_clean();  
			list($state, $messages) = $this->view->mMailing->exportToCsv($this->input);  
			Helpers::addMessage($state, $messages, $this);  
		}
		
    	$this->view->pageContent =  $this->view->render('/controls/admin/forms/MailingRecipients.phtml');   
		parent::indexAction('controls/admin/Mailing.phtml');  
	} 

	public function sentAction()   
	{  	 
    	$this->view->pageContent =  $this->view->render('/controls/admin/forms/MailingSent.phtml');   
		parent::indexAction('controls/admin/Mailing.phtml');  
	} 
	
	
	public function homeAction()
	{  	
    	$this->view->pageContent =  $this->view->render('/controls/admin/tabs/MailingHome.phtml');
    	parent::indexAction('controls/admin/Mailing.phtml');
	} 
	
	public function newsletterAction()
	{  	
		$this->input = $this->view->input = (object) $_POST;

		if(isset($this->input->preview) || isset($this->input->sendMail)){
			$this->sendnewsletter(); 
		} 

    	$this->view->pageContent =  $this->view->render('/controls/admin/forms/MailingNewMailNewsletter.phtml');
    	parent::indexAction('controls/admin/Mailing.phtml');


	} 

	public function sendnewsletter()
	{		
		$this->input->recipients = trim($this->request->getPost('recipients')); // znovu nasetuju stripnotou promennou
		$mailing = new module_MailingNewsletter();
		$mailing2 = new module_Mailing();
		
		
		$products = $mailing->getProducts($this->input);  
		$mailText = $this->input->mailContent;
		$mailHead = $this->input->mailHead;
		$bannerUrl = $this->input->bannerUrl;
		$mailSender = $this->input->mailSender;
		
		$mailText = $this->input->mailContent; 
		if(strlen($mailText) < 7){ $mailText = ''; }
		/*  
		if($_FILES['banner']['size']){
			$upload = new HTTP_Upload("cz");						
			$file = $upload->getFiles("banner");	
			$uploadDir = $this->config->htdocsRoot . '/nl';
			$banner = '/nl/' . $file->moveTo($uploadDir);	
		} else {
			$banner = ''; 
		}*/
		if($this->input->banner){
			$banner = content_SFSFile::getFileFullPath($this->input->banner);
		} else {
			$banner = '';
		} 

		$this->view->emailBanner = $banner;
		$this->view->emailBannerUrl = $bannerUrl; 
		$this->view->emailText = $mailText; 
		

		$this->view->emailProducts = array(); 
		foreach ($products as $data){   
			list($path, $title) = $data; 
			$pNode = $this->tree->getNodeByPath($path); 
			if(!$pNode->nodeId){ continue; }
			$title = $title?$title:$pNode->title;    
			$pNode->emailTitle = $title; 
			$this->view->emailProducts[] = $pNode; 
		}  
		if(isset($this->input->preview)){ 
			 
			//   pr($this->input); die();  
			 
			echo $this->view->render('/parts/newsletter.phtml'); 
			die();
		} else { 
			$groups = array();
			foreach ($this->input->group as $ident => $ison){
				//if($ison > 0){
					$groups[] = $ident;
				// }
			} 
			$additionalRecievers = $this->input->additionalRecievers; 
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
			$recipients = $mailing2->getRecipients($groups); 
			$recipients = array_merge($recipients, $additionalRecieversA2);  
			$recipientsCount = count($recipients);  

 			 
			$sender = array('adress' => $this->input->mailSender, 'name' => $this->input->mailSenderName);
			  
			$this->view->inEmail = 1;
			$mailContent = $this->view->render('/parts/newsletter.phtml');  
			$this->view->inEmail = 0;
			$webContent = $this->view->render('/parts/newsletter.phtml');    
 			
			list($state, $messages) = $mailing->sendEmails($mailContent, $webContent, $recipients, $sender, $this->input->mailHead, $this->input);  
 
			if($state){ 
				// ulozim do odeslanych			
				$mail = new Email();
				$mail->save(
					$this->input->mailSender,
					$recipients, 
					$mailHead,
					urldecode($this->input->mailContent),
					true		
				);   
				
				parent::audit($this->input->mailHead);		 
				$this->session->mRecipients = array();
			}

 			
			$this->addInfoInstantMessage($messages);
		}
		
		
	}
}


