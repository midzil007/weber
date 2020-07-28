<?php
class Cms_IndexController extends CmsController
{
	
    public function init()
	{
		parent::init();
		$this->view->showTree = false;		
		
		
	}
	
    public function indexAction()
    {   
    	$this->_redirector->goto('index', 'eshop');   
    	// $this->_redirector->goto('index', 'eshop');      
    	parent::indexAction('controls/admin/Home.phtml'); 	
    	//echo $this->view->render('index.phtml');
    }
    
    public function loginAction(){
    	$this->indexAction();
    }
     
    public function homeAction()
	{   
		$this->view->nodesLastModif = $this->tree->getNodesBy('dateModif');
		$this->view->nodesLastModifPages = $this->tree->getNodesBy('dateModif', 'DESC',7, false, false, false);  
		$this->view->nodesLastModifNotPublished = $this->tree->getNodesBy('dateModif', 'DESC', 7, false, true); 
		$this->view->nodesLastCreated = $this->tree->getNodesBy('created');
		
    	echo $this->view->render('/controls/admin/tabs/IndexHome.phtml');
	}
	
    public function errorAction()
    {   
    	echo 'error';
    	parent::indexAction(); 	
    	//echo $this->view->render('index.phtml');
    }
}
