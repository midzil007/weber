<?
/// data = metoda makeOrder co jsou do Orders, $item jdou do Order_items

class module_Cofidis{	
	private $data;
	private $items; 
	private $orderId;
		
 
		
	public function __construct($data,$items, $orderId) {
		$this->data = $data; 
		$this->items = $items;
		$this->orderId = $orderId;
	}
	
	
	private function setDateUntil()
	{
		$dtz = new DateTimeZone("Europe/Prague"); //your timezone
		$now = new DateTime(date("Y-m-d H:i:s"), $dtz);
		$now->modify('+10 hour');
		return $now->format("Ymd").'T'.$now->format("His");
	}
	
	private function getProductProperites()
	{
		$item = new stdClass();
		$ids = $titles = array();
		foreach ($this->items as $value) {
			$ids[] = $value['itemNodeId'];
			$titles[] = $value['title'];
		}
		$item->ids = implode(',', $ids);
		$item->titles = implode(',', $titles);
		return $item;
	}
	
	public function sendRequest()
	{
		$request_data = $this->setRequestData();
        $iplatba_redirect_url = $this->backgroundcall_open_demand($request_data); // odesilani zadosti
		return urldecode($iplatba_redirect_url); // presmerovani na stranky iplatby

	}
	

	
	public function setRequestData()
	{
		$productProperties = $this->getProductProperites($this->items);
		
		// nasetovat
		$additional_data = "OO"; // OO“=Osobní odběr, „CP“=Balík na poštu (ne balik do ruky), „MS“= ostatní způsoby dopravy, „IT“ = In Time. Pozor, rozlišují se malá a velká písmena. Podle tohoto parametru se vytváří automaticky importy pro jednotlivé kurýrní služby, proto je nutné, abychom jej obdrželi.
		

		
		
		$valid_until = $this->setDateUntil();
       $seed = uniqid();
       $cofisun_pwd = "F3C0K54C";
       $operation = "START_LOAN_DEMAND";
       $transaction_id = $this->orderId;
       $additional_data = "MS";
       $amount = floor($this->data['suma']).'00';
       $deposit  = "0";
       $num_installments = "0";
       $currency = "CZK";
       $session_id = session_id();
       $product_id = $productProperties->ids;
       $product_description = $productProperties->titles;
       $merchant_id = "20530";
       $login = "NESS";
       $last_name = $this->data['surname'];
       $first_name = $this->data['firstname'];
       $mobilephone =  $this->data['phone'];
       $fixphone = "";
       $email = $this->data['email'];
       $pri_city = "";
       $pri_street = "";
       $pri_number = "";
       $pri_orientation_num = "";
       $pri_zip = "";
       $con_city = "";
       $con_street = "";
       $con_number = "";
       $con_orientation_num = "";
       $con_zip = "";
       $url = Utils::getWebUrl().'?cofidis=1';
		
		$concatString = $valid_until."|".$seed."|".$cofisun_pwd."|".$operation."|".$transaction_id."|".$additional_data."|".
        $amount."|".$deposit."|".$num_installments."|".$currency."|".$session_id."|".$product_id."|".$product_description."|".$merchant_id."|".$login."|".$last_name."|".$first_name."|".
        $mobilephone."|".$fixphone."|".$email."|".$pri_city."|".$pri_street."|".$pri_number."|".$pri_orientation_num."|".$pri_zip."|".$con_city."|".$con_street.
        "|".$con_number."|".$con_orientation_num."|".$con_zip."|".$url;
        
        
        $encod = mb_detect_encoding($concatString);
        $utfString = mb_convert_encoding($concatString,"UTF-16LE",$encod);
        $hashTag = sha1($utfString,true);
        $base64Tag = base64_encode($hashTag);
        $forTriple = bin2hex(mb_convert_encoding($base64Tag,"UTF-16LE"));

        // inbound pwd
        $password = "lskoaodlvbob8a6b5a54d";
        $signature = $this->encrypt($forTriple,$password);
		 $request_data = array(
		  					'valid_until' => $valid_until,
		  					'seed' => $seed,
                            'cofisun_pwd' => $cofisun_pwd,
                            'operation' => $operation,
                            'transaction_id' => $transaction_id,
                            'additional_data' => $additional_data,
                            'amount' => $amount,
                            'deposit' => $deposit,//$amount/10,
							'num_installments' => $num_installments,
							'currency' => $currency,//$currency,
							'session_id' => $session_id,
							'product_id' => $product_id,
							'product_description' => $product_description,
							'merchant_id' => $merchant_id,
                            'login' => $login,
                            'last_name' => $last_name,
                            'first_name' => $first_name,
                            'mobilephone' => $mobilephone,
                            'fixphone' => $fixphone,
                            'email' => $email,
                            'pri_city' => $pri_city,
                            'pri_street' => $pri_street,
                            'pri_number' => $pri_number,
                            'pri_orientation_num' => $pri_orientation_num,
                            'pri_zip' => $pri_zip,
                            'con_city' => $con_city,
                            'con_street' => $con_street,
                            'con_number' => $con_number,
                            'con_orientation_num' => $con_orientation_num,
                            'con_zip' => $con_zip,
                            'url' => $url,
							'signature' => $signature);
							// pr($request_data);
							// die;
			return $request_data;
	}
	
	// pomocne funkce
      protected function http_parse_headers($header){
        $retVal = array();
        $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));       
 
        function stuffy_callbackCofidis($things) {           
            return strtoupper($things[0]);
        }
 
        foreach( $fields as $field ) {
            if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {   
 
                                               $match[1] = preg_replace_callback('/(?<=^|[\x09\x20\x2D])./', 'stuffy_callbackCofidis', strtolower(trim($match[1])));
 
                if( isset($retVal[$match[1]])){
                    $retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
                }
                else{
                    $retVal[$match[1]] = trim($match[2]);
                }
            }
        }
        return $retVal;
    }
  

    
       function encrypt($data, $secret) {


        $key = mb_convert_encoding($secret,'utf-16le');
        $secret = md5(utf8_encode($key), true);
        $secret .= substr($secret,0,8);


        $str = mb_convert_encoding($data,'utf-16le');
        $block = mcrypt_get_block_size('tripledes', 'ecb');
        $pad = $block - (mb_strlen($str) % $block);
        $str .= str_repeat(chr($pad), $pad);


        $td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
        $mcrypt_iv = str_repeat(chr(0), mcrypt_enc_get_iv_size($td));
        $mcrypt_iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_3DES, MCRYPT_MODE_ecb), MCRYPT_RAND);

        //Encrypt data
        $encData = mcrypt_encrypt('tripledes', $secret,  $str, 'ecb', $mcrypt_iv); //, $iv);

        mcrypt_generic_init ($td, $secret, $mcrypt_iv);
        $encrypted_data = mcrypt_generic ($td,  $str);
        mcrypt_generic_deinit ($td);
        mcrypt_module_close ($td);
        $result =  mb_convert_encoding(bin2hex(base64_encode($encrypted_data)),'ASCII')."test<br/>";
        $result2 = bin2hex(base64_encode($encrypted_data));
        $str_temp = "";
        for ($i=0; $i<mb_strlen($result2,'ASCII'); $i=$i+2) {
            $str_temp = $str_temp.mb_substr($result2,$i,2)."00";

        }

        return $str_temp;
    }


     // funkce na odesilani zadosti
     //@param pole dat, ktera se maji odeslat vc podpisu.
      function backgroundcall_open_demand($request_data) {


        
        $header[] = "Host: gw1.iplatba.cz";
        
        
        $ch = curl_init();
        
        // pokud pouzivame proxy k pripojeni. 
        //$proxy = "145.226.99.11:8080";
        //curl_setopt($ch, CURLOPT_PROXY, $proxy);
        
        
        curl_setopt( $ch, CURLOPT_URL, 'https://gw1.iplatba.cz/Service/StartLoanDemand'); // adresa na zadost
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
        curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'POST' );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt( $ch, CURLOPT_HEADER, true);
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $request_data);
        curl_setopt( $ch, CURLINFO_HEADER_OUT, true);
        $result = curl_exec( $ch );
        curl_close($ch);
       

        $parsed_header = $this->http_parse_headers($result);
      
        return $parsed_header['Url'];

    }
    
	
	
} 
?>