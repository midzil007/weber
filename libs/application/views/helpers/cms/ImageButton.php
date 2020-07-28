<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * Abstract class for extension
 */
require_once 'Zend/View/Helper/FormElement.php';


/**
 * Helper to generate a "text" element
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Cms_View_Helper_ImageButton extends Zend_View_Helper_FormElement
{
    /**
     * Generates a 'text' element.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are used in place of added parameters.
     *
     * @param mixed $value The element value.
     *
     * @param array $attribs Attributes for the element tag.
     *
     * @return string The element XHTML.
     */
    public function ImageButton($title, $url, $image, $imageOver = null, $attribs = null)
    { 
    	$randIdentifier = 'ib' . rand(0,99999999999);
    	$path = $this->view->cmsFolderPath;
    	
    	//styles      
    	$xhtml = '
        <style type="text/css">
        	a#' . $randIdentifier . ' { background-image:url(' . $path . '/images/newicons/' . $image . '); }	
			a#' . $randIdentifier . ':hover { background-image:url(' . $path . '/images/newicons/' . $imageOver . '); }					
		</style>
		';
        //preload
        $xhtml .= '<div class="preload" style="background-image:url(' . $path . '/images/newicons/' . $imageOver . ');">&nbsp;</div>';
        
        $xhtml .= '<a class="imageButton" '
               . ' href="' .$url. '"'
               . ' id="' .$randIdentifier. '"'
               . $this->_htmlAttribs($attribs)
               . '>' 
               . $title 
               . '</a>';
         

        return $xhtml;
    }
}
