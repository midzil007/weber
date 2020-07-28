<?

/**
 * Třída reprentuje nakupni kosik
 */
class module_Eshop_Basket {	
			
	public  $deliveryOptions = array();
	public  $paymentOptions = array();
	public  $delivery;
	public  $payment;
	public 	$voucher;
	public $states = array( 
		'cz' => 'Česká republika'
	);  
	// module delivery, payment
	public $m_d;
	public $m_p;
	public function __construct() {
		$this->db = Zend_Registry::getInstance()->db;
		$this->db2 = Zend_Registry::getInstance()->db2;	
		$this->tree = Zend_Registry::getInstance()->tree;	
		$this->session = Zend_Registry::getInstance()->session;	
		$this->m_d = new module_Delivery();
		$this->m_p = new module_Payment();
		$this->tempBasket = $this->session->basket;
		$this->voucher = $this->session->voucher;
		if(!is_array($this->tempBasket)){ 
			$this->tempBasket = $this->session->basket = array();
		}
		$this->initDeliveryTypes();
		$this->_tableName = 'module_eshop_orders'; 
		$this->_tableItemsName = 'module_eshop_order_items';
		$this->_tableTemporary = 'module_eshop_temporaryBasket';
	}
	
	function drawInfoLine($view){
		$items = $this->getItems();
	
		$itemsCount = $this->getItemsCount();
		$xhtml = '';
		if($itemsCount >= 1){ 
			$sum = $this->getSum($view);
			if($itemsCount == 1){
				$t = 'položka';
			} elseif($itemsCount < 5){
				$t = 'položky';  
			} else {  
				$t = 'položek';    
			} 
			
			$xhtml .= '<a class="ttt"  id="bpc" href="' . $view->bp . '" rel="nofollow">' . "<strong>$itemsCount $t</strong> za <strong>" . helper_FrontEnd::price($sum) . "</strong> Kč </a>" ;
			 
			$items = $this->getItems();
			?>  
	      	
	      	<div id="basket">
	        <ul> 
	          <li id="visiBlock"  <?=($view->inputGet->ajax?'class="active"':'')?>><?=$xhtml?><a href="<?=$view->bp?>" class="basket">&nbsp;</a>
	            <ul>    
	              <li>
	                <div class="content"> 
	                <? foreach ($items as $id => $data): 
	                	$child = $data['item'];
		              	if(!$child){ continue; } 
		              	$c = $child->getPublishedContent();
		              	$p = helper_FrontEnd::getPhoto('photos', $c, $c->fotoMiniName);
		            		if(count($p)){
		            			$img = $p['path']; 
		            		} else {
		            			$img = '/images/categoryImg.jpg';
		            		} 
		            		$price  = $c->getPropertyValue('price');   
		            		$url = $c->getDetailUrl($this, $child);  
	                ?>
	                  <div class="inner">
	                    <div>  
	                    	<a class="im" href="<?=$url?>"><img src="<?=$img?>" alt=" " /></a> 
	                        <p> 
	                        	<a class="ttt" href="<?=$url?>"><?=helper_FrontEnd::reduceText($child->title, 40, false)?></a>
	                        	<span><?=helper_FrontEnd::price($price)?> Kč</span> 
	                        	<a href="#" onclick="return submitBasket(<?=$child->nodeId?>);" class="remove">&nbsp;</a> 
	                        </p>     
	                    </div>
	                  </div>  
	                 <?  endforeach;?>  
	                </div> 
	                <div class="navigacia"> 
	                	<a class="ttt" id="visibletrigger" href="#">pokračovat v nákupu</a> &nbsp;&nbsp;&nbsp; 
	                	<a class="ttt" href="<?=$view->bp?>" class="pokl">k pokladně  ›</a>
	                </div>
	              </li>
	            </ul>
	          </li>
	        </ul>
	        
	        	<form action="<?=$view->bp?>" method="post" name="orderForm" id="orderFormBasket">
              	<?=$view->formHidden('pid', 0, array('id' => 'bpid'))?>   
              	<?=$view->formHidden('action', 'buy')?>  
              	<?=$view->formHidden('count', 0, array('id' => 'bpks'))?>  
                </form>
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
	
	public function setDefaultDelivery()
	{
		$doprava = $this->deliveryOptions;
		foreach ($doprava as $ident => $data){
			$idDoprava = $data['id'];
			break;
		};
		$this->setDelivery($idDoprava);
	}
	
	public function setDefaultPayment()
	{
		$delivery = $this->getDelivery();
		$platby = $this->getPaymentsById($delivery);

		die();	
	}
	
	
	public function refreshDeliveries()
	{	
		//print_r( $this->calcWeight() );
		//print_r( $this->calcWeight() );
		$this->deliveryOptions = $this->m_d->getValidDeliveriesByWeight( $this->calcWeight() );
		
	}
	
	public function calcWeight()
	{	
		$sum = 0;
		foreach ($this->tempBasket as $data){
			$p = $data['item']->getPublishedContent();
			$w  = $p->getPropertyValue('hmotnost');  
			$sum += $data['count'] * $w; 
		} 
		
		return $sum/1000; //zbozi je v gramech
	}
	
	function initDeliveryTypes(){
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
	
	function setPayment($payment){
		$this->payment = $this->session->basketPayment = $payment;
		$this->saveCookies(false, false, $payment);
	}
		
	function setDelivery($delivery){
		$this->delivery = $this->session->basketDelivery = $delivery;
		$this->saveCookies(false, $delivery);
	}
	
	function getPaymentDetail(){
			return $this->deliveryOptions[$this->session->basketDelivery]['payments'][$this->session->basketPayment];
	}
	
	function getPayment($asText = false){  
		if($asText){
			return $this->deliveryOptions[$this->session->basketDelivery]['payments'][$this->session->basketPayment]['nazev'];
		} else {
			return $this->session->basketPayment;
		}
	}
	
	function getPayment2($delivery, $payment){
		return $this->deliveryOptions[$delivery]['payments'][$payment][0]; 
	}
	function getDelivery2($delivery){     
		return $this->deliveryOptions[$delivery]['nazev']; 
	}
	
	
	function getDeliveryPrice(){
		return $this->deliveryOptions[$this->session->basketDelivery]['payments'][$this->session->basketPayment][1];
	} /**/
	function getDeliveryPrice2(){
	
		foreach($this->deliveryOptions as $opt )
			if($opt['id'] == $this->session->basketDelivery)
			{
				$selected = $opt;
				break;
			}
			
		if($selected['zdarma_nad'] AND ($this->getSum() < $selected['zdarma_nad'])) // pokud zdarma_nad neni vyplneno, tak se to neni zdarma...
			return $selected['price'];
		else
			return 0;
		return $this->deliveryOptions[$this->session->basketDelivery]['payments'][$this->session->basketPayment][1];
	} 
	
	
	public function getDeliveryBasket()
	{
		return $this->session->basketDelivery;
	}
	
	
	// vrací z session před dokončením
	public function getPaymentBasket()
	{
		return $this->session->basketPayment;
	}
	
	/**
		pokud je doprava zdarma ( zdarma nad, ne cena 0kc), tak je i platba zdarma
	*/
	
	
	public function getPaymentPrice()
	{
		foreach($this->deliveryOptions as $opt )
			if($opt['id'] == $this->session->basketDelivery)
				$selected = $opt;
		
		foreach($selected['payments'] as $payment)
			if( $payment['id'] == $this->session->basketPayment )
				$selectedPay = $payment;
		
		if($selected['price'] AND !$this->getDeliveryPrice2())
			return 0;
		else
			return $selectedPay['cena'];
	}
	
	function getDelivery($asText = false){   
		if($asText){
			return $this->deliveryOptions[$this->session->basketDelivery]['nazev'];
		} else {
			return $this->session->basketDelivery;
		}
	}
	
	function getDeliveryDetail(){   
		return $this->deliveryOptions[$this->session->basketDelivery];
	}
	
	function setNote($note){
		$this->note = $this->session->note = $note;
	}
	
	function getNote(){
		return $this->session->note;
	}
	
	function removeCookies(){
		$_COOKIE['basket'] = null;
		$_COOKIE['delivery'] = null;
		$_COOKIE['payment'] = null;
	}
	
public function setBasketFromTemporary($id = false)
	{
		if($id){
			$serializeBasket = $this->db2->fetchRow('select * from '.$this->_tableTemporary.' where ip=:i and id=:ident',
			array('i'=>$_SERVER['REMOTE_ADDR'], 'ident'=>$id));
		}
		else{
			$serializeBasket = $this->db2->fetchRow('select * from '.$this->_tableTemporary.' where ip=:i',
			array('i'=>$_SERVER['REMOTE_ADDR']));
		}
		$basket = unserialize($serializeBasket['basket']);
		$this->session->basket = $basket;
		$cookie_data = $this->tempBasket;	
    	//$this->saveCookies($this->tempBasket);
    	//$this->session->idTempoBasket = $this->addTemporyBasket($serializeBasket['id'],$this->tempBasket);
    	return $basket;		
	}
	
	
	 	
	function addItem($item, $count = 1,$lastProdUrl,$view,$variantId,$optionId){
		$mVarianta = new module_Varianta();
		if(!is_object($item)){
			$tree = Zend_Registry::getInstance()->tree;
			$item = $tree->getNodeById($item);
		} 
		$variantId = $variantId ? $variantId : $view->input->variantId;
		$id = $item->nodeId;
		$mProd = new module_Products();
		$mMagnet = new module_ImportMagnet();
		$varianta = $mVarianta->getVariantsById($variantId);
		$view->input->variantId = $variantId;
		$view->input->optionId = $optionId;
		$view->input->variant = $varianta['title'];
		$newCount = $this->tempBasket[$id.'_'.$variantId.'_'.$optionId]['count'] + $count;
		if($item){						
			$this->tempBasket[$id.'_'.$variantId.'_'.$optionId] = array(
				'count' => $newCount,
				'item' => $item,
				'variantId' => $variantId,
				'price' => $varianta['price'],
				'optionId' => $view->input->optionId
			);   
		}
		$this->refreshDeliveries(); //kvuli hmotnosti
		$this->session->lastProdId = $item->nodeId;
		$this->session->variantId = $view->input->variantId;  
		$this->session->optionId = $view->input->optionId;
		$this->session->basket = $this->tempBasket;
		$this->session->basketItemsCount = $count;
		$cookie_data = $this->tempBasket;
		$this->saveCookies($this->tempBasket);
		$this->session->urlLsProduct = $item->path;
	}
	
	
	function saveCookies($basket = false, $delivery = false, $payment = false, $note = false)
	{
		if($basket){
			$this->setcookielive("basket", '', time()+36000000, '/', 'lie.specshop.cz');
			foreach ($basket as $key=>$value) {
				$cookie[$key]=$value['count'];
			};
			$saveCookie = base64_encode(serialize($cookie));
			$this->setcookielive("basket", $saveCookie, time()+36000000, '/', 'lie.specshop.cz');
		}
		if($delivery){
			$this->setcookielive('delivery',$delivery, time()+36000000, '/', 'lie.specshop.cz');
		}
		if($payment){
			$this->setcookielive('payment',$payment, time()+36000000, '/', 'lie.specshop.cz');
		}
		if($note){
			$note = base64_encode($note);
			$this->setcookielive('note',$note , time()+36000000, '/', 'lie.specshop.cz');
		}
	}
	
	function setcookielive($name, $value='', $expire=0, $path='', $domain='', $secure=false, $httponly=false) {
		//set a cookie as usual, but ALSO add it to $_COOKIE so the current page load has access
		$_COOKIE[$name] = $value;
		return setcookie($name,$value,$expire,$path,$domain,$secure,$httponly);
	}
	
	function removeItem($id){ 
		unset($this->tempBasket[$id]);	
		$this->session->basket = $this->tempBasket;
	}
	
	function changeCount($input){ 
		foreach ($input as $id => $count){
			if($count == 0){
				$this->removeItem($id);
				continue;
			}
			$this->tempBasket[$id]['count'] = $count;
		}
		//unset($this->tempBasket[$id]);	
		$this->session->basket = $this->tempBasket;
	}
	
	
	function getItems(){
		return $this->tempBasket;
	}
	
	function getItemsCount(){
		$count = 0;
		foreach ($this->tempBasket as $id => $data){
			$count += $data['count'];    
		}
		return $count;
	}
	
	function getSum($view,$deliveryPay = false){ 
		$sum = $this->calcPrice($this->tempBasket, $view);
		if($deliveryPay){
			$sum+=$this->getPricePayDel();
		}
		return $sum;
	}
	
	public function getPricePayDel()
	{
		$sum = $this->getDeliveryPrice2();
		$sum+= $this->getPaymentPrice();
		return $sum;
	}
	
	function calcPrice($products, $view){
		$sum = 0;   
		foreach ($products as $data){
			$price  = $data['price'];
			$sum += $data['count'] * $price; 
		} 
		
		$dph = $sum / (1+($view->dph/100)) * ($view->dph/100);   
		$dph = round($dph, 2);    
		$sumBezdph = $sum - $dph; 

		/*if($sumBezdph < 5500	){
			$deliverySum = $this->getDeliveryPrice();
			$sum += $deliverySum;    
		}*/
		  
		return $sum; 
		
	}
		
	function reinit(){		
    		$this->view->basketItems = $this->getItems();
    		$this->view->basketItemsCount = $this->getItemsCount();
    		$this->view->basketSum = $this->getSum();	
	}
	
	
	/* shopper */
	function setShopper($view, $input){		
		$secCode = date('Ym');   
    	unset($input->$secCode);
		unset($input->step); 
		
		$input->fullname = $input->fullname?$input->fullname:$input->jmeno . ' ' . $input->prijmeni;
		   
		//$err = $this->checkShopper($view, $input);
		$err = false; 
		if(!$err){    
			$this->session->shopper = $input;
	
			return true; 
		} else {
			return array(0, $err);
		}
	}
	
	function getShopper(){	
		return 	$this->session->shopper;
	}
	
	function removeShopper($view){  		
		$view->session->shopper = $this->session->shopper = $this->shopper = false; 
	}
	
	function checkShopper($view, $input){	 
		 
		 
    }
    
    function makeOrder($view){  
    	 $this->getItems = $view->mEshop->basket->getItems();
    	$shopper = $this->getShopper();  
    	 
    	
    	$tree = $view->tree;
    	$mEshop = $view->mEshop; 
    	
    	$delivery = $this->getDelivery();
    	$payment = $this->getPayment();
    	 
    	 
    	 
    	$deliverySum = $this->getDeliveryPrice2();
		$paymentSum = $this->getPaymentPrice();
    	
    	$shopperBasket = (array) $this->getShopper();
    
    	
    	$items = $this->getItems();
		$voucher = $this->voucher;
		$itemsCount = count($items);  
		$deliverySum = $this->getDeliveryPrice2();
		$paymentSum = $this->getPaymentPrice();   
		$mp = new module_Eshop_PriceCalc($voucher, $items, $deliverySum, $paymentSum); 
	 	
		$sum = $mp->sum;
		$sumPlatba = $mp->payment;
		$sumDoprava = $mp->delivery;
		$sumDph = $mp->dph;
		$sumBezdph = $mp->sumBezDph; 
		 
		 
		$shopperBasket['suma'] = $sum;
		$shopperBasket['payment'] = $payment; 
		$shopperBasket['delivery'] = $delivery;   
		$shopperBasket['deliverySum'] = $deliverySum;  
		$shopperBasket['paymentSum'] = $paymentSum;  
		
		$tmp =  $this->m_d->getDeliveryById($delivery);
		$shopperBasket['deliveryText'] =   $tmp['nazev'];
		$tmp =  $this->m_p->getPaymentById($payment);
		$shopperBasket['paymentText'] = $tmp['nazev'];  
		
		$payment = $this->getDelivery(true);   
		$shopperBasket['fu'] = $payment; 
		 
		$items = $fakItems = array();	 
		
		$cid = $view->webUser->id?$view->webUser->id:0;
		$parts = explode(' ', $shopperBasket['fullname']);
		$surname = array_pop($parts);
		$name = implode(' ', $parts);   
		
		$surname = $surname?$surname:''; 
		$name = $name?$name:$shopperBasket['fullname'];  
		$name = $name?$name:'';

		foreach ($shopperBasket as $k => $v){
			if(!strlen($v)){     
				$shopperBasket[$k] = '';   
			}    
		}
		$mCustomer = new module_Customer(false,false,false,$shopperBasket['email']);
		$mProd = new module_Products();
		$mMagnet = new module_ImportMagnet();
		if(!$cid){
			$cid = $mCustomer->id;
		}
		
		$data = array(
			'firstname' => $name,
			'surname' => $surname,
			'email' => $shopperBasket['email'],   
			'custid' => $cid,
			'firma' => $shopperBasket['ffirma'],
			'efullname' => $shopperBasket['ffullname'],
			'ulice' => $shopperBasket['fulice'],
			'mesto' => $shopperBasket['fMesto'], 
			'psc' => $shopperBasket['fpsc'],
			'stat' => 'cz',
			'fu_firma' => $shopperBasket['firma'],
			'fu_jmeno' => $shopperBasket['fullname'],  
			'fu_stat' => 'cz',
			'fu_ulice' => $shopperBasket['ulice'], 
			'fu_mesto' => $shopperBasket['mesto'], 
			'fu_psc' => $shopperBasket['psc'],
			'fu_ico' => $shopperBasket['ico'],
			'fu_dic' => $shopperBasket['dic'],
			'phone' => $shopperBasket['ftelefon'],
			'fu_phone' => $shopperBasket['telefon'],
			'suma' => $shopperBasket['suma'],
			'deliveryPrice' => $deliverySum,
			'paymentPrice' => $paymentSum,
			'deliveryText' => $shopperBasket['deliveryText'],
			'paymentText' => $shopperBasket['paymentText'],
			'payment' => $shopperBasket['payment'],
			'delivery' => $shopperBasket['delivery'],  
			'fu' => $shopperBasket['fu'],
			'note' => $this->getNote(), 
			'discountCode' => '' 
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
		 
		if($data['payment'] == 17){ //platba kartou
			$data['paid'] = '1';  
			$data['state'] = 'RPC';    
		}
		  
			 
    	foreach ($data as $k => $v){
			if(!strlen($v)){     
				$data[$k] = '';   
			}      
		}

		$this->db->insert(
			$this->_tableName, 
			$data 
		);	
		$oid = $this->db->lastInsertId();
		
		$zbozi = '';
		$trackItems = $hItems = '';
		foreach ($this->getItems as $key => $odata){
			$temp = explode('_', $key);
			$id = $temp[0];
			$varianta = $temp[1];
			$items = array(); 
    		$child = $odata['item'];  
			$count = $odata['count'];
			if(!$child){ continue; } 
			$c = $child->getPublishedContent(); 
			$price  = $odata['price'];     
			$sum = $price * $count;   
    		$items['o_id'] = $oid;    	
    		$items['count'] = $count;    		
    		$items['title'] = $child->title;    	
    		$items['dph'] = $view->dph;      
    		$items['variant'] = $varianta;
    		$items['itemNodeId'] = $child->nodeId;   
    		$items['price'] = $price; 	 
    		$items['content'] = serialize($c);    	 
			$zbozi .= '&produkt[]=' . urlencode($child->title);


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
    		
    		$hItems .= "
    		_hrq.push(['addProduct', '" . $child->title . "', '" . $items['price'] . "', '" . $odata['count'] . "']);
    		";
			
    	 		//}
			if($_SERVER["REMOTE_ADDR"] == '147.32.120.238'){
				//print_r($fakItems);
				//die;  
			}
			$dph = $sumDph;
			
			$priceBez = $sum;
		$trackItems .=<<<EOD
		_gaq.push(['_addItem',
			'$oid',           // order ID - required
			'{$child->nodeId}',           // SKU/code - required
			'{$child->title}',        // product name
			'',   // category or variation
			'$priceBez',          // unit price - required
			'$count'               // quantity - required
		  ]); 
EOD;
		}
		      
		$u = 'http://www.heureka.cz/direct/dotaznik/objednavka.php?id=25657ad08dde3e3f69ce190f0fc56664&email=' . $data['email'] . $zbozi . '&orderid=' . $oid;  
		file($u);
	 	 
		$trans = $deliverySum+$paymentSum;
		
		$nl = "/n";
		$sum = $this->getSum($view);
		
		$code = <<<EOD
		  _gaq.push(['_addTrans',
			'$oid',           // order ID - required  
			'eshop-povleceni.cz',  // affiliation or store name
			'{$sumBezdph}',          // total - required
			'{$trans}',           // tax
			'',              // shipping
			'{$data['mesto']}',       // city
			'',     // state or province
			'{$data['stat']}'             // country
		  ]);
EOD;
		
		 
		$submit =<<<EOD
		_gaq.push(['_trackTrans']); //submits transaction to the Analytics servers
EOD;
		
		//$_SESSION['ecommerce'] = $code."\n".$trackItems."\n".$submit;
		/*
		$_SESSION['googleOrder'] ='<script type="text/javascript">

var google_conversion_id = 995776401;
var google_conversion_language = "cs";
var google_conversion_format = "3";
var google_conversion_color = "ffffff";
var google_conversion_label = "uyk4CIftlwQQka_p2gM";
var google_conversion_value = '.$sum.';
</script>
<script type="text/javascript" src="http://www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="http://www.googleadservices.com/pagead/conversion/995776401/?value=1&amp;label=uyk4CIftlwQQka_p2gM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>';
		
		$_SESSION['herurekaKonverze'] ="
		<script type=\"text/javascript\">  
		var _hrq = _hrq || [];
		    _hrq.push(['setKey', 'B23637C586494D916DCD5D81E30C3BA9']);
		    _hrq.push(['setOrderId', '$oid']); 
		    $hItems   
		    _hrq.push(['trackOrder']); 
		
		(function() {
		    var ho = document.createElement('script'); ho.type = 'text/javascript'; ho.async = true;
		    ho.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.heureka.cz/direct/js/cache/1-roi-async.js';
		    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ho, s);
		})();
		</script>
		";
		*/  
		
		
		// 1 = vyzva, 0 = zaplacena vyzva, 5 = bez vyzvy
		   
		$vyzva = -1;          
		//print_r($data);
		//die;
		$faktura = $view->mFakturace->addFaktura($view, $data, $vyzva, $fakItems, 0, $oid );
 		
		$textNode = $view->tree->getNodeById(5198);  
		$subject = $textNode->title; 
		$mainText = $textNode->getHtml();   

		$view->payment = $payment = $this->m_p->getPaymentById( $shopperBasket['payment']);
		$basketItems = $view->render('templates/Eshop/ItemsListEmail2.phtml');			
		
		if($data['note']){     
 			 $basketItems .= '      
		 		 <h2>Poznámka</h2>  
		 		 <p>' . nl2br($data['note']) . '</p>  
 		 	'; 
 		}  
 		 
		if(($shopperBasket['payment'] == 16)){
 		 $basketItems .= '    
 		 <h2>Informace o platbě</h2>   
 		 <p>Platbu prosím uhraďte na účet 255776361/0300 s variabilním symbolem: ' . $oid . '. <br />
			Jakmile bude částka připsána na náš účet, zboží bude odesláno.  
 		  </p> ';  
 		}        
 		 
		$payment = $this->m_p->getPaymentById( $shopperBasket['payment'] );
		//if()
		if($payment['useEmailOR'])
		{
			$basketItems .= $payment['emailOrderReceived'];
		}  
		
		if(!$mCustomer->regFormBasket){
			$mainText = str_replace('<p>[login]</p>', '', $mainText);	
		}
		else{
			$login = 'Vaše přihlašovací údaje pro další nákup:';
			$login = '<table style="font-size: 11px;">
						<tbody>
							<tr>
								<td>uživatelsk&eacute; jm&eacute;no:</td>
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
		
		$mainText = str_replace('[EmailKosik]', $basketItems, $mainText);
		
		
		$replacements = array(    
			'[CisloObjednavky]' => $oid,  
			'[Domena]' => $view->domainTitle, 
			'[WWW]' =>  'www.eshop-povleceni.cz/',   
			'[Email]' => 'obchod@eshop-povleceni.cz',
			'[Telefon]' => '734 767 038', 
			'[OtevrenoOd]' => '9:00', 
			'[OtevrenoDo]' => '17:00'
		);
		
		foreach ($replacements as $ident => $substitute){
			$mainText = str_replace($ident, $substitute, $mainText);
		} 
    	foreach ($replacements as $ident => $substitute){
			$subject = str_replace($ident, $substitute, $subject);
		}
        $mail = new Email(); 
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
		$mail->setSubject($subject);		
		
	
		$s = Zend_Registry::getInstance()->settings;
		
		$mail->addTo($data['email'], $data['firstname'] . ' ' . $data['surname']);
		//$mail->addBcc('loucka@eportaly.cz', 'Radek');     
		$mail->addBcc('orlik@eportaly.cz');           
		//$mail->addBcc('nosil@eportaly.cz', 'misa', 'misa'); 
		$mail->addBcc($s->getSettingValue('ordersEmail'), str_replace('&', '&amp;', $s->getSettingValue('ordersEmailName')));		
		     
		$mail->setFrom($s->getSettingValue('ordersEmail'), $s->getSettingValue('ordersEmailName'));
			   
		try {   
			$mail->send();     
		} catch (Exception $e) { } 
	    
		$this->session->basket = $this->tempBasket = null;	
		if($this->voucher['id_v']) {
			$view->mVoucher->addOrder($this->voucher['id_v'],$oid);
			$view->mVoucher->useVoucher($this->voucher['id_v']);
		}
		$this->voucher = $this->session->voucher = null; 

		return $oid;   
    }
    
    
    function setBasketFromCookie($view){
    	$cookie = unserialize(base64_decode($_COOKIE['basket']));
    	if($_COOKIE['delivery']){
    		$this->setDelivery($_COOKIE['delivery']);
    	}
    	if($_COOKIE['payment']){
    		$this->setPayment($_COOKIE['payment']);
    	}
    	if($_COOKIE['note']){
    		$this->setNote(base64_decode($_COOKIE['note']));
    	}
    	foreach ($cookie as $key=>$value) {
    		$produkt = explode('_', $key);
    		$this->addItem($produkt[0], $value, false, $view, $produkt[1]);
    	}
    	return $cookie;
    }
    

function getPaymentsById($idDelivery)
	{
		$delivery = $this->deliveryOptions; 
		$payments = array();
		if(!$idDelivery)
		{
		foreach ($delivery as $value) {
				$idDelivery = $value['id'];
				break;
			}
		}
		foreach ($delivery as $value) {
			if($value['id'] == $idDelivery)
			$payments = $value['payments'];
		}	
		return $payments;
	}
		
    function emptyBasket(){ 
    	$view->session->step = null;
    	$this->session->basket = $this->tempBasket = null;	
		//$this->voucher = $this->session->voucher = null;
    } 
	
	function emptyVoucher(){ 
    	//$this->session->basket = $this->tempBasket = null;	
		$this->voucher = $this->session->voucher = null;
    }
	
	public function addVoucher ($voucher){
		$this->voucher = $this->session->voucher = $voucher;
	}
}
?>