<?php

class module_SearchProducts
{   
	function __construct($domain = ''){
		$this->db =  Zend_Registry::getInstance()->db;
		$this->tree =  Zend_Registry::getInstance()->tree;
		$this->_tableName = 'content_Product';
		$this->_tableNameCache = 'cacheSearch';
		$this->_colorProducts ='module_eshop_colorProducts';
		$this->_variants = 'module_eshop_variantProducts';
	} 
    
	
	public function initSearch( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params, $count = false)    
    {
    	$select =  $this->db->select();
		$bind = array();
		
		
   		 if($params['akceVyprodej']){
    			$disc = new Zend_Db_Expr(" 
    					((price2 - price) /  (price2/100)) as discount 
    		 	");    
    	    } else {
    	    	$disc = new Zend_Db_Expr(" 
    					1 as discount   
    		 	");  
    	    }
    	    
		if($count){    		 
    		$c =  new Zend_Db_Expr("count('*')");  
    		$select->from(array('cm' => $this->_tableName), array( $c,  $disc ));  
    	} else {
    		$select->from(array( 'cm' => $this->_tableName), array($disc, 'cid' => 'cm.id', 'n.id', 'n.title', 'n.path', 'n.parent', 'dateModif', 'dateCreate', 'html', 'files', 'photos', 'parent', 'znacka', 'video', 'hmotnost', 'akce', 'souvisejici', 'kod', 'skladem', 'oldid', 'price', 'ean','price2','state')); 		  
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
        
        if(!$params['onlyColors'])
        {
        	$select->join(
			array('cp' => 'module_eshop_colorProducts'),
        	'cp.nodeId = n.id',
        	array('cp.colorId') 
        );
        	
        }
		       
        if($sort == 'title')  {
        	$sort = 'n.' . $sort;
        } 

        if($params['category']){ 
        	
        	if(is_array($params['category'])){
	        	$childrenIds = $params['category'];
	        } else { 
	        	$childrenIds = $this->tree->getNodeChildrenIds($params['category'], array(), 'FOLDER');
	        	$childrenIds[] = $params['category'];
	        }  
	        
	        if(count($childrenIds)){
	        	$w = array();
	        	foreach ($childrenIds as $id){
	        		$w[] = " cm.parent like '%$id%' ";
	        	}
	        	$select->where(implode('OR', $w));    
	    	}  
        }  
        
        
        
        if(count($params['znacka'])){ 
         	$w = array();
        	foreach ($params['znacka'] as $id){
        		$id = intval($id);  
        		$w[] = " cm.znacka = '$id' ";
        	}
         	$select->where(implode('OR', $w)); 
         }

         if($params['new']){
         	unset($params['new']); 
         	//$ppp = $this->getProducts('n.id', 'Desc', 35, 1, $params);
         	//$ppp = current($ppp);    
         	$select->where('cm.akce LIKE ?', '%2%');    
         }
         
    	 if($params['from']){   
         	$select->where('cm.dateCreate  >= ?', $params['from']);  
         }	
          
         if($params['akce']){
         	$select->where('akce LIKE ?', '%1%');  
         }  
		 
		 if($params['trhak']){
         	$select->where('akce LIKE ?', '%3%');  
         }  
		 
		 if($params['vyprodej']){
			$select->where('akce LIKE ?', '%5%');  
		 }
		 
		 if($params['ean']){
         	$select->where('ean = ""');  
         }   
         
    	if($params['skladem']){
         	$select->where('skladem = ?', $params['skladem']) ;  
         }
         
         if($params['nej']){  
         	unset($params['nej']); 
         	//$ppp = $this->getProducts('sold', 'Desc', 150, 1, $params);
         	//$ppp = current($ppp);     
         	//$select->where('cm.id >= ?', $ppp['cid']);   
         	$select->where('akce LIKE ?', '%4%');    
         }  
           
         if($this->inAdmin){
         	
         } else {
         	  
         	$select->where('prodejny = ?', 1);   
         }
         
          
         if($params['search'] != '' && $params['search'] != 'Hledaný výraz' && isset($params['search'])){
			$select->where(' ( title LIKE :u )');
			$bind2 = array(
				'u' => '%' . $params['search'] . '%' 
			); 
			$bind = array_merge($bind, $bind2);  
		}   
        
		// $select->where('dateShow <= ?', new Zend_Db_Expr('NOW()'));	
		
		if($params['state']){  
			$select->where('state = ?', $params['state']); 
		} else { 
			$select->where('state = ?', 'PUBLISHED'); 
		}
		
		$select->where('c_type = ?', $this->_tableName);   
		  
		$sortType = $sortType?$sortType:'Asc';
    	if($params['akceVyprodej']){    
    		$select->having('discount >= 29.9 ');  
			$select->order('discount DESC');    
        }
        
		
		if($sort=='soldPrice'){
			$select->order('price asc');
			$select->order('sold desc');
		}
		else{
			$select->order($sort . ' ' . $sortType); 
		}
		$select->order('n.id DESC');   
		$select->limit($limitCount, $limitStart); 
		e($select->__toString());  
		return array($select, $bind); 
    }   
    
    
   
    public function getColors($parentID)
    {
    	$select = $this->db->select();
    	$select->from(array( 'c' => $this->_colorProducts), array('*'));
    	if($parentID)
    		$select->where('parent = ?',$parentID);
    	$select->group('color');
    	$select->order('color asc');
    	$allColors = $this->db->fetchALL($select);
    	foreach ($allColors as $value) {
    		$ret[$value['color']] = $value['color'];
    	}
    	return $ret;
    }
    
    public function getIdsColors($color)
    {   
    	// uprava url	
    	$color= str_replace('+', ' ', $color);
    	$color= str_replace('%2', '/', $color);
    	$all = $this->db->fetchAll('select nodeId from '.$this->_colorProducts.' where color = ?',$color);
    	foreach ($all as $value) {
    		$colorIds = $value['nodeId'].',';
    	}
    }
    
    public function addToRelations($view)
    {
    	// do cronu relace na colors a varinaty
    	
    	$mProd = new module_Products();
    	$mVarinta = new module_Varianta();
    	$allPRoducts = $mProd->getProducts('title','asc',0,50000);
    	foreach ($allPRoducts as $value) {
    		$product = $mProd->getProductByContentId($value['cid']);
    		for ($i = 1; $i < 16; $i++) {
    			if($product['varianta_'.$i.'_colorId']>0)
    			{
    				$color = $mVarinta->getColorsById($product['varianta_'.$i.'_colorId']);
    				$data = array(
    					'nodeId' => $value['id'],
    					'colorId' => $product['varianta_'.$i.'_colorId'],
    					'color' => $color,
    					'variantId' => $i,
    					'parent' => $value['parent']
    					);
    				$this->db->insert($this->_colorProducts,$data);			
    			}
    		}
    		for ($i = 1; $i < 16; $i++) {
    			if($product['varianta_'.$i.'_nazev']>0)
    			{
    				$color = $mVarinta->getColorsById($product['varianta_'.$i.'_colorId']);
    				$data = array(
    					'nodeId' => $value['id'],
    					'varianta' => $product['varianta_'.$i.'_nazev'],
    					'variantId' => $i,
    					'parent' => $value['parent']
    					);
    				$this->db->insert($this->_variants,$data);			
    			}
    		}
    	//	
    	}
    	
    }
    
    public function getSearchProducts( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params = array())   
    {
		list($select, $bind) = $this->initSearch($sort, $sortType, $limitStart, $limitCount, $params);		
		return $this->db->fetchAll($select, $bind);	 	 
    }   
      
    public function getSearchProductsCout( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params = array())   
    {   
    	list($select, $bind) = $this->initSearch($sort, $sortType, $limitStart, $limitCount, $params, true); 		
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