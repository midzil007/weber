		<?
/**
 * Třída pro praci s multiselectem, radio buttonama a checkboxama
 * @package helper
 */
 
class helper_Input {
        	
	public static $months = array(
		1 => 'leden',  
		'únor',
		'březen',
		'duben',
		'květen',
		'červen',
		'červenec',
		'srpen',
		'září',
		'říjen',
		'listopad',
		'prosinec'
	);
	
	public static $months2 = array(
		1 => "ledna",
		"února",
		"března",
		"dubna",
		"května",
		"června",
		"července",
		"srpna",
		"září",
		"října",
		"listopadu",
		"prosince"
	);
	
	public static $dayNames=array(
		"Neděle",
		"Pondělí",
		"Úterý",
		"Středa",
		"Čtvrtek",
		"Pátek",
		"Sobota"
	);
	
	public static $countries1 = array(
		'cz' => 'Česká republika',
		'sk' => 'Slovenská republika' 
	);
	  
	function  parseInt($string) {
	//	return intval($string);
		$string = str_replace(' ', '', trim($string));  
		if(preg_match('/(\d+)/', $string, $array)) {
			return $array[1]; 
		} else {
			return 0;
		}
	}

	
	static function addOption($options, $key, $value, $toBeginning = false){		
		if($toBeginning){
			$newOptions = array();
			$newOptions[$key] = $value;
			foreach ($options as $k => $v){
				$newOptions[$k] = $v;
			}			
			$options = $newOptions;			
		} else {
			$options[$key] = $value;
		}
		return $options;
	}
	
	static function addNotDefinedOption($options, $notDefinedText = '-- Vyberte --', $notDefinedValue = '-1'){
		return self::addOption($options, $notDefinedValue, $notDefinedText, true);		
	}
	
	static function check_personal_idenfication_number( $number ){
	    //516111/458
	    $len = strlen( strval( intval( str_replace('/', '', $number ) ) ) );
	    if( $len < 9 || $len > 10 )
	        return 0;
	        
	    $pin['year'] = substr( $number, 0, 2);
	    $pin['month'] = intval( substr( $number, 2, 2 ) );
	    $pin['day'] = intval( substr( $number, 4, 2 ) );
	    $pin['ending'] = substr( $number, 6, 3);
	    $pin['check_number'] = substr($number, 9, 1);
	    
	    //year
	    if( $len == 9 ){
	        if( $pin['year'] > 53 )
	            return 0;
	        else
	            $pin['year'] = 19 . $pin['year'];
	    }
	    else if( $len == 10 ){
	        $pin['year'] = ( date('y') >= $pin['year'] ? 20 . $pin['year'] : 19 . $pin['year'] );   
	    }
	        
	    //month
	    if( $pin['month'] > 50  && $pin['month'] < 63 )
	        $pin['month'] -= 50;
	    if( $pin['month'] < 1 || $pin['month'] > 12 )
	        return 0;
	        
	    //check days
	    if( $pin['day'] >  cal_days_in_month(CAL_GREGORIAN, $pin['month'], $pin['year'] ) )
	        return 0;
	
	    return 1;
	}	

	static function getDateBornSelectData($minAge = 15, $maxAge = 85){
		$y = date('Y');
		$min = $y - $maxAge;
		$max = $y - $minAge;
		$sel = array();
		for($i = $max; $i >= $min; $i--){
			$sel[$i] = $i;
		}
		return $sel;
	}
	
	static function getAgeSelectData($minAge = 15, $maxAge = 85){		
		$sel = array();
		for($i = $minAge; $i <= $maxAge; $i++){
			$sel[$i] = $i;
		}
		return $sel;
	}
	
	static function getStrToTime($date){
		if($date != '?'){
			$d = explode('.', $date);
			return strtotime($d[2].'-'.$d[1].'-'.$d[0]);
		}
	}
	
	static function getNumberSelectData($min = 10, $max = 150){		
		$sel = array();
		for($i = $min; $i <= $max; $i++){
			$sel[$i] = $i;
		}
		return $sel;
	}
	
	static function getMonthsSelecData(){				
		return self::$months2;
	}
	 
	static function getMonthsSelecDataLeden(){				
		return self::$months;   
	}
	
	static function onlyFirstLetterBig($text){
		$letters = explode(' ', $text);
		$newText = array();
		foreach ($letters as $letter){
			$newText[] = self::_onlyFirstLetterBig($letter);  
		}
		return implode(' ', $newText);
		 
	}
	
	static function _onlyFirstLetterBig($text){ 		 
		return mb_strtoupper(mb_substr($text, 0, 1, 'utf-8'), 'utf-8') . mb_strtolower(mb_substr($text, 1, 150, 'utf-8'), 'utf-8'); 
	}
}

?>
