<?php
/**
 * CMS
 */


/**
 * Abstract class for extension
 */
require_once 'Zend/View/Helper/FormElement.php';


/**
 * Helper to generate a "text - number" element
 *
 */
class Cms_View_Helper_FormNumber extends Zend_View_Helper_FormElement
{
    
    public function formNumber($name, $value = null, $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable

        // build the element
        if ($disable) {
            // disabled
            $xhtml = $this->_hidden($name, $value)
                   . $this->view->escape($value);
        } else {
        	
        	
        	if($this->view->useDojo){
        		$d = ' dojoType="dijit.form.TextBox" ';        	
        	}
        
            // enabled
            $xhtml = '<input type="text" '. $d
                   . ' name="' . $this->view->escape($name) . '"'
                   . ' id="' . $this->view->escape($id) . '"'
                   . ' value="' . $this->view->escape(str_replace('.', ',', $value)) . '"'
                   . $this->_htmlAttribs($attribs)
                   . ' />';
        }

        return $xhtml;
    }
}
