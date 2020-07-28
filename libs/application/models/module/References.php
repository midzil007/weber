<?

class module_References {
		
	function __construct(){
		$this->db =  Zend_Registry::getInstance()->db;
		$this->tree =  Zend_Registry::getInstance()->tree;        
        $this->_tableName = 'content_Reference';
	}
	
	/*
	function getVisibleReferences($curentPath){
		$references = array();
		$rNdeId = Zend_Registry::getInstance()->config->instance->referenceNodeId;
		$all = $this->tree->getNodeChildren($rNdeId);
		foreach ($all as $node){
			$r = $node->getPublishedContent();
			if($r){
				$showin = helper_MultiSelect::getMultiSelectValues($r->getPropertyValue('showin'));
				e($showin);
				if(count($showin)){
					
				}
			}
		}
	}
	*/
		
	public function getVisibleReferences($curentPath, $sort = null, $sortType = 'Asc', $limit = 500)
    {
    	//$ids = $this->tree->getNodeParents($this->tree->getNodeByPath($curentPath)->nodeId);
    	$ids = $this->tree->getNodeChildrenIds($this->tree->getNodeByPath($curentPath)->nodeId);    				
    	$select =  $this->db->select();
		$select->from(array( 'cm' => $this->_tableName), array('id', 'n.title', 'n.path', 'n.parent', 'showin', 'obrazky'));

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
       	$select->where('state = ?', 'PUBLISHED');	
				
		$sortType = $sortType?$sortType:'Asc';
		if($sort){
			$select->order($sort . ' ' . $sortType);
		} else {
			$select->order('RAND()');
		}
		$select->limit($limit);	
				
		$all = $this->db->fetchAll($select);		
		
		foreach ($all as $id => $r){
			$selected = helper_MultiSelect::getMultiSelectValues($r['showin']);
			foreach ($ids as $i){
				if(in_array($i, $selected)){
					continue 2;
				}
			}
			unset($all[$id]);
			
		}
		
		return $all;
    }
    
    public function getFooterReferences($limit = 2)
    {			
    	$select =  $this->db->select();
		$select->from(array( 'cm' => $this->_tableName), array('id', 'n.title', 'n.path', 'n.parent', 'showin', 'obrazky'));

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

        $sort = 'dateShow DESC';        
       	$select->where('state = ?', 'PUBLISHED');			
		$select->order($sort);
		$select->limit($limit);	
				
		$all = $this->db->fetchAll($select);		
		return $all;
    }
}
?>