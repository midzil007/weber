<?php
 
class CmsController extends Zend_Controller_Action
{
	
	protected $_flashMessenger = null;
	protected $context = null;

    public function init()
    {
    	
	   
    	$this->session = new Zend_Session_Namespace('CMS7');
    	    	
    	$this->auth = $this->getInvokeArg('auth');    	    	
		$this->view = $this->getInvokeArg('view');
		$this->acl = $this->view->acl = $this->getInvokeArg('acl');
		
		$this->registry = $this->view->registry = Zend_Registry::getInstance();
    	$this->registry->request = $this->request = $this->view->request = $this->getRequest();
    	$this->view->requestParams = $this->request->getParams();
    	//unset($this->view->requestParams['sysnode']);	 
    	
    	$this->config = $this->view->config = $this->registry->config;
    	
		  
    	// jazyky webu
    	$this->languages = $this->view->languages = $this->registry->languages = new module_languages_Languages($this->view);    	
    	$this->language = $this->view->language = $this->registry->language = $this->languages->getCurentLanguage(); 
    	
    	$this->view->logged = $this->session->logged ; 
    	$this->input = new  stdClass();
    	$this->audit = new Audit();
    	$this->dbAdapter = $this->db = $this->audit->adapter;
    	
    	
    	// dostupne vsude 
    	$this->registry->session = $this->view->session = $this->session;  
    	if($this->config->instance->securityKey){
    		$this->registry->encryption = new module_Encryption($this->config->instance->securityKey, $this->config->instance->securityVector);
    	} 
		$this->view->cmsFolderPath = $this->config->cmsFolderPath;
					
    	// stav aplikace
    	$this->view->curentAction = $this->request->action;
    	$this->view->curentController = $this->request->controller;
    	$this->view->curentModule = $this->request->module;    	
    	$this->view->curentViewState = array(
    		'module' => $this->view->curentModule,
    		'controller' => $this->view->curentController,
    		'action' => $this->view->curentAction
    	);
    	$this->view->loadingMessage="<img style='position:relative; top:50%; left:50%;' src='" . $this->view->cmsFolderPath . "/images/logos/ok-micro01.gif' />";    	
		$this->view->addHelperPath('../application/views/helpers/cms/', 'Cms_View_Helper_');
			
		$this->view->addHelperPath( SERVER_ROOT . '/application/views/helpers/cms/', 'Cms_View_Helper_');
		$this->view->addHelperPath( LIBS_ROOT . '/application/views/helpers/cms/', 'Cms_View_Helper_');
		
		
		// panely
		$this->view->selectedLeftHelpPane = true;
		$this->view->showTree = true;  
    	// kontext predavany mezi objekty
    	//$this->context = new Context($this->registry, $this->request, $this->config, $this->session, $this->dbAdapter);
    	    
    	// helpers
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$this->_redirector = $this->_helper->getHelper('Redirector');		            
		$this->_redirector->setUseAbsoluteUri();
    	//$this->view->messages = $this->_flashMessenger->getCurrentMessages();
    	$this->view->messages = $this->_flashMessenger->getMessages();
    	       
		//view		
		$this->view->title = '=CMS*2=';
		$this->view->baseUrl = $this->_request->getBaseUrl();
		//$this->view->requestUrl = Utils::getRedirectPath($this->request->getRequestUri());
		$this->view->requestUrl = $this->request->getRequestUri(); 
		$this->view->showBottomPanel = false;
		$this->view->showIn = array('ne' => 'nezobrazovat v menu', 'main' => 'hlavním menu', 'left' => 'levém menu');
		$this->view->showInVal = array('ne' => '0', 'main' => '1', 'left' => '5');
		$this->view->showInValReverse = array('0' => 'ne', '1' => 'main', '5' => 'left'); 
		$this->initFormValuesFromPost();
		$this->initFormValuesFromGet();  
		
		// pokud je zalogovan  
		if($_GET['extload'] || ($this->session->logged && $this->config->isAdmin)){		
			$this->doPageInit = true;    
			// settings 
			$this->registry->settings = $this->view->settings = $this->settings = new Settings(); 
    				
			// promenne
			//  $this->view->currentNodePath = $this->session->currentNodePath;			
			$this->view->currentFsPath = $this->session->currentFsPath;
			$this->view->currentSysPath = $this->session->currentSysPath;
			
			//menu
			$menu = new Menu();		
			$this->view->mainMenu = $menu->getItems();			
			//uzivatel
			$this->view->user = $this->session->user;					
			if($this->session->user->group == 'Superadmin'){   
				$this->session->user->isSuperAdmin = $this->view->isSuperAdmin = true;   
			} 
			
			// e($this->session->user->isSuperAdmin);
			//strom webu
			$this->tree = $this->view->tree = $this->registry->tree;
						
			$node = $this->request->getParam('node');
			if($node){
				//$this->session->currentNodePath =  $this->tree->getNodeIdPath($node);	
			}
			
			//$this->tree->test();
			//$this->tree->save();
			$this->view->forceReload = '?random='.rand();
			$this->view->templates = $this->config->instance->containers->toArray(); // temp
			
			
			$this->view->isLinkPopup = $this->isLinkPopup = $this->request->getParam('isLinkPopup')?1:0;
			$this->view->isPageSelectPopup = $this->isPageSelectPopup = $this->request->getParam('isPageSelectPopup')?1:0;
			
			// TABLES
			$this->view->tableFilters = array();			
			$this->view->tableListings = $this->config->TableHelper->listingOptions;
			$this->view->tableDefaultListing = $this->config->TableHelper->defaultListing;
			$this->view->tableActions = array();	
			
			//sys strom 
			$this->view->showSysTree = $this->session->user->group == 'Superadmin'?'1':'0';
			//$this->session->currentSysPath =  $this->tree->getNodeIdPath($node);	
			$this->view->currentSysPath = $this->session->currentSysPath?$this->session->currentSysPath:'';
			
			// uzivatele
			$this->groups = new Groups();		 
			$this->view->groups = $this->groups->getGroups();
			
			if(!$this->session->user->isSuperAdmin){
				unset($this->view->groups['Superadmin']);  
			}
		
			
			$users = new Users(); 
			$allU = $users->getUsers('all', 'fullname', 'DESC',  true);			
			foreach ($allU as $u){  
				 $this->systemUsers[$u['id']] = $u['fullname'] . ' (' . $this->view->groups[$u['group']] . ')';
			}
			$this->registry->systemUsers = $this->view->systemUsers = $this->systemUsers;
			//$this->registry->systemUsersFull = $allU;
			
			$this->view->controllerIdentificatorMap = $this->config->controllerIdentificatorMap->toArray();
			$this->view->superTypeControllerMap = $this->config->superTypeControllerMap->toArray();
			
			$this->view->leftPanes = array();  
			
		} else {
			$this->doPageInit = false; 
			if($this->view->curentController != 'login'){
			 $this->_redirector->goto('login', 'login');   
			}  
			//header ("Location: http://www.php.net");  exit();
		}
		 
    	   
    }
    
	public function initHelp($section)
	{  	      
		if($section == 'eshop'){
			$section = 'e-shop';
		}		
		$this->view->children = array();
		
		$helpNode = $this->tree->getNodeByPath('/cms-help/' . $section);
		if ($helpNode){
			$publishedContent = $helpNode->getPublishedContent();
			$this->view->helpText = $publishedContent->getPropertyValue('html');
			$this->view->helpNodeId = $helpNode->nodeId;
			$this->view->children = $helpNode->getChildren('BOTH');
		} else {
			$this->view->helpText = 'Nápověda není k dispozici';			
		}  
				 
    	$this->view->helpContent = $this->view->render('/controls/admin/tabs/HelpWidget.phtml');  
	}
    
    public function audit($message='', $nodeId=null, $action = null, $controller = null){ 	
    	  
		$this->audit->addOperation(
			array(
				'userid' => $this->view->user->id,    
				'module' => $this->request->module,
				'controller' => $controller?$controller:$this->request->controller,
				'action' => $action?$action:$this->request->action,
				'nodeId' => $nodeId,
				'message' => $message
			)
		);	  
    }
    
    public static function getDb(){
    	return  Zend_Db_Table::getAdapter();
    }
    
    public function dbBind($data){ 	
    	$dataNew = array();
		foreach ($data as $k => $v){			
			$dataNew[$k] = $this->dbAdapter->quoteInto('?',$v);
		}
		return $dataNew;
    }
    
    /*
   	public function initFormValuesFromPost(){
   		if($this->fields){
   			$this->fieldsValues = array();
	   		foreach ($this->fields as $f){	   			
	   			if(trim($this->request->getPost($f))){
					$this->fieldsValues[$f] = $this->input->$f = $this->view->$f = trim($this->filter->filter($this->request->getPost($f)));				
	   			}
			}
   		}
   	}
   	*/
    
    public function initFormValuesFromPost(){   		
		$this->fieldsValues = array();  
   		foreach ($_POST as $f => $val){		 
   			if($f{0} != '_'){
				$this->fieldsValues[$f] = $this->input->$f = $this->view->$f = helper_Security::secureInput($this->request->getPost($f));				
   			} 
		}  
   	} 
   	
   	public function initFormValuesFromGet(){   		
		$this->fieldsValuesGet = array();
		
   		foreach ($_GET as $k => $val){
   			if(isset($_GET[$k]) && $k{0} != '_'){
   				//e($k);
				$this->fieldsValuesGet[$k] = $this->inputGet->$k = $this->view->$k = helper_Security::secureInput($_GET[$k]);				
   			}
		}
		$this->view->inputGet = $this->inputGet;
   	}
   	
   	public function initFormValuesFromRowset($rs = null){
   		   		
   		if($this->fields && $rs){
   			$this->fieldsValues = array();
	   		foreach ($this->fields as $f){		
	   			if(isset($rs->$f)){
					$this->fieldsValues[$f] = $this->input->$f = $this->view->$f = $rs->$f;			
	   			}				
			}
   		}
   	}
   	
   	public function initFormValuesFromArray(array $arr, $prefix = ''){  
   		foreach ($arr as $k => $val){
   			$this->input->{$prefix . $k} = $val;					
		}
		$this->view->input = $this->input;
   	}
   	
   	public function performMultiAction(){
   		//pr($this->input);
   		$selectedRows = $this->getRowIds(); 
   		 
		if(count($selectedRows)){
			$multiAction = $this->input->tableaction;
			foreach ($selectedRows as $id){
				$this->performMultiaction($multiAction, $id);
			}
			
			//$this->addInfoInstantMessage('Operace provedena úspěšně');
		}
   	}
   	
   	public function getRowIds(){
   		$selectedRows = array();
   		if(count($this->input->chbx)){
	   		foreach ($this->input->chbx as $ident => $val){			
				$selectedRows[] = $ident; 
			}
   		}
		return $selectedRows;    
   	}
   	
   	public function getRowIdsFromGet(){
   		$selectedRows = array();
   		foreach ($this->request->getParams() as $ident => $val){			
			if(substr($ident,0,4) == 'row_' && $val == 1){
				$selectedRows[] = substr($ident,4,46);
			}			
		}
		return $selectedRows;
   	}
   	   			
   	public function getRowIdsFromPost(){
   		$selectedRows = array();
   		foreach ($_POST as $ident => $val){			
			if(substr($ident,0,4) == 'row_' && $val == 1){
				$selectedRows[] = substr($ident,4,46);
			}			
		}	
		/*	
   		foreach ($_POST as $ident => $val){			
			if(substr($ident,0,4) == 'row_' && $val == 1){
				$selectedRows[] = substr($ident,4,15);
			}			
		}
		*/
		return $selectedRows;
   	}
   	
   	public  function  getControllerFromUrl(){
   		
   	}
   	
   	public function getWYSIWYGHtml($html){
   		return Utils::getWYSIWYGHtml($html);
   	}
   	
   	/*
   	public function setWysiwygContent($html){
   		return stripslashes(urldecode($html));
   	}
   	*/
   	
   	/* render */
   	
   	public  function addModalInfo($status, $txt, $redirect = false, $redirectTabUrl = false, $returnValue = false, $returnValue2 = false, $returnValue3 = false){
   		
   		switch ($status){
   			case 'failure':
   			case '0':
   				$color = '#e51c1c';
   				break;
   			case 'success':
   				$color = '#3cb63c';
   				break;
   			default:
   			case '1':
   				$color = '#3cb63c';
   				break;	
   		}
   		$this->view->color = $color;
   		$this->view->modalText = $txt;
   		
   		$ar = array(
			'status' => $status,
			'html' => $this->view->render('/controls/admin/ModalWindowInfoPanel.phtml')
		);
				
		if($returnValue){
			$ar['value'] = $returnValue;
		}
		
		if($returnValue2){
			$ar['value2'] = $returnValue2;
		}
		else{
			$ar['value2'] = '';
		}
		
		if($returnValue3){
			$ar['value3'] = $returnValue3;
		}
		
		if($redirectTabUrl){
			$ar['redirectTabUrl'] = $redirectTabUrl;
		}
		
		if($redirect){
			$ar['redirectPage'] = $redirect;
		}		
		echo ($ar); 
   	}  
    
   	
    /*
    	osetreni neexistujiich akci
    */
   
    public function __call($method, $args) 
    {
    	/*
        if ('Action' == substr($method, -6)) {
            // If the action method was not found, forward to the index action
           	$this->_redirector->goto('error', 'index');
        }

        // all other methods throw an exception
        throw new Exception('Invalid method "' . $method . '" called');
        */
    }
    
    public function addErrorMessage($txt)
    { 
    	$this->_flashMessenger->addMessage('e-'.$txt);
    	$this->view->messages = $this->_flashMessenger->getMessages();
    }
    
    public function addErrorInstantMessage($txt)
    { 
    	$this->view->messages[] = 'e-'.$txt;  
    }
    
    public function addInfoMessage($txt)
    { 
    	$this->_flashMessenger->addMessage('i-'.$txt);
    }
    
    public function addInfoInstantMessage($txt)
    { 
    	$this->view->messages[] = 'i-'.$txt;
    }
    
    public function indexAction($content = '/controls/admin/tabs/IndexHome.phtml')
    {       
    	//$this->controllers = new Controllers();		
		//$this->view->allControllers =  $this->controllers->getControllers();
		  
    	$this->view->content = $content;    	
		echo $this->view->render('index.phtml');  
		//echo $this->view->render('index2.phtml');
    }
    
    public function renderModal($content)
    {       
    	$this->view->modalContent = $content;
		echo $this->view->render('controls/admin/ModalWindowContent.phtml');
    }
   
	function doLogoutAction() {
		$this->session->logged = false;		
		// unset principal
		echo $this->view->render('index.phtml');	

	}
	
    public function testAction()
    {
    	//
       //$this->render();
       	

		$this->view->content = 'tests.phtml';
		
		// and render a view script called "booklist.php"
		echo $this->view->render('index.phtml');
    }
}
