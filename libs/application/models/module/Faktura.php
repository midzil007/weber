<?
/**
 * Třída reprentuje zalogovaneho uzivatele webu - firmu
 */
class module_Faktura {
	public $items = array();
	public $sum = 0;
	public $weight = 0;
	public $_tableName = 'module_faktura'; 
	
	public function __construct($varSymbol = 0, $orient = 'p', $unit = 'mm', $size = 'A4', $splatnost = 14, $splatnostDate = 0, $dzpDate = 0, $created = 0, $id = 0, $vs = 0) { 			
		require_once('tcpdf/config/lang/eng.php'); 
		require_once('tcpdf/tcpdf.php');		
		
		$this->db = Zend_Registry::getInstance()->db;
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
	
	public function getItems()
	{
		return $this->items;
	}
	
	function addItem($title, $price, $count = 1, $productZnacka = '', $productKod = '', $variantId = '', $weight = 0){
		$this->items[] = new module_Faktura_Item($price, $count, $title, $productZnacka, $productKod, $weight, $variantId); 		
		$this->sum =  $this->getSum(); 
		$this->weight = $this->getWeight();
	}
	
	function addItemById($nodeId,$count = 1, $variantId)
	{
		
		//echo "oooommmmmggggggg!!!";
		//print_r($nodeId);
		$mVariant = new module_Varianta();
		$mOrders = new module_Eshop_Orders(false);
		$itemNode = Zend_Registry::getInstance()->tree->getNodeById($nodeId);
		if(!$itemNode)
			return false;
		$c = $itemNode->getPublishedContent();
		$variant = $mVariant->getVariantsByIdProduct($c->id);
		$price = $variant[0]['price'];
		$code = $c->getPropertyValue('code');
		$znacka = $c->getPropertyValue('znacka');
		$weight = $c->getPropertyValue('hmotnost');
		//echo "oooommmmmggggggg!!!";
		print_r($itemNode->title);
		print_r($price);
		print_r($count);
		print_r($code);	
		$mOrders->addItem($itemNode,$c,$variant[0],$this->oid,$count);
		$this->addItem($itemNode->title, $price, $count, $znacka, $code, $variantId,  $weight);
		
		return true;
	}
	
	function getProductNodeIdByCode($code)
	{
		return $this->db->fetchOne('SELECT n.id FROM Nodes n JOIN (NodesContents nc, content_Product cp) ON ( n.id=nc.n_id AND nc.c_id=cp.id ) WHERE cp.kod= :c', array('c' => $code));
	}
	
	/**
		odstrani(zmeni pocet) polozku z faktury a prepocita soucet
		faktura se timto neuklada
		@param title title produktu (jiny identifikator nemusi byt k dispozici)
		@param cnt novy pocet produktu
	*/
	public function removeItemByTitle($title,$cnt=0)
	{
		if($cnt<0)
			$cnt = 0;
	
			
		foreach($this->items as $key => $item)
		{
			if($item->title == $title)
			{
				$selectedKey = $key;
				$found = true;
				break;
			}
		}
		
		if(!$found)
			return false;
		
		$oldCnt = $this->items[$selectedKey]->count;
		$price = $this->items[$selectedKey]->price;
		if( !$cnt ) //smazat vse
		{
			unset($this->items[$selectedKey]);
			$this->sum -= $oldCnt * $price;
		}
		else
		{
			$this->items[$selectedKey]->count = $cnt;
			$diff = $oldCnt - $cnt;
			$this->sum -= $diff * $price; // Pokud se počet neznměnil, nic se nestane
		}
		return true;
	}
	 
	function getRawData(){
		$vars = get_object_vars($this);
		unset($vars['db']);
		unset($vars['config']);
		unset($vars['pdf']); 
		return $vars; 
	}
	
	function getSum(){
		$this->sum = 0;  
		foreach ($this->items as $item) {
			$this->sum +=  $item->getPrice();
		}
		 
		//$this->sum += $this->deliveryPrice; 
		
		return $this->sum; 
	}
	
	function getWeight()
	{
		$this->weight = 0;  
		foreach ($this->items as $item)
			$this->weight +=  $item->getweight();
		return $this->weight; 
	}
	
	function setData($data){
		Helpers::setValuesToObject($this, $data); 
	}
	
	function update()
	{
		$db =  Zend_Registry::getInstance()->db;
		$session =  Zend_Registry::getInstance()->session;
		 
		$vars = get_object_vars($this);
		unset($vars['db']);
		unset($vars['config']);
		unset($vars['pdf']); 

		// pr($vars); die();
		   		 
		$data = array(
			'vs' => $vars['vs'],  
			'sum' => $vars['sum'],		
			'cId' => $vars['cId'], 
			'oid' => $vars['oid'], 
			'splatnost' => $vars['sqlSplatnost'],   
			'proforma' => $vars['isProforma'],  
			'data' => serialize($vars)			
		); 
		
			$data['payed'] = $vars['payed']; 

		  
		  
			
			$id = $this->db->update(
				$this->_tableName,
				$data,
				$db->quoteInto('id = ?', $this->id) 
			);


		return $this->db->lastInsertId(); 
	}
	
	
	function save($paied = false){
		$db =  Zend_Registry::getInstance()->db;
		$session =  Zend_Registry::getInstance()->session;
		 
		$vars = get_object_vars($this);
		unset($vars['db']);
		unset($vars['config']);
		unset($vars['pdf']); 

		// pr($vars); die();
		   		 
		$data = array(
			'vs' => $vars['vs'],  
			'sum' => $vars['sum'],		
			'cId' => $vars['cId'], 
			'oid' => $vars['oid'], 
			'splatnost' => $vars['sqlSplatnost'],   
			'proforma' => $vars['isProforma'],  
			'data' => serialize($vars)			
		); 
		
		if($paied){
			$data['payed'] = $vars['payed']; 
		} else {
			$data['created'] = new Zend_Db_Expr('NOW()'); 
		}
		  
		  
		if($paied){ 			
			$id = $this->db->update(
				$this->_tableName,
				$data,
				$db->quoteInto('id = ?', $this->id) 
			);
		} else {
			$id = $this->db->insert(
				$this->_tableName,
				$data
			); 
		}

		return $this->db->lastInsertId(); 
	}
	
	function getDownloadUrl($html = false){
		return '/?getFaktura=' . $this->id . ($html?'&html=1':'');
	}
	
	function getFakturaDownloadUrl($html = false, $orderid = 0){  
		return '/?getFaktura=' . $this->id . '&f=1'. ($html?'&html=1':''). ($orderid?'&order='.$orderid:'');
	}
	   
	function getZalohovaDownloadUrl($html = false){
		return '/?getFaktura=' . $this->id . '&z=1'. ($html?'&html=1':'');
	}
	
	function paied($jobs){ 
		//e($this->sluzby);
		$this->isProforma = false;
		$this->payed = mktime(date("H"), date("i"), date("s"), date("m"), date("d"),   date("Y"));
		$this->payed = date('Y-m-d H:i:s', $this->payed);  
		  
		$company = new module_Company($this->cId); 
		
		$this->activate($company, $this->sluzba, $this->pocet);
		  
		$this->save(true);    
		//$this->generatePdf(); 
	}
	
	function activate($company, $sluzbaname, $count){  
		
		$pname = substr($sluzbaname, 3, 2); 
		 
		switch ($pname){ 
			case 1: 
				break;
			case 3:
				 
				break;
			case 5:
				  
				break;
			default:
			case 10: 
				 		 
				break;   
		} 
		
		$company->addCredit($count*$pname);		
		$this->sendConfirmEmail($company);  
		
	}
	
	function sendConfirmEmail($company){
		$mail = new Email();	 
		$mailText22 = '
				<h2 style="color:#0084be; font-size: 12px;">Potvrzení o přijaté platbě</h2>
				<p>Dobrý den, <br />
				dovolujeme si Vás informovat, že Vaše platba byla právě přijata.</p>					
				<p>Děkujeme a těšíme se na další spolupráci.</p>					
				<p>Mámavpráci.cz / <a href="' . Utils::getWebUrl() . '">www.mamavpraci.cz</a></p>
			';
		
		$emailText = Helpers::prepareEmail(
			$mail,
			$mailText22
		);  
		
		$mail->setBodyText(strip_tags(urldecode($mailText22)));
		$mail->setBodyHtml(urldecode($emailText));			
		$mail->setSubject('Mámavpráci.cz - Potvrzení');		
		
		$s = Zend_Registry::getInstance()->settings;
		$mail->setFrom($s->getSettingValue('outcommingEmail'), $s->getSettingValue('outcommingEmailName'));	
		$mail->addTo($company->oo_email, $company->oo_jmeno . ' ' . $company->oo_prijmeni);
					  
		try {
			$mail->send();
		} catch (Exception $e) {
		    return array(0, 'Email se nepodařilo odeslat.');
	    }		 
	}
	
	function getLoggedCompany(){
		return Zend_Registry::getInstance()->session->webUser;
	}
	
	function generateHTML($view, $addBody = false){   
		$view->faktura = $this; 
		$view->addBody  = $addBody;  
		return $view->render('parts/faktura.phtml');
	}
	
	function printOrder($view, $basket, $order){
		$view->basket = $basket; 
		$view->order = $order;
		$view->faktura = $this;  
		return $view->render('parts/orderBe.phtml');
	}
	
	/**
	 * @return module_Eshop_PriceCalc vrati instanci tridy.
	 * */
	function renderPriceCalc($view)
	{
		$view->mp = new module_Eshop_PriceCalc($this->voucher,
		 								$this->items, 
		 								$this->deliveryPrice, 
		 								$this->paymentPrice
										);
		return $view->render('parts/orderPriceCalc.phtml');
	}
	
	function generatePdf($save = false){  
		  
		$this->pdf->SetHeaderMargin(PDF_MARGIN_HEADER);    
		$this->pdf->SetHeaderFont(Array("arialce", 'i', 10));
		$this->pdf->SetHeaderData($this->config->htdocsRoot . '/images/logo.png',10, "", "");      
		$this->pdf->SetFooterFont(Array('arialce', 'b', 8));   
		$this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);   
		$this->pdf->SetMargins(5, 5); 
		 
		$this->pdf->SetFont("arialce", "", 11); 	
		 
		$this->pdf->AliasNbPages();   
		 
		$this->pdf->AddPage();		   	 
		$this->pdf->setCellHeightRatio(1.2);       
		
		if($this->isProforma === 1){
			$t = 'VÝZVA K PLATBĚ';   
		} else { 
			$t = 'FAKTURA';
		} 
		
		$title = $t . ' ' . $this->vs; 
		
		$add = ''; 
		if($this->isProforma === 1){ 
			$title .= '_Z';
			$add = 'Z';
		} 
		
		$this->pdf->writeHTMLCell(200, 0, 0, 8, "<h2>$t č. $add$this->vs</h2>", 0, 0, 0, true, 'R');  
		
		// DODAVATEL //ODBERATEL 		
		$this->pdf->writeHTMLCell(120, 0, 0, 20, "<h3>Dodavatel:</h3>", 0, 0, 0);   
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "<h3>Odběratel:</h3>", 0, 1, 0);    
		 
		
		$this->pdf->SetFillColor(217,255,204);  
		
		$this->pdf->writeHTMLCell(80, 0, 0, 28, "<b>$this->jName</b>", 0, 0, 0); 
		
		$this->pdf->writeHTMLCell(40, 0, 0, 0, " ", 0, 0, 0);      
		
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "IČO: <b>$this->oICO</b>", 0, 1, 0);     
		 
		
		//$this->pdf->SetTextColor(255,58,0); 
		$this->pdf->writeHTMLCell(80, 0, 0, 0, $this->jAdress, 0, 0, 0);		
		$this->pdf->writeHTMLCell(40, 0, 0, 0, " ", 0, 0, 0);   
		//$this->pdf->SetTextColor(21,80,0); 		
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "DIČ: <b>$this->oDIC</b>", 0, 1, 0);   		  
		
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "$this->jPSC $this->jMesto", 0, 0, 0);
		$this->pdf->writeHTMLCell(40, 0, 0, 0, " ", 0, 0, 0); 
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "<b>$this->oName</b>", 0, 1, 0);   
		  
		$this->pdf->SetTextColor(0,0,0); 
		 		
		$this->pdf->writeHTMLCell(120, 0, 0, 0, "IČO: <b>$this->jICO</b>", 0, 0, 0);		 
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "$this->oAdress", 0, 1, 0);    		
		
		$this->pdf->writeHTMLCell(120, 0, 0, 0, $this->jDIC, 0, 0, 0);		  
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "$this->oPSC, $this->oMesto", 0, 1, 0);          
		
		if($this->dJmeno && $this->dAdress){
			$this->insertBreak();
			$this->pdf->writeHTMLCell(80, 0, 0, 0, "", 0, 0, 0);		 
			$this->pdf->writeHTMLCell(40, 0, 0, 0, " ", 0, 0, 0);   
			$this->pdf->writeHTMLCell(80, 0, 0, 0, "<h3>Dodací údaje</h3>", 0, 1, 0);         // , $this->oStat
			$this->pdf->writeHTMLCell(80, 0, 0, 0, "", 0, 0, 0);		 
			$this->pdf->writeHTMLCell(40, 0, 0, 0, " ", 0, 0, 0);     
			$this->pdf->writeHTMLCell(80, 0, 0, 0, "$this->dJmeno", 0, 1, 0);         // , $this->oStat
			$this->pdf->writeHTMLCell(80, 0, 0, 0, "", 0, 0, 0);		 
			$this->pdf->writeHTMLCell(40, 0, 0, 0, " ", 0, 0, 0);     
			$this->pdf->writeHTMLCell(80, 0, 0, 0, "$this->dAdress", 0, 1, 0);         // , $this->oStat
			$this->pdf->writeHTMLCell(80, 0, 0, 0, "", 0, 0, 0);		 
			$this->pdf->writeHTMLCell(40, 0, 0, 0, " ", 0, 0, 0);     
			$this->pdf->writeHTMLCell(80, 0, 0, 0, "$this->dPSC, $this->dMesto", 0, 1, 0);         // , $this->oStat
		}
		  
		$this->insertBreak();
		
		$this->pdf->writeHTMLCell(120, 0, 0, 0, "Mobil: <b>$this->jMobil</b>", 0, 0, 0);		 
		$this->pdf->writeHTMLCell(50, 0, 0, 0, "", 0, 0, 0);    
		$this->pdf->writeHTMLCell(30, 0, 0, 0, "", 0, 1, 0);     
		
		$this->pdf->writeHTMLCell(120, 0, 0, 0, "E-mail: <b>$this->jEmail</b>", 0, 0, 0);		 
		$this->pdf->writeHTMLCell(50, 0, 0, 0, "Datum vystavení faktury:", 0, 0, 0);     
		$this->pdf->writeHTMLCell(30, 0, 0, 0, "<b>" . ($this->dzp) . "</b>", 0, 1, 0);    
		
		$this->pdf->writeHTMLCell(120, 0, 0, 0, $this->jWWW, 0, 0, 0);		  
		$this->pdf->writeHTMLCell(50, 0, 0, 0, "Datum splatnosti:", 0, 0, 0);     
		$this->pdf->writeHTMLCell(30, 0, 0, 0, "<b>" . ($this->splatnost) . "</b>", 0, 1, 0);   
			
		if($this->disasbleDPH){ 
			
		} else {
			$this->pdf->writeHTMLCell(120, 0, 0, 0, '', 0, 0, 0);		  
			$this->pdf->writeHTMLCell(50, 0, 0, 0, "Datum zdanitelného plnění:", 0, 0, 0);      
			$this->pdf->writeHTMLCell(30, 0, 0, 0, "<b>" . ($this->dzp) . "</b>", 0, 1, 0);    
		}
		
		$this->insertBreak();  
		
		$this->pdf->writeHTMLCell(120, 0, 0, 0, "", 0, 0, 0);		 
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "Bankovní spojení: <b>$this->jBanka</b>", 0, 1, 0);   
		$this->pdf->writeHTMLCell(120, 0, 0, 0, $this->jZapsan, 0, 0, 0);	 
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "Č. účtu: <b>$this->jCU</b>", 1, 1, 0);  
		$this->pdf->writeHTMLCell(120, 0, 0, 0, $this->jDPH, 0, 0, 0);		   
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "Variabilní symbol: <b>$this->vs</b>", 1, 1, 0);   
	
		$this->pdf->writeHTMLCell(120, 0, 0, 0, "", 0, 0, 0);		 
		$this->pdf->writeHTMLCell(80, 0, 0, 0, "Forma úhrady: <b>$this->jFU</b>", 1, 1, 0); 
		 
		$this->insertBreak();   
		$this->insertBreak(); 
		
		$this->pdf->setCellHeightRatio(2.6);  		
		
		if($this->isProforma === 1){
			$this->pdf->writeHTMLCell(200, 0, 0, 0, "Výzva k platbě (faktura Vám bude vystavena po zaplacení)", 0, 1, 0);   
		} elseif($this->isProforma === 0) {
			
		$this->pdf->SetFont("arialce", "", 11); 	 
			$this->pdf->writeHTMLCell(200, 0, 0, 0, "Fakturace zakoupeného zboží <Br>  <b>Záloha zaplacena - NEPLAŤTE</b>  (zaplaceno na základě výzvy k platbě č. Z$this->vs).", 0, 1, 0); 						 
		} else {
			$this->pdf->writeHTMLCell(200, 0, 0, 0, "Fakturace zakoupeného zboží ", 0, 1, 0);
		}
			 
		$this->pdf->setCellHeightRatio(1.2);   
		
		$this->pdf->writeHTMLCell(140, 0, 0, 0, "<b>Zboží</b>", 1, 0, 0);		  
		$this->pdf->writeHTMLCell(25, 0, 0, 0, "<b>Počet</b>", 1, 0, 0);   
		$this->pdf->writeHTMLCell(35, 0, 0, 0, "<b>Cena</b>", 1, 1, 0);     
		
		// OBJEDNAVKA
	 	
		$mp = new module_Eshop_PriceCalc($this->voucher, $this->items, $this->deliveryPrice, $this->paymentPrice);
		 
		/*foreach ($this->items as $item) {
			if($this->isProforma === 1 || $this->isProforma === 5){
				$price = $item->getPrice();
				$sum = $this->sum; 
			} else {
				$price = $item->getPrice(); 
				$sum = $this->sum; 
			}   
			$this->drawObjednavkaLine($item->title, $item->count, $price);	 
			
		}   
		if($this->deliveryPrice){
		   	$sum += $this->deliveryPrice;
		}  
			$dph = $sum / 1.2 * 0.2;   
			$dph = round($dph, 2);    
			$sumBezdph = $sum - $dph;   
 			
			$celkem = round($sum);   */
			
			// celkem		
			$this->pdf->writeHTMLCell(165, 0, 0, 0, 'Celkem bez DPH:', 1, 0, 0, true, 'R');  
			$this->pdf->writeHTMLCell(35, 0, 0, 0, '' . ($mp->sumBezdph) . ' Kč', 1, 1, 1);       
			
			$this->pdf->writeHTMLCell(165, 0, 0, 0, 'DPH (20%):', 1, 0, 0, true, 'R');   
			$this->pdf->writeHTMLCell(35, 0, 0, 0, '<b>' . ($mp->dph) . ' Kč</b>', 1, 1, 1);     
			 
			if($this->deliveryPrice){     
				$this->pdf->writeHTMLCell(165, 0, 0, 0, 'Doručení:', 1, 0, 0, true, 'R');   
				$this->pdf->writeHTMLCell(35, 0, 0, 0, '' . helper_FrontEnd::price($this->deliveryPrice) . ' Kč', 1, 1, 0);   
			}	 
			if($this->paymentPrice){     
				$this->pdf->writeHTMLCell(165, 0, 0, 0, 'Doprava:', 1, 0, 0, true, 'R');   
				$this->pdf->writeHTMLCell(35, 0, 0, 0, '' . helper_FrontEnd::price($this->paymentPrice) . ' Kč', 1, 1, 0);   
			}	 
			
			if($this->voucher)
			{
				$this->pdf->writeHTMLCell(165, 0, 0, 0, 'Mezisoučet:', 1, 0, 0, true, 'R');   
				$this->pdf->writeHTMLCell(35, 0, 0, 0, '' . helper_FrontEnd::price($mp->subtotal) . ' Kč', 1, 1, 0);   
				$this->pdf->writeHTMLCell(165, 0, 0, 0, 'Kupón '. $this->voucher['title']. ' ('.$this->voucher['code'].')', 1, 0, 0, true, 'R');   
				$this->pdf->writeHTMLCell(35, 0, 0, 0, '' . helper_FrontEnd::price($mp->voucher['value']) . ' Kč', 1, 1, 0);   
			}
					
			
			if($this->isProforma === 1){
				// celkem		
				$this->pdf->writeHTMLCell(165, 0, 0, 0, 'Celkem k úhradě:', 1, 0, 0, true, 'R');   
				$this->pdf->writeHTMLCell(35, 0, 0, 0, '<b>' . ($mp->sum) . ' Kč</b>', 1, 1, 1);    
			} elseif($this->isProforma === 5){
				// celkem		
				$this->pdf->writeHTMLCell(165, 0, 0, 0, 'Celkem k úhradě:', 1, 0, 0, true, 'R');   
				$this->pdf->writeHTMLCell(35, 0, 0, 0, '<b>' . ($mp->sum) . ' Kč</b>', 1, 1, 1);       
			} else {
				$this->pdf->writeHTMLCell(165, 0, 0, 0, 'Celkem k úhradě:', 1, 0, 0, true, 'R');   
				$this->pdf->writeHTMLCell(35, 0, 0, 0, '<b>0 Kč</b>', 1, 1, 1);    
			}    
		 
		// 
		$this->pdf->writeHTMLCell(200, 0, 0, 260, "<h3>Velmi Vám děkujeme za využití našich služeb.</h3>", 0, 1, 0);        
		// $this->pdf->writeHTMLCell(200, 0, 0, 0, "Těšíme se na další spolupráci s Vámi.", 0, 1, 0);  
		ob_clean(); 
		 
		$title = Utils::generatePathNameSimple($title); 
		 
		if($save){  
			$this->pdf->Output($title . '.pdf', 'F'); 
		} else { 
			$this->pdf->Output($title . '.pdf', 'D');  
		}
	} 
	
	function drawObjednavkaLine($title, $pocet, $cena, $sum = false){
		$pdf2 = clone $this->pdf;
		$startY = $pdf2->GetY(); 
		$pdf2->writeHTMLCell(140, 0, 0, 0, $title, 1, 1, 0);
		$endY = $pdf2->GetY();	 
		
		$height = ($endY - $startY);  
		$this->pdf->writeHTMLCell(140, $height, 0, 0, $title, 1, 0, 0); 
		$this->pdf->writeHTMLCell(25, $height, 0, 0, $pocet, 1, 0, 0);  
		$this->pdf->writeHTMLCell(35, $height, 0, 0, $cena . ' Kč', 1, 1, 0);    
	}   
	   
	function insertBreak(){
		$this->pdf->setCellHeightRatio(0.2);	 
		$this->pdf->writeHTMLCell(100, 0, 0, 0, " ", 0, 1, 0);     // break
		$this->pdf->setCellHeightRatio(1.2);    
	}
	
}

class module_Faktura_Item {
	public $price, $count, $title = 0, $productZnacka, $productKod, $weight;
	
	public function __construct($price = 0, $count = 0, $title = '', $productZnacka = '', $productKod = '', $weight = 0, $variantId) {			
		$this->price = $price;
		$this->count = $count;
		$this->title = $title;
		$this->productZnacka = $productZnacka;
		$this->productKod = $productKod;
		$this->weight = $weight;
		$this->variantId = $variantId;
	}
	
	public function getPrice(){
		return $this->price * $this->count;
	}
	
	public function getWeight(){
		return $this->weight * $this->count; 
	}
}

?>