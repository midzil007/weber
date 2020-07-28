<?php
header('Content-Type: text/html; charset=utf-8'); 

class module_ImportHeurekaReviews
{  
	
	private $key = 'b943273ce13e4bd41d1b12a490f95bdd';
	 
	function __construct($view){ 
		$this->db =  Zend_Registry::getInstance()->db;
		$this->config =  Zend_Registry::getInstance()->config; 
		$this->view = $view; 
		$this->tree =  Zend_Registry::getInstance()->tree;  
		$this->_tableProductKategorie = 'eshop_kategorie';
		$this->_tableNameCounterCron = 'module_eshop_couterCron';
		
	}
	
	function getSummaryProduct($data)
	{
	
		$inc =  0;
		$result = new stdClass();
		foreach ($data as $item) {
			if(!$item->unix_timestamp)
				continue;
			$total += $item->rating;
			$inc++;
		}

		$result->total = round($total/$inc,1);
		$result->count = $inc;
		return $result;
	}
	  
	
	function getSummary($data)
	{
		
		$inc = $delivery = $webUsa = $total = $communication = $transport = 0;
		
		foreach ($data as $item) {
			if(!$item->unix_timestamp)
				continue;
			$inc++;
			$delivery += $item->delivery_time;
			$transport += $item->transport_quality;
			$webUsa += $item->web_usability;
			$communication += $item->communication;
			$total += $item->total_rating;
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
	
	function showSingleRev($url){
			$path = 'http://www.heureka.cz/direct/dotaznik/export-product-review.php?key='.$this->key;
			$data = $this->loadXML($path ,0,10000, $url);	
			$total = $this->getSummaryProduct($data);
			return array($total,$data);
	}
	
	function show($start = 1, $end = 10){ 

   		//$file = $this->config->dataRoot . '/zbozi.xml';
   		$path = 'http://www.heureka.cz/direct/dotaznik/export-review.php?key='.$this->key;
   		$data = $this->loadXML($path);
   		$total = $this->getSummary($data);
   		return array($total,$data);
   		
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
		   				else{
			   				$reviews[] = $data;
		   				}
			   			$data = new stdClass();   
		   				break;  
		   			case 'name':
		   				$data->name = $reader->readString();
		   				break; 
		   			case 'unix_timestamp':
		   				$data->unix_timestamp = date("m.d.y",$reader->readString());
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