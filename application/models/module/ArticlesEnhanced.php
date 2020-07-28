<?php

class module_ArticlesEnhanced
{   
	private $imagePatern = '<a href="/data/sharedfiles/';
	private $mbox = '
		<script type="text/javascript"> 	 
		var box = {};    
		window.addEvent(\'domready\', function(){ 
			box = new multiBox(\'mbb\', {descClassName: \'multiBoxDesc\', useOverlay: true});   
		});  
		</script>	 
	';
	function __construct($domain = ''){
		$this->db =  Zend_Registry::getInstance()->db;
		$this->tree =  Zend_Registry::getInstance()->tree;
		$this->_tableName = 'content_Article';
	} 

	function makeTheMagic($view, $html){
		$html = str_replace('align="left"', 'align="left" style="float:left; margin:10px 15px 5px 0px;"', $html); 
	    $html = str_replace('align="right"', ' align="right" style="float:right; margin:10px 0px 5px 15px;" ', $html);
	    /*
	    // gallery
	    $hasPhotos = strpos($html, $this->imagePatern); 
	    if($hasPhotos){
	    	$newHtml = $add = '';
	    	$gid = 1; 
	    	$exploded = explode($this->imagePatern, $html);
	    	$imagesCount = count($exploded) - 1;
	    	foreach ($exploded as $ii => $t){  
	    		
	    		if($ii == 0){
	    			$newHtml .= $t; continue;	 
	    		} 
	    		  
	    		$isImage = strpos(current(explode('"', $t)), '.jpg'); 
	    		 
	    		if($isImage){
		    		$img = '<a target="_blank" rel="[images]" class="mbb" id="mb' . $gid . '" href="/data/sharedfiles/';
		    		$add .= '<div class="multiBoxDesc mbb' . $gid . '">tt ' . $gid . '</div>';
		    		$newHtml .= $img .  $t; 
		    		$gid++;
	    		} else { 
	    			$newHtml .= $this->imagePatern . $t;
	    		}
	    	}
	    	
	    	$html = $newHtml . $add . $this->mbox;
	    }*/ 
	    return $html;
	}
}