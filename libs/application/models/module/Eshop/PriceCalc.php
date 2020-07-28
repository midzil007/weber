<?
/**
	Pro výpočet a podrobný výpis ceny, dph, slev, dopravy, ...
	původně se toto opakovalo v mnoha různých šablonách, do kterých se data načítají z různých zdrojů
	(= module_Basekt, Faktury)
	
	@author Jakub Kratena
*/
class module_Eshop_PriceCalc
{
	private $mv; //module_Voucher
	public $voucher;
	private $items;
	private $znackyCiselnik; // array( znackaNodeId => title)
	private $discountSum;
	private $noDiscountSum;
	
	public $sumBezDph;		//soucet vseho bez dph
	public $sum;				//celkova castka
	public $dph;
	public $delivery;			//cena za dopravu
	public $payment;		//cena za platbu
	public $subtotal;			//mezisoucet
	public $helpText;		//vysvetlivky pro vouchery
	
	/**
	@var voucher pole vlastnosti voucheru (vystup z DB)
	@var items items z module_Basket, nebo z faktury
	*/
	public function __construct($voucher, $items, $delivery, $payment)
	{
		$this->mv = new module_Voucher();
		$this->eshop = new module_Eshop_Base();
		$this->basket = $this->eshop->basket;  
		$this->voucher = $voucher;
		$this->items = $this->processItems($items);
		$this->delivery = $delivery;
		$this->payment = $payment;
		$this->znackyCiselnik = $this->getZnacky($voucher);
		$this->calcPrice();
	}
	
	
	
	/**
		spocita castky... 
	*/
	private function calcPrice()
	{
		$sumAll = $discountSum = 0;// $this->delivery + $this->payment;
		
		//$itemsFull = $this->basket->getItems();
		
		$sumByDph = array();
		foreach($this->items as $item)
		{
			// pr($item); 
 			$node = $item['item'];
			$content = $node->getPublishedContent();
			  
			$dphQuote = ($content->getPropertyValue('dphQuote'));   
			 
			$sum = $item['price'] * $item['count']; 
			$sumAll += $sum;
			
			$sumByDph[$dphQuote] += $sum;
			
			if( $this->voucher['znacky'] )
			{
				if( array_key_exists( $item['productZnacka'],$this->znackyCiselnik ) )
					$this->discountSum += $sum;
				else
					$this->noDiscountSum += $sum;
			}
			elseif($this->voucher['productCode'])
			{
				if( $item['productKod'] == $this->voucher['productCode'])
					$this->discountSum += $sum;
				else
					$this->noDiscountSum += $sum;
			}
		}
		   
		
		$dph = 0;
		foreach ($sumByDph as $quote => $dphs){ 
			$dph += round( ($dphs / (1 + ($quote/100)) * ($quote/100)), 2); 
			   
		}    
		$this->dph = $dph; //round( ($sumAll / 1.21 * 0.21), 2); 
		
		$this->sumBezDopravy = $sumAll;  
		   
		if($sumAll >= 1700){ 
			$this->payment = 0;  
		}
		  
		$this->sum = $sumAll + $this->delivery + $this->payment; 
		
		$this->sumBezDph = $sumAll - $this->dph;
		  
	}
	
	static function getBezDph($sum){
		$dph = round( ($sum / 1.21 * 0.21), 2);  
		return round($sum - $dph);  
	}
	
	private function calcVoucher()
	{
		if( ($this->voucher['type']==1) OR ($this->voucher['type']==3) )
		{
			$discount = $this->discountSum*$this->voucher['value']/100;
			$this->sum = round( ($this->subtotal - $discount), 2 );
			$this->helpText .= $this->voucher['value'] . "%";
			$this->voucher['value'] .= '% ('.$discount.' Kč)' ;
		}
		else
		{
			$this->sum = $this->discountSum - $this->voucher['value'];
			if($this->sum < 0)
				$this->sum = 0;
			$this->sum +=  $this->noDiscountSum;
			$this->voucher['value'] .= ' Kč';
			$this->helpText .= $this->voucher['value'];
		}
		
		if($this->voucher['znacky'])
			$this->helpText .= ' Platí pro: ' . implode(', ',$this->znackyCiselnik);
		if($this->voucher['productCode'])
			$this->helpText .= ' Platí pro zboží s nákupním kódem: ' . $this->voucher['productCode'];
	}
	
	/**
	Zpracuje položky z košíku, nebo faktury do jednotné podoby
	*/
	private function processItems($items)
	{ 
		//pr($items);
		if( get_class($items[0]) == 'module_Faktura_Item' )
		{
			foreach($items as $item)
			{
				$newItem['price'] 			= $item->price;  
				$newItem['item'] 			= $item->item; 
				$newItem['count'] 			= $item->count;
				$newItem['productKod']	= $item->productKod;
				$newItem['productZnacka']=$item->productZnacka;
				
				$newItems[] = $newItem;
			}
		}
		else // content Product
		{
			foreach( $items as $data )
			{
				$item = $data['item'];
				if(!$item)
					continue;
				$c = $item->getPublishedContent(); 
				
				$newItem['price'] 			= $data['price'];
				$newItem['item'] 			= $item; 
				$newItem['count'] 			= $data['count'];
				$newItem['productKod']	= $c->getPropertyValue('kod') ;
				$newItem['productZnacka']=$c->getPropertyValue('znacka');
				$newItems[] = $newItem;
			}
		}
		return $newItems;
	}
	

	private function getZnacky($voucher)
	{
		if($voucher['znacky'])
		{
			$znacky = explode(',',$voucher['znacky']);
			foreach($znacky as $znacka)
				$znackyCiselnik[$znacka] = $this->mv->getZnackaByNodeId($znacka);
		}
		//print_r($znackyCiselnik);
		return $znackyCiselnik;
	}
}
?>