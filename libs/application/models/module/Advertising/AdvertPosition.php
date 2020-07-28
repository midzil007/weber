<?php

class module_Advertising_AdvertPosition
{
	public $title;
	public $rotate = false;
	public $random = true;
	public $identificator = true;
	public $adverts = array();
	public $show = 1;
	
	function __construct($identificator, $title = false, $rotate = false, $random = false, $show = 1, $imageAndText = false) {
		$this->identificator = $identificator;
		$this->title = $title;
		$this->rotate = $rotate;
		$this->random = $random;
		$this->imageAndText = $imageAndText;
		$this->show = $show;
	}
	
	function setAdverts($adverts){		
		foreach ($adverts as $x => $advert){
			if($advert->title == '' && $advert->description == '' && $advert->file->value == '' && $advert->url == ''){
				unset($adverts[$x]);
			}
		}
		$this->adverts = $adverts;
	}
	
	function getAdverts(){
		return  $this->adverts;
	}
	
	// INIT DATA FROM INPUT */
	function getPositionsIdentificators($input){
		$positions = array();
		if(is_array($input->title) && count($input->title)){
			$positions = array_keys($input->title);
			return $positions;
		} else {
			return false;
		}
	}
	
	function getPositionInfoFromInput($input, $position){
		$posInfo = array();
		$advert = new module_Advertising_Advert();
		$vars = get_object_vars($advert);
		$leap = array('posIdentificator'); // , 'identificator'
	
		foreach( $input->title[$position] as $ident => $value){
			$advert = array();
			foreach ($vars as $name => $property){			
				if(in_array($name, $leap)){
					continue; 
				}
				$prop = $input->$name;
				$pos = $prop[$position];
 
				
	    		if($name == 'file'){
					$pName = $name . '_' . $position . '_' . $ident;				
					//e($input->$pName);
					$advert[$name]	= $input->$pName;	
	    		} else {
	    			$val = stripslashes($pos[$ident]);
	    			/*
	    			$val = str_replace('\\', 'QxQ', $pos[$ident]);
	    			$val = str_replace('QxQQxQQxQ', '', $val);
	    			$val = str_replace('QxQ', '\\', $val);
	    			*/
	    			$advert[$name] = $val;
	    		}
	    	}  

	    	if($advert['active']  == ''){
	    		$advert['active']  = 0;
	    	} 
	    	$posInfo[] = $advert;	
	    	
    	}
    	return $posInfo;
	}
	
	function initAllAdvertsFromInput($input) {
		$allAdvets = array();
		$positions = $this->getPositionsIdentificators($input);
		if(!$positions){
			return $allAdvets;
		}
		foreach ($positions as $position){
			$advertData = $this->getPositionInfoFromInput($input, $position);			
			foreach ($advertData as $adv){
				$advert = new module_Advertising_Advert(
					$position, 
					$adv['title'], 
					$adv['description'], 
					$adv['url'], 
					$adv['active'], 
					$adv['showFrom'], 
					$adv['showUntil'], 
					$adv['target'], 
					$adv['file'],
					$adv['google'],
					$adv['identificator']
				);
				$allAdvets[$position][] = $advert;
			}
		}
		return $allAdvets;
	}
	
	function hasBanner(){
		return count($this->getVisibleAdverts());
	}
	
	function getVisibleAdverts(){		
		if(!is_array($this->visibleAdverts)){
			$now = date('Y-m-d');
			$this->visibleAdverts = array();		
			foreach ($this->adverts as $advert){	
				if($advert->active != 1){
					continue;
				}
				if($advert->showFrom->value){
					if(strcasecmp($advert->showFrom->value, $now) > 0){
		    			continue;
		    		}
				}
	    		if($advert->showUntil->value){
					if(strcasecmp($advert->showUntil->value, $now) < 0){
		    			continue;
		    		}
				}
				
	    		$this->visibleAdverts[] = $advert;
			}
		}		
		return $this->visibleAdverts;
	}
	
	function render($view, $template = false){
		$xhtml = '';
		if(!count($this->adverts)){
			return $xhtml;			
		}
		if($this->random && $this->show == 1){
			// $i = rand(0, (count($this->adverts)-1));
			$i = array_rand($this->adverts); 
			
			$advert = $this->adverts[$i];
			$xhtml = $advert->render($view, $template); 
		} elseif($this->show > 1){			
			$max = min($this->show, count($this->adverts));
			$i = 0; 
			foreach ($this->adverts as $banner){ 
				$i++; 
				if($i > $max){ break; }
				$banner->imageAndText = $this->imageAndText; 
				if(method_exists($banner, 'render')){  
					$xhtml .= $banner->render($view, $template);
				} else { 
					// e($this->adverts[$i]); 
				}
			}
		} else {
			$advert = $this->adverts[0];
			$xhtml = $advert->render($view, $template);
		}
		
		return $xhtml;		
	}
	
}