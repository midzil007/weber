<?php

class module_XMLFeed 
{    	 
	public $newLine = "\n";
	public $sitemap = ''; 
		
    function __construct()
    {    
    	$this->tree =  Zend_Registry::getInstance()->tree;
        $this->db =  Zend_Registry::getInstance()->db;
        $this->config =  Zend_Registry::getInstance()->config; 
    }
	    
    public function generate(){
    	return $this->tree->getNodeById(1)->getChildren('FOLDER', true);
    }  
        
    function getPagesByContentType($contentType){
    	 
    	$select =  $this->db->select();
		$bind = array();
		$contentType = 'content_' . $contentType; 
		$select->from(array( 'cm' => $contentType), array('*'));      	
    	
		$select->join( 
			array('nc' => 'NodesContents'), 
        	'cm.id = nc.c_id',
        	array() 
        );
        
        $select->join(
			array('n' => 'Nodes'), 
        	'n.id = nc.n_id',
        	array('*')  
        );
        
        $select->where('state = ?', 'PUBLISHED'); 
        //  $select->where('export = ?', 1);     
        $select->where('c_type = ?', $contentType);
         
        return $this->db->fetchAll($select, $bind);
    }
    
     function getPages(){
     	$pages = $this->getPagesByContentType('Product');
     	// $pages = array();      	
     	//$pages = array_merge($this->getPagesByContentType('Accesories'), $pages);      	
     	return $pages; 
     }
    
    
    function append($txt){
    	$this->sitemap .= iconv('utf-8', 'windows-1250', $txt) . $this->newLine;  
    }
    
	function append2($txt){
    	$this->sitemap .= $txt . $this->newLine;  
    } 
    
    function append3($txt){ 
    	$this->sitemap .= iconv('utf-8', 'iso-8859-2', $txt) . $this->newLine;  
    } 
     
    function makeIso8601TimeStamp ($dateTime = 0) {
	    if (!$dateTime) {
	        $dateTime = date('Y-m-d H:i:s');
	    }
	    if (is_numeric(substr($dateTime, 11, 1))) {
	        $isoTS = substr($dateTime, 0, 10) ."T"
	                 .substr($dateTime, 11, 8) ."+00:00";
	    }
	    else {
	        $isoTS = substr($dateTime, 0, 10);
	    }
	    return $isoTS;
	} 
	    
    function render($view){
    	$pages = $this->getPages();
    	$basePath = Utils::getWebUrl();  	
    	
    
    	$this->append('<?xml version="1.0" encoding="windows-1250"?>');  
		$this->append('<shop>');  
		foreach ($pages as $page){
			$photos = $page['photos'];
			if($photos{0} == ';'){
				$photos = substr($photos, 1);
			}
			$part = explode(';', $photos); 
			$photo = $part[0] . ';' . $part[1];
			$img = content_SFSFile::getFileFromProperty($photo);
			
			 
			$html = $page['html'];
			$html = str_replace(array('<p>','</p>', '<div>', '</div>'), '', $html);
			//$page['title'] = str_replace(array('è'), '', $page['title']);
			  
			/*
			$html = str_replace('</li>', 'xxxx', $html);
			$html = strip_tags($html);
			$html = str_replace('xxxx', '<br/>', $html);    
			*/    
			  
			$page['dph'] = 20;
			
			$this->append('<SHOPITEM>');      
				$this->append('<PRODUCT><![CDATA[' .  $view->escape($page['title']) . ']]></PRODUCT>'); 
				$this->append('<DESCRIPTION><![CDATA[' . $view->escape($html) . ']]></DESCRIPTION>');
				 
				$this->append('<PRICE_VAT>' . $page['price'] . '</PRICE_VAT>');
				$this->append('<VAT>' . $page['dph'] . '</VAT>'); 
				   
				$this->append('<URL>' . $basePath . $page['path'] . '</URL>'); 
				$this->append('<DELIVERY_DATE>0</DELIVERY_DATE>');  
				 
				if($img['path']){
					$this->append('<IMGURL>' . $basePath . $img['path'] . '</IMGURL>');
				}   
				/*
				if($page['id'] == 1466){
					$this->append('<TOLLFREE>0</TOLLFREE>'); 
				} else {  
					$this->append('<TOLLFREE>1</TOLLFREE>'); 
				} */
			$this->append('</SHOPITEM>');
			 
		}
		$this->append('</shop>');
		 
		ob_clean();   
		 header('Content-type: application/xml; charset="windows-1250"',true); 
		echo $this->sitemap; 
		die(); 
    } 
    
    function renderHeureka($view){
    	$pages = $this->getPages();
    	$basePath = Utils::getWebUrl();  	
    	
    
    	$this->append2('<?xml version="1.0" encoding="utf-8"?>');  
		$this->append2('<shop>');  
		foreach ($pages as $page){
			$photos = $page['photos'];
			if($photos{0} == ';'){
				$photos = substr($photos, 1);
			}
			$part = explode(';', $photos); 
			$photo = $part[0] . ';' . $part[1];
			$img = content_SFSFile::getFileFromProperty($photo);
			
			
			$page['dph'] = 20;   
			$html = $page['html'];
			$html = str_replace(array('<p>','</p>', '<div>', '</div>'), '', $html);
			 			  
			$this->append2('<SHOPITEM>'); 
				$this->append2('<PRODUCT><![CDATA[' . $page['title'] . ']]></PRODUCT>'); 
				$this->append2('<PRICE>' . $page['price'] . '</PRICE>');
				$this->append2('<PRICE_VAT>' . $page['price'] . '</PRICE_VAT>');
				$this->append2('<VAT>' . $page['dph'] . '</VAT>'); 
				$this->append2('<DESCRIPTION><![CDATA[' . strip_tags($html) . ']]></DESCRIPTION>');   
				$this->append2('<URL>' . $basePath . $page['path'] . '</URL>');
				$this->append2('<DELIVERY_DATE>0</DELIVERY_DATE>');   
				if($img['path']){  
					$this->append2('<IMGURL>' . $basePath . $img['path'] . '</IMGURL>');
				}  
			$this->append2('</SHOPITEM>');
			 
		}
		$this->append2('</shop>');
		 
		ob_clean();  
		header('Content-type: application/xml; charset="utf-8"',true);  
		echo $this->sitemap; 
		die(); 
    } 
    
    function renderHeldejceny($view){
    	$pages = $this->getPages();
    	$basePath = Utils::getWebUrl();  	
    	
    
    	$this->append3('<?xml version="1.0" encoding="iso-8859-2"?>');  
		$this->append3('<shop>');  
		foreach ($pages as $page){
			$photos = $page['photos'];
			if($photos{0} == ';'){
				$photos = substr($photos, 1);
			}
			$part = explode(';', $photos); 
			$photo = $part[0] . ';' . $part[1];
			$img = content_SFSFile::getFileFromProperty($photo);
			
			 
			$html = $page['html'];
			$html = str_replace('</li>', 'xxxx', $html);
			$html = strip_tags($html);
			$html = str_replace('xxxx', '<br/>', $html);  
			 			  
			$this->append3('<SHOPITEM>'); 
				$this->append3('<PRODUCT>' . $page['title'] . '</PRODUCT>'); 
				$this->append3('<PRICE>' . $page['price'] . '</PRICE>');
				$this->append3('<PRICE_VAT>' . $page['price2'] . '</PRICE_VAT>');
				$this->append3('<VAT>' . $page['dph'] . '</VAT>'); 
				$this->append3('<DESCRIPTION><![CDATA[' . $html . ']]></DESCRIPTION>');   
				$this->append3('<URL>' . $basePath . $page['path'] . '</URL>');
				$this->append3('<DELIVERY_DATE>0</DELIVERY_DATE>');   
				if($img['path']){
					$this->append3('<IMGURL>' . $basePath . $img['path'] . '</IMGURL>');
				}   
			$this->append3('</SHOPITEM>');
			 
		}
		$this->append3('</shop>');
		 
		ob_clean();  
		header('Content-type: application/xml; charset="iso-8859-2"',true);  
		echo $this->sitemap; 
		die(); 
    } 
}