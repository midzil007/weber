<?php

header ( 'Content-Type: text/html; charset=utf-8' );

class module_HeurekaPrices 
{   
	function __construct($domain = ''){
		$this->db =  Zend_Registry::getInstance()->db;
		$this->_tableName = 'module_HeurekaPrices';
		$this->_tableNameVar = 'module_eshop_variants';
		$this->_tableNameLimit = 'module_HeurekaLimit';
		$this->mCronChecker = new module_CronChecker();
		$this->mVarianta = new module_Varianta();
	}
	
	
	/*
	 * table
	 * title	
	2	c_id	
	3	eshop	
	4	price	
	5	alert
	 * heureka_url
	 * */
	
	
	/*
	 * 
	 *   <p><strong><a href="http://www.heureka.cz/exit/mall-cz/1756330647/?z=2&t=9e6cab035f475afd761fb5c97d987da0&p=2" class="pricen" target="_blank">17 499 KÄŤ</a></strong></p>
	 * */
	 
	 public function checkAlert()
	 {
	 	$mProd = new module_Products();
		$da['alert'] = 0 ;
		$this->db->update($this->_tableName,$da);
	 	$products = $mProd->getProducts(false,false,0,99999, $params,$view);
		foreach ($products as $value) {
			$allheureka = $this->db->fetchall("select id,price from ".$this->_tableName." WHERE price < ".$value['heureka-hlid']." and c_id=:n", array('n' => $value['cid']));
			foreach ($allheureka as $val) {
				$wh = $this->db->quoteInto("id =?",$val['id']);
				$data['alert']=1;
				$this->db->update($this->_tableName,$data,$wh);
			}
		}
		return false;
	 }
	 
	 private function saveLimit($min = 0)
	 {
	 	$this->db->delete($this->_tableNameLimit);
	 	$data['value'] = $min;
		$this->db->insert($this->_tableNameLimit,$data);
	 }
	 
	 public function getLimits()
	 {
	 	return $this->db->fetchOne("select value from ".$this->_tableNameLimit);
	 }
	 
	 public function getSelectList($arrayName,$arrayId)
	 {
	 	$all = $this->db->fetchALL("select ".$arrayId.",".$arrayName." from ".$this->_tableName." group by ".$arrayId);
		$re = array();
		foreach ($all as  $value) {
			$re[$value[$arrayId]] = $value[$arrayName];
		}
		return $re;
	 }
	 
	 
	private function initSearch( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params, $count = false)    
    {
    	$select =  $this->db->select();
		$bind = array();	 
		if($count){    		
    		$c =  new Zend_Db_Expr("count('c_id')"); 
    		$select->from(array('cm' => $this->_tableName), array( $c )); 
    	} else {
    		$select->from(array( 'cm' => $this->_tableName), array('*')); 		  
    	}
    	
		 $select->join(
			 array('nc' => $this->_tableNameVar), 
        	 'cm.c_id = nc.id_product',
        	 array('nc.price as ourprice') 
         );
//         
        // $select->join(
			// array('n' => 'Nodes'),
        	// 'n.id = nc.n_id',
        	// array('n.title') 
        // );
        if($params['alert'])
		{
			$select->where('cm.alert =?', '1');
		}
		
		if($params['min']){
			$select->where('cm.price <= (nc.price - '.$params['min'].')');
			$this->saveLimit($params['min']);	
		}
		
        
        if($sort == 'price')
		$sort ='cm.price';
		
		if($params['search']){
			$params['search'] = str_replace('&quot;', '"', $params['search']);
			$select->where("`title` LIKE  '%" . $params['search']."%'");
		}
		$sortType = $sortType?$sortType:'Asc';
		$select->order($sort . ' ' . $sortType); 
		$select->limit($limitCount, $limitStart);  
		pr($select->__toString());
		return array($select, $bind); 
    }   
    
    public function getData( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5,$params)   
    {
		list($select, $bind) = $this->initSearch($sort, $sortType, $limitStart, $limitCount,$params);		
		return $this->db->fetchAll($select, $bind);	 	 
    }   
     
    public function getDataCount( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5,$params)   
    {  
    	list($select, $bind) = $this->initSearch($sort, $sortType, $limitStart, $limitCount, $params, true); 		
		return $this->db->fetchOne($select, $bind);	  	  
    }   
	 
	 
	public function sendResult()
	{
		$params['min'] = $this->getLimits();
		$params['alert'] = true;
		$all = $this->getData('price','asc', 0, 2000,$params);
		pr($all);
		$ids = array();
		foreach ($all as $value) {
			if(!in_array($value['c_id'], $ids)) {
				$ids[]= $value['c_id'];
				$products[] = $value;		
				}
		}
		if($products)
		{
			$html ='<p>Výsledky z Heureka kde cenový rozdíl je -'.$params['min'].' Kč od naší ceny.</p>';
			$html .= '<table>';
			$html .= '<tr>';
			$html .= '<td><strong>Název</strong></td>';
			$html .= '<td><strong>E-shop</strong></td>';
			$html .= '<td><strong>Cena</strong></td>';
			$html .= '<td><strong>Naše cena</strong></td>';
			$html .= '</tr>';
			foreach ($products as $value) {
				$html .= '<tr>';
				$html .= '<td><a href="'.$value['heureka_url'].'">'.$value['title'].'</td>';
				$html .= '<td>'.$value['eshop'].'</td>';
				$html .= '<td>'.helper_FrontEnd::price(round($value['price'])).' Kč</td>';
				$html .= '<td>'.helper_FrontEnd::price(round($value['ourprice'])).' Kč</td>';
				$html .= '</tr>';
			}
			$html .= '<table>';
			$this->mCronChecker->sendEmail('Upozornění na ceny Heureky Shop-Jura', $html,'miloslav.skrha@gmail.com');
		}
	} 
	
	public function separetePath($path)
	{
		$html = implode('', file($path));
		$temp = explode('product', $html);
		$tempp = explode('<a href="', $temp[1]);
		$tempps = explode('">', $tempp[1]);
			$tessmpps = explode('/"', $tempps[0]); 
		$path = $tessmpps[0];
		return $path;		
	}
	
	public function loadProducts($start,$stop)
	{
		$mProd = new module_Products();
		$start = $this->mCronChecker->getPosition();
		$this->mCronChecker->incPosition(false,20); 
		$products = $mProd->getProducts(false,false,$start,20, $params,$view); 
		foreach ($products as $value) {
			$titleprod = str_replace('  ', ' ', $value['title']);
			$title = str_replace(' ', '+', $titleprod);
			$path = 'http://www.heureka.cz/?h%5Bfraze%5D='.$title; 
			$newpath = $this->separetePath($path);
			$where = $this->db->quoteInto('c_id = ?', $value['cid']);
			$this->db->delete($this->_tableName,$where);
			//pr($newpath); 
			//die;   
			$this->loadSaveSource($newpath, $titleprod,$value['cid']);
		}
		return false;
	}
	
	private function loadSaveSource($path, $titleprod,$cid)
	{
		if($path){
			$html = implode('', file($path.'?expand=1'));
			$temp = explode('shoppr', $html); 
			unset($temp[0]);
			foreach ($temp as $value) {
				$tm = explode('class="pricen" target="_blank">', $value);
				$tmm = explode('Kč', $tm[1]);
				$price = trim(str_replace(' ', '' , $tmm[0]));
				$tm1 = explode('link', $value);
				$t = explode('" width="130" height="40"',$tm1[1]);
				$ta = explode('</a></p>',$tm1[1]);
				$a = explode('">',$ta[0]);
				$title =$a[1]; 
				$data['c_id'] = $cid;
				$data['eshop'] = $title;
				$data['price'] = $price;
				$data['title'] = $titleprod;
				$data['alert'] = 0;
				$data['heureka_url'] = $path.'?expand=1';
				$this->db->insert($this->_tableName,$data);
			//insert db
			}
		}
	}
	    
}
?>