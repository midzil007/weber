<?
/**
 * Třída pro razeni poli
 * @package helper
 */
 
class helper_Sorting {

	public static $sortPropertyName = '';
	
	function cmp_title($a1, $a2) {
		return strcoll( iconv("UTF-8", "ISO-8859-2",$a1['title']),  iconv("UTF-8", "ISO-8859-2",$a2['title']));
	}
	
	function cmp_titleDesc($a1, $a2) {
		return self::cmp_title($a1, $a2) * -1;
	}
	
	function cmp_title2($a1, $a2) {
		return strcoll( iconv("UTF-8", "ISO-8859-2",$a1),  iconv("UTF-8", "ISO-8859-2",$a2));
	}
	
	function cmp_titleDesc2($a1, $a2) {
		return self::cmp_title2($a1, $a2) * -1;
	}
	
	function cmp_variable($node1, $node2) {
		if(!is_object($node1)) {
			return 0; 
		}
		 
		$c1 = $node1->getTheRightContent();
		$c2 = $node2->getTheRightContent();
		if(!$c1 || !$c2){
			return 0;
		}
		$p1 = $c1->getPropertyValue(self::$sortPropertyName);
		$p2 = $c2->getPropertyValue(self::$sortPropertyName); 
		
		return strcoll( iconv("UTF-8", "ISO-8859-2",$p1),  iconv("UTF-8", "ISO-8859-2",$p2));
		 
	} 
	
	function cmp_variableDesc($node1, $node2) {
		return self::cmp_variable($node1, $node2) * -1;  
	}
	
	function cmp_property($node1, $node2) {
		if(!is_object($node1)) {
			return 0; 
		}
		 
		$pn = self::$sortPropertyName;
		
		return strcoll( iconv("UTF-8", "ISO-8859-2",$node1->$pn ),  iconv("UTF-8", "ISO-8859-2",$node2->$pn));
		 
	} 
	
	function cmp_propertyDesc($node1, $node2) { 
		return self::cmp_property($node1, $node2) * -1;  
	}
	
}

?>
