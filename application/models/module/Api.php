<?php

class module_Api
{   
		function __construct($domain = ''){
		$this->db =  Zend_Registry::getInstance()->db;
		$this->tree =  Zend_Registry::getInstance()->tree;
		$this->_tableName = 'content_Product';
		$this->_tableNameVariants = 'module_eshop_variants';
		$this->_tableNameOrder = 'module_eshop_orders';
		$this->_tableNameOrderItem = 'module_eshop_order_items';
	}    
		
	public function getSection()
	{
		// bohužel staticky protože neznám částku
		$ret[] = array(id=>7767,
						title => 'Tablety a Filtery', 
						descr => 'Šetrná doprava, která zajistí doručení zásilky bez poškození až k Vám domů. Cena dopravy je 150 Kč, 
						při zakoupení zboží nad 1 000 Kč zdarma',
						price => '150');
		$ret[] = array(id=>7764,
						title => 'Doplňky', 
						descr => 'specSHOP s.r.o. - Chrudimská 1146, Pardubice',
						price => '0');
		$ret[] = array(id=>75261,
						title => 'Hadičky a koncovky', 
						descr => 'specSHOP s.r.o. - Chrudimská 1146, Pardubice',
						price => '0');
		$ret[] = array(id=>74680,
						title => 'Přislušenství profi', 
						descr => 'specSHOP s.r.o. - Chrudimská 1146, Pardubice',
						price => '0');
						  
		$ret[] = array(id=>7768,
						title => 'Výběrová káva', 
						descr => 'specSHOP s.r.o. - Chrudimská 1146, Pardubice',
						price => '0');
						
		$ret[] = array(id=>7763,
						title => 'Šálky a lžičky', 
						descr => 'specSHOP s.r.o. - Chrudimská 1146, Pardubice',
						price => '0');
		$ret[] = array(id=>1,
						title => 'Kávovary', 
						descr => 'specSHOP s.r.o. - Chrudimská 1146, Pardubice',
						price => '0');
		return $ret;    
	}	
		
	public function login($email)
	{  
		return $this->db->fetchRow("SELECT * FROM ".$this->_tableNameOrder." WHERE email = ? order by id desc",$email);   
	}   
	
	public function getDelivery($id)
	{
		// bohužel staticky protože neznám částku
		$ret[] = array(id=>19,
						title => 'Šetrná doprava', 
						descr => 'Šetrná doprava, která zajistí doručení zásilky bez poškození až k Vám domů. Cena dopravy je 150 Kč, 
						při zakoupení zboží nad 1 000 Kč zdarma',
						price => '150');
		$ret[] = array(id=>20,
						title => 'Osobní odběr - Pardubice', 
						descr => 'specSHOP s.r.o. - Chrudimská 1146, Pardubice',
						price => '0');
		return $ret;    
	}
	
	public function getPayment($id)
	{
		$mPayment = new module_Delivery(); 
		$pay = $mPayment->getAppValidPayments(19);
		foreach ($pay as $value) {
			$item['title'] = $value['nazev'];
			$item['id'] = $value['id'];    
			$item['descr'] = strip_tags($value['popis']); 
			$item['price'] = $value['cena'];
			$retPay[] = $item;
		}  
		return $retPay;   
	}
	 
	public function getVideo($ids)
	{
		if(!$ids)$ids = 76008;  // e8 
		$params['souvisejici'] = "'".str_replace(';', "','", $ids)."'";   
		$mProducts = new module_Products();
		$machines = $mProducts->getProducts(false,false,0,1000000,$params);
		foreach ($machines as $key => $value) {  
			$node = $this->tree->getNodeById($value['id']);   
			$content = $node->getTheRightContent();
			$video = $content->getPropertyValue('video');   
       	 	preg_match_all('/<iframe[^>]+src="([^"]+)"/', $video, $match);
        	$v = str_replace('<p><strong>', '<h7>', $video);
        	$v2 = str_replace('</strong></p>', '</h7>', $v);  
       	 	preg_match_all('|<strong.*?id=\"([^\"]*)\".*?>(.*)</strong[^>]+>|iU', $video, $headings );
       	 	$urls = $match[1];                 
       	 	$vText = (strip_tags($v2,'<h7>'));    
  			$t = str_replace('</h7>', '', $vText);      
  			$t = explode('<h7>', $t);        
			unset($t[0]);
	    	foreach ($urls as $key => $value) {
	         	$vObj = new stdClass();
	         	$vObj->path = str_replace('?rel=0','',$value);     
	         	$vObj->title = $t[$key+1];   
	         	$data[] = $vObj;   
	        }
		}
		return $data;
	}
	
	  
	public function getDetailProduct($nodeId)
	{
		$data = array(); 
		$mp = new module_Products();
    	$mVarianta = new module_Varianta();  
		$node =  $this->tree->getNodeById($nodeId);
		$c = $node->getTheRightContent();   
		$selectedVariant = $mVarianta->getVariantsByIdProduct($c->id,true,true);	
		$data['title'] = $node->title;
		$data['html'] = str_replace("/data/sharedfiles/", "https://www.shop-jura.cz/data/sharedfiles/", $c->getPropertyValue('html'));   
		$photo = $mVarianta->getResizedPhotos($selectedVariant['obrazky']);
		$pathPhoto = helper_FrontEnd::getFirstPhoto($photo,'pFull' , false);
		if($pathPhoto['path']){    
			$data['photo'] = Utils::getWebUrl(). $pathPhoto['path'];
		}  
		return $data;
	}
	
	public function getPrislusenstvi($nodeId)
	{
		$node = $this->tree->getNodeById($nodeId);
		$content = $node->getTheRightContent();
		$souvisejici = $content->getPropertyValue('souvisejici');
		if($souvisejici) 
		{
			$souv = array();
			$vals = helper_MultiSelect::getMultiSelectValues($souvisejici); 
			$params['souvisejici']= implode(',',$vals); 	
			$mProducts = new module_Products();		 
			return $mProducts->getProducts('soldPrice', 'desc', 0,100, $params,$view);
		}
	} 
	
	public function getProducts($params)
	{
		//pr($params);       
		$mProducts = new module_Products(); 
		$mVarianta = new module_Varianta();       
		  
		$data =  $mProducts->getProducts('soldPrice', 'desc', 0,99999, $params,$view);
		$dataRet = array(); 
		foreach ($data as $value) {   
			$item = new stdClass(); 
			$item->id = $value['id']; 
			$item->title = $value['title']; 
			$item->html = $value['html']; 
			$item->path = $value['path'];  
			$item->price = $value['price'];
			$photos = $mVarianta->getResizedPhotos($value['obrazky']);
			$p = helper_FrontEnd::getFirstPhoto($photos,'pThumbc' , false);
			$item->photo = 'https://www.shop-jura.cz'.$p['path']; 
			$dataRet[]=$item;  
		}  
		return $dataRet;  
	}
		 
public function getOrders( $sort = 'id', $sortType = 'desc', $limitStart = 0, $limitCount = 10000000, $params,$view, $count = false)    
    { 
    	$select =  $this->db->select(); 
		$bind = array();
 
		if($count){    		 
    		$c =  new Zend_Db_Expr("count('id')");  
    		$select->from(array('cm' => $this->_tableNameOrder), array( $c,  $disc ));
		}     		  
    	else {   
    		$select->from(array( 'cm' => $this->_tableNameOrder), array('id','DATE_FORMAT(created, "%d.%m.%y") as created','suma'));     		  
    	}   
    	 
		// $select->join(    
			// array('nc' => $this->_tableNameOrderItem ), 
        	// 'cm.id = nc.o_id',    
        	// array("title","itemNodeId","price","dph","count")  
           
        if($params['email'])
        {
        	$select->where('email =?',$params['email']); 
        }   
		
		$data = $this->db->fetchAll($select);
		if($params['onlyOrder'])
        {
        	return $data;   
        } 
		foreach ($data as $key => $value) {
			$ids[] = $value['id'];
		}
		$select2 =  $this->db->select();   
		if($count){    		 
    		$c =  new Zend_Db_Expr("count('id')");  
    		$select2->from(array('cm' => $this->_tableNameOrderItem), array( $c,  $disc ));
		}     		  
    	else { 
    		$select2->from(array( 'cm' => $this->_tableNameOrderItem), array('*','count(count) as pocet'));  		  
    	}    
		$select2->where("o_id in (".implode(",", $ids).")"); 
		$select2->group('itemNodeId');  
		$orders = $this->db->fetchAll($select2);   
		// $select2 =  $this->db->select();  
		// $orders = array();  
		// foreach ($data as $key => $value) {
			// if($orders[$value['itemNodeId']])
			// {  
				// $count = $orders[$value['itemNodeId']['count']] + $value['count'];     
				// $orders[$value['itemNodeId']]['count'] = $count;  
			// }  
			// else{
				// $newItem = array();
				// $newItem['count'] = $value['count']; 
				// $newItem['title'] = $value['title'];  
				// $newItem['dph'] = $value['dph'];  
				// $newItem['price'] = $value['price'];   
				// $orders[$value['itemNodeId']]= $newItem;	
			// }
		// }  
		// ksort($orders);  
		// pr($orders );   
		return  $orders;  
    }

	public function getOrdersDetail( $sort = 'id', $sortType = 'desc', $limitStart = 0, $limitCount = 10000000, $params,$view, $count = false)    
    { 
    	$select =  $this->db->select(); 
		$bind = array();
 
		if($count){    		 
    		$c =  new Zend_Db_Expr("count('id')");  
    		$select->from(array('cm' => $this->_tableNameOrderItem), array( $c,  $disc ));
		}     		  
    	else { 
    		$select->from(array( 'cm' => $this->_tableNameOrderItem), array('*'));     		  
    	}   
    	 
		// $select->join(    
			// array('nc' => $this->_tableNameOrderItem ), 
        	// 'cm.id = nc.o_id',    
        	// array("title","itemNodeId","price","dph","count")  
           
        if($params['id'])
        {
        	$select->where('o_id =?',$params['id']); 
        }   
		$data = $this->db->fetchAll($select);
		$orders = array();
		foreach ($data as $key => $value) {
			$item = new stdClass();
			$item->title = $value['title'];
			$item->price = $value['price'];
			$item->dph = $value['dph'];
			$item->count = $value['count'];
			$orders[] = $item;
		}    
		return  $orders;  
    }   	 
	
	   	 
}
