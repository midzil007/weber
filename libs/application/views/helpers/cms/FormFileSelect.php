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
class Cms_View_Helper_FormFileSelect extends Zend_View_Helper_FormElement
{    
    public function FormFileSelect($name, $value = null, $attribs1 = null, $attribs2 = null, $options = array(), $inMultiselect = false)
    {    
    	
    	//e($options);
		if(!isset($options['showSelectFile'])){
    		$options['showSelectFile'] = true;
    	}  
    	
    	if(!isset($options['showUploadFile'])){
    		$options['showUploadFile'] = false;
    	}
    	
    	if(!isset($options['inputWidth'])){
    		$options['inputWidth'] = 420;
    	}
    	
    	if(!isset($options['inputText'])){
    		$options['inputText'] = 'název';
    	}
    	
    	if(!isset($options['inputVisible'])){
    		$options['inputVisible'] = 'inline';
    	}
    	 
    	if(!isset($options['showAlt'])){
    		$options['showAlt'] = 0;
    	} 
    	
   		if(!isset($options['showUrl'])){
    		$options['showUrl'] = 0;
    	} 
    	
		if(is_array($value)){
			$showValue = $value['name'];
			$path = $value['path'];
		} else {
			if(strpos($value, ';')){
				list($path, $showValue) = explode(';', $value);
			} else {
				$showValue = $path = $value;				  
			} 
		}
		 
		$this->view->multiFileOptions = $options;
		
		$this->view->uploadFolderNodeId = $options['uploadFileDirectoryNodeId'];
		
		//  $this->view->addSubmitFunction .= "saveOptions(document.PageForm, '" . $name . "_fileSelect', 'trigger', '$name'); "; 
			
		$this->view->visibleOptions = $this->view->prevFiles = array();  
		// $this->view->multiFileOptions = array();
		$this->view->multiFileOptions['maxFiles'] = 999;     
		
		$isPng = Zend_Registry::getInstance()->settings->getSettingValue('disableJPGTransform');
		$this->view->MultiFileSelectName = $name;
			$conf = Zend_Registry::getInstance()->config; 
    	if($path){
				list($nodeId, $n) = content_SFSFile::parseSFSPath($path);
				
				$fnid = content_SFSFile::getFileNodeId($path);
				$nm = Zend_Registry::getInstance()->nodeMeta;  
				$cm = $nm->getMetaById($fnid);
			 	
				$this->view->prevFiles[0]['name'] = $showValue; 
				$this->view->prevFiles[0]['alt'] = $cm['fileAlt']; 
				$this->view->prevFiles[0]['url'] = $cm['fileUrl'];  
				$this->view->prevFiles[0]['path'] = $path;
				$this->view->prevFiles[0]['ident'] = $name . rand(9999,99999999);				
				$this->view->prevFilesPreview[0] = Utils::getFrontEndLink($path, false, '', true, $this->view->prevFiles[$z]['ident']);
				
				if(content_SFSFile::isResizableImage($path)){
					list($nodeId, $n) = content_SFSFile::parseSFSPath($path);
					$thump = '';
					if($isPng !=0)
					{
						$thump = '/sysThumb-' . content_SFSFile::getFileExtension($n);
					}
					$this->view->prevFiles[0]['previewImage'] =  $conf->sfFolder . '/' . $nodeId .$thump. '/' . $n;	
				}		  
				 
			}
			//  pr($this->view->prevFiles); 
		//$xhtml = $this->view->formHidden($name, $path);
		  
        return $this->view->render('controls/admin/content/_MuiltiFiles2.phtml'); 
        return $xhtml;
    }
}
