<?php
/*
	 SELECT t1 . * , t2.name
FROM `real_import_brookers` AS t1, real_import_companies AS t2
WHERE t1.`company` = t2.id 

*/
class Cms_UsersController extends CmsController
{

	public function init()  
	{
		
		$this->fields = array('username', 'password', 'password2', 'fullname', 'email', 'group', 'added', 'locked');
		parent::init();				 	
		if($this->doPageInit){
			$this->initPage();
		}
		
		 
	}
	
	private function initPage()
	{
    	//$showGroup = $this->_request->__get('userGroup');	
    	
		$this->view->title .= ' - Uživatelé';
		$this->template = 'controls/admin/Users.phtml'; 
		
		$this->users = $this->view->users = new Users();
		$this->webusers = new module_Customers();
		$this->webusers->fromMailingModule = true;
		
		$this->view->showBottomPanel = true;
		$this->view->bottomContentTitle = 'Výpis uživatelů';
		$this->view->bottomContentHref = $this->view->url(array('controler' => 'users','action' => 'list'));
		
		$this->view->showUsersWidget = true;
		$this->view->selectedLeftHelpPane = false;
		$this->view->showTree = false;
		//$this->audit->getUserAudit('a');
		 
		$this->view->leftColl = $this->view->render('parts/leftUsers.phtml');  
		$this->calledFrom = $this->view->calledFrom = $this->request->getParam('calledFrom'); 
	}
	
	public function homeAction()
	{  	
    	echo $this->view->render('/controls/admin/forms/UserHome.phtml');
	}
	
	public function performMultiaction($action, $id){
		switch ($action){
			case 'delete':				
				$this->deleteAction($id);				
				break;
			case 'deletewu':				
				$this->deleteWebuserAction($id);				
				break;
			case 'deleteAxa':				
				$this->deleteAxaAction($id);				
				break; 
			case 'deleteIB':				
				$this->deleteIBAction($id);				
				break;	
		}
	}
	
	public function showWidgetAction()
	{  	
    	echo $this->view->render('/controls/admin/forms/UserWidget.phtml');
	}
	
	public function listAction()
	{  					
		$getItems = $this->request->getParam('getItems');
		$isAjax = $this->request->getParam('ajax'); 
		parent::performMultiAction(); 
		
		$nodeId = $nodeId?$nodeId:1;
		 
		$params = array();
		$dg = new DataGrid('pagesTable');
		$dg->setRefresUrl($this->view->url(array('action' => 'list', 'getItems' => 1, 'ajax' => 1)))
			->setTitle('Výpis uživatelů')
			->setHeight(400)
			->setItemsPerPageOptions('10, 15, 20, 30, 50, 500', 50)
			; 
			
		$dg->setHeaders( 
			array( 
				array('Uživatel', 'fullname', 250, 'true', 'left', 'false'),
				array('Login', 'username', 120, 'true', 'left', 'false'), 
				array('Email', 'email', 120, 'true', 'left', 'false'),    
				array('Skupina', 'group', 120, 'true', 'left', 'false'),
				array('Založen', 'added',  60, 'true', 'left', 'false')
			)
		)->setSearchableColls(   
			array(    
				array('Název', 'fullname', 'true'), 
				array('Login', 'username', 'true') 
			)
		)->setButtons(
			array(  
				array('Smazat označené', 'delete', 'onpress', 'deleteu')  
			));        
		if($getItems){    
			$dg->isDebug(false);    
			$dg->setTableName('usr', 'Users')  
				->setSelectCols(array( 'u' => 'username', 'fullname', 'username', 'added', 'group', 'email'))  
				->getSelect($params);   
				    
			list($rowsTotalCount, $rows, $currentPage) = $dg->getRows('fullname', 'asc');  
			 
			//   e($rowsTotalCount);  pr($rows); 
			$rowsFormated = array();
			foreach($rows AS $row){ 
				if($this->session->user->group != 'Superadmin' && $row['group'] == 'Superadmin'){
					continue; 
				} 
				
				$editUrl = $this->view->url(array('controller' => 'users','action' => 'edit', 'u'=> $row['username'], 'ajax' => 0));
				  
				$entry = array(   
					'id'=>$row['username'],
					'cell'=>array(    
						'fullname'=> '<input name="chbx[' . $row['username'] . ']" type="checkbox" /> <a href="' . $editUrl . '">' . $row['fullname'] . '</a>',  
						'username'=> $row['username'],  
						'email'=> $row['email'],  
						'group'=> $row['group'], 
						'added'=> Utils::formatDate($row['added']) 
					),
				); 
				$rowsFormated[] = $entry;
			}
			
			if($isAjax){
				$dg->renderAjax($currentPage, $rowsTotalCount, $rowsFormated);
				die();  
			};
		}  
		$this->view->usersList = $dg->render($this->view, 'controls/admin/lists/Users.phtml') ;
	}
	
	public function webusersAction()  
	{  					 
		$getItems = $this->request->getParam('getItems');
		$isAjax = $this->request->getParam('ajax'); 
		parent::performMultiAction(); 
		
		$nodeId = $nodeId?$nodeId:1;
		 
		$params = array();
		$dg = new DataGrid('pagesTable');
		$dg->setRefresUrl($this->view->url(array('action' => 'webusers', 'getItems' => 1, 'ajax' => 1)))
			->setTitle('Výpis uživatelů')
			->setHeight(400) 
			->setItemsPerPageOptions('10, 15, 20, 30, 50, 500', 50)
			; 
			  
		$dg->setHeaders( 
			array( 
				array('Příjmení', 'prijmeni', 150, 'true', 'left', 'false'), 
				array('Jméno', 'jmeno', 150, 'true', 'left', 'false'), 
				array('Email', 'email', 140, 'true', 'left', 'false'),    
				array('Telefon', 'telefon', 70, 'true', 'left', 'false'),
				array('Odebírá novinky', 'mailing', 90, 'true', 'left', 'false'), 
				array('Ulice', 'ulice', 120, 'true', 'left', 'false'),
				array('Město', 'mesto', 120, 'true', 'left', 'false'),
				array('PSČ', 'psc', 40, 'true', 'left', 'false'),
				array('Registrace', 'added',  60, 'true', 'left', 'false')
			) 
		)->setSearchableColls(   
			array(    
				array('Příjmení', 'prijmeni', 'true'), 
				array('Jméno', 'jmeno', 'true'), 
				array('Email', 'email', 'true') 
			)
		)->setButtons(
			array(  
				array('Smazat označené', 'delete', 'onpress', 'deletewu')  
			));        
		if($getItems){     
			$dg->isDebug(false);    
			$dg->setTableName('usr', 'module_Customer')     
				->setSelectCols(array( 'id' => 'id', 'prijmeni', 'jmeno', 'email', 'telefon', 'mailing', 'ulice', 'mesto', 'psc', 'added'))  
				->getSelect($params);   
				    
			list($rowsTotalCount, $rows, $currentPage) = $dg->getRows('prijmeni', 'asc');  
			 
			//   e($rowsTotalCount);  pr($rows); 
			$rowsFormated = array();
			foreach($rows AS $row){  
				
				// $editUrl = $this->view->url(array('controller' => 'users','action' => 'webuseredit', 'u'=> $row['username'], 'ajax' => 0));
				  
				$entry = array(   
					'id'=>$row['username'],   
					'cell'=>array(     
						'prijmeni'=> '<input name="chbx[' . $row['id'] . ']" type="checkbox" />' . $row['prijmeni'] . '',   
						'jmeno'=> $row['jmeno'],  
						'email'=> $row['email'],   
						'telefon'=> $row['telefon'], 
						'mailing'=> $row['mailing']?'Ano':'Ne', 
						'ulice'=> $row['ulice'], 
						'mesto'=> $row['mesto'], 
						'psc'=> $row['psc'],    
						'added'=> Utils::formatDate($row['added']) 
					), 
				); 
				$rowsFormated[] = $entry;
			}
			
			if($isAjax){
				$dg->renderAjax($currentPage, $rowsTotalCount, $rowsFormated);
				die();  
			};
		}  
		$this->view->usersList = $dg->render($this->view, 'controls/admin/lists/Users.phtml') ;
		
		
    	parent::indexAction($this->template); 
	}
	
	public function multiAction(){		
		parent::performMultiAction();
	}
	 
	public function indexAction()
	{  	
		$this->view->showForm = false; // zorbzeni formulare
		$this->listAction();
    	parent::indexAction($this->template);		
    	
	}
			
	public function newAction()
	{   
		   
		if(isset($this->input->save)){   
			list($state, $message) = $this->saveAction();  
			if($state){
				$this->_redirector->goto('index', 'users', null);    
			} else {
				$this->addErrorInstantMessage($message );
			}   
		} 
		
		if(isset($this->input->update)){    
			list($state, $message) = $this->updateAction();  
			if($state){ 
				$this->_redirector->goto('index', 'users', null);    
			} else {
				$this->addErrorInstantMessage($message );
			}   
		}
		
    	$this->view->showForm = true; 
    	$this->view->pageContent = $this->view->render('controls/admin/forms/User.phtml'); 
		parent::indexAction($this->template);  			
	}
	
	public function newWebUserAction() 
	{
		$wu = new module_WebUsers();
		
		
		$wu->initCmsView($this->view);  
		$this->template = 'controls/admin/forms/WebUser.phtml';  
    	$this->view->showForm = true;
    	parent::renderModal($this->template);				
	}
	
    
	public function saveAction()
	{ 
		$err = $this->checkForm();
		if(!$err){ // ok
			$foto = '';
			if($this->fieldsValues['photo_fileSelect']){
				$foto = $this->fieldsValues['photo_fileSelect'] . ';' . $this->fieldsValues['photo_fileSelect_title'];
				$this->users->createPhoto($this->fieldsValues['photo_fileSelect']);
			}
			$this->fieldsValues['photo'] = $foto;
			unset($this->fieldsValues['password2']);
			unset($this->fieldsValues['save']);
			unset($this->fieldsValues['photo_fileSelect']);
			unset($this->fieldsValues['photo_fileSelect_title']); 
			$this->fieldsValues['password']	= sha1($this->fieldsValues['password']); 
			
			// pr($this->fieldsValues); die();
			
			$pKey = $this->users->insert($this->fieldsValues);
			
			parent::audit($this->fieldsValues['fullname']);
			return array(1,'Data uložena');
			//$this->_redirector->goto('index', 'users');  
		} else {
			return array(0,$err);   
		}
	}
	
	public function saveWebUserAction(){
		$wu = new module_WebUsers();
		$wu->addUser($this->inputGet);
		parent::addModalInfo(1,'Data uložena');
	}
		
	public function editAction()
	{
		$user = $this->request->getParam('u');
		$user = $this->users->getUser($user);
		if(!$this->request->isPost()){ // jinak nechame z postu data
			parent::initFormValuesFromArray($user);
			$this->view->password2 = $this->view->password = $this->view->input->password;
			//$this->fieldsValues['password2'] = $this->inputGet->password2 = $this->view->password2 = $this->inputGet->password;	
		}
		
    	$this->view->showForm = true;
    	$this->view->edit = true;
    	//$this->template = 'controls/admin/forms/User.phtml';
    	$this->view->pageContent = $this->view->render('controls/admin/forms/User.phtml'); 
		parent::indexAction($this->template);
	}
	
	public function editWebuserAction()
	{
		$user = $this->request->getParam('id');
		$mc = new module_Customers();
		$user = $mc->getUser2($user); 
		
		if(!$this->request->isPost()){ // jinak nechame z postu data
			parent::initFormValuesFromArray($user);
			$this->view->password2 = $this->view->password = $this->view->input->password;
			$this->fieldsValues['password2'] = $this->inputGet->password2 = $this->view->password2 = $this->inputGet->password;	
		}
		parent::renderModal('controls/admin/modules/Webusers/User.phtml');
	}
	
	public function infoWebuserAction()
	{
		$customers = new module_Customers();
		$user = $this->request->getParam('id');
		$user = (array) $customers->getUser($user);  
		parent::initFormValuesFromArray($user);
		require_once('content/cpMap.php');
		$this->view->cp_Translate = $_cpMap;			
		parent::renderModal('controls/admin/modules/Webusers/UserDetails.phtml');
	}
	
	public function updateWebuserAction()
	{		 
		$err = $this->checkFormEditWU(); 
		if(!$err){ // ok
			$userData = $this->inputGet;
			$mc = new module_Customers();   
			$mc->updateUser2($userData->id, $userData);
    		parent::audit($userData->jmeno  . ' ' . $userData->prijmeni);
    		parent::addModalInfo(1,'Data uložena');
		} else {
			parent::addModalInfo(0,$err);
		}
	}
	
	public function updateAction()
	{		
		//pr($this->fieldsValues); pr($this->inputGet); return;
		$this->view->edit = true;
		$this->fieldsValues['np'] = $this->input->password_new;
		$this->fieldsValues['np2'] = $this->input->password2_new;
		
		$err = $this->checkFormEdit(); 
			 
		if(!$err){ // ok			  
			 
			$this->fieldsValues['password'] = $this->fieldsValues['np']?sha1($this->fieldsValues['np']):$this->fieldsValues['password'];
			
			unset($this->fieldsValues['password2']);
			unset($this->fieldsValues['np']);  
			unset($this->fieldsValues['np2']);
			unset($this->fieldsValues['update']);
			unset($this->fieldsValues['password_new']);
			unset($this->fieldsValues['password2_new']);
			
			//fotka
			$foto = '';
			if($this->fieldsValues['photo_fileSelect']){
				$foto = $this->fieldsValues['photo_fileSelect'] . ';' . $this->fieldsValues['photo_fileSelect_title'];
				$this->users->createPhoto($this->fieldsValues['photo_fileSelect']);
			}
			$this->fieldsValues['photo'] = $foto;
			
			unset($this->fieldsValues['password2']);		
			unset($this->fieldsValues['photo_fileSelect']);		
			unset($this->fieldsValues['photo_fileSelect_title']);	
			    
			$pKey = $this->users->update($this->fieldsValues, "id = ".$this->fieldsValues['id']."");
				
    		parent::audit($userData['fullname']);
    		return array(1,'Data uložena');;
		} else {
			return array(0,$err);
		} 
	}
	
	
	public function deleteWebuserAction($uid = 0)
	{		  
		$user = $uid?$uid:$this->request->getParam('id');		
		if($user){
			$mc = new module_Customers(); 
			$udata = $mc->getUser2($user);
			$fullname = $udata['jmeno'] . ' ' . $udata['prijmeni'];   
			$mc->deleteUser($user);			  
	    	parent::audit($fullname, '', 'deleteWebuser');	
	    	parent::addErrorInstantMessage("Uživatel webu $fullname smazán.");
		}
    	if(!$uid){
    		//$this->listWebusersAction();
    	}
		//$this->_redirector->goto('index', 'users');
	}
		
	public function deleteAction($uid = 0)
	{		
		$user = $uid?$uid:$this->request->getParam('u');	
		$user = str_replace('_', '.', $user);
		if($user){
			$where = $this->users->adapter->quoteInto('username = ?', $user);
			
			$udata = $this->users->getUser($user);
			$fullname = $udata['fullname'];
					
			$this->users->delete($where);			
	    	parent::audit($fullname, '', 'delete');	
	    	parent::addErrorInstantMessage("Uživatel $user smazán.");
		}
		
    	if(!$uid){
    		//$this->listAction();
    	}
    	
		//e($this->view->url(array('action' => 'list')));
		//$this->_redirector->goto($this->view->url(array('action' => 'list')));
		//$this->_redirector->gotoUrlAndExit ($this->view->url(array('action' => 'list', 'u' => 0)));
				
	}
	
	public function deleteIBAction($uid = 0)
	{		
		$userId = $uid?$uid:$this->request->getParam('id');		
		e($userId);
		if($userId){		
			$brookers = new module_reality_ImportedBrookers();	
			$brookers->deleteUser($userId);			
		}
    	
	}
	
	public function checkForm()
	{	
		//pr($this->fieldsValues);	
		  //   pr($this->input); exit();
		do{	 
			if(!$this->input->fullname){
				$err = "Zadejte jméno";			   
			    break;
			}		
			if(!$this->input->username){
				$err = "Zadejte login";			   
			    break;
			}	
			
			if(!$this->input->password){
				$err = "Zadejte heslo";			   
			    break;
			}			
			
			if($this->input->password != $this->input->password2){
				$err = "Hesla se neshodují";			   
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
	
	
	public function checkFormEditWU()
	{	
		//pr($this->fieldsValues);	
		//pr($this->input); exit();
		do{	
			if(!$this->inputGet->jmeno || !$this->inputGet->prijmeni){
				$err = "Zadejte jméno";			   
			    break;
			}	  		
			$validator = new Zend_Validate_EmailAddress();			
			if (!$validator->isValid($this->inputGet->email)) {			    
				$err = current($validator->getMessages());			   
			    break;
			}
			return false;
		} while (false);			
		return $err;
	}
	public function checkFormEdit()
	{	
		//pr($this->fieldsValues);	
		//pr($this->input); exit();
		do{	
			if(!$this->input->fullname){
				$err = "Zadejte jméno";			   
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
	
	/* AUDIT */
	public function auditAction()
	{
		$this->template = 'controls/admin/forms/UserAudit.phtml'; 
    	$this->view->showForm = true;
    	parent::renderModal($this->template);				
	}
	
	function cmp_stat($u1, $u2) {
		return strcoll( $u1['stat'],  $u2['stat']);
	}
	
	function cmp_statDesc($u1, $u2) {
		return $this->cmp_stat($u1, $u2) * -1;
	}
	
	
	function cmp_firstname($u1, $u2) {
		return strcoll( iconv("UTF-8", "ISO-8859-2",$u1['firstname']),  iconv("UTF-8", "ISO-8859-2",$u2['firstname']));
	}
	
	function cmp_firstnameDesc($u1, $u2) {
		return $this->cmp_firstname($u1, $u2) * -1;
	}
	
	function cmp_surname($u1, $u2) {
		return strcoll( iconv("UTF-8", "ISO-8859-2",$u1['firstname']),  iconv("UTF-8", "ISO-8859-2",$u2['firstname']));
	}
	
	function cmp_surnameDesc($u1, $u2) {
		return $this->cmp_surname($u1, $u2) * -1;
	}
	
	public function listUsersByParamGroupedAction()
	{  			
		
		switch ($this->calledFrom){
			case 'mailingClubMembers':
				$this->view->tableParentTab = 'recipientsClubMembersGroups';
				$this->view->tableActions = array();	
				$template = '/controls/admin/forms/MailingUserListGrouped.phtml';
				$this->view->usersTableActions = array();
				$this->view->tableForceCheckboxes = true;
				break;
			default:

				break;			
		}
								
		switch ($this->request->getParam('param')){
			default:		
				$this->view->groups = array();
				break;
			case 'clubMembersGroups':								
				$cm = new module_ClubMembers();
				$this->view->groups = $cm->getAvailableGroups();
				break;
		}
							
    	echo $this->view->render($template);
	}  
	 
}
