<?php
/**
 * CMS
 * *
 */

class Web_View_Helper_Email
{   
    public function Email($email, $name = '')
    { 
    	if($email){
    		$name = $name?$name:$email;
    		return '<a href="mailto:' . $email . '">' . $name . '</a>';
    	} else {
    		return '';
    	}
    }
}
