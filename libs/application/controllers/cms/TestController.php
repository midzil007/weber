<?php
class Cms_TestController extends CmsController
{

	public function init()
	{
		parent::init();

	}
	
	public function indexAction($content = null)
	{
		//$content = 'tests.phtml';
		parent::indexAction($content);
	}
	
	public function t1Action()
	{
		$c1 = new content_HtmlFile();	
		$this->view->contentType = $c1->userName;		
		$this->view->contentProperties = $c1->properties;
		$this->indexAction('controls/admin/contentProperties.phtml');
	}
	
	
	public function languagesAction()
	{
		$languages = new module_languages_Languages();
		$languages->addLAnguage('en');
	}
	
	public function deleteUnpairedContentsAction()
	{
		$all = $this->db->fetchAll('select id, path, title from Nodes');
		foreach ($all as $node){			
			$ids[] = $node['id'];			
		}
		
		$all = $this->db->fetchAll('SELECT * FROM `NodesContents`');
		foreach ($all as $ncontent){			
			if(!in_array($ncontent['n_id'], $ids)){
				$delted[] = $ncontent;	
				$this->db->query('DELETE FROM `NodesContents`  WHERE `n_id` = ' . $ncontent['n_id']);	
			}
		}
		
		//pr($delted);
		/*
		foreach ($delted as $del){		
			//$contents = $this->db->fetchAll('SELECT * FROM `NodesContents` WHERE `n_id` = ?', $id);
			try {
				$this->db->query('DELETE FROM ' . $del['c_type'] . '  WHERE `id` = ' . $del['c_id']);
			} catch (Exception $e) {
    			e($e);
  			}
		}
		*/
		e('ok');
	}
	
	
	
	public function intranetAction()
	{
		$docs = $this->db->fetchAll("SELECT *
FROM `extranet_document`
WHERE `document_FOLDER` LIKE CONVERT( _utf8 'vnitřní směrnice'
USING cp1250 )
COLLATE cp1250_general_ci LIMIT 0,500");
		
		//pr($docs);
		foreach ($docs as $document){
break;
			$ext = Utils::getExtension($document['document_FILE']);
			$nPath = str_replace('.', '',substr($document['document_FILE'], 0, -4)) . '.' . $ext;
				
			$content = $contentName = 'content_SFSFile';
			$content = new $content();			
	    				
			$content->id = $content->getNextContentId($this->dbAdapter);;
	    	$content->localId = 1;
			$content->dateCreate = $document['CREATE_DATETIME'];
			$content->dateModif = $document['CHANGE_DATETIME'];
			$content->owner = $content->modifiedBy = $this->session->user->username;
			$content->state = 'PUBLISHED';
			$content->properties[0]->value = '/soubory-intranetu' . $nPath; 
			// pr($content); 
			$content->save();
			
			$n = new Node();
	    	$n->type = 'ITEM';
	    	$n->nodeId = $n->getNextNodeId($this->dbAdapter);
	    	$n->parentId = 345;	
	    	$n->dateCreate = $document['CREATE_DATETIME'];
			$n->dateModif = $document['CHANGE_DATETIME'];
			
	    	$n->owner = $n->modifiedBy = $this->session->user->username;
	    		    	
	    	$n->title = $document['document_DESCRIPTION'];
	    	
	    	$document['document_FILE'];
	    	$n->description = $document['document_DESCRIPTION'];				
	    	$n->path =  $content->properties[0]->value;
			$n->intranetAprroved = true;
			//pr($n);
			    	
	    	$this->tree->addNode($n);	   
	    	$this->tree->pareNodeAndContent($n->nodeId, $content->id, $contentName);
	    	
		}

		//e($fcontents);
		/*
		$content = $contentName = 'content_Overview';
			$content = new $content();
	    	
			$content->id = $content->getNextContentId($this->dbAdapter);;
	    	$content->localId = 1;
			$content->dateCreate = $content->dateModif = Utils::mkTime();
			$content->owner = $content->modifiedBy = $this->session->user->username;
			$content->state = 'PUBLISHED';
			//$content->properties[0]->value = 'Html';						
			$content->save();
			
		$n = new Node();
	    	$n->type = 'FILETREE';
	    	$n->sort = 'dateCreate';
	    	$n->title = 'Soubory';
	    	$n->description = '';
	    	$n->showInNavigation = '0';
	    	
	    	$n->path = '/';
	    	
	    	$n->nodeId = 2;
	    	$n->parentId = 0;	    	
	    	$n->dateCreate = $n->dateModif = Utils::mkTime();
	    	$n->owner = $n->modifiedBy = $this->session->user->username;	    	    	
	    	  		
	    	$this->tree->addNode($n);
	    	$this->tree->pareNodeAndContent($n->nodeId, $content->id, $contentName);
	   
		
		
		$this->tree->save('files');
		*/
		//$this->indexAction();
	}
	
	public function addNodeAction()
	{
		/*
		$content = $contentName = 'content_Overview';
			$content = new $content();
	    	
			$content->id = $content->getNextContentId($this->dbAdapter);;
	    	$content->localId = 1;
			$content->dateCreate = $content->dateModif = Utils::mkTime();
			$content->owner = $content->modifiedBy = $this->session->user->username;
			$content->state = 'PUBLISHED';
			//$content->properties[0]->value = 'Html';						
			$content->save();
			
		$n = new Node();
	    	$n->type = 'FILETREE';
	    	$n->sort = 'dateCreate';
	    	$n->title = 'Soubory';
	    	$n->description = '';
	    	$n->showInNavigation = '0';
	    	
	    	$n->path = '/';
	    	
	    	$n->nodeId = 2;
	    	$n->parentId = 0;	    	
	    	$n->dateCreate = $n->dateModif = Utils::mkTime();
	    	$n->owner = $n->modifiedBy = $this->session->user->username;	    	    	
	    	  		
	    	$this->tree->addNode($n);
	    	$this->tree->pareNodeAndContent($n->nodeId, $content->id, $contentName);
	   
		
		
		$this->tree->save('files');
		*/
		$this->indexAction();
	}
	
		
	
	/*** IMPORT */
	
	function getContetns($url){
		$contents = file_get_contents($url);
		$contents = str_replace('>', '>%%', $contents);
		return explode('%%', $contents);
	}
	
	function getFileContetns(){
		$contents = Utils::readFromFile($this->config->dataRoot . '/html.txt');
		$contents = str_replace('>', '>%%', $contents);
		return explode('%%', $contents);
	}
	
	function getOverPhoto($path){
		list($name, $ext) = explode('.', $path);
		$imgSrc = '/data/Generated/Productselection/' . $name . '.jpg';
		return $imgSrc;
	}
	
	function getKitchenLinks($url){
		$links  =  array();
		$contents = $this->getFileContetns($url);
		$detailsLinkPatern = '/<a(.*)/i';		
		//$detailsLinkPatern = '/<(a.*) href="(.*?)"(.*)>(.*)(<\/a>)/';		
		
		foreach ($contents as $piece){	
			if(preg_match($detailsLinkPatern, $piece, $matches)) {
			
				if(strpos($matches[0], 'onmouseout="mOut')){
					$href = preg_match('/(href)\s*=\s*"([^"]*)"/i', $matches[1], $link);
					$links[] = array(
						$link[2],
						$this->getOverPhoto($link[2])
					);
				}
			}
		}
		return $links;
	}
	
	function getKitchenDetails($url){
		$links  =  array();
		$contents = $this->getContetns($url);
		$mainImagePatern = '/<img (src)\s*=\s*"([^"]*)"(.*)/i';	
		$detailLinkPatern = '/<a (href)\s*=\s*"([^"]*)"(.*)/i';
		//$detailsLinkPatern = '/<(a.*) href="(.*?)"(.*)>(.*)(<\/a>)/';		
		
		foreach ($contents as $piece){	
			if(preg_match($mainImagePatern, $piece, $matches)) {
				if(strpos($matches[3], 'width="953"')){
					//pr($matches);
					$Imgurl = $matches[2];
					break;
				}
			}
		}
		
		foreach ($contents as $piece){	
			if(preg_match($detailLinkPatern, $piece, $matches)) {
				if(strpos($matches[3], 'title="INFO')){
					$detailsUrl = current(explode(';',$matches[2]));
					break;
				}
			}
		}
		
		return array(
			'img' => $Imgurl,
			'detailsUrl' => $detailsUrl
		);
	}
	
	function getKitchenInfoImage($url){
		$contents = $this->getContetns($url);
		$mainImagePatern = '/<img (src)\s*=\s*"([^"]*)"(.*)/i';	
		
		foreach ($contents as $piece){	
			if(preg_match($mainImagePatern, $piece, $matches)) {
				if(strpos($matches[3], 'width="538"')){
					//pr($matches);
					$Imgurl = $matches[2];
					break;
				}
			}
		}
		return $Imgurl;
	}
	
	function getKitchenInfoDoors($url){
		$contents = $this->getContetns($url);
		$imagePatern = '/<img (src)\s*=\s*"([^"]*)" (id)\s*=\s*"([^"]*)" (.*)/i';	
		$namePatern = '/front\[([^"]*)(.*)/i';
		
		///data/Image/Produktfarben/Full/Tueren/NX/GR80-L100G-G935.jpg
		//<a href="javascript:fw('c0')"><img src="/data/Image/Produktfarben/Thumb/Fronten/NL/NL501-L111G.jpg" id="c0" style="border: 1px solid rgb(211, 228, 245);" border="0" height="72" width="50"></a>

		$doors = array();		
		foreach ($contents as $piece){	

			if(preg_match_all($namePatern, $piece, $matches)) {
				if(count($matches[2]) && $matches[2][0] ){
					$names = $matches[2];
				}
			}	
			
					
			if(preg_match($imagePatern, $piece, $matches)) {
				if(strpos($matches[0], '/Produktfarben/Thumb/')){
					$full = str_replace('/Produktfarben/Thumb/', '/Produktfarben/Full/', $matches[2]); 
					$doors[$full] = array(
						'thumb' => $matches[2],
						'full' => $full,
						'id' => $matches[4]
					);
				}
			}
		}
		if(count($names)){
			
			array_pop($names);
			$i = 0;
			$nNames = array();
			foreach ($names as $name){
				$name = substr(trim(str_replace('"', '', $name)), 0, -1);
				$i++;
				if($i%2 == 0){
					if($nNames[$name]){
						$name .= '-2';
					}
					$nNames[$name] = $old;
					continue;
				}
				$old = $name;
			}
			
			$nNames = array_flip($nNames);
			//pr($nNames);
			foreach ($nNames as $key => $name){		
				if(count($doors[$key])){
					$doors[$key]['name'] = $name;
				}
			}
		}
		return $doors;
	}
	
	function getKitchenInfoGlass($url){
		return $this->getKitchenInfoDoors($url);
	}
	
	function getKitchenInfoHolds($url){
		return $this->getKitchenInfoDoors($url);
	}
	
	function getKitchenInfoSurfaces($url){
		return $this->getKitchenInfoDoors($url);
	}
	
	function getKitchenInfo($url){
		$info  =  array();		
		$kitchenDetailsUrl = substr($url, 0, -4);		
		
		for($i = 1; $i < 7; $i++){
			$info['details'][] = $this->getKitchenInfoImage($url . '?img=b' . $i);
		}
		$info['doors'] = $this->getKitchenInfoDoors($kitchenDetailsUrl . '/fronten.htm');		
		$info['glass'] = $this->getKitchenInfoGlass($kitchenDetailsUrl . '/glaeser.htm');
		$info['holds'] = $this->getKitchenInfoHolds($kitchenDetailsUrl . '/griffe.htm');
		$info['surfaces'] = $this->getKitchenInfoSurfaces($kitchenDetailsUrl . '/arbeitsplatten.htm');
				
		return $info;
	}
	
	function schuellerAction(){
		$startTime = Utils::getMicrotime();
		
		return ;
		
		// nextLine
		$this->www = 'http://www.schueller.de';
		//$this->base = '/de/produkt/nextline/';
		$this->base = '/de/produkt/next125/';
		//$this->base = '/de/produkt/creativ/';
		
		
		$this->importData = array();
		$this->kCount = 1;
		
		/*
		$this->importData = unserialize(Utils::readFromFile($this->config->dataRoot . '/temp.txt'));
		//pr((($this->importData)));		
		$this->importData();
		return ;
		*/
		
		$url = 'produktauswahl.htm';
		$kitchenLinks = $this->getKitchenLinks($this->www . $this->base . $url);
			
				
		foreach ($kitchenLinks as $link){
			$link[0] = str_replace($this->base, '', $link[0]);	
			$details = $this->getKitchenDetails($this->www . $this->base . str_replace($this->base, '', $link[0]));			
			$details['info'] = $this->getKitchenInfo($this->www . $this->base . str_replace($this->base, '', $details['detailsUrl']));
			$this->importData[$link[0]] = array(
				'overImage' => $link[1],
				'details' => $details
			);				
		}

		pr($this->importData);
		
		Utils::writeToFile(serialize($this->importData), $this->config->dataRoot . '/temp.txt');
		
		
		$processTime = round(Utils::getMicrotime() - $startTime, 4);
		e('vygenerovano za: ' . $processTime . ' s');	
	}
	
	function importData(){
		//$nodeAddTo = $this->config->instance->cz->productsNext125x2NodeId;		
		$nodeAddTo = $this->config->instance->cz->productsNext125x1NodeId;		
		$this->kitchenPicturesNodeID = 2046;
		
		$this->debug = false;
		$this->creative = false;
		
		$this->importedImages = array();
		
		foreach ($this->importData as $kitchenName => $data){			
			
			
			$kitchenTitle = substr($kitchenName, 0 , -4);
			$input = new stdClass();
			$input->pageTitle  = $kitchenTitle;
			
			$detailsData = $data['details'];
			$info = $detailsData['info'];
			
			//e($kitchenTitle);
			$this->createKtchenFileFolder($kitchenTitle);
			
			//pr($info);
			
			if($this->creative){
				$overImage = $this->importImage('/data/My/romantik/overviewPhoto' . $this->kCount . '.jpg');			
			} else {			
				$overImage = $this->importImage($data['overImage']);		
			}
			
			
			$photo = $this->importImage($detailsData['img']);			
			$details = $this->importDetails($info['details']);
			$doors = $this->importItems($info['doors'], $this->config->instance->cz->detailsDoorsNodeId, $this->config->instance->cz->detailsImagesDoorsNodeId);			
			$glasses = $this->importItems($info['glass'], $this->config->instance->cz->detailsGlassNodeId, $this->config->instance->cz->detailsImagesGlassNodeId);
			$holds = $this->importItems($info['holds'], $this->config->instance->cz->detailsHoldersNodeId, $this->config->instance->cz->detailsImagesHoldersNodeId);
			$materials = $this->importItems($info['surfaces'], $this->config->instance->cz->detailsMaterialsNodeId, $this->config->instance->cz->detailsImagesMaterialsNodeId);
			
			
			// node
			$newNode = Node::init('ITEM', $nodeAddTo, $input, $this->view);
						
			//content    	
			$ctype = 'Kitchen';
			$input = new stdClass();
			
			
			$content = Content::init($ctype, $input, $this->acl);	
			
			$content->getPropertyByName('overviewPhoto')->value = $overImage;
			$content->getPropertyByName('photos')->value = $photo;
			$content->getPropertyByName('details')->value = $details;
			$content->getPropertyByName('doors')->value = $doors;
			$content->getPropertyByName('glasses')->value = $glasses;
			$content->getPropertyByName('materials')->value = $materials;
			$content->getPropertyByName('holders')->value = $holds;
			$content->getPropertyByName('special')->value = '';
			
			pr($content);
			$this->save($newNode, $content); // !!!!!!!
			
			$this->kCount++;
		}	
		if(!$this->debug){
			$this->tree->save('files'); // !!!!!!!
			$this->tree->save('structure'); // !!!!!!!
		}
	}
	
	function createKtchenFileFolder($kname){
		$input = new stdClass();
		$input->pageTitle  = $kname;
		$newNode = Node::init('FOLDER', $this->kitchenPicturesNodeID, $input, $this->view);
		
		if(!$this->debug){
			$this->tree->addNode($newNode, false, false); // !!!!!!!
			parent::audit($newNode->title, $newNode->nodeId); // !!!!!!!
		}
		
	   	$err = Folder::makeFolder($this->config->fsRoot . $newNode->path );
	   	
	   	$this->fileFolderNodeId = $newNode->nodeId;
	   	$this->fileFolderPath = $newNode->path;
	}
	
	function importImage($image, $addTo = 0){
		if(in_array($image, $this->importedImages)){
			return false;
		} else {
			$this->importedImages[] = $image;
		}
		
		
		if($image){
			
			$addTo = $addTo?$addTo:$this->fileFolderNodeId;
			
		   	$fullpath = $this->move($image, $this->fileFolderPath);
		   	if(!$fullpath){
		   		return '';
		   	}
		   	
			$input = new stdClass();
			$input->pageTitle = content_SFSFile::getFileWithouExtension(content_SFSFile::getFileName($fullpath));
			
			$newNode = Node::init('ITEM', 	$addTo, $input, $this->view);
			$newNode->path = $fullpath;
			$ctype = 'SFSFile';		
			$content = Content::init($ctype, $input, $this->acl);
			$content->properties[0]->value = stripslashes($fullpath);
			
			$this->save($newNode, $content); // !!!!!!!
			return $this->config->sfFolder . '/' . content_SFSFile::getSFSPath($newNode->nodeId, $newNode->path) . ';' . $newNode->title;
		}
	}
	
	function importDetails($details){
		$d = array();
		foreach ($details as $dImage){
			if($dImage){
				$d[] = $this->importImage($dImage);
			}
		}
		return implode(';',$d);
	}
	
	function importItems($items, $folderToImport, $fileFolderToImport){

		$i = array();
		$i[] = 0;
		if(count($items)){
			foreach ($items as $image => $item){		
				
				$propertyValue = $this->importImage($image, $fileFolderToImport);
						
				$val = $this->addItem($item, $folderToImport, $propertyValue);
				if($val){
					$i[] = $val;
				}
			}
		}
		
		return helper_MultiSelect::setMultiSelectValues(array_flip($i));
	}
	
	function addItem($item, $folderToImport, $propertyValue){
		
		//e($item);
		
		$input = new stdClass();
		$input->pageTitle = $item['name'];			
		$newNode = Node::init('ITEM', $folderToImport, $input, $this->view);
			
		if(strpos($newNode->path, '_2')){
			$newPath = substr($newNode->path, 0 , -2);			
		} else {
			$newPath = $newNode->path;
		}
		
		$exists = $this->tree->getNodeByPath($newPath);
	
		if(!$exists){
			
			
			//e($image . ' - '. $fileFolderToImport);
			
			//content    	
			$ctype = 'KitchenItem';
			$input2 = new stdClass();
						
			$content2 = Content::init($ctype, $input, $this->acl);	
			$content2->getPropertyByName('photo')->value = $propertyValue;
			
			//e('new - ' . $newNode->title . ' - nodeId = ' . $newNode->nodeId);
			
			$this->save($newNode, $content2); // !!!!!!!
			return $newNode->nodeId;
		} else {
			//e('exists - ' . $exists->title . ' - nodeId = ' . $exists->nodeId);
			return $exists->nodeId;
		}
	}
	
	function move($target, $destionation){	
				
		if(!content_SFSFile::isMovable($target)){ // !!!!!!!
			$sfRoot = $this->config->fsRoot;
			$entry = content_SFSFile::getFileName($target);
			
			$entry = Utils::generatePathName($entry,'.-_', $destionation . '/');
			$entry = str_replace('_', '-', $entry);
			
			//e('!!!! - ' . $sfRoot . $target . '  x-x ' . $sfRoot . $destionation . '/' . $entry);
			
			copy( $sfRoot . $target, $sfRoot . $destionation . '/' . $entry ); // !!!!!!!
			if(!$this->debug){
				
			}
			return $destionation . '/' . $entry;
		} else {
			e('!!! ' . $target);
		}
	}
	
	function save($newNode, $content){		
		if($this->debug){
			return;
		}
		$err2 = $content->save();	
    	$this->tree->addNode($newNode, false, false);
    	$this->tree->pareNodeAndContent($newNode->nodeId, $content->id, $content->_name);    		    	
    	parent::audit($newNode->title, $newNode->nodeId);
	}
	
	function addMembersAction(){
    	$cm = new content_ClubMember();
    	//$cm->importUsers2($this->view, 'all-new.csv');
    	$cm->importUsers2($this->view, 'karernici.csv');
    	
    	//$cm->importUsers($this->view, 'cz_f.csv');
    	//$cm->importUsers($this->view, 'sk_m.csv');
    	//$cm->importUsers($this->view, 'sk_f.csv');
    	
    	//$cm->saveHairDressers($this->view);
    	//!!!!!! nastavit gender + zemi
    	e('ok');
    	die();
	}
	
}
