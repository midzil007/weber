<?php
class Cms_HelpController extends CmsController
{
	
    public function init()
	{				
		parent::init();		
		$this->view->title .= ' - Nápověda';	
			
	}
	
	private function initPage()
	{  					
		
		$this->view->showHelpTree = true;
		$this->view->selectedLeftHelpPane = false;
		
		$this->view->helpTree = $this->helpTree = $this->tree->getTree(3,true);
		$this->view->selectedLeftHelpPane = false;
		$this->view->overviewTypes = $this->config->overviewTypes;
		$this->view->sortTypes = Node::$sortTypes;
		
    	$node = $this->request->getParam('helpnode');
    	$node = $node?$node:3;
		
		$this->initTreePath($node);
		
		//e($this->session->currentNodePath); 
		$this->nodeId = $node;
		$this->view->node = $this->node = $this->tree->getNodeById($node);	
		//e($this->node->getSupertype());
		$this->view->parentSelect =  $this->tree->getParentSelect($this->node->parentId);
		$this->view->curentParent = $this->session->curentParent = $this->node->parentId;
		$this->view->curentNode = $this->session->curentNode = $this->nodeId;
		$this->view->template = $this->node->getTemplate();
		$this->view->overviewType = $this->node->getOverviewType();

		$this->view->showBottomPanel = true;
		$this->view->bottomContentTitle = 'Výpis stránek';
		$this->view->bottomContentHref = '/cms/pages/list/node/' . $this->nodeId;
		//pr($this->view->node);
				
	}
	
	function initTreePath($node){
		$this->session->currentHelpPath = $this->view->currentHelpPath = $this->tree->getNodeIdPath($node);
		
	}
	
    public function indexAction()
    {    
    	$this->initPage();    	
    	parent::indexAction('controls/admin/Help.phtml'); 
    }
    
    public function popupindexAction()
    {   
    	$this->initPage();  	
		echo $this->view->render('controls/admin/Help.phtml');
    }
         
    public function helpAction()
	{  	
		$section = $node = $this->request->getParam('helpSection');
		
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
				
    	$this->view->helpContent =  $this->view->render('/controls/admin/tabs/HelpWidget.phtml');
	} 
	
	public function fullhelpAction()
	{  	
		$this->view->showFullHelpTree = true;
    	// echo $this->view->render('/controls/admin/tabs/Help.phtml');
    	parent::indexAction('controls/admin/Help.phtml');  
	}
	
	public function fullhelpdetailAction()
	{  
	
		$this->view->children = array();
		$detail = $this->request->getParam('helpDetail');
		$detail = $detail?$detail:3;
		$this->view->helpNode = $this->tree->getNodeById($detail);				
		echo $this->view->render('/controls/admin/tabs/HelpDetail.phtml');
	}
		
	public function homeAction()
	{ 	
		$this->initPage();
		//$this->tree->_save('helpFull', true);
				
    	echo $this->view->render('/controls/admin/forms/HelpHome.phtml');
    	
	}
	
	public function deleteAction()	{	
		$this->initPage();
		if($this->nodeId > 3){
			parent::audit($this->node->title,$this->nodeId);
			$this->tree->removeNode($this->nodeId);
		}
		$this->_redirector->goto('index', 'help', null, array('helpnode' => $this->node->parentId));
	}
	
	public function showTreeAction()	
	{  
		$this->initPage();
    	echo $this->view->render('controls/admin/forms/HelpTree.phtml');   	
	}
	
	public function showFullTreeAction()	
	{  
		$this->initPage();
    	echo $this->view->render('controls/admin/forms/HelpFullTree.phtml');   	
	}
}
