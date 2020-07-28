<?

class Tree
{    
	private  $treeStructure = array();
	private  $treeStructureSimple = array();
	private  $folderArray = array();
	private  $treeStructureJSON = array();
   
    
    public function __construct($config = array())
    {
    	$this->adapter =  Zend_Registry::getInstance()->db;
    	$this->_name = 'Nodes';        
    	$this->json = new Services_JSON();
    }
    
    // deprecated
    public function saveAll(){
    	$this->save(1);
		$this->save(3,'help');
		$this->save(4,'sysPages');
		$this->save(99,'intranet');
    }
	
	
    
    public function save($tree='structure'){
    	return;
    	$reg = Zend_Registry::getInstance();
    	$this->prefix = '';
    	if($tree == 'help' || $tree == 'helpFull'){
    		$this->_save('help'); 
    		$this->_save('helpFull', true);
    	} if($tree == 'intranet'){
    		$this->_save($tree);
    		    		
    	} else {    		
    		if($tree == 'structure' || $tree == 'sysPages'){
    			if($reg->languages->isMultiLanguage){
	    			$curentLang = $reg->languages->language;
	    			foreach ($reg->languages->languagePrefixMap as $ident => $prefix){
	    				$reg->languages->language = $ident;
	    				$this->prefix = $prefix;
	    				$this->_save($tree);
	    			}
	    			$reg->languages->language = $curentLang;
    			} else {
    				$this->_save($tree);
    			}
    		} else {
    			$this->_save($tree);
    		}
    		
    	}
    }
    
    public function _save($tree='structure', $encodeItems = false, $intranetTree = false, $vybor = ''){
    	return;  
    	if(!$tree){ return; }
    	$reg = Zend_Registry::getInstance();
    	$nodeId = $reg->config->treeNodeIdMap->$tree;
    	//$this->getTree(1,true);
    	$rootNode = $this->getNodeById($nodeId);
    	if($encodeItems){
    		$get = 'BOTH'; 
    	} else {
    		$get = 'FOLDER'; 
    	}
    	$children = $this->getNodeChildren($nodeId, $get, $rootNode->sort, true );
    	if($intranetTree){
    		if($vybor == 'RR'){
    			$path = '/intranet/soubory-ke-stazeni---vybor-rr';
    			$tree = 'intranetRR';
    		} elseif ($vybor == 'MV') {
    			$path = '/intranet/soubory-ke-stazeni---vybor-mv';
    			$tree = 'intranetMV';
    		} elseif ($vybor == 'URR') {
    			$path = '/intranet/soubory-ke-stazeni';
    			$tree = 'intranetURR';
    		}
    		foreach ($children as $i => $child){
    			if($child->path != $path){
    				unset($children[$i]);
    			}
    		}
    	}
    	$rootNode->children = $children;
    	//pr($rootNode);
    	
    	$this->encode($rootNode, $encodeItems);    
    	if($reg->languages->isMultiLanguage){
    		Utils::writeToFile($this->treeStructureJSON, $reg->config->dataRoot . '/tree/' . $this->prefix . $reg->config->tree->{$tree});
    	} else {
    		Utils::writeToFile($this->treeStructureJSON, Zend_Registry::getInstance()->config->tree->{$tree});
    	}
    	
    }
    /*
    public function saveFileTree(){
    	//$this->getTree(2,true);
    	$rootNode = $this->getNodeById(2);
    	$rootNode->children = $this->getNodeChildren(2, 'FOLDER', $rootNode->sort, true );
    	
    	$this->encode($rootNode);    	
    	Utils::writeToFile($this->treeStructureJSON, $this->context->config->tree->files);
    }
    */
    
    /* ENCODE 2 JSON */ 
    public function encode($rootNode, $encodeItems){    	
    	$this->JsonTree = array(
			'identifier' => 'nodeId', 
			'label' => 'name', 
			'items' => array()
		);
		$this->encodeTree($rootNode, $encodeItems);
    	$this->treeStructureJSON = $this->json->encode(($this->JsonTree));
    
    }
    
    public function encodeTree($rootNode, $encodeItems){     
    	
    	$chilren = array();   
    	if(count($rootNode->children)){
	    	foreach ($rootNode->children as $child){
	    		if($child->type == 'FOLDER' || $encodeItems){
		    		$chilren[] = array('_reference' => $child->nodeId);	 
	    		}
	    	}
    	}
    	
    	$aa = array(
    		'name' => $rootNode->title,
			'type' => $rootNode->type,
			'nodeId' => $rootNode->nodeId
    	);	
    	
    	if(count($chilren)){
    		$aa['children'] = $chilren;
    	}
    	
    	$this->JsonTree['items'][] = $aa;
    	
    	if(count($rootNode->children))
	    	foreach ($rootNode->children as $child){    		
	    		if($child->type == 'FOLDER' || $encodeItems)
	    			$this->encodeTree($child, $encodeItems);
	    	}	
    }
    
    function getNodeChildren($nodeId, $type = 'ITEM', $sort = false, $deep = false, $setPublishedContent = false, $count = -1, $quotedWhere = '', $limmit = 3000 ) {
		$reg = Zend_Registry::getInstance();
		
    	$sort = $sort?$sort:$reg->config->defaultNodeSort;
		if($count = 0){
			$this->tempCount = 0;
		}
			
		if($type == 'BOTH'){ 
			$nodetype = 'TREE';
			$comparison = '!=';
		} else {
			$nodetype = $type;
			$comparison = '=';
		} 
		
		$limmit = ' LIMIT ' . $limmit;    
				
		
		if($this->cache && !$this->cache->isAdmin){ 
			$ident = $this->cache->identificator . "children_" . $nodeId . '_' . $type; 			
			if($this->cache->test($ident) === false ){
				$r = $this->adapter->fetchAssoc(
					'SELECT id, parent, type, sort, title, object FROM Nodes WHERE parent = ? AND type ' . $comparison . ' ? ' . $quotedWhere . $limmit,array($nodeId, $nodetype)
				);	
				$this->cache->save($r, $ident, array(), 45);  
			} else {
				$r = $this->cache->load($ident);   
			} 
		} else {  
			$r = $this->adapter->fetchAssoc(
				'SELECT id, parent, type, sort, title, object FROM Nodes WHERE parent = ? AND type ' . $comparison . ' ? ' . $quotedWhere . $limmit,array($nodeId, $nodetype)
			);	
		}
		 
		
		$nodes = array();
		if(count($r)){
			foreach ($r as $data) {
				if($this->cache){
					$node = $this->getNodeById($data['id']);
				} else {
					$node = Node::initFromDatabase(unserialize($data['object']));
				}
				//e($node->type);
				//pr($node);
				if(!$node){
					pr($data);
					continue;
				}
				
				if(!$node->contents){   
					$node->contents = $node->getContents();
				}
				
				
				if(!$node->showInCurentLanguage()){
					continue;
				} 
				
				
				if($setPublishedContent){ // ????
					$node->contents[] = $node->getPublishedContent();
				} 
				
				if($count > -1){
					$this->tempCount++;
				}
				
				if($deep){
					$node->children = $this->getNodeChildren($data['id'], $type, $data['sort'], $deep, $setPublishedContent, $count, $quotedWhere );
				}				
				$nodes[] = $node;					
			}
		}
		usort($nodes, array($this, "cmp_$sort"));
		return  $nodes;
	}
	
	
	function getNodesBy( $order, $orderType = 'DESC', $limit = 10, $onlyPublished = false, $notPublished = false, $files = true, $onlyFuture = false ) {
		$count = 0;
		$max = $limit;
		$limit += 20;  
			
		$select = $this->adapter->select();
		$select->from('Nodes', array( 'object', 'dateModif'));
		$select->order($order . ' ' . $orderType);
		$select->limit(200, 0);       
		$r = $this->adapter->fetchAssoc($select);	  
		$nodes = array();
		$today = date('Y-m-d');		  
		
		if(count($r)){
			foreach ($r as $data) {
				$node = Node::initFromDatabase(unserialize($data['object']));					
				$node->contents = $node->getContents();
				$content = $node->getPublishedContent();
				if($onlyPublished){										
					if(!$content){
						continue;
					}					
				}
				
				if($onlyFuture){
					if(!$content){
						continue; 
					}	
					 
					if($date = $content->getPropertyValue('dateShow')){ 
						if(strcasecmp($today, $date) > 0){     
							continue;   
						}
					} else {
						continue;
					}
				}
				
				if($notPublished){										
					if($content || count($node->contents) == 0){ 
						continue; 
					}					
				}
				
				if($files != true){	  							 
					if($content->_name == 'content_SFSFile'){
						continue;  
					}					
				}
				
				$nodes[] = $node;		
				$count++;	
				if($count >= $max){
					break;
				}
			}
		}
		return  $nodes;
	}
	
	function getNodesByContentType( $ctype, $limit = 9999, $onlyPublished = true, $order = 'n_id', $orderType = 'desc' ) {
		$count = 0; 
		$max = $limit;
		$limit += 20;
			 
		$select = $this->adapter->select();
		$select->from('NodesContents', array( 'n_id'));
		$select->where('c_type = ?', $ctype);
		$select->order($order . ' ' . $orderType);
		
		$map = $this->adapter->fetchAssoc($select);	 
				/*
		$select = $this->adapter->select();
		$select->from('Nodes', array( 'object', 'dateModif'));
		$select->where('id IN (?)', implode(", ", array_keys($map)));
		e($select->__toString());
		*/
				
		if(count($map)){
			$nodesRes = $this->adapter->fetchAssoc("
				SELECT `Nodes`.`object` , `Nodes`.`dateModif`
				FROM `Nodes`
				WHERE (
				id
				IN ( " .  implode(", ", array_keys($map)) ." )
				)
			");	
		}
		
		$nodes = array();
		if(count($nodesRes)){
			foreach ($nodesRes as $data) {		
				$startTime = Utils::getMicrotime();
				$node = unserialize($data['object']);	
				
				$node->contents = $node->getContents();		
				if($onlyPublished){										
					if(!$node->hasPublishedContent()){
						continue;
					}					
				}
				
				$nodes[] = $node;		
				$count++;	
				if($count >= $max){
					break;
				} 
			} 
		} 
		return  $nodes; 
	}
	/*
	function getSimpleTree($parent, $level = 0) {
		$r = $this->adapter->fetchAssoc('SELECT id, parent, type, title FROM Nodes WHERE parent=?',array($parent));		
		$this->treeStructureSimple[$parent] = array();
		if(count($r)){
			foreach ($r as $data) {		
				$this->treeStructureSimple[$parent][] = array(
					$data,
					'children' => $this->getSimpleTree($data['id'], $level++)
				);				
			}
		}
	}*/
	
	function getTree($id, $deep = false ) {
		$node = $this->adapter->fetchAssoc('SELECT id, parent, type, title FROM Nodes WHERE id=?',array($id));			
		$this->treeStructureSimple = $this->getSimpleTree(current($node), $deep ? -1 : 1);
		return  $this->treeStructureSimple;
	}
	
	function hasChildren($id, $type = 'FOLDER'){     
		return $this->adapter->fetchOne("SELECT id FROM Nodes WHERE parent =? AND type = '$type'", array($id));			
	}
	

	private function getSimpleTree($node, $step = -1, $level = 0) {
		/*
		if(($node['type']=='FOLDER' || $node['type']=='TREE')){ // select pro nadrezene
			if($node['type']=='TREE'){
				$parentSelectSpace = '';
			} else {
				$parentSelectSpace = str_repeat('&ensp;',($level)) . '';
			}
			$this->folderArray[$node['id']] = $parentSelectSpace . $node['title'];
		}
		*/
		$tree = array(
			'id' => $node['id'],
			'parentId' => $node['parent'],
			'title' => $node['title'], 
			'type' => $node['type'],
			'children' => array());		
		if ($step > 0 || $step < 0){
			//pr($tree);
			$subnodes = $this->adapter->fetchAssoc('SELECT id, parent, type, title FROM Nodes WHERE parent=?',array($node['id']));
			foreach ($subnodes as $child){
				$tree['children'][] = $this->getSimpleTree($child, $step == -1 ? $step : $step - 1, $level++);
			}
		}
		return $tree;
	}	  
	
	function getParentSelect($nodeId = 1, $prefix = '-', $addId = true){
		$this->folderArray = array();
		   
		$rootNode = $this->getNodeById($nodeId); 
    	$rootNode->children = $this->getNodeChildren($nodeId, 'FOLDER', $rootNode->sort, true );
    	//pr($rootNode);
    	$this->generateParentSelect($rootNode, 1, $prefix, $addId);  
		return  $this->folderArray;
	}
	
	public function generateParentSelect($rootNode, $level = 1, $prefix = '-', $addId = true){           	
    	$chilren = array();   
    	  
    	if($node['type']=='TREE' || $node['type']=='FILETREE'){
			$parentSelectSpace = '';
		} else {  
			$parentSelectSpace = str_repeat($prefix,($level)) . '';
		}
		$t = $parentSelectSpace . $rootNode->title . $prefix;  
		if($addId){
			 $t.= ' (' . $rootNode->nodeId . ')';
		}    
		$this->folderArray[$rootNode->nodeId] = $t;
		
    	if(count($rootNode->children)){
	    	foreach ($rootNode->children as $child){    		
	    		if($child->type == 'FOLDER')
	    			$this->generateParentSelect($child, $level+1, $prefix, $addId);
	    	}
		}    	
			
    }
    
    
    function getNodesAsSelect($nodesParentNodeId, $type = 'ITEM', $sort = false){
    	$nodes = $this->getNodeChildren($nodesParentNodeId, $type, $sort);
    	$options = array();
    	foreach ($nodes as $node){
    		$options[$node->nodeId] = $node->title;
    	}
    	return $options;
    }
	
    function getNodesAsSelect2Level($nodesParentNodeId, $type = 'ITEM', $sort = false){
    	$nodes = $this->getNodeChildren($nodesParentNodeId, $type, $sort);
    	$options = array();
    	foreach ($nodes as $node){
    		$chDepp = array();
    		$chDepp['title'] = $node->title;
    		
    		$ch = $node->getChildren('BOTH');
    		foreach ($ch as $n){ 
    			$chDepp[$n->nodeId] = $n->title;
    		}
    		
    		$options[$node->nodeId] = $chDepp;
    	}
    	return $options; 
    }
	
    function getNodesSelectByParentNodeId($nodesParentNodeId, $type = 'ITEM', $contentName, $sort = false, $sortType = false, $level = 0){   
    	$nodes = $this->getNodeChildrenSQL($nodesParentNodeId, $contentName, $type, $sort);   
    	// e($nodes);      
    	 
    	$options = array();
    	foreach ($nodes as $node){
    		$options[$node->nodeId] = $node->title;  
    	}
    	return $options;
    }
    
    function getNodeChildrenSQL($nodesParentNodeId, $contentType, $type = 'ITEM', $sort = false, $sortType = false){
    	$select =  $this->adapter->select();
		$bind = array();
		 
		$select->from(array( 'cm' => $contentType), array('n.id', 'n.object')); 		  
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
        
        $select->where('n.type = ?', $type);	 
        $select->where('cm.state = ?', 'PUBLISHED');	  
        $select->where('n.parent = ?', $nodesParentNodeId);	
        		       
        if($sort == 'title')  {
        	$sort = 'n.' . $sort;
        }  
        
        if($sort){ 
        	$sortType = $sortType?$sortType:'Asc';         
			$select->order($sort . ' ' . $sortType); 
        }
		//$select->limit($limitCount, $limitStart);  
		
		$nodes = array();
		$all = $this->adapter->fetchAll($select);
		foreach ($all as $nodeObj){
			$nodes[$nodeObj['id']] = $this->getNodeById($nodeObj['id'], $nodeObj['object']);  
		} 
		return $nodes;	 	  
    }
    
    /* pro potreby stromu */
    function  getNodeIdPath($nodeId, $isSFS = false){
    	
    	// rozbalime i ten na ktery klikne
    	$node = $this->getNodeById($nodeId);
    	if($node){
	    	// $children = $node->getChildren('FOLDER');
	    	if(count($children)){
	    		$child = current($children);
	    		$nodeId = $child->nodeId;
	    	}
    	}
    	$inTree = true;
    	$id = $nodeId;
    	$path = array();
    	do {
    		$parent = $this->adapter->fetchOne('SELECT parent FROM Nodes WHERE id=?',array($id));
    		if($parent > 0){
    			$path[] = $id = $parent;    			
    		} else { 
    			$inTree = false;
    		} 
    	} while ($inTree);  
    	 
    	if(count($path)){
    		if($isSFS){ 
    			return "['node_".implode("', 'node_",array_reverse($path)) ."', 'node_".$nodeId .  "']"; // 
    		} else {
    			return "['node_".implode("', 'node_",array_reverse($path))."', 'node_".$nodeId .  "']"; //   
    		} 
    		  
    	} else {
    		return "['node_".$nodeId .  "']";      
    	}
    	
    	
    	// return "['0', '".implode("', '",array_reverse($path)). "', '".$nodeId . "']"; 
    } 
     
    function  getNodeParents($nodeId){
    	
    	// rozbalime i ten na ktery klikne
    	
    	$inTree = true;
    	$id = $nodeId;
    	$path = array();
    	do {
    		$parent = $this->adapter->fetchOne('SELECT parent FROM Nodes WHERE id=?',array($id));
    		if($parent > 0){
    			$path[] = $id = $parent;    			
    		} else {
    			$inTree = false;
    		}
    	} while ($inTree);
    	return '/'.implode('/',array_reverse($path)).'/'.$nodeId;
    }
    
    
    function  getNodeChildrenIds($nodeId, $ids = array(), $type = 'BOTH'){    
    	
    	$ids[] = $nodeId;
    	
    	if($type == 'BOTH'){
    		$w = '';
    	} elseif ($type == 'ITEM'){
    		$w = "AND type = 'ITEM'";
    	} elseif ($type == 'FOLDER'){ 
    		$w = "AND type = 'FOLDER'"; 
    	}
    			
		$nid = $this->adapter->fetchAll('SELECT id, type FROM Nodes WHERE parent=?' . $w, array($nodeId));
		if(count($nid)){
			foreach ($nid as $data){
				$id = $data['id']; 
				if($data['type'] != 'ITEM'){
					$ids = $this->getNodeChildrenIds($id, $ids, $type); 
				} else {
					$ids[] = $id; 
				}
			} 
		} 
    	return $ids;
    }
    
    /*
    public function encodeNode($node){
    	return array(
			'name' => $node->title,
			'type' => $node->type,
			'nodeId' => $node->nodeId,
			'children' => array()
		);
    }
    
    public function test(){}
	*/
	
	function addNode($node, $inFileBranch = false, $save = true) {
		$reg = Zend_Registry::getInstance();
		
		// defaultni uzivatelske razeni podle ID
		$node->orderValuation = $node->nodeId;
		
		$langs = array();
		if(count($node->showInLanguages)){
			foreach ($node->showInLanguages as $lang => $ison){
				if($ison){
					$langs[] = $lang;
				}
			}
		} 
    	
		$insertData = array(
			'id' => $node->nodeId, 
			'parent' => $node->parentId, 
			'type' => $node->type, 
			'path' => $node->path, 
			'template' => $node->template, 
			'sort' => $node->sort, 
		
			'languages' => implode(',', $langs),     
			'title' => $node->title, 
			'description' => $node->description, 
			'dateModif' => $node->dateModif, 
			'timeCondition' => serialize($node->timeCondition), 
			'orderValuation' => $node->orderValuation, 
			'object' => serialize($node)
		);
		
		$insertData = $node->checkLangCols($insertData);
					
    	$this->adapter->insert('Nodes', $insertData);
		
		if($save){
			$this->save($node->getSupertype());		
		}
		//return $node->nodeId;
	}
	
	function pareNodeAndContent($nodeId, $contentId, $contentName, $increaseMaxContentId = true) {
		
		$this->adapter->query("
    		INSERT INTO `NodesContents` ( `n_id` , `c_id` , `c_type` )
			VALUES ( ?, ?, ? );				
			",
			array($nodeId, $contentId, $contentName)
		);
		/*
		if($increaseMaxContentId){
			$contentId = $contentId + 1;
			$this->adapter->query("				
				INSERT INTO `maxContentId` ( `nextId` )
				VALUES (
					$contentId
				);    
				"
				 
			);
		}  	*/	
	}
	
	function updateNode($node, $save = true) {	  
		$node->save($this->adapter, $this, $save); 
	}
	
	
	function removeNode($nodeid, $saveTree = true) {
		if($this->cache){
			$ident = $this->cache->identificator . "node_" .  $node->nodeId;
			$this->cache->remove($ident); 
		}
		 
		$nodeToRemove = $this->getNodeById($nodeid);
		
		  	
		if($nodeToRemove){ 		  
			$superType = $nodeToRemove->getSupertype();
			
			
			
			
			$nodeToRemoveChildren = array_merge($nodeToRemove->getChildren('FOLDER'), $nodeToRemove->getChildren('ITEM'));
			 
			/* SMAZU VSECHNY POD */  
			if(count($nodeToRemoveChildren)){  
				foreach ($nodeToRemoveChildren as $child){
					$this->removeNode($child->nodeId, false);
				}
			} 
							
			/* DELETE contents */
			if(!$nodeToRemove->contents){
				$nodeToRemove->contents = $nodeToRemove->getContents();
			}		
			foreach ($nodeToRemove->contents as $content){
				if(method_exists($content, 'onDelete')){
					$content->onDelete();
				}
				$this->adapter->query('
					DELETE FROM `'.$content->_name.'` WHERE id=?;
					',
					array( $content->id ) 
				);
			}
					
			// u slozek smazu adresar
			if($nodeToRemove->getSupertype() == 'files'){
				$dir = Zend_Registry::getInstance()->config->fsRoot . $nodeToRemove->path;
				if(is_dir($dir)){
					rmdir($dir);
				}			
			}		
					
			$this->adapter->query('DELETE FROM `NodesContents` WHERE `n_id` = ?', array($nodeid));	
					
			//delete node
			$this->adapter->query('
				DELETE FROM `Nodes` WHERE id=?;
				',
				array( $nodeid )
			);
		}  	
		
		if($saveTree && $superType) { // pri rekurzi dolu neukladame, az pak nejdnou
			$this->save($superType);		
		}
		
		return $nodeid;
	}
	
	function getNodeSimple($id) {		 
		return (object) $this->adapter->fetchRow('SELECT id, parent, type, path, title, created, dateModif FROM Nodes WHERE id=?',$id); 		
	}
	
	function getNodeById($id, $object = false, $initContents = true) {		 
		if(is_int($id) && $this->cache  && !$this->cache->isAdmin){ 
			// e($id); 
			$ident = $this->cache->identificator . "node_" . $id; 
			
			 
			if($this->cache->test($ident) === false ){ 
				if(!$object){
					$object = $this->adapter->fetchOne('SELECT object FROM Nodes WHERE id=?',array($id));
				}
				$node = Node::initFromDatabase(unserialize($object));		
				if($node){
					$node->contents = $node->getContents();
				}
				/*
				if($_SERVER['REMOTE_ADDR'] == '217.195.175.130'){
					e('SAVESAVEEEE');
				}
				*/ 
				$this->cache->save($node, $ident);
			} else {
				$node = $this->cache->load($ident);  
				 
				if((!$node || /*!is_array($node->contents) ||*/ !count($node->contents))){   
					if(!$object){
						$object = $this->adapter->fetchOne('SELECT object FROM Nodes WHERE id=?',array($id));
					}
					$node = Node::initFromDatabase(unserialize($object));		
					if($node){
						$node->contents = $node->getContents();
					}
					$this->cache->save($node, $ident); 
				}
			}
		} else {
			if(!$object){
				$object = $this->adapter->fetchOne('SELECT object FROM Nodes WHERE id=?',array($id));
			} 
			$node = Node::initFromDatabase(unserialize($object));	
			if($node){ 
				$node->contents = $node->getContents();
			}
		} 
		 
			
		return $node;
	}
	
	function getNodeByPath($path, $inFileBranch = false) {
		$path = str_replace('//', '/', $path);
		if(!$inFileBranch){
			if(strpos($path, '.'))		{
				$path = current(explode('.', $path));
			}
		}
		/*
		if($_SERVER['REMOTE_ADDR'] == '217.195.175.130'){
					e('PATH');
				}
			*/	
		$reg = Zend_Registry::getInstance();
		if($reg->languages->isMultiLanguage){
			$pathName = $reg->languages->fullLangPrefix . 'path';
		} else {
			$pathName = 'path';
		}
				
		$ident = 'node_' . $pathName . $path;
		$ident = str_replace(array('/', '_', '-'), '', $ident);
		//e($ident);
		/*
		if($this->cache){
			if($this->cache->test($ident) === false){
				$node = Node::initFromDatabase(unserialize($this->adapter->fetchOne("SELECT object FROM Nodes WHERE $pathName=? OR path=? ORDER BY id Asc",array($path, $path))));	
				if($node){
					$node->contents = $node->getContents();
				}
				$this->cache->save($node, $ident);
			} else {
								
				$node = $this->cache->load($ident);
			}
		} else {
			$node = Node::initFromDatabase(unserialize($this->adapter->fetchOne("SELECT object FROM Nodes WHERE $pathName=? OR path=? ORDER BY id Asc",array($path, $path))));	
			if($node){
				$node->contents = $node->getContents();
			}
		} 
		*/
		  
		$id = $this->adapter->fetchOne("SELECT id FROM Nodes WHERE $pathName=? OR path=? ORDER BY id Asc",array($path, $path));	  
		/* 
		$node = Node::initFromDatabase(unserialize($this->adapter->fetchOne("SELECT object FROM Nodes WHERE $pathName=? OR path=? ORDER BY id Asc",array($path, $path))));	
		if($node){
			$node->contents = $node->getContents();
		}		
		return $node; 
		*/ 
		if(!$id)
		{
			$id = $this->adapter->fetchOne("SELECT nodeId FROM NodesPathHistory WHERE path = ? ORDER BY dateCreate desc ",$path);	
			if($id>0){
			$node = $this->getNodeById($id);

				header("HTTP/1.1 301 Moved Permanently");
				header("Location: ".$node->path);
				header("Connection: close");
			}  
		}
		return $this->getNodeById($id);
	}
	
	function getNodesWithContentType($contentType, $onlyPublished = false) {
		$nodesWithCType = array();
		$nodes = $this->adapter->fetchAll("SELECT n_id FROM `NodesContents` WHERE `c_type`=?", array($contentType));
		foreach ($nodes as $n){		
			$node = $this->getNodeById($n['n_id']);	
			if($onlyPublished){
				if(!$node->hasPublishedContent()){
					continue;
				}
			}
			$nodesWithCType[$n['n_id']] = $node;
		}
		
		return $nodesWithCType;
	}
	
	function getContentsNode($contentId) {
		
		$id = $this->adapter->fetchOne("
    		SELECT n_id FROM `NodesContents` WHERE c_id = ?;				
			",
			array($contentId)
		);
		return $this->getNodeById($id);
	}
	
	function getContentsNodeId($contentId) {
	
		$id = $this->adapter->fetchOne("
      SELECT n_id FROM `NodesContents` WHERE c_id = ?;
   ",
				array($contentId)
		);
		return $id;
	}
		
	// porovnávací funkce pro třízení poduzlů
	function cmp_dateCreate($node1, $node2) {
		return strcasecmp($node1->dateCreate, $node2->dateCreate);
	}
		
	function cmp_dateCreateDesc($node1, $node2) {
		return $this->cmp_dateCreate($node1, $node2) * -1;
	}
	
	function cmp_dateModif($node1, $node2) {		
		return strcasecmp($node1->dateModif, $node2->dateModif);
	}
		
	function cmp_dateModifDesc($node1, $node2) {		
		return $this->cmp_dateModif($node1, $node2) * -1;
	}
	
	function cmp_levelId($node1, $node2) {
		
		return $node1->orderValuation - $node2->orderValuation;
	}

	function cmp_levelIdDesc($node1, $node2) {
		return $this->cmp_levelId($node1, $node2) * -1;
	}
	
	function cmp_title($node1, $node2) {
		//e($node1->title);		
		//echo iconv("UTF-8", "ISO-8859-2", "This is a test.");
		return strcoll( iconv("UTF-8", "ISO-8859-2",$node1->title),  iconv("UTF-8", "ISO-8859-2",$node2->title));
	}
	
	function cmp_titleDesc($node1, $node2) {
		return $this->cmp_title($node1, $node2) * -1;
	}
	
	
	
/*
	
	function cmp_timeConditionDesc($node1, $node2) {
		return $this->cmp_timeCondition($node1, $node2) * -1;
	}
	*/
			
			
	function cmp_publishedContent($node1, $node2) {
		$publishedContent1 = $node1->getPublishedContent();
		
		if(!$publishedContent1){
			$publishedContent1 = $node1->contents[array_pop(array_keys(($node1->contents)))];
		}
		
		$publishedContent2 = $node2->getPublishedContent();
		if(!$publishedContent2){
			$publishedContent2 = $node2->contents[array_pop(array_keys(($node2->contents)))];
		}
		
		return strcasecmp($publishedContent1->userName, $publishedContent2->userName);
		
	}
	
	function cmp_publishedContentDesc($node1, $node2) {
		return $this->cmp_publishedContent($node1, $node2) * -1;
	}	
	
	/**
	 * Porovná podle data zveřejnění - pokud existuje
	 *
	 * @param Node $node1
	 * @param Node $node2
	 * @return int
	 */
	function cmp_dateShow($node1, $node2) {
		$c1 = $node1->getTheRightContent();
		$c2 = $node2->getTheRightContent();
		if(!$c1 || !$c2){
			return 0;
		}
		$date1 = $c1->getPropertyValue('dateShow');
		$date2 = $c2->getPropertyValue('dateShow');
		if(!$node2){
			e($node2);
		}
		if($date1 && $date2 && $date1 != $date2){
			return strcasecmp($date1, $date2);	
		} else {
			return $this->cmp_dateCreate($node1, $node2);
		}		
	}
	
	function cmp_dateShowDesc($node1, $node2) {
		return $this->cmp_dateShow($node1, $node2) * -1;
	}
}
?>
