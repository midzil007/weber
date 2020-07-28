<?
  
class module_Voucher{ 
	public $status = array('1' => 'platný','2'=>'použitý','3'=>'propadlý'); //, '4'=>'propadlý', '5'=>'smazaný' , '6' => 'čeká na platnu'
	public $type = array('1' => 'procentní', '2' => 'peněžní');
	//vydaný, použitý, propadlý,vydaný-zaplaceny , smazani, čeká na platnu
	
	public $_tableName = 'module_Voucher';
	/*public $_tableNameCode = 'Voucher_Code';
	public $_tableNameBuy = 'module_VoucherBuys'; 
	public $_tableNameRestaurant = 'content_Restaurant'; 
	public $_tableNameStats ='module_VoucherDownloads';
	public $_tableNameBP = 'module_PointsBP';
	public $_tableCouponDown ='module_CouponDownloads';*/
	
	public function __construct() {
		$this->db =  Zend_Registry::getInstance()->db; 
	} 
	
	function getFreePoints(){
		return $this->db->fetchRow("select sum(points) as sum, count(points) count from `" . $this->_tableNameBP ."`"); 
	}
	
	function getSumVoucher(){
		return $this->db->fetchOne("SELECT sum(value) FROM `" . $this->_tableNameCode ."` as c, `" . $this->_tableName ."` as v where `idVoucher` = v.id and status = 3");
	}
	
	function getCountVoucher(){
		return $this->db->fetchOne("select count(id) as pocet from `" . $this->_tableNameCode ."` "); 
	}
	  
	function getPayVouchersCount() {
		return $this->db->fetchone("select count(id) from `" . $this->_tableNameBuy ."`");	 	
	}
	
	
	function isActive($code,$view){
		// datum koupeni 
		//?? divny
		/*
		$dateCode =  strtotime($this->db->fetchOne("select dateUsed from  `" . $this->_tableNameCode ."` where code =:c", array('c' => $code)));
		$today = strtotime("now");  
		
		if ($today > $dateCode){
			return true;
		} else{
			return false;
		}*/
		
		$status =  $this->getStatusByCode($code);  
		if ($status == 3 || $status == 1){
			return true;  
		} else {
			return false; 
		}
	}
	
	// vrati id voucheru, pokud existuje platny voucher s kodem $code
	public function getVoucherByCode($code){
		$select = $this->db->select();
		$select2 = $this->db->select(); //kvuli datu
		$select->from($this->_tableName);
		$select->where('code = ?',$code);
		$select2->from($this->_tableName);
		$select2->where('code = ?',$code);
		//$select->where('status = ?',1);
		$select2->where('valid >= ?', date('Y-m-d'));
		
		$ret = $this->db->fetchRow($select); //$this->db->fetchOne("SELECT id_v WHERE code=$code AND status=1 AND valid >='".$date."'");
		$ret2 = $this->db->fetchRow($select2);
		if(($ret['status'] == 1) AND (!$ret2)){ //voucher je prosly
			$ret['status']=3;
		}
		
		return $ret?$ret:0;
	}
	
	function getStatusByCode($code){
		return $this->db->fetchOne("select status from  `" . $this->_tableName ."` where code =:c", array('c' => $code));
	}
	
	public function addOrder($vid,$oid){
		$data = array( 'id_order' => $oid);
		$where = $this->db->quoteInto('id_v = ?', $vid);
		$this->db->update($this->_tableName, $data, $where);
	}
	
	public function initSearch( $sort = 'n.id', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params = false)   
    {
    	  
    	$select =  $this->db->select();
		$bind = array();
		$langTitle = 'v.title';
		$langDescr = 'v.descr';
		
		if($view->language == 'en'){
			$langTitle .= '_'.$view->language;
			$langDescr .= '_'.$view->language; 
		}
		if($params['count']){    		
    		$c =  new Zend_Db_Expr("count('id') as pocet"); 
    		$select->from(array('v' => $this->_tableName), array( $c));
    	} else if($params['sum']) {
			$select->from(array( 'v' => $this->_tableName), array('sum( code ) * v.`value` AS value, count( code ) * v.`pricePoints` AS points'));    		 		         
    		}
    	else{ 
    		$select->from($this->_tableName , array('id_v','title', 'code','status','type','category','countFrom','value','id_order','valid','created'));
    		//$select->from($this->_tableName , array('id_v','title', 'code','status','type','value','id_order','valid'));
    	}  
    	
     	/*if(!$params['notJoin']){

           	$select->join(  
			array('c' => $this->_tableNameCode), 
        	'v.id = c.idVoucher',
        	array('code','status','dateUsed') 
        	);
        	
        	$select->joinLeft(  
			array('b' => $this->_tableNameBuy), 
        	'v.id = b.vid',
        	array('fullname','added','forPoints') 
        	);
        	
        	
        	$select->joinLeft(  
			array('d' => $this->_tableCouponDown), 
        	'c.code = d.vcode',
        	array('active', 'custId') 
        	); 
    	}
     		
        if(!$params['deleted']){
        	$select->where('deleted <?', 1);
        }
        
    	 if($params['pays'] ){
        	$select->where('b.cid >?', 0);
        }
        	
        if($params['point']){ 
        	$select->where('pricePoints >?', 0);
        }
        
     	if($params['filtr']>0){
        	if($params['filtr'] == 3){
        		$select->where('status =?', $params['filtr']);        		
        	}
        	else{
        		$select->where('status =?', $params['filtr']);
        	}
        }
                
    	if($params['isAdmin']){
        	$select->where('price > ? OR ( price = ? AND pricePoints = ?)', 0); 
        }
        
     	if($params['gCode']){
        		$select->group('c.code');
        }
       
        
    	if($params['price']){ 
        	$select->where('price >?', 0);
        } 
        
        if($params['showDelete']){
        	$select->where('deleted !=?', '1');
        }  
    
		if($params['id']){
        	$select->where('v.id >= ?', $params['id']);        	
        }
        

    	
    	if($params['status']>0){
    		$select->where('v.status = ?', $params['status']);
    	}		 	 
    	     
        if($params['code']){
        	$select->where('v.code >= ?', $params['code']);        			
        }  
	
        
		$sortType = $sortType?$sortType:'Asc';
		if($params['group']){
			$select->group('v.id');
		}	  */
		
		
		 if($params['search']){
    		$select->where('title LIKE ? OR value LIKE ? OR code LIKE ? OR id_order LIKE ?', '%' . $params['search'] . '%'); 		
    	}
		if($params['status'] > 0){
			if($params['status'] == 3){
				$select->where('status = ?', 1);
				$select->where('valid < ?', date('Y-m-d'));
			}
			else{
				$select->where('status = ?', $params['status']);	
			}
    	}
		if($params['type'] > 0){
    		$select->where('type = ?', $params['type']);	
    	}
		else{
		}
		
		$sort = $sort?$sort:'title';
		$select->order($sort.' ' . $sortType); 
		$select->order('id_v DESC');   
		$select->limit($limitCount, $limitStart);
		//e($select->__toString());
		return array($select, $bind); 
    }

    public function getVouchers( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params = false)   
    {
		list($select, $bind) = $this->initSearch($sort, $sortType, $limitStart, $limitCount, $params);		
		return $this->db->fetchAll($select, $bind);	 	 
    }   

    public function getVouchersAdminCount( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $view, $params = false)   
    {
		
		list($select, $bind) = $this->initSearch($sort, $sortType, $limitStart, $limitCount, $view, $params);		
		return $this->db->fetchOne($select, $bind);	 	 
    }    
    
    public function getVouchersCount( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params = false)   
    {     					
		return $this->db->fetchOne("SELECT count( v.id) FROM `module_Voucher` AS v
									WHERE deleted= '0'");		  	 
    }

    
    public function getCodesStatusByIdVoucher($id){
    	return $this->db->fetchAll("SELECT code, status from `" . $this->_tableNameCode ."` WHERE idVoucher = :v", array('v' => $id));
    	
    }
    
    public function getStatusCountVoucher($id){
    	for ($i = 1; $i < 5; $i++) {
    		$ret = $this->db->fetchOne("SELECT count(*) from `" . $this->_tableNameCode ."` WHERE idVoucher = :v and status =:s", array('v' => $id, 's' => $i));
    		$status[] = $ret;	
    	}
    	return $status; 
    }
	
	public function deleteById($id){
		$this->db->delete($this->_tableName, 'id_v = '.$id);
	}
	
	public function deleteVoucher($vouchers){
		foreach ($vouchers as $v) 
		{
			$data = array(
					'deleted' => 1
					);
			$where = $this->db->quoteInto('id = ?', $v);
			$this->db->update($this->_tableName, $data, $where);
			
			$data ='';
			$where ='';
			$data = array(
				'status' => 5
					);
			$where = $this->db->quoteInto('idVoucher = ?', $v);
			$this->db->update($this->_tableNameCode, $data, $where);
		}
	}
	
	public function getDetailVoucherByCode($view, $code){
		$params['code'] = $code;
		$voucher = $this->getVouchers('','', '','', $view, $params);
		return $voucher[0];
		
	}
	
	public function getDetailVoucherById($id, $lang){ 
		$langTitle = 'title';
		$langDescr = 'descr';
		
		if($lang == 'en'){
			$langTitle .= '_' . $lang; 
			$langDescr .= '_' . $lang; 
		}
		
		return $this->db->fetchRow("SELECT *, $langTitle as t, $langDescr as d FROM $this->_tableName WHERE id = ?", $id);   
	}
	
	public function updateVoucher($view){
	
		if(!$view->input->title){
			$err = 'Zadejte prosím všechny povinné údaje';			  
		}
		if($err){	
				$data = array(
					'title' => $view->input->title,
					'title_en' => $view->input->titleEn,
					'pricePoints' => $view->input->pricePoints,
					'price' => $view->input->price,
					'value' => $view->input->value,
					'descr' => $view->input->descrCZ,
					'descr_en' => $view->input->descrEN,	
					'availableDay' => $view->input->availableDay
					);
			$where = $this->db->quoteInto('id = ?', $view->input->idVoucher);
			$this->db->update($this->_tableName, $data, $where);
			$status = $this->db->fetchAll("SELECT code, status from `" . $this->_tableNameCode ."` WHERE idVoucher = :v", array('v' => $view->input->idVoucher));
			//pr($view->input->codes);
			
			foreach ($view->input as $key=>$value) {
				//e(strpos($key, "select_"));
				//e($key);
				if(strpos($key, '_')>0){
					$t = explode('_',$key);
					};
				$this->updateVoucherStatus($t[1],$value);
			}
			$countVoucher = $this->db->fetchOne("SELECT count(*) from `" . $this->_tableNameCode ."` WHERE idVoucher = :v", array('v' =>  $view->input->idVoucher));
			/*
			$incCount = $countVoucher - $view->input->countVouchers;
			if($countVoucher < $view->input->countVouchers){
				$incCount = $view->input->countVouchers-$countVoucher;
				$this->addCodeVoucher($view->input->idVoucher, $incCount);
			}
			*/
			return array(1, 'Voucher byl upraven.');
			}
		else
			{
			return array(0, $err);	
			}			
	}
	
	public function useVoucher($id){
		$data = array(
			'status' => '2',
			);
		$where = $this->db->quoteInto('id_v =?', $id);
		$this->db->update($this->_tableName,$data,$where);		
	}
	
	public function resetVoucher($id){
		$data = array(
			'status' => '1',
			'id_order' => '0'
			);
		$where = $this->db->quoteInto('id_v =?', $id);
		$this->db->update($this->_tableName,$data,$where);		
	}
	
	public function updateVoucherStatus($code,$status){
				$data = array(
					'status' => $status
					);		
				$where = $this->db->quoteInto('code = ?', $code);
				$this->db->update($this->_tableNameCode, $data, $where);
	}
	
	public function addCodeVoucher($idVoucher,$count, $status = 3){  
				$code = $this->getNextCode();
				$cData = array(
					'idVoucher' => $idVoucher,
					'code' => $code, 
					'status' => $status, 
					'dateUsed'=>new Zend_Db_Expr('NOW()')
				);
				$this->db->insert( 
					$this->_tableNameCode,
  					$cData 
				);
			return $code;
	}
	
	public function addVoucher($data){

				$data['code'] = $this->getNextCode(); 
			
				$this->db->insert( 
					$this->_tableName,
  					$data
				);
			
				$lid = $this->db->lastInsertId();
				return array(1, 'Voucher byl přidán.');
		}  
	
	public function removeDownloadHit() {  
		Zend_Registry::getInstance()->db->query(
    		'UPDATE `' . $this->_tableName . '` SET usedTotal = usedTotal - 1 WHERE cid = ?', 
    		array($this->cid)  
    	);  
	} 
	
		
	function generateCode(){
		$pref = helper_Security::generatePassword(4);
		if(strlen($pref) == 1){
			$pref2 = helper_Security::generatePassword(4);
			$pref .= $pref2{0};  
		}
		return strtoupper($pref) . floor(rand(1000, 9999));     
	}
	
	function getNextCode(){
		do{ 
			$code = $this->generateCode(); 
		} while ($this->codeExists($code));
		
		return $code;
	}
	function codeExists($code){
		$c =  $this->db->fetchOne("SELECT code from `" . $this->_tableName ."` WHERE code = :c", array('c' => $code)); 
		return $c?true:false;  
	} 
	 
}	
?>