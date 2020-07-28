<?

/**
 * Třída reprezentující časovou podmínku uzlu.
 * @since 1.0
 * @package model_node
 */ 
class model_node_TimeCondition {
	
	/**
	 * Časový údaj od ve formě univerzálního času.
	 * @var string
	 * @access public
	 */
	var $from = '';
	
	/**
	 * Časový údaj do ve formě univerzálního času.
	 * @var string
	 * @access public
	 */
	var $to = '';
	
	/**
	 * Konstruktor.
	 * @param strin $str řetězcová reprezentace časové podmínky
	 * @return void
	 * @access protected
	 */
	function __construct($str = '') {
		$this->set($str);
	}
	
	/**
	 * Test, zda časová podmínka platí (aktuální čas je v časovém intervalu stanoveném podmínkou).
	 * @return boolean
	 */
	function applies() {
		$now = Utils::mkTime();
		//echo "from: $this->from, to: $this->to, now: $now<br>";
		if ($this->from && strcmp($this->from, $now) > 0) return false;
		if ($this->to && strcmp($this->to, $now) < 0) return false;
		return true; 
	}
	
	/**
	 * Nastaví časovou podmínku podle textové reprezentace předané v parametru, kterou metoda
	 * příslušným způsobem zparsuje.
	 * @param string $str časová reprezentace (definice) podmínky
	 * @return void
	 */
	function set($str) {
		if (!$str) {
			$this->from = $this->to = '';
		} else {
			$arr = explode('-', $str);
			$this->from = Utils::parseTime(trim($arr[0]));
			if (count($arr) > 1) {
				$this->to = Utils::parseTime(trim($arr[1]));
				if (($pos = strpos($this->to, '00:00:00')) !== false) {
					$this->to = substr($this->to, 0, $pos).'23:59:59';
				}
			}
		}
	}
	
	/**
	 * Vrátí řetězcovou reprezentaci časové podmínky.
	 * @return string
	 */
	function __toString() {
		$str = '';
		if ($this->from) $str .= Utils::formatTime($this->from);
		if ($this->to) $str .= '-'.Utils::formatTime($this->to);
		return $str;
	}

	function getCopy() {
		return new model_node_TimeCondition($this->__toString());
	}

}

/**
 * Třída reprezentuje uzel v datovém stromu. Uzly jsou základním stavebním kamenem datového
 * modelu systému. 
 * @since 1.0
 * @package model
 */
class Node
{    
	
	/*
	'levelId' => 'Uživatelsky definované pozice',
		'levelIdDesc' => 'Uživatelsky definované pozice sestupně',		
		*/
	public static $sortTypes = array(
		'dateCreateDesc' => 'data vytvoření od nejnovějšího',
		'dateCreate' => 'data vytvoření od nejstaršího',
		'dateModifDesc' => 'data poslední úpravy od nejnovějšího',
		'dateModif' => 'data poslední úpravy od nejstaršího',		
		'titleDesc' => 'názvu Z-A',
		'title' => 'názvu A-Z',
		'random' => 'náhodně',
		'levelId' => 'Uživatelsky definované pozice',
		'publishedContentDesc' => 'typu obsahu Z-A',
		'publishedContent' => 'typu obsahu A-Z',
		'dateShow' => 'Podle data zobrazení od nejstaršího (Novinky)',
		'dateShowDesc' => 'Podle data zobrazení od nejnovějšího (Novinky)'
		
	);
	
		
	/**
	 * Identifikátor uzlu (1 až n).
	 * @var int
	 * @access public
	 */
	public $nodeId;
	
	/**
	 * Identifikátor nadžazeného (parent) uzlu (1 až n nebo NULL, pokud se jedná o kořenový uzel
	 * (root)).
	 * @var int
	 * @access public
	 */
	public $parentId;
	
	/**
	 * Typ uzlu.
	 * @var string
	 * @access public
	 */
	 
	public $type = 'ITEM';
    
		
	public  $orderValuation = 0;
	
	/**
	 * Identifikace pravidla určujícího třízení podstránek. Možnosti viz. porovnávací metody (cmp_xxx)
	 * ve třídě Tree.
	 * @var string
	 * @access public
	 */
	public $sort = false;
			
	/**
	 * Zobrazovat uzel v navigaci.
	 * @var boolean
	 * @access public
	 */
	public $showInNavigation = true;
	
	/**
	 * V databázi uložená textová reprezentace časová podmínka uzlu určující, kdy může být obsah
	 * uzlu zobrazen.
	 * @var model_node_TimeCondition
	 * @access public
	 */
	public $timeCondition;
	
	/**
	 * Název uzlu.
	 * @var string
	 * @access public
	 */
	public $title = '';
	
	/**
	 * Datum a čas vytvoření uzlu.
	 * @var string
	 * @access public
	 */ 
	public $dateCreate;
	
	/**
	 * Uživatelské jméno vlastníka (autora) uzlu.
	 * @var string
	 * @access public
	 */
	public $owner;
	
	/**
	 * Datum a čas poslední modifikace uzlu.
	 * @var string
	 * @access public
	 */
	public $dateModif;
	
	/**
	 * Uživatelské jméno uživatele, který jako poslední uzel modifikoval.
	 * @var string
	 * @access public
	 */
	public $modifiedBy;
	
	/**
	 * popis uzlu
	 * @var string
	 * @access public
	 */
	public $description;
	
	/**
	 * cesta
	 * @var string
	 * @access public
	 */
	public $path;
	
	/**
	 * Vlastnosti uzlu (pole objektů třídy NodeProperty).
	 * @var array
	 * @access public
	 * @see NodeProperty
	 */
	public $properties = array();
	
	/**
	 * Vlastnosti uzlu - jakou použít šablonu
	 * @var array
	 * @access public
	 */
	public $template;
	
	public $searchableCols = array('title', 'description');
		
	public $translated = array('cz');
	
	function __construct() {		
		$this->timeCondition = new model_node_TimeCondition();
	}

	/**
	 * Vrátí nadřazený (parent) uzel. 
	 * @return Node
	 */
	function getParent() {
		return Zend_Registry::getInstance()->tree->getNodeById($this->parentId);
	}
	
	function getSupertype() {
				
		if($this->parentId == 0 || $this->type == 'TREE'){
			$nodeId = $this->nodeId;
		} else {
			$tree = Zend_Registry::getInstance()->tree;
			$root = false;
			$parentId = $this->parentId;	
			$i = 0;
			do {
				$i++;
	    		$node = $tree->getNodeById($parentId);
	    		$nodeId = $node->nodeId;
				$parentId = $node->parentId;			
				if($i > 500){
					break;
				}
	    	} while ($parentId > 0 && $node->type != 'TREE');
		}
		
		$map = array_flip(Zend_Registry::getInstance()->config->treeNodeIdMap->toArray());	
		return $map[$nodeId];
	}
	
	
	function changeUserOrder($orderType, $parentSort) {
		$tree = Zend_Registry::getInstance()->tree;
		//orderUp orderTop orderDown orderBottom
		$children = $tree->getNodeChildren($this->parentId, $this->type, $parentSort);		
		$firstNodeOrderValuation = $nextNodeOrderValuation = 0;
		foreach ($children as $child){
			if($_SERVER['REMOTE_ADDR'] == '217.195.175.145'){
			//	e($child->orderValuation . '-' . $child->nodeId . '-' . $child->title);
			}  
			 
			if(!$firstNodeOrderValuation){
				$firstNodeOrderValuation = $child->orderValuation;
				$firstNodeId = $child->nodeId;
				
				// pokud jeto 1.
				$lastNodeOrderValuation =  $child->orderValuation;
				$lastNodeId =  $child->nodeId;
			}			
			if($this->nodeId == $child->nodeId){ // je to ona
				$prevNodeOrderValuation = $lastNodeOrderValuation;
				$prevNodeId = $lastNodeId;
			} elseif ($prevNodeOrderValuation && !$nextNodeOrderValuation){ // tzn jsme jeden cyklus za
				$nextNodeOrderValuation = $child->orderValuation;
				$nextNodeId = $child->nodeId;
			}			
			$lastNodeOrderValuation =  $child->orderValuation;
			$lastNodeId =  $child->nodeId;
		}
		
		/*
		e($this->nodeId);
		e($firstNodeOrderValuation . '-' . $firstNodeId);
		e($prevNodeOrderValuation . '-' . $prevNodeId);
		e($nextNodeOrderValuation . '-' . $nextNodeId);
		e($lastNodeOrderValuation . '-' . $lastNodeId);
		*/
		
		switch ($orderType){
			case 'orderUp':
				$n = $tree->getNodeById($prevNodeId);
				$n->orderValuation = $this->orderValuation;
				$this->orderValuation = $prevNodeOrderValuation;
				break;
			case 'orderTop': 
				$this->orderValuation = $firstNodeOrderValuation - 1;
				
			//	e($this->orderValuation);
				break;
			case 'orderDown':
				$n = $tree->getNodeById($nextNodeId);
				$n->orderValuation = $this->orderValuation;
				$this->orderValuation = $nextNodeOrderValuation;				
				break;
			case 'orderBottom':
				$this->orderValuation = $lastNodeOrderValuation + 1;
				break;
			default:
				break;
		}
		
		if($n){
			$n->save();
		}
		$this->save();
		
	}
	
	// vrati true pokud je v publikovane vetvi
	function isInPublishedBrach() {
		
		if($this->getSupertype() == 'files'){
			return true;
		}
		
		if($this->parentId == 0){
			$hasPublished = $this->hasPublishedContent();
		} else {
			$tree = Zend_Registry::getInstance()->tree;
			$root = false;
			$parentId = $this->parentId;	
			
			$i = 0;
			do {
				$node = $tree->getNodeById($parentId);
				$i++;
				if($node){
		    		if($node->hasPublishedContent()){
		    			$parentId = $node->parentId;	
		    		} else {
		    			return false;
		    		}
				} else {
					return false;
				}
	    		$nodeId = $node->nodeId;
				$parentId = $node->parentId;			
				if($i > 500){
					return false;
				}
	    	} while ($parentId > 0);
	    	return true;
		}
    	return $hasPublished;
	}
	
	/**
	 * 
	 */
	function getTemplate() {
		if ($this->template){
			return $this->template;
		}
		
		$tree = Zend_Registry::getInstance()->tree;
		$template = false;
		$parentId = $this->parentId;	
		$i = 0;
		do {
			$i++;
    		$node = $tree->getNodeById($parentId);
			$template = $node->template;
			$parentId = $node->parentId;
			if($i > 500){
				$template = 'homepage';
			}
    	} while (!$template);
    	
		return $template;
	}
	
	function getOverviewType() {
		if($this->type == 'FOLDER' || $this->type == 'TREE'){
			$publishedContent = $this->getPublishedContent();
			if(!$publishedContent){
				$publishedContent = end($this->contents);
			}
			if($publishedContent){
				return $publishedContent->getName();
			} else {
				return null;
			}
		} else {
			return null;
		}
	}
	
	
	/**
	 * Vrátí vlasnost uzlu se zadaným názvem. Pokud vlastnost neexistuje, vrací NULL.
	 * @param string $name název vlastnosti uzlu
	 * @return ContentProperty
	 */
	function getProperty($name) {
		foreach ($this->properties as $property) {
			if ($property->name == $name) return $property;
		}
		return null;
	}
		
	/**
	 * Vrátí hodnotu vlastnosti uzlu se zadaným názvem. Pokud vlastnost neexistuje, vrací NULL.
	 * @param string $name název vlastnosti uzlu
	 * @return mixed
	 */
	function getPropertyValue($name) {
		$property = $this->getProperty($name);
		return ($property) ? $property->value : null;
	}
	
	function setProperty($name, $type, $value) {
		if($property = $this->getProperty($name)){
			$property->value = $value;			
		} else {
			$this->properties[] = new  NodeProperty($name, $type, $value);
		}
	}
	
	//function save($dbAdapter = null, $tree = null, $node = 1) {
	function save($dbAdapter = null, $tree = null, $saveTree = true) {
		$reg = Zend_Registry::getInstance();
		if($reg->cache){
			$ident = $reg->cache->identificator . "node_" .  $this->nodeId;
			$reg->cache->remove($ident);   
		} 
		
		$this->title = html_entity_decode($this->title);
		
		if(!$dbAdapter){
			$dbAdapter = Zend_Registry::getInstance()->db;
		}
		if(!$tree){
			$tree = Zend_Registry::getInstance()->tree;
		}
		
		$this->dateModif = Utils::mkTime();
		$this->modifiedBy = $reg->session->user->username; 
		
	
		$langs = array();
		if(count($this->showInLanguages)){
			foreach ($this->showInLanguages as $lang => $ison){
				if($ison){
					$langs[] = $lang;
				}
			}
		}
		
    	$updateData = array(
			'parent' => $this->parentId, 
			'type' => $this->type, 
			'path' => $this->path, 
			'template' => $this->template, 
			'sort' => $this->sort, 
			'title' => $this->title, 
			'description' => $this->description, 
			'languages' => implode(',', $langs),    
			'dateModif' => $this->dateModif, 
			'timeCondition' => serialize($this->timeCondition), 
			'orderValuation' => $this->orderValuation
		);
		
		if($_SERVER['REMOTE_ADDR'] == '217.195.175.152'){
			//e($updateData); 
			
		}
		
		$updateData = $this->checkLangCols($updateData);
		
		$serializableNode = clone $this;
		$serializableNode->contents = array();
		$serializableNode->dateModif = Utils::mkTime();
		$updateData['object'] = serialize($serializableNode);
		
		/*
		if($_SERVER['REMOTE_ADDR'] == '217.195.169.225'){
			e($updateData);
			die();
		}*/
		
		if($_SERVER['REMOTE_ADDR'] == '217.195.175.152'){
			//e($updateData); die();
			
		}
			
    	$dbAdapter->update('Nodes', $updateData, 'id = ' . $this->nodeId);
    	
		if($saveTree){
			$tree->save($this->getSupertype());	
		}
	}
	
	function saveEn($dbAdapter = null, $tree = null, $saveTree = true) {
		$reg = Zend_Registry::getInstance();
		if($reg->cache){
			$ident = $reg->cache->identificator . "node_" .  $this->nodeId;
			$reg->cache->remove($ident);   
		} 
		
		$this->title = html_entity_decode($this->title);
		$this->en_title = html_entity_decode($this->en_title);
		
		if(!$dbAdapter){
			$dbAdapter = Zend_Registry::getInstance()->db;
		}
		if(!$tree){
			$tree = Zend_Registry::getInstance()->tree;
		}
		
		$this->dateModif = Utils::mkTime();
		$this->modifiedBy = $reg->session->user->username; 
    	$updateData = array(
    		'cz_path' => $this->path, 
			'cz_title' => $this->title,  
			'en_path' => $this->en_path, 
			'en_title' => $this->en_title, 
		);
				
		$this->showInLanguages['en'] = 1; 
		$this->showInLanguages['cz'] = 1;  
		
		$serializableNode = clone $this;
		$serializableNode->contents = array();
		$serializableNode->dateModif = Utils::mkTime();
		$updateData['object'] = serialize($serializableNode);
					
    	$dbAdapter->update('Nodes', $updateData, 'id = ' . $this->nodeId);
    	
		if($saveTree){
			$tree->save($this->getSupertype());	
		} 
	}
	
	function getLangsValues(){
		$reg = Zend_Registry::getInstance();
		$dbAdapter = Zend_Registry::getInstance()->db;
		$data = $dbAdapter->fetchRow("SELECT * FROM Nodes WHERE id = " . $this->nodeId);
		return $data;
	}
	
	function checkLangCols($data){
		$reg = Zend_Registry::getInstance();
		if($reg->languages->isMultiLanguage){
			$cols = helper_Database::getTableColumns('Nodes');
			
			$prefix = $reg->languages->fullLangPrefix . 'path';
			$this->$prefix = $this->path;
			//$this->cz_path = '/';
			
			$defaultCol1 = $reg->languages->defaultPathLanguage . '_path';
			$defaultCol2 = $reg->languages->defaultPathLanguage . '_title';
			if(!$defaultCol1 || !$this->$defaultCol1){
				$defaultCol1 = $reg->languages->defaultLanguage . '_path';
				$defaultCol2 = $reg->languages->defaultLanguage . '_title';
			}
			
			$dbData = $this->getLangsValues();
/*
			if($_SERVER['REMOTE_ADDR'] == '217.195.175.149'){ 
					e($data); 
					die();
				}
				*/   
							
			foreach ($reg->languages->languageFullPrefixMap as $ident => $prefix){
				
				
				
				$langColl = $prefix . 'title';		
								
				//if($dbData[$langColl] && $prefix != $reg->languages->fullLangPrefix){
				if($prefix != $reg->languages->fullLangPrefix){
					$n = $prefix . 'path';
					$data[$n] = $dbData[$n];
					$this->$n = $data[$n];
					
					$n = $prefix . 'title';
					$data[$n] = $dbData[$n];
					$this->$n = $data[$n];
					
					continue;
				}
				
				if(!in_array($langColl, $cols)){
					helper_Database::addColl('Nodes', $langColl, 'title', 'VARCHAR( 255 )');
				}
								
				if(!$this->$langColl){
					$this->$langColl = $dbData[$langColl];
				}
				if(!$this->$langColl){
					$this->$langColl = $this->$defaultCol2;
				}
				$data[$langColl] = $this->$langColl;
								
					
				$langPathColl = $prefix . 'path';
				if(!in_array($langPathColl, $cols)){
					helper_Database::addColl('Nodes', $langPathColl, 'path', 'VARCHAR( 255 )');
				}
				
				if(!$this->$langPathColl){
					$this->$langPathColl = $dbData[$langPathColl];
				}
				if(!$this->$langPathColl){
					//e($defaultCol1);
					$this->$langPathColl = $this->$defaultCol1;
				}
				
				$data[$langPathColl] = $this->$langPathColl;
				/*
				if($val){
					$data[$langPathColl] = $val;
				} else {					
					//e($this->path);
					$pathIdent = $reg->languages->isForeignLanguagePath($this->path);
								
					if($pathIdent){
						$nPath = str_replace('/' . $pathIdent, '', $this->path);
						$nPath = str_replace('//', '/', $nPath);
					} else {
						$nPath = $this->path;
					}
					
					$langPath = $reg->languages->pathMap[$ident];
					if($langPath){
						if($nPath == '/'){
							$nPath = '';
						}
						$nPath = '/' . $langPath . $nPath;
					}
					$nPath = $nPath?$nPath:'/';					
					$data[$langPathColl] = $nPath;
					
				}
			}
		}	
		*/
				/*
				if($_SERVER['REMOTE_ADDR'] == '217.195.175.149'){
					e($data);
					die();
				}
				*/
			}
		}
		return $data;
	}
	
	function getNextNodeId($dbAdapter = null) {	
		if(!$dbAdapter){
			$dbAdapter = Zend_Registry::getInstance()->db;
		}
		$data = $dbAdapter->fetchRow("SHOW TABLE STATUS LIKE 'Nodes'");
		return $data['Auto_increment'];
	}
	
	function getChildren($type='ITEM', $deep = false, $setPublishedContent = false, $count = -1, $quotedWhere = ''){
		return Zend_Registry::getInstance()->tree->getNodeChildren($this->nodeId, $type, $this->sort, $deep, $setPublishedContent, $count, $quotedWhere);
	}
	
	static function saveAudit($old,$new)
	{
		$db = Zend_Registry::getInstance()->db;
		$data['old'] = serialize($old);
		$data['nodeId'] = $old->nodeId;
		$data['new'] = serialize($new);
		$db->insert('Audit_Object',$data);
	}
		
	static function isPathAvailable($path){
		$db = Zend_Registry::getInstance()->db;
		$p = $db->fetchOne("SELECT path FROM `Nodes` WHERE `path`=?", array($path));
		return strlen($p)?false:true;
	}
	
	/*
	function getUserFriendlyPath($dontShow = array()){
		$db = Zend_Registry::getInstance()->db;
		$langs = Zend_Registry::getInstance()->tree;
		$paths = array();
		$id = $this->parentId;
		do{
			$parent = $db->fetchRow("SELECT parent, path, title FROM `Nodes` WHERE `id`=?", array($id));
			if($parent['path'] == '/web'){
				$parent['path'] = '/';
			}
			
			if($parent['path']){
				if(!in_array($id, $dontShow)){
					$paths[$parent['path']] = $parent['title'];
					
					if($parent['path'] == '/'){
						$paths[$parent['path']] = 'Úvod';
					}
				}
			}
			$id = $parent['parent'];
		} while ($id > 0);
		return array_reverse($paths);
	}
	*/
	
	function getUserFriendlyPath($dontShow = array(), $homeTitle = 'Úvod'){
		$db = Zend_Registry::getInstance()->db;
		$tree = Zend_Registry::getInstance()->tree;
		$paths = array();
		// e($dontShow); 
		$id = $this->parentId;
		do{
			$parent = $tree->getNodeById($id); 
			if($parent->path == '/web'){
				$parent->path = '/';
			}
			
			if($parent->path && !in_array($parent->path, $dontShow)){ 
				$paths[$parent->path] = $parent->title;
				
				if($parent->path == '/'){
					$paths[$parent->path] = $homeTitle;
				} 
			}
			$id = $parent->parentId;
		} while ($id > 0);
		return array_reverse($paths);
	}
	
	// WORKFLOW FUNCTIONS 
	
	function getContents() {
		$dbAdapter = Zend_Registry::getInstance()->db;
		$contents = $dbAdapter->fetchAll('SELECT c_id, c_type  FROM `NodesContents` WHERE `n_id` = ?', array($this->nodeId));
		$oContents = array();
		foreach ($contents as $content){
			$c = new $content['c_type']();
			if($c->serializable == true){
				$c = unserialize($dbAdapter->fetchOne('SELECT data FROM `'.$content['c_type'].'` WHERE `id` = ?', array(
					$content['c_id'])
				));
			} else {
				$c->setValues($dbAdapter->fetchRow('SELECT * FROM `'.$content['c_type'].'` WHERE `id` = ?', array(
					$content['c_id'])
				));
			}
			if($c->state == 'DELETED'){
				continue;
			}
			$oContents[$content['c_id']] = $c;
		}
		return $oContents;
	}
		
	function getPublishedContent() {
		if(is_array($this->contents)){
			foreach ($this->contents as $content){
				if($content->state == 'PUBLISHED'){
					return $content;
				}
			}
		}
		return null;
	}
	
	function getContent($cId) {		
		return $this->contents[$cId];
	}
	
	function getAllContents() {		
		return $this->contents;
	}
	
	// kdyz nejaky potrebuju, verze apod
	function getTheRightContent() {				
		$content = $this->getPublishedContent();
		if(!$content){			
			$content = end($this->contents);					
		}
	//pr($content);
		return $content;
	} 
		
	function publishContent($id) {
		//pr($id);
		
		foreach ($this->contents as $content){
			//e($content->id);
			if ($content->id == $id) {
				$this->archivePublishedContent();
				$content->state = 'PUBLISHED';				
				$content->update();				
			}
		}
	}
	
	function archivePublishedContent() {		
		foreach ($this->contents as $content){
			if($content->state == 'PUBLISHED'){
				$content->state = 'ARCHIVED';
				$content->update();
			}
		}
	}
	
	function hasPublishedContent() {
		foreach ($this->contents as $content){
			if($content->state == 'PUBLISHED'){
				return true;
			}
		}
		return false;
	}
	
	function deleteContent($id) {		
		foreach ($this->contents as $content){
			if ($content->id == $id) {				
				$content->state = 'DELETED';
				$content->update();					
			}
		}
	}
	/*
	function deleteContent($id) {
		$dbAdapter = Zend_Registry::getInstance()->db;
		$ctype = $dbAdapter->fetchOne(			
			'SELECT `c_type` FROM NodesContents WHERE c_id = ? ',
			$id
		);
				
		$where = $dbAdapter->quoteInto(" `n_id`= ? ", $this->nodeId) . $dbAdapter->quoteInto(" AND `c_id` = ? ", $id);
		$dbAdapter->delete(			
			'NodesContents',
			$where
		);
		
		$dbAdapter->delete(			
			$ctype,
			$dbAdapter->quoteInto("id= ?", $id)
		);
				
	}
	*/
	
	/*** LANGUAGE HELPERS */	
	
	public static function initTitle($node, $input){
		$reg = Zend_Registry::getInstance();
		$nodeTitleName = $reg->languages->fullLangPrefix . 'title';
    	$node->$nodeTitleName = htmlspecialchars($input->pageTitle);
    	$node->title = $node->$nodeTitleName;
	}
	
	public function showInCurentLanguage(){
		$reg = Zend_Registry::getInstance();		
		$lang = $reg->languages->language; 
		if($this->getPublishedContent()->_name == 'content_SFSFile'){
			return true; 
		}
		return $this->showInLanguage($lang);				
	}
	
	
	public function showInLanguage($lang){
		$reg = Zend_Registry::getInstance();
		if($reg->languages->isMultiLanguage && (count($this->showInLanguages) || $reg->languages->language != $reg->languages->defaultLanguage)){					
			if(!$this->showInLanguages[$lang]){				
				return false;
			} else {
				return true;
			}
		} else {
			return true;
		}
		
	}
	
	/*** HELPERS */
	
	public static function initFromDatabase($unserializedNode){
		
		if($unserializedNode){
			$reg = Zend_Registry::getInstance();
			//e($reg->languages->language);
			if($reg->languages->isMultiLanguage){
				$nodeTitleName = $reg->languages->language . '_title';
				$nodePathName = $reg->languages->language . '_path';			
				$translatedTitle = $unserializedNode->$nodeTitleName;
				/*
				if($_SERVER['REMOTE_ADDR'] == '217.195.175.134'){
					if($unserializedNode->nodeId == 3580){
						e($nodePathName);
						e($nodeTitleName); 
						pr($unserializedNode); die(); 
					} 
				}*/ 
				if($translatedTitle){
					$unserializedNode->title = $translatedTitle;			
				}			
				
				$translatedPath = $unserializedNode->$nodePathName;
				if($translatedPath){
					$unserializedNode->path = $translatedPath;			
				}			
				
				$unserializedNode->setProperty('pageTitle', 'text', $unserializedNode->getPropertyValue($reg->languages->fullLangPrefix . 'pageTitle'));
				$unserializedNode->setProperty('pageKw', 'text', $unserializedNode->getPropertyValue($reg->languages->fullLangPrefix . 'pageKw'));
				$unserializedNode->setProperty('pageDescription', 'text', $unserializedNode->getPropertyValue($reg->languages->fullLangPrefix . 'pageDescription'));
			}
						
			return $unserializedNode;
		} else {
			return null;
		}
	}


	
	
	public static function init($type, $parentId, $input, $view){		
		$reg = Zend_Registry::getInstance();
		
		$n = new Node();
		$n->type = $type;	    	
    	$n->title = htmlspecialchars($input->pageTitle);
    	$n->description = Utils::getWYSIWYGHtml($input->fck_description);
    	$n->parentId = $parentId;	
    	$n->nodeId = $n->getNextNodeId();
    		    	
    	
    	$n->dateCreate = $n->dateModif = Utils::mkTime();
    	$n->owner = $n->modifiedBy = $reg->session->user->username;	
    	
    	$n->saveLanguages($input);
    	$n->saveSEO($input);
    	if($type != 'ITEM'){
    		$n->saveSettings($input, $view);
    	}
    	
    	return $n;
	}

	private function savePathHistory($nodeId,$path)
	{
		$dbAdapter = Zend_Registry::getInstance()->db;
		$data['path'] = $path;
		$data['nodeId'] = $nodeId;
		$dbAdapter->insert('NodesPathHistory',$data);
	}
	
	public function initUpdate($input, $view){		
		$reg = Zend_Registry::getInstance();
		
				
		$this->dateModif = Utils::mkTime();
	    $this->modifiedBy = Zend_Registry::getInstance()->session->user->username;	    	
	    			    
	    $this->description = $input->description;
	   	
	    $this->saveLanguages($input);
		$this->saveSEO($input);
    	if($type != 'ITEM'){
    		$this->saveSettings($input, $view);
    	}
    	$this->saveParent($input, $view);
    	    	    	
    	$this->title = htmlspecialchars($input->pageTitle);
   }
	
	public function saveLanguages($input)
	{
		if(count($input->showInLanguages)){
			$this->showInLanguages = $input->showInLanguages;
			/*
			if($_SERVER['REMOTE_ADDR'] == '217.195.175.152'){
				e($this->showInLanguages);
				pr($this);
				
			}
			*/
		} else {
			$reg = Zend_Registry::getInstance();			
			// ulozim jen pokud uz neni zakazana, je to novej uzel v danym jazyku
			if(!isset($this->showInLanguages[$reg->languages->language])){				
				if(!is_array($this->showInLanguages)){
					$this->showInLanguages = array();
				}
				/*
				if($_SERVER['REMOTE_ADDR'] == '217.195.175.155'){
					e($reg->config->instance->createInAll);
					die();  
				}
				*/ 
				if($reg->config->instance->createInAll){
					if(!count($this->showInLanguages)){ 
						$langs = $reg->config->instance->languages->toArray();
						foreach ($langs as $ident => $title){
							$this->showInLanguages[$ident] = 1;
						}	 					
					} 
				} else {
					$this->showInLanguages[$reg->languages->language] = 1;
					
					
				}				
			}			
		}
	}
	
	public function saveSEO($input)
	{
		$reg = Zend_Registry::getInstance();
		$oldPath = $this->path;
		//if($input->pageSEOTitle || $input->pageKw || $input->pageDescription){
			if($reg->languages->isMultiLanguage){
				$prefix = $reg->languages->fullLangPrefix;
			} else {
				$prefix = '';
			}
			if(!$input->pageSEOTitle)
			{
				$input->pageSEOTitle= $input->pageTitle;
			}
			
			$this->setProperty($prefix . 'pageTitle', 'text', $input->pageSEOTitle);
			$this->setProperty($prefix . 'pageKw', 'text', $input->pageKw);
			$this->setProperty($prefix . 'pageDescription', 'text', $input->pageDescription);
		//}  
		$tempPageTitle = mb_strtolower ($input->pageTitle,"UTF-8");
		$tempTitle = mb_strtolower ($this->title,"UTF-8");

		if($tempPageTitle != $tempTitle || $this->path == '' ||  (!$reg->languages->isLanguagePath($this->path) && $reg->languages->isMultiLanguage)){
		

			if($reg->languages->isMultiLanguage){
				
				self::initTitle($this, $input);
					Node::saveAudit($this, $input);
		//		echo 'isMultiLanguage';				
				$ppath = $reg->tree->getNodeById($this->parentId)->path;    			
				
				// check
				$prefix = $reg->languages->getPathPrefix($ppath);
				
				
				if($this->nodeId == 1){			
	    			if($prefix){
	    				$newPath = '/' . $prefix;
	    			} else {
	    				$newPath = '/';
	    			}
		    	} else {
		    		if($prefix){
		    			$ppath = $prefix . $ppath;
		    		} 
		    		
		    		if($reg->languages->useNodePath()){
		    			$newPath = $ppath .'/' . $this->nodeId;	 
		    			
		    		} else {
		    			$newPath = $ppath .'/' . Utils::generatePathName($this->title, null, $prefix . '/' . $ppath . '/');
						//e($newPath );
						//pr('dsf');	 	    				    		
		    		}
		    	}  
		    	
			} else {
			
					Node::saveAudit($this, $input);		
				$ppath = Zend_Registry::getInstance()->tree->getNodeById($this->parentId)->path;    			
				if($this->parentId > 1){
					
					$tempPageTitle = strtoupper($input->pageTitle);
					$newTitle = Utils::cleanTitle($input->pageTitle);
						$this->title = Utils::cleanTitle($this->title);
						$newPath = $this->path;
						if(strtoupper($newTitle) != strtoupper($this->title) || $this->path == '')
						{
		    				$newPath = $ppath .'/'. Utils::generatePathName($tempPageTitle, '', $ppath . '/');
							
						}
		    	} else {
		    		if($this->nodeId != 1){
		    			
		    			$newTitle = Utils::cleanTitle($input->pageTitle);
							$this->title = Utils::cleanTitle($this->title);
						$newPath = $this->path;
						if(strtoupper($newTitle) != strtoupper($this->title) || $this->path == '')
						{
							
		    				$newPath = '/'. Utils::generatePathName($input->pageTitle);
						}	

		    		} else {
		    			$newPath = '/';
		    		}
		    	}  
			}
		
				
    	} else {
    			Node::saveAudit($this, $input);
    		if($reg->languages->isMultiLanguage){
	    		self::initTitle($this, $input);
				if($input->nodePath){
					
					// check
					$prefix = '';
					if(!$reg->languages->isDefaultLanguage){
						if(!$reg->languages->isLanguagePath($input->nodePath)){
							$prefix = $reg->languages->language;
							
						}
					}
					    	
					$newPath = $prefix . $input->nodePath;
					
				}
    		} else {
    				Node::saveAudit($this, $input);
    			if($input->nodePath){
					$this->path = $input->nodePath;
				}
    		}
    	}

		
    		if($newPath){
    		$this->path = str_replace('//', '/', $newPath);    
			
			$this->path = str_replace('  ', ' ', trim($newPath));    		
    		$nodePathName = $reg->languages->fullLangPrefix . 'path';    	    				
    		$this->$nodePathName = $this->path;
			if(($this->path != $oldPath || $newPath !=$this->path) && $oldPath !='')
			{
				$this->savePathHistory($this->nodeId, $oldPath);
			}
		}
    		
    	
	}
	
	public function saveSettings($input, $view)
	{		
		if(is_numeric($this->showInNavigation)){ // uz je ulozen
			 $this->showInNavigation = $input->showInNavigation?$view->showInVal[$input->showInNavigation]:$this->showInNavigation;
		} else {
			 $this->showInNavigation = $input->showInNavigation?$view->showInVal[$input->showInNavigation]:'1';
		}

		if(is_numeric($input->showInSitemap)){ // uz je ulozen
			 $this->showInSitemap = $input->showInSitemap;
		}
		
		if(is_numeric($input->showInPages)){ //  
			 $this->showInPages = $input->showInPages;
		}
		
		if(is_numeric($input->showH1)){ // uz je ulozen
			 $this->showH1 = $input->showH1;  
		} 
		
				 
	    if($this->sort){
			$this->sort = $input->sortType?$input->sortType:$this->sort;		
	    } else {
	    	$this->sort = $input->sortType?$input->sortType:Zend_Registry::getInstance()->config->defaultNodeSort;		
	    }
		
		if((!$this->template || $input->template != $this->template) && $input->template){
			$this->template = $input->template;		
		}
	}
	
	
	public function saveParent($input, $view)
	{
		// e($input->parentId); die(); 
		if($input->parentId){
			if($this->nodeId > $view->rootNodeId && $input->parentId != $this->parentId ){
				//echo pr($view->tree->getNodeById($input->parentId)->path);
				//echo (Utils::getPathsLastPart($this->path));
				$this->parentId = $input->parentId;		
				$newPath = $view->tree->getNodeById($input->parentId)->path . '/'. Utils::getPathsLastPart($this->path);
				$newPath = str_replace('//', '/', $newPath); // root path fix
				
				if($this->getPublishedContent()->_name == 'content_SFSFile'){				
					$c = $this->getPublishedContent();
					$c->move($newPath);
				}
				
				$ref = new References($this->getTheRightContent());	    	    	
		    	foreach ($ref->getNodeReferences() as $nodeId => $contentId){
		    		$n = $view->tree->getNodeById($nodeId);
		    			
		    		if($n){
		    			$content = $n->getContent($contentId);
			    		$content->fixNodePathInHtml($this->path, $newPath);
			    		$content->update();
			    		$n->save();
		    		}
		    	}
		    	
		    	$newPath = str_replace('//', '/', $newPath); // root path fix
				$this->path = $newPath;
				
			}
		}
	}
	
	public function checkStateChange($input, $content){
		$this->contents = $this->getContents();
		
		if($input->state != $content->state){
    		//parent::audit($cnode->title . ' ',$cnode->nodeId. 'wfchange:' . $this->input->state);	  
    		if($input->state == 'PUBLISHED'){
				$this->publishContent($content->id);					
			} else {
				$content->state = $input->state;
				$content->update();
			}
    	}
	}	
	
	// ADVERTS 
	function saveAdverts($adverts, $content){
		if(is_array($adverts)){
			$oldAdverts = $this->adverts;			
			
			

			$contentAdverts = $content->getAdvertsIdentificators();
			$contentAdverts = array_flip($contentAdverts);		
			if(count($adverts) == 0){
				$adverts = $contentAdverts;		
			}
			
			if(count($oldAdverts)){
				foreach ($oldAdverts as $ident => $advets){
					if(!isset($advets[$ident])){
						unset($oldAdverts[$ident]);
					}
				}
			}
  
			if($oldAdverts){
				$adverts = array_merge($oldAdverts, $adverts);
			}
			$this->adverts = $adverts;	
		}
	}
	
	function getAdverts($content, $onlyActive = false){		
		$adverts = array(); 
		$content->setAdvertsFromNode($this->adverts, $onlyActive);
		foreach ($content->advertsPositions as $position){
			    
			$adverts[$position->identificator] = $position;
		}

		return $adverts;
	}
	
	function getHtml($name = 'html'){
		return $this->getPublishedContent()->getHtml($name);   
	}
}

/**
 * Třída reprezentující v datovém modelu uloženou vlastnost uzlu.
 * @since 1.0
 * @package model
 */
class NodeProperty {
	
	/**
	 * Jméno vlastnosti.
	 * @var string
	 * @access public
	 */
	var $name;
	
	/**
	 * Typ vlastnosti. 
	 * @var string
	 * @access public
	 */
	var $type = 'text';
	
	/**
	 * Hodnota vlastnosti.
	 * @var mixed
	 * @access public
	 */
	var $value;
	
	function __construct($name = false, $type = false, $value = false) {
		$this->name = $name;
		$this->type = $type;
		$this->value = $value;
	}
}

?>
