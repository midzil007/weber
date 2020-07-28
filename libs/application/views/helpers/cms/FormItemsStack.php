<?php
/**
 * CMS
 * *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 */

/**
 * Helper to generate a "stack" element
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 */
class Cms_View_Helper_FormItemsStack extends Zend_View_Helper_FormElement
{    
    public function FormItemsStack($name, $value = null, $attribs = null, $options = array())
    {           
    	$this->view->stackName = $name; 
    	$this->view->stackValue = $value; 		
    	$this->view->stackOptions = $options; 		 
        return $this->view->render('helpers/ItemsStack.phtml');  
    }
}
