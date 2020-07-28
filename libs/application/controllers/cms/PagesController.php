<?php
/*
	Shared files
*/
class Cms_PagesController extends CmsController
{

	public function init()
	{				
		$this->fields = array('title', 'parentId', 'pageTitle', 'description_new', 'parentId_new', 'contentType');
		parent::init();
		
		$this->view->title .= ' - Stránky';
		$this->view->selectedLeftHelpPane = false;
		
		$this->template = 'controls/admin/Pages.phtml';  			
		
		$this->view->contentStates = $this->config->instance->workflow->toArray(); 
		
		if($this->config->instance->showContentsDetailsWidget){
			$this->view->showExtraContentsWidget = true; 
		}
		
		if($this->doPageInit){
			$this->initPage();
		}
	}
	
	private function initPage()
	{  		 
		//$this->view->curentTreeNode = $this->view->curentNode = $this->session->curentNode = $this->nodeId;
		$this->view->isPopup = $this->isPopup = $this->request->getParam('isPopup')?1:0;
		
		$detail = $this->inputGet->showDetail;
		if($detail){
			$this->showDetail = $this->view->showDetail = $detail;
		}
		
		$this->callBackInput = $this->request->getParam('callBackInput');
		
    	$node = $this->request->getParam('node');
    	$node = $node?$node:1;

    	$n = $this->tree->getNodeById($node);
		if(!$n){
			$node = 1;
		}
		$this->view->rootNodeId = 1;  
		$this->session->currentNodePath =  $this->tree->getNodeIdPath($node);	 
		$this->view->currentNodePath = $this->session->currentNodePath?$this->session->currentNodePath:'';
				 
		
		$this->session->currentSysPath =  substr($this->session->currentNodePath, 2);
				 
		$this->registry->curentNodeId = $this->nodeId = $this->view->nodeId = $node;  
		$this->view->node = $this->node = $this->tree->getNodeById($node);
		 
		 /*
		$content = $this->node->getTheRightContent();
		if(method_exists($content, 'initAdverts')){
			$content->initAdverts();
			//$content->setAdvertsFromNode($this->node->adverts);
			$this->view->advertsPositions = $content->getAdverts();
		}
		*/ 
		
		// co muze mit pod sebou za stranky
		
		if($this->node->type == 'ITEM'){
			$parent = $this->tree->getNodeById($this->node->parentId);  
			$this->session->curentNodeParentId = $this->registry->curentNodeParentId = $this->node->parentId; 
			 
			$alowed = Content::getAllowedContentTypes($parent->getTheRightContent()->allowablePages);
			
			if($this->node->getSupertype() == 'files'){
				$alowed = array(
					'SFSFile' => 'Soubor'
				);
			}
		} else {
			$alowed = Content::getAllowedContentTypes($this->node->getTheRightContent()->allowablePages);
		}
		
		$this->view->contentTypes = $this->contentTypes = $alowed;
		//pr($this->node);
		
		$this->view->parentSelect =  $this->tree->getParentSelect($this->view->rootNodeId);		
		$this->view->parentFileSelect = $this->tree->getParentSelect(2);
		
		$this->view->curentParent = $this->session->curentParent = $this->node->nodeId;
		$this->view->curentNode = $this->view->curentTreeNode = $this->session->curentNode = $this->nodeId;
		
		$this->view->showBottomPanel = true;
		$this->view->bottomContentTitle = 'Výpis';
		$this->view->bottomContentHref = $this->view->url(array('controler' => 'pages','action' => 'list'));
		
		// mapa contenProperties na cesky 
		require_once('content/cpMap.php');
		$this->view->cp_Translate = $_cpMap;
		//pr($this->view->cp_Translate);
		
		
		$this->initHelp('pages');  
		$this->view->leftColl = $this->view->render('parts/leftPages.phtml'); 
		$this->view->isEdit = $this->request->getParam('isEdit')?true:false;
	}
	
	
	/** page select */
	public function pageselectAction()  
	{  	  
		$root = $this->request->getParam('root');
		$root = $root?$root:1;		
		$display = $this->request->getParam('display');
		$display = $display?$display:'BOTH';
		$this->view->display = $display;
		
		$this->view->callbackInput = $this->request->getParam('callbackInput');
		
		$root = $this->tree->getNodeById($root);
		$this->view->nodes = $root->getChildren($display);
		// selected
		$value = $this->request->getParam('value');
		$this->view->selectedNodes = array();
		if($value){
			foreach (helper_MultiSelect::getMultiSelectValues($value) as $nodeId){
				$k = 'row_' . $nodeId;
				$this->view->selectedNodes[$k] = 1;
			}
		}
    	echo $this->view->render('/controls/admin/forms/PagesSelect.phtml');
	}
	
	public function historyAction()
	{			
		$this->setViewNode();	

		//$this->systemUsers);
		$users = new Users(); 
		
		$getItems = $this->request->getParam('getItems');
		$isAjax = $this->request->getParam('ajax');
		
		
		$dg = new DataGrid('auditTable');
		$dg->setRefresUrl($this->view->url(array('action' => 'history', 'getItems' => 1, 'ajax' => 1)))
			->setTitle('Audit uživatelských akcí')  
			->setHeight(400) 
			->setItemsPerPageOptions('10, 15, 20, 30, 50, 500', 50) 
			; 
			
		$dg->setHeaders(
			array( 
				array('Uživatel', 'userid', 150, 'true', 'left', 'false'),
				array('Modul', 'controller', 150, 'true', 'left', 'false'),
				array('Akce', 'action', 220, 'true', 'left', 'false'),  
				array('Čas', 'time', 100, 'true', 'left', 'false')  
			)
		)->setSearchableColls(   
			array(     
				array('Akce', 'action', 'true')  
			)
		)->setButtons( 
			array(    
			)
		); 
		 
		
		$this->view->defaultSort = 'time'; 
		$this->view->defaultSortType = 'asc';  
		  
		if($getItems){  
			
			$sort = 'time';
			$sortType = 'desc'; 
			
			list($currentPage, $rp, $sortname, $sortorder, $query, $qtype, $start) = $dg->getParams('time', 'desc');
			   
			if($this->view->node->nodeId){
				$audit = $this->audit->getNodeAudit(  
					$this->view->node->nodeId,  
					$sortname,
					$sortorder 
				); 
				$rowsTotalCount = count($audit);
			} 	  
			  
			
			$rowsFormated = array();
			foreach ($audit as $a){ 
				
				$entry = array(
						'id'=> 0,
						'cell' => $a 
				);
				
				$rowsFormated[] = $entry;
			}  
			
			 
			if($isAjax){  
				$dg->renderAjax($currentPage, $rowsTotalCount, $rowsFormated);
				die();  
			};
		}
 
		$this->view->userAuditTableActions = array();  
 	 	
 	 	$this->view->disableForm = true; 
		//$this->view->renderFilter = 'controls/admin/lists/filter/ListStatsAudit.phtml'; 
    	$this->view->contentHistory = $dg->render($this->view, 'controls/admin/lists/PagesStandard.phtml');  
    	    
	} 
	
	public function setSelectedPagesAction(){
		$selected = parent::getRowIdsFromPost();
		
		$nodes = array();
		foreach ($selected as $nodeId){		
			$nodes[$nodeId] = $this->tree->getNodeById($nodeId)->title;
		}
		if(empty($nodes)){
		$nodes[0] = 'nic';
		}
		
		return array(1, 'Stránky vybrány.', false, false, (implode(', ', $nodes)), helper_MultiSelect::setMultiSelectValues($nodes),  $this->request->getParam('callbackInput'));
	}
	
	public function multiAction(){		
		parent::performMultiAction();
	}
	
	public function getContentProperties($ctype)
	{  		
    	$ctype = $ctype?$ctype:'HtmlFile';	
		if($ctype && in_array($ctype,array_keys($this->contentTypes))){	
			$c = 'content_'.$ctype;
			$c = new $c();						
			return $c->properties;
		}
	}
			
	public function indexAction()
	{  		
		$this->initPage();    
		$this->listAction();     
		parent::indexAction($this->template); 
	}
	
	public function deleteAction(){	
		$node = $this->request->getParam('n');
		// $node = $node?$node:$this->node->nodeId;  
		parent::audit($this->tree->getNodeById($node)->title, $node);
		$this->tree->removeNode($node, false);
		$this->homeAction();		
	}
	
	public function sortAction() 
	{
		$stringSort = $this->inputGet->sort;
		$this->saveSortDD($stringSort);   
	}
	
	function saveSortDD($values){ 
		$values = str_replace('row', '', $values); 
		 
		$sort = explode(',',$values);   
		$position = 1;
		foreach ($sort as $key=>$valeu) {    
			$node = $this->tree->getNodeById($valeu);	    
			$node->orderValuation = $position; 
			$node->save();		  
			
			$position++;
		} 
	}
	
	public function deletePageAction()	{	
		$node = $this->request->getParam('contentNodeId');
		if($node){
			parent::audit($this->tree->getNodeById($node)->title, $node,  'delete');
			$this->tree->removeNode($node, false);
		} 
		//$this->listAction();		 
	}
	
	public function deleteVersionAction(){	
		$content = $this->request->getParam('contentId');
		if($content){
			$this->node->deleteContent($content);
			parent::audit($this->node->title, $this->node->nodeId);
		}
		$this->versionsAction();		
	} 
	
	public function advertsAction()
	{			
		$content = $this->node->getTheRightContent();
		
		if($content){
			if(method_exists($content, 'initAdverts')){
				$content->initAdverts(); 
				$this->view->advertsPositions = $content->getAdverts(); 
				$this->view->aStats = new module_Advertising_AdvertStats();
			} 
			
			$content->setAdvertsFromNode($this->node->adverts);
			$this->setViewNode();		
		}

		
		$this->view->contentAdverts = $this->view->render('controls/admin/forms/PagesFormAdverts.phtml');  	   
	}
	
	public function advertFormAction()
	{			
		$this->setViewNode();
		$this->view->identificator = $this->request->getParam('identificator');
		$this->view->banner = new module_Advertising_Advert($this->view->identificator);		
		echo $this->view->render('controls/admin/forms/_AdvertForm.phtml');
	}
	
	public function userSortAction(){		
		$move = $this->request->getParam('move');	
		if($move){
			$nodeToMove = $this->tree->getNodeById($this->request->getParam('id'));
			$nodeToMove->changeUserOrder($move, $this->node->sort);		
			parent::audit($this->tree->getNodeById($id)->title, $nodeToMove->nodeId, 'sortChange');		
		}
		$this->listAction();
	}
		
	public function listAction()
	{
		
		parent::performMultiAction();   
		$this->view->sortUrl = '/cms/pages/sort';
		
		$getItems = $this->request->getParam('getItems');
		$isAjax = $this->request->getParam('ajax');
		$nodeId = $this->request->getParam('node');
		$nodeId = $nodeId?$nodeId:1;

		$parentNode = $this->tree->getNodeById($nodeId); 
		 
		$params = array();
		$dg = new DataGrid('pagesTable');
		$dg->setRefresUrl($this->view->url(array('action' => 'list', 'getItems' => 1, 'ajax' => 1)))
			->setTitle('Výpis stránek')
			->setHeight(400) 
			->setItemsPerPageOptions('10, 15, 20, 30, 50, 500', 50) 
			; 
			
		$dg->setHeaders( 
			array( 
				array('Název', 'title', 350, 'true', 'left', 'false'),
				array('Typ', 'ctype', 120, 'false', 'left', 'false'), 
				array('Vytvořeno', 'n.created', 60, 'true', 'left', 'false'),
				array('Změněno', 'n.dateModif',  60, 'true', 'left', 'false'),
				array('Stav', 'state',  60, 'false', 'left', 'false'),
				array('ID', 'n.id', 30, 'true', 'center', 'true')   
			)
		)->setSearchableColls(   
			array(   
				array('Název', 'title', 'true') 
			)
		);
		if($this->view->user->group =='Superadmin' || $this->view->user->group == 'Administrators'){
			$btns = array('Smazat označené', 'delete', 'onpress', 'deletep');
			$btns1 = array('Publikovat', 'published', 'onpress', 'published');
			$btns2 = array('Archivovat', 'archivated', 'onpress', 'archivated');
			$dg->setButtons( 
			array(   
				$btns,$btns1,$btns2
			)); 
			
		} elseif($this->view->user->group =='Redactors') {
			$btns1 = array('Publikovat', 'published', 'onpress', 'published');
			$btns2 = array('Archivovat', 'archivated', 'onpress', 'archivated');
			$dg->setButtons( 
			array(   
				$btns1,$btns2
			));   
		}  
		/*
		 * ->setButtons(
			array(
				array('Btn1', 'add', 'onpress', 'null'),
				array('Btn11', 'add', 'onpress', 'null'), 
				array('Btn111', 'add', 'onpress', 'null'),
				array('Btn1111', 'add', 'onpress', 'null'), 
				array('Btn11141', 'add', 'onpress', 'null'),
				array('Btn2', 'delete', 'onpress', 'null') 
			)
		 */
			 
		if(strpos($parentNode->sort, 'DESC') || strpos($parentNode->sort, 'Desc')){
			$sort = str_replace('Desc', '', $parentNode->sort);
			$this->view->defaultSortType = 'desc';
		} else {  
			$sort = $parentNode->sort;  
			$this->view->defaultSortType = 'asc';
		} 
		if($sort == 'levelId'){
			$this->view->hasDD = true;     
			$sort = 'orderValuation'; 
		}
		if($sort == 'dateCreate'){  
			$sort = 'n.created';   
		}  
		 
		$this->view->defaultSort = $sort;
		
		$lang = Zend_Registry::getInstance()->languages->language;
		$lang = $lang?$lang:'cz';
		 
		if($getItems){ 
			$dg->isDebug(false);      
			if(!$this->view->isSuperAdmin && $nodeId == 1){
				$rowsFormated = array(); 
			} else {
				list($currentPage, $rp, $sortname, $sortorder, $query, $qtype, $start) = $dg->getParams('prijmeni', 'asc');
				
				if($sort == 'dateShow'){ // novinky, clanky  
					
					list($nodes, $rowsTotalCount) = $this->tree->getNodeChildrenCMS($nodeId, 'ITEM', $sort, $start, $rp);
					//pr($nodes); 
					$rows = array();
					foreach ($nodes as $n){
						$n->id = $n->nodeId;
						$n->created = $n->dateCreate;
						$rows[] = (array) $n;   
					} 
				} else { 
					$dg->setTableName('n', 'Nodes')     
						->setSelectCols(array('n.*'))   
					 	->getSelect($params) 
						// ->join('nc', 'NodesContents', 'n.id = nc.n_id', 'n.id')  
						->addWhereBind('type', '=', 'ITEM') 
						->addWhereBind('languages', 'LIKE', '%' . $lang . '%')  
						->addWhereBind('deleted', '=', '0') 
						->addWhereBind('parent', '=', $nodeId) 
						->setLimit($start, $rp);   
						  
					
					list($rowsTotalCount, $rows, $currentPage) = $dg->getRows($this->view->defaultSort, $this->view->defaultSortType);
				}
				
				// e($rowsTotalCount);  pr($rows); 
				 
			
				// jen superadmin 
				foreach($rows AS $row){
					//If cell's elements have named keys, they must match column names
					//Only cell's with named keys and matching columns are order independent. 
					$node = $this->tree->getNodeById($row['id']);  
					$c = $node->getTheRightContent(); 
					 $state = $this->view->contentStates[$c->state];
					$editUrl = $this->view->url(array('controller' => 'pages','action' => 'detail', 'node'=> $row['id'], 'parentnode'=> $row['parent'], 'ajax' => 0, 'contentId' => 0));
					 
					$entry = array(   
						'id'=>$row['id'],   
						'cell'=>array(     
							'title'=> '<input name="chbx[' . $row['id'] . ']" type="checkbox" /><a href="' . $editUrl . '">' . $row['title'] . '</a> &nbsp;&nbsp;'  . Utils::getFrontEndLink($node->path, false, '', false, 0, $this->view),  
							'ctype'=> $c->userName, 
							'n.created'=> Utils::formatDate($row['created']),  
							'n.dateModif'=> Utils::formatDate($row['dateModif']),  
							'state' => $state,
							'n.id'=> $row['id']. ($this->view->hasDD?'<a class="usort"></a>':''),  
						),
					);
					$rowsFormated[] = $entry;
				}
			}
			if($isAjax){
				$rowsTotalCount = $rowsTotalCount?$rowsTotalCount:0; 
				$dg->renderAjax($currentPage, $rowsTotalCount, $rowsFormated);
				die();   
			};
		}
		$this->view->pagesList = $dg->render($this->view, 'controls/admin/lists/PagesStandard.phtml');
	} 
	
	function setViewNode(){
		if(!$this->view->isEdit){
			$this->view->node = new Node();
		}	


	}
	
	public function newAction()
	{
		$this->view->isEdit = false;  

		$lang = Zend_Registry::getInstance()->languages->language;
		$lang = $lang?$lang:'cz';
			
		$this->view->contentType = $ctype = $this->request->getParam('contentType');
		$this->view->content  = Content::getNewContent($this->view->contentType);	
			if(isset($this->input->save)){   
			list($state, $message) = $this->saveAction();  
			if($state){
				$this->_redirector->goto('index', 'pages', null, array('node' => $this->node->nodeId, 'language' => $lang));
			} else {
				$this->addErrorInstantMessage($message );
			}   
		}   
		
				
		$this->setViewNode();	

		$this->node = $this->view->node; 
		 
		$this->formAction();
		if(!$this->view->inFileBranch){
			$this->seoAction();  
			$this->settingsAction();
			if($this->view->languages->isMultiLanguage){
				$this->languagesAction();
			} 
		}

		if($this->view->advertsPositions){
			$this->advertsAction();
		}
		
		
		if($this->view->isEdit){
			$this->nodeAudit(); 
		}
		
		$this->view->pageContent = $this->view->render('controls/admin/forms/PageDetail.phtml'); 
		parent::indexAction($this->template);
		
	} 
	
	public function newVersionAction(){
		$this->setViewNode();
		$this->view->contentType = $this->request->getParam('contentType');		
		$this->view->isNewVersion = true;
		$this->view->content  = Content::getNewContent($this->view->contentType);	
		$this->view->isEdit = false;	
		$this->view->isVersion = true;	
		parent::renderModal('controls/admin/forms/PageForm.phtml');
		//$this->versionsAction();
	}
		
	public function settingsAction()
	{			
		$this->setViewNode();	
		$this->view->contentSettings = $this->view->render('controls/admin/forms/PagesFormSettings.phtml');  
	}
	
	public function languagesAction()
	{			
		$this->setViewNode();	 
		$this->view->contentLanguages = $this->view->render('controls/admin/forms/PagesFormLanguages.phtml');  
	}
	
	public function detailAction()
	{	
		if(isset($this->input->save)){ 
			
			if($this->input->state == 'DELETED'){ 
				$this->deleteAction();  
				return array(1, 'Smazáno');
			}
			 
			$lang = Zend_Registry::getInstance()->languages->language;
			$lang = $lang?$lang:'cz';
		
			
			list($state, $message) = $this->updateAction();  
			if($state){
				$this->_redirector->goto('index', 'pages', null, array('node' => $this->node->parentId, 'language' => $lang));
			} else {
				$this->addErrorInstantMessage($message );
			}   
		} 
		$this->view->isEdit = true;	
		$content = $this->view->content  = $this->node->getTheRightContent();
		//$content  = $this->node->getTheRightContent();		  
		//$this->view->contentId = $content->id;	 
		$this->view->inFileBranch = $this->request->getParam('inFileBranch');  
		
		
		$this->formAction();
		if(!$this->view->inFileBranch){
			$this->seoAction();
			$this->settingsAction();
			$this->historyAction(); 
			if($this->view->languages->isMultiLanguage){
				$this->languagesAction();
			} 
		}
		if($this->view->advertsPositions){
			$this->advertsAction();
		}
		
		
		if($this->view->isEdit){
			$this->nodeAudit(); 
		}
		
		$this->view->pageContent = $this->view->render('controls/admin/forms/PageDetail.phtml'); 
		parent::indexAction($this->template); 
	} 
	public function fileFormAction()
	{
		$content = $this->view->content  = $this->node->getPublishedContent();
		parent::renderModal('controls/admin/forms/PageFormFile.phtml');
	}
	
	public function formAction()
	{	
		$showVersions = $this->request->getParam('showVersions');		
		//e($showVersions);
		if($showVersions){
			$this->versionsAction();
			return;
		}
		
		$isVersion = $this->request->getParam('isVersion');
		if($isVersion){
			$this->view->isVersion = true;
		}
		
		$this->setViewNode();	
		  
		if($this->view->isEdit){
			$this->view->inFileBranch = $this->request->getParam('inFileBranch');		
			$contentId = $this->request->getParam('contentId');		
			
			if($contentId){
				$content = $this->view->content  = $this->node->getContent($contentId);
				$this->view->contentId = $contentId;	
				$this->view->inVersions = true;	
			} else {				
				$this->view->content  = $this->node->getTheRightContent();	
				//e($this->view->contentId);
			}	
			$this->view->contentType = $this->view->content->_name;		
		} else {
			$this->view->contentType = $ctype = $this->request->getParam('contentType');
			$this->view->content  = Content::getNewContent($this->view->contentType);	 
		}		 
		
		
		 
		$this->view->contentForm = $this->view->render('controls/admin/forms/PageForm.phtml');
	}
	
	/*
	public function editFormAction()
	{
		$this->view->inFileBranch = $this->request->getParam('inFileBranch');	
		$cnode = $this->request->getParam('contentNode');
		$contentId = $this->request->getParam('contentId');		
		$this->view->cnode = $this->cnode = $this->tree->getNodeById($cnode);
		if($contentId){
			$content = $this->view->content  = $this->cnode->getContent($contentId);
			$this->view->contentId = $contentId;	
			$this->view->inVersions = true;
			$this->view->content  = $this->cnode->getContent($contentId);		
		} else {
			$this->view->content  = $this->cnode->getPublishedContent();		
		}
		//$this->view->contentType = $content->userName;
		parent::renderModal('controls/admin/forms/PageEdit.phtml');
	}
	*/
	
	/*
	public function detailAction()
	{
		$cnode = $this->request->getParam('contentNode');
		$this->view->cnode = $this->cnode = $this->tree->getNodeById($cnode);		
		$this->view->inFileBranch = $this->request->getParam('inFileBranch');	
		parent::renderModal('controls/admin/forms/PageDetail.phtml');
	}
	*/
	
	public function seoAction()
	{			
		$this->setViewNode();	
		
		$this->view->contentSEO = $this->view->render('controls/admin/forms/PageFormSEO.phtml'); 
	}
	
	
		
	public function saveAction()
	{
		
		$ctype = $this->input->contentType; 
		$content = Content::init($ctype, $this->input, $this->acl);	
		if(method_exists($content, 'preSave')){
			$this->input = $content->preSave($this->input, $this->view); 
		}		 
		//pr($this->input);return ;
		$err = $this->checkFormNewPage(); 
		if(!$err){
			$err = $this->checkSEOForm();	
		}
		if(!$err){ // ok	
			
			//content
			$content = Content::init($ctype, $this->input, $this->acl);	
			
			if(method_exists($content, 'beforeSave')){
				$err = $content->beforeSave( $this->view);
				if($err){ 
					return array(0, $err);
		    		return ; 
				}
			} 
			
			$err2 = $content->save();
		 	
			//node				
			$parentId = $this->request->getParam('parentId');						
			$n = Node::init('ITEM', $parentId, $this->input, $this->view);
			
			//save		
	    	$this->tree->addNode($n, false , false);
	    	$this->tree->pareNodeAndContent($n->nodeId, $content->id, $content->_name);

			if(method_exists($content, 'afterNodeSave')){
				$content->afterNodeSave( $this->view, $n); 
			}
			
	    	parent::audit($n->title, $n->nodeId);
	    	
	    	$calledFrom = $this->request->getParam('calledFrom');	
	    	if($calledFrom == 'eshop'){
	    		return array(1, 'Data uložena', 0, $this->view->url(array('module' => 'cms', 'controller' => 'eshop','action' => 'listItems', 'node' => $this->nodeId), null,true));
	    		return ;
	    	}
	    	   
			//return array(1, 'Data uložena', 0, $this->view->url(array('module' => 'cms', 'controller' => 'pages','action' => 'list', 'node' => $this->nodeId), null,true));
			return array(1, 'Data uložena');
		} else {
			return array(0,$err);
		}				
	}
		
	public function updateAction()
	{		
	//	pr($this->input); return ;
				
		 
		$err = $this->checkFormEditPage();
		if(!$err){
			$err = $this->checkSEOForm();	
		}
		if(!$err){ // ok				
					
		
			if($input->state == 'DELETED'){
				$id = $this->node->nodeId;  
				parent::audit($this->tree->getNodeById($id)->title, $id, 'delete');
				$this->tree->removeNode($id, false);
				return array(1, 'Smazáno');
			}  
			
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
			  
			if(method_exists($this->view->content, 'beforeUpdate')){
				$err = $this->view->content->beforeUpdate( $this->view);
				if($err){
					return array(0, $err);  
		    		return ; 
				} 
			} 
					
			$this->view->content->update(false, false, $this->view, $this->input);    // provede onDelete

			//node   
			$this->node->initUpdate($this->input, $this->view);	 			    	  	
	    	$this->tree->updateNode($this->node, false); 	    	
	    	 
	    	$this->node->checkStateChange($this->input, $this->view->content);
	    		    	  
			if(method_exists($this->view->content, 'afterNodeUpdate')){   
				$this->view->content->afterNodeUpdate( $this->view, $this->node);     
			}
			
	    	parent::audit($this->node->title, $this->node->nodeId);	    	
			$calledFrom = $this->request->getParam('calledFrom');	    
			if($calledFrom == 'eshop'){
	    		return array(1, 'Data uložena', 0, $this->view->url(array('module' => 'cms', 'controller' => 'eshop','action' => 'listItems', 'node' => $this->nodeId), null,true));
	    		return ;
	    	}   
	    	 
			if($inVersions){ // ve verzich
				return array(1, 'Data uložena', '',  $this->view->url(array('action' => 'versions', 'node' => $this->node->nodeId)));
			} else {
				return array(1, 'Data uložena');
			}
			
		} else {
			return array(0,$err);
		}				
	}

	public function updateVersionAction()
	{
		$this->updateAction();
	}
	
	public function saveVersionAction()
	{
		//pr($this->input); 
		//return ;
		$err = $this->checkFormNewPage();
		if(!$err){
			$err = $this->checkSEOForm();	
		}
		if($this->input->contentType == 'SFSFile'){
			$err = $this->checkFileForm();
		}
		
		if(!$err){ // ok	
			$ctype = $this->input->contentType;
			$content = Content::init($ctype, $this->input, $this->acl, true);	
			$content->id = $content->getNextContentId($this->dbAdapter);
	    	$content->localId = $content->getNextContentLocalId($this->node->nodeId);
	    	
			$err2 = $content->save();
		
			//node
			$this->node->initUpdate($this->input, $this->view);				    	  	
	    	$this->tree->updateNode($this->node); 	    	
	    	$this->tree->pareNodeAndContent($this->node->nodeId, $content->id, $content->_name); 		
	    	$this->node->checkStateChange($this->input, $content);	
	    	
	    	parent::audit($this->node->title . ' - ' . $content->localId,$content->id);	    	
			return array(1, 'Data uložena', '', $this->view->url(array('action' => 'versions', 'node' => $this->node->nodeId)));
			
		} else {
			return array(0,$err);
		}				
	}
	
	
	public function versionsAction()
	{	
		
		parent::performMultiAction();
 				
		$this->view->tableActions = array();
		$this->view->curentViewState['action'] = 'versions';				
		$this->view->tableSort = $this->request->getParam('sort')?$this->request->getParam('sort'):'contentId';			
		$this->view->tableSortType = $this->request->getParam('sortType')?$this->request->getParam('sortType'):'';
		$this->view->tableFilter0 = $this->request->getParam('tableFilter0')?$this->request->getParam('tableFilter0'):'all';
		$this->view->tableParentTab = 'pageFormTab';							
		$this->view->tableFilters = array();				
			
		
		$versionsTable = array();
		foreach ($this->node->getContents() as $content){		
			$stateIco = Utils::getContentStateIcon($content);
			$versionsTable[] = array(
				'contentId' => $content->id,
				'localId' => $this->node->title . ' v.' . $content->localId,
				'type' => $stateIco . $content->userName,
				'state' => $this->view->contentStates[$content->state],
				'modif' => Utils::formatDate($content->dateModif),
			);
		}				
				
		$this->view->versionsTable = $versionsTable;
		$this->view->versionsSubmitFormUrl = $this->view->url(array('controller' => 'pages','action' => 'versionsTabActions'));
				
		$this->view->versionsTableHead = array(
			'localId' => array(
				'title' => 'Verze',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'pageFormTab'
			),
			'userName' => array(
				'title' => 'Typ obsahu',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'pageFormTab'
			),
			'state' => array(
				'title' => 'Stav obsahu',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'pageFormTab'
			),
			'dateModif' => array(
				'title' => 'Poslední modifikace',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'pageFormTab'
			)
		);
		
		$this->view->versionsTableActions = array(
			'edit' => array(
				'title' => 'Editovat',
				'type'  => 'tab-refresh',
				'tabId'  => 'pageFormTab',
				'url'   => $this->view->url(array('controller' => 'pages','action' => 'form', 'showVersions' => 0, 'isVersion' => 1,  'node'=>$this->node->nodeId, 'contentId'=>'%$%'))
			)				
		);
		
		if(count($versionsTable) > 1){
			$this->view->versionsTableActions['delete'] = array(
				'title' => 'Smazat',
				'type'  => 'tab-refresh',
				'tabId' => 'pageFormTab',
				'url'   => $this->view->url(array('controller' => 'pages','action' => 'deleteVersion',  'node'=>$this->node->nodeId, 'contentId'=>'%$%'))
			);
		}
		
    	parent::renderModal('/controls/admin/forms/_Versions.phtml');
	}
		
	public function checkFormEditPage()
	{			
		do{	
			if(!$this->input->pageTitle){
				$err = "Zadejte název stránky";			   
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
				$err = "Zadejte název stránky";			   
			    break;
			}				
			return false;
		} while (false);			
		return $err;
	}
	
	public function checkFileForm()
	{		
		//pr($this->inputGet);
		//exit();		
		do{	
			if(!$this->input->fullpath){
				$err = "Vyberte na Vašem počítači novou verzi souboru";			   
			    break;
			}				
			return false;
		} while (false);			
		return $err;
	}
	
	public function checkSEOForm()
	{		
		
		do{		
			if($this->input->nodePath){
				if(($this->input->nodePath{0} != '/')){
					$err = "Cesta musí začínat lomítkem";			   
				    break;
				}
				if(substr($this->input->nodePath , -1) == "/"){
					 $err = "Cesta nesmí končit lomítkem";			   
				     break;
			 	}	   
				if(strpos($this->input->nodePath, '.')){
					$err = "Zadejte cestu bez přípony (.html apod), přidá se automaticky";			   
				    break;
				}
				
				if($this->input->nodePath != $this->node->path){
					if(!Node::isPathAvailable($this->input->nodePath)){
						$err = "Zadaná cesta se již používá, zvolte jinou";			   			    	
						break;
					}
				}
			}					
				
			return false;
		} while (false);			
		return $err;
	}
	
	/*
	public function saveAction()
	{	
		$err = $this->checkForm();		
		if(!$err){ // ok				
			$this->node->title = $this->inputGet->title;			
			if($this->node->nodeId > 1 )
				$this->node->parentId = $this->inputGet->parentId;			
			
			$this->node->save($this->dbAdapter, $this->tree);
			parent::audit('',$this->node->nodeId);
			return array(1, 'Data uložena');
			//$this->_redirector->goto('index', 'structure', null, array('node' => $this->nodeId));
		} else {
			return array(0,$err);
		}				
	}
	
	public function deleteAction()	{	
		parent::audit('',$this->nodeId);
		$this->tree->removeNode($this->nodeId);
		$this->_redirector->goto('index', 'structure', null, array('node' => $this->node->parentId));
	}
	
	public function checkForm()
	{		
		do{	
			if(!$this->inputGet->title){
				$err = "Zadejte titulek";			   
			    break;
			}
			if($this->inputGet->parentId == $this->nodeId ){
				$err = "Uzel nemůže odkazovat sám na sebe";			   
			    break;
			}			
			if(!isset($this->inputGet->parentId)){
				$err = "Zadejte nadřazený uzel";			   
			    break;
			}	
			return false;
		} while (false);			
		return $err;
	}
	*/
	
	
	/* contetns */
	
	public function contentAction()
	{  		
		$this->view->subTemplate = 'controls/admin/contentList.phtml';
    	$this->indexAction();
    	
	}
	
	public function newcontentAction()
	{  		
		$ctype = $this->request->getParam('contentType');	
		$ctype = $ctype?$ctype:'HtmlFile';	
		if($ctype && in_array($ctype,array_keys($this->contentTypes))){	
			$c = 'content_'.$ctype;
			$c = new $c();						
			$this->view->contentProperties = $c->properties;
			$this->view->subTemplate = 'controls/admin/contentProperties.phtml';
		} else {
			parent::addErrorInstantMessage("Neplatný typ obsahu");
		}
		
    	$this->indexAction();
    	
	}
	
	
	public function performMultiaction($action, $id){
		switch ($action){
			case 'delete':
				parent::audit($this->tree->getNodeById($id)->title, $id, 'delete');
				$this->tree->removeNode($id, false);
				break;	 
			case 'deleteTag':
				e($id); 
				list($tagName, $domain) = explode('--', $id);   
				$tags = new module_Tags($domain);				
				$tagName = str_replace('_', ' ', $tagName);
				// $tag = $tags->getTagDetail($tagName);  
				$tags->deleteTag($this->view, $tagName);  
				break;
			case 'archivated':
					parent::audit($this->tree->getNodeById($id)->title, $id, 'archiveContent');
					$node = $this->tree->getNodeById($id);
					$node->archivePublishedContent();		
				break;			
			case 'published':
					parent::audit($this->tree->getNodeById($id)->title, $id, 'publishContent');
					$node = $this->tree->getNodeById($id);
					$content = $node->getTheRightContent();		
					$node->publishContent($content->id);
			break;	 
		}
	}
	
	
	/* TAGS */ 
	public function showDetailsWidgetAction()
	{  	
    	echo $this->view->render('/controls/admin/tabs/PagesDetailsWidget.phtml');
	}
	
	public function tagsHomeAction()
	{  	
    	echo '<h1>Štítky</h1>'; 
	}
	
	public function tagsDetailsAction() 
	{ 
		parent::performMultiAction(); 				 
		$this->view->tableActions = array('deleteTag' => 'Smazat'); 
		
		//$this->systemUsers);
		$users = new Users();
		$this->view->tableParentTab = 'vypisDole';  
		
		if($this->config->instance->domains){
			$this->view->domains = $this->config->instance->domains->toArray();
			$this->view->tableFilter0 = ($this->request->getParam('tableFilter0'))?$this->request->getParam('tableFilter0'):'0';  		 		 
			$this->view->tableFilters[] = helper_Input::addNotDefinedOption($this->view->domains, 'Všechny domény', '0');  
		} else {
			$this->view->tableFilter0 = 0; 
		}   		
		
		$this->view->curentViewState['action'] = 'tagsDetails'; 
		$this->view->tableSort = $this->request->getParam('sort')?$this->request->getParam('sort'):'tag'; 
		$this->view->tableSortType = $this->request->getParam('sortType')?$this->request->getParam('sortType'):'Desc' ;
		
		
		
		$tags = new module_Tags($this->view->tableFilter0);
		$allDomains = $tags->subdomain ? 0 : 1;
		$allTags = $tags->getUsedTagsBE('tag', $this->view->tableSortType, 7000, $allDomains); 
		 
		$this->view->tagsTable = array();
		foreach ($allTags as $tag){ 
			$this->view->tagsTable[] = array(
				$tag['tag'] . '--' . $this->view->tableFilter0,   
				$tag['tag'], 
				$tag['used'], 
				helper_FrontEnd::reduceText(strip_tags($tag['description']), 30, false, false) 
			); 
		} 
			  
		$this->view->tagsTableHead = array(
			'tag' => array(
				'title' => 'Štítek',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab', 
				'parentTab' => 'vypisDole'
			),
			'used' => array(  
				'title' => 'Použit x krát',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole' 
			),
			'description' => array(  
				'title' => 'Popis',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole' 
			)  
		); 
		
		$this->view->tagsTableActions = array();
		$this->view->tagsTableActions['detail'] = array(
			'title' => 'Detail štítku', 
			'type'  => 'modal',
			'url'   => $this->view->url(array('controller' => 'pages','action' => 'tagDetail', 'tag'=>'%$%'))
		); 
		/*
		$this->view->tagsTableActions['delete'] = array(
			'title' => 'Smazat',
			'type'  => 'tab-refresh',
			'tabId' => 'vypisDole',
			'url'   => $this->view->url(array('controller' => 'pages','action' => 'deletePage','contentNodeId'=>'%$%'))
		); */ 
		 
		echo $this->view->render('/controls/admin/forms/PagesDetailsTagsList.phtml'); 
	}
	
	function tagDetailAction(){
		list($tagName, $domain) = explode('--', $this->request->getParam('tag'));   
		$tags = new module_Tags($domain);
		$tag = $tags->getTagDetail($tagName); 
		
		$this->view->domain = $domain; 
		parent::initFormValuesFromArray($tag); 
		parent::renderModal('/controls/admin/forms/PagesDetailsTag.phtml');	 
	}
	
	public function saveTagAction()
	{	
		$err = 'Zadejte údaje';
		$domain = $this->input->domain;
		$tags = new module_Tags($domain);
		if($this->input->tag){// ok 				
			$result = $tags->updateTag($this->view, $this->input->tagOrig, $this->input->tag, $this->input->description);	 
			return array(1,'Data uložena'); 
		} else {
			return array(0, $err);
		} 
	}
}

