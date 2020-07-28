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
class Cms_View_Helper_FormPageSelect extends Zend_View_Helper_FormElement
{    
    public function FormPageSelect($name, $value = null, $attribs1 = null, $attribs2 = null)
    {          
		//$attribs1['disabled'] = 'disabled';
		//$attribs1['style'] = 'border:1px solid gray;';
				
		if($value){		
			$showValue = $this->view->tree->getNodeById($value)->title;
		}
					
		$xhtml = $this->view->formHidden($name, $value);
		$attribs2['onclick'] = 'return openPageBrowser(\'' . $name . '\')';
		$xhtml .= ' ' . $this->view->formSubmit($name . '_trigger', 'Vybrat', $attribs2);
		$xhtml .= ' &nbsp; <input onkeypress="return false;" type="text" value="' . $showValue . '" name="' . $name . '_title" id="' . $name . '_title" style="border:1px solid #FFFFFF; font-weight:bold; background:#FFFFFF; width:350px;" />';
        return $xhtml;
    }
}
