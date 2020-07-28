<?php

// statusy


class module_GoogleDimension
{
	  public function __construct() {
	  	$this->tree = Zend_Registry::getInstance ()->tree; 
	   }
	  
	  
	  public function getIP()
	  {
	  	if($_SESSION['IP-klient'])
	  	{
	  		$ip = $_SESSION['IP-klient']; 
	  	}
		else{
			 if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
 				$ip=$_SERVER['HTTP_CLIENT_IP'];}
 				elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
 				$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];} else {
 				$ip=$_SERVER['REMOTE_ADDR'];}
			$_SESSION['IP-klient'] = $ip;
		}
		   
		return "ga('send', 'pageview', {
  				'dimension10':  '".$ip."'});";  
	  }
	    
	  public function getRemarketingCode($node,$mBasket = false,$basketSum = false,$view)
	  {
	  	$text = '<script type="text/javascript">'; 
	  	switch ($node->nodeId) { 
			  case '1':  
				  $text .= 'var google_tag_params = {
						ecomm_prodid: "'.$node->nodeId.'",
						ecomm_pagetype: "home",
						ecomm_totalvalue: false
};';
				  break;
				   case '135':    
				  $text .= 'var google_tag_params = {
						ecomm_prodid: "'.$node->nodeId.'",
						ecomm_pagetype: "searchresults",
						ecomm_totalvalue: false}
};';
				  break;
		  }
		if(get_class($view->content) == 'content_Basket')
			{  
				foreach ($mBasket->getItems() as $key => $data) {
					$ids[] = "'".$data['item']->nodeId."'";   
				}  	 
				$textId = implode(',',  $ids);
				
			  
				
				if($_GET['done'] == 1)
				{ 
					$basketSum = $_SESSION['googleDimSum'];
					$textId = $_SESSION['googleDimItems'];   
				}  
				  $text .= 'var google_tag_params = {  
						ecomm_prodid: ['.$textId.'],   
						';    
					$text .= $_GET['done'] == 1 ? 'ecomm_pagetype: "cart",' : 'ecomm_pagetype: "purchase",  
						' 
					;    
					$text .= 'ecomm_totalvalue: '.$basketSum.'.00   
};';   
			}
		elseif(get_class($view->content) == 'content_OverviewProducts')
			{
				$text .= 'var google_tag_params = {
						
						ecomm_pagetype: "category",
						ecomm_totalvalue: false}';///ecomm_prodid: "'.$node->nodeId.'",  
			} 
			elseif(get_class($view->content) == 'content_Product') 
			{
				$content = $node->getTheRightContent();  
				$varianta = $view->mVarianta->getVariantsByIdProduct($content->id,true);
			
				$text .= 'var google_tag_params = {
						ecomm_prodid: "'.$node->nodeId.'",
						ecomm_pagetype: "product",
						ecomm_totalvalue: '.$varianta['price'].'}';    
			} 
		$text .= '</script>';
		return $text; 
	  }
	  
	  public function getGetGooleDimension($node,$mBasket = false,$basketSum = false,$view) 
	  {
	  	//nakupni kosik  
	  	if($node->nodeId == 4410){
	  		if($_GET['step'] == 2){
	  			$text = "ga('set','dimension2', 'AddPaymentInfo');";
	  		}
			elseif($_GET['done'])
			{ 
				// sumFaceBook		
				 $textId = implode(',',  $_SESSION['idsFaceBook']);
				  	$text = "ga('set','dimension1','".$textId."');\n"; 
				$text .= "ga('set','dimension2', 'Purchase');\n"; 
			
				$text .= "ga('set','dimension3', '".$_SESSION['sumFaceBook']."');"; 
			}
			elseif($_GET['step'] == 3){  
				//$text = "fbq('track', 'InitiateCheckout');"; 
 
			} 
	  	} 
		// předkošik
		elseif($node->nodeId == 6553){
			$text = "ga('set','dimension2','AddToCart');";
		}
		elseif($node->nodeId == 135){
			$text = "ga('set','dimension2', 'Search');"; 
		}
		elseif($node->nodeId == 4780 && $_GET['state'] == '1'){
			$text = "ga('set','dimension2','CompleteRegistration');"; 
		}
		else
		{
			if(get_class($view->content) == 'codntent_OverviewProducts')
			{
					$text .= "ga('set','dimension2', 'product_group')"; 
			} 
			if(get_class($view->content) == 'content_Product') 
			{
				$text = "ga('set','dimension1','".$node->nodeId."');\n";
				$text .= "ga('set','dimension2', 'product');";  		
			}        
			//$this->getCategory($node->parentId);  
			
		}
		return $text; 
	  }
  
	private function getCategory($parentId){
		if($parentId != 3801 || $parentId != 0){
			$node = $this->tree->getNodeById($parentId);    
			$this->categories[] = $node->title;
			$this->getCategory($node->parentId); 	
		}
	}
	     
}
 
 