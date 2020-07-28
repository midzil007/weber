<?

/**
 * Třída reprentuje nakupni kosik
 */
class module_Eshop_Basket {

	public $deliveryOptions = array();
	public $paymentOptions  = array();
	public $privateKey = 'asdas56s463@#q';
	public $delivery;
	public $payment;
	public $voucher;
	public $states = array(
		'cz' => 'Česká republika',
	);
	// module delivery, payment
	public $m_d;
	public $m_p;
	public function __construct() {
		$this->db         = Zend_Registry::getInstance()->db;
		$this->db2        = Zend_Registry::getInstance()->db2;
		$this->tree       = Zend_Registry::getInstance()->tree;
		$this->session    = Zend_Registry::getInstance()->session;
		$this->m_d        = new module_Delivery();
		$this->m_p        = new module_Payment();
		$this->tempBasket = $this->session->basket;
		$this->voucher    = $this->session->voucher;
		if (!is_array($this->tempBasket)) {
			$this->tempBasket = $this->session->basket = array();
		}
		$this->initDeliveryTypes();
		$this->_tableName      = 'module_eshop_orders';
		$this->_tableItemsName = 'module_eshop_order_items';
		$this->_tableTemporary = 'module_eshop_temporaryBasket';
	}

	function saveLastVisitedToCookie($nodeId, $variant) {
		//$_COOKIE['products'] = null;
		$inst = true;

		$cookieProducts = unserialize(base64_decode($_COOKIE['products']));
		foreach ($cookieProducts as $value) {
			if (is_numeric(strpos($value, $nodeId))) {
				$inst = false;
			}
		}
		if ($inst) {
			$count = count($cookieProducts);
			if ($count < 4) {
				end($cookieProducts);// move the internal pointer to the end of the array
				$key = key($cookieProducts);
				unset($cookieProducts[$key]);
				$last           = $cookieProducts;
				$first[]        = $nodeId.'_'.$variant;
				$cookieProducts = array_merge($first, $last);
			} else {
				$cookieProducts[] = $nodeId.'_'.$variant;
			}

			$saveCookie = base64_encode(serialize($cookieProducts));
			$this->setcookielive("products", $saveCookie, time()+36000000, '/', 'svycarskekavovary.cz');
		}
	}

	public function getLastVisitedFromCookie($nodeId) {
		$cookies = unserialize(base64_decode($_COOKIE['products']));
		$count   = count($cookies);
		for ($i = $count; $i >= 0; $i--) {
			$temp = explode('_', $cookies[$i]);
			if ($nodeId != $temp[0] && is_numeric($temp[0])) {
				$nod = $this->tree->getNodeById($temp[0]);
				if (is_object($nod)) {
					$retArray[$temp[0]] = $temp[1];
				}
			}
		}
		return $retArray;
	}

	function drawInfoLine($view) {
		$items = $this->getItems();

		$itemsCount = $this->getItemsCount();
		$xhtml      = '';
		if ($itemsCount >= 1) {
			$sum = $this->getSum($view);
			if ($itemsCount == 1) {
				$t = 'položka';
			} elseif ($itemsCount < 5) {
				$t = 'položky';
			} else {
				$t = 'položek';
			}

			$xhtml .= '<a class="ttt"  id="bpc" href="'.$view->bp.'" rel="nofollow">'."<strong>$itemsCount $t</strong> za <strong>" .helper_FrontEnd::price($sum)."</strong> Kč </a>";

			$items = $this->getItems();
			?>

																			      	<div id="basket">
																			        <ul>
																			          <li id="visiBlock"  <?=($view->inputGet->ajax?'class="active"':'')?>><?=$xhtml?><a href="<?=$view->bp?>" class="basket">&nbsp;</a>
																			            <ul>
																			              <li>
																			                <div class="content">
			<?foreach ($items as $id => $data):
			$child = $data['item'];
			if (!$child) {continue;}
			$c = $child->getPublishedContent();
			$p = helper_FrontEnd::getPhoto('photos', $c, $c->fotoMiniName);
			if (count($p)) {
				$img = $p['path'];
			} else {
				$img = '/images/categoryImg.jpg';
			}
			$price = $c->getPropertyValue('price');
			$url   = $c->getDetailUrl($this, $child);
			?>
																			                  <div class="inner">
																			                    <div>
																			                    	<a class="im" href="<?=$url?>"><img src="<?=$img?>" alt=" " /></a>
																			                        <p>
																			                        	<a class="ttt" href="<?=$url?>"><?=helper_FrontEnd::reduceText($child->title, 40, false)?></a>
																			                        	<span><?=helper_FrontEnd::price($price)?> Kč</span>
																			                        	<a href="#" onclick="return submitBasket(<?=$child->nodeId?>);" class="remove">&nbsp;
																		</a>
																			                        </p>
																			                    </div>
																			                  </div>
			<?endforeach;?>
																			                </div>
																			                <div class="navigacia">
																			                	<a class="ttt" id="visibletrigger" href="#">pokračovat v nákupu</a> &nbsp;
																		&nbsp;
																		&nbsp;

																			                	<a class="ttt" href="<?=$view->bp?>" class="pokl">k pokladně  ›</a>
																			                </div>
																			              </li>
																			            </ul>
																			          </li>
																			        </ul>

																			        	<form action="<?=$view->bp?>" method="post" name="orderForm" id="orderFormBasket">
			<?=$view->formHidden('pid', 0, array('id' => 'bpid'))?>
																		<?=$view->formHidden('action', 'buy')?>
																		<?=$view->formHidden('count', 0, array('id' => 'bpks'))?></form>
																			      </div>
			<?php

		} else {
			?>
																					<div id="basket" class="na">
																				      	<a href="<?=$view->bp?>" class="basket">&nbsp;</a>
																			      	</div>
			<?
		}
	}

	public function setDefaultDelivery() {
		$doprava = $this->deliveryOptions;
		foreach ($doprava as $ident => $data) {
			if ($this->getSum() > $data['od'] && $this->getSum() < $data['do']) {
				$idDoprava = $data['id'];
				//break;
				//pr($data);
			}
		}

		//	e($this->getSum());
		//	die;
		$this->setDelivery($idDoprava);
		return $idDoprava;
	}

	public function setDefaultPayment() {
		$delivery = $this->getDelivery();
		$platby   = $this->getPaymentsById($delivery);

		die();
	}

	public function refreshDeliveries() {
		//print_r( $this->calcWeight() );
		//print_r( $this->calcWeight() );

		$this->deliveryOptions = $this->m_d->getValidDeliveriesByWeight($this->calcWeight());

	}

	public function calcWeight() {
		$sum = 0;
		foreach ($this->tempBasket as $data) {
			$p = $data['item']->getTheRightContent();
			if ($p) {
				$w = $p->getPropertyValue('hmotnost');
				$sum += $data['count']*$w;
			}
		}

		return $sum/1000;//zbozi je v gramech
	}

	function initDeliveryTypes() {

		$this->deliveryOptions = $this->m_d->getValidDeliveries();

		/*$this->deliveryOptions['OO'] = array(
	'title' => 'Osobní odběr - ZDARMA',
	'price' => 0,
	'payments' => array(
	1 => array(
	'Hotově/kartou - ZDARMA <small>Home&Cook OC Palladium</small> ',
	0,
	'Hotově',
	)
	)
	);
	$this->deliveryOptions['PPL'] = array(
	'title' => 'Kurýr Praha',
	'price' => 260,
	'payments' => array(
	1 => array(
	'Dobírkou (260 Kč) <small>(Objednáno do 12:00, doručení 13 – 17 hod)</small>',
	260,
	'Dobírka - 260 Kč',
	)
	)
	);
	$this->deliveryOptions['GP'] = array(
	'title' => 'General Parcel',
	'price' => 140,
	'payments' => array(
	1 => array(
	'Dobírkou (140 Kč)',
	140,
	'Dobírka - 140 Kč'
	),
	2 => array(
	'Bankovním převodem (100 Kč)',
	100,
	'Bankovním převod - 100 Kč'
	),
	3 => array(
	'Platba kartou (100 Kč)',
	100,
	'Platba kartou - 100 Kč'
	)
	)
	);

	$this->deliveryOptions['CP'] = array(
	'title' => 'Obchodní balík - Česká pošta',
	'price' => 140,
	'payments' => array(
	1 => array(
	'Dobírkou (160 Kč)',
	160,
	'Dobírka - 160 Kč',
	),
	2 => array(
	'Bankovním převodem (120 Kč)',
	120,
	'Bankovním převod - 120 Kč',
	),
	3 => array(
	'Platba kartou (120 Kč)',
	120,
	'Platba kartou - 120 Kč'
	)
	)
	);*/
	}

	function setPayment($payment) {
		$this->payment = $this->session->basketPayment = $payment;
		$this->saveCookies(false, false, $payment);
	}

	function setDelivery($delivery) {
		$this->delivery = $this->session->basketDelivery = $delivery;
		$this->saveCookies(false, $delivery);
	}

	function getPaymentDetail() {
		return $this->deliveryOptions[$this->session->basketDelivery]['payments'][$this->session->basketPayment];
	}

	function getPayment($asText = false) {
		if ($asText) {
			return $this->deliveryOptions[$this->session->basketDelivery]['payments'][$this->session->basketPayment]['nazev'];
		} else {
			return $this->session->basketPayment;
		}
	}

	function getPayment2($delivery, $payment) {
		return $this->deliveryOptions[$delivery]['payments'][$payment][0];
	}
	function getDelivery2($delivery) {
		return $this->deliveryOptions[$delivery]['nazev'];
	}

	function getDeliveryPrice() {
		return $this->deliveryOptions[$this->session->basketDelivery]['payments'][$this->session->basketPayment][1];
	}/**/
	function getDeliveryPrice2() {
		foreach ($this->deliveryOptions as $opt) {
			if ($opt['id'] == $this->session->basketDelivery) {
				$selected = $opt;
				//pr($selected);       
			}
			if (is_numeric($selected['zdarma_nad']) AND ($this->getSum() > $selected['zdarma_nad']) && ($this->getSum() < $selected['cena_do']))// pokud zdarma_nad neni vyplneno, tak se to neni zdarma...
			{
				return $selected['price'];
			}
		  
		}
		foreach ($this->deliveryOptions as $opt) {
			if (($this->getSum() > $opt['zdarma_nad']))// pokud zdarma_nad neni vyplneno, tak se to neni zdarma...
			{
				return $opt['price'];
			}
		}
		return $this->deliveryOptions[$this->session->basketDelivery]['payments'][$this->session->basketPayment][1];
	}

	public function getDeliveryBasket() {
		return $this->session->basketDelivery;
	}

	// vrací z session před dokončením
	public function getPaymentBasket() {
		return $this->session->basketPayment;
	}

	/**
	pokud je doprava zdarma ( zdarma nad, ne cena 0kc), tak je i platba zdarma
	 */

	public function getPaymentPrice() {
		foreach ($this->deliveryOptions as $opt)
		if ($opt['id'] == $this->session->basketDelivery) {
			$selected = $opt;
		}

		foreach ($selected['payments'] as $payment)
		if ($payment['id'] == $this->session->basketPayment) {
			$selectedPay = $payment;
		}

		if ($selected['price'] AND !$this->getDeliveryPrice2()) {
			return 0;
		} else {

			return $selectedPay['cena'];
		}
	}

	function getDelivery($asText = false) {
		if ($asText) {
			return $this->deliveryOptions[$this->session->basketDelivery]['nazev'];
		} else {
			return $this->session->basketDelivery;
		}
	}

	function getDeliveryDetail() {
		return $this->deliveryOptions[$this->session->basketDelivery];
	}

	function setNote($note) {
		$this->note = $this->session->note = $note;
	}

	function getNote() {
		return $this->session->note;
	}

	function removeCookies() {
		$_COOKIE['basket']   = null;
		$_COOKIE['delivery'] = null;
		$_COOKIE['payment']  = null;
	}

	function addItemQuickShop($item, $count = 1, $price, $view, $variantId, $optionId, $voucher = false) {
		$mVarianta = new module_Varianta();
		if (!is_object($item)) {
			$tree = Zend_Registry::getInstance()->tree;
			$item = $tree->getNodeById($item);
		}
		$variantId              = $variantId?$variantId:$view->input->variantId;
		$id                     = $item->nodeId;
		$mProd                  = new module_Products();
		$varianta               = $mVarianta->getVariantsById($variantId);
		$price                  = $price;
		$view->input->variantId = $variantId;
		$view->input->optionId  = $optionId;
		$view->input->variant   = $varianta['title'];
		if ($view->voucher) {
			$price = $mVoucher->setPriceProduct($price, $id, $view->voucher);
		}
		$newCount = $this->tempBasket[$id.'_'.$variantId.'_'.$optionId]['count']+$count;
		if ($item) {
			$this->tempBasket[$id.'_'.$variantId.'_'.$optionId] = array(
				'count'     => $newCount,
				'item'      => $item,
				'variantId' => $variantId,
				'price'     => $price,
				'optionId'  => $view->input->optionId
			);
		}
		$this->refreshDeliveries();//kvuli hmotnosti
		$this->session->lastProdId = $item->nodeId;
		$this->session->variantId  = $view->input->variantId;
		$this->session->optionId   = $view->input->optionId;

		$this->session->basket = $this->tempBasket;

		$this->session->basketItemsCount = $count;
		$cookie_data                     = $this->tempBasket;
		$this->saveCookies($this->tempBasket);
		$this->session->urlLsProduct = $item->path;
	}

	function addItem($item, $count = 1, $lastProdUrl, $view, $variantId, $optionId, $voucher = false) {
		$mVarianta = new module_Varianta();
		if (!is_object($item)) {
			$tree = Zend_Registry::getInstance()->tree;
			$item = $tree->getNodeById($item);
		}     
		$c = $item->getTheRightContent(); 
		if($c->setSleva() && $_SESSION['sl'])
		{
			$sleva = $_SESSION['sl'];
		}
		$variantId              = $variantId?$variantId:$view->input->variantId;
		$id                     = $item->nodeId;
		$mProd                  = new module_Products();       
		$varianta               = $mVarianta->getVariantsById($variantId,$sleva); 
		$price                  = $varianta['price3']?$varianta['price3']:$varianta['price'];
		$view->input->variantId = $variantId;
		$view->input->optionId  = $optionId;
		$view->input->variant   = $varianta['title'];
		$price                  = $varianta['price'];
		if ($view->voucher) {
			$price = $mVoucher->setPriceProduct($price, $id, $view->voucher);
		}
		$newCount = $this->tempBasket[$id.'_'.$variantId.'_'.$optionId]['count']+$count;
		if ($item) {
			$this->tempBasket[$id.'_'.$variantId.'_'.$optionId] = array(
				'count'     => $newCount,
				'item'      => $item,
				'variantId' => $variantId,
				'price'     => $price,
				'optionId'  => $view->input->optionId
			);
		}
		$this->refreshDeliveries();//kvuli hmotnosti
		$this->session->lastProdId = $item->nodeId;
		$this->session->variantId  = $view->input->variantId;
		$this->session->optionId   = $view->input->optionId;

		$this->session->basket = $this->tempBasket;

		$this->session->basketItemsCount = $count;
		$cookie_data                     = $this->tempBasket;
		$this->saveCookies($this->tempBasket);
		$this->session->urlLsProduct = $item->path;
	}

	/**
	 * zjisti cenu objednavky, a zjisti, jake dopravy lze pouzit
	 */
	public function getDeliveries() {
		$cenaObj = $this->getSum();
		//print_r($cenaObj);
		$ret = array();
		foreach ($this->deliveryOptions as $key => $delivery) {
			if (($delivery['cena_do'] AND ($cenaObj > $delivery['cena_do'])) OR (isset($delivery['cena_od']) AND ($cenaObj < $delivery['cena_od']))) {
				$delivery['disabled'] = true;
			} else {
				$delivery['disabled'] = false;
			}
			$ret[$key] = $delivery;
		}
		return $ret;
	}

	public function reCalcPriceVoucher($view) {
		$items                 = $this->getItems();
		$newItems              = $view->mVoucher->setVoucher($items, $view->voucher);
		$this->session->basket = $this->tempBasket = $newItems;
		$this->saveCookies($this->tempBasket);
	}

	function saveCookies($basket = false, $delivery = false, $payment = false, $note = false, $clear = false) {
		if ($_COOKIE['eu-cookies']) {
			if ($clear) {
				$this->setcookielive("basket", null, time()+36000000, '/', 'svycarskekavovary.cz');
				$this->setcookielive('delivery', null, time()+36000000, '/', 'svycarskekavovary.cz');
				$this->setcookielive('note', null, time()+36000000, '/', 'svycarskekavovary.cz');
				$this->setcookielive('payment', null, time()+36000000, '/', 'svycarskekavovary.cz');
			}
			if ($basket) {
				$this->setcookielive("basket", '', time()+36000000, '/', 'svycarskekavovary.cz');
				foreach ($basket as $key => $value) {
					$cookie[$key] = $value['count'];
				};
				$saveCookie = base64_encode(serialize($cookie));
				$this->setcookielive("basket", $saveCookie, time()+36000000, '/', 'svycarskekavovary.cz');
			}
			if ($delivery) {
				$this->setcookielive('delivery', $delivery, time()+36000000, '/', 'svycarskekavovary.cz');
			}
			if ($payment) {
				$this->setcookielive('payment', $payment, time()+36000000, '/', 'svycarskekavovary.cz');
			}
			if ($note) {
				$note = base64_encode($note);
				$this->setcookielive('note', $note, time()+36000000, '/', 'svycarskekavovary.cz');
			}
		}
	}

	function setcookielive($name, $value = '', $expire = 0, $path = '', $domain = '', $secure = false, $httponly = false) {
		//set a cookie as usual, but ALSO add it to $_COOKIE so the current page load has access
		$_COOKIE[$name] = $value;
		return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
	}

	function removeItem($id) {
		unset($this->tempBasket[$id]);
		$this->session->basket = $this->tempBasket;

	}

	function changeCount($input) {
		foreach ($input as $id => $count) {
			if ($count == 0) {
				$this->removeItem($id);
				continue;
			}
			$this->tempBasket[$id]['count'] = $count;
		}
		//unset($this->tempBasket[$id]);
		$this->saveCookies($this->tempBasket);
		$this->session->basket = $this->tempBasket;
	}

	function getItems() {
		return $this->tempBasket;
	}

	function getItemsCount() {
		$count = 0;
		if ($this->tempBasket) {
			foreach ($this->tempBasket as $id => $data) {
				$count += $data['count'];
			}
			return $count;
		}
	}

	function getSum($view, $deliveryPay = false) {
		$sum = $this->calcPrice($this->tempBasket, $view);
		if ($deliveryPay) {
			$sum += $this->getDeliveryPrice2();
		}
		return $sum;
	}

	public function getPricePayDel() {
		$sum = $this->getDeliveryPrice2();
		$sum += $this->getPaymentPrice();
		return $sum;
	}

	function calcPrice($products, $view) {
		$sum = 0;
		if ($products) {
			foreach ($products as $data) {
				$price = $data['price'];
				$sum += $data['count']*$price;
			}

			$dph       = $sum/(1+($view->dph/100))*($view->dph/100);
			$dph       = round($dph, 2);
			$sumBezdph = $sum-$dph;

			/*if($sumBezdph < 5500	){
		$deliverySum = $this->getDeliveryPrice();
		$sum += $deliverySum;
		}*/
		}
		return $sum;

	}

	function reinit() {
		$this->view->basketItems      = $this->getItems();
		$this->view->basketItemsCount = $this->getItemsCount();
		$this->view->basketSum        = $this->getSum();
	}

	/* shopper */
	function setShopper($view, $input) {
		$secCode = date('Ym');
		unset($input->$secCode);
		unset($input->step);

		$input->fullname = $input->fullname?$input->fullname:$input->jmeno.' '.$input->prijmeni;

		//$err = $this->checkShopper($view, $input);
		$err = false;
		if (!$err) {
			$this->session->shopper = $input;

			return true;
		} else {
			return array(0, $err);
		}
	}

	function getShopper() {
		return $this->session->shopper;
	}
	 

	function removeShopper($view) {
		$view->session->shopper = $this->session->shopper = $this->shopper = false;
	}
	
	function createToken($privateKey, $url, $orderId){
    return hash('sha256', $privateKey.$url.$orderId);
} 

	function createUrl( $orderId){
		$id = $orderId;
    	$hash = $this->createToken($this->privateKey, Utils::getWebUrl(), $orderId);
		$orderId = base64_encode($orderId);
    	$autoLoginUrl = http_build_query(array(
       	 	'repeatBasket' => 1,
       	 	'idorder' => ($orderId), 
        	'token' => $hash       
		));       
		$data['hash_order'] = $autoLoginUrl;      
		$where = $this->db->quoteInto('id =?',$id);                
		$this->db->update('module_eshop_orders',$data,$where);
		return $autoLoginUrl;
}  
	

	function repeatBasket($view)
	{	  
		$this->emptyBasket();   
		$mOrder = new module_Eshop_Orders();       
		$order = $mOrder->getOrderByToken($view->inputGet->token);       
		if($order['email']){              
			$this->setPayment($order["payment"]);   
			$this->setDelivery($order["delivery"]);     
			$mItems = $mOrder->getOrderDetails($order['id']);    		 
			$input = new stdClass();       
			$input->email = $order['email'];
			$input->ffullname = $order['efullname'];   
			$input->ffirma = $order['firma'];
			$input->fulice = $order['ulice'];
			$input->fMesto = $order['mesto'];
			$input->fpsc = $order['psc']; 
			$input->firma = $order['fu_firma'];
			$input->fullname = $order['fu_jmeno'];	    
			$input->ulice =  $order['fu_ulice'];   
			$input->mesto = $order['fu_mesto'];
			$input->psc = $order['fu_psc'];      
			$input->ico = $order['fu_ico'];
			$input->ftelefon = $order['phone'];
			$input->dic = $order['fu_dic'];   
			$input->telefon =$order['fu_phone'];
			$this->setShopper($view,$input); 
			if($mItems)  
			{      
				foreach ($mItems as $key => $value) {  
					$this->addItemQuickShop($value["itemNodeId"], $value["count"], $value["price"], $view, $value["variant"]);
					//$this->addItem($value["itemNodeId"],$value["count"],false,$view,$value["variant"],false);
				}
			}
		} 
		return false;          
	}

	function checkShopper($view, $input) {

	}

	function makeOrder($view) {
		$mIportAdmin = new module_ImportAdmin(); 
		$mConversionChecker = new module_ConversionChecker();
		$this->getItems = $view->mEshop->basket->getItems();
		if ($this->getItems) {
			$shopper = $this->getShopper();

			$tree   = $view->tree;
			$mEshop = $view->mEshop;

			$delivery = $this->getDelivery();
			$payment  = $this->getPayment();
			$idDoklad = '';
			if (($delivery == 20 && 33 == $payment) || ($delivery == 20 && 35 == $payment)  ) {
				$mDoklad  = new module_PDoklad($view);
				$idDoklad = $mDoklad->getMaxId();
			}

			$deliverySum = $view->delivery = $this->getDeliveryPrice2();
			$paymentSum  = $view->payment  = $this->getPaymentPrice();

			$selPayment = $this->m_p->getPaymentById($payment);

			$shopperBasket = (array) $this->getShopper();

			$items       = $this->getItems();
			$voucher     = $this->voucher;
			$itemsCount  = count($items);
			$deliverySum = $this->getDeliveryPrice2();
			$paymentSum  = $this->getPaymentPrice();
			$mp          = new module_Eshop_PriceCalc($voucher, $items, $deliverySum, $paymentSum);

			$sum        = $totalSum        = $mp->sum;
			$sumPlatba  = $mp->payment;
			$sumDoprava = $mp->delivery;
			$sumDph     = $mp->dph;
			$sumBezdph  = $mp->sumBezDph;
			$sumDPH = $mp->sum-($deliverySum+$paymentSum); 
			$_SESSION['sumPlatba']  = $sumPlatba;
			$_SESSION['sumDoprava'] = $sumDoprava;
			$_SESSION['sumDph']     = $sumDph;
			$_SESSION['sumBezdph']  = $sumBezdph;
			$_SESSION['sum']        = $sum;
			$_SESSION['oid']        = $oid;

			$shopperBasket['suma']        = $sum;
			$shopperBasket['payment']     = $payment;
			$shopperBasket['delivery']    = $delivery;
			$shopperBasket['deliverySum'] = $deliverySum;
			$shopperBasket['paymentSum']  = $paymentSum;

			$tmp                           = $this->m_d->getDeliveryById($delivery);
			$shopperBasket['deliveryText'] = $tmp['nazev'];
			$tmp                           = $this->m_p->getPaymentById($payment);
			$shopperBasket['paymentText']  = $tmp['nazev'];

			$payment             = $this->getDelivery(true);
			$shopperBasket['fu'] = $payment;

			$items = $fakItems = array();

			$cid     = $view->webUser->id?$view->webUser->id:0;
			$parts   = explode(' ', $shopperBasket['fullname']);
			$surname = array_pop($parts);
			$name    = implode(' ', $parts);

			$surname = $surname?$surname:'';
			$name    = $name?$name:$shopperBasket['fullname'];
			$name    = $name?$name:'';

			foreach ($shopperBasket as $k => $v) {
				if (!strlen($v)) {
					$shopperBasket[$k] = '';
				}
			}
			$mCustomer = new module_Customer(false, false, false, $shopperBasket['email']);

			$mProd   = new module_Products();
			$mMagnet = new module_ImportMagnet();
			if (!$cid) {
				$cid = $mCustomer->id;
			}
			$mCustomer->setMailing($shopperBasket['email']);
			$full = explode(' ', $shopperBasket['fullname']);

			// 		die();
			$data = array(
				'firstname'     => $name,
				'surname'       => $surname,
				'email'         => $shopperBasket['email'],
				'custid'        => $cid,
				'firma'         => $shopperBasket['ffirma'],
				'efullname'     => $shopperBasket['ffullname'],
				'ulice'         => $shopperBasket['fulice'],
				'mesto'         => $shopperBasket['fMesto'],
				'psc'           => $shopperBasket['fpsc'],
				'stat'          => 'cz',
				'fu_firma'      => $shopperBasket['firma'],
				'fu_jmeno'      => $shopperBasket['fullname'],
				'fu_stat'       => 'cz',
				'fu_ulice'      => $shopperBasket['ulice'],
				'fu_mesto'      => $shopperBasket['mesto'],
				'fu_psc'        => $shopperBasket['psc'],
				'fu_ico'        => $shopperBasket['ico'],
				'fu_dic'        => $shopperBasket['dic'],
				'phone'         => $shopperBasket['ftelefon'],
				'fu_phone'      => $shopperBasket['telefon'],
				'suma'          => $shopperBasket['suma'],
				'deliveryPrice' => $deliverySum,
				'paymentPrice'  => $paymentSum,
				'deliveryText'  => $shopperBasket['deliveryText'],
				'paymentText'   => $shopperBasket['paymentText'],
				'payment'       => $shopperBasket['payment'],
				'delivery'      => $shopperBasket['delivery'],
				'state'         => 'WAIT',
				'fu'            => $shopperBasket['fu'],
				'note'          => $this->getNote(),
				'discountCode'  => '',
				'id-doklad'     => $idDoklad
			);

			/*if($data['delivery'] == 'CP' && $data['payment'] == 2){ // CP platba
			$data['state'] = 'WPBCP';
			} elseif($data['delivery'] == 'CP' && $data['payment'] == 3){ // kartou
			$data['state'] = 'RPC';
			} elseif($data['delivery'] == 'GP' && $data['payment'] == 3){ // kartou
			$data['state'] = 'RPC';
			} elseif($data['delivery'] == 'GP' && $data['payment'] == 2){ // GP platba
			$data['state'] = 'WPBGP';
			} */

			if ($data['payment'] == 17) {//platba kartou
				$data['paid']  = '1';
				$data['state'] = 'WAIT';
			}

			foreach ($data as $k => $v) {
				if (!strlen($v)) {
					$data[$k] = '';
				}
			}

			$this->db->insert(
				$this->_tableName,
				$data
			);
			$oid = $this->db->lastInsertId();
			$_SESSION['lastOrder'] = $oid;   
			
			$this->createUrl($oid);    
			
			   
			$addToEmail = '';
			if ($data['ulice']) {
				$addToEmail = '<p style="margin: 0; margin-bottom: 5px; clear: left;"><strong>Dodací údaje</strong></p>
<p style="margin: 0; margin-bottom: 5px; margin-left: 5px;">'.$shopperBasket['ffirma'].'</p>
<p style="margin: 0; margin-bottom: 5px; margin-left: 5px;">'.$shopperBasket['ffullname'].'</p>
<p style="margin: 0; margin-bottom: 5px; margin-left: 5px;">'.$shopperBasket['fulice'].'</p>
<p style="margin: 0; margin-bottom: 5px; margin-left: 5px;">'.$shopperBasket['fMesto'].'</p>
<p style="margin: 0; margin-bottom: 5px; margin-left: 5px;">'.$shopperBasket['fpsc'].'</p>
<p style="margin: 0; margin-bottom: 5px; margin-left: 5px;">'.$shopperBasket['ftelefon'].'</p>';
			}
			$mVarianta = new module_Varianta();
			$zbozi      = '';
			foreach ($this->getItems as $key => $odata) {
				$temp     = explode('_', $key);
				$id       = $temp[0];
				$varianta = $temp[1];
				$items    = array();
				$child    = $odata['item'];
				$count    = $odata['count'];
				if (!$child) {continue;}
				$selVariant = $mVarianta->getVariantById($varianta);
				$c                   = $child->getTheRightContent();
				$price               = $odata['price'];
				$sum                 = $price*$count;
				$items['o_id']       = $oid;
				$items['count']      = $count;
				$dphQuote            = $c->getPropertyValue('dphQuote');
				$items['title']      = $child->title;
				$items['dph']        = $dphQuote;
				$items['variant']    = $varianta;
				$items['itemNodeId'] = $child->nodeId;
				$items['price']      = $price;
				$items['content']    = serialize($c);
				$zbozi .= '&produkt[]='.urlencode($child->title);
				$itemCo['itemNodeId'] = $child->nodeId;
				$itemCo['title']      = $child->title;
				$itemsCof[]           = $itemCo;
				$agmoItem[$dphQuote] += $sum;
				$agmoItemSum[$dphQuote] += $sum;  
				$_SESSION['idsFaceBook'][] = "jura_cz_".$child->nodeId;
				$idZnacka = $c->getPropertyValue('znacka');
				$znacka = $view->mProducts->getZnacky($idZnacka);
				$p = $c->getPropertyValue('parent'); 
    			$vals = helper_MultiSelect::getMultiSelectValues($p);
    			$parentNode = $path = $view->tree->getNodeById(max($vals));
				$ite = $items; 
				$ite['mark'] = $znacka['nazev'];       
				$ite['ext_id']  = $selVariant['ext_id'];
				$itemsImport[$child->nodeId.'_'.$varianta] = $ite;     
				$wh                   = $this->db->quoteInto('id = ?', $c->id);
				$realSold['realSold'] = $c->getPropertyValue('realSold')+1;
				$this->db->update('content_Product', $realSold, $wh);
				$this->db->insert(
					$this->_tableItemsName,
					$items
				);

				$fakItems[] = array(
					$child->title,
					$odata['count'],
					$items['price'],
					$c->getPropertyValue('znacka'),
					$c->getPropertyValue('kod'),
					$items['variantId'],
				);
				
			

				$dph = $sumDph; 
   
				$priceBezUnit = round($price -  ($price / (1 + ($dphQuote/100)) * ($dphQuote/100)), 2); ;
				$priceBez = round($sum -  ($sum / (1 + ($dphQuote/100)) * ($dphQuote/100)), 2); ;
				$totalPriceBezDPH += $priceBez; 
				$totalPriceDPH += ($sum  - $totalPriceBezDPH); 
				$conversionItem->name = $child->title;
				$conversionItem->sku = $child->nodeId;
				$conversionItem->category = $parentNode->path;
				$conversionItem->brand = $znacka['nazev'];
				$conversionItem->categoryTitle = $parentNode->title;
				$conversionItem->price = $priceBezUnit;
				$conversionItem->quantity = $count;
				$converstionEcommerse->items[]= $conversionItem;
				}
				$converstionEcommerse->orderId = $oid;
				$data['referal'] = $mConversionChecker->getReferal();  
            
			$idAdminOrder = $mIportAdmin->makeOrder($itemsImport,$data,$oid);  //admin   
			// QRCode  
			if($shopperBasket['payment'] == 36 || $shopperBasket['payment'] == 6) {    
				$mQRPayment = new module_QRPayment();                
				$qrFile = $mQRPayment->getQRCode($idAdminOrder,$oid,$shopperBasket['suma']);
				$paymentInfo = '<p>QR kód pro Vaši platbu:</p>
								<img width="180" height="180" src="'.Utils::getWebUrl().$qrFile.'">';           
				$qrImg = $oid;                           
			}          
			else{    
				$paymentInfo = '';    
			}       
 

			// doprava a platba dph 21      
			if ($deliverySum > 0) {  
				$agmoItem[21] += $deliverySum;
				$agmoItemSum[21] += $deliverySum;
			}  
			if ($paymentSum > 0) {
				$agmoItem[21] += $paymentSum;
				$agmoItemSum[21] += $paymentSum;
			}

			$dph21 = $agmoItem[21]-($agmoItem[21]/(1+(21/100)));
			$dph15 = $agmoItem[15]-($agmoItem[15]/(1+(15/100))*(15/100));
			//	pr($dph21);
			//	pr($dph15);
			$agmo['celk_trzba'] = $shopperBasket['suma']*100;
			;

			if ($dph21 > 0) {
				$agmo['dan1']      = round($dph21, 2)*100;
				$dph21             = round($dph21, 2);
				$agmo['zakl_dan1'] = ($agmoItemSum[21]-$dph21)*100;
				;

			}
			if ($dph15 > 0) {
				$dph15             = round($dph15, 2);
				$agmo['zakl_dan2'] = $dph15*100;
				;

				$agmo['dan2'] = ($agmoItemSum[15]-$dph15)*100;
				;

			}
			//$agmo['cest_sluz'] = ($deliverySum + $paymentSum)*100;
			//pr($agmo);
			// die;
			unset($_SESSION['redirectPayment']);
			unset($_SESSION['redirectPayLink']); 
			if ($selPayment['agmo']) {
				$paymentsProtocol = new module_Agmo_AgmoPaymentsSimpleProtocol($view->config);
				$paymentsDatabase = new module_Agmo_AgmoPaymentsSimpleDatabase($view->config);
				$refId            = $paymentsDatabase->createNextRefId();
				$price            = $shopperBasket['suma'];
				$currency         = 'CZK';

				// create new payment transaction
				$paymentsProtocol->createTransaction(
					'CZ', // country
					$price, // price
					$currency, // currency
					'Objednávka Jura', // label
					$refId, // refId
					NULL, // payerId
					'STANDARD', // vatPL
					'PHYSICAL', // category
					$selPayment['agmo'], // method
					json_encode($agmo)
				);
				$transId = $paymentsProtocol->getTransactionId();

				// save transaction data
				$paymentsDatabase->saveTransaction(
					$transId, // transId
					$refId, // refId
					$price, // price
					$currency, // currency
					'PENDING'// status
				);
				$_SESSION['redirectPayment'] = $_SESSION['redirectPayLink'] = str_replace("https:/","https://",$paymentsProtocol->getRedirectUrl()); // vypsat a uložit do asi i do DB emailu...
				$paymentInfoLink ='<br/><p><strong>V případě, že Vám platba neprošla</strong>, můžete platbu zopakovat na tomto <a href="'.$_SESSION['redirectPayment'].'">odkazu.</a></p>';
				$_SESSION['mPaymentAgmo'] = $paymentsProtocol; 
			}

			unset($_SESSION['mCofidis']);
			if ($shopperBasket['payment'] == 16) {
				$mCofidis = new module_Cofidis($data,$itemsCof, $oid);
				$_SESSION['redirectPayment'] = $_SESSION['redirectPayLink'] = $_SESSION['mCofidis'] = $mCofidis->sendRequest();		
			}
		

			$u = 'http://www.heureka.cz/direct/dotaznik/objednavka.php?id=b943273ce13e4bd41d1b12a490f95bdd&email='.$data['email'].$zbozi.'&orderid='.$oid;
			file($u);

			$trans = $deliverySum+$paymentSum;

			$nl  = "/n";
			$sum = $this->getSum($view);  
			$converstionEcommerse->revenue = $totalPriceBezDPH;
			$converstionEcommerse->trans = $trans;  
			$converstionEcommerse->tax = $totalPriceDPH;
			$mConversionChecker = new module_ConversionChecker();
			$mConversionChecker->setConversions($converstionEcommerse); 
			$_SESSION['sumFaceBook'] = $sumBezdph;

				if ($view->conversionSettings->heurekaConversion) {
					$_SESSION['herurekaKonverze'] = "
		<script type=\"text/javascript\">
		var _hrq = _hrq || [];
		    _hrq.push(['setKey', '']);
		    _hrq.push(['setOrderId', '$oid']);
$hItems_hrq.push(['trackOrder']);

		(function() {
		    var ho = document.createElement('script');
 ho.type = 'text/javascript';
 ho.async = true;

		    ho.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.heureka.cz/direct/js/cache/1-roi-async.js';
		    var s = document.getElementsByTagName('script')[0];
 s.parentNode.insertBefore(ho, s);

		})();
		</script>
";

				}

			
				//print_r($data);
				//die;
				$faktura = $view->mFakturace->addFaktura($view, $data, $vyzva, $fakItems, 0, $oid);

				$textNode = $view->tree->getNodeById(5198);
				$subject = $textNode->title;
				$mainText = $textNode->getHtml();   

				$view->payment = $payment = $this->m_p->getPaymentById($shopperBasket['payment']);
				$basketItems = $view->render('templates/Eshop/ItemsListEmail2.phtml');
				//if($_GET['basket']){
				//e($basketItems);
				//	die;
				//}

				$this->saveCookies(false, false, false, false, true);
				$payment = $this->m_p->getPaymentById($shopperBasket['payment']);
				//if()
				if ($payment['useEmailOR']) {
					$basketItems .= $payment['emailOrderReceived'];
				}

				if (!$mCustomer->regFormBasket) {
					$mainText = str_replace('<p>[login]</p>', '', $mainText);
				} else {
					$login = 'Vaše přihlašovací údaje pro další nákup:';
					$login = '<table style="font-size: 11px;">
						<tbody>
							<tr>
								<td>uživatelsk&eacute;
 jm&eacute;
no:</td>
								<td><strong>'.$mCustomer->email.'</strong></td>
							</tr>
							<tr>
								<td>heslo:</td>
								<td><strong>'.$mCustomer->heslo2.'</strong></td>
							</tr>
						</tbody>
					</table>';
					$mainText = str_replace('[login]', $login, $mainText);
					$mCustomer->changeStatus(0);
				}

				$mainText = str_replace('[emailKosik]', $basketItems, $mainText);
				//$name,
				//'surname' => $surname,

				$addToEmail = '';
				if ($shopperBasket['ffullname']) {
					$addToEmail = '<p style="margin: 0; margin-bottom: 5px; clear: left;"><strong>Dodací údaje</strong></p>
<p style="margin: 0; margin-bottom: 5px; margin-left: 5px;">'.$shopperBasket['ffirma'].'</p>
<p style="margin: 0; margin-bottom: 5px; margin-left: 5px;">'.$shopperBasket['ffullname'].'</p>
<p style="margin: 0; margin-bottom: 5px; margin-left: 5px;">'.$shopperBasket['fulice'].'</p>
<p style="margin: 0; margin-bottom: 5px; margin-left: 5px;">'.$shopperBasket['fMesto'].'</p>
<p style="margin: 0; margin-bottom: 5px; margin-left: 5px;">'.$shopperBasket['fpsc'].'</p>
<p style="margin: 0; margin-bottom: 5px; margin-left: 5px;">'.$shopperBasket['ftelefon'].'</p>';
				}

				if ($order['fu_dic']) {
					$d = 'DIČ: '.$shopperBasket['ico'];
				}

				$config = Zend_Registry::getInstance()->config;

				$totalSum = $sum+$deliverySum+$paymentSum;
				
      
				
				// if($shopperBasket['payment'] == 6)
				// { 
					// $paymentInfo = '<br>
					// <p style="margin: 0; margin-bottom: 5px; clear: left;font-size: 14px"><strong>Informace k platbě</strong></p>
// <p style="margin: 0; margin-bottom: 5px; margin-left: 5px;">Číslo účtu: <strong>2600340251/2010</strong></p>
// <p style="margin: 0; margin-bottom: 5px; margin-left: 5px;">Variabilní symbol: <strong>'.$oid.'</strong></p>        
// <p style="margin: 0; margin-bottom: 5px; margin-left: 5px;">Fakturu obdržíte automaticky e-mailem v den doručení zboží.</p>
// <p style="margin: 0; margin-bottom: 5px; margin-left: 5px;"></p><br>';         
// 				    
				// }    
				  
				$replacements = array(    
					'[dateCreate]'      => Utils::formatDate(date('Y-m-d H:i:s')),
					'[domainName]'      => $config->instance->title,
					'[varSym]'          => $oid,
					'/[domainUrl]'      => Utils::getWebUrlHttps(),
					'[WWW]'             => 'https://www.svycarskekavovary.cz',
					'[customerName]'    => $name.' '.$surname,
					'[paymentInfo]'		=> $paymentInfo,
					'[customerTel]'     => $shopperBasket['telefon'],
					'[streetName]'      => $shopperBasket['ulice'],
					'[email]'           => $shopperBasket['email'],
					'[showEmail]'       => $shopperBasket['email'],
					'[paymentInfo]'		=> $paymentInfo,
					'[phone]'           => $shopperBasket['telefon'],
					'[note]'            => strip_tags($this->getNote()),
					'[city]'            => $shopperBasket['psc'].' '.$shopperBasket['mesto'],
					'[CisloObjednavky]' => $oid,
					'[priceTotal]'      => helper_FrontEnd::price($totalSum).' Kč',
					'[payment]'         => $payment['nazev'],
					'[ico]'             => $shopperBasket['ico'],
					'[dic]'             => $shopperBasket['dic'],
					'[firma]'           => $shopperBasket[''],
					'[dorucovaci]'      => $addToEmail,
					'[phoneCom]'        => $view->phoneNum,
					'[firma]'           => $shopperBasket['firma'],
					'[paymentInfoLink]' => $paymentInfoLink,
				);

				///[dorucovaci]

				// sejme varSymbol
				if ($payment['id'] != 16) {
					$temp = explode('<tr id="varSym" style="margin-left: 0; padding-left: 0;">', $mainText);

					//pr($temp);
					$temptt = explode('<tr id="price"', $temp[1]);
					$mainText = $temp[0].'<tr id="price"'.$temptt[1];
				}
				foreach ($replacements as $ident => $substitute) {
					$mainText = str_replace($ident, $substitute, $mainText);
				}

				

				foreach ($replacements as $ident => $substitute) {
					$subject = str_replace($ident, $substitute, $subject);
				}

				

				// kontrola na prázdné řádky
				$mainText = str_replace('<p style="margin: 0; margin-bottom: 5px; margin-left: 5px;"></p>', '', $mainText);
		       
	        	         
				$mail = new Email();
				$mail2 = new Email();
				$emailText = Helpers::prepareEmail(
					$mail,
					$mainText,
					false,
					false,    
					'484848',
					'000000'
				);
				        
			      
				$mail->setBodyText(strip_tags(urldecode($mainText)));
				$mail->setBodyHtml(urldecode($emailText));      
				
				$this->emptyBasket();     
				$mail->setSubject($subject);

				$mail2->setBodyText(strip_tags(urldecode($mainText)));
				$mail2->setBodyHtml(urldecode($emailText));
				$mail2->setSubject($subject);
				$s = Zend_Registry::getInstance()->settings;

		
				$mail->addTo($data['email'], $data['firstname'].' '.$data['surname']);

				$mail2->addTo('objednavky.specshop@gmail.com', 'Svycarskekavovary.cz');
				$mail2->addBcc('nosil@specshop.cz', 'Svycarskekavovary.cz');   
				$mail2->addBcc('prodej@specshop.cz', 'Svycarskekavovary.cz');
				$mail2->addBcc('obchod@specshop.cz', 'Svycarskekavovary.cz');
				$mail2->addBcc('info@specshop.cz', 'Svycarskekavovary.cz');  
				if($shopperBasket['payment'] == 6)
						$mail2->addBcc('fakturace@specshop.cz', 'Svycarskekavovary.cz');
    
				$mail2->setFrom('noreply@specshop.cz', 'Svycarskekavovary.cz');
				// if($shopperBasket['suma']<10000)     
				// {
				// $mail->addBcc('sylvie.drevinkova@jablum.cz', 'Jura');
				// }

				// $filePath = Utils::getWebUrl().'/attachment/Formular_pro_vraceni_zbozi.docx';
				// $fileContents = file_get_contents($filePath);
				// $file = $mail->createAttachment($fileContents);
				// $file->filename = "Formular_pro_vraceni_zbozi.docx";

				$filePath = Utils::getWebUrl().'/attachment/Formular_pro_vraceni_zbozi.doc';
				$fileContents = file_get_contents($filePath);
				$file = $mail->createAttachment($fileContents);   
				$file->filename = "Formular_pro_vraceni_zbozi.doc";

				$filePath1 = Utils::getWebUrl().'/attachment/obchodni_podminky.cz.pdf';
				$fileContents1 = file_get_contents($filePath1);
				$filePdf = $mail->createAttachment($fileContents1);
				$filePdf->filename = "obchodni_podminky.cz.pdf";

				$mail->setFrom("prodej@svycarskekavovary.cz", 'Svycarskekavovary.cz');

				try {
					$mail->send();
				} catch (Exception $e) {}

				try {
					$mail2->send();
				} catch (Exception $e) {}
				$mHisBas = new module_HistoryBasket();
				$mHisBas->incBasket(5);

				$this->session->basket = $this->tempBasket = null;
				if ($this->voucher['id_v']) {
					$view   ->mVoucher->addOrder($this->voucher['id_v'], $oid);
					$view->mVoucher->useVoucher($this->voucher['id_v']);
				}
				$this->voucher = $this->session->voucher = null;
   
				return array($oid, $qrImg );  
			}
		}

		function setBasketFromCookie($view) {
			if ($_COOKIE['eu-cookies']) {
				$cookie = unserialize(base64_decode($_COOKIE['basket']));
				if ($_COOKIE['delivery']) {
					$this->setDelivery($_COOKIE['delivery']);
				}
				if ($_COOKIE['payment']) {
					$this->setPayment($_COOKIE['payment']);
				}
				if ($_COOKIE['note']) {
					$this->setNote(base64_decode($_COOKIE['note']));
				}
				foreach ($cookie as $key => $value) {
					$produkt = explode('_', $key);
					$this->addItem($produkt[0], $value, false, $view, $produkt[1]);
				}
				return $cookie;
			}
		}

		function showDeliveries($view) {
			$sum = $this->getSum($view);
			$delivery = $this->m_d->getValidDeliveries();
			if ($sum < 2499) {
				unset($delivery[25]);
				unset($delivery[26]);
			} elseif ($sum > 100000) {
				unset($delivery[16]);
				unset($delivery[25]);
			} elseif ($sum > 2499 && $sum < 99999) {
				unset($delivery[16]);
				unset($delivery[26]);
			}
			if (!array_key_exists($this->getDelivery(), $delivery)) {
				$del = $delivery;
				unset($del[24]);
				$this->setDelivery(key($del));
			}

			$this->deliveryOptions = $delivery;

			return $this->deliveryOptions;
		}

		function getPaymentsById($idDelivery) {
			$delivery = $this->deliveryOptions;
			//	pr($delivery ) ;
			$payments = array();
			if (!$idDelivery) {
				foreach ($delivery as $value) {
					$idDelivery = $value['id'];
					break;
				}
			}
			foreach ($delivery as $value) {
				if ($value['id'] == $idDelivery) {
					$payments = $value['payments'];
				}
			}

			return $payments;
		}

		function emptyBasket() {
			$view->session->step = null;
			$this->session->basket = $this->tempBasket = null;
			$this->delivery = $this->session->basketDelivery = null;
			$this->payment = $this->session->basketPayment = null;
			$this->saveCookies(false, false, false, false, true);

			//$this->voucher = $this->session->voucher = null;
		}

		function emptyVoucher($view) {
			//$this->session->basket = $this->tempBasket = null;
			$mVoucher->setDefaultPrice($this->getItems);
			$this->voucher = $this->session->voucher = null;
			$this->session->basket = $this->tempBasket = $newItems;
			$this->saveCookies($this->tempBasket);
		}

		public function addVoucher($view) {
			$this->voucher = $this->session->voucher = $view->voucher;
			$this->reCalcPriceVoucher($view);
		}
	}
	?>