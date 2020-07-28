<? 
class Instance {
	
	function __construct($controller) {		
		$this->controller = $controller;		
	}  
	
	
	function getAdvert() { 
		$view = $this->controller->view;
		$conf = $view->config->instance;
		$view->homepage = $view->tree->getNodeById(1);
		$view->homepageContent = $view->homepage->getPublishedContent(); 
    	
    	/* REKLAMA */
    	$view->mainAdverts = $view->homepage->getAdverts($view->homepageContent, 1);  
		    
    	if(count($view->mainAdverts['rightBanners']->adverts)){  
    		ob_clean();
    		$view->disableMoore = 1; 
    		$view->isExternal = 1; 
      		echo $view->mainAdverts['rightBanners']->render($view); 
     	}  
    	// $view->mainAdverts = $view->nHome->getAdverts($view->cHome);            	
	}
	
	
	
	function init() {
		if($_GET['test'])
		{
			$_SESSION['test'] = 1;
		}
		if($_SESSION['test']!='1')
		{
			die();
		}
		
		if($_GET['gimmeadverts']){ 
			$this->getAdvert();
		}
		
		$view = $this->controller->view;		 
		
		if($_GET['readCSV'] == 1){
			$import = new module_ImportAstra($view);
			e($import->getProperty()); 	   
			die('ok');         
		} 
		
		if($_GET['importPrice'] == 1){
			$import = new module_ImportAstra($view);
			$import->import(false, true);
			die('ok');
		}

		if($_GET['importDos'] == 1){
			$import = new module_ImportAstra($view);
			$import->import(false, false, true);
			die('ok');
		}
		
		if($_GET['import'] == 1){
			$import = new module_ImportAstra($view);
			$import->import(false,false, false, true);
			die('ok');
		}
		
		if($_GET['importProp'] == 1){
			$import = new module_ImportAstra($view);
			$import->import(false,false, false, false,true);
			die('ok');
		}
		
		if($_GET['saveFile'] == 1){
			$import = new module_ImportAstra($view);
			$import->import(true);
			die('ok');
		}
		 
		
		
		if($_GET['cleanCron'] == 1){
			$import = new module_ImportAstra($view);
			$import->incPosition(true);
			die('ok');
		}
		
		if($_GET['importRev'] == 1){
			$mReviews = new module_Reviews($view);
			$mReviews->importRev();
			die('ok');
		}
		
		if($_GET['importRevProd'] == 1){
			$mReviews = new module_Reviews($view);
			$mReviews->importProductRev();
			$mReviews->saveRatingProduct($view);
			die('ok');
		}
		
		
		
		$mBasket = new module_Eshop_Basket();
		///nastuje košík z cookie
//		pr($_SESSION['WEB07']['basketDelivery']=null);
//		pr($_SESSION['WEB07']['basketPayment']=null);
// 	$_SESSION['WEB07']['basket'] =NULL;
// //		$_SESSION['WEB07']['note'] =NULL;
// //		$_COOKIE = null;
//      	if(!$_SESSION['WEB07']['basket']){
//      		$mBasket->setBasketFromCookie($this);
//      	}
		//$_SESSION['WEB07']['basket'] =NULL;
		$view->homePath = $view->languages->getLangFePrefix($view->languages->language);
		$view->isHomepage = $view->curentPath == $view->homePath; 	
		
		//     e($view->curentPath); die(); 
		if($view->curentPath == '/kavy/asie'){
			helper_FrontEnd::redirect('http://www.kavablack.cz/e-shop/kavy/asie', 301);   
		}
		if($view->curentPath == '/kavy/afrika'){
			helper_FrontEnd::redirect('http://www.kavablack.cz/e-shop/kavy/afrika', 301);   
		} 
		if($view->curentPath == '/kavy/amerika'){
			helper_FrontEnd::redirect('http://www.kavablack.cz/e-shop/kavy/amerika', 301);   
		}
		
		if($view->inputGet->pop || $view->inputGet->popup){
			$view->popup = true;
		}	
		
		if($_GET['testLouvis'])
		{
			$_SESSION['testLouvis'] = 1;
		}
		if($_SESSION['testLouvis']=='1'){
			$view->showPrice = true;
		}
		
		$root = $view->tree->getNodeById(1);
		  $cRoot = $root->getPublishedContent();
		  $banners = ($cRoot->getFilesNames('banners'));
			$view->bannersMainOrig = $banners; 
			$banners = array_keys($banners); 
    		shuffle($banners);  
    		$view->bannersMain = $banners; 
		// pr($_GET);
		
		$view->fullUrl = $view->url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']; 
		
		$view->language = 'cz';  
		//preklady
		$view->FETranslation = $view->languages->getTranslationArray();
		
		$view->mArticles = new module_Articles();       
		$view->mProducts =  new module_Products();     
		$view->mEshop = new module_Eshop_Base();    
		$view->mCustomers = new module_Customers();    
		$view->mFakturace = new module_Fakturace();  
		$view->newsUrl = 'akcni-nabidky-slevy-a-novinky-do-vaseho-e-mailu';
		$searchNode = $view->tree->getNodeById(135);
		$view->searchUrl = $searchNode->path; 
		$view->mVoucher = new module_Voucher();
		$view->settings = Zend_Registry::getInstance()->settings;
		$view->profileUrl = '/vas-profil';
		$view->bp = '/nakupni-kosik';
		$view->profileLogout = '/vas-profil?logout=1';
		$view->basketUrl = '/nakupni-kosik';
		$view->confirmUrl = '/potvrzeni';
		$view->mVarianta = new module_Varianta();
		$view->mReviews = new module_Reviews($view);
		$view->IPtoDisable = $view->settings->getSettingValue('ipNotSetToGoogle');
		
		// neni tam
		$view->dph = $view->settings->getSettingValue('dph');
		
		$view->dphNo = 20;
		$view->dphQuote = 0.20; 
		$view->dphQuote2 = 1 + $view->dphQuote;
		
		
		
//		if($_GET['Status'] == 1 && $_GET['PAY']){ 
//			$payment = $view->mEshop->basket->getPayment();      
//			if($payment == 3){    
//				helper_FrontEnd::redirect('https://www.homeandcook.cz/nakupni-kosik?step=5&success=1', 302);   
//			}   
//		} 
		if($_GET['v2'])
			error_reporting(E_STRICT);
		 
		 
	
		if($view->inputGet->ajaxProducts){
			$params['showFirstVariant'] = 1;
			$count = $view->mProducts->getProductsCout('order','',0, 1000000,$params);
			if($view->inputGet->position<0){
				$view->inputGet->position = $count - abs($view->inputGet->position);
			}
			elseif($view->inputGet->position > $count ){
				$view->inputGet->position = 0;
			}
			$view->souvisejici = $view->mProducts->getProducts('order','',$view->inputGet->position, 6,$params);
			$view->onHp = true;
			$first['html'] = $view->render('parts/alternativeProducts.phtml');
			$first['position'] =  $view->inputGet->position;
			echo json_encode($first);
			die();
		}
		
		if($view->inputGet->vouTest)
		{
			$db =  Zend_Registry::getInstance()->db;
			$select = $db->select();	
			$select->from(array('v' => 'module_Voucher'),array('v.id_v', 'v.id_order'))
				->join(array('f' => 'module_faktura'),'v.id_order = f.id', array('f.id', 'f.oid'));
			$stmt = $db->query($select);
			$result = $stmt->fetchAll();
			print_r($result);
			foreach($result as $item)
			{
				//echo 'v_id('.$item['id_v'].') => ' . $item['oid'] ."\n"; 
				$view->mVoucher->addOrder($item['id_v'],$item['oid']);
			}
			die;
		}
		
				
		if($view->session->loggedWebUser){  
			$view->webUserLogged = true;
			$view->webUser = $view->session->loggedWebUser;			
		}  
		
		$view->webUser = $view->session->webUser; 
		
		if($view->inputGet->export){			
			$export = new module_XMLFeed2($view);
			if($view->inputGet->export == 1){
				$export->render($view);
			} elseif ($view->inputGet->export == 2){
				$export->renderHeureka($view);
			} elseif ($view->inputGet->export == 3){
			$export = new module_XMLFeed($view);
				$export->renderHeldejceny($view);   
			}
			elseif ($view->inputGet->export == 4){
				$export->renderTest($view);   
			} 			
		}  
				
		// detail faktury
		if($view->inputGet->getFaktura){    
			$faktura = $view->mFakturace->getFaktura($view, $view->inputGet->getFaktura);	 	  
			if($faktura && $faktura->vs){
				if($view->inputGet->z){
					$faktura->isProforma = 1;    
				}
				if($view->inputGet->f){  
					if($faktura->isProforma > 1){
						$faktura->isProforma = 5;  
					} else { 
						$faktura->isProforma = 0;
					}  
				} 
				if($view->inputGet->html){ 
					if($view->inputGet->order){   
						$order = $view->mEshop->orders->getOrder($view->inputGet->order);   
						$view->orderPage = $faktura->printOrder($view, $view->mEshop->basket, $order); 
					}       
					echo  $faktura->generateHTML($view, true);     
				} else {  
					$faktura->generatePdf();   
				}  
				die(); 
			}  
		}
		 
		
		if($view->inputGet->addToCache){
			$mProd = new module_Products();
			$mProd->addToCache($view);
			die();
		}
		
		
		
		if($view->inputGet->autocomplete){ 
			$found = array();
			$limit = 10;
			 
			$value = $view->mProducts->diakritika($_POST['value']);			 
 
			if (is_string($value) && strlen($value) > 1)   
			{  
				$words = $view->mProducts->getSeachableWords($view);  
				$i = 0;
				foreach ($words as $word){					
					if(stripos($view->mProducts->diakritika($word), $value) !== false){ 
						$found[] = $word;
						$i++;
					}
					if($i >= $limit){
						break;  
					} 
					
				}				
			}
						 
			foreach ($found as $word)
			{
				echo "<li>$word</li>";
			} 
			die(); 
			 
		}
				
	}	
	
	
	
	
	
	function translatePath(){ 
		$path = $this->controller->view->requestUrl;
		$view = $this->controller->view;		
		//$this->curentPath = $view->curentPath = $view->requestUrl = helper_FrontEnd::getNodeFullPath($view);	
	}
	
	function postInit() { 
		$view = $this->controller->view;
		
	}
	
	function preRender() { 
		$view = $this->controller->view;
		$conf = $view->config->instance;
		$view->homepage = $view->tree->getNodeById(1);
		$view->homepageContent = $view->homepage->getPublishedContent();
		$externalLinks = $view->homepageContent->getPropertyValue('externalLinks');   
		$externalLinks = explode('><', $externalLinks);
		$view->externalLinks = implode('> | <', $externalLinks); 
		$banners = $view->homepageContent->getFilesNames('banners');
    	if(count($banners)){
    		$b = array_rand($banners, 1);    	    	
    		$view->headBanner = new module_Banner($b, $banners[$b]);
    	} else {
    		$view->headBanner = false; 
    	} 
    	
    	$requests = $view->homepageContent->getFilesNames('requests');
    	if(count($requests)){
    		$b = array_rand($requests, 1);    	    	
    		$view->requestBanner = new module_Banner($b, $requests[$b]);
    	} else {  
    		$view->requestBanner = false; 
    	} 
    	
    	/* REKLAMA */
    	$view->mainAdverts = $view->homepage->getAdverts($view->homepageContent, 1);  
		    
    	// $view->mainAdverts = $view->nHome->getAdverts($view->cHome);            	
	}

}
?>