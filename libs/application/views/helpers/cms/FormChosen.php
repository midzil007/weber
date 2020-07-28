<?php
/**
 * CMS
 * *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 */

/**
 * Helper to generate a "wysiwyg" element
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Cms_View_Helper_FormChosen extends Zend_View_Helper_FormElement
{
	
	///($this->name,$this->value, $param3, $this->options);	    
    public function FormChosen($name, $value = null,  $attribs = null, $options)
    {
    	$this->view->name = $name;
		$this->view->value = explode("|",$value);
		$this->view->properties = $properties;
		$this->view->attribs = $attribs;      					
		$this->view->options = $options;
		return $this->view->render('controls/admin/forms/Chosen.phtml');
    }
}
