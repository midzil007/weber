<?php
/*
	Reklama
*/
class Cms_AdvertisingController extends CmsController
{
	public function init()
	{				
		parent::init();
						
		if($this->doPageInit){
			$this->initPage();
		}
	}
	
	private function initPage()
	{    	
		$this->view->title .= ' - Reklama';
		$this->template = 'controls/admin/modules/Advertising/Advertising.phtml';

		$this->mPPC = $this->view->mPPC = $mPPC = new module_Advertising_PPC();	
		$this->mAdresar = $this->view->mAdresar = $mAdresar = new module_Adresar();	
		 
		
		$this->view->showTree = false; 
		$this->view->showBottomPanel = true; 
		$this->view->bottomContentTitle = 'Výpis';
		
		
		$this->view->showAdvertsWidget = true;
		$this->view->selectedLeftHelpPane = false;  
		
		$this->aModule = $this->request->getParam('amodule');
		$this->aModule = $this->aModule ? $this->aModule : 'companies'; 
		
		
		switch ($this->aModule){
			default:
			case 'home':
				$action = 'list';
			break;
			case 'companies': 
				$action = 'adresarOverview';  
				$this->view->bottomContentHref = '/cms/pages/list/controler/pages/tree/1/nosortinfo/1/node/33295'; 
				break; 
			case 'articles':  
				$action = 'articlesOverview';  
			break; 
		} 
		if(!$this->view->bottomContentHref){
			$this->view->bottomContentHref = $this->view->url(array('controler' => 'events','action' => $action));
		}
		
		// e($this->aModule); 
		if($this->config->instance->domains){
			$this->view->domains = $this->config->instance->domains->toArray();
		} else {
			$this->view->domains = array($this->config->instance->webhost => $this->config->instance->webhost);
		} 
	}

	public function multiAction(){		
		parent::performMultiAction();
	}
	
	public function indexAction()
	{  		
		parent::indexAction($this->template);
	}
	
		
	public function homeAction()
	{  	
		switch ($this->aModule){
			default:
			case 'home':
				$template = 'Home';
			break;
			case 'ppc':
				$this->view->clients = $this->mPPC->getClients(); 
				$template = 'PPC'; 
			break;
			case 'companies': 
				//$this->view->clients = $this->mPPC->getClients(); 
				$template = 'Companies';  
			break;
			case 'adresar': 
				$template = 'Adresar';   
			break;
			case 'articles': 
				$template = 'Articles';    
				$this->view->inputGet->company = $this->request->getParam('company'); 
				$this->view->companiesSelect = $this->tree->getNodesAsSelect(33295);  
			break;
		} 
    	echo $this->view->render('controls/admin/modules/Advertising/' . $template . '.phtml');   	
	}
	 
	public function performMultiaction($action, $id){
		switch ($action){
			case 'delete':				
				
				$eId = $this->request->getParam('e');
				
				$e = $this->moduleEvents->getEvent($id);		
				parent::audit($e->title, '', 'delete');
		
				$this->moduleEvents->deleteEvent($id);
		
				break;
							
		}
	}
	
	public function showWidgetAction()
	{  	
    	echo $this->view->render('/controls/admin/modules/Advertising/AdvertisingWidget.phtml');
	}

	/*****************************************************************/
	/***********************        ARTICLES  PR        ***********************/
	/*****************************************************************/
	public function articlesOverviewAction()
	{	
		$this->view->inputGet->company = $this->view->companyId = $this->request->getParam('company'); 	
		
		if($this->view->companyId){
			
			$this->view->Locations = new module_Location(); 
			$this->view->mArticles = new module_Articles(); 
			$this->view->mArticles->inAdmin = 1; 
			$this->view->mArticles->companyId = $this->view->companyId;   
		
			$params = array(
				'company' => $this->view->companyId, 
				'simpleArray' => 1  
			); 
			$positions = $this->mAdresar->getPositions($params);
						
			$this->view->kraje = $this->view->Locations->getKraje(true);  
			
			$nodesDomainsMap = $this->config->instance->domains->toArray();
			$this->view->positionsByKraje = array();
			$this->view->domains = array();
			
			foreach ($positions as $position){ 
				$this->view->positionsByDomain[$position['domain']][] = $position;
				$this->view->positionsByKraje[$position['domain']][$position['kraj']][] = $position;   
				$this->view->domains[$position['domain']] = $nodesDomainsMap[$position['domain']]; 
			} 
		     	 
	    	parent::renderModal('controls/admin/modules/Advertising/ArticlesOverview.phtml');
		} else {
			echo ''; 
		}      			
	}  
	
	/*****************************************************************/
	/***********************        ADRESAR        ***********************/
	/*****************************************************************/
	public function adresarOverviewAction()
	{		
		$this->view->Locations = new module_Location();
    	$this->view->staty = $this->view->Locations->getStatesSelect();
    	$this->view->kraje = $this->view->Locations->getKraje(true);  
    	
    	$this->view->positions = $this->mAdresar->getPositions(); 
		     	 
    	parent::renderModal('controls/admin/modules/Advertising/AdresarOverview.phtml');     			
	}  
	
	public function newRecordAction()
	{		
		$this->input->domain = $this->request->getParam('domain');
		$this->input->kraj = $this->request->getParam('kraj');
		$this->input->section = $this->request->getParam('section');
		$this->view->input = $this->input;
		
		$this->view->companiesSelect = $this->tree->getNodesAsSelect(33295);  
		  
    	parent::renderModal('controls/admin/modules/Advertising/AdresarRecordForm.phtml');				
	} 

	public function editRecordAction()
	{		
		$zId = $this->request->getParam('zid');
		$z = $this->mAdresar->getPositionDetail($zId);	
		
		parent::initFormValuesFromArray($z, ''); 
		 
		$this->input->domain = $this->request->getParam('domain');
		$this->input->kraj = $this->request->getParam('kraj');
		$this->input->id = $zId;
		$this->input->section = $this->request->getParam('section');
		
		$this->input->from = $this->input->dateStart;
		$this->input->too = $this->input->dateEnd; 
		
		$this->view->input = $this->input;
		
		
		
		// pr($this->view->input); 
		
		$this->view->companiesSelect = $this->tree->getNodesAsSelect(33295);  
		  
    	parent::renderModal('controls/admin/modules/Advertising/AdresarRecordForm.phtml');				 
	} 
	
	public function saveAdresarRecordAction()
	{				
		$err = $this->checkAdresarRecordForm();
		if(!$err){ // ok 
			
			if($this->input->id){ 
				$this->mAdresar->updatePosition(
					$this->input->id, 
					$this->input->cid, $this->input->kraj, $this->input->section, $this->input->domain, 
					$this->input->from, $this->input->too  
				);
			} else {
				$this->mAdresar->addPosition(
					$this->input->cid, $this->input->kraj, $this->input->section, $this->input->domain, 
					$this->input->from, $this->input->too
				); 
			} 
			
			$company = $this->tree->getNodeById($this->input->cid);
			parent::audit('Zápis do adresáře - ' . $company->title . ' - sekce;' . $this->input->section . ' - kraj:' . $this->input->kraj );
			
			parent::addModalInfo(1,'Zápis uložen');  
		} else { 
			parent::addModalInfo(0,$err);
		}
	}
	
	public function checkAdresarRecordForm()
	{	
		do{	
			if($this->input->cid <=  0){ 
				$err = "Zadejte klienta";			   
			    break; 
			}	
			
			if(!$this->input->section || !$this->input->kraj){
				$err = "Kraj ? Sekce?";			   
			    break;
			}	 
				
			if(!$this->input->from){
				$err = "Zadejte datum začátku";			   
			    break;
			}	
			
 			if(!$this->input->too){
				$err = "Zadejte datum konce";			    
			    break;
			}	 
 			
			return false;
		} while (false);			
		return $err;
	}
	
	
	
	
	/*****************************************************************/
	/***********************        COMPANIES        ***********************/
	/*****************************************************************/
	
	/*****************************************************************/
	/***********************        PPC        ***********************/
	/*****************************************************************/
	
	public function newPPCClientAction()
	{		
    	parent::renderModal('controls/admin/modules/Advertising/PPCForm.phtml');				
	}  
	
	public function savePPCAction()
	{		
		 
		$err = $this->checkPPCForm();
		if(!$err){ // ok
			
			if($this->input->id){ 
				$this->mPPC->editClient(
					$this->input->id,
					$this->input->title, $this->input->email, $this->input->url, $this->input->price, 
					$this->input->from, $this->input->too, $this->input->domain
				);
			} else {
				$this->mPPC->addClient(
					$this->input->title, $this->input->email, $this->input->url, $this->input->price, 
					$this->input->from, $this->input->too, $this->input->domain
				); 
			} 
			
			parent::audit($this->input->title);
		
			parent::addModalInfo(1,'Klient uložen');  
		} else { 
			parent::addModalInfo(0,$err);
		}
	}

	public function checkPPCForm()
	{	
		do{	
			if(!$this->input->title || !$this->input->email || !$this->input->url){
				$err = "Zadejte klienta, URL i jméno";			   
			    break;
			}	
			
			if(!$this->input->price){
				$err = "Zadejte cenu";			   
			    break;
			}
				
			if(!$this->input->from){
				$err = "Zadejte datum začátku";			   
			    break;
			}	
			
 			if(!$this->input->too){
				$err = "Zadejte datum konce kampaně";			    
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
	
	public function editPPCAction()
	{
		$eId = $this->request->getParam('id');
		$e = $this->mPPC->getClientById( $eId );			
		parent::initFormValuesFromArray($e, ''); 
		
		$this->input->email = $this->input->clientEmail;
		$this->input->title = $this->input->clientName; 
		$this->input->url = $this->input->clientUrl;
		 
		$this->newPPCClientAction();		 		
	}
	
	public function listPPCAction()
	{  					

		$this->view->tableParentTab = 'vypisDole';
		$this->view->tableActions = array('delete' => 'Smazat');
			
		$this->view->curentViewState['action'] = 'listPPC';					 
		$this->view->tableSort = $this->request->getParam('sort')?$this->request->getParam('sort'):'clientName';			
		$this->view->tableSortType = $this->request->getParam('sortType')?$this->request->getParam('sortType'):'' ;
		$this->view->tableFilter0 = $this->request->getParam('tableFilter0')?$this->request->getParam('tableFilter0'):'active';
		$this->view->tableFilter1 = $this->request->getParam('tableFilter1')?$this->request->getParam('tableFilter1'):'all';
		
		$this->view->tableFilters[] = array('active'=>'Aktivní klienti', 'all' => 'Všichni klienti');
		$this->view->tableFilters[] = helper_Input::addNotDefinedOption($this->view->domains, 'Vše', 'all');
		
		$active = $this->view->tableFilter0 == 'active' ? 1 : 0;
		$domain = $this->view->tableFilter1 == 'all' ? 0 : $this->view->tableFilter1;
		
		
		$eId = $this->view->enquiryType;
		if(!is_numeric($eId)){
			$eId = $this->view->enquiryTypesNodes[$this->view->enquiryType];
		} 
		
		$ppc = $this->mPPC->getClients($active, $domain, $this->view->tableSort, $this->view->tableSortType); 
		$ppcUser = array();
		foreach ($ppc as $pp){
			$pp['domain'] = $this->view->domains[$pp['domain']];
			$pp['from'] = Utils::formatDate($pp['from']);
			$pp['too'] = Utils::formatDate($pp['too']); 
			
			$ppcUser[] = $pp;
		} 
		
		$this->view->ppcTable = $ppcUser; 
		
		
		$this->view->ppcTableHead = array(
			'clientEmail' => array(
				'title' => 'Email', 
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),	
			'clientName' => array(
				'title' => 'Klient',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),	
			'clientUrl' => array(
				'title' => 'WWW',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),	
			'price' => array(
				'title' => 'Cena za klik',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),	
			'from' => array(
				'title' => 'Aktivní od',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'too' => array(
				'title' => 'Aktivní do',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'domain' => array(
				'title' => 'Doména',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),	
		);  
		
		$this->view->ppcTableActions = array(
			'edit' => array(
				'title' => 'Editovat',
				'type'  => 'modal',
				'url'   => $this->view->url(array('action' => 'editPPC','id'=>'%$%'))
			)
		);
		
		/*
		,
			'delete' => array(
				'title' => 'Smazat',
				'type'  => 'tab-refresh',
				'tabId' => 'vypisDole',
				'url'   => $this->view->url(array('controller' => 'enquiry','action' => 'delete','id'=>'%$%'))
			),
				
			*/
    	echo $this->view->render('controls/admin/modules/Advertising/PPCList.phtml');   	 
	} 
	
	
	
	
	
	
	public function listAction()
	{  	
		return '';
	} 
	
	
	public function editAction()
	{
		$eventId = $this->request->getParam('e');
		$this->view->event = $this->moduleEvents->initEvent( $eventId );	
		parent::initFormValuesFromArray($this->view->event, 'event_');		
		$this->newEventAction();				
	}
	
	public function saveAction()
	{		
		
		//pr($this->input); 
		$err = $this->checkForm();
		if(!$err){ // ok
			if($this->input->event_id){
				$this->moduleEvents->updateEvent(
					$this->input->event_id,
					$this->input->event_title, 
					$this->input->event_detail, 
					$this->input->event_startAt, 
					$this->input->event_within, 
					$this->input->event_type,
					$this->input->photos
				);				
				parent::audit($this->input->event_title, '', 'edit');
			} else {
				$this->moduleEvents->addEvent(
					0,
					$this->input->event_title, 
					$this->input->event_detail, 
					$this->input->event_startAt, 
					$this->input->event_within, 
					$this->input->event_type,
					$this->input->photos
				);
				parent::audit($this->input->event_title);
			}
			
						
			parent::addModalInfo(1,'Data uložena');
		} else {
			parent::addModalInfo(0,$err);
		}
	}
	
	public function deleteAction()
	{			
		$eId = $this->request->getParam('e');
		
		$e = $this->moduleEvents->getEvent($id);		
		parent::audit($e->title);
		
		$this->moduleEvents->deleteEvent($eId);
		$this->listAction();
	}
	
	public function checkForm()
	{	
		//Utils::debug($this->input);
		do{	
			if(!$this->input->event_title){
				$err = "Zadejte název akce";			   
			    break;
			}		
			if(!$this->input->event_detail){
				$err = "Zadejte detailní informace";			   
			    break;
			}				
			if(!$this->input->event_startAt){
				$err = "Zadejte datum konání";			   
			    break;
			}			
			
			return false;
		} while (false);			
		return $err;
	}
	
}
