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
	
	
	public function __construct($basket, $view = null) {
		$this->basket = $basket; 
		$this->db = Zend_Registry::getInstance()->db;	 
		$this->tree = Zend_Registry::getInstance()->tree;		
		if($view){
			$this->view = $view;		
		}
		
		$this->tempBasket = $this->view->session->basket;
		if(!is_array($this->tempBasket)){
			$this->tempBasket = $this->view->session->basket = array();
		}
		
		$this->webUser = Zend_Registry::getInstance()->session->webUser;
		
		// pr($this->webUser); 
		//$this->initDeliveryTypes();
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
			'orderSent' => 'Objednávka odeslána',
			'orderReady' => 'Objednávka odeslána na prodejnu', 
			'orderStorno' => 'Objednávka stornována', 
			'orderNone' => 'Objednávka vrácena do nevyřešeného stavu',
			'orderDelete' => 'Objednávka smazána',
			'orderReklamace' => 'Objednávka reklamována'  
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
		$data = (array) $data;   
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
			'fu_dic' => $data['fu_dic'],
			'stat' =>  $data['stat'],
			'fu_stat' =>  $data['fu_stat'],
			'suma' => $sum
		); 
		
		$where = $this->db->quoteInto('id = ?', $oId); 
		$this->db->update(
			$this->_tableName, 
			$data,
			$where
		);	
	

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
		
		
	public function getOrdersBackend($state = 'nf', $deliveryType = '0', $sort = 'created', $sortType = 'desc', $limitStart = 0, $limitCount = 1500, $params = array())
    {
    	$select =  $this->db->select();
		if($params['cnt']){
		$c =  new Zend_Db_Expr("count('*')"); 
    	$select->from(array('o' => $this->_tableName), "count('*') as cnt") ; 
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
    	
    	if($state){	
		switch ($state){
			default:
			case 'on': // nevyrizene
				$select->where("(paid = 1 AND sent = 0) OR (p.paymentFirst = 0 AND sent = 0 AND paid = 0)");  
				$select->where("state=''");
				//$select->where("state = '' OR state = 'RPBCP' OR state = 'RPBGP' OR state = 'RPC'");  
				break;   
			case 'or':    //reklamovane
				$select->where("state = 'COMP'");  
				break;  
			case 'ov':  //vyrizene
				$select->where(' paid = 1 AND sent =  1');
				//$select->where("state = 'RPDCP' OR state = 'RPCS' OR state = 'RPDGP' OR state = 'RPDPPL' OR state = 'RPOOO'  OR state = 'WPDPPL' OR state = 'WPOOO' OR state = 'WPDGP'");  
				break;   
			case 'onall':     //nepouzito ?
				$select->where("state != '' OR state != 'RPBCP' OR state != 'RPBGP'");  
				break;  
			case 'wo':    //cekajici   
				//$select->where("state = 'WPBCP' OR state = 'WPDCP' OR state = 'WPBGP'"); 
				$select->where('(p.paymentFirst = 1 AND sent = 0 AND paid = 0) OR (sent = 1 AND paid = 0)');
				break;    
			case 'os': //storno
				$select->where('state = ?', 'SS'); 
				break;   
			case 'all':  
				break;  
		}
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
		if(!$params['cnt']){
			$select->group('o.id');
		}
		if($_SERVER['REMOTE_ADDR'] == 'asdaf147.32.120.238')	
			print_r($select->__toString());    
		$all = $this->db->fetchAll($select);		
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
    	$c =  new Zend_Db_Expr("count('*')"); 
    	$select->from(array('o' => $this->_tableName), array( $c )); 
    		    	
    	$select->join(
			array('f' => $this->_tableNameFaktura),
        	"o.id = f.oid",  
        	array('ident' => 'vs', 'oid', 'sum')   
        );  
        
    	
		//    e($select->__toString());    
		return $this->db->fetchOne($select); 
    }
    
    public function getOrder($id)
    {
    	$select =  $this->db->select();
    	$select->from(array('u' => $this->_tableName), array( '*'));
    	$select->where('id = ?', $id); 
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
    	$where = "(itemNodeId = ? AND variantId = ?)";
    	$values = array($item['nodeId'], $item['variantId']);
    	
    	foreach($values as $value){
    		$where = $this->db->quoteInto($where, $value , '', 1);
    	}
    	$data = array('count' => $item['count']);
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
    	
    	$dbCount = $this->db->fetchOne('select count from '.$this->_tableItemsName.' where itemNodeId = ? ',$item->nodeId);
    	if($dbCount)
    	{
    		$c = $count + $dbCount;
    		$data = array('count' => $c);
    		$where = $this->db->quoteInto(' itemNodeId = ? ', $item->nodeId);
    		$this->db->update($this->_tableItemsName, $data,$where);
    	}
    	else{
    		$mSet = Zend_Registry::getInstance()->settings;
    		$dph = $mSet->getSettingValue('dph');
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