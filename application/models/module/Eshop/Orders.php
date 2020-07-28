<?

use Zend\XmlRpc\Request\Stdin;

/**
 * Třída reprentuje nakupni kosik
 */
class module_Eshop_Orders {	
			
	public $_tableName = 'module_eshop_orders';
	public $_tableItemsName = 'module_eshop_order_items'; 
	public $_tableNameFaktura = 'module_faktura'; 
	
	public $tStats = 'module_eshop_orders_stats';
	public $tHistory = 'module_eshop_orders_history';  
	
	private $_states = array(
			'DEL' => 'Smazaná',
			'CHANGE' => 'Upravená',
			'WAIT' => 'Nevyřízená',
			'TEXTED' => 'Zadaná',
			'WPAY' => 'Čeká na platbu',
			'SEND' => 'Odesláno',
			'CANCEL' => 'Stornována'
			);
	
	public function __construct($basket, $view = null) {
		$this->basket = $basket; 
		$this->db = Zend_Registry::getInstance()->db;	 
		$settings = Zend_Registry::getInstance()->settings;
		$this->tree = Zend_Registry::getInstance()->tree;
		
		if($view){
			$this->view = $view;		
		}
		$this->dph = $settings->getSettingValue('dph');
		$this->tempBasket = $this->view->session->basket;
		if(!is_array($this->tempBasket)){
			$this->tempBasket = $this->view->session->basket = array();
		}
		
		$this->webUser = Zend_Registry::getInstance()->session->webUser;
		
		// pr($this->webUser); 
		//$this->initDeliveryTypes();
	}
	
	public function getCustomerByEmail($email)
	{
		if($email)   
		{
			$select = $this->db->select();
            $select->from(array( 'cm' => $this->_tableName), array('*'));   	
            $select->where('email = ?', $email);
			$select->order('id desc')  ;
			$select->limit(1);
			$data = $this->db->fetchRow($select);  
    		return json_encode($data );      
		}
	}
	
	
	public function showPrintableFa($view,$idOrder)
	{
 		$view->order = $this->getOrder($idOrder);  
 		$mDelivery = new module_Delivery();
 		$view->allDeliveries = $mDelivery->showValidDeliveries();
 		$view->selectedPayment = $mDelivery->showValidPayments($this->view->order['delivery']);
 		$view->items = $this->getItemsOrder($idOrder); 
 		$view->total = $this->getTotalSum($view->items,$view->order,true);
		return array($view->order,$view->items,$view->total);
	}
	
	public function renderReverseOrders()
	{
		$all = $this->db->fetchAll("select id from ".$this->_tableName." where unsetGADate IS NULL and  unsetGA =?",'1');
		if($all){  
			foreach ($all as $key => $value) {  
			  $orders[] = $this->renderReverseOrder($value['id']);
			}
			return implode('\n', $orders);
		}  
	}
	 
	private function renderReverseOrder($id) 
	{
		$data['unsetGADate'] = date("Y-m-d H:i:s");
		$where = $this->db->quoteInto('id = ?', $id); 
		$this->db->update(
			$this->_tableName, 
			$data,
			$where
		);	 
			$order = $this->getOrder($id);
			$items = $this->getOrderDetails($id);   
			$trans = $order['deliveryPrice'] + $order['paymentPrice']; 
			foreach ($items as $item) {
				$count += $item['count'];
				$dph += $item['dph'];

			$celkemDph = round($dph/$count);
			}
			$dph = $sum / (1+($celkemDph/100)) * ($celkemDph/100);   
			$dph = round($dph, 2);     
	    	$sumBezdph = $order['suma'] - $dph; 
			
		//return $codeRequire."\n".$code."\n".$trackItems."\n".$submit; 
		
	}
	
	public function getTotalSum($items, $order,$totalSum = false)
	{
		foreach ($items as $item){
			$itemsPrice = $item['price']*$item['count'];
			$sum += $itemsPrice;
			$dph += round( ($itemsPrice / (1 + ($item['dph']/100)) * ($item['dph']/100)), 2);
			$sumItems += ($item['price']*$item['count']); 
		}
		
		

	
		$sumBezDph = round($sumItems)  -  ceil($dph);
		if($totalSum){
			$sum += ($order['paymentPrice'] + $order['deliveryPrice']);
			$d = $order['paymentPrice'] + $order['deliveryPrice'];
			$deliveryBezDPH = round( ($d / (1 + ($this->dph/100)) * ($this->dph/100)), 2);
			$sumTotalBezDph = $sumBezDph + ($d-$deliveryBezDPH);

		}

		return array($sumBezDph, ceil($dph),$sum,$this->dph,$sumTotalBezDph);
		
		//die();
	}
	
	
	public function getTotalSum2($order)
	{
		return ($order['suma'] + $order['paymentPrice'] + $order['deliveryPrice']);
	}
	
	
	
	public function sendEmail($type,$order,$view, $sendTo = false)
	{
		switch ($type){	
			case 'orderSentEmail':
				$nodeId = 73348;
			break;
			case 'orderAdjustedEmail':  
				$nodeId = 73324;
			break;
			case 'orderStornoEmail':
				$nodeId = 73350;
			break;
			case 'orderWaitPayEmail':
				$nodeId = 73349;
			break;	
			case 'TEXTED':
				$nodeId = 73798;
			break;	
		}
		$textNode = $this->tree->getNodeById($nodeId);

		$subject = $textNode->title;
		$mainText = $textNode->getHtml();

		$view->order = $this->getOrder($order['id']);

		$view->items = $this->getItemsOrder($order['id']); 
		//$this->view->basket = $this->mEshop->basket;

		list($sumBezPDH,$dph,$sum,$dphQuote,$sumTotalBezDph) = $this->getTotalSum($view->items,$view->order,true);
		$config =  Zend_Registry::getInstance()->config;
		$view->sumOrder = $sum;
		$view->sumOrderBezDPH = $sumTotalBezDph;
		$basketItems = $view->render('templates/Eshop/ItemsListEmail.phtml');
		$mainText = str_replace('[emailKosik]', $basketItems, $mainText);
		//$name,
		//'surname' => $surname,
		if($order['fu_dic'])
		{
			$d = 'IČO: '.$order['fu_dic'];
		}
		
		$addToEmail = '';   
		if($order['ulice']) 
		{  
			$addToEmail = '<p style="margin: 0; margin-bottom: 5px; clear: left;"><strong>Dodací údaje</strong></p>
<p style="margin: 0; margin-bottom: 5px; margin-left: 5px;">'.$order['firma'].'</p>
<p style="margin: 0; margin-bottom: 5px; margin-left: 5px;">'.$order['ffullname'].'</p>
<p style="margin: 0; margin-bottom: 5px; margin-left: 5px;">'.$order['ulice'].'</p>
<p style="margin: 0; margin-bottom: 5px; margin-left: 5px;">'.$order['mesto'].'</p>
		<p style="margin: 0; margin-bottom: 5px; margin-left: 5px;">'.$order['psc'].'</p>
<p style="margin: 0; margin-bottom: 5px; margin-left: 5px;">'.$order['phone'].'</p>';
		}
		 
		
		$view->phoneNum = $view->settings->getSettingValue('phoneNum');
		$view->dphSettings = $view->settings->getSettingValue('dph');
		$order['ico'] = 'IČO: '.$order['fu_ico'] ? $order['fu_ico'] : $order['ico'];
	//	$order['fu_jmeno'] = $order['fu_firma'] ? $order['fu_firma'] : $order['fu_jmeno'];
	
	
		$replacements = array(
				'[dateCreate]' => Utils::formatDate(date('Y-m-d H:i:s')),
				'[domainName]' => $config->instance->title,
				'[varSym]'=> $order['id'],
				'/[domainUrl]' => Utils::getWebUrlHttp(), 
				'[customerName]' => $order['firstname'].' '.$order['surname'],
				'[customerNameFa]' => $order['fu_jmeno'],
				'[customerTel]' => $order['fu_phone'],
				'[streetName]' => $order['fu_ulice'],
				'[city]' => $order['fu_psc'].' '.$order['fu_mesto'],
				'[CisloObjednavky]'=> $order['id'],
				'[priceTotal]'=> helper_FrontEnd::price( $sum).' Kč',
				'[payment]' => $order['paymentText'],
				'[firmaFa]' => $order['fu_firma'],
				'[ico]' => $order['ico'],
				'[email]' => $order['email'],
				'[dic]' => $d,
				'[note]' => $order['note'],
				'[firma]' => $order['firma'],
				'[phoneCom]' => $view->phoneNum,
				 '[showEmail]' => $view->order['email'],
				'[dorucovaci]' => $addToEmail,
				'[phoneCom]' => $view->phoneNum,
		);
		$view->deliveryPrice = $order['deliveryPrice'];
		$view->paymentPrice = $order['paymentPrice'];
		$view->deliveryText = $order['deliveryText'];
		$view->paymentText = $order['paymentText'];
		foreach ($replacements as $ident => $substitute){
			$subject = str_replace($ident, $substitute, $subject);
		}
		// sejme varSymbol
		if($order['payment'] != 16){
			$temp = explode('<tr id="varSym" style="margin-left: 0; padding-left: 0;">',$mainText);
			//pr($temp);
			$temptt = explode('<tr id="price"',$temp[1]);
			$mainText = $temp[0].'<tr id="price"'.$temptt[1];
		}
		
		foreach ($replacements as $ident => $substitute){
			$mainText = str_replace($ident, $substitute, $mainText);
		}
		foreach ($replacements as $ident => $substitute){
			$subject = str_replace($ident, $substitute, $subject);
		}
		

		$mainText = str_replace('<p style="margin: 0; margin-bottom: 5px; margin-left: 5px;"></p>','',$mainText);
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
		if($sendTo){
			$mail->addTo($sendTo, 'objednavka','objednavka');
			}
		else{
			$mail->addTo($view->order['email'], $view->order['firstname'] . ' ' . $view->order['surname']);
		}
		//$mail->addBcc('loucka@eportaly.cz', 'Radek');           
		$mail->addBcc('prodej@specshop.cz', 'prodej@specshop.cz');  
	///	$mail->addBcc($s->getSettingValue('ordersEmail'), str_replace('&', '&amp;', $s->getSettingValue('ordersEmailName')));		
		     
		$mail->setFrom($s->getSettingValue('ordersEmail'), $s->getSettingValue('ordersEmailName'));
	   
		try {   
			$mail->send();     
		} catch (Exception $e) { }
	}
	
	public function getState($textState)
	{
		return $this->_states[$textState];
	}
	
	function logAction($oid, $action, $state, $user){
		$item = array(
			'oid' => $oid,
			'action' => $action,   
			'message' => $state,  
			'user' => $user,
			'added' => new Zend_Db_Expr('NOW()')
		);   
		$this->db->insert(
			$this->tHistory,
			$item  
		);
	}
	
	function getOrderHistory($order){
		  
		$oid = $order['id']; 
		$trans = array(
			'orderRecievedPayment' => 'Přijata platba',
			'orderReady' => 'Objednávka odeslána na prodejnu', 
			'orderNone' => 'Objednávka vrácena do nevyřešeného stavu',
			'orderDelete' => 'Objednávka smazána',
			'orderReklamace' => 'Objednávka reklamována',
			'orderPending' => 'Nevyřízeno',
			'orderAdjusted' => 'Upraveno',
			'orderSent' => 'Odesláno',
			'orderStorno' => 'Stornovat',
			'orderWaitPay' => 'Čeká na platbu (pro platbu převodem)'
		);
		
		$all = $this->db->fetchAll("SELECT * FROM `" . $this->tHistory . "` WHERE oid = ? ORDER BY id ASC", $oid);
		$new = array(
			0 => array(  
				'added' => Utils::formatDate($order['created'], 'd.m.Y H:i'),
				'message' => 'Objednávka přijata / objednáno'
			)  
		);
		
		foreach ($all as $i => $o){
			$o['added'] = Utils::formatDate($o['added'], 'd.m.Y H:i');
			$o['message'] = $trans[$o['action']];
			$new[] = $o;  
		} 
		 
		return $new;
	}
	
	function getMostSellItems($limit){	 	 
		return  $this->db->fetchAll("SELECT count(`id`) as c, `title`, itemNodeId FROM `" . $this->_tableItemsName . "` GROUP BY `itemNodeId` ORDER BY c DESC LIMIT 0, $limit");	      
	} 
	
	function setOrderStatus($id, $status = ''){
		$data = array(
			'state' => $status
		); 
		$where = $this->db->quoteInto('id = ?', $id); 		
		$this->db->update($this->_tableName, $data, $where);
	}
	
		/**
		updatuje hodnotu jednoho atributu
		@var id id objednavky
		@var name jmeno sloupce v DB
		@var value nova hodnota
	*/
	public function setValue($id, $name, $value)
	{
		$data = array(
			$name => $value
		); 
		$where = $this->db->quoteInto('id = ?', $id); 		
		$this->db->update($this->_tableName, $data, $where);
	}

	function addNote($oId, $data){
		$data = (array) $data;   
		$data = array(
			'adminNote' => $data['adminNote']
		);
		$where = $this->db->quoteInto('id = ?', $oId); 
		$this->db->update(
			$this->_tableName, 
			$data,
			$where
		);	 
	}
	
	function changeOrder($oId, $data ){
		$orders = $this->db->fetchAll('select * from '.$this->_tableItemsName.' where o_id = ?',$oId);
		$sum = 0;
		foreach ($orders as $value) {
			$c = 0;
			$c = $value['price'] * $value['count'];
			$sum += $c;
		}

		$mPayment = new module_Payment();
		$mDelivery = new module_Delivery();
		$data = (array) $data;
		$delivery = $mDelivery->getDeliveryById($data['delivery']);
		$payment = $mPayment->getPaymentById($data['payment']);
		$data = array(
			'firstname' => $data['firstname'],
			'surname' => $data['surname'],
			'efullname' => $data['efullname'],
			'fu_firma' => $data['fu_firma'], 
			'ulice' => $data['ulice'],
			'mesto' => $data['mesto'],
			'psc' => $data['psc'], 
			'fu_ulice' => $data['fu_ulice'],
			'fu_mesto' => $data['fu_mesto'],
			'fu_psc' => $data['fu_psc'],
			'fu_ico' => $data['fu_ico'],
			'fu_phone' => $data['fu_phone'],
			'phone' => $data['phone'],
			'fu_dic' => $data['fu_dic'],
			'fu_jmeno' => $data['fu_jmeno'],
			'stat' =>  $data['stat'],
			'email' =>  $data['email'],
			'fu_stat' =>  $data['fu_stat'],
			'paymentPrice' =>  $data['paymentPrice'],
			'deliveryPrice' =>  $data['deliveryPrice'],
			'paymentText' => $payment['nazev'],
			'deliveryText' => $delivery['nazev'],
			'payment' => $data['payment'],
			'delivery' => $data['delivery'],
			'suma' => $sum
		); 
		$where = $this->db->quoteInto('id = ?', $oId); 
		$this->db->update(
			$this->_tableName, 
			$data,
			$where
		);	
// 	pr($data);
// 	die();
	}
	
	public function getPreorders(){
		/*$select = $this->db->select();
		$select->from(array('p' => 'content_Product'),array('o_id'));
		$select->where('skladem = 4');
		$select->join(array('o' => 'module_eshop_order_items'))*/
		//$sql = 'SELECT * FROM module_eshop_orders WHERE id IN(SELECT o.o_id FROM content_Product p JOIN (NodesContents n, module_eshop_order_items o) ON (p.id=n.c_id AND o.itemNodeId=n.n_id) WHERE p.skladem=4) AND state NOT IN("RPDCP", "RPDGP", "RPDPPL", "RPOOO")';
		$sql = 'SELECT o.o_id FROM content_Product p JOIN (NodesContents n, module_eshop_order_items o) ON (p.id=n.c_id AND o.itemNodeId=n.n_id)';
		$stmnt = $this->db->query($sql);
		$res = $stmnt->fetchAll();
		return $res;
	}
	
	public function getOrders($state = 'nf', $sort = 'created', $sortType = 'desc', $limitStart = 0, $limitCount = 150)
    {
    	$select =  $this->db->select();
    	$select->from(array('u' => $this->_tableName), array( '*'));
 /*
    	 $select->join(
			array('f' => $this->_tableNameFaktura),
        	"u.id = f.oid",   
        	array('ident' => 'vs', 'oid', 'sum')   
        );   */
        $select->where('state != ?', 'DELETED');  
		$select->where('custid = ?', $this->webUser->id);   
	 
		switch ($state){
			default:
			case 'on': 
				$select->where("state = '' OR state = 'RPBCP' OR state = 'RPBGP' OR state = 'RPC'");   
				break;  
			case 'onall':    
				$select->where("state = '' OR state = 'RPBCP' OR state = 'RPBGP' OR state = 'WPBCP' OR state = 'WPDCP' OR state = 'WPDGP' OR state = 'WPDGP' OR state = 'WPDPPL' OR state = 'WPOOO'");  
				break;      
			case 'ov':   
				$select->where("state = 'RPDCP' OR state = 'RPDGP' OR state = 'RPDPPL' OR state = 'RPOOO'");  
				break;  
			case 'wo':     
				$select->where("state = 'WPBCP' OR state = 'WPDCP' OR state = 'WPDGP' OR state = 'WPDGP' OR state = 'WPDPPL' OR state = 'WPOOO'");  
				break;    
			case 'os':
				$select->where('state = ?', 'SS'); 
				break;   
			case 'all':  
				break;  
		}  
		
		$sortType = $sortType?$sortType:'Asc';
		$select->order($sort . ' ' . $sortType); 
		$select->limit($limitCount, $limitStart);
		
		
		//  e($select->__toString());   
		$all = $this->db->fetchAll($select);		
		$orders = array();
		foreach ($all as $id => $u){
			$orders[$id] = $u; 
		}
		return $orders; 
		
    }
    
	/*public function getOrdersBackend($state = 'nf', $deliveryType = 'all', $sort = 'created', $sortType = 'desc', $limitStart = 0, $limitCount = 1500, $params = array())
    {
    	$select =  $this->db->select();
		if($params['cnt']){
		$c =  new Zend_Db_Expr("count('*')"); 
    	$select->from(array('o' => $this->_tableName), "count('*') as cnt") ; 
		}
		else{
			$select->from(array('o' => $this->_tableName), array( '*')); 
    	}   	
    	$select->join(
			array('f' => $this->_tableNameFaktura),
        	"o.id = f.oid",  
        	array('ident' => 'vs', 'oid', 'sum', 'fid' => 'f.id')   
        );  
          
        if($deliveryType != 'all'){
        	if($deliveryType == 'czDobirka'){
        		 $select->where("payment = ? AND delivery != 'OO'  ", 1);
        	} 
        	if($deliveryType == 'bank'){
        		 $select->where("payment = ? AND delivery != 'OO'  ", 2);  
        	}   
        	 
       		if($deliveryType == 'direct'){
        		 $select->where("payment = ? AND delivery = 'OO'  ", 1);  
        	}
        	
        }
        
        if($params['day']){    
    		$select->where('DATE(o.created) = ?', $params['day']);	
    	} 	
    	 	 		
		switch ($state){
			default:
			case 'on': 
				$select->where("state = '' OR state = 'RPBCP' OR state = 'RPBGP' OR state = 'RPC'");  
				break;   
			case 'or':    
				$select->where("state = 'COMP'");  
				break;  
			case 'ov':  
				$select->where("state = 'RPDCP' OR state = 'RPCS' OR state = 'RPDGP' OR state = 'RPDPPL' OR state = 'RPOOO'  OR state = 'WPDPPL' OR state = 'WPOOO' OR state = 'WPDGP'");  
				break;   
			case 'onall':     
				$select->where("state != '' OR state != 'RPBCP' OR state != 'RPBGP'");  
				break;  
			case 'wo':       
				$select->where("state = 'WPBCP' OR state = 'WPDCP' OR state = 'WPBGP'");  
				break;    
			case 'os':
				$select->where('state = ?', 'SS'); 
				break;   
			case 'all':  
				break;  
		}
		
		
		if(!$params['cnt']){
		$sortType = $sortType?$sortType:'Asc';
		$select->order($sort . ' ' . $sortType); 
		$select->limit($limitCount, $limitStart);
		}
		
		if($params['month']){
			$select->where('MONTH(o.`created`) = ?', $params['month']);
			$select->where('YEAR(o.`created`) = ?', $params['year']);
		} elseif ($params['year']){
			$select->where('YEAR(o.`created`) = ?', $params['year']);
		}   
		 
		if($params['search']){
			$select->where('
				firstname LIKE ? OR surname LIKE ? OR email LIKE ? OR efullname LIKE ?  OR phone LIKE ? 
				OR ulice LIKE ? OR mesto LIKE ? OR psc LIKE ? OR fu_firma LIKE ? OR fu_jmeno LIKE ? OR fu_ulice LIKE ?
				OR fu_mesto LIKE ? OR fu_psc LIKE ? OR fu_ico LIKE ? OR fu_dic LIKE ? OR vs LIKE ?  
				', '%' . $params['search'] . '%'); 		
		}
		if($params['ids']){
			$ids="";
			foreach($params['ids'] as $key => $subArr){
				$params['ids'][$key] = $subArr['o_id'];
			}
			$ids = implode(',',$params['ids']);
			$select->where("o.id IN (".$ids.")");
		}
		
				if($params['notids']){
			$ids="";
			foreach($params['notids'] as $key => $subArr){
				$params['notids'][$key] = $subArr['o_id'];
			}
			$ids = implode(',',$params['notids']);
			$select->where("o.id NOT IN (".$ids.")");
		}
		 
		//    e($select->__toString());    
		$all = $this->db->fetchAll($select);		
		$orders = array();
		foreach ($all as $id => $u){ 
			$orders[$id] = $u;  
		}
		if($params['cnt']){
			return $orders[0][cnt];
		}
		return $orders; 
		
    }*/
		
    
		
	public function getOrdersBackend($state = '', $deliveryType = '0', $sort = 'id', $sortType = 'desc', $limitStart = 0, $limitCount = 1500, $params = array())
    {

    	$select =  $this->db->select();
		if($params['cnt']){
		$c =  new Zend_Db_Expr("count('id')"); 
    	$select->from(array('o' => $this->_tableName), "count('id') as cnt") ; 
		}
		else{
			$select->from(array('o' => $this->_tableName), array( '*')); 
    	}   	
    	$select->joinLeft(
			array('f' => $this->_tableNameFaktura),
        	"o.id = f.oid",  
        	array('ident' => 'vs', 'oid', 'sum', 'fid' => 'f.id')   
        );  
		$select->joinLeft(
		array('p' => 'module_eshop_Payment'),
				'o.payment = p.id',
				array('paymentFirst')
		);
          
        if($deliveryType > 0){
        		 $select->where("o.payment= ? ", $deliveryType);
        	
        } 
		$select->where('state != ?', 'DELETED');
        if($params['day']){    
    		$select->where('DATE(o.created) = ?', $params['day']);	
    	} 	
    	
    	if(isset($params['start'])){
    		$select->where('DATE(o.created) >= ?', $params['start']);
    	}
    	 
    	if(isset($params['stop'])){
    		$select->where('DATE(o.created) <= ?', $params['stop']);
    	}
    	
   		if($state)
   		{
   			$select->where('state = ?', $state);
   		}
		 
		if(!$params['cnt']){
		$sortType = $sortType?$sortType:'Asc';
		$select->order($sort . ' ' . $sortType); 
		$select->limit('id desc');
		}
		
		if($params['month']){
			$select->where('MONTH(o.`created`) = ?', $params['month']);
			$select->where('YEAR(o.`created`) = ?', $params['year']);
		} elseif ($params['year']){
			$select->where('YEAR(o.`created`) = ?', $params['year']);
		}   
		
		 if($params['search']){
			$select->where('
				firstname LIKE ? OR surname LIKE ? OR email LIKE ? OR efullname LIKE ?  OR phone LIKE ? 
				OR ulice LIKE ? OR mesto LIKE ? OR psc LIKE ? OR fu_firma LIKE ? OR fu_jmeno LIKE ? OR fu_ulice LIKE ?
				OR fu_mesto LIKE ? OR fu_psc LIKE ? OR fu_ico LIKE ? OR fu_dic LIKE ? OR vs LIKE ?  
				', '%' . $params['search'] . '%'); 		
		}
		if($params['ids']){
			$ids="";
			foreach($params['ids'] as $key => $subArr){
				$params['ids'][$key] = $subArr['o_id'];
			}
			$ids = implode(',',$params['ids']);
			$select->where("o.id IN (".$ids.")");
		}
		
				if($params['notids']){
			$ids="";
			foreach($params['notids'] as $key => $subArr){
				$params['notids'][$key] = $subArr['o_id'];
			}
			$ids = implode(',',$params['notids']);
			$select->where("o.id NOT IN (".$ids.")");
		}
		if(!$params['cnt']){
			$select->group('o.id');
		}
		
		           
    		$select->where('DATE(o.created) >= ?', '2017-12-1 00:00:00');
        
			         
		$all = $this->db->fetchAll($select);		
		//pr($all);
		//print_r($all);
		$orders = array();
		foreach ($all as $id => $u){     
			$orders[$id] = $u;  
		}
		if($params['cnt']){
			return $orders[0][cnt];
		}
		return $orders;    
		
    }
		
    
	public function getOrdersBackendCount($state = 'nf', $deliveryType = 'all', $sort = 'created', $sortType = 'desc', $limitStart = 0, $limitCount = 1500, $params = array())
    {
    	$select =  $this->db->select();
    	$select->from(array('o' => $this->_tableName), "count('o.id') as cnt") ; 
	  	
    	$select->joinLeft(
			array('f' => $this->_tableNameFaktura),
        	"o.id = f.oid",  
        	array()   
        );  
		$select->joinLeft(
		array('p' => 'module_eshop_Payment'),
				'o.payment = p.id',
				array()
		);
          
        if($deliveryType > 0){
        		 $select->where("o.payment= ? ", $deliveryType);
        	
        }
        
		$select->where('state != ?', 'DELETED');

        if($params['day']){    
    		$select->where('DATE(o.created) = ?', $params['day']);	
    	} 	
    	
    	if(isset($params['start'])){
    		$select->where('DATE(o.created) >= ?', $params['start']);
    	}
    	 
    	if(isset($params['stop'])){
    		$select->where('DATE(o.created) <= ?', $params['stop']);
    	}
    	
   		if($state)
   		{
   			$select->where('state = ?', $state);
   		}
		
		
		
		if($params['month']){
			$select->where('MONTH(o.`created`) = ?', $params['month']);
			$select->where('YEAR(o.`created`) = ?', $params['year']);
		} elseif ($params['year']){
			$select->where('YEAR(o.`created`) = ?', $params['year']);
		}   
		
		 if($params['search']){
			$select->where('
				firstname LIKE ? OR surname LIKE ? OR email LIKE ? OR efullname LIKE ?  OR phone LIKE ? 
				OR ulice LIKE ? OR mesto LIKE ? OR psc LIKE ? OR fu_firma LIKE ? OR fu_jmeno LIKE ? OR fu_ulice LIKE ?
				OR fu_mesto LIKE ? OR fu_psc LIKE ? OR fu_ico LIKE ? OR fu_dic LIKE ? OR vs LIKE ?  
				', '%' . $params['search'] . '%'); 		
		}
	$select->where('DATE(o.created) >= ?', '2017-12-1 00:00:00');  
		if($params['ids']){   
			$ids="";
			foreach($params['ids'] as $key => $subArr){
				$params['ids'][$key] = $subArr['o_id'];
			}
			$ids = implode(',',$params['ids']);
			$select->where("o.id IN (".$ids.")");
		}
		
				if($params['notids']){
			$ids="";
			foreach($params['notids'] as $key => $subArr){
				$params['notids'][$key] = $subArr['o_id'];
			}
			$ids = implode(',',$params['notids']);
			$select->where("o.id NOT IN (".$ids.")");
		}

			$select->group('o.id');
			
			$all = $this->db->fetchAll($select); 				   
			
		
	
			return count($all); 
    }
    
   
    
    public function getOrder($id)
    {
    	$select =  $this->db->select();
    	$select->from(array('u' => $this->_tableName), array( '*'));
    	$select->where('id = ?', $id); 
		return  $this->db->fetchRow($select);		 		 
	}
	
	public function getOrderByToken($token)
    {
    	$select =  $this->db->select();
    	$select->from(array('u' => $this->_tableName), array( '*'));
    	$select->where('hash_order = ?', $token); 
		return  $this->db->fetchRow($select);		 		   
	}
	

    
    function getOrderDetails($oid, $sort = 'id', $sortType = 'Desc'){
    	$select =  $this->db->select();
    	$select->from(array('u' => $this->_tableItemsName), array( '*')); 
    	    					
		$select->where('o_id = ?', $oid); 
				
		$sortType = $sortType?$sortType:'Asc'; 
		$select->order($sort . ' ' . $sortType); 
		
		//  e($select->__toString());   
		return  $this->db->fetchAll($select);	    
    }
    
    function updateItem($item,$oid)
    {
    	 
		if($item['variantId'] > 0)
		{
    		$where = "(itemNodeId = ? AND variant = ? and o_id =?)";
    		$values = array($item['nodeId'], $item['variantId'],$oid);
		}
		else{
			$where = "(itemNodeId = ?  and o_id =?)";
    		$values = array($item['nodeId'],$oid);
		}
    	
    	foreach($values as $value){
    		$where = $this->db->quoteInto($where, $value , '', 1);
    	}
    	$data['count'] = $item['count'];
    	$data['price'] = $item['price'];
    	if($item['count'] > 0)
    	{  
			
    		$this->db->update($this->_tableItemsName, $data,$where);
    	
    	}
    	else{
    		$this->db->delete($this->_tableItemsName, $where);
    	}
    }
    
    
    function addItem($item,$content,$varinta,$oid,$count)
    { 
    	$dbCount = $this->db->fetchOne('select count from '.$this->_tableItemsName.' where  o_id = '.$oid.' and itemNodeId = ? ',$item->nodeId); 
    	if($dbCount)
    	{
    		$c = $count + $dbCount;
    		$data = array('count' => $c);
    		$where[] = $this->db->quoteInto(' itemNodeId = ? ', $item->nodeId);
			$where[] = $this->db->quoteInto(' o_id = ? ', $oid);
			
    		$this->db->update($this->_tableItemsName, $data,$where);
    	}
    	else{
    		
    		$mSet = Zend_Registry::getInstance()->settings;
    		$dph = $content->getPropertyValue('dphQuote') ? $content->getPropertyValue('dphQuote') : $mSet->getSettingValue('dph');
    	// dotaz na dph přechozího
    		$data = array(
    			'o_id' => $oid, 
    			'title' => $item->title,
    			'itemNodeId' => $item->nodeId,
    			'price' => $varinta['price'],
    			'dph' => $dph,
    			'content' => serialize($content),
    			'count' => $count
    			);
    		$this->db->insert($this->_tableItemsName, $data);
    		
    	}
    }
    
    function getItemsOrder($idOrder)
    {
    	$item = new stdClass();
    	$itemsOrder = $this->db->fetchAll('select * from '.$this->_tableItemsName. ' where o_id =?',$idOrder);
    	//pr($itemsOrder);
    	foreach ($itemsOrder as $value)
    	{   				
    		$item->title = $value['title'];
    		$item->nodeId = $value['itemNodeId'];	
    		$item->dph = $value['dph'];
    		$item->count = $value['count'];
    		$item->variantId = $value['variantId'];
  			$item->price = $value['price'];
    		$items[] = $item;
    	}
    	return $itemsOrder;
    }
}
?>