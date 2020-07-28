<?
/**
 * Překlady
 *  * 
 */
class module_languages_Languages
{
	/**
	 * Defaultni jazyk pro tento web
	 *
	 * @var unknown_type
	 */
	public $defaultLanguage;	
	
	/**
	 * Aktualne nastaveny jazyk
	 *
	 * @var string
	 */
	public $language;	
		
	public $availableLanguages = array();

	public $_tableName = 'module_Languages_Preklady';
	
	function __construct($view) {
		$this->view = $view;
		$this->init();
		$this->initCurentLanguage();
		$this->postInit();
		//ALTER TABLE `Nodes` ADD `en_title` VARCHAR( 255 ) NOT NULL AFTER `title` ;
		//ALTER TABLE `Nodes` ADD `translated` VARCHAR( 200 ) NOT NULL DEFAULT 'cz' AFTER `object` ;	
	}
	
	function init(){
		$this->config = Zend_Registry::getInstance()->config;		
		$this->db = Zend_Registry::getInstance()->db;		
		$this->reg = Zend_Registry::getInstance();	
		
		if($this->config->instance->languages){
			$this->defaultLanguage = $this->config->instance->defaultLanguage;
			$this->defaultPathLanguage = $this->config->instance->defaultPathLanguage;
			$this->availableLanguages = $this->config->instance->languages->toArray();
			$this->defaultLanguage = $this->defaultLanguage?$this->defaultLanguage:'cz';
			
			if($this->config->instance->useNodePath){
				$this->useNodePathArray = $this->config->instance->useNodePath->toArray();
			} else {
				$this->useNodePathArray = array();
			}
			
			
			if(!count($this->availableLanguages)){
				$this->availableLanguages = array('cz' => 'česky');
			}
			
			$this->languagePrefixMap = array();
			$this->languageFullPrefixMap = $this->languageFullPathMap = array();
			foreach ($this->availableLanguages as $ident => $name){
				if($ident == $this->defaultLanguage){
					$prefix = '';
					$pathPrefix = '';
				} else {
					$prefix = $ident . '_';
					$pathPrefix = '' . $ident;
				}
				$this->languagePrefixMap[$ident] = $prefix;
				$this->languageFullPrefixMap[$ident] = $ident . '_';
				$this->languageFullPathMap['/' . $ident] = '/' . $ident;
				$this->pathMap[$ident] = $pathPrefix;
			}
			
			$this->isMultiLanguage = count($this->availableLanguages) > 1;
		}
	}	
	
	function useNodePath(){
		if(is_array($this->useNodePathArray) && count($this->useNodePathArray)){
			if(in_array($this->language, $this->useNodePathArray)){
				return true;
			}
		}
		return false;		
	}
	
	function initCurentLanguage(){
		
		if($this->config->isAdmin){
			$lang = $this->view->request->getParam('language');		
		} else {		
			$lang = $this->getLanguageFromPath($this->view->curentPath);					
		}
		$this->language = $lang?$lang:$this->defaultLanguage;
		$this->fullLangPrefix = $this->language . '_';
		$this->isDefaultLanguage = $this->language == $this->defaultLanguage;
	}	
	
	function postInit(){
		$this->langPrefix = $this->getLangPrefix($this->language);
		$this->langFEPrefix = $this->getLangFePrefix($this->language);
		$this->langPathPrefix = '/' . str_replace('_', '', $this->getLangPrefix($this->language));
		
		$this->fullLangPrefix = $this->language . '_';
	}	
	
	function getLangPrefix($lang){
		if($lang == $this->defaultLanguage){
			$prefix = '';			
		} else {
			$prefix = $lang . '_';
		}
		return $prefix;
	}
	
	function getLangFePrefix($lang){ 
		if($lang == $this->defaultLanguage){
			$prefix = '/';			
		} else {
			$prefix = '/' . $lang;
		}
		return $prefix;
	}
	
	function getCurentLanguage(){		
		return $this->language;
	}
	
	function isLanguagePath($nodePath){
		if(strpos($nodePath, '/' . $this->language . '/') === false && !$this->isDefaultLanguage) {			
			return false;
		} else {
			return $this->language;
		}
	}
	
	function isForeignLanguagePath($nodePath){
		if($this->getLanguageFromPath($nodePath)) {			
			return $this->language;
		} else {
			return false;			
		}
	}
	
	function getLanguageFromPath($nodePath){
		$test = substr($nodePath, 1 , 2);
		if($nodePath{0} == '/' && $this->availableLanguages[$test] && ($nodePath{3} == '/' || strlen($nodePath) == 3)) {
			return $test;
		} else {
			return false;
		}
	}
	
	function getPathPrefix($nodePath){
		
		$prefix = '';
		if(!$this->getLanguageFromPath($nodePath)){			
			$prefix = $this->pathMap[$this->language];
		}		
		return $prefix;
	}
	
	function copyDefaultToAll($only = false){
		$tree = Zend_Registry::getInstance()->tree;		
		$reg = Zend_Registry::getInstance();
		
		$all = $this->db->fetchAll("SELECT id FROM Nodes LIMIT 0, 1000");        
		$languages = $this->availableLanguages; 
		$nLangs = array(); 
		 
		foreach ($languages as $lang => $lTitle){
			if($only){
				if(!in_array($lang, $only)){ 
					continue;
				} 
			}
			$nLangs[$lang] = 1;
		}
		
		
		 
		$input = new stdClass();
		$input->showInLanguages = $nLangs;
		//$input->showInLanguages = array();
		//$input->showInLanguages['cz'] = 1;
				
		//   	pr($all); 	
		foreach ($all as $id){			 
			$node = $tree->getNodeById($id['id']);		
						
			$c = $node->getTheRightContent(); 
			if($c->_name == 'content_SFSFile'){
				continue;
			}
			$node->cz_title = $node->title; 
			$node->cz_path = $node->path;    
			 
			if($c){
				foreach ($input->showInLanguages as $ident => $one){				
					$properties = $c->getTranslatedProperties();
					$prefix = $reg->languages->languagePrefixMap[$ident];
					if($node->showInLanguages[$ident] != 1){ // novy neprelozeny jazky - nebo uz smazany, pak cekuju zda uz properties nejsou vyplnene
						foreach ($properties as $name => $property){
							$trasProperty = $c->getPropertyByName($prefix . $name);
							if($trasProperty->value == ''){ // nevypnena, naplnim defaultem
								//e(setuju);
								$trasProperty->value = $property->value;						
							} else {
								//e(nesetuju);
							}
						}
					}
				}
				 
				try{
					$c->update(true, true);
				} catch (Exception $e){
					e($e);
					e($c); 
					die(); 
				}
			}
			
			if($only && count($only)){
				if(is_array($node->showInLanguages)){
					$input->showInLanguages = array_merge($input->showInLanguages, $node->showInLanguages); 
				} 
			} 
						
			$node->saveLanguages($input);
			$node->save($this->db, $tree, false);									
		}
		// e($node);  
		$node->save($this->db, $tree, true);
		
		 
		$reg->db->query("update `Nodes` set `cz_path` = `path`, `cz_title` = `title` where `cz_path` IS NULL");
		 e('OK');  
		die();
		
	}
	
	
	function copyDefaultToAllNew($only = false){
		$tree = Zend_Registry::getInstance()->tree;		
		$reg = Zend_Registry::getInstance();
		
		$all = $this->db->fetchAll("SELECT id FROM Nodes LIMIT 0, 1000");        
		$languages = $this->availableLanguages; 
		$nLangs = array(); 
		 
		foreach ($languages as $lang => $lTitle){
			if($only){
				if(!in_array($lang, $only)){ 
					continue;
				} 
			}
			$nLangs[$lang] = 1;
		}
		
		
		 
		$input = new stdClass();
		$input->showInLanguages = $nLangs;
		//$input->showInLanguages = array();
		//$input->showInLanguages['cz'] = 1;
				
		
		//   	pr($all); 	
		foreach ($all as $id){			 
			$node = $tree->getNodeById($id['id']);							
			$c = $node->getTheRightContent(); 
			if($c->_name == 'content_SFSFile'){
				continue;
			}			
			if($only && count($only)){
				if(is_array($node->showInLanguages)){
					$input->showInLanguages = array_merge($input->showInLanguages, $node->showInLanguages); 
				} 
			} 
						
			$node->saveLanguages($input);
			$node->save($this->db, $tree, false);									
		}
		// e($node);  
		$node->save($this->db, $tree, true);
		 
		 
		$reg->db->query("update `Nodes` set `cz_path` = `path`, `cz_title` = `title` where `cz_path` IS NULL");
		 e('OK');  
		die();
		
	}
	
	/* PREKLADY FRAZI */
	
	function getTranslationArray(){
		$cols = $translation = array();
		
		$cols['ident'] = 'ident';
		if(!count($this->availableLanguages)){
			$this->availableLanguages[] = 'cz'; 
		}
		foreach ($this->availableLanguages as $prefix => $title){
			$cols[$prefix] = $prefix;
		}
		
		$select =  $this->db->select();
		$select->from($this->_tableName, $cols);    
		$select->order('ident asc');	
		foreach ($this->db->fetchAll($select) as $s){
			$k = array_shift($s);
			$translation[$k] = $s;
		}
		//$sel = implode('', )
		//$all = $this->db->fetchAll("SELECT id FROM Nodes LIMIT 10000"); 
		return $translation;
	}
	
	function saveFromInput($input){
	//	e($input);
		$save = $saveNew = array();
		foreach ($input as $k => $v){
			$parts = explode('_', $k);
			$p = count($parts);
			if($p == 3){ // new
				if($parts[0] == 'new'){
					$saveNew[$parts[2]][$parts[1]] = $v;
				}
			} elseif($p == 2){ 
				$save[$parts[0]][$parts[1]] = $v;
			}
		}
		
		foreach ($saveNew as $k => $v){
			if($v[$this->defaultLanguage] == ''){
				unset($saveNew[$k]);
			}
		}
		
		if(count($save)){
			foreach ($save as $k => $trans){    	
    			$where = $this->db->quoteInto(' ident = ? ', $k);    	
				$this->db->update(
					$this->_tableName,
					$trans,
					$where
				);
			}
		}
		
		if(count($saveNew)){
			foreach ($saveNew as $k => $trans){    
				$trans['ident']	= Utils::generatePathNameSimple(current($trans));
				$this->db->insert(
					$this->_tableName,
					$trans,
					$where
				);
			}
		}
	}
			
}


?>