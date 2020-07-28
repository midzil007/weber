<?php
/*
	Booking module
*/
class Cms_LanguagesController extends CmsController
{

	public function init()
	{
		parent::init();		
		 				
		if($this->doPageInit){
			$this->initPage();
		}		
	}
	
	private function initPage()
	{ 		
    	    	
		$this->view->title .= ' - Překlady';
		$this->template = 'controls/admin/modules/Languages/Languages.phtml'; 
		$this->view->showBottomPanel = false;
		$this->view->selectedLeftHelpPane = true;
		$this->view->showTree = false;
				
		$this->view->leftColl = $this->view->render('parts/leftSettings.phtml');  
	}
		
	
    public function indexAction()
    {     	  
    	$input = (object) $_POST; 
    	if(isset($input->saveSetB)){
    		$this->saveAction($input);
    	} 
    	$this->view->page = $this->homeAction();   
    	parent::indexAction($this->template); 	
    	//echo $this->view->render('index.phtml');
    	 
    }
    
    public function homeAction()
	{  	
			
		//$languages = new module_languages_Languages($this->view);
		
		$this->view->availableLanguages = $this->languages->availableLanguages;
		$this->view->phrases = $this->languages->getTranslationArray();
		//e($this->view->phrases); 
    	return $this->view->render('controls/admin/modules/Languages/LanguagesHome.phtml');
	}
	
	public function saveAction($input)
    {     	 
    	parent::audit();    	
    	$this->languages->saveFromInput($input);
    	
    	$this->addInfoInstantMessage('Uloženo' );  
    	
    }
	
}
