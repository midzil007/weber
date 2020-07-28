<?

class module_Search
{		
	public $searchString;
	public $options = array(); // podle datumu, autora, nadpisů, fulltext)
	public $tablesToSearch = array();
	public $tableColsToSearch = array();
	public $isAdmin = false;
	public $searchedNodes = array();
	public $validSearchedNodes = array();
	public $_tableSearchHistory = 'module_SearchHistory';
	
	
	function __construct($isAdmin = false, $options = array(), $exclude = array('help', 'helpFull')) {
		$this->db = Zend_Registry::getInstance()->db;
		$this->tree = Zend_Registry::getInstance()->tree;	
		$this->isAdmin = $isAdmin;		
		$this->options = $options;
		$this->exclude = $exclude;
	} 
	
	function saveSearch($ss){
		$this->db->insert(
			$this->_tableSearchHistory,
			array('searched' => $ss)			
		);
		$insertId = $this->db->lastInsertId();
		$this->db->delete(
			$this->_tableSearchHistory,
			" sid < " . max(0, $insertId - 5000)   
		);    
	}
	
	function getLatestSearch($limit = 10){ 
		return $this->db->fetchAll(
			"SELECT distinct `searched` FROM $this->_tableSearchHistory ORDER BY sid DESC LIMIT 0, $limit"
		); 
	}
	
	function performSearch($searchString, $byType = false){
		$this->init($searchString);
		$this->getTablesToSearch();
		$this->getTableColsToSearch();
		
		$this->searchedNodes = $this->searchInTables();
		//pr($this->searchedNodes);
		$this->checkSearchedNodes();
		$searchResults = array();				
		foreach ($this->validSearchedNodes as $n){
			$sType = $n->superType;
 

			if($n->type == 'ITEM' && (strpos($n->path, '/eshop') !== false || strpos($n->path, '/e-shop') !== false)){
				$sType = 'eshop';
			} 

			$sType = $sType?$sType:'structure'; 
 
			$data = array(
				'title' => $n->title,
				'superType' => $sType,
				'nodeId' => $n->nodeId,
				'path'  => $n->path
			);
 

			if($this->isAdmin){		 
				if($byType){
					$searchResults[$sType][] = $data;
				} else {
					$searchResults[] = $data;
				}				
			} else {
				$searchResults[] = $data; 
			}
			
			//superTypeControllerMap
		
			
		}
		return $searchResults;
	}
	
	function init($searchString) {		
		$this->searchString = $searchString;	
		$this->searchStrings = Utils::trimArray(explode(',', $searchString));		
	}
	
	function getTablesToSearch(){
		
		$contentTypes = array();
		$allContentTypes = array_unique(array_merge(
			Zend_Registry::getInstance()->config->contentTypes->toArray(),
			Zend_Registry::getInstance()->config->overviewTypes->toArray()
		));
		foreach ( $allContentTypes as $name => $username){
			$contentTypes['content_' . $name] = 'content_' . $name;
		}
		
		if($this->options['searchIn'] == 'title'){
			$this->tablesToSearch = array('Node' => 'Nodes');
		} else {
			$this->tablesToSearch = array_merge(
				$contentTypes,
				array('Node' => 'Nodes')
			);
		}
			
		if($this->isAdmin){
			
		} else {
			
		}
	}
	
	function getTableColsToSearch(){
		foreach ($this->tablesToSearch as $oName => $tName){
			$o = new $oName;
			if(count($o->searchableCols)){
				$this->tableColsToSearch[$tName] = $o->searchableCols;
			} else {
				unset($this->tablesToSearch[$tName]); 
			}
		}
	}
	
	function searchInTables(){
		$select = array();
		foreach ($this->tablesToSearch as $oName => $tName){	
			$whereString = array();
			foreach ($this->tableColsToSearch[$tName] as $col){
				foreach ($this->searchStrings as $ss){
					$whereString[] = $this->db->quoteInto("`$col` LIKE ? ", '%' . $ss  . '%');
				}
			}	
			
			if($tName != 'Nodes'){
				if($this->isAdmin){
					$publishedOnly = '';
				} else {
					$publishedOnly = " state = 'PUBLISHED' AND ";
				}
				
				$join = "SELECT n_id as id, '$oName' as type FROM `NodesContents`, `$tName` WHERE `c_id` = $tName.id AND " . $publishedOnly;
			} else {				
				$join = "SELECT id, '$oName' as type FROM `$tName` WHERE";
			}
			
			$select[] = "$join ( " . implode(' OR ', $whereString) . " )";
			
				
		}
		
		$sqlQuery = implode(' UNION ', $select);
		
		//echo $sqlQuery;
		$ret = array();
		foreach ($this->db->fetchAll($sqlQuery) as $array){
			$ret[$array['id']] = $array['type'];
		}
		return $ret;		
	}
	
	function checkOwner($owner){		
		if($this->options['searchBy'] && $this->options['searchBy']!='all'){
			if($owner == $this->options['searchBy']){
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
		
	}
	
	function checkTemplate($node){
		$publishedContent = $node->getPublishedContent();
		if(!$publishedContent){
			return $node;
		}
		$pn = $node;
		$i = 0;
		while(!$publishedContent->templateExists()){							
			$n = $this->tree->getNodeById($node->parentId);
			if(!$n){
				return $node;
			}
			$node = $n;
			//if(!$node){ e($pn); e($node); }
			$publishedContent = $node->getPublishedContent();
			if(!$publishedContent){
				break;
			}
			$i++;
			//e($i);
			if($i > 50){
				break;
			}
		}
		return $node;				
	}
	
	function checkSearchedNodes(){
		
		$today = date('Y-m-d');		  
		if($this->isAdmin){
			
			foreach ($this->searchedNodes as $nodeId => $type){
				$node = $this->tree->getNodeById($nodeId);
				//pr($node);
				$superType = $node->getSupertype();		
				
				if($superType == 'structure'){
					if($node->type == 'ITEM'){ // pak smeruju do pages
						$superType = 'pages';
					}
				}
				
				
				$node->superType = $superType;
				
				if(in_array($superType, $this->exclude)){
					continue;
				}
				if(!$this->checkOwner($node->owner)){
					continue;
				}
				$this->validSearchedNodes[$nodeId] = $node;
			}
		} else {
			
			if($this->options['searchCategory']){
				$onlyIds = $this->tree->getNodeChildrenIds($this->options['searchCategory']);
			}			
			
			if($this->options['exclude']){
				$exclude = $this->options['exclude'];
			} else {
				$exclude = array();
			}
			
			foreach ($this->searchedNodes as $nodeId => $type){
				//e($nodeId);
				if(count($onlyIds)){
					if(!in_array($nodeId, $onlyIds)){
						continue;
					}
				}
				
				if(in_array($nodeId, $exclude)){
					continue;
				}
				
				$node = $this->tree->getNodeById($nodeId);
							
				if($node->isInPublishedBrach()){
					if($node->hasPublishedContent()){
						//e($node->nodeId);
						$publishedContent = $node->getPublishedContent();
						$superType = $node->getSupertype();	
						
						if($date = $publishedContent->getPropertyValue('dateShow')){ 
							if(strcasecmp($today, $date) < 0){     
								continue; 
							}
						}
				
						if($superType != 'files'){
							//$node = $this->checkTemplate($node);
						}
						
						$node->superType = $superType;
																	
    	
						if(!$this->checkOwner($node->owner)){
							continue;
						}
						if(in_array($superType, $this->exclude)){
							continue; 
						}
						
						// systemove stranky nezahrnujeme
						if(strpos($node->path, '/web/') === 0){
							continue;
						}
												
						if($superType == 'files'){
							$fileCotnent = $publishedContent;
						//	pr($fileCotnent);
							if(get_class($publishedContent) == 'content_SFSFile'){
								$fileFrontEndAvailableNodes = $fileCotnent->getFileFrontEndAvailableNodes();
								if(count($fileFrontEndAvailableNodes)){
									foreach ($fileFrontEndAvailableNodes as $fFEANode){
										if($fFEANode){
											$fFEANode = $this->checkTemplate($fFEANode);
											$this->validSearchedNodes[$fFEANode->nodeId] = $fFEANode;		
										}
									}
								}
							} 
							// pro soubory musim provest dalsi check, zobrazit se mohou pouze ty, ktere jsou viditelne na webu, tzn nekde jsou vlozene
							
							// pridam nody kde se soubor vyskytuje, ale soubor samotny nechci
							continue;							
						}
						
						$this->validSearchedNodes[$nodeId] = $node;
					} else {
											
					}
				}
				//if($node->)
			}
		}
	}
		
	
}
