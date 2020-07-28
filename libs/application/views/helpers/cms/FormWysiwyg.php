<?php
/**
 * CMS
 * *
 */
class Cms_View_Helper_FormWysiwyg extends Zend_View_Helper_FormElement
{    
    public function FormWysiwyg($name, $value = null, $height = 350, $prefix = 'fck_', $atribs = array())
    {          
    	if(!$atribs['toolbar']){
    		$atribs['toolbar'] = 'CMSToolbar';
    	}  
		if(!$height){
			$height = 350;
		}  
		if(!$atribs['height']){
    		$atribs['height'] = $height;
    	} 
    	if(!$atribs['width']){
    		$atribs['width'] = '100%';
    	} 

    	if(!$atribs['idecko']){
			$atribs['idecko'] = $name . '-' . rand(55, 99999);  
    	}   
		
		$atribs['name'] = $name;
		$atribs['value'] = $value;  
		$atribs['prefix'] = $prefix;
		
		if(!is_array($this->view->WysiwygIds)){
			$this->view->WysiwygIds = array();
		}
		$this->view->WysiwygIds[] = $atribs['idecko'];
		
		$this->view->wAtribs = $atribs;
		return $this->view->render('controls/admin/content/_WYSIWYGtiny.phtml');		 
    }
}
