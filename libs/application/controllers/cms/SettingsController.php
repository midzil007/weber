<?php
class Cms_SettingsController extends CmsController
{	
	public $m_znacky;
	
  public function init()
	{				
		parent::init();
						
		if($this->doPageInit)
			$this->initPage();
		
	}
	
	private function initPage()
	{  			
		$this->view->title .= ' - Nastavení';
		$this->view->selectedLeftHelpPane = true;	
 		
		
		$this->initHelp('settings');  
		$this->view->leftColl = $this->view->render('parts/leftSettings.phtml'); 
	}
		
	
    public function indexAction() 
    {     	
    	$this->homeAction(); 
    	parent::indexAction('controls/admin/Settings.phtml'); 	
    	
    	//echo $this->view->render('index.phtml');
    }
     
    public function homeAction()
	{  	
		$this->view->s_Translate_groups = array(
			'general' => 'Nastavení webu',
			'images' => 'Nastavení obrázků a fotogalerie',
			'jobsik' => 'Nastavení cen a služeb',
			'jobsikPackages' => 'Nastavení balíčků',
			'Rezervation' => 'Rezervace' 
		); 
		
		if(!$this->view->isSuperAdmin){
			unset($this->view->s_Translate_groups['images']);
		}
		
		require_once('content/cpMap.php'); 
		$this->view->s_Translate = $_cpMap;
		
		$this->settings->initAll();     
		$this->view->settings = $this->settings->getAll();  
	}
	
	public function saveAction()
    {     	
    	parent::audit(); 
    	$this->settings->saveAll($this->input); 
    	$this->addInfoInstantMessage('Nastavení uloženo' );
    	      
    	$this->indexAction();      
    }
	
	/**
		ulozeni hodnot z noveho nastaveni (leve menu, vice tabu)
	*/
	public function saveAllAction()
	{
		//stejné jako puvodni nastaveni
		$classic['outcommingEmail'] = $this->input->outcommingEmail;
		$classic['outcommingEmailName'] = $this->input->outcommingEmailName;
		$classic['ordersEmail'] = $this->input->ordersEmail;
		$this->settings->saveAll($classic);
		
		$this->settings->saveKategorie( $this->input );
		$this->settings->savePriznaky( $this->input );
		parent::addModalInfo(1,'Nastavení uloženo');
	}
	
	
	
	// nastaveni z leveho menu
	public function tabsAction()
	{
		$this->view->s_Translate_groups = array(
			'general' => 'Nastavení webu', 
			'images' => 'Nastavení obázků a fotogalerie',
			'jobsik' => 'Nastavení cen a služeb',
			'jobsikPackages' => 'Nastavení balíčků',
			'Rezervation' => 'Rezervace' 
		);
		
		require_once('content/cpMap.php');
		$this->view->s_Translate = $_cpMap;
		$this->view->lSettings = $this->view->settings->getAllLeft();
		$this->view->znacky = $this->view->settings->getZnacky();
		$this->view->kategorie = $this->view->settings->getKategorie();
		$this->view->priznaky = $this->view->settings->getPriznaky();
		
		echo $this->view->render('controls/admin/tabsSettings.phtml');
	}
   
}
