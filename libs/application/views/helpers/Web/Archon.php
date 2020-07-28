<?php
/**
 * CMS
 * *
 */

class Web_View_Helper_Archon
{   
    public function Archon($url, $target = '')
    { 
    	$url = Utils::checkUrl($url); 
    	if($url){  
    		return '<a href="' . $url . '" ' . ($target?'target="' . $target . '"':'') . '>' . str_replace('http://', '', $url) . '</a>';
    	} else {
    		return '';
    	}
    }
}  
