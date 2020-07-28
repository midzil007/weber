<?php

class module_Advertising_Advert
{
	public $title;
	public $description;
	public $url = '';
	public $active = true;
	public $showFrom = '';
	public $showUntil, $file = '';
	public $target = '_blank';
	public $posIdentificator = true;
	public $identificator = false;
	
	function __construct($posIdentificator = false, $title = false, $description = false, $url = false, $active = true, $showFrom = false, $showUntil = false, $target = false, $file = false, $google = '', $identicifator = false) {
		$reg = Zend_Registry::getInstance();
		if(!$identicifator){
			$identicifator = mt_rand();
		}
		$this->identificator = $identicifator;
		$this->posIdentificator = $posIdentificator;
		$this->title = $title;
		$this->description = $description;
		$this->google = $google;
		$this->url = $url; 
		    
		
		$this->active = $active;
		$this->showFrom = new ContentProperty('showFrom[' . $this->posIdentificator . '][' . $this->identificator . ']','TextDate',$showFrom, array(), array(), array('class' => 'bs1'));
		$this->showUntil = new ContentProperty('showUntil[' . $this->posIdentificator . '][' . $this->identificator . ']','TextDate',$showUntil, array(), array(),  array('class' => 'bs1'));
		$this->target = new ContentProperty('target[' . $this->posIdentificator . '][' . $this->identificator . ']','Select',$target, array(), array('0' => 'Stejného okna', '_blank' => 'Nového okna', '_parent' => 'Nadřazeného okna' ));
		$this->file = new ContentProperty('file_' . $this->posIdentificator . '_' . $this->identificator ,'FileSelect', $file, array(), array(), array('showSelectFile' => true, 'inputWidth' => '150', 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => $reg->config->instance->bannersFolderNodeId ));   
		
	}
		
	function render($view, $template = false){ 
		$xhtml = '';  
		if($this->url{0} == '/'){
			$this->url = Utils::getWebUrl() . $this->url; 
		}
		$url = Utils::checkUrl($this->url);  
		
		if($view->config->modules->advertising->clickthru){
			//$url = $view->escape($url);
			$url = $this->url = Utils::getWebUrl() . '?b=' . $this->identificator . '&amp;redirect=' . $url;
		}
		
		
				
		if($this->google){
			$xhtml = $this->google;
		} elseif($this->imageAndText || $view->imageAndText){
			$this->target->value = $this->target->value ? $this->target->value : '_self'; 
			$xhtml = '<div class="advertRow">'; 
			if($this->file->value){
				$this->target->value = $this->target->value ? $this->target->value : '_self';  
				$b = new module_Banner($this->file->value, $url, $this->target->value);
				$xhtml .= $b->render($view); 
			} 
			$xhtml .= '<a class="bTitle" href="' . $url . '">' . $this->title . '</a><div class="bDescr">' . $this->description . '</div></div>';  
			 
		}  elseif($this->file->value){
	
			$this->target->value = $this->target->value ? $this->target->value : '_self';  
			$b = new module_Banner($this->file->value, $url, $this->target->value);

			$xhtml = $b->render($view);
		} else {
			$xhtml = '<p><a href="' . $url . '">' . $this->title . '</a><br />' . $this->description . '</p>';
		}
		
		$stats = new module_Advertising_AdvertStats();
		$stats->bannerAction($this->identificator, 'shown');
		
		return $xhtml;
	}
}