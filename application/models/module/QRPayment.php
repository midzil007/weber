<?php
class module_QRPayment
{     
    const accountPrefix = '';
    const accountNumber = '2600340251';
    const bankCode = '2010';
    const currency = 'CZK';  
    const size = '200';   
    const url = 'http://api.paylibo.com/paylibo/generator/czech/image';
	function __construct(){
		$this->db =  Zend_Registry::getInstance()->db;
		$this->tree =  Zend_Registry::getInstance()->tree;  
	} 
    
//http://api.paylibo.com/paylibo/generator/czech/image?accountNumber=222885&bankCode=5500&amount=250.00&currency=CZK&vs=333&message=FOND%20HUMANITY%20CCK

    public function getQRCode($idOrder,$oid,$sum)
    {   
        $varSym = file_get_contents('http://admin.specshop.cz/?qr=1&id='.$idOrder);      
        $urlParams = 'accountNumber='.self::accountNumber;
        $urlParams .= '&bankCode='.self::bankCode;    
        $urlParams .= '&currency='.self::currency;
        $urlParams .= '&size='.self::size;  
        $urlParams .= '&amount='.$sum;
        $urlParams .= '&vs='.$varSym;    
        $urlParams .= '&ss='.$oid;                 
        $url =  self::url.'?'.$urlParams;     
        $file = 'qr/'.$oid.'.png';                             
        file_put_contents($file, file_get_contents($url)); 
        return '/'.$file;            
    }    

}