<?php
/*
	Shared files
*/
class Cms_StructureController extends CmsController
{

	public function init()
	{
				
		$this->fields = array('title', 'parentId', 'title', 'description', 'parentId', 'contentType');
		parent::init();
		
		$this->view->title .= ' - Strom webu';
		$this->template = 'controls/admin/Structure.phtml';  		
		$this->view->subTemplate = 'controls/admin/contentList.phtml';  		
					
		if($this->doPageInit && $this->view->logged){
			$this->initPage();   
		} 
	}
	
	private function initPage()
	{
		//pr($this->view->user)  ;
		$this->nodeRootUser = 1;
	
		if($this->view->user->group == 'OnlyEshop')
		{
			$this->nodeRootUser = 3801;
		}
	
		$this->view->isPopup = $this->isPopup = $this->request->getParam('isPopup')?1:0; 
		$this->callBackInput = $this->request->getParam('callBackInput');
		//$this->view->mainTree = $this->mainTree = $this->tree->getTree(1,true);
		$this->view->selectedLeftHelpPane = false;
		$this->view->overviewTypes = $this->config->overviewTypes;
		$this->view->sortTypes = Node::$sortTypes;
		
    	$node = $this->request->getParam('node');
		if($this->view->user->group == 'OnlyEshop')
		{
			$node = 3801;
		}
    	$this->isCalledRemotelly = $this->request->getParam('inRemoteModule')?true:false;
    	$this->view->rootNodeId = 1;
    	if($this->isCalledRemotelly){
	    	if($this->request->getParam('helpnode') ){
	    		$node = $this->request->getParam('helpnode');
	    		$this->view->rootNodeId = 3;
	    	} elseif ($this->request->getParam('intranetnode')){
	    		$this->view->overviewTypes = $this->config->modules->intranet->overviewTypes;	    		
	    		$node = $this->request->getParam('intranetnode');
	    		$this->view->rootNodeId = 99;
	    	}
    	}
    			
		$this->view->parentSelect =  $this->tree->getParentSelect($this->view->rootNodeId);
			
		$node = $node?$node:1;	
		//e($this->session->currentNodePath);
		
		$n = $this->tree->getNodeById($node);
		
		if(!$n || !$n->showInCurentLanguage()){
			$node = 1;
			$this->view->node = $this->node = $this->tree->getNodeById($node);			
		} else {
			$this->view->node = $this->node = $n;			
		}
		
		 
		$this->registry->curentNodeId = $this->nodeId = $this->view->nodeId = $node;    
		 
		$this->session->currentNodePath =  $this->view->currentNodePath = $this->tree->getNodeIdPath($node);	
		
		$this->session->currentSysPath =  substr($this->session->currentNodePath, 2);  
		
		$this->view->node = $this->node = $this->tree->getNodeById($node);

		$this->view->curentParent = $this->session->curentParent = $this->node->parentId;
		$this->view->curentTreeNode = $this->view->curentNode = $this->session->curentNode = $this->nodeId;
		$this->view->template = $this->node->getTemplate();
		$this->view->overviewType = $this->node->getOverviewType();
		
		$this->view->contentTypes = $this->contentTypes = Content::getOverviewAllowedContentTypes($this->node->getTheRightContent()->allowableOverviews);
		
		$this->view->showBottomPanel = true;
		$this->view->bottomContentTitle = 'Výpis podsekcí';
		$this->view->bottomContentHref = $this->view->url(array('action' => 'list'));
		
		
		$this->view->isEdit = $this->request->getParam('isEdit')?true:false;
		$this->view->contentStates = $this->config->instance->workflow->toArray();
		
		$this->initHelp('structure');  
		
		$this->view->leftColl = $this->view->render('parts/leftStructure.phtml'); 
		require_once('content/cpMap.php'); 
		$this->view->cp_Translate = $_cpMap;	
	}
	
	function jstreeAction(){
		
		
		$data = $_GET;    
		
		header("HTTP/1.0 200 OK");  
		header('Content-type: application/json; charset=utf-8');
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Pragma: no-cache");
		
		$inPages = $this->request->getParam('inpages')?1:0;
		$this->inputGet->operation = $this->inputGet->operation?$this->inputGet->operation:$this->input->operation;
		switch ($this->inputGet->operation){
			case 'move_node':
				$node = $this->tree->getNodeById($this->input->id);	 
				$node2 = $this->tree->getNodeById($this->input->ref); 
				 
				$inp = new stdClass();
				$inp->parentId = $node2->nodeId;
				$node->saveParent($inp, $this->view);
				$node->save();		   
				
				$result = array('status' => 1, 'id' => $this->input->ref);  
				break; 
			case 'rename_node':
				$node = $this->tree->getNodeById($this->input->id);	 
				$node->title = $this->input->title;
				$node->save();				
				$result = array('status' => 1 );
				break;  
			case 'get_children':
				$result = array();   
				if($data["id"] == 0){
					$nodes = array($this->tree->getNodeById($this->nodeRootUser));
					
				} else {  
					$nodes = $this->tree->getNodeById($data["id"])->getChildren('FOLDER');
				}
				foreach($nodes as $n) {
					$k = $n->nodeId;
					if(!$this->view->isSuperAdmin &&  $n->showInPages != 1){
						continue; 
					}  
					if(!$n->showInCurentLanguage()){
						continue;   
					} 
 					$result[] = array(
						"attr" => array("id" => "node_".$k, "rel" => ($k<4?"drive":"folder")),
						"data" => $n->title,   
						"state" => ($this->tree->hasChildren($k, 'FOLDER')?"closed":"")  
					);
				}  
				break;
		}
		$r =  json_encode($result);  
		echo $r;
		die();  
	} 
	
	function showbannerAction(){ 
		$ident = $this->request->getParam('bId');
		$b = 0;
		$pos = $this->node->adverts[$this->request->getParam('pos')];
		if(count($pos)){
			foreach ($pos as $advert){
				if($advert->identificator == $ident){
					$b = $advert;
				}
			}
		} 
		
		if($b){
			$xhtml = $this->view->render('indexFELike.phtml');
			$ban = $b->render($this->view);
			$xhtml = str_replace('xxxxx', $ban, $xhtml);
		}
		echo $xhtml;
	}
	
	public function multiAction(){		
		parent::performMultiAction();
	}
		
	public function indexAction()
	{
    	$this->initPage();  
		$this->listAction();   
		 
		parent::indexAction($this->template);	
	}
	
	public function showTreeAction()
	{  		 
		$this->view->curentController = $node = $this->request->getParam('cController');
    	echo $this->view->render('controls/admin/Tree.phtml');   	
	}
	
	public function homeAction()
	{  	
		//e($this->session->user->group);
		/*
		$n = $this->tree->getNodeById(191);
		$n->created = '2007-11-15 10:05:23';
		$n->save($this->dbAdapter, $this->tree);
		*/
		/*
		$n = $this->tree->getNodeById(1);
		$n->parentId = 0;
		$n->save($this->dbAdapter, $this->tree);
		*/
		/*
		
		$content = $contentName = 'content_Overview';
			$content = new $content();
	    	
			$content->id = $content->getNextContentId($this->dbAdapter);;
	    	$content->localId = 1;
			$content->dateCreate = $content->dateModif = Utils::mkTime();
			$content->owner = $content->modifiedBy = $this->session->user->username;
			$content->state = 'PUBLISHED';
			//$content->properties[0]->value = 'Html';						
			$content->save();
			
		$n = new Node();
	    	$n->type = 'TREE';
	    	$n->sort = 'dateCreate';
	    	$n->title = 'Intranet';
	    	$n->description = '';
	    	$n->showInNavigation = '0';
	    	
	    	$n->path = '/intranet';
	    	
	    	$n->nodeId = 5;
	    	$n->parentId = 0;	    	
	    	$n->dateCreate = $n->dateModif = Utils::mkTime();
	    	$n->owner = $n->modifiedBy = $this->session->user->username;	    	    	
	    	  		
	    	$this->tree->addNode($n);
	    	$this->tree->pareNodeAndContent($n->nodeId, $content->id, $contentName);
	   
		
		$this->tree->save('intranet');
	   */
    	echo $this->view->render('/controls/admin/forms/StructureHome.phtml');
    	
	}
	
	function setViewNode(){
		if(!$this->view->isEdit){
			$this->view->node = new Node();
		}	
	}
				
	public function newAction()
	{
		$this->view->parentId = $this->request->getParam('parentId');
		$this->view->isEdit = false;		
		$this->setViewNode();				

		if(isset($this->input->save)){  
			list($state, $message, $redir) = $this->saveNewAction();   
			if($state){
				$this->_redirector->gotoUrlAndExit($redir);
			} else {  
				$this->addErrorInstantMessage($message );
			}   
		} 
		
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
		
		$this->view->pageContent = $this->view->render('controls/admin/forms/StructureDetail.phtml'); 
		parent::indexAction($this->template);
	}
	
	public function newVersionAction(){
		$this->setViewNode();
		$this->view->contentType = $this->request->getParam('contentType');		
		
		$this->view->content  = Content::getNewContent($this->view->contentType);	
		$this->view->isEdit = false;	
		$this->view->isVersion = true;	
		parent::renderModal('controls/admin/forms/StructureForm.phtml');
		//$this->versionsAction();
	}
	
	public function rendersrovnaceAction()
	{
		$this->view->content = new content_OverviewProducts();
		$node = $this->tree->getNodeById($this->request->getParam('node'));
		$content = $node->getPublishedContent();
		$this->view->valzbozicz = explode('|', $content->getPropertyValue('zbozicz'));
		$this->view->valheureka = explode('|',$content->getPropertyValue('heureka'));
		$this->view->valmerchant = explode('|',$content->getPropertyValue('merchant'));
		echo $this->view->render('controls/admin/forms/Srovnace.phtml'); 
	}
	
	public function detailAction()
	{	
		
		$lang = Zend_Registry::getInstance()->languages->language;
		$lang = $lang?$lang:'cz';
		
		if(isset($this->input->save)){  
			list($state, $message) = $this->saveAction();   
			if($state){
				$this->_redirector->goto('index', 'structure', null, array('node' => $this->node->parentId, 'language' => $lang ));
			} else {
				$this->addErrorInstantMessage($message );
			}    
		} 
		
		$this->view->isEdit = true; 
		
		//e($this->request->getParam('contentType'));
		$showVersions = $this->request->getParam('showVersions');		
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
			$contentId = $this->request->getParam('contentId');		
			
			if($contentId){
				$content = $this->view->content  = $this->node->getContent($contentId);
				$this->view->contentId = $contentId;	
				$this->view->inVersions = true;
			} else {	
				$this->view->content  = $this->node->getTheRightContent();		
			}	
			$this->view->contentType = $this->view->content->_name;		
		} else {
			$this->view->contentType = $ctype = $this->request->getParam('contentType');
			$this->view->content  = Content::getNewContent($this->view->contentType);	
		}	  
		$this->view->node = $this->node;
		 $this->view->loadsrovnavace = $this->view->contentType =='content_OverviewProducts';
		if(method_exists($this->view->content, 'initAdverts')){
			$this->view->content->initAdverts();
			//$content->setAdvertsFromNode($this->node->adverts);
			$this->view->advertsPositions = $this->view->content->getAdverts(); 
			$this->view->aStats = new module_Advertising_AdvertStats();
		}  
		
		
		$this->view->contentId = $this->view->content->id;
		
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
		
		
		
		 
		$this->view->pageContent = $this->view->render('controls/admin/forms/StructureDetail.phtml'); 
		parent::indexAction($this->template);   
	}
	
	/*
	public function formAction()
	{	
		$this->setViewNode();		
		$this->view->contentType_new = $ctype = $this->request->getParam('contentType');			
		$this->view->content  = Content::getNewContent($this->view->contentType_new);	
		
		$this->template = 'controls/admin/forms/StructureForm.phtml'; 
		parent::renderModal($this->template);
	}
	*/
	public function formAction()
	{	
		//e($this->request->getParam('contentType'));
		$showVersions = $this->request->getParam('showVersions');		
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
			$contentId = $this->request->getParam('contentId');		
			
			if($contentId){
				$content = $this->view->content  = $this->node->getContent($contentId);
				
				$this->view->contentId = $contentId;	
				$this->view->inVersions = true;	
			} else {	
				$this->view->content  = $this->node->getTheRightContent();		
			}	
			$this->view->contentType = $this->view->content->_name;		
		} else {
			$this->view->contentType = $ctype = $this->request->getParam('contentType');
			$this->view->content  = Content::getNewContent($this->view->contentType);	
		}		
		$this->view->contentForm = $this->view->render('controls/admin/forms/StructureForm.phtml'); 
	}
	
	public function advertsAction()
	{			
		$content = $this->node->getTheRightContent();
		$content->setAdvertsFromNode($this->node->adverts);
		
			$this->view->aStats = new module_Advertising_AdvertStats();
		$this->setViewNode();		 	 
		$this->view->contentAdverts = $this->view->render('controls/admin/forms/StructureFormAdverts.phtml'); 
	}
	
	public function addadvertformAction()
	{		 	

		$this->view->aStats = new module_Advertising_AdvertStats();
		$this->setViewNode();	  
		$this->view->identificator = $this->request->getParam('identificator');
		$this->view->banner = new module_Advertising_Advert($this->view->identificator);		
		echo $this->view->render('controls/admin/forms/_AdvertForm.phtml'); 
		die();  

	}

	public function advertFormAction()
	{			
		$this->setViewNode();	  
		$this->view->identificator = $this->request->getParam('identificator');
		$this->view->banner = new module_Advertising_Advert($this->view->identificator);		
		
		$this->view->contentAdverts = $this->view->render('controls/admin/forms/PagesFormAdverts.phtml');  	   
	}   
	
	public function seoAction()
	{			
		$this->setViewNode();	 
		$this->view->contentSEO = $this->view->render('controls/admin/forms/StructureFormSEO.phtml');
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
	
	
	public function languagesAction()
	{			
		$this->setViewNode();	 
		$this->view->contentLanguages = $this->view->render('controls/admin/forms/StructureFormLanguages.phtml');  
	}
	
	public function settingsAction()
	{			 
		$this->setViewNode();	 
		$this->view->contentSettings = $this->view->render('controls/admin/forms/StructureFormSettings.phtml');  
	}
		
	public function saveNewAction()
	{				
		$err = $this->checkFormNewNode();
		if(!$err){
			$err = $this->checkSEOForm();	
		}
		if(!$err){
			$err = $this->checkSettingsForm();	
		}
		if(!$err){ // ok		
			//content
			$ctype = $this->input->contentType;
			$content = Content::init($ctype, $this->input, $this->acl);	
			
			if(method_exists($content, 'beforeSave')){
				$err = $content->beforeSave($this->view);
				if($err){
					return array(0, $err);
		    		return ; 
				} 
			}
			
			$lang = Zend_Registry::getInstance()->languages->language;
			$lang = $lang?$lang:'cz';
			
			
			$err2 = $content->save();	
			
			//node				
			$parentId = $this->request->getParam('parentId');						
			$n = Node::init('FOLDER', $parentId, $this->input, $this->view);
			$n->saveAdverts($content->adverts, $content);
			
			//save		
	    	$this->tree->addNode($n);
	    	$this->tree->pareNodeAndContent($n->nodeId, $content->id, $content->_name); 
	    				
	    	parent::audit($n->title, $n->nodeId);
	    
			
			if($this->isCalledRemotelly){
				if($this->request->getParam('helpnode')){
					$url = $this->view->url(array('controller' => 'help','action' => 'index', 'inRemoteModule' => '0', 'helpnode' => $n->nodeId, 'node' => 1));
				} elseif ($this->request->getParam('intranetnode')){
					$url = $this->view->url(array('controller' => 'intranet','action' => 'index', 'inRemoteModule' => '0', 'intranetnode' => $n->nodeId, 'node' => 1));			
				}
			} else {				
				$url = $this->view->url(array('controller' => 'structure','action' => 'index', 'node' => $n->parentId, 'language' => $lang));
			}	    ;
			return array(1, 'Data uložena', $url);
			
		} else {
			return array(0,$err);
		}	 
	}
	
	public function saveAction()
	{			  
		$err = $this->checkForm();		
		if(!$err){
			$err = $this->checkSEOForm();	
		}
		if(!$err){
			$err = $this->checkSettingsForm();	
		}
		
		if(!$err){ // ok		
			
			if($this->input->state == 'DELETED'){
				
			
			  
				$this->deleteAction();  
				return array(1, 'Smazáno');
			}
		
			// content			
			$contentId = $this->request->getParam('contentId');
			/*
			e($contentId); die(); 
				pr($_POST );
			pr($this->input); die();  
			 */ 
			
		
			$this->view->content = Content::initUpdate($this->node, $this->input, $contentId);	      
			$this->view->contentId = $contentId;	
		
			if(method_exists($this->view->content, 'beforeUpdate')){
				$err = $this->view->content->beforeUpdate( $this->view);
				if($err){  
					return array(0, $err);
		    		return ;   
				}
			}
//			pr($this->view->content);
			$this->view->content->update();
			  		
			//node

			$this->node->initUpdate($this->input, $this->view);				    	  	
			$this->node->saveAdverts($this->view->content->adverts, $this->view->content);
	    	$this->tree->updateNode($this->node); 	    	
	    	$this->node->checkStateChange($this->input, $this->view->content);
	    	
	    	
			if($this->isCalledRemotelly){
				if($this->request->getParam('helpnode')){
					$url = $this->view->url(array('controller' => 'help','action' => 'index', 'inRemoteModule' => '0', 'helpnode' => $this->node->nodeId, 'node' => 1));
				} elseif ($this->request->getParam('intranetnode')){
					$url = $this->view->url(array('controller' => 'intranet','action' => 'index', 'inRemoteModule' => '0', 'intranetnode' => $this->node->nodeId, 'node' => 1));			
				}				
			} else {				
				$nid = $this->request->getParam('refreshNode')?$this->request->getParam('refreshNode'):$this->node->nodeId;
				$url = $this->view->url(array('controller' => 'structure','action' => 'index', 'node' => $nid));
			}
			
	    	if($this->input->pageTitle != $this->node->title){
		    	$this->fixChildrenPath($this->node); 	         	 
		    	$this->node->save($this->dbAdapter, $this->tree, true);   
	    	} 
			 
			$t = $this->node->title==$this->input->title?$this->node->title:$this->node->title . ' (' . $this->input->title . ')';
			parent::audit($t, $this->node->nodeId); 
							
			if($contentId){ // ve verzich  
				return array(1, 'Data uložena', '',  $this->view->url(array('action' => 'versions', 'node' => $this->node->nodeId)));
			} else {
				return array(1, 'Data uložena', $url);
			}
			
			
			//$this->_redirector->goto('index', 'structure', null, array('node' => $this->nodeId));
		} else {
			return array(0,$err);
		}				
	}
	
	function fixChildrenPath($node){
		$child = $node->getChildren('BOTH');
		foreach ($child as $n){
			$n->path = $node->path .'/' . Utils::generatePathName($n->title, '', $node->path . '/');
			$n->path = str_replace('//', '/', $n->path);  
			$n->save($this->dbAdapter, $this->tree, false); 	
			if($n->type == 'FOLDER'){ 
				$this->fixChildrenPath($n);
			}  
		} 
		 
	}  
	
	public function updateVersionAction()
	{
		$this->saveAction();
	}
	
	public function saveVersionAction()
	{
		$err = $this->checkForm();		
		if(!$err){
			$err = $this->checkSEOForm();	
		}
		if(!$err){
			$err = $this->checkSettingsForm();	
		}
		
		
		if(!$err){ // ok	
			$ctype = $this->input->contentType;
			$content = Content::init($ctype, $this->input, $this->acl, true);	
			$content->id = $content->getNextContentId($this->dbAdapter);;
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
				'title' => 'Typ',
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
				'title' => 'Posl. modifikace',
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
				'url'   => $this->view->url(array('controller' => 'structure','action' => 'form', 'showVersions' => 0, 'isVersion' => 1,  'node'=>$this->node->nodeId, 'contentId'=>'%$%'))
			)				
		);
		
		if(count($versionsTable) > 1){
			$this->view->versionsTableActions['delete'] = array(
				'title' => 'Smazat',
				'type'  => 'tab-refresh',
				'tabId' => 'pageFormTab',
				'url'   => $this->view->url(array('controller' => 'structure','action' => 'deleteVersion',  'node'=>$this->node->nodeId, 'contentId'=>'%$%'))
			);
		} else {
			$this->view->versionsTableActions['blank'] = array();	
		}
		
    	parent::renderModal('controls/admin/forms/_Versions.phtml');
	}
	
	public function deleteAction()	{	
		if($this->nodeId > 3){
			parent::audit($this->node->title,$this->nodeId);
			$this->tree->removeNode($this->nodeId); 
		}
		$this->_redirector->goto('index', 'structure', null, array('node' => $this->node->parentId));
		
	}	
	
	public function deletePageAction()	{	
		$node = $this->request->getParam('section');
		if($node){
			parent::audit($this->tree->getNodeById($node)->title, $node,  'delete');
			$this->tree->removeNode($node);
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
	
	public function userSortAction(){		
		$move = $this->request->getParam('move');	
		if($move){
			$nodeToMove = $this->tree->getNodeById($this->request->getParam('id'));
			$nodeToMove->changeUserOrder($move, $this->node->sort);		
			parent::audit($this->tree->getNodeById($id)->title, $nodeToMove->nodeId, 'sortChange');	
		}	
		$this->listAction();
	}
	
	public function performMultiaction($action, $id){
		switch ($action){
			case 'delete':
				parent::audit($this->tree->getNodeById($id)->title, $id, 'delete');
				$this->tree->removeNode($id);
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
	  
	
	public function sortAction()
	{
		$stringSort = $this->inputGet->sort;
		$this->saveSortDD($stringSort);   
	}
	 	
	
	public function saveSort($string) 
	{
		
	} 
	
	public function listAction()
	{
		
		
		parent::performMultiAction(); 
		
		
		$this->view->sortUrl = '/cms/structure/sort';
		 
		$getItems = $this->request->getParam('getItems');
		$isAjax = $this->request->getParam('ajax');
		$nodeId = $this->request->getParam('node');
		$nodeId = $nodeId?$nodeId:1;
		if($this->view->user->group == 'OnlyEshop')
		{
			$nodeId = 3801;
		}
	
		$parentNode = $this->tree->getNodeById($nodeId); 
		 
		$params = array(); 
		$dg = new DataGrid('pagesTable');
		$dg->setRefresUrl($this->view->url(array('action' => 'list', 'getItems' => 1, 'ajax' => 1)))
			->setTitle('Výpis stránek')
			->setHeight(400) 
			->setItemsPerPageOptions('10, 15, 20, 30, 50, 100', 50) 
			; 
			
		

		$dg->setHeaders( 
			array( 
				array('Název', 'title', 350, 'true', 'left', 'false'),
				array('Typ', 'ctype', 120, 'false', 'left', 'false'), 
				array('Vytvořeno', 'n.created', 60, 'true', 'left', 'false'),
				array('Změněno', 'n.dateModif',  60, 'true', 'left', 'false'),
				array('Stav', 'state',  60, 'false', 'left', 'false'),
				array('ID', 'n.id', 70, 'true', 'center', 'true')   
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
		$this->view->refreshTree = 'demo';
		
		$lang = Zend_Registry::getInstance()->languages->language;
		$lang = $lang?$lang:'cz';
		
		if($getItems){ 
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
					->addWhereBind('type', '=', 'FOLDER') 
					->addWhereBind('deleted', '=', '0')  
					->addWhereBind('languages', 'LIKE', '%' . $lang . '%')  
					->addWhereBind('parent', '=', $nodeId)  
					->setLimit($start, $rp);   
					  
				
				list($rowsTotalCount, $rows, $currentPage) = $dg->getRows($this->view->defaultSort, $this->view->defaultSortType);
			}
			// e($rowsTotalCount);  pr($rows);  
			$rowsFormated = array();
			
			foreach($rows AS $row){
				//If cell's elements have named keys, they must match column names
				//Only cell's with named keys and matching columns are order independent.
				$node = $this->tree->getNodeById($row['id']);  
				$c = $node->getTheRightContent();  
				$state = $this->view->contentStates[$c->state];
				//e($state);
					 
				if(!$this->view->isSuperAdmin &&  $node->showInPages != 1){
					continue; 
				}  

				
				$editUrl = $this->view->url(array('controller' => 'structure','action' => 'detail', 'node'=> $row['id'], 'ajax' => 0, 'contentId' => 0));
				
				$disabled = '';
				 if($this->view->user->group !='Superadmin' && $row['id'] == 3801)
				 {
				 	$disabled = 'disabled';
				 }
				$entry = array(   
					'id'=>$row['id'],  
					'cell'=>array(   
					
						'fullname'=> '<input name="chbx[' . $row['username'] . ']" type="checkbox" /> <a href="' . $editUrl . '">' . $row['fullname'] . '</a>',
				
						'title'=> '<input name="chbx[' . $row['id'] . ']" type="checkbox" '.$disabled.' /> <a href="' . $editUrl . '">' . $row['title'] . '</a>'  . Utils::getFrontEndLink($node->path, false, '', false, 0, $this->view),  
						'ctype'=> $c->userName, 
						'n.created'=> Utils::formatDate($row['created']),  
						'n.dateModif'=> Utils::formatDate($row['dateModif']),  
						'state' => $state,
						'n.id'=> $row['id'] . ($this->view->hasDD?'<a class="usort"></a>':''),  
					),
				);
				$rowsFormated[] = $entry;
			}
			
			if($isAjax){
				$rowsTotalCount = $rowsTotalCount?$rowsTotalCount:0; 
				$dg->renderAjax($currentPage, $rowsTotalCount, $rowsFormated);
				die();  
			};
		}   
		$this->view->pagesList = $dg->render($this->view, 'controls/admin/lists/PagesStandard.phtml');
	}
	
	public function checkForm()
	{		
		do{	
			if(!$this->input->pageTitle){
				$err = "Zadejte titulek";			   
			    break;
			}
			/*
			if(($this->input->parentId == $this->nodeId) && $this->node->nodeId > $this->view->rootNodeId ){
				$err = "Uzel nemůže odkazovat sám na sebe";			   
			    break;
			}	
			if(!isset($this->input->parentId) && $this->node->nodeId > 1 ){
				$err = "Zadejte nadřazený uzel";			   
			    break;
			}	
				
			*/	
			return false;
		} while (false);			
		return $err;
	}
	
	public function checkSettingsForm()
	{		
		//pr($this->input);
		//echo $this->input->parentId . '-'. $this->view->rootNodeId;
		do{		
			/*		
			if(($this->input->parentId == $this->nodeId) && $this->node->nodeId > $this->view->rootNodeId ){
				$err = "Uzel nemůže odkazovat sám na sebe";			   
			    break;
			}
			*/		
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
				if($this->input->nodePath != $this->node->path && $this->input->parentId_new > 1){
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
	
	public function checkFormNewNode()
	{		
		//pr($this->input);
		//exit();		
		do{	
			if(!$this->input->pageTitle){
				$err = "Zadejte titulek";			   
			    break;
			}	
			return false;
		} while (false);			
		return $err;
	}
}
