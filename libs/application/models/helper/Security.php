<?
/**
 * Security
 * @package helper
 */
 
class helper_Security {
    		
	static function secureInput($val, $enc = false){
		$temp  = strtolower($val);
	if(!is_numeric(strpos($temp,'select'))){  
	  if(is_array($val)){
      foreach ($val as $i => $v){
       $val[$i] = self::secureInput($v);
      }
      $securedVal = $val;
     } else { 
      $securedVal = stripslashes(trim(($val)));
      if($enc){
       $securedVal = htmlspecialchars($securedVal, ENT_COMPAT, 'UTF-8');  
      }
	 }
	 return $securedVal;
	}
}
	
	static function encrypt($txt){
		$salt = Zend_Registry::getInstance()->config->instance->securityTextSalt;
		$salt = $salt?$salt:'wsdc4vh54j';
		return md5(sha1($txt . $salt));
	}

	static function encryptSimple($txt){		
		return sha1($txt);    
	}
	
	static function generatePassword($length = 6){ // musi byt / dvema
		$conso=array("b","c","d","f","g","h","j","k","l","m","n","p","r","s","t","v","w","x","y","z");
    	$vocal=array("a","e","i","u"); 
    	$password="";
    	srand ((double)microtime()*1000000);
	    $max = $length/2; 
	    for($i=1; $i<=$max; $i++)
	    {
		    $password.=$conso[rand(0,19)];
		    $password.=$vocal[rand(0,3)];
	    }
	    $newpass = $password;
	    return $newpass;
	} 
	
}
?>
