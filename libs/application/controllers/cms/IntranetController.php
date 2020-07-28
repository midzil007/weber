<?php
/*
	intraent
*/
class Cms_IntranetController extends CmsController
{
	public function init()
	{	
		parent::init();
						
		if($this->doPageInit)
			$this->initPage();
		
	}
	
	private function initPage()
	{    	
		$intranetnode = $this->request->getParam('intranetnode');
    	$intranetnode = $intranetnode?$intranetnode:99;
    	
		$this->session->currentIntranetNodePath =  $this->tree->getNodeIdPath($intranetnode);	
		$this->view->currentIntranetNodePath = $this->session->currentIntranetNodePath?$this->session->currentIntranetNodePath:'/99/165/166';
		//e($this->view->currentIntranetNodePath);
		$this->view->curentTreeNode = $this->nodeId = $intranetnode;
		$this->view->node = $this->node = $this->tree->getNodeById($intranetnode);		
		
		
		//pr($this->node);
		
		$this->view->title .= ' - Intranet';
		$this->template = 'controls/admin/modules/Intranet/Intranet.phtml';		
		$this->moduleEnquiry = new module_Enquiry();
		
		$this->view->showTree = false;
		$this->view->showIntranetTree = true;
		$this->view->selectedLeftHelpPane = false;
		/*
		if($this->request->__get('e')){
			$this->view->urlAdd = '?e=' . $this->request->__get('e') . '&vote=' . $this->request->__get('vote');
		}
		*/
		
		if ($this->acl->isAllowed($this->session->user->group, 'approveIntranetFiles')){
			$this->view->showBottomPanel = true;
			$this->view->bottomContentTitle = 'Výpis';
			$this->view->bottomContentHref = $this->view->url(array('controler' => 'intranet','action' => 'welcome'));
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
    	echo $this->view->render('controls/admin/modules/Intranet/IntranetHome.phtml');   	
	}
	
	public function performMultiaction($action, $id){
		switch ($action){
			case 'delete':
				parent::audit($this->tree->getNodeById($id)->title,$id, 'delete');
				$this->tree->removeNode($id);
				break;
			case 'unapprove':
			case 'approve':
				$node = $this->tree->getNodeById($id);
				$node->intranetAprroved = ($action=='approve'?1:0);
				$this->tree->updateNode($node);   
				parent::audit($this->tree->getNodeById($id)->title,$id, $action);
				break;
			
		}
	}
	
	public function deleteAction()	{	
		if($this->nodeId > 3){
			parent::audit($this->node->title,$this->nodeId);
			$this->tree->removeNode($this->nodeId);
		}
		$this->_redirector->goto('index', 'intranet', null, array('node' => $this->node->parentId));
		
	}
	
	public function getFiles( $isAdmin = false )
	{  						
		$this->view->tableSort = $this->request->getParam('sort')?$this->request->getParam('sort'):'dateCreate';			
		$this->view->tableSortType = $this->request->getParam('sortType')?$this->request->getParam('sortType'):'';
		if(!$this->request->getParam('sort')){
			$this->view->tableSortType = 'Desc';
		}
		
		$this->view->tableFilter0 = $this->request->getParam('tableFilter0')?$this->request->getParam('tableFilter0'):'all';
				
		if($isAdmin){
			$this->view->tableActions = array('delete' => 'Smazat');			
			$this->view->curentViewState['action'] = 'fileApprove';	
			$this->view->tableFilters[] = array();	
		} else {
			$this->view->tableFilter0 = 'approved';
			$this->view->tableActions = array();	
			$this->view->curentViewState['action'] = 'welcome';	
			$this->view->tableFilters = array();	
					
		}
		//pr($this->request);			
		$sortFunction = $this->view->tableSort . $this->view->tableSortType;		
		$children = $this->tree->getNodeChildren($this->nodeId, 'ITEM', $sortFunction);
		//e($sortFunction);					
		$filesTable = array();
		foreach ($children as $child){
					
			$publishedContent = $child->getPublishedContent();
			
			if($this->view->tableFilter0 != 'all' || !$isAdmin){
				//e($this->view->tableFilter0);
				switch ($this->view->tableFilter0){
					case 'notApproved':						
						if($child->intranetAprroved){
							continue 2;
						}
						break;	
					case 'approved':						
						if(!$child->intranetAprroved){
							continue 2;
						}
						break;					
				}
			}
			
			$title = $child->title;
							
			//ikona typu souboru
			$ico = '/admin/images/icons/filetype/' . Utils::getExtension($child->path) . '.gif';
			if(!file_exists($this->config->htdocsRoot . $ico)){
				$ico = '/admin/images/icons/filetype/file.gif';
			}	
			
			if($isAdmin){
				$filesTable[] = array(
					'nodeId' => $child->nodeId,
					'title' => $title,	
					'approved' => $child->intranetAprroved?'<span class="cgreen">schváleno</span>':'<span class="cred">neschváleno</span>',			
					'feLink' => Utils::getFrontEndLink('/' . content_SFSFile::getSFSPath($child->nodeId, $child->path)),	
					'type' => '<img src="' . $ico . '" /> ' . Utils::getExtension($child->path) . '',
					'created' => Utils::formatDate($child->dateCreate),
					'modif' => Utils::formatTime($publishedContent->dateModif),
				);
			} else {
				$filesTable[] = array(
					'title' => Utils::getFrontEndLink('/' . content_SFSFile::getSFSPath($child->nodeId, $child->path), true, $title),	
					'type' => '<img src="' . $ico . '" /> ' . Utils::getExtension($child->path) . '',
					'created' => Utils::formatDate($child->dateCreate),
					'modif' => Utils::formatTime($publishedContent->dateModif),
				);
			}
						
		}
		
		$this->view->filesTable = $filesTable;
				
		if($isAdmin){
			$this->view->filesTableHead = array(
				'title' => array(
					'title' => 'Název',
					'atribs' => array(),
					'sortUrlType' => 'refresh-tab',
					'parentTab' => 'vypisDole'
				),
				'approved' => array(
					'title' => 'nohead',
					'atribs' => array('style' => 'width:60px;'),
				),
				
				'show' => array(
					'title' => 'nohead',
					'atribs' => array('style' => 'width:20px;'),
				),
				'publishedContent' => array(
					'title' => 'Typ obsahu',
					'atribs' => array('style' => 'width:130px;'),
					'sortUrlType' => 'refresh-tab',
					'parentTab' => 'vypisDole'
				),
				'dateCreate' => array(
					'title' => 'Datum vytvoření',
					'atribs' => array('style' => 'width:130px;'),
					'sortUrlType' => 'refresh-tab',
					'parentTab' => 'vypisDole'
				),
				'dateModif' => array(
					'title' => 'Poslední modifikace',
					'atribs' => array('style' => 'width:150px;'),
					'sortUrlType' => 'refresh-tab',
					'parentTab' => 'vypisDole'
				)
			);
		} else {
			$this->view->filesTableHead = array(
				'title' => array(
					'title' => 'Název',
					'atribs' => array(),
					'sortUrlType' => 'refresh-tab',
					'parentTab' => 'intranetHome'
				),
				'publishedContent' => array(
					'title' => 'Typ souboru',
					'atribs' => array('style' => 'width:130px;'),
					'sortUrlType' => 'refresh-tab',
					'parentTab' => 'intranetHome'
				),				
				'dateCreate' => array(
					'title' => 'Datum vytvoření',
					'atribs' => array('style' => 'width:130px;'),
					'sortUrlType' => 'refresh-tab',
					'parentTab' => 'intranetHome'
				),
				'dateModif' => array(
					'title' => 'Poslední modifikace',
					'atribs' => array('style' => 'width:150px;'),
					'sortUrlType' => 'refresh-tab',
					'parentTab' => 'intranetHome'
				)
			);
			
			$this->view->curentViewParent = 'intranetHome';
		}
		
		
		/*
		$this->view->filesTableActions['detail'] = array(
			'title' => 'Detail obsahu',
			'type'  => 'modal',
			'url'   => $this->view->url(array('controller' => 'pages','action' => 'detail', 'contentNode' => $this->filenode->nodeId,  'contentfilenode'=>'%$%', 'inFileBranch' => 1))
		);
		$this->view->filesTableActions['delete'] = array(
			'title' => 'Smazat',
			'type'  => 'tab-refresh',
			'tabId' => 'vypisDole',
			'url'   => $this->view->url(array('controller' => 'sf','action' => 'deleteFile', 'filenode' => $this->filenode->nodeId, 'fileContentNode'=>'%$%'))
		);
		*/
    		 				
    	
	}
	
	
	public function fileApproveAction()
	{  				
		parent::performMultiAction();
		$this->getFiles(true);  	
    	echo $this->view->render('controls/admin/modules/Intranet/IntranetFileApprove.phtml');   	
	}
	
	
	public function welcomeAction()
	{ 	
		//$this->fileApproveAction();
		// aktualni content
		
		
		$this->view->enquiry = new module_Enquiry();
		$this->view->votedEnquiries = $this->view->enquiry->vote($this);
		
		$publishedContent = $this->view->intranetContent = $this->view->content = $this->view->node->getPublishedContent();
		//e($this->view->node);
		//return ;
		if($publishedContent->_name == 'content_OverviewFiles'){
			$this->getFiles(false);  	
		}
		$this->view->intranetPage = $publishedContent->show(&$this->view, &$this->view->node);
		$this->view->h1Title = $this->view->node->title;

				
    	echo $this->view->render('controls/admin/modules/Intranet/IntranetWelcome.phtml');   	
	}
	
	public function showTreeAction()
	{  		
    	echo $this->view->render('controls/admin/modules/Intranet/IntranetTree.phtml');   	
	}
	
	
	
}
