<?php

class module_Encryption
{    
	function __construct($securityKey, $securityVector){ 		
		$this->securityKey = $securityKey;	
		$this->securityVector = $securityVector;	
		
		/* Open the cipher */
		$this->td = mcrypt_module_open('rijndael-256', '', 'cbc', '');
    	$this->iv = $this->securityVector;
    	$this->ks = mcrypt_enc_get_key_size($this->td);
    	
    	mcrypt_generic_init($this->td, $this->securityKey, $this->iv);
	}
	
	function __destruct(){
		mcrypt_generic_deinit($this->td);
    	mcrypt_module_close($this->td);
	}
        
    public function encrypt($data){    	
    	
    	/* Intialize encryption */
    	mcrypt_generic_init($this->td, $this->securityKey, $this->iv);
    	
    	$encrypted_data = mcrypt_generic($this->td, $data);
    	
		return $encrypted_data;
    }
    
    public function decrypt($encryptedData){    	 
    	if($encryptedData == ''){
    		return '';
    	}
    	
    	mcrypt_generic_init($this->td, $this->securityKey, $this->iv);  	
    	
    	$decrypted_data = mdecrypt_generic($this->td, $encryptedData);
    	
    	//$substrLength = strlen($decrypted_data) / 2;    	
      	//$decrypted_data = substr($decrypted_data, 0, $substrLength);
      	$decrypted_data = trim($decrypted_data);
		return $decrypted_data;
    }
    
}