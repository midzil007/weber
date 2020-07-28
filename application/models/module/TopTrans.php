<?
  /**
	uzivatelsky editovatelne zpusoby dopravy
	@author Jakub Kratena
  */
use Zend\Validator\Explode;
class module_TopTrans{
	
	private $_dobirka = 13;
	 
	private $_tableName = 'module_eshop_orders';
	private $_tableItemsName = 'module_eshop_order_items'; 
	private $_tableVariants = 'module_eshop_variants';  
	
	private $_delivery = array
		(
			16 => array('comfort' => 0, 'svoz' => 0),
			23 => array('comfort' => 1, 'svoz' => 0),
			24 => array('comfort' => 1, 'svoz' => 1),
		);
		
	private $_hmotnost = array(0,5, 5, 15, 30, 50, 75, 100, 150, 200, 300, 400);
		
	
	public function __construct() {
		
		$this->db = Zend_Registry::getInstance()->db;	 
		$this->tree = Zend_Registry::getInstance()->tree;		  
		$this->config = Zend_Registry::getInstance()->config;		 
		}


	public function getOrder($id)
	{
		$select =  $this->db->select();
    	$select->from(array('order' => $this->_tableName), array( '*'));
		$select->where('order.id = ?', $id);
		return $this->db->fetchRow($select);	
	}



	public function getOrderDetails($id)
    {
    	$select =  $this->db->select();
    	$select->from(array('items' => $this->_tableItemsName), array());
    	 
         $select->join(
			 array('variants' => $this->_tableVariants),
        	 'items.variant = variants.id',
        	 array('*')      
         ); 
		$select->where('o_id = ?', $id);
		return $this->db->fetchAll($select);		 		 
    }
	
	private function replaceDia($text)
	{
		$prevodni_tabulka = Array(
		 '&'=>'&amp;',
  'ä'=>'a',
  'Ä'=>'A',
  'á'=>'a',
  'Á'=>'A',
  'à'=>'a',
  'À'=>'A',
  'ã'=>'a',
  'Ã'=>'A',
  'â'=>'a',
  'Â'=>'A',
  'č'=>'c',
  'Č'=>'C',
  'ć'=>'c',
  'Ć'=>'C',
  'ď'=>'d',
  'Ď'=>'D',
  'ě'=>'e',
  'Ě'=>'E',
  'é'=>'e',
  'É'=>'E',
  'ë'=>'e',
  'Ë'=>'E',
  'è'=>'e',
  'È'=>'E',
  'ê'=>'e',
  'Ê'=>'E',
  'í'=>'i',
  'Í'=>'I',
  'ï'=>'i',
  'Ï'=>'I',
  'ì'=>'i',
  'Ì'=>'I',
  'î'=>'i',
  'Î'=>'I',
  'ľ'=>'l',
  'Ľ'=>'L',
  'ĺ'=>'l',
  'Ĺ'=>'L',
  'ń'=>'n',
  'Ń'=>'N',
  'ň'=>'n',
  'Ň'=>'N',
  'ñ'=>'n',
  'Ñ'=>'N',
  'ó'=>'o',
  'Ó'=>'O',
  'ö'=>'o',
  'Ö'=>'O',
  'ô'=>'o',
  'Ô'=>'O',
  'ò'=>'o',
  'Ò'=>'O',
  'õ'=>'o',
  'Õ'=>'O',
  'ő'=>'o',
  'Ő'=>'O',
  'ř'=>'r',
  'Ř'=>'R',
  'ŕ'=>'r',
  'Ŕ'=>'R',
  'š'=>'s',
  'Š'=>'S',
  'ś'=>'s',
  'Ś'=>'S',
  'ť'=>'t',
  'Ť'=>'T',
  'ú'=>'u',
  'Ú'=>'U',
  'ů'=>'u',
  'Ů'=>'U',
  'ü'=>'u',
  'Ü'=>'U',
  'ù'=>'u',
  'Ù'=>'U',
  'ũ'=>'u',
  'Ũ'=>'U',
  'û'=>'u',
  'Û'=>'U',
  'ý'=>'y',
  'Ý'=>'Y',
  'ž'=>'z',
  'Ž'=>'Z',
  'ź'=>'z',
  'Ź'=>'Z'
);
	return strtr($text, $prevodni_tabulka);
	}
	
	
	private function cleanText($html)
	{
		$html = trim(strip_tags($html)); 
		$html = str_replace('×', ' x' , $html);          
		//$html = str_replace('
//','',$html);    
		 return html_entity_decode(str_replace('  
 ', ' ', str_replace('
 
','',str_replace('°','',str_replace('&','',str_replace('©','',trim(strip_tags($html))))))));        
		      
	}    
	
	function xmlOrder($orderId)
	{
		// pozn8mku i naší
		//výška s obalem...
		$dO = $this->getOrder($orderId);  
		$data = $this->getOrderDetails($orderId);
		$count = $hmotnost =  0;
		
		
		$firma = $dO['firma'] ? $dO['firma'] : $dO['fu_firma'];
		$ulice = $dO['ulice'] ? $dO['ulice'] : $dO['fu_ulice'];
		$mesto = $dO['mesto'] ? $dO['mesto'] : $dO['fu_mesto'];
		$psc = $dO['psc'] ? $dO['psc'] : $dO['fu_psc'];
		$phone = $dO['phone'] ? $dO['phone'] : $dO['fu_phone'];
		$prijmeni  = $this->cleanText($dO['surname']);
		$jmeno= $this->cleanText($dO['firstname']);
	  	if(!$firma){
	  		$firma = $jmeno.' '.$prijmeni;  
	  	}
		$confort = $this->_delivery[$dO['delivery']]['comfort']; 
		$sberny_dvur  = $this->_delivery[$dO['delivery']]['svoz']; 
		$dobirka = $dobi= $dO['payment'] == $this->_dobirka ? 1 : 0;	
     
		$casti = explode(" ", $ulice);     
		$cp = end ($casti);
		$hmotnost = $dO['suma'] > 14999 ? '5' : '0.5';
		$t = count($casti)-1;
		unset($casti[$t]);		    
		$ulice = implode("",$casti); 
		$firma = $this->cleanText($firma);      		
		if(date('G') < 13)
		{
			$dateNakladky = date('d.m.Y');
			$dateVykladky = (date('d.m.Y', strtotime($dateNakladky. ' + 1 days')));    
		} 
		else
		{
		//	$dateNakladky = date('d.m.Y',strtotime($dateNakladky. ' + 1 days'));
		//	$dateVykladky = (date('d.m.Y', strtotime($dateNakladky. ' + 2 days')));      
		}    
		if(date(w) > 4 )
		{ 
			$count = 7 - date(w);    
			$string= ' + '.$count.' days';     
		//	$dateNakladky = date('d.m.Y',strtotime($dateNakladky. $string));    
			$dateVykladky = (date('d.m.Y', strtotime($dateVykladky. $string)));  
		}
		   
		$cena = $dobirka == 1 ?  $dO['suma'] :  '0';  
		 $casNaklady = date("H:i", strtotime("+10 minutes"));  
		 $casNaklady2 = date("H:i", strtotime("+60 minutes"));
		$this->append('
	<order>
		<label>'.$dO['id'].'</label>
		<var_symbol>'.$dO['id'].'</var_symbol>
		<payer_select>1</payer_select>
		<loading_select>1</loading_select>
		<term_id>1</term_id>   
		<loading_date>'.$dateNakladky.'</loading_date>   
		<loading_time_from>'.$casNaklady.'</loading_time_from>
		<loading_time_to>'.$casNaklady2.'</loading_time_to>    
		<discharge_date>'.$dateVykladky.'</discharge_date>  
		<discharge_time_from>08:00</discharge_time_from>
		<discharge_time_to>16:00</discharge_time_to> 
		<loading_personal_branch_id/>
<discharge_personal_branch_id/>
<payer/>
<loading/>
		<discharge>   
		<address>
			<country>Česká republika</country>
			<city>'.$this->replaceDia($mesto).'</city>
			<street>'.$this->replaceDia($ulice).'</street>
			<house_num>'.$cp.'</house_num>
			<zip>'.$psc.'</zip> 
		</address>   
		<name>'.$firma.'</name>');
		//if($dO['fu_ico']){
			//$this->append('
			//<registration_code>'.$dO['fu_ico'].'</registration_code>
			//<vat_code>'.$dO['fu_dic'].'</vat_code>');
		//}       
		$this->append('<first_name>'.$jmeno.'</first_name>
		<last_name>'.$prijmeni.'</last_name>
		<phone>+420'.$phone.'</phone>
		<email>'.$dO['email'].'</email>
		</discharge>
		<loading_comfort_id>1</loading_comfort_id>
		<discharge_comfort_id>1</discharge_comfort_id>
		<twoway_shipment>0</twoway_shipment>
		<yard>0</yard>
		<delivery_notes_back>0</delivery_notes_back>
		<euro_pallets_back>0</euro_pallets_back>
		<loading_aviso>0</loading_aviso>
		<discharge_aviso>0</discharge_aviso>
		<aviso_sms>1</aviso_sms>
		<consider>0</consider>
		<oversize>0</oversize>
		<label_fragile>1</label_fragile>
		<label_dont_tilt>0</label_dont_tilt> 
		<label_this_side_up>0</label_this_side_up>
		<hydraulic_front_loading>0</hydraulic_front_loading>
		<hydraulic_front_discharge>0</hydraulic_front_discharge>');   
		         

$this->append('
<loading_comfort_id>1</loading_comfort_id>
<discharge_comfort_id>1</discharge_comfort_id>
<twoway_shipment>0</twoway_shipment> 
<twoway_shipment_description></twoway_shipment_description>
<yard>0</yard>
<delivery_notes_back>0</delivery_notes_back>
<euro_pallets_back>0</euro_pallets_back>
<loading_aviso>0</loading_aviso>
<discharge_aviso>0</discharge_aviso>
<aviso_sms>1</aviso_sms>
<consider>1</consider>
<oversize>0</oversize>
<label_fragile>0</label_fragile>
<label_dont_tilt>0</label_dont_tilt>
<label_this_side_up>0</label_this_side_up>  
<hydraulic_front_loading>0</hydraulic_front_loading>
<hydraulic_front_discharge>0</hydraulic_front_discharge>');
if($dobi){          
	$this->append('
		<cash_on_delivery> 
			<type>1</type>
			<price>'.$dO['suma'].'</price>
			<price_cur_id>1</price_cur_id>
			<account2>2600340251</account2>
			<bank>2010</bank>
		</cash_on_delivery>');}    
$this->append('<kg>'.$hmotnost.'</kg> 
<m3/>
<order_value>'.$dO['suma'].'</order_value>
<order_value_currency_id>1</order_value_currency_id>
<note_loading></note_loading>
<note_discharge></note_discharge>
<packs>
<pack>
				<quantity>1</quantity>
				<pack_id>1</pack_id>
				<description>Přislušenství ke kávovaru</description>
			</pack>
</packs>
</order>');  
	}
	
	function exportOrders($orderIs)  
	{
		$this->oextId = time().'-toptrans.xml'; 	     
		$this->append('<?xml version="1.0" encoding="UTF-8"?>
<orders>');  
		foreach ($orderIs as $id) { 
			$this->xmlOrder($id);          
       
		} 
		$this->append('
</orders>');
		$filename = $this->config->dataRoot . '/objednavky/' . $this->oextId;
		Utils::writeToFile($this->sitemap, $filename);   
		return '/data/objednavky/'.$this->oextId;
	}

	function append($txt){
    	$this->sitemap .= $txt . $this->newLine;
    }
	 
}	
?>