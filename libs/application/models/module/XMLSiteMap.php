<?php

class module_XMLSiteMap 
{    
	public $excludeContentType = array(
		'content_SFSFile'
	); 
	
	public $newLine = "\n";
	public $sitemap = ''; 
	
	public $excludeNodes = array();
	public $hasDateShow = array();
	
    function __construct()
    {    
    	$this->tree =  Zend_Registry::getInstance()->tree;
        $this->db =  Zend_Registry::getInstance()->db;
        $this->config =  Zend_Registry::getInstance()->config; 
    }
	
		private function cleanText($html)
	{
		$html = trim(strip_tags($html)); 
		$html = str_replace('×', ' x' , $html);          
		//$html = str_replace('
//','',$html);    
		 return html_entity_decode(str_replace('  
 ', ' ', str_replace('
 
','',str_replace('°','',str_replace('&','',str_replace('©','',trim(strip_tags($html))))))));        
		      
	}      
	    
    public function generate(){
    	return $this->tree->getNodeById(1)->getChildren('FOLDER', true);
    }  
	
	public function renderImage($view) {
		$mProduct  = new module_Products();
		$mVarianta = new module_Varianta();
		$content   = new content_Product();
		$all       = $mProduct->getProducts(FALSE, false, 0, 9999999999999);
		$basePath  = Utils::getWebUrl();
		$this->append('<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" 
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">');
		foreach ($all as $page) {
			if (in_array($page['id'], $this->ids)) {
				continue;
			}
			$node = $this->tree->getNodeById($page['id']);
			if ($node->showInSitemap == '0') {
				continue;
			}
			//$content->getPropertyByName('photos')->value = $page['obrazky'];

			//$view->content->getFilesNames('photos');
			$photos      = $mVarianta->getResizedPhotos($page['obrazky']);
			$this->ids[] = $page['id'];
			$changefreq  = $page['type'] == 'FOLDER'?'daily':'weekly';
			$priority    = $page['type'] == 'FOLDER'?0.6:0.5;

			$path = helper_FrontEnd::getPath($view, $page['path']);

			if ($pathTrigger || strpos($path, 'http://') === false) {
				$path        = $basePath.$path;
				$pathTrigger = 1;
			}
			//$p      = helper_FrontEnd::getPhoto('photos', $content, $content->fotoCropShowName);
			$images = helper_FrontEnd::getResizedPhotos($photos, 'pShow', 'pFull');
			$this->append('<url>');
			$this->append('<loc>'.$path.'</loc>');
			//die();
			foreach ($images as $value) {  
				$this->append('<image:image>');      
				$this->append('<image:loc>'.$basePath.$value['fullPath'].'</image:loc>');
				$this->append('<image:title>'.$this->cleanText($value['name']).'</image:title>');
				$this->append(' </image:image>');
			}
			$this->append('</url>');
		}
		$this->append('</urlset>');
		ob_clean();
		header('Content-type: application/xml; charset="utf-8"', true);
		echo $this->sitemap;
		die();
		pr($all);
		die;
	}  
    
    function getExcludesNodes(){
    	//$files = $this->tree->getNodeById(2);
    	$this->excludeNodes[] = 2; 
    	$this->excludeNodes[] = 3; 
    	//$this->excludeNodes[] = 4; 
    	    	
    	$this->excludeNodes = array_merge($this->tree->getNodeChildrenIds(3, array(), 'BOTH'), $this->excludeNodes);   
    	$this->excludeNodes = array_merge($this->tree->getNodeChildrenIds(2, array(), 'FOLDER'), $this->excludeNodes);   
    	//$this->excludeNodes = array_merge($this->tree->getNodeChildrenIds(4, array(), 'BOTH'), $this->excludeNodes);  
    	/*  
    	if($_SERVER['REMOTE_ADDR'] == '217.195.175.151'){
    		pr($this->excludeNodes); 
    		die(); 	
    	}*/
    }
    
    function getPagesByContentType($contentType, $dateShow = false){
    	$this->getExcludesNodes();
    	$select =  $this->db->select();
		$bind = array();
		$contentType = 'content_' . $contentType; 
		if($contentType == 'content_Product'){
			$select->from(array( 'cm' => $contentType), array('n.id', 'n.title', 'n.path', 'n.parent', 'n.created', 'n.dateModif', 'n.type')); 
		}
		else{
			$select->from(array( 'cm' => $contentType), array('n.id', 'n.title', 'n.path', 'n.parent', 'n.created', 'n.dateModif', 'n.type'));
		}     	
    	
		$select->join( 
			array('nc' => 'NodesContents'), 
        	'cm.id = nc.c_id',
        	array() 
        );
        
        $select->join(
			array('n' => 'Nodes'), 
        	'n.id = nc.n_id',
        	array('n.title') 
        );
        
        if($contentType == 'content_Product'){ 
        	// 	$select->where('cm.skladem = ?', '1');
        	$select->join(
        			array('var' => 'module_eshop_variants'),
        			'var.id_product = cm.id',
        			array());
        	$select->where('var.skladem = ?', '1');
        }
        
        if($dateShow){
        	$select->where('dateShow <= ?', new Zend_Db_Expr('NOW()'));	
        }
        $select->where(" n.path NOT LIKE '%heureka%'"); 
        $select->where(" n.path NOT LIKE '%zbozi%'");
        $select->where(" n.path NOT LIKE '%neverejne%'");
        $select->where("n.id NOT IN ('" . implode("','", $this->excludeNodes) . "')"); 
        $select->where('state = ?', 'PUBLISHED'); 
        $select->where('c_type = ?', $contentType);
       
        return $this->db->fetchAll($select, $bind);
    }
    
     function getPages($contentTypes){
     	$this->getExcludesNodes();
     	$pages = array();
     	foreach ($contentTypes as $ctype){
     		$pages = array_merge($pages, $this->getPagesByContentType($ctype, in_array($ctype, $this->hasDateShow)));
     	}
     	return $pages;
     }
    
    /*
    function getPages(){
    	$this->getExcludesNodes();
    	 
    	$select =  $this->db->select();
		$bind = array();
		
		$select->from(array( 'n' => 'Nodes'), array('n.id', 'n.title', 'n.path', 'n.parent', 'created', 'dateModif', 'type'));     	
        
        $select->join(
			array('nc' => 'NodesContents'),
        	'n.id = nc.n_id',  
        	array('c_type') 
        ); 
		  
        $select->where("n.id NOT IN ('" . implode("','", $this->excludeNodes) . "')");  
        $select->where("c_type NOT IN (?)", implode("','", $this->excludeContentType)); 	
        
		// $select->where('state = ?', 'PUBLISHED'); 		
		$select->order('n.id DESC');      
		
		// $select->limit(50, 0);    
		
		
		return $this->db->fetchAll($select, $bind);
    }
    */
    
    function append($txt){
    	$this->sitemap .= $txt . $this->newLine;
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
	
	function getContentTypes(){
		$contentsTypes = array_merge(array_keys($this->config->contentTypes->toArray()), array_keys($this->config->overviewTypes->toArray()));
		foreach ($contentsTypes as $i => $type){
			if(in_array($type, $this->excludeContentType)){
				unset($contentsTypes[$i]);
			}
			$cName = 'content_' . $type;
			$c = new $cName;
			if(is_object($c->getPropertyByName('dateShow'))){ 
				$this->hasDateShow[] = $type;
			}
		}
		return $contentsTypes;
	}
    
    function render($view){
    	$ctypes = $this->getContentTypes();
    	$pages = $this->getPages($ctypes);
    	$basePath = Utils::getWebUrl();   	
 	 	$this->append('<?xml version="1.0" encoding="UTF-8"?>');  
		$this->append('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
		$pathTrigger = 0;
		foreach ($pages as $page){
			if(in_array($page['id'], $this->ids))
			{
				continue;
			}
			$node = $this->tree->getNodeById($page['id']);
		if($node->showInSitemap == '0')
				{
						continue;
				}
			$this->ids[] = $page['id'];
 			$changefreq = $page['type'] == 'FOLDER' ? 'daily' : 'weekly';
			$priority = $page['type'] == 'FOLDER' ? 0.6 : 0.5;
			
			$path = helper_FrontEnd::getPath($view, $page['path']);	
			
			  
			if($pathTrigger || strpos($path, 'http://') === false){ 
				$path = $basePath . $path;	
				$pathTrigger = 1;
			} 
			   
			$path = str_replace("//jura/","/jura/",$path); 
			 
			$this->append('<url>');
				$this->append('<loc>' . $path . '</loc>'); 
				$this->append('<changefreq>' . $changefreq . '</changefreq>');
				$this->append('<priority>' . $priority . '</priority>'); 
			$this->append('</url>');
		}
		$this->append('</urlset>');
		ob_clean();  
		header('Content-type: application/xml; charset="utf-8"',true);
		echo $this->sitemap; 
		die(); 
    } 
}