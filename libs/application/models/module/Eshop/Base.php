<?

/**
 * Třída reprentuje eshop
 */
class module_Eshop_Base {
	
	public $products = array();
	public $structure = false;

	public $tZbozi = 'module_EshopZbozi'; 
	public $tStruktura = 'module_EshopKategorie'; 
	public $tCeny = 'module_EshopZboziCeny';  
	public $tStrukturaDodatky = 'module_EshopKategorieDodatky'; 
	public $tStrukturaCiselniky = 'module_EshopKategorieCiselnik'; 
	public $tDopravne = 'module_EshopTabDopravne'; 
	public $tDopravaPlatba = 'module_EshopTabDopravaPlatba';
  	
	public $tStats = 'module_eshop_orders_stats';
	public $tHistory = 'module_eshop_orders_history';
  	
	
	public $priceLevel = 1;
	  
	public $basket;   
	public $tree;  
	public $eshopType;  
	public $today;
	public $tomorrow;
	public $afterTomorrow;
	public $dph = 20;
	
	public function __construct() {
		$this->db = Zend_Registry::getInstance()->db;	
		$this->tree = Zend_Registry::getInstance()->tree;			
		$this->basket = new module_Eshop_Basket();   
		//$this->helios = new module_Eshop_Helios();
		$this->today = date('Y-m-d'); 		
		$tTomorrow = mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"));
		$this->tomorrow = date('Y-m-d', $tTomorrow);
		$tAfterTomorrow = mktime(0, 0, 0, date("m")  , date("d")+2, date("Y"));
		$this->afterTomorrow = date('Y-m-d', $tAfterTomorrow); 
		$this->orders = new module_Eshop_Orders($this->basket); 
		 
	} 
	
	function getProdejeByZnacka(){
		return $this->db->fetchAll("
			SELECT znacka, sum(price) as c
			FROM `module_eshop_orders_stats`
			GROUP BY `znacka`  
			LIMIT 0 , 300");   
	}
	
	
	function getProdejeByIds($pids, $params = array()){
		$in = implode("', '", $pids);  
		
		$params['year'] = date('Y');
		
		if($params['month'] && $params['month'] != 'all'){
			$w = ' AND MONTH(`added`) =   ' . $params['month'];
			$w .= ' AND YEAR(`added`) =   ' . $params['year']; 
		} elseif ($params['year']  && $params['month'] != 'all'){  
			$w .= ' AND YEAR(`added`) =   ' . $params['year']; 
		}     
		  
		return $this->db->fetchAll("
			SELECT pid, sum( price ) as s  , count( * ) as c
			FROM module_eshop_orders_stats
			WHERE `pid` IN ('$in') 
			$w 
			group by pid   
			ORDER BY s DESC 
			LIMIT 0 , 30 
		");  
	}
	
	
	function itemUnSold($order){
		//$details = $this->orders->getOrderDetails($order['id']);
		$where = 'oid = ' . $order['id'];
		$this->db->delete(
			$this->tStats,
			$where
		);  
	}
	
	function itemSold($order){
		//$order = $this->mEshop->orders->getOrder($id);  
		$details = $this->orders->getOrderDetails($order['id']);		 
		$mVariant = new module_Varianta();  
		$v = $mVariant->getVariantById($details[0]['variant'], false);
	 	     
		if(count($details) > 0){
			foreach ($details as $detail){
				$content = unserialize($detail['content']);
				//$price  = $content->getPropertyValue('price');  
				$price = $v['price'];    
				$znacka  = $content->getPropertyValue('znacka');   
				$count  = $detail['count'];    
				
				for($i = 1; $i <= $count; $i++){
					$item = array(
						'pid' => $detail['itemNodeId'],
						'znacka' => $znacka,
						'price' => $price,  
						'oid' => $order['id'],    
						'action' => 'sold',
						'added' => new Zend_Db_Expr('NOW()')
					);  
					$this->db->insert(
						$this->tStats,  
						$item  
					);  
				}
			}
		}   
	}
	
	function getVersion(){
		return 1;
	}     
	 
	function printNiceDate($view, $timestamp, $addSkladem){
		$date = date('Y-m-d', $timestamp);
		$dateNice = date('j.n.', $timestamp); 
		
		if($this->today == $date){ 
			$t = $addSkladem?'skladem':'dnes';
		} elseif($this->tomorrow == $date){
			$t = $addSkladem?'skladem zítra ' . $dateNice:' zítra ' . $dateNice;
			} elseif($this->afterTomorrow == $date){
			$t = $addSkladem?'pozítří ' . $dateNice:' pozítří ' . $dateNice;
		} else {  
			$t = Utils::$dayNames2[date('w', $timestamp)] . ' ' . $dateNice;  
		} 
		  
		return $t;		
	} 
	     
	function getPaymentOptionsSelect(){
		return array();  
	}
	
	function getDeliveryOptionsSelect(){
		return array();  
	}
	
	
	function getDeliveryOptions($view){
		return array(); 
		 
		return $all; 
	} 
	
	function getPaymentOptions($doprava, $view){
		return array();  
		//pr($all);
		return $all;
	} 
	
	function calcDeliveryPrice($view, $dopravaID, $price){ 
		return 0;  
		return $p;		
	}
	 
	
	function getCurrency($view){ 
		if($view->language == 'cz'){
    		$c = 'Kč';       		 
    	} else { 
    		$c = '&euro;';   
    	} 
    	
    	return $c; 
	}
	 
	function formatPrice($p, $zdarma = false){
		if($p <= 0){
			$p = $zdarma?'ZDARMA':0;    
		} else {
			$p = round($p);
		}
		if($zdarma){
			return $p;
		} else {
			return number_format($p, 0, '.', ' ');
		}    
	} 
	
	function formatPriceFull($p, $zdarma = false, $view){
		if(!$this->currency){
			$this->currency = $this->getCurrency($view);
		} 
		
		$p = $this->formatPrice($p, $zdarma);
		if($zdarma && $p <= 0){
			
		} else {
			$p .= ' ' . $this->currency;
		}
		return $p;
	} 
	
	/* NAKUP */
 
	  
	function addItemById($id, $count,$view){  
		if($count > 0){ 
			$itemId= explode('_', $id);
			$product = $this->tree->getNodeById($itemId[0]);
			$mProd = new module_Products();
			$this->basket->addItem($product, $count,false,$view,$itemId[1],$itemId[2]);
		} else { 
			//$this->basket->removeItem($id); 
		}
		 
	} 
	function generateXmlHeureka($view){
		$export = new module_XMLFeed($view);
		// $export->render($view);
		// $export->renderHeldejceny($view);
		$export->renderHeureka($view); 		
	}
}
?>