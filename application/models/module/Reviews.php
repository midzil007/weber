<?php
header('Content-Type: text/html; charset=utf-8'); 

class module_Reviews
{  
	
	private $key = 'b943273ce13e4bd41d1b12a490f95bdd';
	 
	function __construct($view = false){ 
		$this->db =  Zend_Registry::getInstance()->db;
		$this->config =  Zend_Registry::getInstance()->config; 
		$this->view = $view; 
		$this->tree =  Zend_Registry::getInstance()->tree;
		$this->settings = Zend_Registry::getInstance()->settings;
		$this->_tableName = 'module_Reviews';
		$this->_tableNameProd = 'module_Reviews_Product';
	}
	
	public function deleteReviewById($id){
		$array = array('deleted' => '1');
		$where = $this->db->quoteInto ( 'id = ?', $id);
		$this->db->update($this->_tableName,$array,$where);
	}
	
	public function saveRatingProduct($view)
	{
		$mProd = new module_Products();
		$params['showFirstVariant'] = 1;
		$all = $mProd->getProducts('title','asc',0,10000000,$params);
		foreach ($all as $value) {
			$this->saveRating($value['id']);
		}
		return $all;
	}
	
	public function saveRating($nodeId)
	{
		$all = $this->db->fetchAll("select * from ".$this->_tableNameProd." where id_node = ?",$nodeId);
		$count = $count = $total = 0;
		if($all)
		{
			foreach ($all as $v){
				$count++;
			 $total += $v['rating'];
			}
		}
		$rating = $total/$count;
		if($rating>0){
			$node = $this->tree->getNodeById($nodeId);
			$c = $node->getPublishedContent();
			$where = $this->db->quoteInto ( 'id = ?', $c->id);
			$da['rating'] = $rating;
			$this->db->update('content_Product',$da, $where);
		}
	}
	
	public function deleteReviewProductById($id){
		$array = array('deleted' => '1');
		$where = $this->db->quoteInto ( 'id = ?', $id);
		$this->db->update($this->_tableNameProd,$array,$where);
	}
	
	public function initSearch( $sort = 'date', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params, $count = false)    
    {
    	$select =  $this->db->select();
		$bind = array();
		if($count){
			$c =  new Zend_Db_Expr("count('*')");
			$select->from(array('cm' => $this->_tableName), array( $c ));
		} else {
			$select->from(array( 'cm' => $this->_tableName), array('*'));
		}
		$disableHeurekaReviews = $this->settings->getSettingValue('disableHeurekaReviews');
		if($disableHeurekaReviews){
			$select->where('id_heureka IS NULL');
		}
		$select->where('deleted = ?', '0');
		$sortType = $sortType?$sortType:'Asc';
		$select->order($sort . ' ' . $sortType);
		$select->limit($limitCount, $limitStart);
		//pr($select->__toString());
		return array($select, $bind);
    }
    
    public function getReviews($sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params = array())
    {
    	list($select, $bind) = $this->initSearch($sort, $sortType, $limitStart, $limitCount, $params);
    	return $this->db->fetchAll($select, $bind);
    }
    
    public function getReviewsCount( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params = array())
    {
    	list($select, $bind) = $this->initSearch($sort, $sortType, $limitStart, $limitCount, $params, true);
    	return $this->db->fetchOne($select, $bind); 
    }
    
    public function initSearchProducts( $sort = 'date', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params, $count = false)
    {
    	$select =  $this->db->select();
    	$bind = array();
    	if($count){
    		$c =  new Zend_Db_Expr("count('*')");
    		$select->from(array('cm' => $this->_tableNameProd), array( $c ));
    	} else {
    		$select->from(array( 'cm' => $this->_tableNameProd), array('*'));
    	}
    	$disableHeurekaReviews = $this->settings->getSettingValue('disableHeurekaReviews');
    	if($disableHeurekaReviews){
    		//$select->where('id_heureka IS NULL');
    	}
    	if($params['nodeId'] > 0)
    	{
    		$select->where('id_node = ?', $params['nodeId']);
    	}
  
    	$select->where('deleted = ?', '0');        
    	$sortType = $sortType?$sortType:'Asc';
    	$select->order($sort . ' ' . $sortType);
    	$select->limit($limitCount, $limitStart);
    	//pr($select->__toString());
    	return array($select, $bind);
    }
    
    public function getReviewsProduct($sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params = array())
    {
    	list($select, $bind) = $this->initSearchProducts($sort, $sortType, $limitStart, $limitCount, $params);
    	return $this->db->fetchAll($select, $bind);
    }
    
    public function getReviewsProductCount( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params = array())
    {
    	list($select, $bind) = $this->initSearchProducts($sort, $sortType, $limitStart, $limitCount, $params, true);
    	return $this->db->fetchOne($select, $bind);
    }

    
    
    public function saveReviews($view)
    {
    	
    	$input = $view->input;
    	for ($i = 1; $i <= $input->countRev; $i++) {
    		$this->saveReview($input,$i);
    	}
    	return true;
    }
    
    
	
	public function saveReview($input)
	{
		$d = 'date';
		$p = 'pros';
		$m = 'cons';
		$sT = 'summary';
		$dT = 'delivery';
		$de = 'transport';
		$c = 'webUsa';
		$con = 'communication';
		$sum = 'total_rating';
		$nma = 'name';
		$data = array(
				'date' => Utils::formatDateSaveDB($input->$d),
				'pros' => $input->$p,
				'cons' => $input->$m,
				'summary' => $input->$sT,
				'delivery' => $input->$dT,
				'transport' => $input->$de,
				'webUsa' => $input->$c,
				'communication' => $input->$con,
				'total_rating' => $input->$sum,
				'name' => $input->$nma,
				);
		$this->db->insert($this->_tableName,$data);
	}
	
	function getSummaryProduct($data)
	{
	
		$inc =  0;
		$result = new stdClass();

		foreach ($data as $item) {
			if(!$item['date'])
				continue;
			$total += $item['rating'];
			$inc++;
		}

		$result->total = round($total/$inc,1);
		$result->count = $inc;
		return $result;
	}
	  
	public function getDbRevByDate($date)
	{
		$s = explode('.', $date);
		$dat = $s[2].'-'.$s[1].'-'.$s[0];
		$this->db->fetchRow("select * from ".$this->_tableName." where date =?",$dat);
	}
	
	function getSummary($data)
	{
		
		$inc = $delivery = $webUsa = $total = $communication = $transport = 0;
		foreach ($data as $item) {
			
			if(!$item['date'])
				continue;
			$inc++;

			$delivery += $item['delivery'];
			$transport += $item['transport'];
			$webUsa += $item['webUsa'];
			$communication += $item['communication'];
			$total += $item['total_rating'];
		}
		
		$result = new stdClass();
		$result->delivery = round($delivery/$inc,1);
		$result->transport = round($transport/$inc,1);
		$result->webUsa = round($webUsa/$inc,1);
		$result->communication = round($communication/$inc,1);
		$result->total = round($total/$inc,1);
		$result->count = $inc; 
		return $result;
	}
	
	function showSingleRev($nodeId){
			$data = $this->db->fetchall("select * from ".$this->_tableNameProd." where id_node =?  and deleted = '0' order by date desc",$nodeId);
			$total = $this->getSummaryProduct($data);
 
			return array($total,$data);
	}
	
	public function saveProductReviews($view)
	{
		$input = $view->input;  
		for ($i = 1; $i <= $input->countRev; $i++) {
			$this->saveSingleReview($input,$i);
		}
		return true;
	}
	
	public function saveSingleReview($input,$nodeId)
	{
		$d = 'date';
		$p = 'pros';
		$m = 'cons';
		$sT = 'summary';
		$de = 'rating';
		$nn = 'name';

			$date = date("Y-m-d H:i:s");  
		
		$data = array(
				'date' =>$date,
				'pros' => $input->$p,
				'cons' => $input->$m,
				'summary' => $input->$sT,
				'rating' => $input->$de,
				'id_node' => $nodeId,
				'name' => $input->$nn
				);
		
		$this->db->insert($this->_tableNameProd,$data);
	}
	
	public function importProductRev()
	{
		$path = 'http://www.heureka.cz/direct/dotaznik/export-product-review.php?key='.$this->key;
		
		
		$filepath = $this->config->fsRoot . '/docs/produkty/' . $imageName;
		file_put_contents ( $filepath, $contents );
		$data = $this->loadXML($path ,0,1000000);
		die;    
		foreach ($data as $v){
			$v->product_name = trim(str_replace('(Doprava ZDARMA)','',$v->product_name));
			$v->product_name = trim(str_replace('(Doprava ZDARMA, záruka 2+3 roky)','',$v->product_name));
			$v->product_name = trim(str_replace('(Záruka 2+3, doprava ZDARMA)','',$v->product_name));
			$v->product_name = trim(str_replace(', DOPRAVA ZDARMA, OFICIÁLNÍ DISTRIBUCE','',$v->product_name));
			$v->product_name = trim(str_replace(', OFICIÁLNÍ DISTRIBUCE','',$v->product_name)); 
			$v->product_name = trim(str_replace('(Záruka 2+3, doprava ZDARMA, další sleva na produkt 10% viz. popis produktu!)','',$v->product_name));
			$v->product_name = trim(str_replace('(až 5 let záruka, doprava ZDARMA)','',$v->product_name));
			$v->product_name = trim(str_replace(' + spořící přepážka','',$v->product_name));
			$v->product_name = trim(str_replace("'",'',$v->product_name));
			$nodeId = $this->db->fetchOne("select id from Nodes where title = '".$v->product_name."'");
			if(!$nodeId)
			{
				$contentId = $this->db->fetchOne("select id_Product from module_eshop_variants where EAN = '".$v->ean."'");
				$node = $this->tree->getContentsNode($contentId);
				$nodeId = $node->nodeId;
			}
			elseif($nodeId){
				$temp = explode(' ', $v->product_name);
				$count = count($temp);
				$model = $temp[$count-2].' '.$temp[$count-1];
				$contentId = $this->db->fetchOne("select id_Product from module_eshop_variants where model = '".$model."'");
				$node = $this->tree->getContentsNode($contentId);
				$nodeId = $node->nodeId;
			}
	
			$isExist = $this->db->fetchOne('select id from '.$this->_tableNameProd.' where id_heureka = ?',$v->id);
			if(!$isExist && $nodeId){
				$data = array(
					'id_heureka' => $v->id,
					'date' => $v->date,
					'rating' => $v->rating,
					'summary' => $v->summary,
					'cons' => $v->cons,
					'pros' => $v->pros,
					'id_node'=>$nodeId);
			$this->db->insert($this->_tableNameProd,$data);
			}
		}
die; 
	}
	
	function show($start = 0, $end = 10){ 
   		$data = $this->db->fetchall("select * from ".$this->_tableName." where deleted='0' order by date desc LIMIT ".$start." , ".$end);
   		$total = $this->getSummary($data);
   		return array($total,$data);
   		
	}
	
	function getCountReview(){
		return $this->db->fetchOne("select count(id) from ".$this->_tableName."  where deleted='0'");	 
	}
	
	
	private function downloadUrlToFile($url, $path)
{
    $newfname = $path; 
    $file = fopen ($url, 'rb'); 
    if ($file) {
        $newf = fopen ($newfname, 'wb');
        if ($newf) {
            while(!feof($file)) {
                fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
            }
        }
    }
    if ($file) {
        fclose($file);
    }
    if ($newf) {
        fclose($newf);
    }
}


	function download_page($path){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$path);
    curl_setopt($ch, CURLOPT_FAILONERROR,1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $retValue = curl_exec($ch);          
    curl_close($ch);
    return $retValue;
}

	private function saveFile($url,$fileName)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL,$url);
		$sXML=curl_exec($ch);
		$fp = fopen($fileName, 'w');
		fwrite($fp, $sXML);
		$filepath = $this->config->htdocsRoot.'/'.$fileName; 
		return $this->loadXML($filepath);      
	}
	 
	
	function importRev()
	{
		$data = $this->saveFile('https://www.heureka.cz/direct/dotaznik/export-review.php?key='.$this->key,'data-reviews.xml');  
		foreach ($data as $value) {
			$isExist = $this->db->fetchOne('select id from '.$this->_tableName.' where id_heureka = ?',$value->id);
			if(!$isExist && $value->total_rating > 1)
			{
				$data = array(
						'id_heureka' => $value->id,
						'name' => $value->name, 
						'total_rating' => $value->total_rating,
						'transport' => $value->transport_quality,
						'delivery' => $value->delivery_time,
						'webUsa' => $value->web_usability,
						'communication' => $value->communication,
						'pros' => $value->pros,
						'date' => $value->date,
						'cons' => $value->cons,
						'summary' => $value->summary, 
						);
				$this->db->insert($this->_tableName, $data);
				//die();
			} 
		}
	}
	
	
//	function showReviews($data)
//	{
//		$reviews = null;
//		foreach ($data as $item)
//		{
//			if(!$item->unix_timestamp)
//				continue;
//			$name = '';
//			$name = $item->name?$item->name:'Ověřený uživatel Heureka'.'<br />';
//			$reviews .= 'uživatel: '.$name.'<br />';
//			$reviews .= 'datum hodnocení: '.$item->unix_timestamp.'<br />';
//			$reviews .= 'doručovací doba: '.$item->delivery_time.'<br />';
//			$reviews .= 'doprava: '.$item->transport_quality.'<br />';
//			$reviews .= 'přehlednost internetového obchodu: '.$item->web_usability.'<br />';
//			$reviews .= 'komunikace: '.$item->communication.'<br />';
//			$reviews .= 'celkem: '.$item->total_rating.'<br />';
//			$reviews .= 'komunikace: '.$item->communication.'<br />';
//			$reviews .= $item->pros?'pro: <p>'.$item->pros.'</p>':'';
//			$reviews .= $item->cons?'proti: <p>'.$item->cons.'</p>':'';
//			$reviews .= $item->summary?'souhrn: <p>'.$item->summary.'</p>':'';	
//			$reviews .= $item->reaction?'Odpověď: <p>'.$item->reaction.'</p>':'';					
//		}
//		return $reviews;
//	}

		
	function loadXML($xmlPath, $min = 0, $max = 40000000,$urlProduct = false){
		$reader = new XMLReader();
		$isopen = $reader->open($xmlPath); 
		$products = array();
		$data = new stdClass();  
		$i = 0;
		$productsCount = 0; 			 
		while ($reader->read()){
		   if ($reader->nodeType == XMLREADER::ELEMENT) {  
		   		$i++;
		   		switch ($reader->localName) { 
		   			case 'review': 
		   				if($urlProduct)
		   				{
		   			
		   					if($urlProduct == $data->url)
		   					{
		   						$reviews[] = $data;
		   					}
		   				}
		   				elseif($data->id){
			   				$reviews[] = $data;
		   				}
			   			$data = new stdClass();   
		   				break;  
		   			case 'name':
		   				$data->name = $reader->readString();
		   				break;
		   			case 'rating_id':
		   					$data->id = $reader->readString();
		   			break;
		   			case 'unix_timestamp':
		   				$data->date = date("Y-m-d G:i:s",$reader->readString());
		   				break;  
		   			case 'total_rating':
		   				$data->total_rating = $reader->readString();
		   				break;  
		   			case 'delivery_time':
		   				$data->delivery_time = $reader->readString();
		   			case 'transport_quality':
		   				$data->transport_quality = $reader->readString();
		   			case 'web_usability':
		   				$data->web_usability = $reader->readString();
		   				break;
		   			case 'communication':
		   				$data->communication= $reader->readString();
		   				break;		   				
		   			case 'summary':
		   				$data->summary = $reader->readString();
		   				break;
		   			case 'cons':
		   				$data->cons = $reader->readString();
		   				break;
		   			case 'pros':
		   				$data->pros = $reader->readString();
		   				break;
		   			case 'url':
		   				$data->url = $reader->readString();
		   				break;
		   			case 'product_name':
		   				$data->product_name = $reader->readString();
		   				break;
		   			case 'ean':
		   				$data->ean = $reader->readString();
		   			break;
		   			case 'rating':
		   				$data->rating = $reader->readString();
		   				break;
		   			case 'reaction':
		   				$data->reaction = $reader->readString();
		   				break;	
		   				 
		   		}
		   		
		   		if($productsCount > $max){
		   			break;
		   		} 
		   		
		   }
		} 
		return ($reviews); 
	}	
}