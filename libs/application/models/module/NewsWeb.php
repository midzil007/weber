<?php

class module_NewsWeb
{   
	function __construct($domain = ''){
		$this->db =  Zend_Registry::getInstance()->db;
		$this->_tableName = 'content_NewsWithPhoto';
	}

	public function initSearch( $sort = 'dateShow', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $date = false)    
    {
    	$select =  $this->db->select(); 
		$bind = array(); 
		 
		if($count){    		
    		$c =  new Zend_Db_Expr("count('*')"); 
    		$select->from(array('cm' => $this->_tableName), array( $c ));  
    	} else {
    		$select->from(array( 'cm' => $this->_tableName), array('n.id', 'n.title', 'n.path', 'n.parent', 'dateCreate', 'html', 'dateShow', 'image')); 		  
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

    	if($date){
    		$select->where('dateShow = ?', $date);	  
    	} else {
			$select->where('dateShow <= ?', new Zend_Db_Expr('NOW()'));	
    	}
			
		$select->where('state = ?', 'PUBLISHED'); 
		$select->where('c_type = ?', 'content_NewsWithPhoto');  
		
		$select->order($sort . ' ' . $sortType);  
		$select->order('n.id DESC');     
		$select->limit($limitCount, $limitStart);  
		//e($select->__toString());     
		return array($select, $bind); 
    }   
    
    public function getNews( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $day = false)   
    {
		list($select, $bind) = $this->initSearch($sort, $sortType, $limitStart, $limitCount, $day);		
		return $this->db->fetchAll($select, $bind);	 	 
    }    
    
    
}
?>