<?php

class module_Tipy 
{   
	function __construct($domain = ''){
		$this->db =  Zend_Registry::getInstance()->db;
		$this->_tableName = 'content_Recept';
	}

	public function initSearch( $parent, $sort = 'dateShow', $sortType = 'Asc', $limitStart = 0, $limitCount = 5)    
    {
    	$select =  $this->db->select(); 
		$bind = array(); 
		 
		if($count){    		
    		$c =  new Zend_Db_Expr("count('*')"); 
    		$select->from(array('cm' => $this->_tableName), array( $c ));  
    	} else {
    		$select->from(array( 'cm' => $this->_tableName), array('n.id', 'n.title', 'n.path', 'n.parent', 'dateCreate', 'html',  'photo')); 		  
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
    			 	   
		$select->where('state = ?', 'PUBLISHED');  
		$select->where('parent = ?', $parent); 
		$select->where('c_type = ?', 'content_Recept');  
		
		$select->order($sort . ' ' . $sortType);  
		$select->order('n.id DESC');     
		$select->limit($limitCount, $limitStart);  
		//e($select->__toString());     
		return array($select, $bind); 
    }    
    
    public function getTipy( $parent, $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5)   
    {
		list($select, $bind) = $this->initSearch($parent, $sort, $sortType, $limitStart, $limitCount);		
		return $this->db->fetchAll($select, $bind);	 	 
    }   
    
    
}
?>