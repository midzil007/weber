<?php

class Content
{    
	/**
	 * Globalni identifikátor obsahu
	 * @var int
	 * @access public
	 */
	var $id;
	
	/**
	 * Identifikátor obsahu v rámci nadřazeného uzlu.
	 * @var int
	 * @access public
	 */
	var $localId;
	
		
	/**
	 * Datum a čas vytvoření obsahu.
	 * @var string
	 * @access public
	 */
	var $dateCreate;
	
	/**
	 * Uživatelské jméno vlastníka (autora) obsahu.
	 * @var string
	 * @access public
	 */
	var $owner;
	
	/**
	 * Datum a čas poslední modifikace obsahu.
	 * @var string
	 * @access public
	 */
	var $dateModif;
	
	/**
	 * Uživatelské jméno uživatele, který jako poslední obsah modifikoval.
	 * @var string
	 * @access public
	 */
	var $modifiedBy; 
	
	/**
	 * Stav obsahu
	 * @var string
	 * @access public
	 */
	var $state; 
	
	/**
	 * Vlastnosti 
	 * @var array
	 * @access public
	 */
	var $properties = array(); 
	
	/**
	 * Způspb ukládáni
	 * @var boolean 
	 */
	var $serializable = false;
	
	
	public $searchableCols = array();	
	public $nodeTitle = 'title';
	public $vars = array('id', 'localId', 'dateCreate', 'owner', 'dateModif', 'modifiedBy', 'state', 'properties' );
    public $advertsPositions = array();
    	    
	public function getValues(){
		
		$data = array();
		foreach ($this->vars as $var){			
    		if($var == 'properties'){    	
    			if($this->serializable == true){		
    				continue;	
    			}
    			foreach($this->properties as $property){
    				$this->checkDbTableCols($property);   
    				if(isset($property->value)){
    					$data[$property->name] = $property->value;
    				}
    			} 
    		} else {
    			$data[$var] = $this->{$var};
    		}     		
    	}
    			
    	return $data;
	}
	    		
	public function setValues($data){
		//  pr($data);;
		$reg = Zend_Registry::getInstance();	
			
		foreach ($this->vars as $var){
    		if($var == 'properties'){
    			foreach($this->properties as $property){    		
    				//if($reg->languages->isMultiLanguage){
    					$property->value = $data[$property->name];
    				//}
    			}
    		} else {
    			$this->{$var} = $data[$var];
    		}    		
    	}
	}
		
	/*** TRANSLATION */
	
	public function initProperies()
    {
   		$translatedProperties = $this->getTranslatedProperties();
   		if(count($translatedProperties)){
   			$this->initTranslatedProperties($translatedProperties);
   		}
   		
    }
    
	function getTranslatedProperties(){
		
		$translatedProperties = array();
		$reg = Zend_Registry::getInstance();	
		if($reg->languages->isMultiLanguage){
			foreach($this->properties as $property){ 
				if($property->translate){
					$translatedProperties[$property->name] = clone $property;
				}
			}
		}
		return $translatedProperties;
	}
	
	function initTranslatedProperties($translatedProperties = false){
		$reg = Zend_Registry::getInstance(); 
		foreach ($translatedProperties as $property){
			foreach ($reg->languages->languagePrefixMap as $ident => $prefix){
				if($prefix){ // default uz mame
					if(!$this->getPropertyByName($prefix . $property->name)){				
						$tProp = clone $property;
						$tProp->name = $prefix . $tProp->name;
						$tProp->translate = false;
						$this->properties[] = $tProp;
					}
				}
			}
		}
	}
	
	
	/*** PROPERTIES */
	
	public function getProperties() {
		return  $this->properties;
	}
	
	public function getPropertyByName($name) {
		$reg = Zend_Registry::getInstance();
		
		foreach ($this->properties as $property){
			if($property->name == $name){
				if($property->translate && !$reg->languages->isDefaultLanguage){					
					$translatedName = $reg->languages->fullLangPrefix . $name;
					return $this->getPropertyByName($translatedName);
				} else {
					return $property;
				}
			}
		}
	}
	
	public function getPropertyByNameNotTranslated($name) {		
		foreach ($this->properties as $property){
			if($property->name == $name){				
				return $property;
			}
		}
	}
	
	public function getPropertiesByType($type) {
		$props = array();
	
		foreach ($this->properties as $property){
			if($property->type == $type){				
				$props[] = $property;
			}
		}
		return $props;
	}
	
	
	public function getPropertySelectValue($name) {
		$p = $this->getPropertyByName($name);
		return  $p->options[$p->value];
	}
	
	
	public function getPropertyValue($name) {
		return  $this->getPropertyByName($name)->value;
	}
	
	public static function getContentTypes() {		
		return  Zend_Registry::getInstance()->config->contentTypes->toArray();
	}
	
	public static function getContentTypesKeys() {		
		return array_keys(Zend_Registry::getInstance()->config->contentTypes->toArray());
	}
	
	public static function getOverviewTypes() {		
		return  Zend_Registry::getInstance()->config->overviewTypes->toArray();
	}
		
	public static function getOverviewAllowedContentTypes($allowedTypes){		
		$allTypes = self::getOverviewTypes();
		if(count($allowedTypes) && is_array($allowedTypes)){			
			foreach ($allowedTypes as $type){
				$allowedTypesWithNames[$type] = $allTypes[$type];
			}
			return $allowedTypesWithNames;
		} else {
			return $allTypes;
		}
	}	
		
	public function getContentTemplate() {
		 
		if($this->_template){
			$t = $this->_template;
		} else {
			$t = $this->getName();
		}
		return  $t . '.phtml';
	}
	
	public function getName() {
		return  substr($this->_name, 8);
	}
	
	function getNodeId() {	
		$dbAdapter = Zend_Registry::getInstance()->db;	
		$id = $dbAdapter->fetchOne('SELECT n_id FROM `NodesContents` WHERE `c_id` = ?', array($this->id));
		return $id;
	}
	
	function getNextContentId($dbAdapter = null) {	
		if(!$dbAdapter){
			$dbAdapter = Zend_Registry::getInstance()->db; 
		}
		$id = $dbAdapter->fetchOne('SELECT max( c_id ) + 2 FROM `NodesContents`');  
		// if($dbAdapter->fetchOne('SELECT max( c_id ) + 1 FROM `NodesContents`'))NodesContents
		return $id?$id:1;   
	}
	 
	function getNextContentLocalId($nodeId) {	
		$id = Zend_Registry::getInstance()->db->fetchOne('SELECT count(*) + 1  FROM `NodesContents` WHERE `n_id` = ?', array($nodeId));
		return $id?$id:1;
	}
	
	function checkDbTableCols($property){
		$reg = Zend_Registry::getInstance();		
		if($reg->languages->isMultiLanguage){
			$cols = helper_Database::getTableColumns($this->_name);
			     				
			if(!in_array($property->name, $cols)){									
				$sqlRow = $property->createTableRow(true);
				$after = substr($property->name, 3);
				helper_Database::addColl2($this->_name,  $after, $sqlRow);
			}
			
		}
	}
	
	function save(){
    	//$vars = get_class_vars('Content');
    	$this->insertData = $this->getValues();
    	if($this->serializable == true){
    		$this->insertData['data'] = serialize($this);
    		unset($this->insertData['properties']);    		
    	}
    	if(method_exists($this, 'onSave')){
    		$this->onSave();
    	}
    	//pr($data); pr($this); return ;
    	Zend_Registry::getInstance()->db->insert($this->_name, $this->insertData);
    	
    	if(method_exists($this, 'afterSave')){
    		$this->afterSave();    
    	}  
    	return Zend_Registry::getInstance()->db->lastInsertId();
    }
	
	function update($onlyData = false, $doNotLogTheChange = false, $view = false, $input = false){
    	//$vars = get_class_vars('Content'); 
    	
    	//pr($data); pr($this); return ;
    
    	if($doNotLogTheChange == false){
    		$this->dateModif = Utils::mkTime();
			$this->modifiedBy = Zend_Registry::getInstance()->session->user->username;
    	}
    	
    	
		
		$this->updateData = $this->getValues();   
		if(get_class($this) == 'content_Product'){
			if($input->state == 'ARCHIVED')
			{
				$this->updateData['dateArchived'] = (date('Y')+1).'-'.date("m-d H:i:s");
			}
			if($input->state == 'PUBLISHED')
			{
			$this->updateData['dateArchived'] = NULL;
			}
		}
    	
		if($this->state == 'DELETED'){
			if(method_exists($content, 'onDelete')){
				$content->onDelete($view, $input); 
			}				
		} else {
			if($onlyData != true && method_exists($this, 'onUpdate')){
	    		$this->onUpdate($view, $input);
	    	} 
		}
				
		if($this->serializable == true){
    		$this->updateData['data'] = serialize($this);
    	}
    	 
    	$state = Zend_Registry::getInstance()->db->update($this->_name, $this->updateData, 'id = ' . $this->id);
    	
    	if($this->state != 'DELETED'){
    		if(method_exists($this, 'afterUpdate')){
	    		$this->afterUpdate($view, $input);
	    	}  
    	} 
	    	 
    	return $state; 
    }
    
   
    /** admin render */
    
    function showAdmin($view){
    	$this->showAdminInit($view);    	
    	$this->renderAdmin($view);
    }
    
    function renderAdmin($view){
    	echo $view->render($this->template);
    }
	
    
    function showAdminInit($view){
    	$this->cnode = $view->cnode;
    	//$this->content = $view->content = $this->cnode->getPublishedContent();
    	//$this->content = $view->content;
    	$this->template = 'controls/admin/content/' . $this->getContentTemplate();	
    	$file = Zend_Registry::getInstance()->config->view->adminTemplatesFullpath . $this->template;
    	if(!(file_exists( SERVER_ROOT . $file) || file_exists( LIBS_ROOT . $file))){
    		$this->template = 'controls/admin/content/_StandartProperties.phtml';	
    	}
    }
    
	/* multi files */
	function getFilesNodes(){
    	
    }
        
    function getFiles($propertyName, $value = false){
    	if(!$value){
	    	$propertyName = $propertyName?$propertyName:'files';
	    	$fProperty = $this->getPropertyValue($propertyName);
    	} else {
    		$fProperty = $value;
    	}
    	
    	$files = array();
    	if(strlen($fProperty)){
    		if($fProperty{0} == ';'){
    			$fProperty = substr($fProperty,1);
    		}
    		
    		$files = explode(';', $fProperty);
    	}
    	return $files;
    }
    
    function getFilesNames($propertyName = '', $value = false){    	
		$propertyName = $propertyName?$propertyName:'files';
		$files = $this->getFiles($propertyName, $value);
		
		$filesNames = array();
		$isFile = true;
		$i=0;
		$conf = Zend_Registry::getInstance()->config;
    	foreach ($files as $file){
    		if($isFile){    			 			
    			$filesNames[$file] = '';
    			$isFile = false;
    		} else {
    			$filesNames[$prewFile] = $file; // file name
    			$isFile = true;
    		}
    		//$path = substr($file, strlen(Zend_Registry::getInstance()->config->sfFolder));
    		//$filesNames[$file] = Zend_Registry::getInstance()->tree->getNodeByPath($path, true)->title;
    		$prewFile = $file;
    	}
    		
    	//pr($filesNames);
    	return $filesNames;
    }
    
    /**
     * Vyrvori nahledy fotek - deprecated
     *
     */    
    function createPropertyImages($fotoFullName, $fotoThumbName, $fullWidth, $thumbWidth, $autosize, $propertyName = ''){	
    	$files = $this->getFilesNames($propertyName);				
    	//pr($files);				
		foreach ($files as $filePath => $fileName) {			
			$ext = content_SFSFile::getFileExtension($filePath);
			if($ext != 'jpg' && $ext != 'png'){
				continue;
			}
			
			$img = new Image(content_SFSFile::getSFSFullPath($filePath));
			$img->generateThumbnail($fotoFullName, $fullWidth, 0, $autosize);
			$img->generateThumbnail($fotoThumbName, $thumbWidth, 0, $autosize);
		}
    }
    
    /**
     * Vyrvori nahledy fotek 
     *
     */
    function createPropertyThumbs(array $imagesProperties, $propertyName = '', $value = false, $limmit = 0){	
    	$files = $this->getFilesNames($propertyName, $value);				    	 			
    	$x = 0;	
		foreach ($files as $filePath => $fileName) {			
			if($x >= $limmit && $limmit){ break; }  
			content_SFSFile::createFileThumbs($filePath, $imagesProperties);			
			$x++;
		}
    }
    
    function createGrayScaleImage($propertyName, $newImageName){	
    	$path = $this->getPropertyValue($propertyName);
    	content_SFSFile::createGrayScaleFile($path, $newImageName);	
    }
    
    function createGrayScaleImages($propertyName, $newImageName){	
    	$files = $this->getFilesNames($propertyName);		
		foreach ($files as $filePath => $fileName) {			
			content_SFSFile::createGrayScaleFile($path, $newImageName);		
		}
    }
    
     function createGrayScaleImagesFromResized($propertyName, $resizedName, $newImageName){	
    	$files = $this->getFilesNames($propertyName);	    	
    	//pr($files);	
    	
		foreach ($files as $filePath => $fileName) {	
			$i = new Image($filePath)		;
			//pr($i);
		//	e(content_SFSFile::getSFSFullPath($filePath));
    		//$resizedFullPath =  Zend_Registry::getInstance()->config->sfFolder . '/' . $nodeId . '/' . $resizedName . '-' . content_SFSFile::getFileExtension($n) . '/' . $n;
    		//e($resizedFullPath);
			//content_SFSFile::createGrayScaleFile($path, $newImageName);		
		}
    }
    
    
    
	
	/** HELPERS */
	public function templateExists(){
		$root = Zend_Registry::getInstance()->config->serverRoot . '/application/views/scripts/web/';
		if(strpos($this->_name, 'Overview')){
			$template = $this->getPropertyByName('pathToTemplate')->value . '.phtml';
			$exists = file_exists($root . Zend_Registry::getInstance()->config->view->overviewsDir . $template);
			
			
		} else {
			$template = $this->getContentTemplate();
			$exists = file_exists($root . Zend_Registry::getInstance()->config->view->contentsDir . $template);	
		}
		return $exists;    	
	}
	
	public function setValuesFromInput($input){
		
		foreach ($this->properties as $property){			
			$pname = $property->name;	
			$pname2 = 'fck_'.$property->name;
			if(!isset($input->$pname) && !isset($input->$pname2)){
				continue; 
			}
			//e($property->type);			
			switch ($property->type){
				case 'Wysiwyg':
					$pname =  $property->name;   
					$html = $input->$pname;  
					if($_SERVER['REMOTE_ADDR'] == '217.195.175.139'){
						$images = new Images($this); 
						$html = $images->resizeTextImages($html);  						
					}  
					 
					$property->value = Utils::getWYSIWYGHtml($html);
					 
					break;
				case 'FileSelect':
					if($input->$pname){
						if($input->$pname == 'del'){ 
							$property->value = '';
						} else {
							$property->value = trim($input->$pname);					
							$pname = $property->name.'_title';
							$property->value .= ';' . trim($input->$pname);
						}
						
						//e($input->$pname);
					}
					break;
				case 'CheckboxGroup':
					$property->value = helper_MultiSelect::setMultiSelectValues($input->$pname);
					break;
				case 'Chosen':        
					$property->value = implode("|", $input->$pname);
				
					break;
				case 'Number':
					$property->value = str_replace(',','.', $input->$pname);
					break;
				case 'TextSecured':
					if($input->$pname != ''){
						$property->value = Zend_Registry::getInstance()->encryption->encrypt($input->$pname);							
					}
					break;		
				case 'TextDate':
					list($d, $m, $y) = explode('.', $input->$pname);
					$property->value = "$y-$m-$d";	    
					break;			 		
				case 'Select':		 
				case 'ComboBox':		
					$property->value = ($input->$pname=='-1'?'':$input->$pname);		
					//e($property->value);
				default:
					$property->value = $input->$pname;	
					break;
			}
			$property->value = str_replace('; /data/sharedfiles', ';/data/sharedfiles', $property->value);
			 	
		}	
		//pr($this->properties);
		//exit();
		//Utils::debug($this->properties);
	}
	
	public function init($ctype, $input, $acl, $newVersion = false){		
		
		$session = Zend_Registry::getInstance()->session;
		$content = $contentName = 'content_'.$ctype;
		$content = new $content();			
    	
		//  Zend_Registry::getInstance()->
		$content->id = $content->getNextContentId();;
    	$content->localId = 1;
		$content->dateCreate = $content->dateModif = Utils::mkTime();
		if(!$session->user->username){
			$session->user->username = $session->webUser->username;
			if(!$session->user->username){
				$session->user->username = 'a'; 
			}
		}
		$content->owner = $content->modifiedBy = $session->user->username;
		
		 
		$state = 'NEW';
		if(!$newVersion && $acl){  
			if($acl->isAllowed($session->user->group, 'makePublishedContent')){
				$state = 'PUBLISHED';
			}			
		} else {
			
		}
		
		if($session->user->username == 'a'){
				$state = 'PUBLISHED';
			}   
		$state = 'PUBLISHED';  
		
		$content->state = $state; 					
		$input->author = $input->author?$input->author:$session->user->username; // redactors fix - no select
		$input->author = $input->author?$input->author:'a';
		$content->dateCreate = $content->dateModif = Utils::mkTime(); 
		 
		$content->setValuesFromInput($input);			
		//$content->copyTranslatedPropertiesToLAnguage($input, $node); 
		 
		if($input->saveAdverts){
			$content->setAdverts($input);	
		}  
		
		return $content;	
	}
	
	public function initUpdate($node, $input, $contentId = 0){		
		
		$session = Zend_Registry::getInstance()->session;
		
		/*
		$cache = Zend_Registry::getInstance()->cache;		
		if($cache){
			$cache->remove('node_' . $node->nodeId);
		}
		 
		*/   
	  
      
				$content   = $node->getTheRightContent();   
				//$content = $this->view->content  = $node->getTheRightContent();   
			
		
		
		   
		$content->dateModif = Utils::mkTime();
		$content->modifiedBy = $session->user->username;
				
		$input->author = $input->author?$input->author:$session->user->username; // redactors fix - no select
		
		if(method_exists($content, 'preSetValues')){
    		$content->preSetValues($this->view);    		
    	}
		$content->setValuesFromInput($input);	
				
		
		$content->copyTranslatedPropertiesToLAnguage($input, $node);
			
		if($input->saveAdverts){
			$content->setAdverts($input);	
		}  
		
		return $content;	
	}
	
	function copyTranslatedPropertiesToLAnguage($input, $node){
							
		// poprve zapnul dany uzel i pro jiny jazyk - zkopiruju obsah
		if(count($input->showInLanguages)){
			$reg = Zend_Registry::getInstance();	
			
			$translatedProperties = $this->getTranslatedProperties();
			foreach ($input->showInLanguages as $lang => $isOn){
				
				if($isOn && !isset($node->showInLanguages[$lang]) && $lang != $reg->languages->language){ // checme ho a jeste neni nastaveny 
					
					foreach ($translatedProperties as $property){						
						$curentLangPropertyName = $reg->languages->langPrefix . $property->name;
						$targetLangPropertyName = $reg->languages->getLangPrefix($lang) . $property->name;
						
						// copy
						$newVal = $this->getPropertyByNameNotTranslated($curentLangPropertyName)->value;						
						$this->getPropertyByNameNotTranslated($targetLangPropertyName)->value = $newVal;						
					}
				}
			}
		}			
	}
	
	public static function getNewContent($ctype)
	{  		
    	$ctype = $ctype?$ctype:'HtmlFile';		
		$c = 'content_'.$ctype;
		$c = new $c();						
		return $c;		
	}
	
	public static function getAllowedContentTypes($allowedTypes){
		$allTypes = self::getContentTypes();
		$allowedTypesWithNames = array();
		if(is_array($allowedTypes)){
			foreach ($allowedTypes as $type){
				$allowedTypesWithNames[$type] = $allTypes[$type];
			}
			return $allowedTypesWithNames;
		} else {
			return  array();
		}
	}
	
	/* references */
	function onSave() {
		$ref = new References($this);
		$ref->deleteReferences();
		$ref->insertReferences();
	}
	
	function onUpdate() {
		$ref = new References($this);
		$ref->deleteReferences();
		$ref->insertReferences();
		 
	}
	
	function onDelete() {
		$ref = new References($this);
		$ref->deleteReferences();
	}
	
	/**
	 * Opraví odkaz na stánku webu, pokud se změní
	 *
	 * @param string $oldPath
	 * @param string $newPath
	 */
	function fixNodePathInHtml($oldPath, $newPath){				
		foreach ($this->getPropertiesByType('Wysiwyg') as $property){
			$ntext = str_replace( '"'. $oldPath . '"', $newPath, $this->getPropertyByName($property->name)->value);	
			$this->getPropertyByName($property->name)->value = $ntext;
		}		
	}
	
	/**
	 * Vytvori SQL dotaz, ktery vytvoří tabulku pro daný content
	 *
	 */
	function createTable($createTable = false){
    	$create = "
			CREATE TABLE IF NOT EXISTS `" . $this->_name . "` (
				`id` int(9) NOT NULL auto_increment,
				`localId` varchar(9) NOT NULL,
				`dateCreate` timestamp NOT NULL default CURRENT_TIMESTAMP,
				`owner` varchar(150) NOT NULL,
				`dateModif` timestamp NOT NULL default '0000-00-00 00:00:00',
				`modifiedBy` varchar(150) NOT NULL,
				`state` varchar(50) NOT NULL,
    	";
    	//pr($this->properties);
    	foreach ($this->properties as $property){
    		$create .= $property->createTableRow() . "\n";
    	}
    	
    	$create .= "  
			  PRIMARY KEY  (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
		";
    	if($createTable){
    		Zend_Registry::getInstance()->db->query($create);
    	} else {
    		return $create;
    	}
    }
    
    /** ADVERTS */
    function getAdverts(){
    	return $this->advertsPositions;
    }
    
     function setAdvertsFromNode($adverts, $onlyActive = false){
     	 
     	if(!$this->advertsPositions && method_exists($this, 'initAdverts')){
     		$this->initAdverts();
     	}
     	
     	foreach ($this->advertsPositions as $i => $position){
     		$ident = $position->identificator;
     		$banners = $adverts[$ident];
     		//     pr($banners); 
			if($onlyActive && count($banners) && is_array($banners)){   
				foreach ($banners as $x => $banner){ 
					 
					if(!$banner->active){
						unset($banners[$x]);
					} 
				}
			}
			
     		if(is_array($banners)){
     			$position->setAdverts($banners);
     			$this->advertsPositions[$i] = $position;
     		}     		
     	}     	
    }
    
    function getAdvertsIdentificators(){
    	$ident = array();
    	foreach ($this->advertsPositions as $pos){
    		$ident[] = $pos->identificator;
    	}
    	
    	return $ident;
    }
    
    
    public function setAdverts($input){
    	$advertPos = new module_Advertising_AdvertPosition('save');
    	$adverts = $advertPos->initAllAdvertsFromInput($input);
    	$this->adverts = $adverts;	
	}
	
	/* DISCUSSION */
	
	function manageDiscussion($view){				
		$view->enableDiscussion = true;  
		if($view->enableDiscussionOld){
			$view->discussion = $discussion = new module_Discussion();
			$view->discussionThemeId = $discussion->discussionThemeId = $view->node->nodeId;  
			$discussion->initFE($view);  
		} else { 
			$view->discussion = $discussion = new module_DiscussionFinal($view, false, false);  
			$discussion->articleDiscussion = true; 
			$view->discussion->setDiscussionId($view->node->nodeId); 
			$view->discussion->setDiscussionTitle($view->node->title);
			$view->discussion->enableAjax(); 
			$view->discussion->initFE();    
		}
	}
	
	/* HELPERS */
	function getHtml($name = 'html'){
		return $this->getPropertyValue($name); 
	}
	 
}