<?
/**
 * Třída pro praci s multiselectem, radio buttonama a checkboxama
 * @package helper
 */
 
class helper_MultiSelect {
    	
	static function setMultiSelectValues($values, $separator = '|'){
    	if(!is_array($values)) {
    		return '';
    	}
    	$values2 = array();
    	foreach ($values as $k => $v){
    		if((is_numeric($v) && $v > 0) || (!is_numeric($v) && $v != '')){
    			$values2[] = $k;
    		}
    	}
		$vals = implode($separator, $values2);
		return $vals;
	}
	
	/**
	 * Pokud chci ulozit pole hodnot, ne 0,1,2
	 *
	 * @param unknown_type $array
	 */
	static function fixArrayValues($array){
		$nA = array();
		foreach ($array as $k => $v){
			$nA[$v] = $v;
		}
		return $nA; 
	}
	/*
    static function setMultiSelectValues($values, $separator = '|'){
    	
    	if(!is_array($values)) {
    		return '';
    	}
    	$values2 = array();
    	if((is_numeric($v) && $v > 0) || (!is_numeric($v) && $v != '')){
    		$values2[] = $k;
    	}
		$vals = implode($separator, $values2);
		return $vals;
	}
	*/	
	
	static function getMultiSelectValues($values, $separator = '|'){
		$vals = array();
		if($values != ''){
			if(strpos($values, $separator)){
				$vals = explode($separator, $values);
			} elseif ($values != ''){				
				$vals = array($values);
			}
		}
		return $vals;
	}
	
	static function getMultiSelectValuesAsString($property, $separator = '|'){
		$vals = self::getMultiSelectValues($property->value, $separator);
		$string = '';		
		foreach ($property->options as $i => $descr){
			if(in_array($i, $vals)){
				$string .= '1';
			} else {
				$string .= '0';
			}
			
		}
		return $string;
	}
	
	static function getMultiSelectValuesFromArray($values){
		if(!count($values)){
			return array();
		}
		foreach ($values as $i => $value){
			if($value <= 0){				
				unset($values[$i]);
			}
		}		
		return $values;
	}
		
}
?>
