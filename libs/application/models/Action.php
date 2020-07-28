<?

class Action 
{
	public static $accessible = array(
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
	'sf' => array(
		'title' => 'Souborový systém' , 
		'actions' => array(
			'makefolder' => 'Vytvořil adresář %s',
			'uploadFile' => 'Vložil soubor %s',	
			'delete' => 'Smazal adresář %s',
			'deleteFile' => 'Smazal soubor %s'				
		)
	),
	'structure' => array(
		'title' => 'Struktura webu' , 
		'actions' => array(
			'delete' => 'Smazal adresář %s',
			'save' => 'Změnil adresář %s',
			'saveNew' => 'Vložil adresář %s',
			'saveSEO' => 'Upravil SEO adresáře %s'	
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
				$nOperations[$id]['user'] = Zend_Registry::getInstance()->systemUsers[$o['username']];
			}
			$nOperations[$id]['controller'] = $this->controllersAll[$o['controller']];
			$nOperations[$id]['action'] = str_replace('%s', $o['message'], self::$actionMap[$o['controller']]['actions'][$o['action']] );
			$nOperations[$id]['time'] = $o['fTime'];
			
		}
		return $nOperations;
	}
	
	public function getUserAudit($username,  $sort = 'time', $sortType = 'Desc' ){		
		$select =  $this->adapter->select();
		$select->from($this->_name, array( 'module', 'controller', 'action', 'nodeId', 'message', new Zend_Db_Expr("DATE_FORMAT(`time`,'%d.%m.%Y %H:%i') as fTime")));
    	$select->where('username = ?', $username);				
		$sortType = $sortType?$sortType:'Asc';
		$select->order($sort . ' ' . $sortType);		
		//e($select->__toString());	
		$operations = $this->adapter->fetchAll($select);
		return $this->makeAuditUserReadable($operations);
	}
	
	public function getNodeAudit($nodeId, $sort = 'time', $sortType = 'Desc' ){		
		$select =  $this->adapter->select();
		$select->from($this->_name, array( 'module', 'controller', 'action', 'nodeId', 'username', 'message', new Zend_Db_Expr("DATE_FORMAT(`time`,'%d.%m.%Y %H:%i') as fTime")));
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

}
?>
