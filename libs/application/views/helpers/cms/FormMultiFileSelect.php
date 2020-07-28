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
class Cms_View_Helper_FormMultiFileSelect extends Zend_View_Helper_FormElement
{    
    public function FormMultiFileSelect($name, $value = null, $attribs = null, $options = array())
    {          

    	    	
    	
		$this->view->visibleOptions = 3;	
		$this->view->prevFiles = $this->view->prevFilesPreview = array();
		$this->view->MultiFileSelectName = $name;
		
		
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
    	 
    	$this->view->multiFileOptions = $options;	 
    	
		$this->view->addSubmitFunction .= "saveOptions(document.PageForm, '" . $name . "_fileSelect', 'trigger', '$name'); "; 
		//   e($this->view->addSubmitFunction);
		if($this->view->content && !$options['noContent']){
			$files = $this->view->content->getFilesNames($name); 
		} elseif ($value){  
			$fakeContent = new Content();
			$files = $fakeContent->getFilesNames('foo', $value);
		} 
		
		
		$this->view->uploadFolderNodeId = $options['uploadFileDirectoryNodeId'];
		  
		$this->view->visibleOptions = $this->view->prevFiles = array(); 	
		if(count($files)){			 
			$conf = Zend_Registry::getInstance()->config;
			$isPng = Zend_Registry::getInstance()->settings->getSettingValue('disableJPGTransform');
			$this->view->visibleOptions = count($files);
			$z = 1; 
			foreach ( $files as $path => $name){		

				$fnid = content_SFSFile::getFileNodeId($path);
				$nm = Zend_Registry::getInstance()->nodeMeta;  
				$cm = $nm->getMetaById($fnid);  
				  //  pr($cm);
				$this->view->prevFiles[$z]['name'] = $name;
				$this->view->prevFiles[$z]['alt'] = $cm['fileAlt']; ;
				$this->view->prevFiles[$z]['url'] = $cm['fileUrl']; ;
				$this->view->prevFiles[$z]['path'] = $path; 
				$this->view->prevFiles[$z]['ident'] = $name . rand(9999,99999999);				
				$this->view->prevFilesPreview[$z] = Utils::getFrontEndLink($path, false, '', true, $this->view->prevFiles[$z]['ident']);
				
				if(content_SFSFile::isResizableImage($path)){
					list($nodeId, $n) = content_SFSFile::parseSFSPath($path);
					$thump = '';
					if($isPng !=0)
					{
						$thump = '/sysThumb-' . content_SFSFile::getFileExtension($n);
					}
					$this->view->prevFiles[$z]['previewImage'] =  $conf->sfFolder . '/' . $nodeId . $thump. '/' . $n;	
				}											
				$z++; 
			}
		}
		
        return $this->view->render('controls/admin/content/_MuiltiFiles2.phtml');
       //  return $this->view->render('controls/admin/content/_MuiltiFiles.phtml');
    }
}
