<?php
/*
	Shared files
*/
class Cms_EventsController extends CmsController
{
	public function init()
	{				
		//$this->fields = array('event_title', 'event_detail', 'event_startAt', 'event_within', 'event_status', 'event_type');
		parent::init();
						
		if($this->doPageInit){
			$this->initPage();
		}
	}
	
	private function initPage()
	{    	
		$this->view->title .= ' - Kalendář akcí';
		$this->template = 'controls/admin/modules/Events/Events.phtml';
		
		$this->moduleEvents = new module_Events();
		
		$this->view->showTree = false;
		$this->view->showBottomPanel = true;
		$this->view->bottomContentTitle = 'Výpis';
		$this->view->bottomContentHref = $this->view->url(array('controler' => 'events','action' => 'list'));
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
		$this->view->days = $this->moduleEvents->createCalendar();
		//$this->view->days = $this->moduleEvents->getEvents();
		//$this->moduleEvents->addEvent('xx','node', '11','12','normal');
		//$this->moduleEvents->initEvent( 1 );		
    	echo $this->view->render('controls/admin/modules/Events/EventsHome.phtml');   	
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
	
	public function listAction()
	{  					
		//parent::performMultiAction();
		
		$this->view->tableActions = array('delete' => 'Smazat');			
		$this->view->curentViewState['action'] = 'list';					
		$this->view->tableSort = $this->request->getParam('sort')?$this->request->getParam('sort'):'startAt';			
		$this->view->tableSortType = $this->request->getParam('sortType')?$this->request->getParam('sortType'):'Desc' ;
		$this->view->tableFilter0 = $this->request->getParam('tableFilter0')?$this->request->getParam('tableFilter0'):'new';
		
		$this->view->tableFilters[] = array(
			'new' => 'Nadcházející akce',
			'all' => 'Všechny akce'
		);
				
		
		$this->view->eventsTable = $this->moduleEvents->getEvents(
			$this->view->tableFilter0, 
			$this->view->tableSort, 
			$this->view->tableSortType,
			false
		);
		
		$this->view->eventsTableHead = array(
			'title' => array(
				'title' => 'Název',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),	
			'startAt' => array(
				'title' => 'Datum konání',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),	
			'type' => array(
				'title' => 'Typ akce',
				'atribs' => array( 'style' => 'width:120px;'),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			)
		);
		$this->view->eventsTableActions = array(
			'edit' => array(
				'title' => 'Editovat',
				'type'  => 'modal',
				'url'   => $this->view->url(array('controller' => 'events','action' => 'edit','e'=>'%$%'))
			)				
		);
		
    	echo $this->view->render('controls/admin/modules/Events/EventsList.phtml');   	
	}
	
	public function newEventAction()
	{
		$this->view->types = $this->moduleEvents->getEventTypes();
		if($this->config->instance->eventsAllowFiles){
			$this->view->files = new ContentProperty('photos','MultiFileSelect',$this->input->event_photos, array(), array(), array('showSelectFile' => true, 'inputWidth' => '250', 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => $reg->config->instance->eventsFolderNodeId ));   		    	
		}
    	parent::renderModal('controls/admin/modules/Events/EventNew.phtml');				
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
