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
    	
		$this->properties[] = new ContentProperty('banners','MultiFileSelect','', array(), array(), array('inputText' => 'odkaz', 'showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 7, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' =>  3242));
		   
		
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
		$view->isgallery = true;
	
    	$view->content = $node->getPublishedContent();  
    	$mArticles = new module_Articles(); 
		 		$mVideos = new module_Videos();       
		$view->mVideos = $mVideos->getVideos(); 
		$view->sections = $view->mProducts->getHPSection();           	
 		$mNews = new module_NewsWeb();
    	$view->isHomepage = true; 
    	$view->homepageTitle = $node->title;
    	$params['onHome'] = 1;   
    	$articles = $mArticles->getArticles('dateShow', 'Desc', 0, 4, 7219);             
		$view->articles = $mArticles->getArticlesAsNodes($view->tree, $articles); 	  	 
		$banners = ($view->content->getFilesNames('banners'));
		$view->bannersMainOrig = $banners; 
		$banners = array_keys($banners); 
    	shuffle($banners);  
    	$view->bannersMain = $banners; 		
		$view->rowImages = $images;
		$banners = $view->content->getFilesNames('banners');         
    	if($banners){
    		$view->showHPBan = true;
			 $view->bannerHPs = array(); 
		  foreach ($banners as $key => $value) {    
			  if($value)
			  $view->bannerHPs['/data/sharedfiles'.content_SFSFile::getSFSFullPath($key)] = $value;   	 
		  }         
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
