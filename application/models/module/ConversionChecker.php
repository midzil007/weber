<?
class module_ConversionChecker{	
          
    const seznanConversion = '100012566';
    const adwordsConversion = 'AW-995776401/uyk4CIftlwQQka_p2gM';
    const webTitle = "svycarskekavovary.cz";
    const adwordsLabel = 'uyk4CIftlwQQka_p2gM';
    public function __construct()
    {
        $this->session = Zend_Registry::getInstance()->session;	
    }  

    public function deleteConversions(){
        unset($this->session->conversionCodes);
    }   

    public function saveReferal()
    {
        if(!isset($this->session->referal))
        {
            $this->session->referal = $_SERVER['HTTP_REFERER'];
        }
    }
 
    public function getReferal()
    {
        return $this->session->referal;
    }

    public function addProduct($object){
        $this->session->conversionAddProduct 
             =  "ga('require', 'ec');      
                  ga('set', 'currencyCode', 'CZK');
                  ga('ec:addProduct', {
                  'id': '$object->nodeId',
                  'name': '$object->title',  
                  'category': '$object->category',  
                  'brand': '$object->brand',                   
                  'variant': '',  
                  'price': '$object->price'});     
                  ga('ec:setAction', 'detail');";
    }

    public function renderAddProduct()
    { 
        $text = $this->session->conversionAddProduct;
        $this->session->conversionAddProduct = ""; 
        unset($this->session->conversionAddProduct);
        return $text;
    }

    private function renderEcommerce($ecommerceCode)
    {  
        
			$codeRequire  = <<<EOD
            ga('require', 'ecommerce');
EOD;
        foreach($ecommerceCode->items as $item){
        $trackItems .=<<<EOD
		ga('ecommerce:addItem', {
            'id': '$ecommerceCode->orderId',           // order ID - required
            'name': '{$item->name}',        // product name
            'sku': '{$item->sku}',           // SKU/code - required
            'category': '{$item->category}',   // category or variation
            'price': '$item->price',          // unit price - required
            'quantity': '$item->quantity'               // quantity - required
		  }); 
EOD;
        }
        $mark = self::webTitle;
        $code = <<<EOD
		ga('ecommerce:addTransaction', {
            'id': '$ecommerceCode->orderId',           // order ID - required  
            'affiliation': '$mark',  // affiliation or store name
            'revenue': '{$ecommerceCode->revenue}',          // total - required
            'shipping' : '{$ecommerceCode->trans}',           // tax
            'tax' : '$ecommerceCode->tax',                
		  });
EOD;
        $submitTemp =<<<EOD
		ga('ecommerce:send');
EOD;
		return $codeRequire."\n".$code."\n".$trackItems."\n".$submit;	
    } 

    public function setConversions($ecommerceCode ){   
        $conversionCodes = new stdClass();        
        $conversionCodes->adwords = $this->renderAdwords($ecommerceCode);
        $conversionCodes->sklik = $this->renderSklik($ecommerceCode);  
        $conversionCodes->ecommerce = $this->renderEcommerce($ecommerceCode);  
        $conversionCodes->measuringTransaction = $this->measuringTransaction($ecommerceCode);
        $this->session->conversionCodes = $conversionCodes;  
    }

    private function measuringTransaction($ecommerceCode)
    {  
        $text = "ga('require', 'ec');";        
        foreach($ecommerceCode->items as $item){
            $text .= "ga('ec:addProduct', {               // Provide product details in an productFieldObject.
                'id': '$item->sku',                   // Product ID (string).
                'name': '$item->name', // Product name (string).
                'category': '$item->categoryTitle',            // Product category (string).
                'brand': '$item->brand',                // Product brand (string).
                'price': '$item->price',                 // Product price (number).
                'quantity': $item->quantity                     // Product quantity (number).
            });";
        }
            $text .= "ga('ec:setAction', 'purchase', {          // Transaction details are provided in an actionFieldObject.
                 'id': '$ecommerceCode->orderId',                         // (Required) Transaction id (string).
                'revenue': '$ecommerceCode->revenue',                     // Revenue (number).
                'tax': '$ecommerceCode->tax',                          // Tax (number).   
                'shipping': '$ecommerceCode->trans',                     // Shipping (number).
            });";  
        return $text;   
    }

    public function showConversions($idOrder)   
    {  
        return $this->session->conversionCodes;
    }  
    
    private function renderAdwords($ecommerceCode)
    {
        $text ="<!-- Event snippet for Objednávka conversion page --> <script> 
                gtag('event', 'conversion', 
                { 'send_to': '".self::adwordsConversion."', 'value': ".$ecommerceCode->revenue.", 'currency': 'CZK', 'transaction_id': '".$ecommerceCode->orderId."' }); 
                </script>";
        return $text;  
    }

    private function renderSklik($ecommerceCode)
    {       
        $text  = '<script type="text/javascript">
		var seznam_cId = '.self::seznanConversion.';
		var seznam_value = '.$ecommerceCode->revenue.';   
		</script>
        <script type="text/javascript" src="https://www.seznam.cz/rs/static/rc.js" async></script>';
        return $text; 
    }
}?>