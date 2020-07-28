<?
class content_OverviewProducts extends content_Overview {
     
	public $fotoFullName = 'pFull';
	public $fotoShowName = 'pShow';
	public $fotoShow3Name = 'pShow3';
    public $fotoThumbName = 'pThumb'; 
    public $fotoMiniName3 = 'pMini';
    public $fotoMiniName = 'pMini3';
    public $fotoCropShowName = 'pShowc';
    public $fotoCropMini3Name = 'pMinic3'; 
   	public $fotoCropThumbName = 'pThumbc';   
    
    public function __construct()
    {   
    	parent::__construct();
    	$this->allowablePages = array( 
    		'Product' 
    	);
    	
    	$this->allowableOverviews = array(
    		'OverviewProducts'
    	);  
    	
    	$this->userName = 'Produkty'; 
    	    	
    	foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){
				$property->value = 'Products';
			}
		} 
		 
		$this->properties[] = new ContentProperty('photo','FileSelect','', array(), array(), array('showSelectFile' => true, 'inputWidth' => '300',  'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 53100 ));   
    	$this->properties[] = new ContentProperty('heureka','MultiPageSelect','', array(), array('root' => 6226, 'display' => 'FOLDER', 'sort'=>'title')); 
		$this->properties[] = new ContentProperty('zbozicz','MultiPageSelect','0', array(), array('root' => 6430, 'display' => 'FOLDER', 'sort'=>'title'));
		$this->properties[] = new ContentProperty('level','hidden','', array(), array(), array(), false);
		$this->properties[] = new ContentProperty('order','hidden','', array(), array(), array(), false);
		$this->properties[] = new ContentProperty('showOnHome','Checkbox','', array());
		$this->properties[] = new ContentProperty('idOrigCat','hidden','', array(), array(), array(), false);
    }
    
	function show($view, $node) {
		$template = $this->getPropertyByName ( 'pathToTemplate' )->value . '.phtml';
		$view->pageText = $view->content->getPropertyValue ( 'html' );
		$view->node = $node;
		$view->content = $this;
		if ($view->inputGet->page > 0) :
			$pageList = ' - stránka ' . ($this->inputGet->page + 1);
    	endif;
    	$view->disableH1 = true;
    	
    	$view->inputGet->sort=$view->inputGet->sort?$view->inputGet->sort:'priceasc'; 
    	/*
    	switch ($view->inputGet->sort) {
    		case 'dateCreate':
    			$view->pageTi = 'Nejnovější ';
    			break;
    		case 'pricedesc':
    			$view->pageTi = 'Nejdražší ';
    			break;
    		case 'priceasc':
    			$view->pageTi = 'Nejlevnější ';
    			break;
    		case 'soldPrice':
    			$view->pageTi = 'Nejprodávanější ';
    			break;
    	}*/ 
    	$view->pageTitleRaw = $view->pageTi.''.$view->node->title.''.$this->getPageTitle($view).''.$pageList;
    	$view->pageTitle = $view->pageTi.''.$view->node->title;
    	$view->listingPerPage = 36;	
		$params['category'] = $node->nodeId;
		$params['onWeb'] = 1;   
		$params['showFirstVariant'] = true; 
		$params['joinOption'] = false;
		$view->showSelectedOption = $view->mVarianta->showSelectedOptions($view);
		if($view->inputGet->colors)
		{
			$params['joinOption'] = $view->showVariantId = true; 
			$params['sizes'] = $view->inputGet->sizes;
		}
		
		$view->ListingItemsCount  = $view->mProducts->getProductsCout($view->inputGet->sort, $tableSortType, 0, 5000, $params);
		helper_Listing::init($view);
		$view->products = $view->mProducts->getProducts($view->inputGet->sort, $tableSortType, $view->listingSQLStartPos, $view->listingPerPage, $params);
		foreach ($view->products as $pr)
		{
			$ids[] = $pr['id'];
		}
		$view->enerClass = $view->mProducts->getOptions('enerClass',$view,$ids);
		$view->colors = $view->mProducts->getOptions('color',$view,$ids);
// 		e($view->colors );
// 		e($view->other);
// 		e($view->sizes);
// 		e($view->kinds);
		return $view->render ( Zend_Registry::getInstance ()->config->view->overviewsDir . $template );
	}
	
	function showAdmin($view){    	
    	parent::showAdminInit($view);
    	//$this->initOptions($view);    		    	
    	parent::renderAdmin($view);   	
    } 
    

    
    
    function getPageTitle($view)
    {
    	$string = '(';
    	if($view->inputGet->colors)
    	{
    		$showString = true;
    		foreach ($view->inputGet->colors as $val)
    		{
    			$title[] = $view->mVarianta->getOptionById($val,true);
    		} 
    	}
    	if($view->inputGet->sizes)
    	{
    		$showString = true;
    		foreach ($view->inputGet->sizes as $val)
    		{
    			$title[] = $view->mVarianta->getOptionById($val,true);
    		}
    	}
    	if($view->inputGet->others)
    	{
    		$showString = true;
    		foreach ($view->inputGet->others as $val)
    		{
    			$title[] = $view->mVarianta->getOptionById($val,true);
    		}
    	} 
    	if($showString)
    	{
    		$string .= implode(',', $title).')';
    		return $string;
    	}
    	
    	
    }
	
	function getHeurekaOptions(){
	$ret = array(
				'Bílé zboží # Malé spotřebiče # Žehličky' => 'Žehličky',
				'Bílé zboží # Malé spotřebiče # Vysavače' => 'Vysavače'
				);
	return $ret;
	}
	
	function getZboziczOptions(){
	$ret = array(
				'Bílé zboží # Malé spotřebiče # Žehličky' => 'Žehličky',
				'Bílé zboží # Malé spotřebiče # Vysavače' => 'Vysavače'
				);
	return $ret;
	}
	
	function initOptions($view){      	    	  
    	$heureka = $this->getPropertyByName('heureka'); 
    	$heureka->options = $this->getHeurekaOptions();
		$zbozicz = $this->getPropertyByName('zbozicz');
		$zbozicz->options = $this->getZboziczOptions();
    }
	
    
	function createFiles($subdomain = false){
    	$settings = Zend_Registry::getInstance()->settings;	      	 
    	$this->createPropertyThumbs(
    		array(
    			array(
    				'name' => $this->fotoShowName, 
    				'width' => 146,        
    				'height' =>193,     
    				'autosize' => false
    			), 
    			array(
    				'name' => $this->fotoFullName, 
    				'width' => 780,  
    				'height' => 660,       
    				'autosize' => false 
    			) 
    		),  
    		'photo'   
    	);  

    	
    	   
    }
     
    function onSave(){ 
    	$this->createFiles();	    	
    	parent::onSave();
    }
        
    function onUpdate(){  
    	
    	$this->createFiles(); 
    	
    	parent::onUpdate();
    }
            
    function onDelete(){
    	parent::onDelete();
    }
}
?>
