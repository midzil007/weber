<?
/// data = metoda makeOrder co jsou do Orders, $item jdou do Order_items

class module_Calculator{			
 
 	private $_response;
	private $_products;
	private $_num;
	private $_selProd;
	private $_url;
	
		
	public function __construct($data) {
		$this->sendRequest($data);
		if($data->url){
			$this->_url = $data->url;
		}
	}     
	
	
	public function getUrlBasket() 
	{
		return $this->_url;
	}
	
	private function setDateUntil()
	{
		$dtz = new DateTimeZone("Europe/Prague"); //your timezone
		$now = new DateTime(date("Y-m-d H:i:s"), $dtz);
		$now->modify('+10 hour');
		return $now->format("Ymd").'T'.$now->format("His");
	}
	
	public function getProducts() 
	{

		foreach ($this->_response['Products'] as $value) {  
			$this->separeteItem($value);
		}
		return $this->_products; 
	}
	
	private function separeteItem($result,$isNum = false)
	{
		if(is_array($result))  
		{ 
			foreach ($result as $key => $value) {
					$this->separeteItem($value,$isNum);
			} 
		}
		else 
		{  
			$item = explode('|', $result);  
 		
			if($isNum){
				$this->_num[$item[0]] = $item[1];       
			}else{ 
				$this->_products[$item[0]] = $item[1];
			} 
		}
	} 
	
	public function getTextProduct()
	{
  		 
		$text = '<strong>Cena na splátky dnes</strong><span id="cofidis"></span>
								<br>
								<p class="price-cofidis">
									<strong>'.helper_FrontEnd::price($this->_response['Deposit_min']/100).' Kč</strong>  
								</p>
								<p class="text-cofidis">
									'.helper_FrontEnd::price($this->_response['Deposit_min']/100).' Kč, poté 
									'.$this->_response['Num_installments'].' × '.helper_FrontEnd::price(($this->_response['Total_payment']/$this->_response['Num_installments'])/100).'
									 Kč Celkem: '.helper_FrontEnd::price(($this->_response['Total_payment']/100)).' Kč, RPSN: '.str_replace('%2c' , ',', $this->_response['Rpsn']).'
									  % 
								</p>'; 
		return $text;   
	}
	
	public function getPrice()
	{
		return $this->_response['Amount']/100;
	}
	
		public function getDeposit1($input)
	{
		
		if($_SESSION['_selProd'] == $input->product){   
			$d = $input->deposit > $this->_response['Deposit_min'] ?  $input->deposit : ($this->_response['Deposit_min']/ 100);	
		}
		else{  
			$_SESSION['_selProd'] = $input->product;
			$d = ($this->_response['Deposit_min']/ 100); 
		}	
     	 
		return $d == 0 ? '0' : $d;    
	}
	
	public function getDeposit($input)
	{
	      
		if($_SESSION['_selProd'] == $input->product){
			$depo = round(str_replace('%2c' , '.',$this->_response['Deposit_min']))/ 100;      				  
			$d = $input->deposit > $depo ?  $input->deposit : ($depo);
		}
		else{  
			$_SESSION['_selProd'] = $input->product;            
			$d = (str_replace('%2c' , '.',$this->_response['Deposit_min'])/ 100);
			              
		}	
		return $d == 0 ? '0' : $d;   
	}
	
	public function getSelectedProduct()
	{
		return $this->_response['Selected_product'];  
	}
	
	public function getSelectetedNumInstallmenst()  
	{
		return $this->_response['Selected_installment'];
	}
	
	public function getInstallments()
	{
		$this->separeteItem($this->_response['Installments'],true);
		return $this->_num;     
	}
	   
	public function sendRequest($data)
	{
             
			$data->deposit = str_replace(" " ,"", $data->deposit);        
		$data->amount = str_replace(" " ,"", $data->amount);   

		$request_data = $this->setRequestData($data);
		$this->_response = $this->backgroundcall_open_demand($request_data); // odesilani zadosti
	}
	 
 
 	public function renderResult()  
 	{   
 		$text .= '<table id="pop-kalkulator">';               
 		$text .= '<tr>
 					<th>Vaše měsíční splátka: </th><td><strong>'.helper_FrontEnd::price(str_replace('%2c' , ',', $this->_response['Total_payment'])/$this->_response['Num_installments']/100).'</strong> Kč
 				</td></tr>';       
		$text .= '<tr>  
			<th>RPSN: </th><td><strong>'.str_replace('%2c' , ',', $this->_response['Rpsn']).'</strong></td></tr>';  
		$text .= '<tr><th>Úrok p.a.: </th><td><strong>'.str_replace('%2c' , ',', $this->_response['Interest']).'</strong> %</td></tr>';     
		$text .= '<tr><th>Celkem zaplatíte: </th><td><strong>'.helper_FrontEnd::price(($this->_response['Total_payment']/100)+($this->_response['Deposit']/100)).'</strong> Kč</td></tr>';     
		$text .= '<tr><th>Počet splátek: </th><td><strong>'.$this->_response['Num_installments'].'</strong></td></tr>';
		$text .= '<tr><th>Výše uvěru: </th><td><strong>'.helper_FrontEnd::price($this->_response['Amount']/100).'</strong> Kč</td></tr>';         
		$text .= '</table>';         
		return $text; 
 	}
	
	public function setRequestData($data) 
	{
		$valid_until = $this->setDateUntil();
		$seed = uniqid(); 
		$merchant_id = "15002";  
		 $amount = $data->amount ? $data->amount*100 : '500000'; 
		$product = $data->product ? $data->product : '2';    
		$selected_installment = $data->selected_installment ? $data->selected_installment : '0';
		$num_installments = '0';    
		$deposit = $data->deposit ? $data->deposit*100 : '1';
		 $request_data = array(
		  					'operation' => 'GET_LOAN_DEMAND_PARAMETERS',
		  					'currency' => 'CZK', 
                            'amount' => $amount,    
                            'deposit' => $deposit,  
                            'load_amount' => '0',
                            'num_installments' => $num_installments,
                            'selected_product' => $product,
                            'selected_installment' => $selected_installment,  
                            'valid_until' => $valid_until,
                            'seed' => $seed,
                            'merchant_id' => $merchant_id
							); 		
			return $request_data;
	}
	
	// pomocne funkce 
  function http_parse_headers( $header ) {
        $retVal = array();
        $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
        foreach( $fields as $field ) {
            if ( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
                $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
                if ( isset($retVal[$match[1]]) ) {
                    $retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
                } else {
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
          
        
        curl_setopt( $ch, CURLOPT_URL, 'https://gw1.iplatba.cz/Service/Calculator'); // adresa na zadost
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
        return $parsed_header; 

    }
    
	
	
} 
?>