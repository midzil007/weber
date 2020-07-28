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
class Cms_View_Helper_FormMultiPageSelect extends Zend_View_Helper_FormElement
{    
    public function FormMultiPageSelect($name, $value = null, $attribs = null, $options = array())
    {          
    	if(!isset($options['root'])){
    		$options['root'] = 1;
    	}
    	if(!isset($options['display'])){
    		$options['display'] = 'BOTH';  
    	}  
    	
		$conf = Zend_Registry::getInstance()->config;
		
		$values = helper_MultiSelect::getMultiSelectValues($value);		
		$showValues = array();
		foreach ($values as $val){
			$title = $this->view->tree->getNodeById($val)->title;
			if($title){
				$showValues[] = $title;
			}
		}
		
		$this->view->pageSelectOptions = $options;
		$this->view->pageSelectName = $name;
		$this->view->pageSelectValue = $value;		
		$this->view->pageSelectValueShow = implode(', ', $showValues);		
		$this->view->pageSelectValue = $this->view->pageSelectValue?$this->view->pageSelectValue:0; 
		$this->view->pageSelectUrl = $this->view->url(array('controller' => 'pages','action' => 'pageSelect', 'root' => $options['root'], 'display' => $options['display'], 'callbackInput' => $this->view->pageSelectName, 'value' => $this->view->pageSelectValue ));
		  		
        return $this->view->render('helpers/MultiPageSelect.phtml');
    }
}
