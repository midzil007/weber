<?
class content_OverviewProducts extends content_Overview {

	public $fotoFullName      = 'pFull';
	public $fotoShowName      = 'pShow';
	public $fotoShow3Name     = 'pShow3';
	public $fotoThumbName     = 'pThumb';
	public $fotoMiniName3     = 'pMini';
	public $fotoMiniName      = 'pMini3';
	public $fotoCropShowName  = 'pShowc';
	public $fotoCropMini3Name = 'pMinic3';
	public $fotoCropThumbName = 'pThumbc';

	public function __construct() {
		parent::__construct();
		$this->allowablePages = array(
			'Product',
		);

		$this->allowableOverviews = array(
			'OverviewProducts',
		);

		$this->userName = 'Produkty';

		foreach ($this->properties as $property) {
			if ($property->name == "pathToTemplate") {
				$property->value = 'Products';
			}
		}

		$this->properties[] = new ContentProperty('photo', 'FileSelect', '', array(), array(), array('showUrl' => 1, 'showAlt' => 1, 'showSelectFile' => true, 'inputWidth' => '300', 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 53100));
		$this->properties[] = new ContentProperty('heureka', 'Chosen', '', array(), array());
		$this->properties[] = new ContentProperty('merchant', 'Chosen', '', array(), array());
		$this->properties[] = new ContentProperty('zbozicz', 'Chosen', '', array(), array());
		$this->properties[] = new ContentProperty('level', 'hidden', '', array(), array(), array(), false);
		$this->properties[] = new ContentProperty('order', 'hidden', '', array(), array(), array(), false);
		$this->properties[] = new ContentProperty('onHP', 'Checkbox', array(), array(), array(), false);
		$this->properties[] = new ContentProperty('onHPImage', 'FileSelect', '', array(), array(), array('showUrl' => 1, 'showAlt' => 1, 'showSelectFile' => true, 'inputWidth' => '300', 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 53100));
		$this->properties[] = new ContentProperty('position', 'Text', array(), array(), array(), false);
		//$this->properties[] = new ContentProperty('showOnHome','Checkbox','', array());
		//$this->properties[] = new ContentProperty('idOrigCat','hidden','', array(), array(), array(), false);
	}

	function showSrovnavace() {
		$mSronavace = new module_Merchant();
		$merchant   = $mSronavace->getTree();
		$mHeureka   = new module_HeurekaTree();  
		$heureka    = $mHeureka->getTree();
		$mZbozi     = new module_ZboziTree();
		$zbozi      = $mZbozi->getTree();
	
		return array($heureka, $zbozi, $merchant);
	}

	function show($view, $node) {
		$template             = $this->getPropertyByName('pathToTemplate')->value.'.phtml';
		$view->pageText       = $view->content->getPropertyValue('html');
		$view->node           = $node;
		$view->bannerHPs      = $view->isgallery      = false;
		$view->content        = $this;
		$view->noDeteteParent = 1;
		if ($view->inputGet->page > 0):
		$pageList = ' - stránka '.($view->inputGet->page+1);
		endif;
		$view->disableH1 = true;
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
			case 'rating':
				$view->pageTi = 'Nejlépe hodnocené ';
				break;
			case 'soldPrice':
				$view->pageTi = 'Nejprodávanější ';
				break;
		}

		$params = array();
		if ($this->inputGet->znackyAll) {
			$view->inputGet->znacka    = array();
			$view->inputGet->znackyAll = 0;
		}

		$ds  = 'priceasc';
		$dst = 'asc';
		  
		if($view->node ->nodeId == 7237)
		{
			$ds  = 'soldPrice';
			$dst = 'asc';
		}  
       	$sleva = array(7237,7767,7764,74680,75261);    
		if($_SESSION['sl'] && in_array($view->node ->nodeId, $sleva))  
		{  
			$params['sleva'] = $_SESSION['sl'];
		} 

		$params['znacka']           = $view->inputGet->znacka;
		$params['onWeb']            = 1;
		$params['showFirstVariant'] = true;
		$params['joinOption']       = false;
		$params['category']         = $view->node->nodeId;
		if ($view->inputGet->sort == 'price') {
			$view->inputGet->sortType = 'asc';
		}

		if ($view->inputGet->cat) {
			$c = $view->tree->getNodeById($view->inputGet->cat);
			if ($c) {
				$params['category'] = $view->inputGet->cat;
			}
		}

		$view->tableSort     = $tableSort     = $view->inputGet->sort     = $view->inputGet->sort?$view->inputGet->sort:$ds;
		$view->tableSortType = $tableSortType = $view->inputGet->sortType = $view->inputGet->sortType?$view->inputGet->sortType:$dst;
		//pr($params);
		$products = $view->mProducts->getProducts($tableSort, $tableSortType, 0, 5000, $params);
		// LISTING
		$count                   = count($products);
		$view->ListingItemsCount = $count;
		$view->listingPerPage    = 100;
		//$this->inputGet->pocet;

		$view->paramsSeo = $params;

		helper_Listing::init($view);
		$view->products = $view->mProducts->getProducts($tableSort, $tableSortType, $view->listingSQLStartPos, $view->listingPerPage, $params);
		foreach ($view->products as $prod) {
			$facebookIds[] = "'".$prod['id']."'";
		}
		$view->facebookIds = implode(',', $facebookIds);

		if ($view->inputGet->ajax == 1) {
			echo $view->render(Zend_Registry::getInstance()->config->view->overviewsDir.$template);
			die;
		}
		return $view->render(Zend_Registry::getInstance()->config->view->overviewsDir.$template);
	}

	function showAdmin($view) {
		parent::showAdminInit($view);
		$this->initOptions($view);
		parent::renderAdmin($view);
	}

	function initOptions($view) {
		$mSronavace                                   = new module_Merchant();
		$merchant                                     = $mSronavace->getTree();
		$mHeureka                                     = new module_HeurekaTree();
		$heureka                                      = $mHeureka->getTree();
		$mZbozi                                       = new module_ZboziTree();
		$zbozi                                        = $mZbozi->getTree();
	
		$this->getPropertyByName('heureka')->options  = $heureka;
		$this->getPropertyByName('zbozicz')->options  = $zbozi;
		$this->getPropertyByName('merchant')->options = $merchant;
	}

	function getPageTitle($view) {
		$string = '(';
		if ($view->inputGet->colors) {
			$showString = true;
			foreach ($view->inputGet->colors as $val) {
				$title[] = $view->mVarianta->getOptionById($val, true);
			}
		}
		if ($view->inputGet->sizes) {
			$showString = true;
			foreach ($view->inputGet->sizes as $val) {
				$title[] = $view->mVarianta->getOptionById($val, true);
			}
		}
		if ($view->inputGet->others) {
			$showString = true;
			foreach ($view->inputGet->others as $val) {
				$title[] = $view->mVarianta->getOptionById($val, true);
			}
		}
		if ($showString) {
			$string .= implode(',', $title).')';
			return $string;
		}

	}

	function createFiles($subdomain = false) {
		$settings = Zend_Registry::getInstance()->settings;
		$this->createPropertyThumbs(
			array(
				array(
					'name'     => $this->fotoShowName,
					'width'    => 146,
					'height'   => 193,
					'autosize' => false
				),
				array(
					'name'     => $this->fotoFullName,
					'width'    => 780,
					'height'   => 660,
					'autosize' => false
				)
			),
			'photo'
		);

	}

	function onSave() {

		$this->createFiles();
		parent::onSave();
	}

	function onUpdate() {

		$this->createFiles();

		parent::onUpdate();
	}

	function onDelete() {
		parent::onDelete();
	}
}
?>
