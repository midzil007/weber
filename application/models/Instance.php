<? 
class Instance {
	
	function __construct($controller) {		
		$this->controller = $controller;		
	}  
	
	function closest($search, $arr) {
    $closest = null;
    foreach($arr as $item) {
        // distance from image width -> current closest entry is greater than distance from  
        if ($closest == null || abs($search - $closest) > abs($item - $search)) {
            $closest = $item;
        }
    }
    $closest = ($closest == null) ? $closest = $search : $closest;
    return $closest;   
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
		if($_GET['qrs']){        
			$mQRPayment = new module_QRPayment();    
			pr($mQRPayment->getQRCode(66,1000));
			die;            
		}     

		        

		if($_GET['saveFileTxt'])   
			{      
			  $mAdmin = new module_AdminReport();
			 pr($mAdmin->sendNotify());     
			 die;     
			}  
		    
			if($_GET['requestIonicOrder'])  
		{   
			   if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }
			   
			
 
    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
 
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
 
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
 
        exit(0);
    }  
 
   
    //http://stackoverflow.com/questions/15485354/angular-http-post-to-php-and-undefined
    $postdata = file_get_contents("php://input");
    if (isset($postdata)) {
        $request = json_decode($postdata);
		pr($request);     
        $username = $request->username;
 
        if ($username != "") {
            echo "Server returns: " . $username;
        }
        else {
            echo "Empty username parameter!";
        }
    }
    else {
        echo "Not called properly with username parameter!";
    }    
	die;   	
		}


   
			if($_GET['3dimage'])
		{    
	   
			                       
$url="https://media.jura.com/360/giga_6/images/";       
                         
for ($i=1; $i < 200; $i++) {          
	$path = $url.$i.'.jpg';                                     
	pr($path);                            
		$content = file_get_contents($path);      
		if($content != false){                        
			e('/images/3d/'.$i.'.jpg');                                                          
			file_put_contents('images/3d/76883/image_1_'.$i.'.jpg', $content);
		}   
		else{                    
			break;       
		}              
}        
die;         
		}  
		
     
		
		if($_GET['getdelivery'])
		{
			header('Access-Control-Allow-Origin: *'); 
			$mApi = new module_Api();		      
			//$data['login'] =  );        
			echo json_encode($mApi->getDelivery($_GET['delivery']));     
			die;  
		}   
		if($_GET['getPayment'])
		{
			header('Access-Control-Allow-Origin: *'); 
			$mApi = new module_Api();		      
			//$data['login'] =  );        
			echo json_encode($mApi->getPayment($_GET['delivery']));     
			die;  
		}     
		if($_GET['section'])    
		{
			header('Access-Control-Allow-Origin: *'); 
			$mApi = new module_Api();		      
			//$data['login'] =  );  
			echo json_encode($mApi->getSection());     
			die;
		}
		if($_GET['loginapi'])
		{
			header('Access-Control-Allow-Origin: *'); 
			$mApi = new module_Api();		      
			//$data['login'] =  );  
			echo json_encode($mApi->login($_GET['email']));     
			die;  
		}   
		if($_GET['getvideo'])
		{
			header('Access-Control-Allow-Origin: *'); 
			$mApi = new module_Api();	 	      
			//$data['login'] =  );       
			echo json_encode($mApi->getVideo($_GET['machines']));     
			die;  
		}    
		
		if($_GET['getordersItems'])
		{
			header('Access-Control-Allow-Origin: *'); 
			$mApi = new module_Api();
			$params['email'] = $_GET['email'];  
		    
			$orders['orders'] = $mApi->getOrders(false,false,0,1000,$params);
			echo json_encode($orders);    
			die; 
		}      
		if($_GET['getproduct']) 
		{
			header('Access-Control-Allow-Origin: *'); 
		    if($_GET['id']>0)
		    { 
		    	$mApi = new module_Api(); 
		    	$orders['product'] = $mApi->getDetailProduct($_GET['id']);  
		    }  
			echo json_encode($orders);    
			die; 
		}      
		
		if($_GET['getproducts']) 
		{
			header('Access-Control-Allow-Origin: *'); 
			
				$mApi = new module_Api();  
		    if($_GET['nodeid']>0)
		    {    
		    	$params['category'] = $_GET['nodeid'];   
		    	$orders['products'] = $mApi->getProducts($params);  
		    }    
			elseif($_GET['onlykavovars']>0){      
				$params['onlykavovars'] = true;      
		    	$orders['products'] = $mApi->getProducts($params); 
			} 
			elseif($_GET['search']){        
				$params['search'] = $_GET['search'];       
		    	$orders['products'] = $mApi->getProducts($params); 
			} 
			else{
			   
				$params['category'] = 7391;    
		    	$orders['products'] = $mApi->getProducts($params); 
			}
			echo json_encode($orders);    
			die; 
		}      
		
		
		if($_GET['getorders'])
		{
			header('Access-Control-Allow-Origin: *'); 
			$mApi = new module_Api();
			if($_GET['detail'])
			{
				$params['id'] = $_GET['detail'];    
				$orders['orders'] = $mApi->getOrdersDetail(false,false,0,1000,$params);
			}
			else{
				$params['email'] = $_GET['email'];
				$params['onlyOrder'] = 1;     
				$orders['orders'] = $mApi->getOrders(false,false,0,1000,$params);
			}
			echo json_encode($orders);    
			die; 
		}      
		   
		   
		   
		if($_GET['sendHeurekaReviews']){ 
			$mProd = new module_Mailing();   			
			e($mProd->sendAutomaticEmail());         
			die;   
		} 
		
		
		if($_GET['send'] == 7){  
			$sender = new module_EmailSender();
			//$sender->sendPack(50); 		 
		//	$sender->clear();     
			die('sent');      
		}
		
				
		
		if($_GET['gimmeadverts']){ 
			$this->getAdvert();
		} 
				  
				  
				if($_GET['resize']){ 
			
			// Return JSON
header('Content-Type: application/json');

if (isset($_GET['image'])) {
    foreach ($_GET['image'] as $key => $imageArr) {
        // Error suppression
        if (isset($imageArr["src"]) && $imageArr["src"] != "" && $imageArr["src"] != "/") {
      	    // get image width
      	    $width = (int) $imageArr['width'];
            $src = $imageArr['src'];
			//e($width);
	//	e(file_exists($image));
      	    // get the optimum width from the available options
      	   
//pr($cache);
			$pathImg = 'thumb';
      	   $sizes = array(
      	  		200 => 'thumb',
      	  		201 => 'small',
				300 => 'medium'				
      	   );
			
		   foreach ($sizes as $key => $value) {
			   if($width >= $key )
			   {
			   	 
			   	$replace = $value;
			   }
			  
		   }
		    
			
            $newSrc = substr($src, 1);
			
			$pathex = explode('/', $src);
			$pathex[3] = $replace;
		 	$newSrc = implode('/', $pathex);
		//	e($newSrc);
          	$images[] =  array('og_src' => $src, 'src' => $newSrc);
        } else {
            $images[] =  array('og_src' => $src, 'src' => '', 'fail' => true);
        }
    }
}

echo json_encode($images);
			
			die
			;
			
		}
		   	

		if($_GET['setprrr'])
		{
			$v = new module_Varianta();
			pr($v->setPreText111());
			die;
		}
		
			if($_GET['heureka'])
		{
			$mH = new module_HeurekaPrices();
			
			e($mH->loadProducts($title));
			
			die;
		}
		
		
		if($_GET['fixMer'])
		{
			$merchant = new module_Merchant();
			$view->mProducts->setCompareFromGet($view,$_GET['products']);
		}
		
		
		if($_GET['sendheureka'])
		{
			$mH = new module_HeurekaPrices();
			
			e($mH->sendResult());
			
			die;
		}
		
		if($_GET['chekher'])
		{
			$mH = new module_HeurekaPrices();
			
			e($mH->checkAlert());
			
			die;
		}
		
		
		if($_GET['clearcron'])
		{
			$mH = new module_CronChecker();
			
			e($mH->incPosition(true));
			
			die;
		}
		
		if($_GET['cof'])
		{
			$_SESSION['cofidis'] = 1; 
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
		
		
		
		
		
		  
		if($_GET['utm_medium'] == 'tel')
		{
			$_SESSION['testLouvis'] = 1;
		}
		if($_SESSION['testLouvis']=='1'){
			$view->showPrice = true;
		}  
		    
		if($_GET['sl'] == 'news')
		{
			$_SESSION['sl'] = 0.85;  
		}       
		
		if($_GET['demo'] == '1')
		{
			$_SESSION['demo'] = 1;
		}
		elseif ($_GET['ostra'] == '1') {
			unset($_SESSION['demo']);    
		}
		 
		
		if($_GET['importRevProd'] == 1){
			$mReviews = new module_Reviews($view);
			$mReviews->importProductRev(); 
			//pr($mReviews->saveRatingProduct($view));   
			die();
		}
		
		
	
		$view = $this->controller->view;
		$view->isAdminLogin = $_SESSION['CMS7']['logged'];
		
		$mBasket = new module_Eshop_Basket();
		//nastuje košík z cookie

//		$_SESSION['WEB07']['note'] =NULL;
//		$_COOKIE = null;
     	if(!$_SESSION['WEB07']['basket']){
     		
     		$mBasket->setBasketFromCookie($this);
     	}
		//$_SESSION['WEB07']['basket'] =NULL;
		$view->homePath = $view->languages->getLangFePrefix($view->languages->language);
		$view->isHomepage = $view->curentPath == $view->homePath; 	
		
		
		if($view->inputGet->pop || $view->inputGet->popup){
			$view->popup = true;
		}	

		if(!$_SESSION['referal'])
		{
			$mConversionChecker = new module_ConversionChecker();
			$mConversionChecker->saveReferal();
		}
		
		if($view->inputGet->renderFa)
		{
			$view ->inputGet->idorder =  $id  = 165444;      
			$mDoklad = new module_PDoklad($view);			
			$mRenderPDF = new module_RenderPDF() ;    
			//$path = ''            
			echo("/attachment/".$mRenderPDF->generateAndSave(false,$mDoklad->renderZalohovaFa(),$id,true));      
			die;              
		}
		
		if($_GET['testLouvis'])
		{
			$_SESSION['testLouvis'] = 1;
		}
		if($_GET['saveExt'])
		{
			$mExt = new module_SaveExtermalFiles();
			die;
		}
		if($_SESSION['testLouvis']=='1'){
			$view->showPrice = true;
		}	
		if(!isset($_SESSION['historyBasketCount1'])){
			 $_SESSION['historyBasketCount1'] = $view->historyBasketCount = Utils::loadUrl('https://www.nivona-eshop.cz/?basketHistory=1','2');
			 }
		else{
			$view->historyBasketCount = $_SESSION['historyBasketCount1'];
		}
		$view->historyBasketCount = str_replace(' 	', '', iconv('UTF-8', 'ASCII//TRANSLIT',$view->historyBasketCount));  
	  
   
	
		  //
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
		$view->mGooleDim = new module_GoogleDimension();    
		$view->newsUrl = '/jura/akcni-nabidky-slevy-a-novinky-do-vaseho-e-mailu';
		$searchNode = $view->tree->getNodeById(135);
		$view->searchUrl = $searchNode->path; 
		$view->mVoucher = new module_Voucher();
		$view->settings = Zend_Registry::getInstance()->settings;
		$view->profileUrl = '/jura/vas-profil';
		$view->predkosik = '/jura/?predkosik=1';   
		$view->bp = '/jura/nakupni-kosik';
		$view->profileLogout = '/jura/vas-profil?logout=1';
		$view->basketUrl = '/jura/nakupni-kosik';
		$view->confirmUrl = '/jura/potvrzeni';
		$view->mVarianta = new module_Varianta();
		$view->mBasket = new module_Eshop_Basket();
		$view->mHeurekaRev = new module_ImportHeurekaReviews($view);
		$view->mReviews = new module_Reviews($view);
		$view->IPtoDisable = $view->settings->getSettingValue('ipNotSetToGoogle');
		
		$view->articlesNews = $view->mArticles->getArticles('id', 'desc',0,1);
		$view->articlesNew = $view->articlesNews[0];
		$nodeNews = $view->tree->getNodeById($view->articlesNew['id']);
		$view->contetNews = $nodeNews->getPublishedContent();
		$view->mFacebookPixel = new module_FacebookPixel();  
		$view->mGooleDim = new module_GoogleDimension();   
		// neni tam
		$view->dph = $view->settings->getSettingValue('dph');
		$view->phoneNum = $view->settings->getSettingValue('phoneNum'); 
		$view->dphNo = 20;
		$view->dphQuote = 0.20; 
		$view->dphQuote2 = 1 + $view->dphQuote;
		  
		$view->schema = true;
		
		if($view->inputGet->removeBasket==1){
			   $id = $view->inputGet->nodeId."_".$view->inputGet->variant."_";
			$view->mBasket->removeItem($id);   
			 echo($view->render('parts/basketQuick.phtml')); 
			 die;  
		}   
		
			if($view->inputGet->admin){ 
				   
			$mAdmin = new module_ImportAdmin($view);      
			$mAdmin->renderProducts($view); 
			die;   
		}

		if($view->inputGet->loadProducts){
			$mAdmin = new module_ImportWeb($view);      
			//pr($mAdmin->addActicle(false));     
			pr($mAdmin->addNewSimple(false));   
			die;               
		}
		       
		               
		if($view->inputGet->adminDelivery){
			$mAdmin = new module_ImportAdmin($view);      
			$mAdmin->renderDelivery();   
			die;
		}
		
		if($view->inputGet->adminPayment){
			$mAdmin = new module_ImportAdmin($view);        
			$mAdmin->renderPayments();   
			die;
		}  
		
		if($_GET['quickAddItem'] && $view->input->count > 0){  
			$view->mEshop->basket->addItemQuickShop($view->input->nodeId,$view->input->count,$view->input->price,$view,$view->input->variantId);
			 echo($view->render('parts/basketQuick.phtml'));   
			die();              
		}   
		if($view->inputGet->repeatBasket == 1){  
			pr($view->mEshop->basket->createUrl(163512));    
			$view->inputGet->token='repeatBasket=1&idorder=MTczNDYy&token=b2f51c7ce56591eba0c23c98d87fce035f0fff9e99227b154a2e8019f451de62';                              
			pr($view->mEshop->basket->repeatBasket($view));
			die();      
			helper_FrontEnd::redirect(Utils::getWebUrl().$view->bp, 302);              
		}
		
		if($view->inputGet->offerSet == 1){  
			$item =$person = $item2 = new stdClass();
			
			$item->ext_id = 'jura_cz_139';
			$item->price = '15000';
			$item->count = '1'; 

			$item2->ext_id = 'jura_cz_158';
			$item2->price = '12000';
			$item2->count = '4';

			$objects->items[] = $item;
			$objects->items[] = $item2;
			 
			$person->firstname = 'Michal'; 
			$person->surname  = 'Nosil';
			$person->email  = 'michal.nosil@gmail.com';
			$person->fu_firma  = 'specShop';
			$person->fu_jmeno  = 'Michal Nosil';
			$person->fu_ulice = 'Stavbařů 153';
			$person->fu_mesto = 'Pardubice';
			$person->fu_psc = '53009';
			$person->fu_ico = '28858506';
			$person->fu_dic  = 'CZ28858506';
			$person->fu_phone  = '725420032';
			$person->ulice = 'Bezručova 661';
			$person->mesto = 'Náchod';
			$person->psc  = '54701';
			  
			$objects->person = $person;     
			

			$mImportAdmin = new module_ImportAdmin();
			pr($mImportAdmin->getOffer($view,2));

    
			die;

			/// vytvoření  /// udělám to do importuAdmin metodu, která si sáhne do offers - podle ní nastaví cenu, počet do košíku
			/// separace
			/// nastavení košíku
			/// vynulování linku - admin...
			pr($view->mEshop->basket->createUrl(163512));    
			$view->inputGet->token='repeatBasket=1&idorder=MTczNDYy&token=b2f51c7ce56591eba0c23c98d87fce035f0fff9e99227b154a2e8019f451de62';                              
			pr($view->mEshop->basket->repeatBasket($view));

			die();      
			helper_FrontEnd::redirect(Utils::getWebUrl().$view->bp, 302);              
		}


		if($_GET['quickshopSearch']){       
			   if(strlen($view ->input->autoquick)>0)
			 echo($view->render('parts/basketQuickSearch.phtml'));      
			die();             
		}        
		
			 
		if($_GET['repath'])
		{
			$mProd = new module_Products();   
			$nodes = $mBasket->db->fetchall("select id from Nodes");
			foreach ($nodes as $key => $value) {
				
			    
			$node = $view->tree->getNodeById($value['id']);               
			$node->path = '/laurastar'.$node->path;    
			$node->cz_path = '/laurastar'.$node->path;                    ;         
			$view->tree->updateNode($node, true);         
			}      
			die; 
		}
		if($_GET['reText'])
		{
		$mProd = new module_Products();
		// content_HtmlFile, ++
		//content_OverviewProducts ++
		// content_OverviewArticles ++
		// content_OverviewHomepage ++,
		// content_OverviewHtml ++ ,  
		// content_OverviewVideos ++,      
		//content_Product  ++ ,  
		//content_Article ++   
		// content_ApplicationHtml  ++  
		$dom = new DOMDocument;                                                                  
		$table = 'content_Product';       
		$nodes = $mProd->db->fetchall("select id, html,preText from ".$table);
		foreach ($nodes as $key => $value) {		
		pr($value['preText']);        
		$dom->loadHTML($value['preText']);  
	foreach ($dom->getElementsByTagName('a') as $node){   
		if($node->getAttribute("href")=="/")
		{    
			$newPath = '"/laurastar"'; 
			 $value['preText'] = str_replace('"'.$node->getAttribute("href").'"',$newPath,$value['html']);         
		}    
		elseif(is_numeric(strpos($node->getAttribute("href"), "sharedfiles"))){
			
		}
		elseif(is_numeric(strpos($node->getAttribute("href"), "http"))){
			  
		}     
		elseif(is_numeric(strpos($node->getAttribute("href"), "mailto"))){
			     
		}  
		elseif(is_numeric(strpos($node->getAttribute("href"), "domainUrl"))){
			  
		}                
		else{
			$newPath = '"/laurastar'.$node->getAttribute("href").'"';	
			$value['preText'] = str_replace('"'.$node->getAttribute("href").'"',$newPath,$value['preText']); 
		}
   
	}		
	                      
			pr($value['preText']);    
			$where = $mProd->db->quoteInto("id =?",$value['id']);
		//	$mProd->db->update($table,$value,$where);       
	}
       
	die;     
}
		
		
		
		
		
	if($_GET['stateAgmo']){
			$paymentsProtocol = new module_Agmo_AgmoPaymentsSimpleProtocol($view->config);
			$paymentsDatabase = new module_Agmo_AgmoPaymentsSimpleDatabase($view->config);	
			try {
   				$paymentsProtocol->checkTransactionStatus($_POST);
			
			    // check transaction parameters in my database
			    $paymentsDatabase->checkTransaction(
			        $_POST['transId'], // transId
			        $paymentsProtocol->getTransactionStatusRefId(),
			        $paymentsProtocol->getTransactionStatusPrice(),
			        $paymentsProtocol->getTransactionStatusCurrency()
			    );
			
			    // save new transaction status to my database
			    $paymentsDatabase->saveTransaction(
			         $_POST['transId'], // transId
			        $paymentsProtocol->getTransactionStatusRefId(),
			        $paymentsProtocol->getTransactionStatusPrice(),
			        $paymentsProtocol->getTransactionStatusCurrency(),
			        $paymentsProtocol->getTransactionStatus()
			    );
				//$mOrders = new module_Eshop_Orders();  
				//$mOrders->setValue(false, 'paid', '1', $_POST['refId']);   

			    // return OK
			    echo 'code=0&message=OK';
					die;
				}
			catch (Exception $e) {
		
		    // return ERROR
		    echo 'code=1&message='.urlencode($e->getMessage());
		
			}
	die;
}     
		if($view->inputGet->price){ 
			$view->input->amount = $view->inputGet->price;    
			$_SESSION['calculatorUrl'] = $view->predkosik."&nodeId=".$view->inputGet->nodeId."&variant=".$view->inputGet->variant."&count=1&redirect=1&payment=16"; 
 
		}     
		        
		$view->input->url = $_SESSION['calculatorUrl'];   
		$view->mCalculator = new module_Calculator($view->input); 

		if($_GET['calculator'] == 1){   
		//	e($import->getProperty()); 	     
				echo $view->render('parts/calculator.phtml');
				die;   
		}     
		  
		if($view->input->loginEmail && !$view->input->step)    
		{
			$mOrder = new module_Eshop_Orders();
			echo ($mOrder->getCustomerByEmail($view->input->loginEmail));   
			die;   
		} 
		
	 
		if($_GET['predkosik']=='1')
		{		
			$node = $view->nodeitem = $view->tree->getNodeById($view->inputGet->nodeId);
			if(is_object($node) && (is_numeric($view->inputGet->count)) && (is_numeric($view->inputGet->variant))){
				$c = $node->getTheRightContent(); 
				$view->selectedVariant = $view->mVarianta->getVariantsByIdProduct($c->id,true); 
				$varianta = $view->mVarianta->getVariantsByIdProduct($c->id,true);
				$photos = $view->mVarianta->getResizedPhotos($varianta['obrazky']);
				$view->p = helper_FrontEnd::getFirstPhoto($photos,'pShow2' , false); 
				$view->mEshop->basket->addItem($view->inputGet->nodeId, $view->inputGet->count, $node->path,$view,$view->inputGet->variant,false);
				if(!$view->mEshop->basket->getDeliveryBasket())
				{   
					$view->mEshop->basket->setDelivery(16);	      
				}   
				$view->mEshop->basket->setPayment($view->inputGet->payment);
               
			}  
			if($view->inputGet->redirect){ 
					helper_FrontEnd::redirect(Utils::getWebUrl().$view->bp, 302);   
			}
			if(!$view->inputGet->norender){ 
				echo $view->render('parts/predkosik.phtml');
			}
			die; 
		}
		
		if(!$_SESSION['conversionSettings']){
			$view->conversionSettings = $view->settings->conversionSetting(true);
		}
		else{
			$view->conversionSettings = $_SESSION['conversionSettings'];
		}
		
		
		if($view->inputGet->proLouku)
		{
			$mP = new module_Products();
			pr($mP->saveToCSV());
			die;       
		}
		
		
//		if($_GET['Status'] == 1 && $_GET['PAY']){ 
//			$payment = $view->mEshop->basket->getPayment();      
//			if($payment == 3){    
//				helper_FrontEnd::redirect('https://www.homeandcook.cz/nakupni-kosik?step=5&success=1', 302);   
//			}   
//		} 
		if($_GET['v2'])
			error_reporting(E_STRICT);
		 
		 
		
		
		if($_GET['products'])
		{
			
			$view->mProducts->setCompareFromGet($view,$_GET['products']);
		}
		
		$view->comparePath = $view->mProducts->setPathCompare($view);

		if($_GET['removeCompare'])
		{
			echo $view->mProducts->removeCompare($_GET['removeCompare'],$view);
			die();
		}
		
		if($view->inputGet->ajaxProducts){
		//	$params['showFirstVariant'] = 1;
			$view->products = $view->mProducts->getProducts($view->inputGet->sort,false,0, 6,$params);
			$view->onHp = true;
		//	$first =  $view->inputGet->position;
			echo $view->render('parts/alternativeProducts.phtml');
			die();
		}
		
		if($view->noDeteteParent != 1){
			$_SESSION['parent'] = '';
		}
		
		if($view->inputGet->ajaxsearch){
		//	$params['showFirstVariant'] = 1;
			echo $view->render('templates/Contents/App/SearchProducts.phtml');
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
				$export->renderZbozi($view);
			} elseif ($view->inputGet->export == 2){
				$export->renderHeureka($view);
			} elseif ($view->inputGet->export == 10){  
				$export->renderBigEshop($view); 
			} elseif ($view->inputGet->export == 3){
			$export = new module_XMLFeed($view);
				$export->renderHeldejceny($view);   
			} elseif ($view->inputGet->export == 7){
				$export->renderBig($view);    
			} elseif ($view->inputGet->export == 44){ 
			$export = new module_XMLFeed2($view);  
				$export->renderMerchant($view);   
			} elseif ($view->inputGet->export == 5){
			$export = new module_XMLFeed2($view);
				$export->renderFacebook($view);    
			}    
		}  
		
		
		if($view->inputGet->getFaktura && $view->showPrice){
			$view ->inputGet->idorder = $view ->inputGet->order;    
			$mDoklad = new module_PDoklad($view);  
			echo($mDoklad->prepareDataToPFD());    
			die; 
		}
   
			// detail faktury   
		if($view->inputGet->downloaddoklad==1 && $view->showPrice){
				 
			 $id =   $view ->inputGet->idorder;    
			//http://test.shop-jura.cz/?getFaktura=9084&f=1&html=1&order=MTYxNTkz&test=1 
			$mDoklad = new module_PDoklad($view);			
			$mRenderPDF = new module_RenderPDF() ;   
			//$path = ''            
			echo("/attachment/".$mRenderPDF->generateAndSave(false,$mDoklad->prepareDataToPFD(),$id,true));      
			die;    
		} 
		  
		// detail faktury
		if($view->inputGet->getFaktura2){    
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
		
			if($view->inputGet->merchant){
				$mMerchant = new module_Merchant();
				$mMerchant->readPropertyCsv(); 
			}		
				
		if($view->inputGet->autocomplete){ 
			$objs = $view->mProducts->searchProduct($view);    
			echo $objs;
			   die;
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
		//$banners = $view->homepageContent->getFilesNames('banners');
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