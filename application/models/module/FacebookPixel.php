<?php

// statusy


class module_FacebookPixel
{
	  public function __construct() {
	  	$this->tree = Zend_Registry::getInstance ()->tree; 
	   }
	  public function getFacebookTrack($node,$mBasket = false,$basketSum = false,$view) 
	  {
	  	//nakupni kosik  
	  	if($node->nodeId == 4410){
	  		if($_GET['step'] == 2){
	  			$text = "fbq('track', 'AddPaymentInfo');";
	  		}
			elseif($_GET['done'])
			{
				// sumFaceBook		
				 $textId = implode(',',  $_SESSION['idsFaceBook']);
				$text = "fbq('track', 'Purchase', {
  content_ids: ['".$textId."'],  
   value: '".$_SESSION['sumFaceBook']."',     
   currency: 'CZK'  
 });"; 
			}
			elseif($_GET['step'] == 3){  
				$text = "fbq('track', 'InitiateCheckout');";

			}
	  	}   
		// předkošik
		elseif($node->nodeId == 6553){
			$text = "fbq('track', 'AddToCart');";
		}
		elseif($node->nodeId == 135){
			$text = "fbq('track', 'Search');"; 
		}
		elseif($node->nodeId == 4780 && $_GET['state'] == '1'){
			$text = "fbq('track', 'CompleteRegistration');"; 
		}
		else
		{
			if(get_class($view->content) == 'codntent_OverviewProducts')
			{
				$content_type = "content_type: 'product_group'"; 
				$text = "fbq('track', 'ViewContent', { 
  content_name: '".$node->title."',  
  content_category: '".str_replace(' » ', '|', strip_tags(helper_FrontEnd::generateBreadCrumbs($view, array('/e-shop'))))."', 
  ".$content_type." 
 });";   
			} 
			elseif(get_class($view->content) == 'content_Product') 
			{ 
				$content_type = "content_type: 'product'"; 
				$text = "fbq('track', 'ViewContent', {
  content_name: '".$node->title."',       
  content_category: '".str_replace(' » ', '|', strip_tags(helper_FrontEnd::generateBreadCrumbs($view, array('/e-shop'))))."',
  content_ids: ['jura_cz_".$node->nodeId."'],  
  
  ".$content_type." 
 });";
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
 
 