<?php
/*
	ESHOP
*/
use Zend\Validator\File\Count;
class Cms_EshopController extends CmsController
{
	private $_urlFilterParams = array(
			'vyrobci', 'skladem', 'cats', 'fmFilterSel', 'start','end', 'filter1', 'filter2', 'fmFilterSel');
	public function init()
	{				
		 
		global $startTime;   
 
		parent::init();
		$this->view->contentStates = $this->config->instance->workflow->toArray();
		  
		  
		if($this->doPageInit){
			$this->initPage();  
		} 
	}
	
	private function initPage()
	{    	
		require_once('content/cpMap.php');
		$this->view->cp_Translate = $_cpMap;	
		
		if(is_numeric(strpos($_SERVER['HTTP_REFERER'], 'login'))){
			$this->view->disableBack = true;
		}
		$this->view->title .= ' - E-shop';
		$this->template = 'controls/admin/modules/Eshop/Eshop.phtml'; 
		$this->view->leftWidth=15;  
		$this->view->rightWidth=85;   
		
		$this->view->showTree = false; 
		$this->view->showBottomPanel = false;  
			 
		$this->view->showEshopWidget = true; 
		$this->view->selectedLeftHelpPane = false;  		 
		$this->view->mEshop = $this->mEshop = $eshop = new module_Eshop_Base($this->view);
		$this->view->mSettings = $this->mSettings = $eshop = new Settings;  		
		$this->view->mFakturace = $this->mFakturace = $mFakturace = new module_Fakturace();
		$this->view->mCustomers = $this->mCustomerts = new module_Customers;
		$this->view->mReviews = $this->view->mReviews = new module_Reviews;
		//$this->view->totalCustomers = $this->mCustomerts->getCustomersCout('prijmeni', $sortorder, 0, 100000000); 
		$para['cnt'] = true;
		    
		
		$this->view->mProducts = new module_Products();
		$params = array();
		$params['showFirstVariant'] = true;   
		$params['cms'] = 1;
		//$this->view->totalProduct = $this->view->mProducts->getProductsCout(false,false,0,100000,$params);
		$this->view->mVariant = $this->mVariant =  new module_Varianta(); 		 		
		$this->aModule = $this->request->getParam('amodule');
		$this->aModule = $this->aModule ? $this->aModule : 'home';

		$this->view->leftColl = $this->view->render('parts/leftEshop.phtml'); 
		 
		
		 
		if($this->aModule == 'home' || $this->aModule == 'stats'|| $this->aModule == 'items' ){  
			$this->mCustomerts = new module_Customers();
			
			$this->view->znackyAll = $this->view->mProducts->getZnacky(false,true);    
			
			$this->view->obratAll = 0; 
			$this->view->gdOrders = $this->view->gdMonths = $this->view->gdDays = $this->view->gdRegistrace = $this->view->gdObrat = array();
			for ($i = 11; $i >=0; $i--){
				$t = mktime(0, 0, 0, date("m")-$i  , date("d"), date("Y"));
				$params = array(
					'month' => date('m', $t),
					'year' => date('Y', $t)
				);
				
				$ordersDay = $this->mEshop->orders->getOrdersBackend(
					'SEND',
					0,
					'id',
					'desc', 0, 10005, $params
				);
				
				$obrat = 0; 
				foreach ($ordersDay as $o){ 
					$obrat += $o['suma']; 
					$this->view->obratAll += $o['suma'];    
				}     
 
				if(date('n', $t) == date('n')){
					$this->view->obratM = $obrat;
				}
				   
				$this->view->gdMonths[] = Utils::$months3[date('n', $t)];
				$this->view->gdOrders[] = count($ordersDay);
				$this->view->gdObrat[] = ($obrat);
				$this->view->gdRegistrace[] = $this->mCustomerts->getCustomersCout( 'id', 'Asc', 0, 5, $params);
			}   
			$this->view->prodeje = array();
			$prodeje = $this->mEshop->getProdejeByZnacka();

			foreach ($prodeje as $p){ 
				$this->view->prodeje[$p['znacka']] = $p['c'];
			}
			
			//e($this->view->gdRegistrace);  
			//  e($this->view->prodeje);
		}
	}

	public function multiAction(){		
		parent::performMultiAction();
	}  
	
	
	public function exporttoptransAction()
	 {
		 $mTop = new module_TopTrans(); 
		 echo ($mTop->exportOrders($this->input->ids));
		 die;
	 }
	
	public function conversionAction(){
//		
		parent::audit(); 
		if($this->input->save)
		{
    		$this->settings->saveConversion($this->input);
			$this->addInfoInstantMessage('Nastavení uloženo' ); 
		}
			$this->view->conSettings = $this->mSettings->conversionSetting();
		$this->view->conversion = $this->view->render('controls/admin/modules/Eshop/Conversion.phtml');
		parent::indexAction($this->template);
		
	}  
	
	
	public function listreviewsAction()
	{
		//e($this->view->url(array('module' => 'cms', 'controller' => 'eshop','action' => 'listItems'), null,true));
		//die;
		if($this->input->tableaction){
			$this->input->tableaction = 'reviewsDelete';
			parent::performMultiAction();
		}
	
		$getItems = $this->request->getParam('getItems');
		$isAjax = $this->request->getParam('ajax');
		$nodeId = $this->request->getParam('node');
		$nodeId = $nodeId?$nodeId:1;
		$this->view->sortUrl = '/cms/eshop/sortDelivery';
		$this->view->defaultSort = 'date';
		$this->view->defaultSort = 'date';
		$params = array();
		$dg = new DataGrid('pagesTable');
		$dg->setRefresUrl($this->view->url(array('action' => 'listreviews', 'getItems' => 1, 'ajax' => 1)))
		->setTitle('Výpis hodnocení eshopu')
		->setHeight(400)
		->setItemsPerPageOptions('10, 15, 20, 30, 50, 500', 50)
		;
		$dg->setHeaders(
				array(
						array('Datum', 'date', 130, 'true', 'left', 'false'),
						array('Jméno', 'name', 130, 'true', 'left', 'false'),
						array('Pro', 'pros', 130, 'true', 'left', 'false'),
						array('Proti', 'cons', 130, 'true', 'left', 'false'),
						array('Celkové', 'summary', 130, 'true', 'left', 'false'),
						array('Celkem', 'total_rating',  40, 'true', 'left', 'false'),
						array('Doprava', 'transport',  40, 'true', 'left', 'false'),
						array('Přehlednost', 'webUsa',  55, 'true', 'left', 'false'),
						array('Doruč. doba', 'delivery',  55, 'true', 'left', 'false'),
						array('Komunikace', 'communication',  55, 'true', 'left', 'false'),
						array('Heureka', 'id_heureka',  40, 'true', 'left', 'false'),
				)
		)->setSearchableColls(
				array(
						array('Název', 'nazev', 'true')
				)
		)->setButtons(
				array(
						array('Smazat označené', 'delete', 'onpress', 'deletep')
				));
	
	
		if($getItems){
	
			//$products->inAdmin = true;
			$params = array();
				
			list($currentPage, $rp, $sortname, $sortorder, $query, $qtype, $start) = $dg->getParams('date', 'DESC');
	
				
			$reviews = $this->view->mReviews->getReviews($sortname, $sortorder, $start, $rp, $params);
			$rowsTotalCount = $this->view->mReviews->getReviewsCount($sortname, $sortorder, $start, $rp, $params,true);
	
			$active = $this->view->tableFilter0 == 'active' ? 1 : 0;
			$domain = $this->view->tableFilter1 == 'all' ? 0 : $this->view->tableFilter1;
			$nOrders = array();
			$cp = new content_Product();
			$rowsFormated = array();
			foreach ($reviews as $del){
	
				//$editUrl = $this->view->url(array('controller' => 'eshop','action' => 'delivery', 'id'=> $del['id']));
				$nOrder = array();
				$isHeureka = $del['id_heureka'] > 0 ? 'Ano' : 'Ne';
				$nOrder['date'] = '<input name="chbx[' . $del['id'] . ']" type="checkbox" /> &nbsp; ' . Utils::formatDate($del['date']);
				$nOrder['pros'] = $del['pros'];
				$nOrder['cons'] = $del['cons'];
				$nOrder['summary'] = $del['summary'];
				$nOrder['name'] = $del['name'];
				$nOrder['transport'] = $del['transport'];
				$nOrder['delivery'] = $del['delivery'];
				$nOrder['communication'] = $del['communication'];
				$nOrder['total_rating'] = $del['total_rating'];
				$nOrder['webUsa'] = $del['webUsa'];
				$nOrder['id_heureka'] =$isHeureka;
				$entry = array(
						'id'=>$del['id'],
						'cell' => $nOrder
				);
				$rowsFormated[] = $entry;
			}
			if($isAjax){
				$dg->renderAjax($currentPage, $rowsTotalCount, $rowsFormated);
				die();
			};
		}
		$this->view->showButton = new stdClass();
		$this->view->showButton->link = $this->view->url(array('action' => 'addnewreview'));
		$this->view->showButton->title = 'Vložit hodnocení';
		$this->view->hasDD = true;
		$this->view->pagesList = $dg->render($this->view, 'controls/admin/lists/PagesStandard.phtml');
		parent::indexAction($this->template);
	}
	
	
	public function indexAction()
	{  		    
		
		// e($this->view->leftColl); 
		parent::indexAction($this->template);  
		
	}  
	
	public function addnewreviewAction()
	{
		if($this->input->save)
		{
			$this->view->mReviews->saveReview($this->input);
			$this->_redirector->goto('listreviews', 'eshop', null,array());
		}
		$this->view->basicForm =  $this->view->render('controls/admin/modules/Eshop/Review.phtml');
		parent::indexAction($this->template);
	}
		
	public function homeAction()
	{  	   
		echo $this->view->render('controls/admin/modules/Eshop/Home.phtml');  	   
	}

	function getOrderDetailsSimple($order){
		$details = $this->mEshop->orders->getOrderDetails($order['id']);
		$txt = array();
		foreach ($details as $detail){
			$txt[] = $detail['title'] . ' (' . $detail['count'] . 'x)'; 
		}
		return implode('<br />', $txt);  
	}
	
	function changeorderStatus($order, $status){

	if($status == 'CANCEL' || $status == 'DELETED')
	{
		$this->mEshop->itemUnSold($order['id']);
		
	}
	elseif($status == 'SEND')
	{    
		$this->mEshop->itemSold($order['id']);
	}
		$this->mEshop->orders->setOrderStatus($order['id'], $status);
	}
	

	
	
/*	public function saveORderAction()
	{	
		//$err = $this->checkOderForm(); 
		if(!$err){ // ok
			$order = $this->mEshop->orders->getOrder($this->input->orderId);  	 
			/*$faktura = $this->mFakturace->getFaktura($order['id']);
			$details = $this->getOrderDetailsSimple($order);	  
			
			$delivery = $this->mEshop->basket->getDeliveryPriceByType($order['delivery']);
			//  $delivery = 0;  
			$faktura->changeData($this->input, $delivery); 
			$this->mEshop->orders->changeOrder($order['id'], $this->input);	
			*/
    
			//pr($details); 
			
			// parent::audit($this->input->title);  
		/*	$this->mEshop->orders->addNote($order['id'], $this->input);	
			
			parent::addModalInfo(1,'Objednávka uložena');  
		} else { 
			parent::addModalInfo(0,$err); 
		}
	}*/
	
	/**
	Zobrazi formular pro editaci objednvek
	*/
	function showeditformAction()
	{
		$oId = $this->request->getParam('id');
 		$this->view->order = $this->mEshop->orders->getOrder($oId);  
 		$this->view->items = $this->mEshop->orders->getItemsOrder($oId);
 		$this->view->faktura = $this->mFakturace->getFakturaByOid($this->view->order['id']);
 		$this->view->basket = $this->mEshop->basket;
 		$this->view->basicForm =  $this->view->render('controls/admin/modules/Eshop/OrderEditForm.phtml');
		parent::indexAction($this->template);
	}
	
	
	/**
	zpracuje data z formulare, preda je module_Orders
	*/
	function saveOrderAction()
	{
		$oId = $this->input->orderId;
		$this->input->efullname = $this->input->firstname . ' ' . $this->input->surname;
		
		$faktura = $this->mFakturace->getFakturaByOid($oId);
		
		// projit vsechno zbozi...
		$i=0;
		
	
		while( isset($this->input->{'item_'.$i}) )
		{
			
			$items[] = array( 	'title' => $this->input->{'item_'.$i},
								'count' => $this->input->{'item_' . $i . '_count'},
								'nodeId' => $this->input->{'item_' . $i . '_nodeId'},
								'price' => $this->input->{'item_' . $i . '_price'},
								'variantId' => $this->input->{'item_' . $i . '_variantId'}
										);
			$i++;
		}
		$mOrder = new module_Eshop_Orders();
	
		foreach($items as $item){
			$faktura->removeItemByTitle( $item['title'],$item['count'] );
			$mOrder->updateItem($item,$faktura->oid);
		}
// 		pr($items);
// 		die();
		//nove pridane
		$i=0;
		
	
		while( isset($this->input->{'newItem_'.$i}) )
		{
			
			if(!$this->input->{'newItem_'.$i})
			{
				$i++;
				continue;
			}
			if(!is_numeric($this->input->{'newItem_' . $i}))
			{
				$contentId = $this->view->mVariant->getVariantsByIdProduct(false,true,$this->input->{'newItem_' . $i});
				$nodeId = $this->tree->getContentsNodeId($contentId['id_product']);
				$this->input->{'newItem_'.$i} = $nodeId;
			}
			// kode je nodeId/varinata když nebude varianta zadaná bere se automaticky ta první
			// var[0] = nodeid, var[1] = variantid
			$var = explode('/',$this->input->{'newItem_'.$i});
			$node = $this->tree->getNodeById($var[0]);
			
			if(is_object($node)){
				if(!$var[1])
				{
					$node = $this->tree->getNodeById($var[0]);
					$c = $node->getPublishedContent();
					$variant = $this->mVariant->getVariantsByIdProduct($c->id,true);
				}
				
				if(!$this->input->{'newItem_' . $i . '_price'})
				{
					$this->input->{'newItem_' . $i . '_price'} = $variant['price'];
				}
 				$newItems[] = array( 'nodeId' => $var[0],
 								 'count' => $this->input->{'newItem_' . $i . '_count'},
 								 'price' => $this->input->{'newItem_' . $i . '_price'},
 								 'varinatId' => $variant['id']
							);
			$i++;
			}
		}
		
		
		
	
		foreach($newItems as $newItem){
			$faktura->addItemById($newItem['nodeId'], $newItem['count'],$newItem['varinatId']);
		}
		$this->input->suma = $faktura->sum;
		$this->input->vaha = $faktura->weight;
		$this->mEshop->orders->changeOrder($this->input->orderId, $this->input);
		$faktura->update();
	
		return parent::addModalInfo(1,'Data uložena');
	}
	
	public function checkOderForm()
	{	
		    
		do{	
			if(!$this->input->firstname || !$this->input->surname){
				$err = "Zadejte objednávajícího";			   
			    break;
			}	
			
			if(!$this->input->ulice || !$this->input->mesto || !$this->input->psc ){
				$err = "Zadejte adresu";			   
			    break;
			}
			
			 
			return false;
		} while (false);			
		return $err;
	}

	
	
	public function showWidgetAction()
	{  	
    	echo $this->view->render('/controls/admin/modules/Eshop/EshopWidget.phtml');
	}	
	
	public function showpaymentsAction()
	{
		
		$mDelivery = new module_Delivery();
		$delivery = $mDelivery->getDeliveryById($this->input->idDelivery);
		$pays['price'] = $delivery['price'];
		$pays['deliveries'] = $mDelivery->showValidPayments($this->input->idDelivery);
		$pays['payPrice'] = $mDelivery->showValidPayments($this->input->idDelivery,true);
		echo json_encode($pays);
	}
	
	public function showpaymentpriceAction()
	{
		$mPayment = new module_Payment();
		$payment = $mPayment->getPaymentById($this->input->idPayment);
		echo json_encode($payment['cena']);
	}
	
	

	public function renderAction()
	{
		echo '<option value="volvo">Volvo</option>
  <option value="saab">Saab</option>
  <option value="mercedes">Mercedes</option>
  <option value="audi">Audi</option>';
die;
	}
	
	public function editorderAction()
	{
			$oId = $this->request->getParam('id');
		if($this->input->saveContentB)
		{
			$this->saveOrderAction();
			$this->_redirector->goto('listorders', 'eshop', null,array('loadsession' => 1));
		}
 		$this->view->order = $this->mEshop->orders->getOrder($oId);  
 		
 	
 		$mDelivery = new module_Delivery();
 		$this->view->allDeliveries = $mDelivery->showValidDeliveries();
 		$this->view->selectedPayment = $mDelivery->showValidPayments($this->view->order['delivery']);
 		$this->view->items = $this->mEshop->orders->getItemsOrder($oId);
 		$this->view->faktura = $this->mFakturace->getFakturaByOid($this->view->order['id']);
 		$this->view->faktura->items = $this->view->items;
		$this->view->mVoucher = new module_Voucher();
		$this->view->voucher = $this->view->mVoucher->getVoucherByOrderId($oId); 
 		$this->view->total = $this->mEshop->orders->getTotalSum($this->view->items,$this->view->order,true);
 		$this->view->basket = $this->mEshop->basket;
 		$this->view->basicForm =  $this->view->render('controls/admin/modules/Eshop/OrderEditForm.phtml');
		parent::indexAction($this->template);
	}
	
	function getColor($state, $delivery = false){
		$color = '';
		
		if($delivery == 'direct' && $state != 'SS'){
			$state = 'OO'; 
		}  
		
		switch ($state){
			case 'SS':
				$color = 'd0d0d0'; 
				break;
			case 'OO':
				$color = 'a6b9ff'; 
				break;
			case 'RP': // prijata platba ceka se na odeslani
				$color = 'ffdf00';  
				break; 
			case 'WP': // ceka se na platbu
				$color = 'fc9393';   
				break; 
			case 'WP1': // ceka se na platbu
				$color = 'f253ff';  
				break;  
			case 'WP2': // ceka se na platbu
				$color = '9f00ac';  
				break;   
			case 'OK':
				$color = '9aff40';
				break;				
		}
		return $color; 
	}
	
	
	private function setInputFromUrlParams()
	{
		if(!is_object($this->input)){
			$this->input =  new stdClass();
		}
		foreach ($this->_urlFilterParams as $item)
		{
			if($this->request->getParam('loadsession'))
			{
				$this->input->$item = '';
				$this->input->$item = $_SESSION[$this->view->curentAction][$item];
			}
			else{
				$this->input->$item = $_SESSION[$this->view->curentAction][$item] = '';
				$value = $this->request->getParam($item);
				if(isset($value))
				{
					$this->input->$item = $_SESSION[$this->view->curentAction][$item]  =  $this->request->getParam($item);			
				}
			}
		}
			
	}
	
	public function listordersAction() 
	{  			

		//$order = $this->mEshop->orders->getOrder(230);
// 		$this->mEshop->orders->sendEmail('orderSentEmail',$order,$this->view);
		//$this->mEshop->orders->sendEmail('orderSentEmail',$order,$this->view);
// 		die();
// 		$orders = $this->mEshop->orders->getOrdersBackend('orderAdjusted',$this->input->filter1,'created','asc');
// 		die();
		
	//	pr($orders);
		$this->view->defaultSortType = 'desc';
			$this->view->defaultSort = 'created';
		$this->view->activeUrl = $this->view->url(array('module' => 'cms','controller'=>'eshop','action'=>'listorders','amodule'=>'items','ajax' => '0'), null, TRUE);
		if($this->input->tableActions){
			$this->input->tableaction = $this->input->tableActions;
		//	pr($this->input);
			parent::performMultiAction();
		}
		 $this->setInputFromUrlParams(); 
		$mDelivery = new module_Delivery();		
		$this->view->filter1 = $mDelivery->getPaymentValid();
		
		$this->view->tableActions = array(
				'orderPending' => 'Nevyřízeno',
				'orderAdjusted' => 'Upraveno',
				'orderTexted' => 'Zadáno',
				'orderAdjustedEmail' => 'Upraveno s emailem',
				'orderSent' => 'Odesláno',
				'orderSentEmail' => 'Odesláno s emailem',
				'orderStorno' => 'Stornovat',
				'orderStornoEmail' => 'Stornovat s emailem',
				'orderWaitPay' => 'Čeká na platbu (pro platbu převodem)',
				'orderWaitPayEmail' => 'Čeká na platbu s emailem (pro platbu převodem)',
				'orderDelete' => 'Smazat',
		);
		
		$this->view->filter2 = array(
			'0' => 'Všechny',
			'WAIT' => 'Nevyřízené',
			'CHANGE' => 'Upravené',
			'TEXTED' => 'Zadáno',
			'SEND' => 'Odeslané',
			'CANCEL' => 'Stornované',
			'WPAY' => 'Čeká na platbu',
			
		);
		
		$colorsFilter = array(
				'0' => 'Všechny',
				'WAIT' => '#FF4242',
				'CHANGE' => '#00F',
				'SEND' => '#00FF40',
				
				'WPAY' => '#FF80FF',
					
		);
		
	  $getItems = $this->request->getParam('getItems');
		$isAjax = $this->request->getParam('ajax');
		$nodeId = $this->request->getParam('node');
		$nodeId = $nodeId?$nodeId:1;
		
		$params = array(); 
		$dg = new DataGrid('pagesTable');
		$dg->setRefresUrl($this->view->url(array('action' => 'listorders', 'getItems' => 1, 'ajax' => 1)))
			->setTitle('Výpis objednávek') 
			->setHeight(400) 
			->setItemsPerPageOptions('10, 15, 20, 30, 50, 500', 50);
		$dg->setHeaders( 
			array( 
				array('Č. obj.', 'id', 30, 'true', 'left', 'false'),
				array('Zákazník', 'name', 100, 'true', 'left', 'false'),
				array('Objednáno', 'created', 100, 'true', 'left', 'false'), 
				array('Zboží', 'items', 300, 'true', 'left', 'false'),  
				array('Částka', 'price', 50, 'true', 'left', 'false'),
				array('Způsob doručení', 'delivery',  100, 'true', 'left', 'false'),
				array('Způsob platby', 'payment',  100, 'true', 'left', 'false'),
				array('Zaplaceno', 'isPayed',  60, 'true', 'left', 'false'),
				array('Stav', 'state',  60, 'true', 'left', 'false'),
				array('Tisk', 'printt',  60, 'true', 'left', 'false'),   
			)
		)->setSearchableColls(   
			array(    
				array('Č. obj.', 'id', 'true') 
			)
		)->setButtons( 
			array(   
			)
		); 
		if($getItems){   
			
			
			  
		
			$products = new module_Products();     
			$products->inAdmin = true;  
			$params = array(); 
			
			list($currentPage, $rp, $sortname, $sortorder, $query, $qtype, $start) = $dg->getParams('created', 'desc');
		//, $start, $rp 
			$params['start'] = $this->input->start!= '?' ? Utils::formatDateDb2($this->input->start) : null;
		 	$params['stop'] = $this->input->end != '?' ? Utils::formatDateDb2($this->input->end) : null;
			// $this->request->getParam('text');
		 	$params['search'] = $this->input->fmFilterSel;
	
			$orders = $this->mEshop->orders->getOrdersBackend($this->input->filter2,$this->input->filter1,'created',$sortorder,$start, $rp, $params);
		
			$params['cnt'] = true;
			$rowsTotalCount = $this->mEshop->orders->getOrdersBackendCount($this->input->filter2,$this->input->filter1,'created',$sortorder,$start, $rp, $params);
			//$cats = $this->view->tree->getParentSelect(3801, '&ensp;', false);			
			$active = $this->view->tableFilter0 == 'active' ? 1 : 0;  
			$domain = $this->view->tableFilter1 == 'all' ? 0 : $this->view->tableFilter1;
			//$znacky = $this->view->tree->getNodesAsSelect(3817, 'FOLDER');			
			$nOrders = array();
			$rowsFormated = array();		
			foreach ($orders as $order){    
				$nOrder = array();   
				$editUrl = $this->view->url(array('controller' => 'eshop','action' => 'editorder', 'id'=> $order['id'], 'ajax' => 0));
				$nOrder['id'] = '<input rel="'.$order['id'].'" name="chbx[' . $order['id'] . ']" type="checkbox" /> '.$order['id'];
				$nOrder['name'] = '<a href="' . $editUrl . '">'.$order['surname'] . ' '. $order['firstname'].'</a>';
				$nOrder['created'] = Utils::formatTime($order['created']);
				
				
				$d =  $this->mEshop->orders->getOrderDetails($order['id']);
				$obj = array();
				foreach ($d as $od){
				$obj[] = helper_FrontEnd::reduceText($od['title'], 30);
				}
				
				$nOrder['items'] = implode('<Br />', $obj);
				$nOrder['price'] = $order['suma'];
				$nOrder['delivery'] = $order['deliveryText']; // . ' <br > ' .  $this->mEshop->basket->deliveryOptions[$order['delivery']]['payments'][$order['payment']][2];
				$nOrder['payment'] = $order['paymentText'];

			

			//nove
			



			$state = '<span style="color:' . $colorsFilter[$order['state']] . '">' . $this->mEshop->orders->getState($order['state']).   '</span>'; // . $order['state']  $order['state']   .

			$nOrder['isPayed'] = $state2;
			$nOrder['state'] = $state;
			$url = '/?getFaktura=' . $order['fid'] . '&f=1'. '&html=1';
			$url2 =  $url . '&order='.$order['id'];
			$nOrder['printt'] = '<a style="color:red;" href="' . $url2 . '" target="_blank">Obj</a>';
			//$nOrder['rowColor'] = $this->getColor($order['state'], $order['delivery']);
			$entry = array(   
					'id'=>$order['id'],
					'cell' => $nOrder
				);
				$rowsFormated[] = $entry;
			}
			if($isAjax){
				$dg->renderAjax($currentPage, $rowsTotalCount, $rowsFormated);
				die();  
			};
		}
		$this->view->renderFilter = 'controls/admin/lists/filter/ListOrders.phtml';
		$this->view->ordersList = $dg->render($this->view, 'controls/admin/lists/PagesStandard.phtml');
		parent::indexAction($this->template);  
	}  
	
	
	
	
	public function listcustomersAction() 
	{
		  					
		$getItems = $this->request->getParam('getItems');
		$isAjax = $this->request->getParam('ajax');
		$text = $this->request->getParam('fmFilterSel');
		$this->view->activeUrl = $this->view->url(array('module' => 'cms','controller'=>'eshop','action'=>'listcustomers','amodule'=>'items','ajax' => '0'), null, TRUE);
		$this->view->sortUrl = '/cms/eshop/sortcustomer';
		$this->view->defaultSort = 'prijmeni';		
		$this->setInputFromUrlParams(); 
		$params = array();
		$dg = new DataGrid('customersTable');
		$dg->setRefresUrl($this->view->url(array('action' => 'listcustomers', 'getItems' => 1, 'ajax' => 1)))
			->setTitle('Výpis nakupujících') 
			->setHeight(400) 
			->setItemsPerPageOptions('10, 15, 20, 30, 50, 500', 50) 
			; 
			
		$dg->setHeaders( 
			array( 
				
				array('Příjmení', 'prijmeni', 90, 'true', 'left', 'false'),
				array('Jméno', 'jmeno', 90, 'true', 'left', 'false'), 
				array('Email', 'email', 150, 'true', 'left', 'false'),
				//array('Foto', 'photos', 80, 'true', 'left', 'false'),  
				array('Telefon', 'telefon', 80, 'true', 'left', 'false'),
				array('Emailing', 'mailing',  45, 'true', 'left', 'false')
			)
		)->setSearchableColls(   
			array(    
				array('Příjmení', 'prijmeni', 'true'),
				array('Jméno', 'jmeno', 'true') ,
				array('Email', 'email', 'true') 
			)
		)->setButtons( 
			array(  
			)
		);
		
		if($getItems){ 	
		
		list($currentPage, $rp, $sortname, $sortorder, $query, $qtype, $start) = $dg->getParams('prijmeni', 'asc');
		$sortname = $sortname ? $sortname : 'prijmeni';
		if($text){			
			$params['search'] = $text;
		}
		$customers = $this->mCustomerts->getCustomers('prijmeni', $sortorder, $start, $rp, $params);
		// pr($orders);    
		$rowsTotalCount =  $this->mCustomerts->getCustomersCout('prijmeni', $sortorder, $start, $rp, $params); 
		
		$active = $this->view->tableFilter0 == 'active' ? 1 : 0;
		$domain = $this->view->tableFilter1 == 'all' ? 0 : $this->view->tableFilter1;
		// pr($orders); //  		 pr($this->mEshop->basket->deliveryOptions);
		
		$nOrders = array(); 
		foreach ($customers as $customer){  
			$nOrder = array();
			//$nOrder['id'] = $customer['id'];
			$editUrl = $this->view->url(array('controller' => 'eshop','action' => 'edituser', 'id'=> $customer['id']));
			$nOrder['prijmeni'] = '<a href="' . $editUrl . '">' . $customer['prijmeni'] . '</a>';
			$nOrder['jmeno'] = $customer['jmeno']; 
			$nOrder['email'] = $customer['email']; 
			$nOrder['telefon'] = $customer['telefon']; 
			$nOrder['mailing'] = $customer['mailing']?'ano':'ne'; 
			$nOrders[] = $nOrder;  
			$entry = array(
					'id'=> $customer['id'],
					'cell' => $nOrder
			);
			$rowsFormated[] = $entry;
		}  
		}
		$export = $this->request->getParam('tableExport'); 
		if($export){

			$headers = array(
				'Příjmení', 'Jméno', 'Email', 'Telefon', 'Novinky', /*'Skladem', 'Obrat', 'Prodáno ks'*/
			); 
			$data = array();
			foreach ($nOrders as $o){
				unset($o['id']);
				$data[] = $o; 
			}
			
			$title = 'zakaznici';   
			$this->generateXLS($headers, $data, $title);
			die();  
		}
		
		//[payment] => 1 [delivery] => czDobirka 
		if($isAjax){
			$dg->renderAjax($currentPage, $rowsTotalCount, $rowsFormated);
			die();http://new.eshop-povleceni.cz/cms/pages/index/amodule/items/getItems/1/id/51217/filenode/51199/ajax/0
		};
		$this->view->renderFilter = 'controls/admin/lists/filter/ListCustomers.phtml'; 
    	$this->view->pagesList = $dg->render($this->view, 'controls/admin/lists/PagesStandard.phtml');  
    	parent::indexAction($this->template);
	}

	
	

	public function repriceAction()
	{
		$this->view->mVariant->rePriceVariant($this->input);
		die;	
	}
	
	
	public function setinvtitleAction()
	{
		$this->view->mVariant->setInvTitle($this->input);
		die;	
	}
	
	public function purchaseAction()
	{
		$this->view->mVariant->setPurchaseVariant($this->input);
		die;	
	} 
	
	
	public function seteanAction()
	{
		$this->view->mVariant->setEANVariant($this->input);
		die;	
	}
	
	public function listitemsAction()
	{
			 
		//			$params = array();  
 			
		//	e($params);
 			$this->setInputFromUrlParams(); 
			$this->view->input = $this->input;
		$this->view->activeUrl = $this->view->url(array('module' => 'cms','controller'=>'eshop','action'=>'listItems','amodule'=>'items'), null, TRUE);
		$repriceUrl = $this->view->url(array('module' => 'cms','controller'=>'eshop','action'=>'reprice',), null, TRUE);
		$poradiUrl = $this->view->url(array('module' => 'cms','controller'=>'eshop','action'=>'setporadi',), null, TRUE);
	//	e($this->view->activeUrl);
		if($this->input->tableaction){
			$this->input->tableaction = $this->input->tableaction;
			parent::performMultiAction();
		}
		
		$products = new module_Products();
		$params['showFirstVariant'] =1; 
		$params['cms'] = 1;
		$params['cms'] = true;
	//	pr($orders = $products->getProducts($sortname, $sortorder, $start, $rp, $params) );
		$getItems = $this->request->getParam('getItems');
		$isAjax = $this->request->getParam('ajax');
		$nodeId = $this->request->getParam('node');
		$nodeId = $nodeId?$nodeId:1;
		
		//$this->view->hasDD = true;  
		

		
		$cats = $this->view->tree->getParentSelect(3801, '', false);
		unset($cats['3801']);
		$this->view->cats = helper_Input::addNotDefinedOption($cats, '- Všechny kategorie -', '');
		
		// dod2lat
		$vyrobci = helper_Input::addNotDefinedOption($products->getZnacky(false,true), '- Všichni výrobci -', '');
		//pr($products->getZnacky(false,true));
		$this->view->vyrobci = $vyrobci;
		
		$this->view->sortUrl = '/cms/eshop/sortitems?cats='.$this->request->getParam('cats');
		$mp = new content_Product();
		$skladem = helper_Input::addNotDefinedOption($mp->skladOptions, '- skladovost (vše) -', '');
		
		$this->view->skladem = $skladem;
		//$this->view->state = array('published' => 'Publikované', 'archived' => 'Archivované');
		
		$this->view->defaultSort = 'cm.dateModif';
		$this->view->defaultSortType = 'desc';
		
		$dg = new DataGrid('pagesTable');
		$dg->setRefresUrl($this->view->url(array('action' => 'listItems', 'getItems' => 1, 'ajax' => 1)))
			->setTitle('Výpis produktů') 
			->setHeight(400) 
			->setItemsPerPageOptions('10, 15, 20, 30, 50, 500', 50) 
			; 
			
		$dg->setHeaders( 
			array( 
				array('Název produktu', 'title', 350, 'true', 'left', 'false'),
				array('Cena', 'price', 70, 'true', 'left', 'false'), 
				array('Nakupní cena', 'purchase_price', 100, 'true', 'left', 'false'), 
				array('Interní ID', 'ext_id', 150, 'true', 'left', 'false'),              
				array('Prodávanost', 'cm.sold', 70, 'true', 'left', 'false'),  
				array('Nazev Fa', 'inv_title', 150, 'true', 'left', 'false'),      
				array('Značka', 'znacka', 80, 'true', 'left', 'false'),
				array('Foto', 'photos', 80, 'true', 'left', 'false'),  
				array('Cena ručně', 'update', 40, 'true', 'left', 'false'),
				array('Změněno', 'cm.dateModif',  40, 'true', 'left', 'false'),
				array('Vytvořeno', 'cm.dateCreate',  40, 'true', 'left', 'false'),
				array('Stav', 'state',  40, 'false', 'left', 'false'),
				array('ID', 'n.id', 30, 'true', 'center', 'true')   
			)
		)->setSearchableColls(   
			array(    
				array('Název', 'title', 'true') 
			)
		);
		if($this->view->user->group =='Superadmin' || $this->view->user->group == 'Administrators'){
			$btns = array('Smazat označené', 'delete', 'onpress', 'itemDelete');
			$btns1 = array('Publikovat', 'published', 'onpress', 'published');
			$btns2 = array('Archivovat', 'archivated', 'onpress', 'archivated');
			$dg->setButtons( 
			array(   
				$btns,$btns1,$btns2
			)); 
			
		} elseif($this->view->user->group =='Redactors') {
			$btns1 = array('Publikovat', 'published', 'onpress', 'published');
			$btns2 = array('Archivovat', 'archivated', 'onpress', 'archivated');
			$dg->setButtons( 
			array(   
				$btns1,$btns2
			));   
		}
		
		if($getItems){  
		   
			
			
			
				
			$products->inAdmin = true;  
		
			$params['showFirstVariant'] =1;
			$params['category'] = $this->request->getParam('cats');
			$params['update'] = $this->input->update;
 			$params['znacka'] = $this->input->vyrobci;
 			$params['state'] = $this->input->state;
 			$params['search'] = $this->input->fmFilterSel;
			$params['skladem'] = $this->input->skladem;
 			
			list($currentPage, $rp, $sortname, $sortorder, $query, $qtype, $start) = $dg->getParams('cm.dateModif', 'asc');
			$_SESSION['currentPage'] = $currentPage;
			$_SESSION['currentCountPage'] = $rp;
			$params['showAllV'] = true;
			$params['cms'] = 1;
			//pr($params);
			$rowsTotalCount = $products->getProductsCout($sortname, $sortorder, 0, 1, $params); 
			$orders = $products->getProducts($sortname, $sortorder, $start, $rp, $params);
		
			$znacky = $this->view->tree->getNodesAsSelect(3817, 'FOLDER');			
			$nOrders = array();
			$cp = new content_Product(); 
			 
			$rowsFormated = array();
	
			foreach ($orders as $order){   
				  
				$editUrl = $this->view->url(array('controller' => 'eshop','action' => 'editproduct', 'id'=> $order['id'], 'ajax' => 0));
					$spanEDITS = $spanEDITE = $spanS = $spanE = '';
					
					if($order['hlucnost']==0 || $order['enerClass']==0 || $order['vyska']==0 || $order['sirka']==0 || $order['hloubka']==0 ||
							$order['spotreba']==0)
					{
						
						$spanE = '</span>';
					}
				 	 if(!$order['parent'] && !$spanS)
				 	 {
				 	 	$spanS = '<span class="red">';  
				 	 	$spanE = '</span>';
				 	 }
					 $state = $order['state'] == 'PUBLISHED' ?  'Publikovaný' : 'Archivovaný';
					//   pr($order); die();
					$order['path'] =  $cp->getDetailUrl2($this->view, $order['parent'], $order['id'], $order['title']);
					$nOrder = array(); 
					$purchaseUrl = '/cms/eshop/purchase';   
					$setTitleUrl = "'/cms/eshop/setinvtitle'";
					$setEANUrl = '/cms/eshop/setean';
					$nOrder['n.id'] =  $order['id'];  
					$nOrder['title'] = '<input name="chbx[' . $order['id'] . ']" type="checkbox" /><a href="' . $editUrl . '">'.$spanS.'' . $order['title'] .''.$spanE.'</a> &nbsp;&nbsp;'  . Utils::getFrontEndLink($order['path'], false, '', false, 0, $this->view);  
					$nOrder['path'] = Utils::getFrontEndLink( $order['path'], false, '', false, 0, $this->view);
					$nOrder['cm.sold'] = '<input type="number" onchange="reOrder(\'reOrder'.$order['cid'].'\', \''.$poradiUrl.'\')" name="reOrder'.$order['cid'].'" id="reOrder'.$order['cid'].'" class="sold" value="'.$order['sold'].'">
					<span id="reOrderShow'.$order['cid'].'" class="ui-icon ui-icon-check removeIcon nodisplay">';   
					  
					if($order['ext_id']){
						$nOrder['ext_id'] = $order['ext_id'];     
					}   
					else{   
						$nOrder['ext_id'] = '<input type="number" onchange="reEAN(\'reEAN'.$order['cid'].'\', \''.$setEANUrl.'\')" name="reEAN'.$order['cid'].'" id="reEAN'.$order['cid'].'" class="reprice" value="'.$order['ext_id'].'">
						<span id="reOrderShow'.$order['cid'].'" class="ui-icon ui-icon-check removeIcon nodisplay">';
					}
					$nOrder['purchase_price'] = '<input type="number" onchange="rePur(\'rePur'.$order['cid'].'\', \''.$purchaseUrl.'\')" name="rePur'.$order['cid'].'" id="rePur'.$order['cid'].'" class="" value="'.$order['purchase_price'].'">
					<span id="reOrderShow'.$order['cid'].'" class="ui-icon ui-icon-check removeIcon nodisplay">';
					   
					$nOrder['inv_title'] = '<input type="text" onchange="sendValue(this, '.$order['variantId'].' ,'.$setTitleUrl.')" name="rePur'.$order['cid'].'" id="rePur'.$order['cid'].'" class="" value="'.$order['inv_title'].'">
					<span id="reOrderShow'.$order['cid'].'" class="ui-icon ui-icon-check removeIcon nodisplay">';       
					            
					    
					$nOrder['price'] = '<input type="number" onchange="rePrice(\'rePrice'.$order['variantId'].'\', \''.$repriceUrl.'\')" name="rePrice'.$order['variantId'].'" id="rePrice'.$order['variantId'].'" class="reprice" value="'.$order['price'].'">
					<span id="reP'.$order['variantId'].'" class="ui-icon ui-icon-check removeIcon nodisplay"></span>';  
					$nOrder['znacka'] = $vyrobci[$order['znacka']] ? $vyrobci[$order['znacka']]:'NE';  
					$nOrder['pid'] = $order['id'];    
					$nOrder['photos'] = strlen($order['obrazky'])?'ANO':'NE';
					$nOrder['update'] = $order['update']?'ANO':'NE'; 
					$nOrder['cm.dateCreate'] = Utils::formatDateFromDb($order['dateCreate']);
					$nOrder['state'] = $state;
					$nOrder['cm.dateModif'] = Utils::formatDateFromDb($order['dateModif']);     
				    
				$entry = array(   
					'id'=>$order['id'],
					'cell' => $nOrder
				);
				$rowsFormated[] = $entry;
			}
			  
			if($isAjax){
				$dg->renderAjax($currentPage, $rowsTotalCount, $rowsFormated);
				die();  
			};
		}
		$this->view->renderFilter = 'controls/admin/lists/filter/ListIItem.phtml';
		$this->view->pagesList = $dg->render($this->view, 'controls/admin/lists/PagesStandard.phtml');
		parent::indexAction($this->template);  
	}

	public function setporadiAction()
	{
		$products = new module_Products();
		$products->setOrder($this->input);
		die;	
	}
	
	
	public function sortpaymentAction()
	{
		$stringSort = $this->inputGet->sort;
		$mPayment = new module_Payment();
		$mPayment->saveSort($stringSort);
	}
	
	
	public function sortitemsAction()
	{
		//$stringSort = $this->inputGet->cats;
		//$stringSort = $this->inputGet->sort;
		$this->view->mProducts->saveSort($this->inputGet->sort,$_SESSION['currentPage'],$_SESSION['currentCountPage']);
		
	}
	
	public function sortdeliveryAction()
	{
		$stringSort = $this->inputGet->sort;
		$mDelivery = new module_Delivery();
		$mDelivery->saveSort($stringSort);
	}
	
	
	
	
	public function listdeliveryAction()
	{
		//echo 'lol';
		//$m_d = new module_Delivery();
		//$deliveries = $m_d->getDeliveries();
	//	echo $this->view->render('controls/admin/modules/Eshop/DeliveryList.phtml');
		
		if($this->input->tableaction == 'delete'){
		   $this->input->tableaction = 'deliveryDelete';
		   parent::performMultiAction();
  		} 

		$getItems = $this->request->getParam('getItems');
		$isAjax = $this->request->getParam('ajax');
		$nodeId = $this->request->getParam('node');
		$nodeId = $nodeId?$nodeId:1;
		$this->view->sortUrl = '/cms/eshop/sortdelivery';
		$this->view->defaultSort = 'poradi';
		$params = array();
		$dg = new DataGrid('pagesTable');
		$dg->setRefresUrl($this->view->url(array('action' => 'listdelivery', 'getItems' => 1, 'ajax' => 1)))
			->setTitle('Výpis možností dopravy') 
			->setHeight(400) 
			->setItemsPerPageOptions('10, 15, 20, 30, 50, 500', 50) 
			; 	
		$dg->setHeaders( 
			array(
				array('Název', 'title', 350, 'true', 'left', 'false'),
				array('Cena', 'price', 80, 'true', 'left', 'false'),
				array('Hmotnost od', 'from', 50, 'true', 'left', 'false'), 
				array('Hmotnost do', 'to', 80, 'true', 'left', 'false'),
				array('Zdarma nad', 'freeDelivery',  60, 'true', 'left', 'false'),
				array('Stav', 'valid',  60, 'true', 'left', 'false'),   
			)
		)->setSearchableColls(   
			array(    
				array('Název', 'nazev', 'true') 
			)
		)->setButtons( 
		   array(  
		    array('Smazat označené', 'delete', 'onpress', 'deletep')   
		   )); 
		 
		
		if($getItems){
			$delivers = new module_Delivery();     
			//$products->inAdmin = true;  
			$params = array(); 
			
			list($currentPage, $rp, $sortname, $sortorder, $query, $qtype, $start) = $dg->getParams('poradi', 'desc');

			$deliveries = $delivers->getAdminDeliveries($sortname, $sortorder, $start, $rp, $params);
			$rowsTotalCount = count($deliveries); 
		 		 
			$active = $this->view->tableFilter0 == 'active' ? 1 : 0;  
			$domain = $this->view->tableFilter1 == 'all' ? 0 : $this->view->tableFilter1;			
			$nOrders = array();
			$cp = new content_Product(); 
			$rowsFormated = array();		
			foreach ($deliveries as $del){   
				  
				$editUrl = $this->view->url(array('controller' => 'eshop','action' => 'delivery', 'id'=> $del['id']));
					$nOrder = array();
					$nOrder['title'] = '<input name="chbx[' . $del['id'] . ']" type="checkbox" /> &nbsp; <a href="' . $editUrl . '">' . $del['nazev'] . '</a>';  
  					$nOrder['price'] = $del['price'];
  					$nOrder['from'] = $del['hmotnost_od'];
  					$nOrder['to'] = $del['hmotnost_do'];
  					$nOrder['to'] = $del['hmotnost_do']; 
  					$nOrder['freeDelivery'] = $del['zdarma_nad'];
  					$nOrder['valid'] = $del['valid']?'<div style="background: green; width:1em; height:1em;float:left;margin: 2px 5px 0 0;"></div>':'';					
				$entry = array(   
					'id'=>$del['id'],
					'cell' => $nOrder
				);
				$rowsFormated[] = $entry;
			}
			  
			if($isAjax){
				$dg->renderAjax($currentPage, $rowsTotalCount, $rowsFormated);
				die();  
			};
		} 
		
		$this->view->hasDD = true;
		$this->view->showButton = new stdClass();
		$this->view->showButton->link = $this->view->url(array('action' => 'delivery'));
		$this->view->showButton->title = 'Vložit dopravu';
		$this->view->pagesList = $dg->render($this->view, 'controls/admin/lists/PagesStandard.phtml');
		parent::indexAction($this->template);  
	}  
	
	public function deliveryAction()
	{
		$mDelivery = new module_Delivery();
		if(isset($this->input->save))
		{
			$message = $mDelivery->saveDelivery($this->input);
			if(!$message){
				$this->_redirector->goto('listdelivery', 'eshop', null,array());
			} else {
				$this->addErrorInstantMessage($message );
			}	
		}
		$this->view->properies = $mDelivery->renderPropertiesDelivery($this->request->getParam('id'),$this->input);		
		$this->view->basicForm =  $this->view->render('controls/admin/forms/BasicForm.phtml');

		parent::indexAction($this->template);  
	}
	
	public function listItemsActionOld()
	{  
		$this->view->hasExport = true;  
		$this->view->tableParentTab = 'vypisDole'; 
		
		$this->view->tableActions = array(      
			'itemArchive' => 'Archivovat',   
			'itemPublish' => 'Publikovat', 
			'itemDelete' => 'Smazat' 
		);  
 
		$tableFilter0 = ($this->request->getParam('tableFilter0'))?$this->request->getParam('tableFilter0'):$_SESSION['tableFilter0'];
		$tableFilter1 = ($this->request->getParam('tableFilter1'))?$this->request->getParam('tableFilter1'):$_SESSION['tableFilter1'];
		$tableFilter2 = ($this->request->getParam('tableFilter2'))?$this->request->getParam('tableFilter2'):$_SESSION['tableFilter2'];   
		$tableFilter3 = ($this->request->getParam('tableFilter3'))?$this->request->getParam('tableFilter3'):$_SESSION['tableFilter3'];
		  
		$this->view->curentViewState['action'] = 'listItems';				  	 
		$this->view->tableSort = $this->request->getParam('sort')?$this->request->getParam('sort'):'created';			 
		$this->view->tableSortType = $this->request->getParam('sortType')?$this->request->getParam('sortType'):'Desc' ; 
		$this->view->tableFilter0 = $tableFilter0?$tableFilter0:'all';  
		$this->view->tableFilter1 = $tableFilter1?$tableFilter1:'all';
		$this->view->tableFilter2 = $tableFilter2?$tableFilter2:'all'; 
		$this->view->tableFilter3 = $tableFilter3?$tableFilter3:'published';
		
		$_SESSION['tableFilter0'] = $this->view->tableFilter0; 
		$_SESSION['tableFilter1'] = $this->view->tableFilter1;  
		$_SESSION['tableFilter2'] = $this->view->tableFilter2;
		$_SESSION['tableFilter3'] = $this->view->tableFilter3;
		  
		
		//$this->view->tableFilter1 = $this->request->getParam('tableFilter1')?$this->request->getParam('tableFilter1'):'all';
		   
		$cats = $this->view->tree->getParentSelect(3801, '&ensp;', false);      
		$this->view->tableFilters[] = helper_Input::addNotDefinedOption($cats, '- Všechny kategorie -', 'all');  
		 
		$vyrobci = helper_Input::addNotDefinedOption($this->view->tree->getNodesAsSelect(3817, 'FOLDER'), '- Všichni výrobci -', 'all');
		$this->view->tableFilters[] = $vyrobci;   
 		 
		$mp = new content_Product();
		$skladem = helper_Input::addNotDefinedOption($mp->skladOptions, '- skladovost (vše) -', 'all');
		 
		$this->view->tableFilters[] = $skladem;    
        $this->view->tableFilters[] = array('published' => 'Publikované', 'archived' => 'Archivované');   
        
		//$orders = $this->mEshop->orders->getOrdersBackend($this->view->tableFilter0, $this->view->tableFilter1, $this->view->tableSort, $this->view->tableSortType);   
		$orders = array();  
		// pr($orders);   
		$products = new module_Products();     
		$products->inAdmin = true;  
		$params = array();
		if($this->view->tableFilter0 != 'all'){
			$params['category'] = $this->view->tableFilter0;
		} 
		
		if($this->view->tableFilter3 != 'all'){ 
			$params['state'] = $this->view->tableFilter3;
		}
		
		// pr($this->view->tableFilters );
		
		if($this->view->tableFilter1 != 'all'){ 
			$params['znacka'] = array($this->view->tableFilter1);
		} 
		 
		if($this->view->tableFilter2 != 'all'){  
			$params['skladem'] = array($this->view->tableFilter2);
		} 
		
		
		$this->view->showSearch = true;  
		$search = $this->request->getParam('tableSearch');  
		if($search){
			$params['search'] = $search;
		} 
		
		$orders = $products->getProducts($this->view->tableSort, $this->view->tableSortType, 0, 5000, $params);
		 
		$active = $this->view->tableFilter0 == 'active' ? 1 : 0;
		$domain = $this->view->tableFilter1 == 'all' ? 0 : $this->view->tableFilter1;
		$znacky = $this->view->tree->getNodesAsSelect(3817, 'FOLDER');			
		$nOrders = array();
		$cp = new content_Product();
		
		 
		$export = $this->request->getParam('tableExport');
		if($export){
		$cats = $this->view->tree->getParentSelect(3801, '', false); 
			foreach ($orders as $order){
				// print_r($order);
				// die;
				$nOrder = array();
				$nOrder['title'] = $order['title']; 
				$nOrder['kategorie'] = $cats[$order['parent']]; 
				$nOrder['cena'] = $order['price']; 
				$nOrder['obvyklaCena'] = $order['price2']; 
				$nOrder['znacka'] = $znacky[$order['znacka']];
				$tmp = explode('|',$order['akce']);
				$more = false;
				foreach($tmp as $pr){
					if($more){
					$nOrder['priznak'] .= ', ';
					}
					$nOrder['priznak'] .= $cp->aptions[$pr];
					$more= true;
				}
				
				$nOrder['kod'] = $order['kod'];
				$nOrder['ean'] = strval($order['ean']);
				$nOrder['dostupnost'] = $cp->skladOptions[$order['skladem']]; 
				$nOrder['stav'] = $order['state'];
				$nOrders[] = $nOrder; 
				
			}
			//print_r()
			$headers = array(
				'Název výrobku', 'Kategorie', 'Cena', 'Běžná cena', 'Značka', 'Příznak', 'Kód zboží', 'EAN', 'Dostupnost', 'Stav'
			); 
			$data = array();
			foreach ($nOrders as $o){
				$data[] = $o; 
			}
			
			$title = 'zbozi';   
			$this->generateXLS($headers, $data, $title);
			die();  
		}
		
		foreach ($orders as $order){   
			//   pr($order); die();
			$order['path'] =  $cp->getDetailUrl2($this->view, $order['parent'], $order['id'], $order['title']);
			$nOrder = array();
			$nOrder['id'] = $order['id'];
			$nOrder['title'] = $order['title'];  
			$nOrder['path'] = Utils::getFrontEndLink( $order['path'], false, '', false, 0, $this->view);
			$nOrder['price'] = $order['price']; 
			$nOrder['znacka'] = $znacky[$order['znacka']]; 
			$nOrder['pid'] = $order['id'];    
			$nOrder['photos'] = strlen($order['photos'])?'ANO':'NE';
			$nOrder['skladem'] = $cp->skladOptions[$order['skladem']]; 
			$nOrder['dateCreate'] = $order['dateCreate'];
			$nOrder['dateModif'] = $order['dateModif'];  
			$nOrders[] = $nOrder;   
		} 
		
		
		
		
		$this->view->ordersTable = $nOrders;  
		
		
		//[payment] => 1 [delivery] => czDobirka 
				
		 
		$this->view->ordersTableHead = array(
			'title' => array(
				'title' => 'Název produktu',  
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			), 
			'show' => array(
				'title' => 'nohead',
				'atribs' => array('style' => 'width:20px;'),
			),  
			'price' => array(
				'title' => 'Cena', 
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),	
			'znacka' => array( 
				'title' => 'Značka',  
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),	
			'n.id' => array(
				'title' => 'ID',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'photos' => array(
				'title' => 'Foto',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),					
			'skladem' => array(
				'title' => 'Skladem',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),	
			'dateCreate' => array(
				'title' => 'Vytvořeno', 
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),
			'dateModif' => array(
				'title' => 'Posl. modifikace',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			)
		);  
		 
		$this->view->ordersTableActions = array( 
			'edit' => array(
				'title' => 'Detail', 
				'type'  => 'tab-refresh',
				'url'   => $this->view->url(array('action' => 'editProduct','id'=>'%$%', 'showSaveButton' => 1))
			)  
		); 
		
		/*
		,
			'delete' => array(
				'title' => 'Smazat',
				'type'  => 'tab-refresh',
				'tabId' => 'vypisDole',
				'url'   => $this->view->url(array('controller' => 'enquiry','action' => 'delete','id'=>'%$%'))
			),
				
			*/
    	echo $this->view->render('controls/admin/modules/Eshop/ItemsList.phtml');   	  
	} 
	
	/******* znacky **/
	function loadznackahtmlAction(){   
		$i = $this->request->getParam('nextid');
		$this->view->znacka = array();  
		$this->view->znackaId = $i; 
		$this->view->uploadLoaded = true;
		$this->view->fileuploadIdent = $i + 10000;
	    echo $this->view->render('controls/admin/modules/Eshop/Settings_znacka.phtml');
	    die(); 
	}
	
	function loadradahtmlAction(){    
		$i = $this->request->getParam('nextid');
		$z = $this->request->getParam('znacka'); 
		$this->view->rada = array();    
		$this->view->radaId = $i;   
		$this->view->znackaId = $z;  
		$this->view->uploadLoaded = true;
		$this->view->fileuploadIdent = $i + $z + 1000;
		$this->view->uploadLoaded = true; 

	    echo $this->view->render('controls/admin/modules/Eshop/Settings_znacka_rada.phtml');
	    die(); 
	} 

	function loadradyselectAction(){    
		$z = $this->request->getParam('znacka');
		$rady = array(); 
		if($z){
			$mProducts = new module_Products();  
			$rady = $mProducts->getRady($z);   
		} 
		if(count($rady)){
			$rady =  helper_Input::addNotDefinedOption($rady, ' - vyberte -', 0);       
		    echo '<Span style="line-height:20px;">řada:</Span> ' . $this->view->formSelect('rada', 0, array('style' => 'width:180px;'), $rady);
		} else { 
		    echo $this->view->formHidden('rada', 0); 
		}
	    die();  
	}

	/******  kategorie ***/
	function loadkategoriehtmlAction(){   
		$i = $this->request->getParam('nextid'); 
		$this->view->kident = $i;  
		$this->view->fileuploadIdent = $i + 100;
		$this->view->uploadLoaded = true; 
	    echo $this->view->render('controls/admin/modules/Eshop/Settings_kategorie_item.phtml');
	    die(); 
	}  
 	
	function loadkategorievlastnosthtmlAction(){    
		$i = $this->request->getParam('nextid');  

		$this->view->fileuploadIdent = $i + $kat + 500;
 		
		$kat = $this->request->getParam('kid'); 
		$this->view->kident = $kat;  
		$this->view->vident = $i;   
		$this->view->uploadLoaded = true;
	    echo $this->view->render('controls/admin/modules/Eshop/Settings_kategorie_item_vlastnost.phtml');
	    die(); 
	}  

	
	
	public function paymentAction()
	{
		$mPayment = new module_Payment();
		
		if(isset($this->input->save))
		{
			$message = $mPayment->save($this->input);
			if(!$message){
				$this->_redirector->goto('listpayments', 'eshop', null,array());
			} else {
				$this->addErrorInstantMessage($message );
			}	
		}
		$this->view->properies = $mPayment->renderPropertiesPayments($this->request->getParam('id'),$this->input);
		$this->view->basicForm =  $this->view->render('controls/admin/forms/BasicForm.phtml');
		parent::indexAction($this->template);  
		
	}
	
	/**
		Dopravy, platby, priplatky
	*/
	public function listpaymentsAction()
	{		
		if($this->input->tableaction =='delete'){
   			$this->input->tableaction = 'paymentDelete';
   			parent::performMultiAction();
  		}
  		
		$m_p = new module_Payment();
		$getItems = $this->request->getParam('getItems');
		$isAjax = $this->request->getParam('ajax');
		$nodeId = $this->request->getParam('node');
		$this->view->sortUrl = '/cms/eshop/sortpayment';
		$this->view->defaultSort = 'poradi';
		$nodeId = $nodeId?$nodeId:1;
		$params = array();
		$dg = new DataGrid('pagesTable');
		$dg->setRefresUrl($this->view->url(array('action' => 'listpayments', 'getItems' => 1, 'ajax' => 1)))
			->setTitle('Výpis možností plateb') 
			->setHeight(400) 
			->setItemsPerPageOptions('10, 15, 20, 30, 50, 500', 50) 
			; 
			
		$dg->setHeaders( 
			array(
				array('Název', 'title', 350, 'true', 'left', 'false'),
				array('Popis', 'popis', 350, 'true', 'left', 'false'),
				array('Cena', 'price', 80, 'true', 'left', 'false'),
				array('Stav', 'valid',  60, 'true', 'left', 'false'),   
			)
		)->setSearchableColls(   
			array(    
				array('Název', 'nazev', 'true') 
			)
		)->setButtons( 
		   array(  
		    array('Smazat označené', 'delete', 'onpress', 'deletep')   
		   )); 
		
		
		if($getItems){
			//$products->inAdmin = true;  
			$params = array(); 
			
			list($currentPage, $rp, $sortname, $sortorder, $query, $qtype, $start) = $dg->getParams('poradi', 'asc');
			$payments = $m_p->getAdminPayments($sortname, $sortorder, $start, $rp, $params);

			$rowsTotalCount = count($payments); 
		 		 
			$active = $this->view->tableFilter0 == 'active' ? 1 : 0;  
			$domain = $this->view->tableFilter1 == 'all' ? 0 : $this->view->tableFilter1;			
			$nOrders = array();
			$cp = new content_Product(); 
			$rowsFormated = array();		
			foreach ($payments as $del){   
				  
				$editUrl = $this->view->url(array('controller' => 'eshop','action' => 'payment', 'id'=> $del['id']));
					$nOrder = array();
					$nOrder['title'] = '<input name="chbx[' . $del['id'] . ']" type="checkbox" /> &nbsp; <a href="' . $editUrl . '">' . $del['nazev'] . '</a>';  
  					$nOrder['popis'] = $del['popis'];
  					$nOrder['price'] = $del['cena']; 
  					$nOrder['valid'] = $del['valid']?'<div style="background: green; width:1em; height:1em;float:left;margin: 2px 5px 0 0;"></div>':'';					
				$entry = array(   
					'id'=>$del['id'],
					'cell' => $nOrder 
				);
				$rowsFormated[] = $entry;
			}
			  
			if($isAjax){
				$dg->renderAjax($currentPage, $rowsTotalCount, $rowsFormated);
				die();  
			};
		}   
		
		$this->view->hasDD = true;  
		$this->view->showButton = new stdClass();
		$this->view->showButton->link = $this->view->url(array('action' => 'payment'));
		$this->view->showButton->title = 'Vložit platbu';
		$this->view->pagesList = $dg->render($this->view, 'controls/admin/lists/PagesStandard.phtml');
		parent::indexAction($this->template);
	} 
	
	public function settingsAction()
	{ 
		$emailsFolderNodeId = 5193; // parent = sys strom   
		// editace mozna take zde, pridani dalsich = http://demo.eportaly.cz/cms/pages/index/node/5193/ajax/0/amodule/items/id/0
		$this->view->emailNodes = $this->tree->getNodeChildren(5193, 'ITEM');
		
		if(isset($this->input->save)) 
		{
			$this->mSettings->saveEmails($this->input, $this->view->emailNodes);  
			$this->mSettings->saveEshopSettings($this->input); 
		 };
		 
		$this->view->priznaky = $this->mSettings->getPriznaky();
		$this->view->kategorie = $this->mSettings->getKategorie(true);
		$this->view->eshopSettings = $this->mSettings->eshopSettings();
		//pr($this->view->kategorie);
		$this->view->znacky = $this->mSettings->getZnackyRady(); 

		$this->view->settings =  $this->view->render('controls/admin/modules/Eshop/Settings.phtml');
		parent::indexAction($this->template);  
	}
	
	
	function saveEmails(){
		
	}
	
	public function deliveryDeleteAction()
	{
		
	}
	 
	 
	 
	 
	 
	 public function listPriplatkyAction()
	{
		$m_p = new module_Priplatky();
		$items = $m_p->getPriplatky();
		
		$this->view->tableDefaultListing = 20; 
		
		$this->view->tableParentTab = 'vypisDole'; 
		$this->view->tableActions = array(
			'priplatkyDelete' => 'smazat'
		);  
			  
		$this->view->curentViewState['action'] = 'listOrders';					 
		$this->view->tableSort = $this->request->getParam('sort')?$this->request->getParam('sort'):'created';
		
		$st = $this->request->getParam('sortType');
		   		    
		
		$this->view->tableSortType = $this->request->getParam('sortType')?$this->request->getParam('sortType'):'Desc' ; 
		$this->view->tableFilter0 = $this->request->getParam('tableFilter0')?$this->request->getParam('tableFilter0'):'on'; 
		$this->view->tableFilter1 = $this->request->getParam('tableFilter1')?$this->request->getParam('tableFilter1'):'all';
		//$this->view->tableFilter1 = $this->request->getParam('tableFilter1')?$this->request->getParam('tableFilter1'):'all'; 
		  
		//$this->view->tableFilters[] = array('on'=>'Nevyřízené','pre' => 'Předobjednávky', 'ov' => 'Vyřízené', 'wo' => 'Čekající', 'os' => 'Stornované', 'or' => 'Reklamované/vrácené', 'all' => 'Všechny');  
		//$this->view->tableFilters[] = array('all'=>'Všechny platby', 'czDobirka' => 'Dobírkou', 'bank' => 'Převodem', 'direct' => 'Osobní odběr'); 
		//$this->view->tableFilters[] = helper_Input::addNotDefinedOption($this->view->domains, 'Vše', 'all');   
		
		
		$this->view->showSearch = false;
		$params = array();
		
		
		  
		// pr($orders);    
		
		$active = $this->view->tableFilter0 == 'active' ? 1 : 0;
		$domain = $this->view->tableFilter1 == 'all' ? 0 : $this->view->tableFilter1;
		// pr($orders); //  		 pr($this->mEshop->basket->deliveryOptions);
		$nOrders = array(); 
		foreach ($items as $order){ 
			
			//print_r($order);
			$nOrder = array();  
			$nOrder['id'] = $order['id'];
			$nOrder['nazev'] = $order['nazev'];  
			$nOrder['cena'] = $order['cena'];  
			/*$color = $order['valid'] ? 'green' : 'red';
			$nOrder['valid'] = '<div style="background: '.$color.'; width:1em; height:1em;float:left;margin: 2px 5px 0 0;"></div>';
			$nOrder['suma'] = $order['suma'];  
			$nOrder['delivery'] = $order['fu'] . ' <br > ' .  $this->mEshop->basket->deliveryOptions[$order['delivery']]['payments'][$order['payment']][2];*/
			$nOrders[] = $nOrder;    
		}  
		
		$this->view->ordersTable = $nOrders;  
		
		$this->view->ordersTableHead = array(
			'nazev' => array(
				'title' => 'Název',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			),   
			'cena' => array(
				'title' => 'cena',
				'atribs' => array(),
				'sortUrlType' => 'refresh-tab',
				'parentTab' => 'vypisDole'
			)
		);  
		 
		
		  
		$this->view->ordersTableActions = array( 
			'edit' => array(
				'title' => 'Detail', 
				'type'  => 'modal',
				'url'   => $this->view->url(array('action' => 'priplatekForm','id'=>'%$%'))
			) 
		); 
		
		echo $this->view->render('controls/admin/modules/Eshop/PriplatkyList.phtml');
	}
	 
	 
	 public function priplatekFormAction()
	 {
		$m_p = new module_Priplatky();
		$p_id = $this->request->getParam('id');
		$this->view->priplatek = $m_p->getPriplatekById($p_id);
		echo $this->view->render('controls/admin/modules/Eshop/PriplatekDetails.phtml');
	 }
	 
	 public function savePriplatekAction()
	 {
		$m_p = new module_Priplatky();
		$err = $m_p->savePriplatek($this->input);
		if(!$err)
			parent::addModalInfo(1,'Data uložena');
		 else 
			parent::addModalInfo(0,$err);	
	 }
	 
	public function listAction()
	{  	
		return '';
	} 
	
	
	public function editAction()
	{
		$eventId = $this->request->getParam('e');
		$this->view->event = $this->moduleEvents->initEvent( $eventId );	
		parent::initFormValuesFromArray($this->view->event, 'event_');		
		$this->newEventAction();				
	}
	
	public function checkFormNewPage()
	{		
		//pr($this->inputGet);
		//exit();		
		do{	
			if(!$this->input->pageTitle){
				$err = "Zadejte název stránky";			   
			    break;
			}
			if(!$this->input->varianta_price_1){
				$err = "Zadejte cenu";			   
			    break;
			}
			if(!is_numeric($this->input->varianta_price_1)){
				$err = "Cena musí být číslo";			   
			    break;
			}										
			return false;
		} while (false);			
		return $err;
	}
	
	
	
	public function checkSEOForm()
	{		
		
		do{		  
			if($this->input->nodePath){
				if(($this->input->nodePath{0} != '/')){
					$err = "Cesta musí začínat lomítkem";			   
				    break;
				}  
				
				if(substr($this->input->nodePath , -1) == "/"){
					 $err = "Cesta nesmí končit lomítkem";			   
				     break;
			 	}	  
				
				if(strpos($this->input->nodePath, '.')){
					$err = "Zadejte cestu bez přípony (.html apod), přidá se automaticky";			   
				    break;
				}
				
				if($this->input->nodePath != $this->node->path){
					if(!Node::isPathAvailable($this->input->nodePath)){
						$err = "Zadaná cesta se již používá, zvolte jinou";			   			    	
						break;
					}
				}
			}					
				
			return false;
		} while (false);			
		return $err;
	}
	
	
	
	public function updateAction()
	{  
	
		
		$err = $this->checkFormEditPage();
		if(!$err){
			$err = $this->checkSEOForm();
		}
		if(!$err){ // ok
				
			// content
			$contentId = $this->request->getParam('contentId');
			
			$inVersions = true;
			if(!$contentId){
				$contentId = $this->node->getTheRightContent()->id;
				$inVersions = false;
			}
			//$contentId=$contentId?$contentId:$this->input-> 
			
			$this->view->content = Content::initUpdate($this->node, $this->input, $contentId);
			$this->view->contentId = $contentId;
			if(method_exists($this->view->content, 'beforeUpdate')){
				$err = $this->view->content->beforeUpdate( $this->view);
				if($err){
					return array(0, $err);   
					return ;
				}
			}
// 			if($_GET['temp']){
// 				pr( $this->input);
// 				die();
// 			}

			

			$this->view->content->update(false, false, $this->view, $this->input);
			$this->view->mVariant->saveVariats($this->input, $contentId);
			//node
			$this->node->initUpdate($this->input, $this->view);
			$this->tree->updateNode($this->node, false);
			 
			$this->node->checkStateChange($this->input, $this->view->content);
	
			if(method_exists($this->view->content, 'afterNodeUpdate')){
				$this->view->content->afterNodeUpdate( $this->view, $this->node);
			}

			parent::audit($this->node->title, $this->node->nodeId);
			if($this->input->rating>0)
			{
				$this->view->mReviews->saveSingleReview($this->input,$this->node->nodeId);
			}
			$this->view->mReviews->saveRating($n->nodeId);

			//$this->view->mProducts->savePositionCategories($contentId,$this->input->parent);
			
			$calledFrom = $this->request->getParam('calledFrom');
			if($calledFrom == 'eshop'){
				return array(1, 'Data uložena', 0, $this->view->url(array('module' => 'cms', 'controller' => 'eshop','action' => 'listItems', 'node' => $this->nodeId), null,true));
				return ;
			}
			 
			if($inVersions){ // ve verzich
				return array(1, 'Data uložena', '',  $this->view->url(array('action' => 'versions', 'node' => $this->node->nodeId)));
			} else {
				return array(1, 'Data uložena');
			}
				
		} else {
			return array(0,$err);
		}
	}
	
	public function saveAction()
	{
		$this->setViewNode();
		$this->input->parent = $this->input->parent?$this->input->parent:3801;
		$parentId = $this->input->parent;   
		 
		$ctype = $this->input->contentType;
		$content = Content::init($ctype, $this->input, $this->acl);			
		if(method_exists($content, 'preSave')){
			$this->input = $content->preSave($this->input, $this->view); 
		}
		
		//pr($this->input);return ;
		
		$err = $this->checkFormNewPage();
		$this->view->node->title = $this->input->pageTitle; 
		if(!$err){
			$err = $this->checkSEOForm();	
		}
		
		if(!$err){ // ok	
			
			//content
			$content = Content::init($ctype, $this->input, $this->acl);	
			
			if(method_exists($content, 'beforeSave')){
				$err = $content->beforeSave( $this->view); 
				if($err){ 
					return array(0, $err);
		    		return ; 
				}
			} 
			
			
			$err2 = $content->save();
				$parentId = 3801;	 	 
			//node				
			//$parentId = $this->request->getParam('parentId');						
			$n = Node::init('ITEM', $parentId, $this->input, $this->view);
		
			// pr($n); die(); 
			
			//save		
	    	$this->tree->addNode($n, false , false);
	    	$this->tree->pareNodeAndContent($n->nodeId, $content->id, $content->_name);
			$this->view->mVariant->saveVariats($this->input,$content->id);
			
		//	$this->view->mProducts->savePositionCategories($content->id,$this->input->parent);
			if(method_exists($content, 'afterNodeSave')){
				$content->afterNodeSave( $this->view, $n); 
			}
	    	parent::audit($n->title, $n->nodeId);
	    	
	    	$calledFrom = $this->request->getParam('calledFrom');	
	    	
	    	if($this->input->rating>0)
	    	{
	    		$this->view->mReviews->saveSingleReview($this->input,$n->nodeId);
	    	}
	    	$this->view->mReviews->saveRating($n->nodeId);
	    	if($calledFrom == 'eshop'){
	    		return array(1, 'Data uložena', 0, $this->view->url(array('module' => 'cms', 'controller' => 'eshop','action' => 'listItems', 'node' => $this->nodeId), null,true));
	    		return ;
	    	}
	    	   
			//return array(1, 'Data uložena', 0, $this->view->url(array('module' => 'cms', 'controller' => 'pages','action' => 'list', 'node' => $this->nodeId), null,true));
			return array(1, 'Data uložena');
		} else {
			return array(0,$err);
		}				
	}
	
	public function saveAction_o()
	{		
		
		//pr($this->input); 
		$err = $this->checkForm();
		if(!$err){ // ok
			if($this->input->event_id){
				$this->moduleEvents->updateEvent(
					$this->input->event_id,
					$this->input->event_title, 
					$this->input->event_detail, 
					$this->input->event_startAt, 
					$this->input->event_within, 
					$this->input->event_type,
					$this->input->photos
				);				
				parent::audit($this->input->event_title, '', 'edit');
			} else {
				$this->moduleEvents->addEvent(
					0,
					$this->input->event_title, 
					$this->input->event_detail, 
					$this->input->event_startAt, 
					$this->input->event_within, 
					$this->input->event_type,
					$this->input->photos
				);
				parent::audit($this->input->event_title);
			}
			
						
			parent::addModalInfo(1,'Data uložena');
		} else {
			parent::addModalInfo(0,$err);
		}
	}
	
	public function deleteAction()
	{			
		$eId = $this->request->getParam('e');
		
		$e = $this->moduleEvents->getEvent($id);		
		parent::audit($e->title);
		
		$this->moduleEvents->deleteEvent($eId);
		$this->listAction();
	}
	 
	
	// NEW ---------------- 
	
	function setViewNode(){
		if(!$this->view->isEdit){
			$this->view->node = new Node();
		}
		if(!$this->view->content){
			$this->view->content = new content_Product(); 
		}	
		$this->view->content->showAdmin($this); 
	}
	
	private function renderReviews($action)
	{
		$this->view->disableForm = true;
		$getItems = $this->request->getParam('getItems');
		$isAjax = $this->request->getParam('ajax');
		$nodeId = $this->request->getParam('node');
		$nodeId = $nodeId?$nodeId:1;
		$this->view->sortUrl = '/cms/eshop/sortDelivery';
		$this->view->defaultSort = 'date';
		$params = array();
		$dg = new DataGrid('pagesTable');
		$dg->setRefresUrl($this->view->url(array('action' => $action, 'getItems' => 1, 'ajax' => 1)))
		->setTitle('Výpis hodnocení produktu')
		->setHeight(400)
		->setItemsPerPageOptions('10, 15, 20, 30, 50, 500', 50);
		$dg->setHeaders(
				array(
						array('Datum', 'date', 130, 'true', 'left', 'false'),
						array('Jméno', 'name', 130, 'true', 'left', 'false'),
						array('Pro', 'pros', 130, 'true', 'left', 'false'),
						array('Proti', 'cons', 130, 'true', 'left', 'false'),
						array('Celkové', 'summary', 130, 'true', 'left', 'false'),
						array('Celkem', 'rating',  40, 'true', 'left', 'false'),
						array('Heureka', 'id_heureka',  40, 'true', 'left', 'false'),
				)
		)->setSearchableColls(
				array(
						array('Název', 'nazev', 'true')
				)
		)->setButtons(
				array(
						array('Smazat označené', 'delete', 'onpress', 'deletep')
				));
		if($this->curentNode != 3801)
		{
			$params['nodeId'] = $this->curentNode;
		}
		if($getItems){
		
			//$products->inAdmin = true;
			
			list($currentPage, $rp, $sortname, $sortorder, $query, $qtype, $start) = $dg->getParams('date', 'DESC');		
			$reviews = $this->view->mReviews->getReviewsProduct($sortname, $sortorder, $start, $rp, $params);
			$rowsTotalCount = $this->view->mReviews->getReviewsProductCount($sortname, $sortorder, $start, $rp, $params,true);
		
			$active = $this->view->tableFilter0 == 'active' ? 1 : 0;
			$domain = $this->view->tableFilter1 == 'all' ? 0 : $this->view->tableFilter1;
			$nOrders = array();
			$cp = new content_Product();
			$rowsFormated = array();
		
			foreach ($reviews as $del){
		
				$editUrl = $this->view->url(array('controller' => 'eshop','action' => 'delivery', 'id'=> $del['id']));
				$nOrder = array();
				$isHeureka = $del['id_heureka'] > 0 ? 'Ano' : 'Ne';
				$nOrder['date'] = '<input name="chbx[' . $del['id'] . ']" type="checkbox" /> &nbsp;' . Utils::formatDate($del['date']);
				$nOrder['pros'] = $del['pros'];
				$nOrder['cons'] = $del['cons'];
				$nOrder['summary'] = $del['summary'];
				$nOrder['name'] = $del['name'];
				$nOrder['rating'] = $del['rating'];
				$nOrder['id_heureka'] =$isHeureka;
				$entry = array(
						'id'=>$del['id'],
						'cell' => $nOrder
				);
				$rowsFormated[] = $entry;
			}
			if($isAjax){
				$dg->renderAjax($currentPage, $rowsTotalCount, $rowsFormated);
				die();
			};
		}
		
		$this->view->hasDD = true;
		return $dg->render($this->view, 'controls/admin/lists/PagesStandard.phtml');
	}
	
	
	public function additemAction()
	{  	    
		// mapa contenProperties na cesky 

		$this->curentNode = $this->view->curentNode = 3801;
		$this->view->isEdit = false;   		
		$this->setViewNode();
		if($this->input->tableaction){
			$this->input->tableaction = 'reviewsProductDelete';
			parent::performMultiAction();
		}
		if(isset($this->input->save))
		{
			$this->view->isEdit = false; 
			$this->view->content  = Content::getNewContent($this->input->contentType);  
			list($state, $message) = $this->saveAction();
			if($state){
				$this->_redirector->goto('listItems', 'eshop', null,array('loadsession' => 1));
			} else {
				$this->addErrorInstantMessage($message );
			}   
						
		 };  
			
		 $this->view->reviewAdd = $this->view->render('controls/admin/lists/ReviewForm.phtml');
		//$this->view->pagesReviews = $this->renderReviews('additem');
			
		$this->view->pageContent = $this->view->render('controls/admin/modules/Eshop/PageDetail.phtml');
//		pr($this->view->pagesReviews)

		parent::indexAction($this->template); 
	}
	
	public function editproductAction()  
	{  	   
		// mapa contenProperties na cesky 
		
		$node = $this->request->getParam('id');
		$this->node  = $this->view->node = $this->tree->getNodeById($node);
		$this->view->content  = $this->node->getTheRightContent();
		if(isset($this->input->save)  && $this->input->tableaction !='delete')
		{
			$this->view->isEdit = true;
			list($state, $message) = $this->updateAction();
			if($state){
				$this->_redirector->goto('listItems', 'eshop', null,array('loadsession' => 1));
			} else {
				$this->addErrorInstantMessage($message );
			}
		
		};
		if($this->input->tableaction =='delete')
		{
			$this->input->tableaction  = 'reviewsProductsDelete';
			parent::performMultiAction();
		}
		$this->view->contentType = $this->view->content->_name; 
		$this->curentNode = $this->view->curentNode = $this->node->nodeId;   
		$this->view->parentNodeId = $this->node->parentId;       
		$this->view->isEdit = true;		
		$this->setViewNode();	     		
		$this->view->pagesReviews = $this->renderReviews('editproduct');
		$this->view->reviewAdd = $this->view->render('controls/admin/lists/ReviewForm.phtml');
		$this->view->pageContent = $this->view->render('controls/admin/modules/Eshop/PageDetail.phtml');    
		parent::indexAction($this->template); 
	} 
	 
	public function itemDeleteAction($id)	{	
		$node = $id; 		
		if($node){
			$n = $this->tree->getNodeById($node);
			$contentId = $n->getTheRightContent()->id;
			$this->mVariant->deleteVariantsByContentId($contentId);    
			parent::audit($n->title, $node,  'delete');
			    
			$this->tree->removeNode($node, false);
			 
		}  
		//$this->listAction();		
	}
	
	public function performMultiaction($action, $id){

		if($action == 'reviewsDelete')
		{ 
			$this->view->mReviews->deleteReviewById($id);
			return; 
		}
		
		if($action == 'reviewsProductsDelete')
		{
			$this->view->mReviews->deleteReviewProductById($id);
			return;
		}
		
		if($action == 'deliveryDelete')
		{
			$m_d = new module_Delivery();
			$m_d->deleteById($id);
			return;
		}
		
		if($action == 'paymentDelete')
		{ 
			$m_d = new module_Payment();
			$m_d->deleteById($id);
			return;  
		}
		
		if($action == 'priplatkyDelete')
		{
			$m_p = new module_Priplatky();
			$m_p->deleteById($id);
		}
		
		if($action == 'itemDelete'){			
			$this->itemDeleteAction($id); 
			
		} elseif($action == 'published'){	
				$n = $this->tree->getNodeById($id);
			$c = $n->getTheRightContent();	
			$n->publishContent($c->id);
			$content = $n->getTheRightContent();
			$content->setDateArchived('PUBLISHED');
			parent::audit($n->title, $id,  'publishContent');
		} elseif($action == 'archivated'){			
			$n = $this->tree->getNodeById($id);
			$c = $n->archivePublishedContent();	 
			$content = $n->getTheRightContent();
			$content->setDateArchived('ARCHIVED');
			parent::audit($n->title, $id,  'archiveContent');
		} elseif($action == 'voucherDelete'){
			$vm = new module_Voucher();
			$vm->deleteById($id);
		} else {
			
			$order = $this->mEshop->orders->getOrder($id); 
			$faktura = $this->mFakturace->getFakturaByOid($order['id']); 
			$details = $this->getOrderDetailsSimple($order); 
			
			if($action == 'orderSent'){
				if($order['delivery'] == 'OO' && $order['payment'] == 1){
					//$action = 'orderReady';     
				}  
			}
			
			$state = ''; 
			switch ($action){	
				case 'orderDelete':
					$this->mEshop->itemUnSold($order);
					$order = $this->mEshop->orders->getOrder($id);
					$state = 'DEL';
					$this->changeOrderStatus($order, 'DELETED');
				break;
				case 'orderAdjusted':
					$state = 'CHANGE';
					$this->changeOrderStatus($order, $state);
				break;
				case 'orderTexted':
					$state = 'TEXTED';
				$this->changeOrderStatus($order, $state);
					break;
				case 'orderAdjustedEmail':
					$state = 'CHANGE';
					$this->changeOrderStatus($order, $state);
					$this->mEshop->orders->sendEmail('orderAdjustedEmail',$order,$this->view);
					break;
				case 'orderPending':
					$state = 'WAIT';
					$this->changeOrderStatus($order, $state);
					break;
				case 'orderWaitPay':
					$state = 'WPAY';
					$this->changeOrderStatus($order, $state);
				break;
				case 'orderWaitPayEmail':
					$state = 'WPAY';
					$this->changeOrderStatus($order, $state);
					$this->mEshop->orders->sendEmail('orderWaitPayEmail',$order,$this->view);
				break;
				case 'orderRecievedPayment': // balik, prevod     
					// stats a prodano
					$this->mEshop->itemSold($order); 
					
					$this->mEshop->orders->setValue($order['id'], 'paid', 1);
					$m_p = new module_Payment();
					$payment = $m_p->getPaymentById($order['payment']);
				
					
					break;  
				case 'orderRecievedPaymentEmail': // balik, prevod
						// stats a prodano
						$this->mEshop->itemSold($order);
							
						$this->mEshop->orders->setValue($order['id'], 'paid', 1);
						$this->mEshop->orders->sendEmail('orderRecievedPaymentEmail',$order,$this->view);						
							
				break;
				case 'orderSent':
					$state = 'SEND';
					$this->changeOrderStatus($order, $state);
					$this->mEshop->orders->setValue($order['id'], 'sent', 1);
					
					$m_p = new module_Payment();
					$payment = $m_p->getPaymentById($order['payment']);
				/*	if($mail){ 
						$mailNode = $this->tree->getNodeById($mnid);
						if($mailNode){ 
							$txt = $mailNode->getHtml();
							if(strpos($mailNode->title, '---')){
								list($subject, $spec) = explode('---', $mailNode->title);
							} else {
								$subject = $mailNode->title;
							}
							
							$mail = str_replace('[zbozi]', $details, $txt); 
							$subject = str_replace('[CisloObjednavky]', $order['id'] , $subject);  
							$mail = str_replace('[CisloObjednavky]', $order['id'], $mail); 
						
							$this->sendNotification($order['firstname'] . ' ' . $order['surname'], $order['email'], trim($subject), $mail);					
						}
					}*/
					 
					break;  
					case 'orderSentEmail':
						$state = 'SEND';
						
						$this->changeOrderStatus($order, $state);
						
						$this->mEshop->orders->setValue($order['id'], 'sent', 1);
						$this->mEshop->orders->sendEmail('orderSentEmail',$order,$this->view);
						$m_p = new module_Payment();
						$payment = $m_p->getPaymentById($order['payment']);
						/*	if($mail){
						 $mailNode = $this->tree->getNodeById($mnid);
						if($mailNode){
						$txt = $mailNode->getHtml();
						if(strpos($mailNode->title, '---')){
						list($subject, $spec) = explode('---', $mailNode->title);
						} else {
						$subject = $mailNode->title;
						}
							
						$mail = str_replace('[zbozi]', $details, $txt);
						$subject = str_replace('[CisloObjednavky]', $order['id'] , $subject);
						$mail = str_replace('[CisloObjednavky]', $order['id'], $mail);
					
						$this->sendNotification($order['firstname'] . ' ' . $order['surname'], $order['email'], trim($subject), $mail);
						}
						}*/
					
				break;
					
				case 'orderStorno':   
					$this->mEshop->itemUnSold($order); 
					$order = $this->mEshop->orders->getOrder($id); 
					$faktura = $this->mFakturace->getFakturaByOid($order['id']);
					$details = $this->getOrderDetailsSimple($order);		 
					$vm = new module_Voucher();
					$vm->resetVoucher($faktura->voucher['id_v']);
					$state = 'CANCEL';
					$this->changeOrderStatus($order, 'CANCEL');		 
				break;
				case 'orderStornoEmail':
					$this->mEshop->itemUnSold($order);
					$order = $this->mEshop->orders->getOrder($id);
					$faktura = $this->mFakturace->getFakturaByOid($order['id']);
					$details = $this->getOrderDetailsSimple($order);
					$vm = new module_Voucher();
					$vm->resetVoucher($faktura->voucher['id_v']);
					$state = 'CANCEL';
					$this->changeOrderStatus($order, 'CANCEL');
					$this->mEshop->orders->sendEmail('orderStornoEmail',$order,$this->view);
				break;
 
				case 'orderNone':	 
					$state = 'RESET';  
					$this->mEshop->itemUnSold($order);   
					$this->changeOrderStatus($order, '');	
					$this->mEshop->orders->setValue($order['id'], 'sent', 0);
					$this->mEshop->orders->setValue($order['id'], 'paid', 0);
					break;
								
			}     
			  
			$this->mEshop->orders->logAction($order['id'], $action, $state, $this->view->user->username);
		}  
	}
	
	public function edituserAction() 
	{
		
		$oId = $this->request->getParam('id'); 
		if(isset($this->input->back))
		{
			$this->view->customer = $this->input;
			$oldCustomer = new module_Customer(0, 0, $oId);
			
			$mCust = new module_Customers();
			$state = $mCust->updateUserAdmin($oId,$this->view->customer,$oldCustomer );
			if($state){
				$this->addErrorInstantMessage($state );
			}
			else{
				$this->_redirector->goto('listcustomers', 'eshop', null,array('loadsession' => 1));
			}
			//$this->_redirector->goto('listcustomers', 'eshop', null,array());
		}
		else{
			$this->view->customer = new module_Customer(0, 0, $oId);
			
		}
		
		
		//$details = $this->mEshop->orders->getOrderDetails($oId);
		//pr($order);  
		//pr($details); 
		 
		$this->view->basicForm =  $this->view->render('controls/admin/modules/Eshop/CustomerDetails.phtml');
		parent::indexAction($this->template);
		  
	}
	
	function generateXLS($headers, $excelData, $title){   
		$excel = new module_Excel();
		$excel->setData($excelData, 0, $headers);	  
		$excel->writeToFile2003($this->view->config->dataRoot . "/test/xx.xls", true, $title . '.xls', $this->view->config->dataRoot . "/test");     
		die();    
	} 
	
	public function statsAction() 
	{  		
		$getItems = $this->request->getParam('getItems');
		$isAjax = $this->request->getParam('ajax');
		$this->view->hasExport = true; 
		$this->view->tableActions = array(  
		);  
			 
		$this->view->curentViewState['action'] = 'stats';					 
		$this->view->tableSort = $this->request->getParam('sort')?$this->request->getParam('sort'):'created';			 
		$this->view->tableSortType = $this->request->getParam('sortType')?$this->request->getParam('sortType'):'Desc' ; 
		$this->view->tableFilter0 = $this->request->getParam('tableFilter0')?$this->request->getParam('tableFilter0'):'all'; 
		$this->view->tableFilter1 = $this->request->getParam('tableFilter1')?$this->request->getParam('tableFilter1'):'all';
		$this->view->tableFilter2 = $this->request->getParam('tableFilter2')?$this->request->getParam('tableFilter2'):'all';
		$this->view->tableFilter3 = $this->request->getParam('tableFilter3')?$this->request->getParam('tableFilter3'):'all';
		
		
		$dg = new DataGrid('statsTable');
		$dg->setRefresUrl($this->view->url(array('action' => 'stats', 'getItems' => 1, 'ajax' => 1)))
		->setTitle('Výpis nakupujících')
		->setHeight(400)
		->setItemsPerPageOptions('10, 15, 20, 30, 50, 500', 50)	;
			

			
		$dg->setHeaders( 
			array( 
				array('Název produktu', 'title', 90, 'true', 'left', 'false'),
				array('Cena', 'price', 90, 'true', 'left', 'false'), 
				array('Značka', 'znacka', 100, 'true', 'left', 'false'),
				array('ID', 'pid', 80, 'true', 'left', 'false'),   
				array('Obrat', 'sum',  30, 'true', 'left', 'false'),
				array('Prodáno Ks', 'pc',  30, 'true', 'left', 'false')
			)
		)->setSearchableColls(   
			array(    
				array('Název', 'title', 'true')
			)
		)->setButtons( 
			array(  
			)
		);

		$cats = $this->view->tree->getParentSelect(3801, '', false);
		unset($cats['3801']);
		$this->view->category = helper_Input::addNotDefinedOption($cats, '- Všechny kategorie -', '');   
			
		//$vyrobci = helper_Input::addNotDefinedOption($this->view->tree->getNodesAsSelect(3817, 'FOLDER'), '- Všichni výrobci -', 'all');
		
				
		$this->view->mount = helper_Input::addNotDefinedOption(helper_Input::getMonthsSelecDataLeden(), '- měsíc (všechny) -', 'all');
		$this->view->year =  helper_Input::addNotDefinedOption(helper_Input::getDateBornSelectData(0, 3), '- rok / (všechny) -', 'all');
		
		
		if($getItems){
		
			list($currentPage, $rp, $sortname, $sortorder, $query, $qtype, $start) = $dg->getParams('title', 'asc');
			$sortname = $sortname ? $sortname : 'prijmeni';		   
			$cats = $this->view->tree->getParentSelect(3801, '&ensp;', false);      
		
		 
        $orders = array();    
		// pr($orders);  
		$products = new module_Products();    
		$products->inAdmin = true;   
		$params = array();
 
		if($this->input->category){
			$params['category'] = $this->input->category;
		}
		
		
		$this->view->showSearch = true;  
		$search = $this->input->search;  
		if($search){
			$params['search'] = $search;
		} 
		
		$products = $products->getProducts($this->view->tableSort, $this->view->tableSortType, 0, 5000, $params);
		$ids = array();
		$productsAll = array();
		foreach ($products as $p){
			$ids[] = $p['id'];
			$productsAll[$p['id']] = $p;
		}
		  
		$params2 = array();
 
	 

		if($this->input->mount != 'all'){ 
			$params2['month'] = $this->input->mount;
			$params2['year'] = $this->input->year?$this->input->year:date('Y');
		} 
		if($this->input->year != 'all'){ 
			$params2['year'] = $this->input->year?$this->input->year:date('Y');
		}   
		
		$prodeje = $this->mEshop->getProdejeByIds($ids, $params2);
		$prodano = $sum = $sumAll = 0;
		foreach ($prodeje as $p){
			$prod = $productsAll[$p['pid']];
			$prod['sum'] = $p['s'];
			$prod['pc'] = $p['c']; 
			$orders[] = $prod;
			$sumAll += $p['c'] * $p['s'];
			$prodano+=$p['c'];
			$sum+=$p['s'];
		} 
		 
		$this->view->obrat = $sum;  
		$this->view->prodano = $prodano;
		
		$this->view->tableDefaultListing = 20;   
		 
		$active = $this->view->tableFilter0 == 'active' ? 1 : 0;
		$domain = $this->view->tableFilter1 == 'all' ? 0 : $this->view->tableFilter1;
		//$znacky = $this->view->tree->getNodesAsSelect(3817, 'FOLDER');			
		$nOrders = array();
		$cp = new content_Product();

		$products = new module_Products();
		$znacky = $products->getZnacky(false,true);  
		foreach($znacky as $ident => $znacka) {
			$znackyCiselnik[$ident] = $znacka;
		} 

		foreach ($orders as $order){   
			//   pr($order); die();  

			$order['path'] =  $cp->getDetailUrl2($this->view, $order['parent'], $order['id'], $order['title']);
			$nOrder = array(); 
			$nOrder['title'] = $order['title'];  
			//$nOrder['path'] = Utils::getFrontEndLink( $order['path'], false, '', false, 0, $this->view);
			$nOrder['price'] = $order['price']; 
			$nOrder['znacka'] =  $znackyCiselnik[$order['znacka']] ; 
			$nOrder['pid'] = $order['id'];     
			$nOrder['sum'] = $order['sum'];    
			$nOrder['pc'] = $order['pc'];    
			$nOrders[] = $nOrder;   
			$entry = array(
					'id'=> $order['id'],
					'cell' => $nOrder
			);
			$rowsFormated[] = $entry;
		} 
		
		
		$this->view->ordersTable = $nOrders;  
		
		
		$export = $this->request->getParam('tableExport'); 
		if($export){
			
			$headers = array(
				'Název produktu', 'Cena', 'Značka', 'ID', 'Foto', 'Skladem', 'Obrat', 'Prodáno ks'
			); 
			$data = array();
			foreach ($nOrders as $o){
				unset($o['path']);
				$data[] = $o;
			}
			$title = 'prehled'; 
			$this->generateXLS($headers, $data, $title);
			die(); 
		}
		
		//[payment] => 1 [delivery] => czDobirka 
				 
		$rowsTotalCount = count($rowsFormated); 
		if($isAjax){ 
			$dg->renderAjax($currentPage, $rowsTotalCount, $rowsFormated);
			die();
		}; 
		
		} 

		$this->view->renderFilter = 'controls/admin/lists/filter/Stats.phtml'; 
    	$this->view->pagesList = $dg->render($this->view, 'controls/admin/lists/PagesStandard.phtml');  
    	parent::indexAction($this->template);  
		  
	}
	
	
	public function listvouchersAction(){
		parent::performMultiAction();	
		$getItems = $this->request->getParam('getItems');
		$isAjax = $this->request->getParam('ajax');
		$this->view->status = array('all' => 'status (vše)' , '1' => 'platný (zelený)','2'=>'použitý (červený)','3'=>'propadlý (fialový)');
		$this->view->type = array('all' => 'typ (vše)' , '1' => 'Procentní', '2' => 'Peněžní', '3' => 'Produktový (%)', '4' => 'Produktový (Kč)');
		$params = array();
		$dg = new DataGrid('vouchersTable');
		
		
		$this->view->hasExport = true;
// 		$this->view->tableActions = array(
// 				'voucherDelete' => 'Smazat'
// 		);
		
		$this->view->curentViewState['action'] = 'listVouchers';
		$this->view->tableSort = $this->request->getParam('sort')?$this->request->getParam('sort'):'created';
		$this->view->tableSortType = $this->request->getParam('sortType')?$this->request->getParam('sortType'):'Desc' ;
		$this->view->tableFilter0 = $this->input->status ? $this->input->status:'all';
		$this->view->tableFilter1 = $this->input->type ? $this->input->type:'all';	
		$dg->setRefresUrl($this->view->url(array('action' => 'listvouchers', 'getItems' => 1, 'ajax' => 1)))
		->setTitle('Výpis Voucherů') 
		->setHeight(400)
		->setItemsPerPageOptions('10, 15, 20, 30, 50, 500', 50)
		;			
		$dg->setHeaders(
				array(
		
						array('Status', 'status', 100, 'true', 'left', 'false'),
						array('Název', 'title', 150, 'true', 'left', 'false'),
						array('Kód', 'code', 150, 'true', 'left', 'false'),
						array('Hodnota', 'value', 100, 'true', 'left', 'false'),
						array('Vytvořena', 'created', 100, 'true', 'left', 'false'),
						array('Id objednávky', 'id_order', 80, 'true', 'left', 'false'),
						array('Počet použití', 'numberOfUses', 50, 'true', 'left', 'false'),
						array('Použito', 'used',  35, 'true', 'left', 'false'),
						array('Produkt', 'productCode',  75, 'true', 'left', 'false'),
						array('Platí pro', 'znacky',  40, 'true', 'left', 'false')
				)
		)->setSearchableColls(
				array(
						array('Status', 'status', 'true'),
						array('Název', 'title', 'true') ,
						array('Kód', 'code', 'true'),
						array('Hodnota', 'value', 'true'),
						array('Id objednávky', 'id_order', 'true'),
						array('Hodnota', 'created', 'true'),
						array('Počet použití', 'numberOfUses', 'true'),
						array('Použito', 'used', 'true'),
						array('Produkt', 'productCode', 'true'),
						
						
				)
		)->setButtons(
			array(  
				array('Smazat označené', 'delete', 'onpress', 'deletev')  
			));  
		
		if($getItems){
			list($currentPage, $rp, $sortname, $sortorder, $query, $qtype, $start) = $dg->getParams('created', 'asc');
			$this->view->showSearch = true;
			$params = array();
			$search = $this->input->search;
			if($search){
				$params['search'] = $search;
			}
		if($this->view->tableFilter0 != 'all'){
			$params['status'] = $this->view->tableFilter0;
		}
		if($this->view->tableFilter1 != 'all'){
			$params['type'] = $this->view->tableFilter1;
		}
		
	
	
		$mc = new module_Voucher();
		$vouchers =    $mc->getVouchers($sortname, $sortorder, $start, $rp, $params);

		$params['count'] = 1; 
		$rowsTotalCount = $mc->getVouchersAdminCount($sortname, $sortorder, 0, 9000, $params); 
		$rowsTotalCount = $rowsTotalCount?$rowsTotalCount:0; 
		// pr($orders);
	
		//$znackyNode = $this->tree->getNodeById(51161); 
		//$znacky = $znackyNode->getChildren('FOLDER');
		$products = new module_Products();
		$znacky = $products->getZnacky(false,true);  
		foreach($znacky as $ident => $znacka)  
			$znackyCiselnik[$ident] = $znacka;
	
	
		$active = $this->view->tableFilter0 == 'active' ? 1 : 0;
		$domain = $this->view->tableFilter1 == 'all' ? 0 : $this->view->tableFilter1;
		// pr($orders); //  		 pr($this->mEshop->basket->deliveryOptions);
		
	
		$nOrders = array();
		foreach ($vouchers as $voucher){
			$nOrder = array();
			$exportable = array();
			$nOrder['id_v'] = $voucher['id_v'];
			if(($voucher['status']==1) AND (strtotime($voucher['valid']) < (time() - (60*60*24) ))){
				$voucher['status'] = 3;
			}
			switch($voucher['status']){
				case 1:
					$color = "green";
					break;
				case 2:
					$color = "red";
					break;
				case 3:
					$color = "purple";
					break;
			}
			$nOrder['status'] = '<input name="chbx[' . $voucher['id_v'] . ']" type="checkbox" /> '.$mc->status[$voucher['status']].'<div style="background: '.$color.'; width:1em; height:1em;float:left;margin: 2px 5px 0 0;"></div>';
			$exportable['status'] = $mc->status[$voucher['status']];
			$exportable['title'] = $nOrder['title'] = $voucher['title'];
			$exportable['code'] = $nOrder['code'] = $voucher['code'];
			//$nOrder['status'] = $mc->status[$voucher['status']];
				
			//$nOrder['type'] = $mc->type[$voucher['type']];
			$exportable['value'] = $nOrder['value'] = $voucher['value'];
			$date = new DateTime($voucher['valid']);
			$date2 = new DateTime($voucher['created']);
			$nOrder['created']= $date2->format('j.n.Y');
			$exportable['valid'] = $nOrder['valid']= $date->format('j.n.Y');
				
			if( ($voucher['type']==1)or($voucher['type']==3) ){
				$nOrder['value'] .= '%';
				$exportable['value'] .= '%';
			}
			else{
				$nOrder['value'] .= ' Kč';
				$exportable['value'] .= 'Kč';
			}
				
			$orders = $mc->getOrders($voucher['id_v']);
			if(count($orders))
			{
				foreach($orders as $order)
				{
					$order = $order['id_o'];
					$editUrl = $this->view->url(array('controller' => 'eshop','action' => 'editorder', 'id'=> $order));
						
					$nOrder['id_order'] .= '<a title="Detail '.$order.'" class="nocio" href="'.$editUrl.'" >'.$order.'</a> ';
				}
			}
			elseif($voucher['id_order'] > 0){
				$editUrl = $this->view->url(array('controller' => 'eshop','action' => 'editorder', 'id'=> $voucher['id_order']));
				
				$nOrder['id_order'] = '<a title="Detail '.$voucher['id_order'].'" class="nocio" href="'.$editUrl.'" >'.$voucher['id_order'].'</a>';
			}
			else
				$nOrder['id_order'] = '';
			//<a title="Detail 743" class="nocio" href="#" onclick="return showModal('Detail - 743','/cms/eshop/editOrder/amodule/orders/id/743');">743</a>
			$nOrder['numberOfUses'] = $voucher['numberOfUses'];
			$nOrder['used'] = $voucher['used'];
				
			if($voucher['productCode'])
			{
			//$nOrder['productCode'] = '<a title="Detail '.$voucher['productCode'].'" class="nocio" href="#" onclick="return showModal(\'Detail - '.$voucher['productCode'].'\',\'/cms/eshop/editProduct/amodule/items/id/'.$mc->getProductNodeIdByCode($voucher['productCode']).'\');">'.$voucher['productCode'].'</a>';
				$nOrder['productCode'] = $voucher['productCode'];
			}
				else
					$nOrder['productCode'] = '';
	
					if($voucher['znacky'] AND !$voucher['productCode'])
			{
							$idZnacky = explode(',',$voucher['znacky']);
				$znNazvy = array();
					foreach($idZnacky as $id)
					$znNazvy[] = $znackyCiselnik[$id];
						
					$nOrder['znacky'] = Utils::getHelpIco(implode(', ', $znNazvy));
				}
							else
								$nOrder['znacky'] ='';
								$nOrders[] = $nOrder;
								$exportables[] = $exportable;
			$entry = array(
				'id'=> $voucher['id_v'],
				'cell' => $nOrder);
			$rowsFormated[] = $entry;
		}
		
	
		$export = $this->request->getParam('tableExport');
		if($export){
			
		$headers = array(
			'Status','Název', 'Kód', 'Hodnota', 'Platnost do' /*'Skladem', 'Obrat', 'Prodáno ks'*/
			);
			$data = array();
			foreach ($exportables as $o){
			$data[] = $o;
		}
			
		$title = 'vouchery';
			$this->generateXLS($headers, $data, $title);
			die();
		}	
			
			if($isAjax){
				$dg->renderAjax($currentPage, $rowsTotalCount, $rowsFormated);
				die();
			};
		}
		$this->view->renderFilter = 'controls/admin/lists/filter/ListVouchers.phtml';
		$this->view->showButton = new stdClass();
		$this->view->showButton->link = $this->view->url(array('action' => 'vouchersgen'));
		$this->view->showButton->title = 'Vygenerovaní poukazu';
		$this->view->pagesList = $dg->render($this->view, 'controls/admin/lists/PagesStandard.phtml');
		parent::indexAction($this->template);
	}
	
	
	
	public function vouchersgenAction(){
    	//$this->view->showForm = true;
		$this->view->input = $this->input;
		if(isset($this->input->save))
		{
			$this->voucherscreate();
		}
    	$this->view->basicForm = $this->view->render('controls/admin/modules/Eshop/Vouchers.phtml');
		parent::indexAction($this->template);				
		
	} 
	private function renderTreeXML($node, $nameTag = "name")
	{
		foreach($node as $item){
			echo "<item id=\"{$item['id']}\">";
			echo	"<content><name><![CDATA[{$item[$nameTag]}]]></name></content>";
			$this->renderTreeXML($item['children'], $nameTag);
			echo "</item>";
		}
	}
/*
	private function renderTreeXML2($node, $nameTag = "name")
	{
		foreach($node as $item){
			echo "<item id=\"{$item['id']}\">";
			echo	"<content><name><![CDATA[{$item[$nameTag]}]]></name></content>";
			$this->renderTreeXML2($item['children'], $nameTag);
			echo "</item>";
		}
	}*/


	public function googletreexmlAction(){
		$tree = new module_GoogleTree();
		$data = $tree->getSubTrees( array(1, 594) );
		header ("Content-Type:text/xml");

		echo "<root>";
		$this->renderTreeXML($data);
		echo "</root>";
	}

	public function zbozitreexmlAction(){
		// 6430 id of root node for zbozicz (table Nodes)
		$zboziczTree = $this->view->tree->getTree(6430, $deep = true);

		header ("Content-Type:text/xml");
		echo "<root>";
		$this->renderTreeXML(array($zboziczTree), 'title');
		echo "</root>";
		die;
	}

	public function heurekatreexmlAction(){
		$tree = new module_HeurekaTree();
		$data = $tree->getSubTrees( array(971, 1513) );
		header ("Content-Type:text/xml");

		echo "<root>";
		$this->renderTreeXML($data);
		echo "</root>";
		die;
	}


	


		public function checkVouchers(){
			if(!$this->input->title){
				$err = "Zadejte název";			   
			    return $err;
				}
			if(!$this->input->value){
				$err = "Zadejte hodnotu voucheru";			   
			    return $err;
				}
			if(!$this->input->quantity){
				$err = "Zadejte počet voucherů";			   
			    return $err;
				}
			if(!$this->input->numberOfUses){
				$err = "Zadejte počet použití";			   
			    return $err;
				}
			if(!is_numeric($this->input->value)){
				$err = "Hodnota voucheru musí být celé číslo";			   
			    return $err;
			}
			if($this->input->value <1){
				$err = "Hodnota voucheru musí být alespoň 1";			   
			    return $err;
			}
			if(!is_numeric($this->input->numberOfUses)){
				$err = "Počet použití musí být celé číslo";			   
			    return $err;
			}	
			if($this->input->numberOfUses < 1){
				$err = "Počet použití musí být alespoň 1";			   
			    return $err;
				}
			if($this->input->quantity <1){
				$err = "Počet voucheru musí být alespoň 1";			   
			    return $err;
			}
			if(!is_numeric($this->input->quantity)){
				$err = "Počet voucherů musí být celé číslo";			   
			    return $err;
			}	
			if(!$this->input->date){
				$err = "Zadejte datum platnosti";			   
			    return $err;
			}
			if(strtotime($this->input->date) < strtotime(date('Y-m-d'))){
				$err = "Datum musí být v budoucnosti";			   
			    return $err;
			}
			if( ($this->input->type == 1) OR ($this->input->type == 3) ){
				if(($this->input->value < 1) or ($this->input->value > 100)){ // procenta
				$err = "Hodnota musí být v procentech (1 - 100)";			   
			    return $err;
				}
			}
			if( ($this->input->type == 3) OR ($this->input->type == 4) ){
				/*if(!$this->input->productCode){
				$err = "Zadejte kód produktu na který se sleva vztahuje";			   
			    return $err;
				}
				$mv = new module_Voucher();  
				if( !$mv->isProductCodeValid($this->input->productCode)){
					$err = "Kód produktu není platný";			   
					return $err;
				}*/  
			}
			
			
		
	}
	
	public function vouchersCreate(){
		 $err = $this->checkVouchers(); 
		 //print_r($this->input->znacky);
		if(!$err){ // ok	  
					
			$userData = $this->input; 	  
			
			if($this->input->type < 3)
				$znacky = $this->input->znacky;
			else
				$znacky = null;
			
			
			if($this->input->allZnack){
				$znacky = 0;
			}
			
			//pr($this->input);
	
			
			
			if($this->input->type > 2){
				//$pCode = $this->inputGet->productCode;
				$prd = array();
				for($i = 1; $i <= 50; $i++){
					$pn = 'productUrl_' . $i; 
					if($this->input->$pn){ 
						$path = str_replace(Utils::getWebUrl(), '', $this->input->$pn);
						$p = $this->tree->getNodeByPath($path);
						if($p->nodeId){
							$prd[] = $p->nodeId;
						}
					}
				} 
				$pCode = implode(',', $prd);
			} else {
				$pCode = null;
			}   
			
			$mc = new module_Voucher();  
			$data = array(
			'title' => $this->input->title,  
			'value' => $this->input->value,
			'type' => $this->input->type, 
			'valid' => $this->input->date, //plati do (datum)
			'status' => 1,
			
			'category' => $this->input->category,
			'countFrom' => $this->input->countFrom, 
			
			'created' => date('Y-m-d'),
			'numberOfUses' => $this->input->numberOfUses,
			'productCode' => $pCode,
			'znacky' => $znacky
			);
			
			// pr($data); die();  
			
			for($i=0; $i< $this->input->quantity; $i++){
				$mc->addVoucher($data); 
			}
			
				
    		
    		$this->_redirector->goto('listvouchers', 'eshop', null,array());
		} else {
				$this->addErrorInstantMessage($err);
		}	
	}
}
