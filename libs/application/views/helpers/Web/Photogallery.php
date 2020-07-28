<?php
/**
 * CMS
 * *
 */


/**
 * Spravne zobrazení jednotek a formátů
 */
class Web_View_Helper_Photogallery
{   
	public $view;
	
	public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
    
    public function Photogallery(array $photos, $template, $thumbName, $fullName, $photosPerLine = 3, $showSpacer = true, array $options = array())
    {    	
    	   
    	$conf = Zend_Registry::getInstance()->config;	
    	$this->view->photosPerLine = $photosPerLine;
    	$this->view->photos = $photos;
    	$this->view->showSpacer = $showSpacer;
    	$this->view->galleryItemTemplate = $conf->view->contentsDir . $template;
    	
    	$this->view->thumbName = $thumbName;
    	$this->view->fullName = $fullName;
    	
    	if($options['disableDojo']){
    		$this->view->disableDojo = true;
    	}
    	
    	if($options['groupby'] == 'nodeId'){
    		$this->view->photoGroup = $this->view->content->id;
    	} else {
    		$this->view->photoGroup = 'gal' . rand(999,9999999999);    		
    	}
    	
    	return $this->view->render($conf->view->contentsDir . 'Parts/Photogallery.phtml');
    }
}
