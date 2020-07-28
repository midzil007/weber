<?php

class module_Articles
{   
	function __construct($domain = ''){
		$this->db =  Zend_Registry::getInstance()->db;
		$this->tree =  Zend_Registry::getInstance()->tree;
		$this->_tableName = 'content_Article';
	}
    
	public function initSearch( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $root, $count = false)    
    {
    	$select =  $this->db->select();
		$bind = array();
		 
		if($count){    		
    		$c =  new Zend_Db_Expr("count('*')"); 
    		$select->from(array('cm' => $this->_tableName), array( $c )); 
    	} else {
    		$select->from(array( 'cm' => $this->_tableName), array('n.id', 'n.title', 'n.path', 'n.parent', 'dateCreate', 'html', 'dateShow', 'files', 'photos')); 		  
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
		       
        if($sort == 'title')  {
        	$sort = 'n.' . $sort;
        } 
        
        if(is_array($root)){
        	$childrenIds = $root;
        } elseif ($root){
        	$childrenIds = $this->tree->getNodeChildrenIds($root);
        } 
       	if(count($childrenIds)){
        	$ids = "" . implode(", ", $childrenIds) . "";  
        	$select->where("n.id IN($ids)");    
    	} 
    			 		
		$select->where('dateShow <= ?', new Zend_Db_Expr('NOW()'));	
		
		$select->where('state = ?', 'PUBLISHED'); 
		$select->where('c_type = ?', 'content_Article');  
		
		// e($select->__toString());     
		$sortType = $sortType?$sortType:'Asc';
		$select->order($sort . ' ' . $sortType); 
		$select->order('n.id DESC');   
		$select->limit($limitCount, $limitStart);  
		
		return array($select, $bind); 
    }   
    
    public function getArticles( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $root = 0)   
    {
		list($select, $bind) = $this->initSearch($sort, $sortType, $limitStart, $limitCount, $root);		
		return $this->db->fetchAll($select, $bind);	 	 
    }   
     
    public function getArticlesCout( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $root = 0)   
    {  
    	list($select, $bind) = $this->initSearch($sort, $sortType, $limitStart, $limitCount, $root, true); 		
		return $this->db->fetchOne($select, $bind);	  	  
    }   
    
    function  getArticlesAsNodes($tree, $articles){  
    	$nodes = array();
    	foreach ($articles as $article){ 
    		$nodes[] = $tree->getNodeById($article['id']);
    	}
    	return $nodes; 
    }
    
    function addViewHit($nodeId){
    	$fav = $this->db->fetchOne("SELECT views from `" . $this->_tableNameStats ."` WHERE nodeId=:n", array('n' => $nodeId));
		if(!$fav){
			$this->db->insert(
				$this->_tableNameStats,
				array(
					'nodeId' => $nodeId,
					'views' => 1
				)
			);
		} else {
			$where = $this->db->quoteInto('nodeId = ?', $nodeId);
			$this->db->update(
				$this->_tableNameStats,
				array( 'views' => new Zend_Db_Expr('views + 1')),
				$where
			);
		}
    }
    
    function getViews($nodeId){
    	return $this->db->fetchOne("SELECT views from `" . $this->_tableNameStats ."` WHERE nodeId=:n", array('n' => $nodeId));
    }   
    
    function getNewestArticlePhotos($limit = 6){ 
    	$x= 1;
    	$newestPhotos = array(); 
    	
    	$articlesNodes = $this->getArticles('dateShow', 'Desc', 0, 5, 0,  0, false);
    	$article = new content_Article();
    	
    	foreach ($articlesNodes as $a){
    		$article->getPropertyByName('photos')->value = $a['photos'];
    		$aPhotos = $article->getFilesNames('photos');
    		foreach ($aPhotos as $path => $title){
    			list($nodeId, $n) = content_SFSFile::parseSFSPath($path);
    			
				$newestPhotos[$path] = array(
					'nodeId' => $nodeId,
					'name' => $n, 
					'title' => $title,
					'path' => $a['path'],
				);  
				 
    			if($x >= $limit){
    				break 2;
    			} 
    			
    			$x++;
    		}
    	}
    	
    	return $newestPhotos; 
    }  
     
    function getTopArticles($limit = 6)
    {
    	$article = new content_Article();
    	$topArticles = $this->getArticles('dateShow', 'Desc', 0, $limit, 3451 );
    	foreach ($topArticles as $i=>$a){
    		$article->getPropertyByName('photos')->value = $a['photos'];
    		$aPhotos = $article->getFilesNames('photos');
    		$topArticles[$i]['photos']= helper_FrontEnd::getPhoto('photos',$article,$article->fotoThumbName,  $article->fotoFullName) ;  
    	}
    	
    	return $topArticles;	
    }
    
    function getPreviewPhotos($limit = 6)
    {
    	
    }
}