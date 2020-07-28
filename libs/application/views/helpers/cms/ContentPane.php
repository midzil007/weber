<?php
/**
 * CMS
 * *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 */


/**
 * Helper for ContentPane
 *
 * @uses Zend_View_Helper_ContentPane
 * @category   Cms
 * @subpackage Helper
 */
class Cms_View_Helper_ContentPane extends Zend_View_Helper_FormElement
{
		
    public function ContentPane(array $attribs, $href = '', $content = '', $doEscape = false, $type='ContentPane')
    {           			
    	if(!strpos($href, '?')){
    		$href .= '?';
    	}
    	$href .= '&extload=1';      
        //   $contents = Utils::loadUrl(Utils::getWebUrl() . $href);  
        if ($attribs) { 
            $attribs = $this->_htmlAttribs($attribs);
        } else {  
            $attribs = '';
        }
        
        if ($doEscape) { 
            $content = $this->view->escape($content);
        } 
        return '<div class="contentpane" ' . $attribs . ' >' . $content . '' . $contents . '</div>';
    }
} 
