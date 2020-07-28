<?
/**
 * Třída reprentuje zalogovaneho uzivatele webu - firmu
 */
class module_Doklad {
	public $items = array();
	public $sum = 0;
	public $_tableName = 'module_faktura'; 
	
	public function __construct($varSymbol = 0, $orient = 'p', $unit = 'mm', $size = 'A4', $splatnost = 3, $splatnostDate = 0, $dzpDate = 0, $created = 0, $id = 0, $vs = 0) { 			
		require_once('libs/tcpdf/config/lang/eng.php'); 
		require_once('libs/tcpdf/tcpdf.php');		 
		
		$varSymbol = $varSymbol?$varSymbol:$this->getNextFakturaVS();  
		 
		$db = Zend_Registry::getInstance()->db;
		$this->config = Zend_Registry::getInstance()->config; 
		$this->pdf = $pdf = new TCPDF($orient, $unit, $size); 	
		$now = date('d.m.Y', mktime(date("H"), date("i"), date("s"), date("m"), date("d"),   date("Y")));
		$splDate = date('d.m.Y', mktime(date("H"), date("i"), date("s"), date("m"), date("d") + $splatnost,   date("Y")));
		if(!$created){
			$created = $now;
		}
				 
		if(!$dzpDate){
			$dzpDate = $now;
		}
		
		if(!$splatnostDate){
			$splatnostDate = $splDate;
		}
		$this->created = $created;
		$this->dzp = $dzpDate;
		$this->splatnost = $splatnostDate;   
		
		
		$this->sqlCreated = mktime(date("H"), date("i"), date("s"), date("m"), date("d"),   date("Y"));
		$this->sqlCreated = date('Y-m-d H:i:s', $this->sqlCreated); 
		
		$this->sqlSplatnost = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + $splatnost,   date("Y"));
		$this->sqlSplatnost = date('Y-m-d H:i:s', $this->sqlSplatnost); 
		 
		$this->vs = $varSymbol; 
	} 
	
	// set saved items
	function setItem($items){
		$this->items = $items;  
	}
	
	function addItem($title, $price, $count = 1){
		$this->items[] = new module_Faktura_Item($price, $count, $title); 		
		$this->sum =  $this->getSum(); 
	}
	
	function getSum(){
		$this->sum = 0; 
		foreach ($this->items as $item) {
			$this->sum +=  $item->getPrice();
		}
		return $this->sum; 
	}
	
	function setData($data){
		Helpers::setValuesToObject($this, $data); 
	}
	
	function getNextFakturaVS(){
		$db =  Zend_Registry::getInstance()->db; 
		$vs = $db->fetchOne("SELECT ident FROM `" . $this->_tableName . "` ORDER BY id desc LIMIT 1");  
		//$vs = $vs['ident']; 
		   
		if(!$vs){
			$vs = '200950000';
		} 
		 		
		list($year, $number) = $this->parseVS($vs);		
		$yearNow = date('Y'); 
		if($year != $yearNow){
			$number = 1;
		}
		 
		$number++; 
		$newNum = '1' . str_repeat('0', 4 - strlen($number . "")) . $number;   
		$newNum = $yearNow . $newNum;
		return $newNum;
	}
	  
	function parseVS($vs){ 
		$year = substr($vs, 0 , 4); 		
		$no = intval(substr($vs, 5 , 4));  
		return array(
			$year,
			$no
		);   
	}
	
	function getRawData(){
		$vars = get_object_vars($this);
		unset($vars['db']);
		unset($vars['config']);
		unset($vars['pdf']); 
		return $vars; 
	}
	
	function update(){ 
		$db =  Zend_Registry::getInstance()->db;
		$session =  Zend_Registry::getInstance()->session;
				
		$vars = $this->getRawData();
		   
		$data = array(
			'ident' => $vars['vs'], 
			'sum' => $vars['sum'] + $delivery,
			'discount' => $vars['discount'],
			'data' => serialize($vars)
		);
		$where = $db->quoteInto('ident = ?', $this->vs);  
		$db->update(   
			$this->_tableName,
			$data,  
			$where 			
		);	
		
		//return $db->lastInsertId();  
	}
	
	function save($paied = false, $delivery = 0){ 
		$db =  Zend_Registry::getInstance()->db;
		$session =  Zend_Registry::getInstance()->session;
		
				
		$vars = $this->getRawData();
		   
		$data = array(
			'ident' => $vars['vs'],
			'orderId' => $vars['orderId'], 
			'sum' => $vars['sum'] + $delivery,
			'discount' => $vars['discount'],
			'data' => serialize($vars),
			'created' =>  new Zend_Db_Expr('NOW()')
		);
		$db->insert(
			$this->_tableName,
			$data
		);	
		
		//return $db->lastInsertId(); 
	}
	
	function getDownloadUrl(){
		return '/__f/f_' . $this->vs . '.pdf';  
	}
	
	function paied($jobs){ 
		//e($this->sluzby); 
		$this->isProforma = false;
		$this->payed = mktime(date("H"), date("i"), date("s"), date("m"), date("d"),   date("Y"));
		$this->payed = date('Y-m-d H:i:s', $this->payed);  
		 
		$company = new module_Company($this->cId); 
				 
		foreach ($this->sluzby as $ident){
			$sluzba = $jobs->sluzby->all[$ident]; 			
			$sluzba->activate($company);
			
		}
		 
		$this->save(true);  
		//$this->generatePdf(); 
	}
	
	function changeData($data, $deliveryPrice){
		$data = (array) $data;    
		$this->firstname = $data['firstname'];
		$this->surname = $data['surname'];
		$this->efullname = $data['efullname'];
		$this->ulice = $data['ulice']; 
		$this->mesto = $data['mesto'];
		$this->psc = $data['psc'];
		$this->fu_ico = $data['fu_ico'];
		$this->fu_dic = $data['fu_dic'];
		$this->fu_firma = $data['fu_firma'];
		$this->efullname = $data['fu_firma']; 		
		$this->fu_mesto = $data['fu_mesto'];
		$this->fu_ulice = $data['fu_ulice'];
		$this->fu_psc = $data['fu_psc'];  
		
		$this->stat = $data['stat']; 
		$this->fu_stat = $data['fu_stat']; 
		
		$this->update();
		
		$conf = Zend_Registry::getInstance()->config;
		$path = '/hosting/www/lightgarden.cz/www/__f/f_' .  $this->vs. '';     		
		$this->generatePdf(true, $path, $this->sum, $this->discount, $deliveryPrice);     
	}
	
	function recalculate($deliveryPrice){	  
		$this->update();		
		$conf = Zend_Registry::getInstance()->config;
		$path = '/hosting/www/lightgarden.cz/www/__f/f_' .  $this->vs. '';     		
		$this->generatePdf(true, $path, $this->sum, $this->discount, $deliveryPrice);  
		return $path; 
	}
	
	function generateAndSave($items, $customer, $discount, $sum, $ident, $deliveryText = '', $deliveryPrice = 0){
		//e($customer);  die(); 
		//e($reservations); die();    
		$conf = Zend_Registry::getInstance()->config;
		$path = '/hosting/www/lightgarden.cz/www/__f/f_' .  $this->vs. '';     
		 
		$this->sum = $sum;
		$this->discount = $discount;  
		 
		$this->setData($customer); 	
	
				  
		$language = 'cz';
		foreach($items as $item){ 
			$this->addItem($item[0], $item[2], $item[1]); //  
		}  
		/*
		if($deliveryPrice){
			$this->addItem($deliveryText, $deliveryPrice, 1); //   
		}  
		*/
		$this->save(false, $deliveryPrice);    
		
		return $this->generatePdf(true, $path, $sum, $discount, $deliveryPrice);   
	} 
	 
	function getLoggedCompany(){
		return Zend_Registry::getInstance()->session->webUser;
	}
	
	function generatePdf($save = false, $title = 'Doklad', $sum, $discount, $dodani = 0){	
		$this->sum =  $this->getSum();  
				
		$this->pdf->SetHeaderMargin(PDF_MARGIN_HEADER); 
		$this->pdf->SetHeaderFont(Array("freesans", 'i', 10));
		$this->pdf->SetHeaderData('',30, "", "");         
		$this->pdf->SetFooterFont(Array(freeserif, 'b', 8));
		$this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER); 
		$this->pdf->SetMargins(5, 5);
		 
		$this->pdf->AliasNbPages();  
		
		$this->pdf->AddPage();		 
		$this->pdf->SetFont("freesans", "", 11); 		 
		$this->pdf->setCellHeightRatio(1.2);    
		
				
		$add = ''; 
			 		
  		if($this->fu_ulice && $this->fu_firma){ 
  			$adress = $this->fu_ulice;
  			$PSC = $this->fu_psc;
  			$mesto = $this->fu_mesto; 
  			$name = $this->fu_firma;  
  			$stat =  $this->fu_stat; 
  		} else {
  			$adress = $this->ulice; 
  			$PSC = $this->psc; 
  			$mesto = $this->mesto;  		
  			$stat =  $this->stat; 	
  			$name = $this->firstname . ' ' . $this->surname ;
  		}   
  		 
  		$stat = helper_Input::$countries1[$stat];  
  
  		//e($this);
		$this->pdf->writeHTMLCell(200, 0, 0, 8, "<h2>FAKTURA č. $add$this->vs</h2>", 0, 0, 0, true, 'R');   
		 
		// DODAVATEL //ODBERATEL 		
		$this->pdf->writeHTMLCell(120, 0, 0, 20, "<h3>Dodavatel:</h3>", 0, 0, 0);    
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "<h3>Odběratel:</h3>", 0, 1, 0);    
		 
		
		$this->pdf->SetFillColor(217,255,204);  
		
		$this->pdf->writeHTMLCell(80, 0, 0, 28, "<b>STANISLAV PETERA</b>", 0, 0, 0); 
		
		$this->pdf->writeHTMLCell(40, 0, 0, 0, " ", 0, 0, 0);      
		
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "IČO: <b>$this->fu_ico</b>", 0, 1, 0);     
		
		  
		
		//$this->pdf->SetTextColor(255,58,0); 
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "BRONZOVÁ 2022", 0, 0, 0);		
		$this->pdf->writeHTMLCell(40, 0, 0, 0, " ", 0, 0, 0);   
		//$this->pdf->SetTextColor(21,80,0); 		
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "DIČ: <b>$this->fu_dic</b>", 0, 1, 0);   		  
		
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "PRAHA 5, 155 00", 0, 0, 0);
		$this->pdf->writeHTMLCell(40, 0, 0, 0, " ", 0, 0, 0); 
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "", 0, 1, 0);   
		 
		
			 
		$this->pdf->SetTextColor(0,0,0); 
		 		
		$this->pdf->writeHTMLCell(120, 0, 0, 0, "IČO: <b>71077481</b>", 0, 0, 0);		 
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "<b>$name</b>", 0, 1, 0);    		
		
		$this->pdf->writeHTMLCell(120, 0, 0, 0, "CZ 8305230065", 0, 0, 0);		
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "$adress", 0, 1, 0);          
		
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "", 0, 0, 0);		
		$this->pdf->writeHTMLCell(40, 0, 0, 0, " ", 0, 0, 0);   
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "$PSC, $mesto, $stat", 0, 1, 0);         // , $this->oStat  
		  
		$this->insertBreak();  
		
		$this->pdf->writeHTMLCell(50, 0, 0, 0, "Datum zdanitelného plnění", 0, 0, 0);    
		$this->pdf->writeHTMLCell(30, 0, 0, 0, "" . ($this->dzp) . "", 0, 0, 0);    
		$this->pdf->writeHTMLCell(40, 0, 0, 0, "", 0, 0, 0);		 
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "Bankovní spojení: <b>Raiffeisenbank</b>", 0, 1, 0);     
		
		 	 
		$this->pdf->writeHTMLCell(50, 0, 0, 0, "Datum vystavení faktury:", 0, 0, 0);     
		$this->pdf->writeHTMLCell(30, 0, 0, 0, "<b>" . ($this->dzp) . "</b>", 0, 0, 0);    
		$this->pdf->writeHTMLCell(40, 0, 0, 0, "", 0, 0, 0);	
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "Č. účtu: <b>509484001 / 5500</b>", 1, 1, 0);  
		
		$this->pdf->writeHTMLCell(50, 0, 0, 0, "Datum splatnosti:", 0, 0, 0);     
		$this->pdf->writeHTMLCell(30, 0, 0, 0, "<b>" . ($this->splatnost) . "</b>", 0, 0, 0);   
		$this->pdf->writeHTMLCell(40, 0, 0, 0, '', 0, 0, 0);		  
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "IBAN: <b>CZ9655000000000509484001</b>", 1, 1, 0);  
		  
		$this->pdf->writeHTMLCell(120, 0, 0, 0, '', 0, 0, 0);	 
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "SWIFT: <b>RZBCCZPP</b>", 1, 1, 0);   
			
		$this->insertBreak();  
		  
		$this->pdf->writeHTMLCell(120, 0, 0, 0, $this->jDPH, 0, 0, 0);		   
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "Variabilní symbol: <b>$this->vs</b>", 1, 1, 0);   
	
		$this->pdf->writeHTMLCell(120, 0, 0, 0, "", 0, 0, 0);		 
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "Forma úhrady: <b>$this->fu</b>", 1, 1, 0);  
		  
		$this->insertBreak();    
		$this->insertBreak();  
		
		$this->pdf->setCellHeightRatio(2.6);  		 
		 
		$this->pdf->writeHTMLCell(200, 0, 0, 0, "Objednávka", 0, 1, 0); 	 					 
			 
		$this->pdf->setCellHeightRatio(1.2); 
		
		$this->pdf->writeHTMLCell(140, 0, 0, 0, "<b>Zboží</b>", 1, 0, 0);		  
		$this->pdf->writeHTMLCell(25, 0, 0, 0, "<b>Počet</b>", 1, 0, 0);    
		$this->pdf->writeHTMLCell(35, 0, 0, 0, "<b>Cena</b>", 1, 1, 0);     
		
		// OBJEDNAVKA
			
		$dphQuote = $this->getDph();			 
		$dphText = $this->getDphReadable();
		
		$itemsPriceBezDPH = 0;
		foreach ($this->items as $item) {			
			$price = $item->price * $item->count;		
			// $itemsPriceBezDPH += round(0.8 * $price, 2);	 
			if($this->hasDph){
				$price = round($price / $dphQuote, 2);
			}
			
			$itemsPriceBezDPH += $price;	  
				
			//$dph = $sum / 1.19 * 0.19;
			$this->drawObjednavkaLine($item->title, $item->count, $price);  
		}
		 /*
		 DODELAT DO VYPOCTU 
		$this->pdf->writeHTMLCell(165, 0, 0, 0, 'Sleva:', 1, 0, 0, true, 'R');  
		$this->pdf->writeHTMLCell(35, 0, 0, 0, '<b>' . helper_FrontEnd::price($discount) . ' Kč</b>', 1, 1, 0);   
		 */ 
		 
		if($this->hasDph){
			$dodaniBezDPH = round($dodani / $dphQuote, 2);				
		} else {
			$dodaniBezDPH = $dodani;
		}
		
		$sumBezdph = $itemsPriceBezDPH + $dodaniBezDPH; 
		 			
		$sum = $sum + $dodani;
		$dph = $sum - $sumBezdph;  
					
		  
		if($dodani){  
			$this->pdf->writeHTMLCell(165, 0, 0, 0, 'Poštovné:', 1, 0, 0, true, 'R');   
			$this->pdf->writeHTMLCell(35, 0, 0, 0, '' . helper_FrontEnd::price($dodaniBezDPH) . ' Kč', 1, 1, 0);   
		}	 
					
		// celkem	 
		
		if($this->hasDph){
			$this->pdf->writeHTMLCell(165, 0, 0, 0, 'Cena bez DPH (základ daně):', 1, 0, 0, true, 'R');    
			$this->pdf->writeHTMLCell(35, 0, 0, 0, '<b>' .helper_FrontEnd::price($sumBezdph) . ' Kč</b>', 1, 1, 0);  
			
			$this->pdf->writeHTMLCell(165, 0, 0, 0, 'DPH ' . $dphText . '%:', 1, 0, 0, true, 'R');   
			$this->pdf->writeHTMLCell(35, 0, 0, 0, '<b>' . helper_FrontEnd::price($dph) . ' Kč</b>', 1, 1, 0);   
			
			$this->pdf->writeHTMLCell(165, 0, 0, 0, 'Celkem k úhradě (s DPH):', 1, 0, 0, true, 'R');  
			$this->pdf->writeHTMLCell(35, 0, 0, 0, '<b>' . helper_FrontEnd::price($sum) . ' Kč</b>', 1, 1, 1); 
		} else {
			$this->pdf->writeHTMLCell(165, 0, 0, 0, 'Cena bez DPH (základ daně):', 1, 0, 0, true, 'R');    
			$this->pdf->writeHTMLCell(35, 0, 0, 0, '<b>' .helper_FrontEnd::price($sumBezdph) . ' Kč</b>', 1, 1, 0);  
			/*
			$this->pdf->writeHTMLCell(165, 0, 0, 0, 'DPH ' . $dphText . '%:', 1, 0, 0, true, 'R');   
			$this->pdf->writeHTMLCell(35, 0, 0, 0, '<b>' . helper_FrontEnd::price($dph) . ' Kč</b>', 1, 1, 0);   
			*/
			
			if(is_float($sum)){
				$sumFull = $sum;
				$sum = floor($sum);
				$dif = $sumFull - $sum;
				$this->pdf->writeHTMLCell(165, 0, 0, 0, 'Zaokrouhlení:', 1, 0, 0, true, 'R');    
				$this->pdf->writeHTMLCell(35, 0, 0, 0, '<b>- ' . $dif . ' Kč</b>', 1, 1, 0);  			
			} 
			
			$this->pdf->writeHTMLCell(165, 0, 0, 0, 'Celkem k úhradě:', 1, 0, 0, true, 'R');  
			$this->pdf->writeHTMLCell(35, 0, 0, 0, '<b>' . helper_FrontEnd::price($sum) . ' Kč</b>', 1, 1, 1); 
		}
				 
		//podpis    
		$this->insertBreak();     
		$this->insertBreak();      
		$this->insertBreak();  
		$this->pdf->setJPEGQuality(95); 
		
		
		//$this->pdf->Image('/hosting/www/lightgarden.cz/faktura/podpis.png', 140, '', 33, 11, '', '', '', true, 300);     
		
		 
		$this->pdf->Image('/hosting/www/lightgarden.cz/www/images/podpis.jpg', 140, '', 40, '', '', '', '', true, 600);       
  
			    
		ob_clean(); 
		
		if($save){
			$this->pdf->Output($title . '.pdf', 'F'); 
		} else { 
			$this->pdf->Output($title . '.pdf', 'D');    
		}
		return $title . '.pdf';   
	}
	
	function hasDph(){
		$has = true;
		if(isset($this->hasDph)){
			$has = $this->hasDph;
		}  
		return $has;
	}
	
	function getDph(){
		if(strpos($this->dzp, '.2010') || strpos($this->dzp, '.2011') || strpos($this->dzp, '.2012') || strpos($this->dzp, '.2013')){
			return 1.2;  
		} else {   
			return 1.19;
		} 
	} 
	
	function getDphReadable(){
		if(strpos($this->dzp, '.2010') || strpos($this->dzp, '.2011') || strpos($this->dzp, '.2012') || strpos($this->dzp, '.2013')){
			return 20; 
		} else { 
			return 19; 
		}
	} 
	 
	
	/*
	function generatePdf($save = false, $title = 'Doklad'){		 
		$this->sum =  $this->getSum();  
		$this->pdf->SetFillColor(204,219,244);    
		  
		$this->pdf->SetHeaderMargin(PDF_MARGIN_HEADER); 
		$this->pdf->SetHeaderFont(Array("freesans", 'i', 10));
		$this->pdf->SetHeaderData('',200, 'Faktura - daňový doklad č. 20090009' );      
		    
		$this->pdf->SetFooterFont(Array(freeserif, 'b', 8)); 
		$this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER); 
		$this->pdf->SetMargins(5, 5);
		 
		$this->pdf->AliasNbPages();  
		
		$this->pdf->AddPage();		  
		$this->pdf->SetFont("freesans", "", 11); 		 
		$this->pdf->setCellHeightRatio(1.2);    
		
		
		$this->pdf->writeHTMLCell(200, 0, 0, 63, "<h3>DOKLAD O ZAPLACENÍ</h3>", 0, 1, 0, true, 'C');     
		
		 	 
		// DODAVATEL //ODBERATEL 	  		
		$this->pdf->writeHTMLCell(80, 0, 0, 75, "DATUM VYSTAVENÍ:", 1, 0, 0); 		
		$this->pdf->writeHTMLCell(120, 0, 0, 0, "<b>$this->created</b>", 1, 1, 0);      
		
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "PŘIJATO OD:", 1, 0, 0);      
		$this->pdf->writeHTMLCell(120, 0, 0, 0, "<b>$this->name</b> ($this->identification_number)", 1, 1, 0);        
		 		
		$this->pdf->setCellHeightRatio(1.2); 
		$this->insertBreak(); 
		
		$this->pdf->writeHTMLCell(140, 0, 0, 0, "<b>POLOŽKA</b>", 1, 0, 0);	
		$this->pdf->writeHTMLCell(60, 0, 0, 0, "<b>CENA</b>", 1, 1, 0);        
		
		// OBJEDNAVKA
		foreach ($this->items as $item) {
			$price = $item->getPrice();
			$sum = $this->sum; 
		
			$this->drawObjednavkaLine($item->title, $price);	 
			
		}  
		
		$dph = 0.09 * $sum;
		$dph = round($dph, 2);
		$sumBezdph = $sum - $dph;
		// celkem	
		$this->pdf->writeHTMLCell(140, 0, 0, 0, 'Celkem k úhradě bez DPH:', 1, 0, 0, true, 'R');  
		$this->pdf->writeHTMLCell(60, 0, 0, 0, '<b>' . helper_FrontEnd::price($sumBezdph) . ' Kč</b>', 1, 1, 0);  
		
		$this->pdf->writeHTMLCell(140, 0, 0, 0, 'DPH 9%:', 1, 0, 0, true, 'R');  
		$this->pdf->writeHTMLCell(60, 0, 0, 0, '<b>' . helper_FrontEnd::price($dph) . ' Kč</b>', 1, 1, 0);   
		
		$this->pdf->writeHTMLCell(140, 0, 0, 0, 'Celkem k úhradě (s DPH):', 1, 0, 0, true, 'R');  
		$this->pdf->writeHTMLCell(60, 0, 0, 0, '<b>' . helper_FrontEnd::price($sum) . ' Kč</b>', 1, 1, 1);   
		
		//podpis
		$this->insertBreak(); 
		$this->insertBreak();  
		$this->insertBreak();  
		$this->pdf->setJPEGQuality(95); 
		$this->pdf->Image('/hosting/www/lightgarden.cz/faktura/signature.jpg', 140, '', 66, 22, '', '', '', true, 300);       
		 
		// 
		$this->pdf->writeHTMLCell(200, 0, 0, 255, "", 0, 1, 0);           
		$this->pdf->writeHTMLCell(200, 0, 0, 0, "Firma je zapsána v obchodním rejstříku vedeném Krajským soudem v Hradci Králové, oddíl C, vložka 21309", 0, 1, 0);  
		ob_clean();  
		
		if($save){
			$this->pdf->Output($title . '.pdf', 'F'); 
		} else { 
			$this->pdf->Output($title . '.pdf', 'D');  
		}
		return $title . '.pdf';  
	}
	*/
	function drawObjednavkaLine($title, $count, $price){ 
		$this->pdf->writeHTMLCell(140, 0, 0, 0, $title . '', 1, 0, 0);	 
		$this->pdf->writeHTMLCell(25, 0, 0, 0, $count, 1, 0, 0);    
		$this->pdf->writeHTMLCell(35, 0, 0, 0, helper_FrontEnd::price($price) . ' Kč', 1, 1, 0);      
		 
	} 
	
	function insertBreak(){
		$this->pdf->setCellHeightRatio(1);	  
		$this->pdf->writeHTMLCell(100, 0, 0, 0, " ", 0, 1, 0);     // break 
		$this->pdf->setCellHeightRatio(1.2);    
	}
	
}

class module_Faktura_Item {
	public $price, $count, $title = 0;
	
	public function __construct($price = 0, $count = 0, $title = '') {			
		$this->price = $price;
		$this->count = $count;
		$this->title = $title;
	}
	
	public function getPrice(){
		return $this->price * $this->count;
	}
}

?>