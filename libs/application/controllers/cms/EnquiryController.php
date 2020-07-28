<?php
/*
	Shared files
*/
class Cms_EnquiryController extends CmsController
{
	public function init()
	{				
		//$this->fields = array('event_title', 'event_detail', 'event_startAt', 'event_within', 'event_status', 'event_type');
		parent::init();
						
		if($this->doPageInit)
			$this->initPage();
		
	}
	
	private function initPage()
	{    	
		$this->view->title .= ' - Ankety';
		$this->template = 'controls/admin/modules/Enquiry/Enquiry.phtml';		
		$this->moduleEnquiry = $this->view->enquiry = new module_Enquiry();
		
		
		// e($this->view->enquiryType);
		 
		$this->view->enquiryTypes = array(
			'home' => 'na úvodní stránce',
			'intranet' => 'v intranetu',
			'webuser' => 'v profilu reg. uživatele'
		);
		
		if($this->view->config->modules->enquiry->types){
			$this->view->enquiryTypesNodes = $this->view->config->modules->enquiry->types->toArray(); 
		} else {
			$this->view->enquiryTypesNodes = array(
				'home' => '1',
				'intranet' => '99',
				'webuser' => '134'
			);	
		}
		
		$this->view->enquiryType = $this->request->getParam('enquiryType')?$this->request->getParam('enquiryType'):current(array_keys($this->view->enquiryTypesNodes));
		
		$this->view->showBottomPanel = true; 
		$this->view->bottomContentTitle = 'Seznam anket';
		$this->view->bottomContentHref = $this->view->url(array('controler' => 'enquiry','action' => 'list'));
		
		$this->view->showEnquiryWidget = true;
		$this->view->selectedLeftHelpPane = false;
	}

	public function multiAction(){		
		parent::performMultiAction();
	}
	
	public function indexAction()
	{  		
		parent::indexAction($this->template);
	}
	
	public function showWidgetAction()
	{  	
    	echo $this->view->render('/controls/admin/modules/Enquiry/EnquiryWidget.phtml');
	}
	
	public function homeAction()
	{  
		
    	echo $this->view->render('controls/admin/modules/Enquiry/EnquiryHome.phtml');   	
	}
		
	public function performMultiaction($action, $id){
		switch ($action){
			case 'delete':				
				$e = $id;		
				$eA = $this->moduleEnquiry->get($e);
				parent::audit($eA['title']);					
				$this->moduleEnquiry->delete($e);				
				parent::addInfoInstantMessage("Anketa smazána");
		
				break;
							
		}
	}
	
	
	public function listAction()
	{  					
		//parent::performMultiAction();
		        

		$this->view->tableParentTab = 'vypisDole';
		$this->view->tableActions = array('delete' => 'Smazat');
			
		$this->view->curentViewState['action'] = 'list';					
		$this->view->tableSort = $this->request->getParam('sort')?$this->request->getParam('sort'):'title';			
		$this->view->tableSortType = $this->request->getParam('sortType')?$this->request->getParam('sortType'):'' ;
		$this->view->tableFilter0 = $this->request->getParam('tableFilter0')?$this->request->getParam('tableFilter0'):'active';
		
		$this->view->tableFilters[] = array('active'=>'Aktivní ankety', 'past' => 'Ankety které už proběhly', 'all' => 'Všechny ankety');
		
		
		$eId = $this->view->enquiryType;
		if(!is_numeric($eId)){
			$eId = $this->view->enquiryTypesNodes[$this->view->enquiryType];
		} 
		
		$this->view->enquiryTable = $this->moduleEnquiry->getAll(
			$this->view->tableFilter0, 
			$this->view->tableSort, 
			$this->view->tableSortType,
			$eId
		);		
		
		$this->view->enquiryTableHead = array(
			'title' => array(
				'title' => 'Otázka',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),	
			'end' => array(
				'title' => 'Konec hlasování',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			)
		);
		
		$this->view->enquiryTableActions = array(
			'edit' => array(
				'title' => 'Editovat',
				'type'  => 'modal',
				'url'   => $this->view->url(array('controller' => 'enquiry','action' => 'edit','id'=>'%$%'))
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
    	echo $this->view->render('controls/admin/modules/Enquiry/EnquiryList.phtml');   	
	}
	
	public function newEnquiryAction()
	{
    	parent::renderModal('controls/admin/modules/Enquiry/EnquiryForm.phtml');				
	}
	
	public function editAction()
	{
		$eId = $this->request->getParam('id');
		$e = $this->moduleEnquiry->get( $eId );	
		
		parent::initFormValuesFromArray($e, 'enquiry_');
		//pr($this->input);
		$i=1; 
		$oldOptionsMap = array();
		//e($this->view->input->enquiry_options);
		foreach ($this->view->input->enquiry_options as $option){
			$oldOptionsMap[] = $option['id'] . '-' . $i;
			$this->view->input->{'enquiry_option' . $i} = $option['question'];
			$i++;
		}
		$this->view->oldOptionsMap = implode(';', $oldOptionsMap);
		$this->view->enquiryQuestionsCount = count($this->view->input->enquiry_options);
		$this->newEnquiryAction();				
	}
	
	public function saveAction()
	{		
		
		$this->options = $this->moduleEnquiry->parseOptions($this->input);
		$err = $this->checkForm();
		if(!$err){ // ok
			$this->moduleEnquiry->add(
				$this->input->enquiry_title, 
				$this->input->enquiry_description, 
				$this->input->enquiry_end,
				$this->view->enquiryTypesNodes[$this->view->enquiryType],
				$this->options
			);
			
			parent::audit($this->input->enquiry_title);
		
			parent::addModalInfo(1,'Anketa uložena');
		} else {
			parent::addModalInfo(0,$err);
		}
	}
	
	public function updateAction()
	{		
		
		$this->options = $this->moduleEnquiry->parseOptions($this->input);		
		$eId = $this->request->getParam('id');
		$err = $this->checkForm();
		
		if(!$err){ // ok
			
			//$this->moduleEnquiry->deleteOptions($eId); // smazu stare
			$this->moduleEnquiry->update(
				$eId,
				$this->input->enquiry_title, 
				$this->input->enquiry_description, 
				$this->input->enquiry_end,
				$this->options
			);			
			
			parent::audit($this->input->enquiry_title);
		
			parent::addModalInfo(1,'Anketa uložena');
		} else {
			parent::addModalInfo(0,$err);
		}
	}
	
	
	public function checkForm()
	{	
		do{	
			if(!$this->input->enquiry_title){
				$err = "Zadejte otázku";			   
			    break;
			}		
			if(!$this->input->enquiry_end){
				$err = "Zadejte datum, kdy proběhne vyhodnocení";			   
			    break;
			}	
			
			if(count($this->options) < 2){
				$err = "Zadejte alespoň 2 možné odpovědi";			   
			    break;
			}
			
			return false;
		} while (false);			
		return $err;
	}
	
}
