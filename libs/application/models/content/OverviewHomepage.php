<?
/**
 * Třída reprezentuje typ obsahu - Uvodni stranku
 * @see model_Content
 * @package content
 */
 
class content_OverviewHomepage extends content_Overview {
    	
    public function __construct()
    {   
    	parent::__construct();    	
    	$this->userName = 'Úvodní stránka';
    	    	
    	foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){
				$property->value = 'Homepage';
			}
		} 		

		
		$this->properties[] = new ContentProperty('photo','FileSelect','', array(), array(), array('showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 30, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 3376 ));
    	$this->properties[] = new ContentProperty('url','Text','', array(), array());
		$this->properties[] = new ContentProperty('title','Text','', array(), array());
		   
		
		//$this->properties[] = new ContentProperty('tags', 'ItemsStack','', array(),array(), array(), true);  
    	 
		//banner
		// $this->properties[] = new ContentProperty('banners','MultiFileSelect','', array(), array(), array('inputText' => 'odkaz', 'showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 7, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 7362)); 
		  
		//banner spodni    
		
    }  
    
    
    function initAdverts(){   
    	//  $this->advertsPositions[] = new module_Advertising_AdvertPosition('rightBanners', 'Banner na pravé straně (160 x 600 px)', true, true, 1);    
    }
    
    
    
    function show($view, $node){    	
    	$template = $this->getPropertyByName('pathToTemplate')->value . '.phtml'; 
    	$view->content = $node->getPublishedContent();  
    	$mArticles = new module_Articles();  	
 		$mNews = new module_NewsWeb();
    	$view->isHomepage = true; 
    	$view->homepageTitle = $node->title;
    	$params['onHome'] = 1;
    	$hpSections = $view->mProducts->getProductsOver('title', false,0,6,$params);
    	$view->hpSections = $view->mProducts->getArticlesAsNodes($view->tree,$hpSections);
    	$articles = $mNews->getNews('dateShow', 'Desc', 0, 1, 0);          
		$view->articles = $mArticles->getArticlesAsNodes($view->tree, $articles);	  	 
    	   
		$banners = ($view->content->getFilesNames('banners'));
		$view->bannersMainOrig = $banners; 
		$banners = array_keys($banners); 
    	shuffle($banners);  
    	$view->bannersMain = $banners; 		
		$view->rowImages = $images;
		
		
    	 
    	$banners = $view->content->getFilesNames('banners');
    	
    	$view->homeBanner = array();
    	if(count($banners)){ 
    		$b = array_rand($banners, count($banners));
    		if(!is_array($b)){
    			$b = array($b); 
    		}
    		foreach ($b as $img){  
    			 $view->homeBanner[] = new module_Banner($img, $banners[$img]);
    		} 	    	
    		
    	} else {
    		$view->homeBanner = false;
    	}
    	
    	  
    	 
    	$view->disableH1 = 1;
    	$view->disableBread = 1; 
    	$view->pageText = $view->content->getPropertyValue('html');
    	
    	
    	  
    	return $view->render(Zend_Registry::getInstance()->config->view->overviewsDir . $template);
    }
    
    function getClients(){
    	$all = explode(',', $this->getPropertyValue('clients'));
    	usort($all, array($this, "cmp_title"));
    	return implode('<br />', $all);
    }
    
    function cmp_title($client1, $client2) {
		return strcoll( iconv("UTF-8", "ISO-8859-2",$client1),  iconv("UTF-8", "ISO-8859-2",$client2));
	} 
}
?>
