<?php
/*
	Reality module
*/
class Cms_RealityController extends CmsController
{

	public function init()
	{
		//e(Utils::getReadableSize(memory_get_peak_usage()));
		//e(Utils::getReadableSize(memory_get_usage()));
		
		parent::init();		
		 				
		if($this->doPageInit)
			$this->initPage();
		
	}
	
	private function initPage()
	{  		
    	//$showGroup = $this->_request->__get('userGroup');	
    	
		$this->view->title .= ' - Reality';
		$this->template = 'controls/admin/modules/Reality/Reality.phtml'; 
		$this->view->showBottomPanel = true;
		$this->view->bottomContentTitle = 'Výpis realit';
		$this->view->bottomContentHref = $this->view->url(array('action' => 'list'));
		
		$this->view->selectedLeftHelpPane = false;
		$this->view->showTree = false;
		//$this->audit->getUserAudit('a');
		
		if(!$this->registry->reality){
    		$this->registry->reality = new module_reality_Reality();
    		if($this->request->getActionName() != 'export'){
    			$this->registry->reality->initAllProperties();
    		} 
    	}
    	
    	$node = $this->request->getParam('realityNode');
    	$this->node = $this->view->node = $this->tree->getNodeById($node);
    	if($this->node){
			$this->view->content  = $this->node->getTheRightContent();	
    	}
		
		// realitni parametry
		$this->advert_function = $this->request->getParam('typ')?$this->request->getParam('typ'):'pronajem';
		$this->category = $this->request->getParam('kategorie')?$this->request->getParam('kategorie'):'byty';
		$this->categoryReal = $this->request->getParam('kategorie');
				
		$this->view->realityContentTypes = $this->registry->reality->getContentTypes();
		$this->view->realityNodeId = $this->view->parentId = $this->config->instance->realityNodeId;
		$this->view->realityCategories = $this->registry->reality->getCategories();
		
		$this->view->leftPanes[] = array(
			'title' => 'Realitní kotegorie',
			'id' => 'realityLeftPane',
			'class' => '',
			'url' => $this->view->url(array('action' => 'showTree'))		
		);
		
	}
	
	public function homeAction()
	{  			
		$this->view->contentType = $this->view->realityCategories[$this->category]['content_type'];
		$this->view->contentTypeName = $this->view->realityContentTypes[$this->view->contentType];		
		$this->view->categoryName = $this->registry->reality->advert_types[$this->advert_function];
		
    	echo $this->view->render('controls/admin/modules/Reality/RealityHome.phtml');
	}
	
	public function multiAction(){		
		parent::performMultiAction();
	}
	
	public function performMultiaction($action, $id){
		//'sell' => 'Prodáno', 'erase' => 'Odstranit z nabídky', 'delete' => 'Úplně odstranit' 'return' => 'Vrátit zpět do nabídky'
		switch ($action){
			case 'delete':				
				$this->deleteAction($id);				
				break;
			case 'sell':				
				$this->sellAction($id);				
				break;
			case 'erase':				
				$this->eraseAction($id);				
				break;	
			case 'return':				
				$this->returnAction($id);				
				break;	
			case 'unexpire':				
				$this->unexpireAction($id);				
				break;	
		}
		
	}
	
	public function deleteAction($id = 0)	{
		parent::audit($this->tree->getNodeById($node)->title, $id,  'delete');
		$this->tree->removeNode($id);
		//$this->listAction();		
	}
	
	public function sellAction($id = 0)	{
		$property = $this->tree->getNodeById($id);
		$content = $property->getTheRightContent();		
		$content->state = 'SOLD';				
		if($_SERVER['DOCUMENT_ROOT'] != '/home/apache/rkcity.cz/www/htdocs'){
			//Utils::debug($content->getPropertyValue('realId') . ' -- sold'. $_SERVER['DOCUMENT_ROOT']);
		}
		$content->update(true);		
		parent::audit($property->title, $id,  'sell');
	}
	
	public function eraseAction($id = 0)	{
		$property = $this->tree->getNodeById($id);
		$content = $property->getTheRightContent();
		$content->state = 'ERASED';		
		if($_SERVER['DOCUMENT_ROOT'] != '/home/apache/rkcity.cz/www/htdocs'){		
			//Utils::debug($content->getPropertyValue('realId') . ' -- erased' . $_SERVER['DOCUMENT_ROOT']);
		}
		$content->update(true);		
		parent::audit($property->title, $id,  'erase');
	}
	
	public function returnAction($id = 0)	{				
		$property = $this->tree->getNodeById($id);
		$content = $property->getTheRightContent();		
		$content->state = 'PUBLISHED';				
		if($_SERVER['DOCUMENT_ROOT'] != '/home/apache/rkcity.cz/www/htdocs'){
			//Utils::debug($content->getPropertyValue('realId') . ' -- return'. $_SERVER['DOCUMENT_ROOT']);
		}
		
		if(method_exists($content, 'onReturn')){
			$content->onReturn();
		}
		$content->update(true);		 
		parent::audit($property->title, $id,  'return');
	}
	
	
	public function unexpireAction($id = 0)	{				
		$property = $this->tree->getNodeById($id);
		$content = $property->getTheRightContent();				
		$content->update(true);		
		
		parent::audit($property->title, $id,  'unexpire');
	}
	
	
	
	public function showTreeAction()
	{  	
		$this->view->realStructure = $this->registry->reality->getStructure();
		
		$filter = new stdClass();
		$filter->onlyExpired = 1;		
		// table			
		$properties = $this->registry->reality->filterProperties(
			0, 
			'', 
			$this->view->tableSort, 
			$this->view->tableSortType,
			$filter
		);
		$this->view->expired = count($properties);
		
    	echo $this->view->render('controls/admin/modules/Reality/RealityTree.phtml');
	}
		
	public function indexAction()
	{  	
    	parent::indexAction($this->template);		
    	
	}
	
	public function newAction()
	{		
		$this->view->isEdit = false;		
		parent::renderModal('controls/admin/modules/Reality/RealityDetail.phtml');
	}
	
	public function detailAction()
	{	
		$this->view->isEdit = true;	
		parent::renderModal('controls/admin/modules/Reality/RealityDetail.phtml');
	}
	
	public function listAction()
	{  	
		parent::performMultiAction();
 		if($this->view->user->group == 'Brokers' ){
			$this->view->tableActions = array();
		} else {	
			$this->view->tableActions = array('sell' => 'Prodáno', 'erase' => 'Odstranit z nabídky', 'delete' => 'Úplně odstranit' );
		}
		
		$this->view->curentViewState['action'] = 'list';	
		$this->view->contentsSubmitFormUrl = $this->view->url(array('action' => 'list'));
					
		$this->view->tableSort = $this->request->getParam('sort')?$this->request->getParam('sort'):'dateModifDesc';			
		$this->view->tableSortType = $this->request->getParam('sortType')?$this->request->getParam('sortType'):'';
		$this->view->tableFilter0 = $this->request->getParam('tableFilter0')?$this->request->getParam('tableFilter0'):'all';
		$this->view->tableParentTab = 'vypisDole';							
		$this->view->tableFilters[] = array('all' => 'Vše');				
		$sortFunction = $this->view->tableSort . $this->view->tableSortType;
		
		$filter = new stdClass();
		if($this->view->user->group == 'Brokers' ){
			$filter->brooker = $this->view->user->username;
		}
		// table			
		$properties = $this->registry->reality->filterProperties(
			$this->advert_function, 
			$this->categoryReal, 
			$this->view->tableSort, 
			$this->view->tableSortType,
			$filter
		);
		//pr($properties);
		$users = $this->registry->systemUsers;
		$regiony = $this->registry->reality->location->getRegiony();
		$this->view->contentsTable = array();
		foreach ($properties as $property){
			$publishedContent = $property->getTheRightContent();
			$this->view->contentsTable[] = array(
				'nodeId' => $property->nodeId,
				'realId' => $publishedContent->getRealId(),
				'title' => $property->title,
				'type' => $publishedContent->userName,
				'advert_city' => $publishedContent->getPropertyValue('advert_city'),
				'advert_street' => $publishedContent->getPropertyValue('advert_street'),
				'makler' => current(explode('(', $users[$publishedContent->getPropertyValue('makler')])),				
				'modif' => Utils::formatTime($publishedContent->dateModif)
			);
		}
		//pr($this->view->contentsTable);
		$this->view->contentsSubmitFormUrl = $this->view->url(array('action' => 'list'));
		$this->view->contentsTableParentTab = 'vypisDole';
		
		$this->view->contentsTableHead = array(
			'realId' => array(
				'title' => 'ID',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'title' => array(
				'title' => 'Název',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),	
			'publishedContent' => array(
				'title' => 'Typ obsahu',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'advert_city' => array(
				'title' => 'Město',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'advert_street' => array(
				'title' => 'Ulice',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			), 'makler' => array(
				'title' => 'Makléř',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'dateModif' => array(
				'title' => 'Posl. modifikace',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			)
		);		
		
		$this->view->contentsTableActions = array();		
		$this->view->contentsTableActions['detail'] = array(
			'title' => 'Detail obsahu',
			'type'  => 'modal',
			'url'   => $this->view->url(array('action' => 'detail', 'realityNode'=>'%$%'))
		);
		
    	echo $this->view->render('controls/admin/modules/Reality/RealityList.phtml');
	}
	
	public function soldListAction()
	{  	
		parent::performMultiAction();
 				
		if($this->view->user->group == 'Brokers' ){
			$this->view->tableActions = array();
		} else {
			$this->view->tableActions = array('return' => 'Vrátit zpět do nabídky', 'delete' => 'Úplně odstranit');		
		}
		$this->view->curentViewState['action'] = 'soldList';	
		$this->view->contentsSubmitFormUrl = $this->view->url(array('action' => $this->view->curentViewState['action']));
					
		$this->view->tableSort = $this->request->getParam('sort')?$this->request->getParam('sort'):'dateModifDesc';			
		$this->view->tableSortType = $this->request->getParam('sortType')?$this->request->getParam('sortType'):'';
		$this->view->tableFilter0 = $this->request->getParam('tableFilter0')?$this->request->getParam('tableFilter0'):'all';
		$this->view->tableParentTab = 'vypisDole';							
		$this->view->tableFilters[] = array('all' => 'Vše');				
		$sortFunction = $this->view->tableSort . $this->view->tableSortType;
		
		$filter = new stdClass();
		$filter->onlySold = 1;		
		// table			
		$properties = $this->registry->reality->filterProperties(
			0, 
			'', 
			$this->view->tableSort, 
			$this->view->tableSortType,
			$filter
		);
		//pr($properties);
		$users = $this->registry->systemUsers;
		$regiony = $this->registry->reality->location->getRegiony();
		$this->view->contentsTable = array();
		foreach ($properties as $property){
			$publishedContent = $property->getTheRightContent();
			$this->view->contentsTable[] = array(
				'nodeId' => $property->nodeId,
				'realId' => $publishedContent->getRealId(),
				'title' => $property->title,
				'type' => $publishedContent->userName,
				'advert_city' => $publishedContent->getPropertyValue('advert_city'),
				'advert_street' => $publishedContent->getPropertyValue('advert_street'),
				'makler' => current(explode('(', $users[$publishedContent->getPropertyValue('makler')])),				
				'modif' => Utils::formatTime($publishedContent->dateModif)
			);
		}
		//pr($this->view->contentsTable);
		$this->view->contentsSubmitFormUrl = $this->view->url(array('action' => 'list'));
		$this->view->contentsTableParentTab = 'vypisDole';
		
		$this->view->contentsTableHead = array(
			'realId' => array(
				'title' => 'ID',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'title' => array(
				'title' => 'Název',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),	
			'publishedContent' => array(
				'title' => 'Typ obsahu',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'advert_city' => array(
				'title' => 'Město',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'advert_street' => array(
				'title' => 'Ulice',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			), 'makler' => array(
				'title' => 'Makléř',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'dateModif' => array(
				'title' => 'Posl. modifikace',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			)
		);		
		
		$this->view->contentsTableActions = array();		
		$this->view->contentsTableActions['detail'] = array(
			'title' => 'Detail obsahu',
			'type'  => 'modal',
			'url'   => $this->view->url(array('action' => 'detail', 'realityNode'=>'%$%'))
		);
		
    	echo $this->view->render('controls/admin/modules/Reality/RealityList.phtml');
	}
	
	public function expiratedListAction()
	{  	
		parent::performMultiAction();
 				
		if($this->view->user->group == 'Brokers' ){
			$this->view->tableActions = array();
		} else {
			$this->view->tableActions = array('unexpire' => 'Obnovit reality', 'erase' => 'Odstranit z nabídky', 'delete' => 'Úplně odstranit');		
		}
		$this->view->curentViewState['action'] = 'expiratedList';	
		$this->view->contentsSubmitFormUrl = $this->view->url(array('action' => $this->view->curentViewState['action']));
					
		$this->view->tableSort = $this->request->getParam('sort')?$this->request->getParam('sort'):'dateModifDesc';			
		$this->view->tableSortType = $this->request->getParam('sortType')?$this->request->getParam('sortType'):'';
		$this->view->tableFilter0 = $this->request->getParam('tableFilter0')?$this->request->getParam('tableFilter0'):'all';
		$this->view->tableParentTab = 'vypisDole';							
		$this->view->tableFilters[] = array('all' => 'Vše');				
		$sortFunction = $this->view->tableSort . $this->view->tableSortType;
		
		$filter = new stdClass();
		$filter->onlyExpired = 1;		
		// table			
		$properties = $this->registry->reality->filterProperties(
			0, 
			'', 
			$this->view->tableSort, 
			$this->view->tableSortType,
			$filter
		);
		//pr($properties);
		$users = $this->registry->systemUsers;
		$regiony = $this->registry->reality->location->getRegiony();
		$this->view->contentsTable = array();
		foreach ($properties as $property){
			$publishedContent = $property->getTheRightContent();
			$this->view->contentsTable[] = array(
				'nodeId' => $property->nodeId,
				'realId' => $publishedContent->getRealId(),
				'title' => $property->title,
				'type' => $publishedContent->userName,
				'advert_city' => $publishedContent->getPropertyValue('advert_city'),
				'advert_street' => $publishedContent->getPropertyValue('advert_street'),
				'makler' => current(explode('(', $users[$publishedContent->getPropertyValue('makler')])),				
				'modif' => Utils::formatTime($publishedContent->dateModif)
			);
		}
		//pr($this->view->contentsTable);
		$this->view->contentsSubmitFormUrl = $this->view->url(array('action' => 'list'));
		$this->view->contentsTableParentTab = 'vypisDole';
		
		$this->view->contentsTableHead = array(
			'realId' => array(
				'title' => 'ID',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'title' => array(
				'title' => 'Název',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),	
			'publishedContent' => array(
				'title' => 'Typ obsahu',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'advert_city' => array(
				'title' => 'Město',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'advert_street' => array(
				'title' => 'Ulice',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			), 'makler' => array(
				'title' => 'Makléř',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'dateModif' => array(
				'title' => 'Posl. modifikace',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			)
		);		
		
		$this->view->contentsTableActions = array();		
		$this->view->contentsTableActions['detail'] = array(
			'title' => 'Detail obsahu',
			'type'  => 'modal',
			'url'   => $this->view->url(array('action' => 'detail', 'realityNode'=>'%$%'))
		);
		
    	echo $this->view->render('controls/admin/modules/Reality/RealityList.phtml');
	}
	
	public function erasedListAction()
	{  	
		parent::performMultiAction();
 				
		if($this->view->user->group == 'Brokers' ){
			$this->view->tableActions = array();
		} else {
			$this->view->tableActions = array('return' => 'Vrátit zpět do nabídky', 'delete' => 'Úplně odstranit');		
		}
		$this->view->curentViewState['action'] = 'erasedList';	
		$this->view->contentsSubmitFormUrl = $this->view->url(array('action' => $this->view->curentViewState['action']));
					
		$this->view->tableSort = $this->request->getParam('sort')?$this->request->getParam('sort'):'dateModifDesc';			
		$this->view->tableSortType = $this->request->getParam('sortType')?$this->request->getParam('sortType'):'';
		$this->view->tableFilter0 = $this->request->getParam('tableFilter0')?$this->request->getParam('tableFilter0'):'all';
		$this->view->tableParentTab = 'vypisDole';							
		$this->view->tableFilters[] = array('all' => 'Vše');				
		$sortFunction = $this->view->tableSort . $this->view->tableSortType;
		
		$filter = new stdClass();
		$filter->onlyErased = 1;		
		// table			
		$properties = $this->registry->reality->filterProperties(
			0, 
			'', 
			$this->view->tableSort, 
			$this->view->tableSortType,
			$filter
		);
		//pr($properties);
		$users = $this->registry->systemUsers;
		$regiony = $this->registry->reality->location->getRegiony();
		$this->view->contentsTable = array();
		foreach ($properties as $property){
			$publishedContent = $property->getTheRightContent();
			$this->view->contentsTable[] = array(
				'nodeId' => $property->nodeId,
				'realId' => $publishedContent->getRealId(),
				'title' => $property->title,
				'type' => $publishedContent->userName,
				'advert_city' => $publishedContent->getPropertyValue('advert_city'),
				'advert_street' => $publishedContent->getPropertyValue('advert_street'),
				'makler' => current(explode('(', $users[$publishedContent->getPropertyValue('makler')])),				
				'modif' => Utils::formatTime($publishedContent->dateModif)
			);
		}
		//pr($this->view->contentsTable);
		$this->view->contentsSubmitFormUrl = $this->view->url(array('action' => 'list'));
		$this->view->contentsTableParentTab = 'vypisDole';
		
		$this->view->contentsTableHead = array(
			'realId' => array(
				'title' => 'ID',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'title' => array(
				'title' => 'Název',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),	
			'publishedContent' => array(
				'title' => 'Typ obsahu',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'advert_city' => array(
				'title' => 'Město',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'advert_street' => array(
				'title' => 'Ulice',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			), 'makler' => array(
				'title' => 'Makléř',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'dateModif' => array(
				'title' => 'Posl. modifikace',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			)
		);		
		
		$this->view->contentsTableActions = array();		
		$this->view->contentsTableActions['detail'] = array(
			'title' => 'Detail obsahu',
			'type'  => 'modal',
			'url'   => $this->view->url(array('action' => 'detail', 'realityNode'=>'%$%'))
		);
		
    	echo $this->view->render('controls/admin/modules/Reality/RealityList.phtml');
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
		parent::renderModal('controls/admin/modules/Reality/Form.phtml');
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
		
		$this->view->realityTitle = $this->view->realityContentTypes[$this->view->contentType];
		$this->view->useDojo = false;
		$this->view->isPrint = true;
		
		$this->view->title = 'Bleskové REALITY (www.bleskovereality.cz)';
		echo $this->view->render('controls/admin/modules/Reality/FormPrint.phtml');
	}
	
	public function printPromoAction()
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
		
		$this->view->realityTitle = $this->view->realityContentTypes[$this->view->contentType];
		$this->view->useDojo = false;
		$this->view->isPrint = true;
		
		$this->view->title = $this->config->title->title;
		echo $this->view->render('controls/admin/modules/Reality/FormPrintNice.phtml');
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
			//$this->view->content->getPropertyByName('dateExported')->value = '';
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
					
			$node = $this->request->getParam('realityNode');
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
			$this->view->content->getPropertyByName('dateExported')->value = '';
			//pr($this->view->content->properties);
			$this->view->contentId = $contentId;		
			$this->view->content->update();
						
			//node
			$this->node->initUpdate($this->input, $this->view);				    	  	
	    	$this->tree->updateNode($this->node); 	    	
	    	
	    	//$this->node->checkStateChange($this->input, $this->view->content);
	    		    	
	    	parent::audit($this->node->title, $this->node->nodeId);	    	
			
	    	//e($this->view->content->dateModif);
	    	//e($this->view->content->getPropertyValue('dateExported'));
	    	
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
	
	/* XHR Actions */
	public  function getCitiesAction(){
		//iconv("UTF-8", "WINDOWS-1250",$val)
		$val = $this->request->getParam('region')?$this->request->getParam('region'):'Praha';		
		echo $this->JSON->encode($this->registry->reality->location->getCities(urldecode($val))); 
	}
	
	public  function getCityPartsAction(){
		$val = $this->request->getParam('city')?$this->request->getParam('city'):'';		
		echo $this->JSON->encode($this->registry->reality->location->getCityParts(urldecode($val))); 
	}
	
	public  function getCityPartStreetsAction(){
		$val = $this->request->getParam('citypart')?$this->request->getParam('citypart'):'';		
		echo $this->JSON->encode($this->registry->reality->location->getCityPartStreets(urldecode($val))); 
	}
	
	/**	 * EXPORT     */
	
	public function exportAction()
	{  	 
		require_once('/var/www/bleskove-reality.cz/web/www/application/export.php');  
		e('ok'); 
		/* 
		$export = new module_reality_Export($this->registry->reality);
		$export->export();
		*/ 
	}
	
	/**	 * NEWS     */
	 
	public function newsAction()
	{  	
		require_once('/home/apache/realityblesk.cz/www/application/news.php');
		/*
		$export = new module_reality_Export($this->registry->reality);
		$export->export();
		*/
	}
	
}
