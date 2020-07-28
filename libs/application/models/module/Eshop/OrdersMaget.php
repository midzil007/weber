<?

/**
 * Export pro magnet
 */
class module_Eshop_OrdersMaget {	
			
	public $_tableName = 'module_eshop_orders';
	public $_tableItemsName = 'module_eshop_order_items'; 
	public $_tableNameFaktura = 'module_faktura'; 
	
	public $tStats = 'module_eshop_orders_stats';
	public $tHistory = 'module_eshop_orders_history';  
	private $oextId = 0;
	
	public function __construct() { 
		$this->db = Zend_Registry::getInstance()->db;	 
		$this->tree = Zend_Registry::getInstance()->tree;		  
		$this->config = Zend_Registry::getInstance()->config;		 
	}
	
	function exportOrder($orderId, $customer, $items, $basket){  
		$this->oextId = 'web-drop-' . date('Y-m-d') . '-' . $orderId; 
	    
	    $delivery = $customer['delivery']==19?5:1;  
	     
		$this->append('<?xml version="1.0" encoding="UTF-8"?>');    
		$this->append('
		<Doklady>
		'); 
		$this->exportHeader($orderId);
		$this->exportCustomer($customer);
		$this->exportItems($items);
		$this->append('
		</Doklady>
		');     
		$filename = $this->config->dataRoot . '/objednavky/' . $this->oextId . '.xml';
		Utils::writeToFile($this->sitemap, $filename);   
		return 1; 
	}
	
	function exportHeader($orderId){
		// doruceni 5 = doruky, 1 balik napostu 
		$this->append('
		   <Variabilni_symbol>' . $this->oextId . '</Variabilni_symbol>  
		   <Kniha>6.Zakázky partneři</Kniha>
			<Firma>spechosp</Firma>');
	}
	
	function exportCustomer($customer){		
		$fCustomer = explode(' ', $customer['fu_jmeno']);
		if($customer['efullname']){
			$dCustomer = explode(' ', $customer['efullname']);
		}
		else{
			$dCustomer = $fCustomer;
		}
		$customer['mesto'] = $customer['mesto']? $customer['mesto']:$customer['fu_mesto'];
		$t = explode(' ', $customer['fu_ulice']);
		foreach ($t as $value) {
			if(is_numeric($value)){
					$cislodomu = $value;
				}
				else{
					$ulice .= $value.' ';
				}
		}
		if($customer['ulice']){	
			$temp = explode(' ', $customer['ulice']);
			foreach ($temp as $value) {
				if(is_numeric($value)){
					$cislodomu2 = $value;
				}
				else{
					$ulice2 .= $value.' ';
				}
			}
		}
		else{
			$cislodomu2 = $cislodomu;
			$ulice2 .= $ulice.'';
		}
		$customer['mesto'] = $customer['mesto']?$customer['mesto']:$customer['fu_mesto'];
		$customer['psc'] = $customer['psc']?$customer['psc']:$customer['fu_psc'];
		$showExpedicni = false;
		if( $ulice2!=$ulice || $customer['fu_mesto']!=$customer['mesto'] ||  $customer['psc']!=$customer['fu_psc']){
			$showExpedicni = true;	
		}		
		$this->append('
			<Firma>' . $customer['fu_firma'] . '</Firma>
			<Jmeno>' . $fCustomer[0] .' '.$fCustomer[1]. '</Jmeno>		
		   <Adresa>' . $ulice . ' '. $cislodomu. '#CRLF#'.$customer['fu_psc']. '#CRLF#' . $customer['fu_mesto']. '</Adresa>');
		if($showExpedicni){
			$this->append('
				<Adresa_2>'.$dCustomer[0].' '.$dCustomer[1].'#CRLF#' . $ulice2 . ' '. $cislodomu2. '#CRLF#'.$customer['psc']. '#CRLF#' . $customer['mesto']. '</Adresa_2>');
// 		   <EXPEDICNI_ADRESA>  
// 			   <JMENO>' . $dCustomer[0] . '</JMENO>
// 			   <PRIJMENI>' . $dCustomer[1] . '</PRIJMENI>
// 			   <FIRMA>' . $customer['firma'] . '</FIRMA>
// 			   <ULICE>' . $ulice2. '</ULICE>
// 			   <CISLO_DOMU>' . $cislodomu2 . '</CISLO_DOMU>
// 			   <MESTO>' . $customer['mesto'] . '</MESTO>
// 			   <PSC>' . $customer['psc'] . '</PSC>
// 			   <DRUHE_JMENO></DRUHE_JMENO>
// 			   <CISLO_BYTU></CISLO_BYTU>
// 			   <REGION></REGION>
// 			   <OBLAST></OBLAST>
// 		   </EXPEDICNI_ADRESA>
		   }
		$this->append('
			<Adresa>' . $ulice . ' '. $cislodomu. '#CRLF#'.$customer['fu_psc']. '#CRLF#' . $customer['fu_mesto']. '</Adresa>
		   <DIC>' . $customer['fu_dic'] . '</DIC>
		   <ICO>' . $customer['fu_ico'] . '</ICO>
		   <Telefon>' . $customer['fu_phone'] . '</Telefon>
		   <E_mail>obchod@euroing.cz</E_mail>');
	
	}
	
	function exportItems($items){
		foreach ($items as $item){
			$child = $item['item'];  
			$count = $item['count'];
			if(!$child){ continue; }
			$mProducts = new module_Products();
			$c = $child->getPublishedContent(); 
			$price  = $item['price'];     
			$sum = $price * $count;  
			$variantaIdName = 'varianta_'. $item['variantId']. '_logistic_number'; 
			$product = $mProducts->getProductByNodeIdVariant($child->nodeId,$item['variantId']);
			$this->append('  
			   <Polozky_dokladu>
				   <Produkt>' . $c->getPropertyValue('origNameProduct') . '</Produkt>
				   <Varianta></Varianta>
				   <POPIS>' . $child->title . '</POPIS>
				   <BARVA>'.$product['color'].'</BARVA>
				   <VELIKOST>'.$product['variant'].'</VELIKOST>
				   <MNOZSTVI>'.$count.'</MNOZSTVI>  
				   <CENA_KS>'.round($price).'</CENA_KS>
				   <DAREK>N</DAREK> 
			   </Polozky_dokladu>');  
		}
	}
	
	function append($txt){
    	$this->sitemap .= $txt . $this->newLine;
    }
}
?>