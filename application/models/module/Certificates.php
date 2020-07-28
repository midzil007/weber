<?php

class module_Certificates
{   
	function __construct($domain = ''){
		$this->db =  Zend_Registry::getInstance()->db;
		$this->tree =  Zend_Registry::getInstance()->tree;
		$this->_tableName = 'content_Certificate';
	}
    
	public function initSearch($sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $root, $count = false)    
    {
    	$select =  $this->db->select();
		$bind = array();
		 
		if($count){    		
    		$c =  new Zend_Db_Expr("count('*')"); 
    		$select->from(array('cm' => $this->_tableName), array( $c )); 
    	} else {
    		$select->from(array( 'cm' => $this->_tableName), array('n.id', 'n.title', 'n.path', 'n.parent', 'dateCreate', 'html', 'photo')); 		  
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
        
//        if(is_array($root)){
//        	$childrenIds = $root;
//        } elseif ($root){
//        	$childrenIds = $this->tree->getNodeChildrenIds($root);
//        } 
//       	if(count($childrenIds)){
//        	$ids = "" . implode(", ", $childrenIds) . "";  
//        	$select->where("n.id IN($ids)");    
//    	} 
    			 		
		
		$select->where('state = ?', 'PUBLISHED'); 
		$select->where('c_type = ?', 'content_Certificate');      
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
}