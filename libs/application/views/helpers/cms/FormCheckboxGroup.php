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
class Cms_View_Helper_FormCheckboxGroup extends Zend_View_Helper_FormElement
{    
    public function FormCheckboxGroup($name, $value = null, $attribs = null, $options = array())
    {   		
    	
    	$this->view->checkboxGroupName = $name;
		$this->view->options = $options;		
		  
		if($value != ''){
			if(is_array($value)){
				$this->view->optionsChecked = helper_MultiSelect::getMultiSelectValuesFromArray($value);
			} else {
				$this->view->optionsChecked = array_flip(helper_MultiSelect::getMultiSelectValues($value));
			}
			
		} else {
			$this->view->optionsChecked = array();
		}
		  
		if(!$attribs['style']){
			$attribs['style'] = "float:left; width:30%";
		}
		
		$this->view->atribs = $this->_htmlAttribs($attribs);
		return $this->view->render('controls/admin/content/_MuiltiCheckboxes.phtml');
    }
}
