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
class Cms_View_Helper_FormFile extends Zend_View_Helper_FormElement
{    
    public function FormFile($name, $value = null, $attribs1 = null, $attribs2 = null)
    {     	
        return $this->view->render('controls/admin/content/_File.phtml');
    }
}
