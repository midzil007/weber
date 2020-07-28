<?php
/*
	Search
*/
class Cms_SearchController extends CmsController
{
	public function init()
	{	
		parent::init();
						
		if($this->doPageInit)
			$this->initPage();
		
	}
	
	private function initPage()
	{    	
		
		$this->view->title .= ' - Vyhledávání';
		$this->template = 'controls/admin/Search.phtml';		
		
		$this->view->showTree = false;
		$this->view->selectedLeftHelpPane = false;
		
		$this->view->showBottomPanel = true;
		$this->view->bottomContentTitle = 'Výsledek vyhledávání';
		$this->view->bottomContentHref = $this->view->url(array('controler' => 'search','action' => 'results')) . '?searchString=' . urlencode($this->inputGet->searchString);

		$this->view->searchString = $this->request->getParam('searchStringDetail')?$this->request->getParam('searchStringDetail'):$this->inputGet->searchString;
		
	}
			
	public function indexAction()
	{  		
		$this->homeAction();
		$this->resultsAction(); 
		parent::indexAction($this->template);
	}
	
		
	public function homeAction()
	{  				
		$this->view->searchIn = array(
			'fulltext' => 'Všude (fulltext)',
			'title' => 'Pouze v názvech stránky'
		);
		
		$users = new Users();		
		$this->view->searchBy = array_merge(
			array(
				'all' => 'Nerozhoduje'
			),
			$users->getUsersSelect()							
		);
		 	
	}
	
	public function resultsAction()
	{  		
		$searchIn = $this->request->getParam('searchIn')?$this->request->getParam('searchIn'):'fulltext';
		$searchBy = $this->request->getParam('searchBy')?$this->request->getParam('searchBy'):'all';
				
		$this->search = new module_Search(true, array('searchIn' => $searchIn, 'searchBy' => $searchBy));
		
		if(strlen($this->view->searchString)){
			$this->view->searchPerformed = true;
			if(strlen($this->view->searchString) >= 3){
				$this->view->searchResults = $this->search->performSearch($this->view->searchString, true);
			}
		}
		
		$this->view->searchSections = $this->config->superTypeUsernameMap->toArray(); 
	}
	
		
}
