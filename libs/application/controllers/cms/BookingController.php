<?php
/*
	Booking module
*/
class Cms_BookingController extends CmsController
{

	public function init()
	{
		parent::init();		
		 				
		if($this->doPageInit)
			$this->initPage();
		
	}
	
	private function initPage()
	{  		
    	//$showGroup = $this->_request->__get('userGroup');	
    	
		$this->view->title .= ' - Rezervace';
		$this->template = 'controls/admin/modules/Booking/Booking.phtml'; 
		$this->view->showBottomPanel = true;
		$this->view->bottomContentTitle = 'Výpis rezervací';
		$this->view->bottomContentHref = $this->view->url(array('action' => 'list'));
		
		$this->view->selectedLeftHelpPane = false;
		$this->view->showTree = false;
		//$this->audit->getUserAudit('a');
		
    	$this->booking = $this->view->bookingModule = new module_Booking_Booking(); 
    	
		$this->view->leftPanes[] = array(
			'title' => 'Rezervace',
			'id' => 'BookingLeftPane',
			'class' => '',
			'url' => $this->view->url(array('action' => 'showTree'))		
		);
		 
		$this->itemType = $this->request->getParam('item');
		$this->itemType = $this->itemType?$this->itemType:1;
		
		$aH1 = array(
			1 => 'Pokoje',
			'Lodě',
		);
		 
		$this->view->h1TitleForced = $aH1[$this->itemType];  
		//$this->booking->confirm('1-8-1238151600-1238583600-49be26d13b4f5', $this);   
		//die();  
	}
	
	public function homeAction()
	{  			
		$this->view->contentType = $this->view->BookingCategories[$this->category]['content_type'];
		$this->view->contentTypeName = $this->view->BookingContentTypes[$this->view->contentType];		
		$this->view->categoryName = $this->registry->Booking->advert_types[$this->advert_function];
		
    	echo $this->view->render('controls/admin/modules/Booking/BookingHome.phtml');
	}
	
	public function settingsAction()
	{  		
		$this->view->rows = array()	;
		$rows = $this->db->fetchAll('SELECT id, persons, price, label, label_en FROM module_Booking_rooms');		 
		foreach ($rows as $row){
			$id = $row['id'];
			unset($row['id']);
			$this->view->rows[$id] = $row; 
		}
    	echo $this->view->render('controls/admin/modules/Booking/Settings.phtml'); 
	}
	
	public function saveSettingsAction()
    {     	
    	foreach ($this->input as $k => $v){ 
    		if(strpos($k, '__')){
    			list($id, $colName) = explode('__', $k); 
    			$this->db->update(
    				'module_Booking_rooms',
    				array(
    					$colName => $v
    				),
    				$this->db->quoteInto('id = ?', $id)   
    			);
    		}
    	}
    	parent::addModalInfo(1,'Nastavení uloženo');
    	
    }
    
	public function multiAction(){		
		parent::performMultiAction();
	}
	
	public function performMultiaction($action, $id){
		//'sell' => 'Prodáno', 'erase' => 'Odstranit z nabídky', 'delete' => 'Úplně odstranit' 'return' => 'Vrátit zpět do nabídky'
		switch ($action){
			case 'confirm':				
				$this->confirmAction($id);				
				break;
			case 'confirmDS':				
				$this->confirmAction($id, false);				
				break;
			case 'abandon':				
				$this->abandonAction($id);				
				break;
			case 'abandonDS':				
				$this->abandonAction($id, false);				
				break;
			case 'delete':				
				$this->deleteAction($id, false);				
				break;
		}
		
	}
	
	public function confirmAction($id = 0, $sendEmail = true)	{
		$this->booking->confirm($id, $this->view, $sendEmail);
		parent::audit($id,  'confirm');
	}
	
	public function abandonAction($id = 0, $sendEmail = true)	{ 
		$this->booking->abandon($id, $this->view, $sendEmail);
		parent::audit($id,  'abandon');
	}
	
	public function deleteAction($id = 0)	{  
		$this->booking->delete($id, $this->view);  
		parent::audit($id,  'delete');
	}
	
	public function showTreeAction()
	{  	
    	echo $this->view->render('controls/admin/modules/Booking/BookingTree.phtml');
	}
		
	public function indexAction()
	{  	
    	parent::indexAction($this->template);		
    	
	}
	
	public function newAction()
	{		
		$this->view->isEdit = false;		
		parent::renderModal('controls/admin/modules/Booking/BookingDetail.phtml');
	}
	
	public function detailAction()
	{	
		$this->view->isEdit = true;	
		$bookingItem = $this->request->getParam('BookingNode'); 
		$this->booking->initDetailView($bookingItem, $this->view); 
		
		//$faktura = new module_Doklad();
		//$faktura->initData($bookingItem);
		//$faktura->
		$this->view->faktura = '/data/faktury/' . $bookingItem . '.pdf';    
		parent::renderModal('controls/admin/modules/Booking/BookingDetail.phtml');
	}
	
	public function listAction()
	{  	
		//$this->confirmAction('1-4-1215424800-1215770400-48561f16163d2');
		
		parent::performMultiAction();
		$this->view->tableActions = array(
			
			'abandon' => 'Zrušit rezervaci',
			'abandonDS' => 'Zrušit rezervaci a neodeslat email', 
			'delete' => 'Smazat' 
		); 
 		 
		if($this->itemType == 1){
			$tableActions2 = array( 
				'confirm' => 'Potvrdit rezervaci',
				'confirmDS' => 'Potvrdit rezervaci a neodeslat potvrzovací email'
			); 
			$this->view->tableActions = array_merge($tableActions2, $this->view->tableActions);
		}
		
		
		$this->booking->initBackend();
				
		$this->view->curentViewState['action'] = 'list';	
					
		$this->view->tableSort = $this->request->getParam('sort')?$this->request->getParam('sort'):'customer';			
		$this->view->tableSortType = $this->request->getParam('sortType')?$this->request->getParam('sortType'):'';
		$this->view->tableFilter0 = $this->request->getParam('tableFilter0')?$this->request->getParam('tableFilter0'):'all';
		
		if($this->itemType == 2){
			$state = 'engaged'; 
		} else {
			$state = 'reserved'; 
		}
		$this->view->tableFilter1 = $this->request->getParam('tableFilter1')?$this->request->getParam('tableFilter1'):$state; 
		
		$this->view->tableParentTab = 'vypisDole';							
		$sortFunction = $this->view->tableSort . $this->view->tableSortType;
		// e($this->view->tableFilter0);  
		$reservations = $this->booking->getReservations($this->view->tableFilter1, $this->view->tableFilter0, $this->view->tableSort, 0, $this->view->tableSortType, $this->itemType);
		
		
		// table			
		$this->view->contentsTable = $reservations;
		  
		$this->view->tableFilters[0] = array(
			'all' => 'Vše'
		);
		
		
		foreach ($this->view->contentsTable as $i => $r){
			$this->view->tableFilters[0]['i-' . $r['itemId']] = $r['item'];
			unset($r['itemId']);
			unset($r['firstStamp']);
			unset($r['lastStamp']);
			unset($r['createdDate']);
				
			$this->view->contentsTable[$i] = $r;
		}
		
		$filter2 = $this->booking->states;
		unset($filter2['free']);
		
		$this->view->tableFilters[1] = $filter2;
		
		//pr($this->view->tableFilters);		
		
		//pr($this->view->contentsTable);
		$this->view->contentsSubmitFormUrl = $this->view->url(array('action' => 'list'));
		$this->view->contentsTableParentTab = 'vypisDole';
				
		$this->view->contentsTableHead = array(			
			'customer' => array(
				'title' => 'Zákazník',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'item' => array(
				'title' => ($this->itemType==2?'Loď':'Pokoj'), 
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'from' => array(
				'title' => 'Od',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'too' => array(
				'title' => 'Do',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'duration' => array(
				'title' => 'Trvání',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			), 			
			'email' => array(
				'title' => 'Email',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'phone' => array(
				'title' => 'Telefon',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'identification_number' => array(
				'title' => 'RČ',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'sum' => array(
				'title' => 'Celkem',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'created' => array(
				'title' => 'D. rezervace',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'state' => array(
				'title' => 'Status',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			)
		);		
		
		
		$this->view->contentsTableActions = array();		
		
		$this->view->contentsTableActions['detail'] = array(
			'title' => 'Detail obsahu',
			'type'  => 'modal',
			'url'   => $this->view->url(array('action' => 'detail', 'BookingNode'=>'%$%'))
		);
		  
    	echo $this->view->render('controls/admin/modules/Booking/BookingList.phtml');
	}
	
	
	public function formAction()
	{	
		$this->view->isEdit = $this->request->getParam('isEdit')?true:false;
		// mapa contenProperties na cesky 
		require_once('content/cpMap.php');
		$this->view->cp_Translate = $_cpMap;
    	if($this->view->isEdit){
    		
    		
			$this->view->content  = $this->node->getTheRightContent();	
			$this->view->contentType = $this->view->content->_name;		
		} else {
			$this->view->contentType = $ctype = $this->request->getParam('contentType');
			$this->view->content  = Content::getNewContent($this->view->contentType);	
		}		
		parent::renderModal('controls/admin/modules/Booking/Form.phtml');
	}
	
	public function printAction()
	{	
		$this->view->isEdit = $this->request->getParam('isEdit')?true:false;
		// mapa contenProperties na cesky 
		require_once('content/cpMap.php');
		$this->view->cp_Translate = $_cpMap;
    	if($this->view->isEdit){   		
			$this->view->content  = $this->node->getTheRightContent();	
			$this->view->contentType = $this->view->content->_name;		
		} else {
			$this->view->contentType = $ctype = $this->request->getParam('contentType');
			$this->view->content  = Content::getNewContent($this->view->contentType);	
		}		
		
		$this->view->BookingTitle = $this->view->BookingContentTypes[$this->view->contentType];
		$this->view->useDojo = false;
		$this->view->isPrint = true;
		
		$this->view->title = 'Bleskové Booking (www.bleskoveBooking.cz)';
		echo $this->view->render('controls/admin/modules/Booking/FormPrint.phtml');
	}
	
	public function saveAction()
	{
				
		//pr($this->input);return ;
		$err = $this->checkFormNewPage();
		if(!$err){ // ok	
			
			//content
			//$this->input->state = 'PUBLISHED';
			$ctype = $this->input->contentType;
			$content = Content::init($ctype, $this->input, $this->acl);	
			$err2 = $content->save();
		
			//node				
			$parentId = $this->request->getParam('parentId');						
			$n = Node::init('ITEM', $parentId, $this->input, $this->view);
			
			//save		
	    	$this->tree->addNode($n);
	    	$this->tree->pareNodeAndContent($n->nodeId, $content->id, $content->_name);
	    		    	
	    	parent::audit($n->title, $n->nodeId);
	    	
			//parent::addModalInfo(1, 'Data uložena', 0, $this->view->url(array('module' => 'cms', 'controller' => 'pages','action' => 'list', 'node' => $this->nodeId), null,true));
			parent::addModalInfo(1, 'Data uložena');
		} else {
			parent::addModalInfo(0,$err);
		}				
	}
		
	public function updateAction()
	{		
	//	pr($this->input); return ;
				
		$err = $this->checkFormEditPage();
		if(!$err){ // ok				
					
			$node = $this->request->getParam('BookingNode');
    		$this->node = $this->view->node = $this->tree->getNodeById($node);
    	
			// content			
			$contentId = $this->request->getParam('contentId');
			$inVersions = true;
			if(!$contentId){
				$contentId = $this->node->getTheRightContent()->id;
				$inVersions = false;
			}
			//$contentId=$contentId?$contentId:$this->input->
			
			$this->view->content = Content::initUpdate($this->node, $this->input, $contentId);			
			$this->view->contentId = $contentId;		
			$this->view->content->update();
						
			//node
			$this->node->initUpdate($this->input, $this->view);				    	  	
	    	$this->tree->updateNode($this->node); 	    	
	    	
	    	//$this->node->checkStateChange($this->input, $this->view->content);
	    		    	
	    	parent::audit($this->node->title, $this->node->nodeId);	    	
			
			if($inVersions){ // ve verzich
				parent::addModalInfo(1, 'Data uložena', '',  $this->view->url(array('action' => 'versions', 'node' => $this->node->nodeId)));
			} else {
				parent::addModalInfo(1, 'Data uložena');
			}
			
		} else {
			parent::addModalInfo(0,$err);
		}				
	}
	
	public function checkFormEditPage()
	{			
		do{	
			if(!$this->input->pageTitle){
				$err = "Zadejte nadpis inzerátu";			   
			    break;
			}				
			return false;
		} while (false);			
		return $err;
	}
	
	public function checkFormNewPage()
	{		
		//pr($this->inputGet);
		//exit();		
		do{	
			if(!$this->input->pageTitle){
				$err = "Zadejte nadpis inzerátu";			   	   
			    break;
			}				
			return false;
		} while (false);			
		return $err;
	}
	
}
