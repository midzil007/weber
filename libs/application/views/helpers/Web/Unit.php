<?php
/**
 * CMS
 * *
 */


/**
 * Spravne zobrazení jednotek a formátů
 */
class Web_View_Helper_Unit
{   
    public function Unit($value, $unitTitle = '', $type = 'text', array $options = array())
    {    	    	
    	switch($type){
    		case 'double':
    			$xhtml = $value;
    			break;    			
    		default:
    		case 'text':
    			$xhtml = $value;
    			break;
    	}
    	
    	if($unitTitle){
    		$xhtml .= '&nbsp;' . $unitTitle;
    	}
    	
        return $xhtml;
    }
}
