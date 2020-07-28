<?
/**
 * Třída reprentuje zalogovaneho uzivatele webu - firmu
 */
class module_Fakturace {
	public $_tableName = 'module_faktura'; 
	 
	public function __construct() { 			
		$this->db =  Zend_Registry::getInstance()->db;
	}
	
	function getNextFakturaVS(){
		$vs = $this->db->fetchOne("SELECT vs FROM `" . $this->_tableName . "` ORDER BY id desc LIMIT 1"); 
		if(!$vs){
			$vs = '090000';
		}
		
		list($year, $number) = $this->parseVS($vs);		
		$yearNow = date('y');
		if($year != $yearNow){
			$number = 1;
		}
		
		$number++;
		$newNum = str_repeat('0', 4 - strlen($number . "")) . $number; 
		$newNum = $yearNow . $newNum;
		return $newNum;
	}
	
	function parseVS($vs){
		$year = substr($vs, 0 , 2); 		
		$no = intval(substr($vs, 2 , 4));
		return array(
			$year,
			$no
		); 
	}
	 	
	function addFaktura($view, $userdata,  $proforma, $items, $redirectUrl, $oid = 0){ 
		
			$varSymbol = $this->getNextFakturaVS();		
			$faktura = new module_Faktura($varSymbol);  
			   
			$faktura->cId = $userdata['custid'];  
			
			$faktura->oid = $oid;   
			$faktura->itemsList = $items;      
			$faktura->sum =   $userdata['suma'];  
 		 	$faktura->deliveryPrice = $userdata['deliveryPrice'];
 		 	$faktura->payment = $userdata['payment'];
 		 	$faktura->delivery = $userdata['delivery'];
 		 	
 		 	if($proforma < 0){
	 		 	if(($faktura->delivery == 'GP' && $faktura->payment == 2) || ($faktura->delivery == 'CP' && $faktura->payment == 2)){
	 		 		$fu = 'bankovní převod';
	 		 		$proforma = 1;
	 		 	} else {  
	 		 		$fu = 'hotově';
	 		 		$proforma = 5;
	 		 	}     
 		 	}
 		 	
 		 	$faktura->isProforma = $proforma; 
 		 	
 		 	$dodaci = '';
 		 	if($userdata['firma']){
 		 		$dodaci .= $userdata['firma'] . ', ';
 		 	}  
 		 	$dodaci .= $userdata['efullname'];
 		 	
			$data = array(  
				'oICO' => $userdata['ico'],
				'oDIC' =>  $userdata['dic'],
				'oName' => ($userdata['firma']?$userdata['firma']:$userdata['efullname']),
				'oAdress' => $userdata['ulice'], 
				'oPSC' => $userdata['psc'],   
				'oMesto' => $userdata['mesto'],  
				'oStat' => $userdata['stat'],      
				'phone' => $userdata['phone'], 
				'dAdress' => $userdata['fu_ulice']?$userdata['fu_ulice']:$userdata['ulice'], 
				'dPSC' => $userdata['fu_psc']?$userdata['fu_psc']:$userdata['psc'], 
				'dMesto' => $userdata['fu_mesto']?$userdata['fu_mesto']:$userdata['mesto'], 
				'dJmeno' => $userdata['fu_jmeno']?$userdata['fu_jmeno']:$dodaci,
				'dStat' => $userdata['fu_stat']?$userdata['fu_stat']:$userdata['stat'] 
			); 
			

				$data['voucher'] = $view->mEshop->basket->voucher;

						  
			$data2 = array( 			
				'jICO' => '27879330',
				'jDIC' => 'CZ27879330', 
				'jName' => 'ART OF COOKING s.r.o.',    
				'jAdress' => 'Bělohorská 186/161', 
				'jPSC' => '160 00',
				'jMesto' => 'Praha 6',  
				'jBanka' => 'Raiffeisenbank', 
				'jMobil' => '+420 774 023 141',    
				'jCU' => '6243269001/5500',			
				'jEmail' => 'eshop@homecook.cz',  
				'jWWW' => 'www.homecook.cz',   
				'jZapsan' => 'Dodavatel zapsán u rejstříkového soudu v Praze - zn. C 123710',
				'jDPH' => 'DPH 20%', 
				'jFU' => $fu    
			);  
			
			$data = array_merge($data, $data2);  
			$faktura->setData($data);  
			foreach ($items as $item){
				$faktura->addItem($item[0], $item[2], $item[1], $item[3], $item[4], $item[5]);	
			} 
			/*
			if($_SERVER['REMOTE_ADDR'] == '89.103.150.78'){
			 	$faktura->generatePdf(false);   die();  
			}
			 */    
			$fId = $faktura->save();     
			$faktura->id = $fId;
			
			
		if($redirectUrl){
			helper_FrontEnd::redirect($redirectUrl . '&z=' . base64_encode($fId), 303);   
		} else {
			return $faktura;  
		}    
	}
	
	function getUserData(){
		$webUser = $this->jobs->getUser(); 
		$staty = $this->jobs->moduleLocation->getStaty2();	
		
		if($webUser->mesto2 && $webUser->ulice2 && $webUser->psc2){
			$data = array(
				'oICO' => $webUser->ico,
				'oDIC' => $webUser->dic,
				'oName' => $webUser->companyName,
				'oAdress' => $webUser->ulice2,
				'oPSC' => $webUser->psc2,
				'oMesto' => $webUser->mesto2,
				'oStat' => $staty[$webUser->stat2],
				'cId' => $webUser->id
			);
		} else { 
			$data = array(
				'oICO' => $webUser->ico,
				'oDIC' => $webUser->dic,
				'oName' => $webUser->companyName,
				'oAdress' => $webUser->ulice,
				'oPSC' => $webUser->psc,
				'oMesto' => $webUser->mesto,
				'oStat' => $staty[$webUser->stat], 
				'cId' => $webUser->id
			);
		}
 
		return $data; 
	}
	
	function getFaktura($view, $id, $data = false, $vs = 0){
		$webUser = $view->webUser;	  
		
		if($data == false){
			if($webUser->isAdmin){
				if($id){
					$data = $this->db->fetchOne( 
						"SELECT data FROM `" . $this->_tableName . "` WHERE id = :id LIMIT 1", 
						array( 
							'id' => $id 
						)
					);  
				} else {
					$cc = $this->db->fetchRow( 
						"SELECT id, data FROM `" . $this->_tableName . "` WHERE vs = :vs LIMIT 1", 
						array( 
							'vs' => $vs   
						)
					);     
					$data = $cc['data'];
					$id = $cc['id'];  
				} 
			} else {
				$data = $this->db->fetchOne( 
					"SELECT data FROM `" . $this->_tableName . "` WHERE cId=:cid AND id = :id LIMIT 1", 
					array(
						'cid' => $webUser->id,
						'id' => $id 
					)
				);  
			}
		}
		
		$data = $this->db->fetchOne( 
						"SELECT data FROM `" . $this->_tableName . "` WHERE id = :id LIMIT 1", 
						array( 
							'id' => $id 
						)
					);  
					 
		if($data){
			$faktura = new module_Faktura(0);  
			$faktura->setData(unserialize($data)); 
			$faktura->id  = $id; 
 
			return $faktura;
		} else {
			return false; 
		}
	}
	
	function getFakturaByOid($id){
		$r = $this->db->fetchRow( 
			"SELECT id, data FROM `" . $this->_tableName . "` WHERE oid = :id ORDER BY id DESC LIMIT 1", 
			array( 
				'id' => $id   
			)
		);  
		
		$data = $r['data'];
		$fid = $r['id'];
		 
		if($data){
			$faktura = new module_Faktura(0);  
			$faktura->setData(unserialize($data)); 
			$faktura->id = $fid;  
 
			return $faktura;
		} else {
			return false; 
		} 
	}
	
	/**
	 * hotfix, vrati items z module_eshop_order_items misto z faktury
	 * TODO opravit poradne
	 * */
	function getItemsByFaktId($faktId)
	{
		$sql = 'SELECT oid FROM ' . $this->_tableName . ' WHERE id = ' . $faktId;
		$oid = $this->db->fetchOne($sql);
		//echo "oid:";
		//print_r($oid);
		$select = $this->db->select();
		$select->from('module_eshop_order_items')
				->where('o_id = ?', $oid);
		$stmt = $this->db->query($select);
		$ret = $this->db->fetchAll('SELECT itemNodeId, variant, price, count FROM module_eshop_order_items WHERE o_id = ' . $oid);
		//print_r($ret);
		$mp = new module_Products();
		
		$items = array();
		foreach($ret as $row)
		{
			$item = array();
			$item = $row;
			$tmp = $mp->getProductByNodeIdVariant($row['itemNodeId'], $row['variant']);
			$item['nazev'] = $tmp['nazev'];
			$items[] = $item;//$mp->getProductByNodeIdVariant($row['itemNodeId'], $row['variant']);
		}
		//print_r($items);
		return $items;
	}
	
	function initSearchSelect($view, $input2, $sort = 'nazev', $sortType = 'Desc', $count = false, $nezaplacene, $admin){
    	$select =  $this->db->select();
    	$bind = array();
    	$webUser = $this->jobs->getUser();	
    	
    	$input = clone $input2;
    	
		
    	if($nezaplacene){
    		$select->where('u.`proforma` = ?', '1');
    	} else { 
    		$select->where('u.`proforma` = ?', '0'); 
    	}
    	if($admin != 1){
			$select->where('u.`cId` = ?', $webUser->id);  
    	} 
		
		unset($input->active);		
    	unset($input->perform);
    	unset($input->page);
    	unset($input->s);
    	unset($input->st);
    	
		    	
    	if($count){    		
    		$c =  new Zend_Db_Expr("count('*')");
    		$select->from(array('u' => $this->_tableName), array( $c ));
    	} else {
    		//'comp.id', 
    		$select->from(array('u' => $this->_tableName), array( 'u.id', 'u.cId',  'vs', 'sum', 'data', 'comp.company'));	 	   
    	} 
    	 
    	$select->join(
			array('comp' => $this->_tableNameFirmy),
        	"comp.id = u.cId",
        	array()  
        ); 
         
 		// e($select->__toString());  
		//e($bind);     
		return array($select, $bind);
    }
    
    public function searchFakturyCount($view, $input, $sort = 'created', $sortType = 'Desc', $limitStart = 0, $limitCount = 30, $nezaplacene, $admin = 0) 
    {
    	if(!$input){
    		$input = new stdClass();
    	}
    	
    	list($select, $bind) = $this->initSearchSelect($view, $input, $sort, $sortType, true, $nezaplacene, $admin); 
    		
    	try {	
    		return $this->db->fetchOne($select, $bind); 
    	} catch (Exception $e) {
			return 0;
	    }
    } 
     
    public function searchFaktury( $view, $input, $sort = 'created', $sortType = 'Desc', $limitStart = 0, $limitCount = 30, $nezaplacene, $admin = 0)  
    {
    	if(!$input){
    		$input = new stdClass();
    	}
    	//e($input);    	
    	list($select, $bind) = $this->initSearchSelect($view, $input, $sort, $sortType, false, $nezaplacene, $admin);
    	 
		if($sort != 'RAND()'){
			$sortType = $sortType?$sortType:'Asc'; 
		}
		
		$select->order($sort . ' ' . $sortType);
		//e($sort . ' ' . $sortType);
		$select->limit($limitCount, $limitStart);
		
		/*
		e($bind);
		e($select->__toString());   
		*/ 		
		try {	
    		$all = $this->db->fetchAll($select, $bind);
    		$faktury = array();
    		foreach ($all as $f) {
    			$faktura = $this->getFaktura($f['id'], $f['data']);
    			$faktura->companyName = $f['companyName']; 
    			//e($faktura); die(); 
    			 
    			$faktury[$f['id']] = $faktura;   
    		}
    		return $faktury; 
    	} catch (Exception $e) {
			return array();
	    }	    
    }
}

?>