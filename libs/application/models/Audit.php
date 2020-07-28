<?

class Audit extends Zend_Db_Table
{
	public  static $actionMap = array(
	'users' => array(
		'title' => 'Uživatelé' , 
		'actions' => array(
			'save' => 'Vložil uživatele  %s',
			'delete' => 'Smazal uživatele  %s',
			'deletewebuser' => 'Smazal uživatele webu  %s',
			'update' => 'Editoval uživatele  %s',
			'updateWebuser' => 'Editoval uživatele webu  %s'	
		)
	),
	
	'eshop' => array(
		'title' => 'E-shop' , 
		'actions' => array(
			'addItem' => 'Vložil produkt  %s',
			'delete' => 'Smazal produkt  %s',  
			'editproduct' => 'Editoval produkt  %s',  
		)
	),
	
	'sf' => array(
		'title' => 'Souborový systém' , 
		'actions' => array(
			'makefolder' => 'Vytvořil adresář %s',
			'uploadFile' => 'Vložil soubor %s',	
			'uploadFileVersion' => 'Nahrál novou verzi souboru %s',	
			'delete' => 'Smazal adresář %s',
			'deleteFile' => 'Smazal soubor %s'				
		)
	),
	'structure' => array(
		'title' => 'Struktura webu' , 
		'actions' => array(
			'delete' => 'Smazal adresář %s',
			'save' => 'Změnil adresář %s',
			'detail' => 'Změnil adresář %s',
			'saveNew' => 'Vložil adresář %s',
			'saveSEO' => 'Upravil SEO adresáře %s', 
			'saveVersion' => 'Změnil verzi adresáře %s'
		)
	),
	'login' => array( 'title' => 'Přihlášení do aplikace' , 'actions' => array('save' => 'Uložil')),
	'pages' => array(
		'title' => 'Stránky' , 
		'actions' => array(			
			'delete' => 'Smazal stránku %s',
			'save' => 'Vložil stránku %s',
			'saveVersion' => 'Editoval verzi stránky %s',
			'update' => 'Editoval stránku %s',
			'sortChange' => 'Změna řazení stránky %s'
		)
	),
	'events' => array(
		'title' => 'Kalendář akcí' , 
		'actions' => array(
			'save' => 'Uložil akci $m',
			'edit' => 'Editoval akci $m',
			'delete' => 'Smazal akci $m'
			
		)
	),
	'login' => array( 'title' => 'Přihlášení do systému' , 'actions' => array('index' => 'Přihlásil se')),
	'index' => array( 'title' => 'Úvodní stránka' , 'actions' => array('save' => 'Uložil')),
	'mailing' => array(
		'title' => 'Emaily' , 
		'actions' => array(
			'delete' => 'Smazal email %s',
			'send' => 'Odeslal email %s',
			'save' => 'Uložil koncept emailu %s'
		)
	),
	'settings' => array( 'title' => 'Nastavení' , 'actions' => array('save' => 'Uložil nastavení')),
	'enquiry' => array(
		'title' => 'Ankety' , 
		'actions' => array(
			'save' => 'Vložil anketu %s',
			'update' => 'Upravil anketu %s',
			'delete' => 'Smazal anketu %s'
		)
	),
	'intranet' => array(
		'title' => 'Intranet' , 
		'actions' => array(
			'delete' => 'Smazal soubor %s',
			'approve' => 'Schválil soubor %s',
			'unapprove' => 'Odschválil soubor %s'
		)
	),
	'help' => array(
		'title' => 'Nápověda' ,
		'actions' => array(
			'delete' => 'Smazal složku nápovědy %s'
		)
	),
	'reality' => array(
		'title' => 'Reality' , 
		'actions' => array(			
			'delete' => 'Smazal realitu %s',
			'save' => 'Vložil realitu %s',
			'update' => 'Editoval realitu %s'
		)
	)
	);




	protected function _setupTableName()
	{
		$this->_name = 'Audit';
		$this->adapter = $this->getAdapter();
		$this->controllers = new Controllers();
		$this->controllersAll = $this->controllers->getControllers();
		parent::_setupTableName();

	}

	public function addOperation($data){
		$this->insert($data);
	}

	public function makeAuditUserReadable($operations, $showUser = false){
		$nOperations = array();
		foreach ($operations as $id => $o){
			  
			if($showUser){  
				$nOperations[$id]['userid'] = Zend_Registry::getInstance()->systemUsers[$o['userid']];
			}
			$nOperations[$id]['controller'] = $this->controllersAll[$o['controller']];
			$nOperations[$id]['action'] = str_replace('%s', $o['message'], self::$actionMap[$o['controller']]['actions'][$o['action']] );
			$nOperations[$id]['time'] = $o['fTime'];
			
		}
		return $nOperations;
	}
	
	
	public function getUserAuditCount($userid){	
 		$condition = $userid > 0 ? " WHERE userid = ".$userid : "";
		return  $this->adapter->fetchOne("SELECT count(*) as pocet FROM `$this->_name`".$condition);
		
	}
	
	public function getUserAudit($userid,  $sort = 'time', $sortType = 'Desc', $limitStart = 0, $limitCount = 50 ){		
		$select =  $this->adapter->select();
		$select->from($this->_name, array( 'module', 'controller', 'action', 'nodeId', 'message', new Zend_Db_Expr("DATE_FORMAT(`time`,'%d.%m.%Y %H:%i') as fTime")));
		if($userid>0)
    		$select->where('userid = ?', $userid);				
		$sortType = $sortType?$sortType:'Asc';
		$select->order($sort . ' ' . $sortType);		
		 //   e($select->__toString());	 die();   
		
		$select->limit($limitCount, $limitStart);
		
		$operations = $this->adapter->fetchAll($select);
		return $this->makeAuditUserReadable($operations); 
	}
	
	public function getNodeAudit($nodeId, $sort = 'time', $sortType = 'Desc' ){		
		$select =  $this->adapter->select();
		$select->from($this->_name, array( 'module', 'controller', 'action', 'nodeId', 'userid', 'message', new Zend_Db_Expr("DATE_FORMAT(`time`,'%d.%m.%Y %H:%i') as fTime")));
    	$select->where('nodeId = ?', $nodeId);				
		$sortType = $sortType?$sortType:'Asc'; 
		$select->order($sort . ' ' . $sortType);		
		//e($select->__toString());	
		$operations = $this->adapter->fetchAll($select);		 
		return $this->makeAuditUserReadable($operations, true);
	}
	
	public function getFileAudit($no = 100){		
		$tree = Zend_Registry::getInstance()->tree;  
		$select =  "		
			SELECT count( * ) AS pocet, name AS path
			FROM `Files_downloads`
			WHERE `name` NOT LIKE (
			'%.jpg%'
			)
			AND `name` NOT LIKE (
			'%.gif%'
			)
			AND `name` NOT LIKE (
			'%.png%'
			)
			GROUP BY name
			ORDER BY pocet DESC 
			LIMIT $no
		";
		
		$dl = $this->adapter->fetchAll($select);
		$nDl = array();
		foreach ($dl as $id => $d){
			$n = $tree->getNodeByPath($d['path']);
			if($n){
				$title = $n->title . '(' . $d['path'] . ')';
			} else {
				$title = $d['path'];
			}
			$nDl[] = array(
				'count' => $d['pocet'],
				'title' => $title				
			);
		}

		return $nDl;
	}
	
	public function getContentsStats($exclude = array()){		
		
				
		$tree = Zend_Registry::getInstance()->tree;
		
		$foldersCount =  $this->adapter->fetchOne("
			SELECT count(*) as pocet  
			FROM `Nodes`
			WHERE `path` NOT LIKE  '/cms-help/%' AND `type` != 'ITEM' 
			"
		);
		
		$pagesCount = $this->adapter->fetchOne("
			SELECT count( * ) AS pocet
			FROM `Nodes`
			WHERE `path` NOT LIKE  '/cms-help/%' AND `type` = 'ITEM' 
			"
		);
		
				
		$fileFolders = $tree->getNodeById(2)->getChildren('FOLDER', true, false, 0);
		$fileFoldersCount = $tree->tempCount;
		
		$fileAll = $tree->getNodeById(2)->getChildren('BOTH', true, false, 0);
		$fileAllCount = $tree->tempCount;
		$filesCount = $fileAllCount - $fileFoldersCount;
		
		$sectionsCount = $foldersCount - $fileFoldersCount - 1;  // -1 za soubory
		$pagesCount = $pagesCount - $filesCount; // soubor je typu ITEM ale F FOLDER ne
		
		return array(
			'sectionsCount' => $sectionsCount>0?$sectionsCount:0,
			'pagesCount' => $pagesCount>0?$pagesCount:0,
			'filesCount' => $filesCount>0?$filesCount:0	
		);
	}
	
	function getStatsByContent($contentType, $date, $sort = 'dateCreate', $sortType = 'DESC', $onlyPublished = 1, $author = 0, $domain = 0, $count = 0){ 
		$select =  $this->adapter->select(); 
		$bind = array();
		   
		if($count){    		
    		$c =  new Zend_Db_Expr("count('*')"); 
    		$select->from(array('cm' => $contentType), array( $c )); 
    	} else {
    		$select->from(array( 'cm' => $contentType), array('n.id', 'n.title', 'n.path', 'n.parent', 'dateCreate')); 		  
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
       	 
        if($dateShow){
			$select->where('dateShow <= ?', new Zend_Db_Expr('NOW()'));	
    	}
    	
		if($date){
			$select->where('DATE(`created`) = ?', $date);	
    	} 
		
    		
    	if($domain){
			$select->where('domain = ?', $domain);	 
    	} 
		
		//$select->where('domain = ?', $this->domain);	  
		
		if($onlyPublished){
			$select->where('state = ?', 'PUBLISHED'); 
		}
		
		if($author){
			$select->where('owner = ?', $author);	  
		}
		
		$select->where('c_type = ?', $contentType);  
		
		$sortType = $sortType?$sortType:'Asc';
		$select->order($sort . ' ' . $sortType); 
		$select->order('n.id DESC');   
		//$select->limit($limitCount, $limitStart);
		
		if($count){    		
			return $this->adapter->fetchOne($select, $bind);	  
		} else {
			return $this->adapter->fetchAll($select, $bind);	 
		}
	}
}
?>
