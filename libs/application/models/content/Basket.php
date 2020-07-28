<?
/**
 * Třída reprezentuje Článek
 * @see model_Content
 * @package content
 */
 
class content_Basket extends content_HtmlFile {
    
    public function __construct($config = array())
    {
    	parent::__construct();
    	$this->userName = 'Nákupní košík';   	 	  	
    	$this->properties = array();
    	$this->searchableCols = array();
    }
    
    
    function initBasket($view){
    	$view->orderSum = $view->mEshop->basket->getSum($view);
    	$view->basketItems = $view->mEshop->basket->getItems(); 
    	$view->basketItemsCount = $view->mEshop->basket->getItemsCount();
    	$view->deliveryOptions = $view->mEshop->getDeliveryOptionsSelect($view);    	     	
    	$view->input->delivery = $view->input->delivery?$view->input->delivery:0;  
    	
    	$view->paymentOptions = $view->mEshop->getPaymentOptionsSelect($view->input->delivery, $view); 
    	 
    	$view->deliveryPrice = $view->mEshop->calcDeliveryPrice($this, $view->input->delivery, $view->orderSum);
    	
    }
    
    function show($view, $node){    	    
    	$view->isWide = true; 
    	$view->urlLsProduct = $view->session->UrlLstProdukt;
    	$action = $view->inputGet->action?$view->inputGet->action:$view->input->action;
		
    	if(!$view->input){
    		$view->shopper = $view->mEshop->basket->getShopper();
    		// pr($view->shopper);  
    		// $view->input = Helpers::setValuesToObject($view->input, $view->shopper); 
    	} 
    	
    	 
    	if($view->input->delivery){
    		$view->session->delivery = $view->input->delivery;
    	} else {
    		$view->input->delivery = $view->session->delivery;
    	}

    	
    	
   		if($view->input->payment){
    		$view->session->payment = $view->input->payment;
    	} else {
    		$view->input->payment = $view->session->payment;
    	}
    	
    	if(isset($view->input->login)){
    		list($state, $messages) = $view->mCustomers->loginUser($view->input->log_email, $view->input->log_pass);
			Helpers::addMessage($state, $messages, $view); 
			      
    	}
    	
    
    	if($view->webUser->id && $view->inputGet->step == 1){  
    		$view->input = Helpers::setValuesToObject($view->input, $view->webUser);   
    		$view->webUser->agree = 1; 
    		$view->mEshop->basket->setShopper($view, $view->webUser);  
    		$view->shopper = $view->mEshop->basket->getShopper();  
    	}   
    	 
		 
		if (!$view->inputGet->done){
		 
    	if($view->input->step || $view->inputGet->step){ 
    		if(is_array($view->input->step)){
    			$s = array_keys($view->input->step);
    			$view->step = $s[0];    
    		} else {
    			$view->step = $view->input->step?$view->input->step:$view->inputGet->step;
    		}  
    	}  
		
		
		if(!$view->input->kod)
			$view->input->kod = $view->inputGet->kod;

		if($view->input->kod)
		{
			$view->voucher = $view->mVoucher->getVoucherByCode($view->input->kod); 
			
			if($view->voucher['znacky'])
			{
				$znacky = explode(',',$view->voucher['znacky']);
				foreach($znacky as $znacka)
					$znackyCiselnik[$znacka] = $view->mVoucher->getZnackaByNodeId($znacka);
			}
			$invalid = true;
			$items = $view->mEshop->basket->getItems();
			foreach ($items as $id => $data)
			{ 
				$child = $data['item'];  
				if(!$child){ continue; } 
				$c = $child->getPublishedContent(); 
				if($view->voucher['znacky'])
				{
					if(isset( $znackyCiselnik[$c->getPropertyValue('znacka')] ))
					{
						$invalid = false;
						break;
					}
				}
				elseif($view->voucher['productCode'])
				{
					//print_r($c->getPropertyValue('kod'));
					//print_r($voucher['productCode']);
					if($c->getPropertyValue('kod') == $view->voucher['productCode'])
					{
						$invalid = false;
						break;
					}
				}
			}
			
			if($invalid)
				$view->voucher['status']= -1;
			
			if(($view->voucher) AND ($view->voucher['status']==1)){
				$view->mEshop->basket->addVoucher($view->voucher); // do modulu ulozim jen platny
			}
			
			if( ($view->step == 2) AND (!$view->voucher OR $view->voucher['status']!=1) )
			{
				helper_FrontEnd::redirect('/nakupni-kosik?step=1&kod=' .$view->input->kod, 302, true);
    			die();  
			}
		}
	
		if($view->inputGet->directBuy){
			$data = $view->mEshop->addItemById($view->inputGet->directBuy, 1); 
		} 
		
    	switch ($action){ 
    		case 'buy':
    			$productToBuy = $view->input->pid;
    			$count = $view->input->count;
    			if($productToBuy){
  	 				$data = $view->mEshop->addItemById($productToBuy, $count); 
    				 
	    			if($view->inputGet->ajax){	  	    				
	    				echo $view->mEshop->basket->drawInfoLine($view); 
		    			die();   
	    			}
    			}
    			break;   
    		case 'empty':    			
    			$view->mEshop->basket->emptyBasket();     			
    			break;  
    		case 'refreshPayment':  			
    			if($view->inputGet->ajax){
    				if($view->input->dop){
    					$view->mEshop->basket->setDelivery($view->input->dop);
    				}
    				if($view->input->platba){
    					$view->mEshop->basket->setPayment($view->input->platba);
    				}
    				echo $view->render('templates/Eshop/priceBlock.phtml'); 
    				echo $view->render('templates/Eshop/deliveryBlock.phtml'); 
    				
    				die();   
    			}			 
    			break;
    		case 'refreshBasket':    			
    			if($view->inputGet->ajax){

						//print_r($view->mEshop->basket->voucher);
						/*if($view->input->kod){
							
						}*/			    				 			
    				$items = $view->mEshop->basket->getItems(); 
    				$view->mEshop->basket->emptyBasket();
    				 
    				if(count($view->input->count)){	
	    				foreach ($view->input->count as $pid => $count){
	    					// e($count); 
	    					 
	    					if(!is_numeric($count)){
	    						$count = 1; 
	    					}
	    					if($count > 0){
		    					$view->mEshop->addItemById($pid, $count);
	    					} 
	    				}
    				}  
    			if($view->input->dop){
    					$view->mEshop->basket->setDelivery($view->input->dop);
    				}
    				if($view->input->platba){
    					$view->mEshop->basket->setPayment($view->input->platba);
    				}
    							//$delivery = $view->mEshop->basket->getDelivery();
			//$payment = $view->mEshop->basket->getPayment();
    				
    				// $view->mEshop->basket->reinit();     				 
    				echo $view->render('templates/Eshop/BasketStep1Inner.phtml');
    				$this->initBasket($view);  
  
    				die();  
    			}		
    				 
    			break;
    	}
    	   	
    
    	$disableRedir= false;
    	
    	if($action != 'buy' && $action != 'calculate'){
	    	$this->initBasket($view);
    	}
    	
    	
    	if($view->basketItemsCount || $view->inputGet->done){ 
    		$view->disableH1 = true;
    		$view->disableLeftColl = true; 
    	}  
    	
    	
    	if($view->step == 2 && !$view->inputGet->step){  
			$redir = "2";
		}   
		
		if($view->step == 2 && $view->inputGet->step!=2){ 
		
			//$delivery = $view->mEshop->basket->getDelivery();
			//$payment = $view->mEshop->basket->getPayment();
			if($view->input->dop && $view->input->platba){
				$view->mEshop->basket->setDelivery($view->input->dop);
    			$view->mEshop->basket->setPayment($view->input->platba); 
    			$view->mEshop->basket->setNote($view->input->note);  
				$redir= "2";   
			}
//			} else {    
//				$this->step = 0; 
//				$redir= "0";
//			}  
		$redir= "2";
		}     
		
		if($view->inputGet->step == 2 && $view->input->username){
				$webUsers = new module_Customers();
				list($state, $messages) = $webUsers->loginUser($this,$view->input->username, $view->input->pass);
				Helpers::addMessage($state, $messages, $this);
		}
		
		
			if($view->step == 3 && $view->inputGet->step!=3){
				if(!$view->webUserLogged){
					$webUsers = new module_Customers();
					$view->inBasket = true;
					// e($view->input); die();  
					list($state, $messages) = $webUsers->registerOrCheck($view, $view->input);
					//$view->mCustomers->registerUser($view, $view->input);
					$view->input->login = '';
				}
				
			if($messages){     
				$view->step = 2;    
					Helpers::addMessage($state, $messages, $view);
					if($state){  
						
					} else {  
						$view->step = 2;
						$disableRedir = true;
						$redir= "2&m=" . rawurlencode(base64_encode($messages));
					}
				
			} else { 
				$view->mEshop->basket->setShopper($view, $view->input);  
			} 

			
			
			if($view->step == '3'){
				if($view->input->login2 && $view->input->password){
					$view->mCustomers->inBasket = true;
					$view->input->login = $view->input->login2;  
					//list($state, $messages) = $view->mCustomers->registerUser($view, $view->input);
					$view->input->login = '';
					
					Helpers::addMessage($state, $messages, $view);
					if($state){  
						
					} else {  
						$view->step = 2;
						
						$redir= "2&m=" . base64_encode($messages);
					}   
				}
				 
				 
			}  
			if(!$redir){
				$redir= "3"; 
			} 
		} 
		
		
		
    	if($view->step == 4){
			$delivery = $view->mEshop->basket->getDelivery();
			$payment = $view->mEshop->basket->getPayment();
			
			if($view->mEshop->basket->voucher){
				$code = $view->mEshop->basket->voucher['code'];
				$checkVoucher = $view->mVoucher->getVoucherByCode($code);
				if($checkVoucher['status'] != 1){
					$redir = $redir= "0&vm=" . urlencode('Kupon byl použit dříve, než jste dokončili objednávku.');
					$view->mEshop->basket->emptyVoucher();
					$stop = true;
				}
			}
			
			if($payment == 17 && !$view->inputGet->success){
				$redir = 6;  
			} elseif(!$stop || $view->inputGet->successPay) {        
				
				$sum = $view->mEshop->basket->getSum($view);
				
				$fid = $view->mEshop->basket->makeOrder($view);  	
				$payment = $view->mEshop->basket->getPayment();
					
    			$deliverySum = $view->mEshop->basket->getDeliveryPrice();
    			$delivery = $view->mEshop->basket->getDelivery();   

    			if($view->inputGet->successPay){
    				$add = '&paied=1';
    			}
    			
				if(!$fid){ 
					helper_FrontEnd::redirect($view->bp . '?done=1&z=' . base64_encode(123456789).'&price='. base64_encode($sum) . '&p=' . $payment . '' . '&d=' . $delivery. '&news=1'.$add, 302);  
				} else{ 
					helper_FrontEnd::redirect($view->bp . '?done=1&z=' . base64_encode($fid).'&price='. base64_encode($sum). '&p=' . $payment . '' . '&d=' . $delivery. '&news=1'.$add, 302);  
				}
			}   
			$view->session->step = 0;   
		}

	
		
		if($view->step >= $view->session->step){
			$view->session->step = $view->step; 
		}
		
		$view->maxStep = $view->session->step;
		
		switch ($view->session->step) {
			case 2:
				$view->show1 = 'show1';
				$view->show2 = 'show2';
			break;
			case 3:
				$view->show1 = 'show1';
				$view->show2 = 'show2';
				$view->show3 = 'show3';
			break;
			default:
				$view->first = 'show1';
			break;
		} 
		
		if($redir && !$disableRedir){  
			helper_FrontEnd::redirect('/nakupni-kosik?step=' . $redir, 302, true);
		}
	}  
    	
    	$view->step = $view->step?$view->step:1; 
    	    	
    	return $view->render(Zend_Registry::getInstance()->config->view->templatesDir . 'Eshop/Basket.phtml');
    }
}
?>
