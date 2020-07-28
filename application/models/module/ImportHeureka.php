<?php
header('Content-Type: text/html; charset=utf-8'); 

class module_ImportHeureka
{  
	
	 
	function __construct($view){ 
		$this->db =  Zend_Registry::getInstance()->db;
		$this->config =  Zend_Registry::getInstance()->config; 
		$this->view = $view; 
		$this->tree =  Zend_Registry::getInstance()->tree;  
		$this->_tableProductKategorie = 'eshop_kategorie';
		$this->_tableNameCounterCron = 'module_eshop_couterCron';
	}
	  
	
	function incPosition($clean = false)
	{
		$position = 0;
		$position = $this->getPosition();
		$position += 1;
		$this->db->delete($this->_tableNameCounterCron);
		if(!$clean)
			{
			$data = array(
				'position' => $position);
			$this->db->insert($this->_tableNameCounterCron, $data);
			}
	}
	 
	
	function getPosition()
	{
		$position = $this->db->fetchOne("select position from ".$this->_tableNameCounterCron);
		if(!$position){$position = 0;}
		return $position;
	}
	

	
	function import($category = false,$start = 1, $end = 10){ 

   		//$file = $this->config->dataRoot . '/zbozi.xml';
   		$path = 'http://jura.jablum.cz/product.xml';
   		$data = $this->loadXML($path );
   		if($category)
   		{
   			$this->unsetSkladem();
   			$this->importCategory($data);
   			die();
   		}  		
		$start = $this->getPosition();
		$end = $start + 1;
		$dataGrouped = $this->groupProductsData($data,$start , $end);
		$this->addProducts($dataGrouped); 
		$this->incPosition();
	}
	
	
	function importCategory($data)
	{
		foreach ($data as $value) {
			$cat = explode(' | ', $value->category);
			$inc = 1;
			$parent = false;
			foreach ($cat as $key=>$val) {
				$parent = $this->isCategoryExist($val);
				if(!$parent && $val)
					{
						$this->addNewCategory($val,$cat[$key-1]);
					}
				}
			}
	}
	
	function addNewCategory($category,$parent = false)
	{
		$parentNode = 3801;
		$inputContent = new stdClass();
		if($parent)
		{
			$parentNode = $this->isCategoryExist($parent);
		}
		$input = new stdClass();
		$input->pageTitle = $category;
		$input->parent = $parentNode;
		$input->parentId = $parentNode;
		$ctype = 'OverviewProducts';
		$newNode = Node::init('FOLDER', $parentNode, $input, $this->view);
		$content = Content::init($ctype, $inputContent, false);
		$err2 = $content->save();	 
    	$this->tree->addNode($newNode, false, false); 
    	$this->tree->pareNodeAndContent($newNode->nodeId, $content->id, $content->_name);
	}
	
	function unsetSkladem()
	{
		$skladem = array('skladem' => '-1');
		$this->db->update('content_Product',$skladem);
	}
	
	function getProductByName($name){
		$p =  $this->db->fetchRow("
			SELECT `cm`.`id` AS `cid`, `n`.`id`, `n`.`title`, `n`.`path`
			
			FROM `content_Product` AS `cm` 
				INNER JOIN `NodesContents` AS `nc` ON cm.id = nc.c_id 
				INNER JOIN `Nodes` AS `n` ON n.id = nc.n_id 
				
			WHERE (c_type = 'content_Product' AND `title` = ?)    
			",
			$name
		);
		$product = false;
		if($p['id']){
			$product = $this->tree->getNodeById($p['id']);
		}
		return $product;
	}
	
	public function getCountSkladem($extId)
	{
		return $this->db->fetchOne('select sklademKs from '.$this->_tableNameSkladem.' where ident =?', $extId);
	}
	
	
	private function updateSimple($variant){
			$nodeToUpdate = $this->getProductByName($variant->title);
			if($nodeToUpdate){
			$contentToUpdate = $nodeToUpdate->getPublishedContent();
			if(is_object($contentToUpdate)){
				
			
			//$oldPhoto = $contentToUpdate->getPropertyByName('photos')->value;
			
			$inputContent = new stdClass();
			$input = new stdClass();
			$section = '';
			list($vimg,$mainText,$alternative) = $this->getPropertiesFromWeb($variant->url,$variant->image);
			// dočasně
			
			$delete = '<p>
	Kompletní nabídku této exkluzivní kávy naleznete na stránkách <a href="http://www.jablum.cz" target="_blanc"><strong> www.jablum.cz</strong></a><strong>.</strong></p>';
			//$inputContent->fck_html = $inputContent->html = str_replace($delete ,'',$mainText);
			$section = $this->getParentCategory($variant->category);
			//$inputContent->fck_html = $inputContent->html = $mainText;
			$inputContent->parentSection = $section;
			$inputContent->author = 'a';
			$sk = 1;
			if($variant->dostupnost == 0)
			{
				$sk = 1;
			}
			elseif($variant->dostupnost == '-1')
			{
				$sk = 3;
			}
			$inputContent->skladem = 1;
			$inputContent->preText= $variant->description;
			$inputContent->souvisejici = $alternative; 
			$inputContent->prodejny = 1;
			$inputContent->extId =  $variant->extId; 
			$inputContent->price  = $variant->price;
			$inputContent->dphQuote = 21;
			$dif = $variant->price - $variant->priceBezDPH;
			if($dif){
				$inputContent->dphQuote = round(($dif/$variant->priceBezDPH) * 100);
			}
			
			
			$inputContent->price2  = $variant->price2>$variant->price?$variant->price2:'';
			$inputContent->EAN = $variant->ean;
// 			if(count($vimg)){
// 					$i = array();
// 					foreach ($vimg as $ii){
// 						$im = $this->importImage($ii,$oldPhoto);
// 						if($im)
// 						$i[] = $im; 
// 					}
// 					$inputContent->photos = implode(';', $i);
// 				}	
						
			foreach ($inputContent as $k => $val){   
				$contentToUpdate->getPropertyByName($k)->value = $val; 
			}

				$this->updateNodesAction($nodeToUpdate, $contentToUpdate, $inputContent);
			} 
			}
	}
			
			
	function sendEmail($title)
	{
		$mail = new Email();
		$mainText = 'Nový produkt na www.shop-jura.cz '.$title;
		$emailText = Helpers::prepareEmail(
			$mail, 
			$mainText, 
			false, 
			false,
			'484848',  
			'000000'     
		); 
					
		$mail->setBodyText(strip_tags(urldecode($mainText)));
		$mail->setBodyHtml(urldecode($emailText));			
		$mail->setSubject('Nový produkt na www.shop-jura.cz '.$title);
		$mail->addTo('nosil@eportaly.cz');
		$mail->setFrom('nosilac@seznam.cz');
		$mail->send();
	}
			
	public function hasColor($value,$categoryId)
	{
		$color =  $this->db->fetchOne('select cid from '.$this->_tableNameColors.' where ctitle=?',$value);
		if(!$color)
		{
			$data = array(
				'ctitle' => $value);
				$this->db->insert($this->_tableNameColors, $data);
		}
		$color =  $this->db->fetchOne('select cid from '.$this->_tableNameColors.' where ctitle=?',$value);
		$data = array(
				'idCategory' => $categoryId,
				'idColor' => $color);
				$this->db->insert($this->_tableNameCategoryColors, $data);
		return $color;
	}

	
	
	
	private function checkKategorie($string)
	{
		//Materiál|transparentní polykarbonát s ocelovou chromovanou základnou; Barva|purpurová; Určeno:|vhodné pro interiér;
		if($string)
		{
			 
			$temp = explode(';', substr($string, 0,-1));
			array_pop($temp);
			foreach ($temp as $value) {
				$kat = explode('|', $value);
				$kategorie = str_replace(' ', '', $kat[0]);
				$kategorie = trim(preg_replace('/\s+/', ' ', $kategorie));
				
				if($kategorie == 'Určeno:')
				{
					$kategorie= 'Určeno';
				}
				$vlastnost = $kat[1];
				if(strlen($kategorie)>2){
					$isExist = $this->db->fetchRow('select * from '. $this->_tableProductKategorie.' where nazevKategorie =?',$kategorie);
					//pr($isExist);
					if(!$isExist)
					{
						$data = array('nazevKategorie' => $kategorie , 'multiOption' => '0');
						$this->db->insert($this->_tableProductKategorie, $data);
						$isExist = $this->db->fetchRow('select * from '. $this->_tableProductKategorie.' where nazevKategorie =?',$kategorie);
					}
					$insert = true;
					
					for ($i = 1; $i < 26; $i++) {
						// test na barvy
						if($kategorie == 'Barva'){
							$color = $vlastnost;
						}
						if($kategorie == 'Určeno'){
							$urceno = $vlastnost;
						}
						
						if($vlastnost == $isExist['nazevVlastnost_'.$i])
						{ 							
							$insert = false;							
							$retKat[$isExist['id']] = $i;
							break;
						}
					}
					
					if($insert){
					//	pr($isExist);
						for ($i = 1; $i < 26; $i++) {
							if(!$isExist['nazevVlastnost_'.$i])
							{
								$position = $i;
								break;
							}
						}
						$retKat[$isExist['id']] = $position;
						//pr($vlastnost);
						$data = array('nazevVlastnost_'.$position => $vlastnost);
						$where = $this->db->quoteInto('id = ?', $isExist['id']);
						$this->db->update($this->_tableProductKategorie,$data,$where);
						}
					}
				}
			}
		return array($retKat,$color,$urceno);
	}
	
	private function hasSize($value,$categoryId)
	{
		$color =  $this->db->fetchOne('select sid from '.$this->_tableNameSizes.' where stitle=?',$value);
		if(!$color)
		{
			$data = array(
				'stitle' => $value);
				$this->db->insert($this->_tableNameSizes, $data);
		}
		$color =  $this->db->fetchOne('select sid from '.$this->_tableNameSizes.' where stitle=?',$value);
		$data = array(
				'idCategory' => $categoryId,
				'idSize' => $color);
				$this->db->insert($this->_tableNameCategorySizes, $data);
		return $color;
	}
	
	
	private function getPropertiesFromWeb($url,$mainImg)
	{
		$img[] = $mainImg;
		$html = implode('', file($url));
		$main = explode('jcarousel-skin-tango', $html);
		$mainR = explode('</div>', $main[1]);
		$mainR1 = explode('<img src="', $mainR[0]);
		unset($mainR1[0]);
		foreach ($mainR1 as $value) {
			$imgTemp = explode('"', $value);
			$img[]= str_replace('smallfiles', 'files', $imgTemp[0]);
		}
		$text = explode('productDetail">', $html);
		$text2 = explode('<div class="cleaner"></div>
		</div>', $text[1]);
		$text22 = explode('</div>',$text2[1]);
		$mainText = $text22[0];
		$mainText = str_replace('<font size="3">', '', $mainText);
		$mainText = str_replace('</font>', '', $mainText);
		
		$alt = explode('Zobrazit detail produktu">', $text2[0]);
		unset($alt[0]);
		$alternativeProducts = $alt;
	
		foreach ($alternativeProducts as $value) {
			
			if(is_numeric(strpos($value,'</a></h3>'))){
				$altrer = explode('</a></h3>', $value);
				if($this->getProductByName($altrer[0]) && $altrer[0])
				{
					$node = null;
					$node = $this->getProductByName($altrer[0]);
					$alter[] = $node->nodeId;
				}
			}
		} 
		$alterinative = implode('|', $alter);
		$mainText = preg_replace("/<img[^>]+\>/i", " ", $mainText);
		return array($img,$mainText,$alterinative );
	}
	
	
	
	private function addNewSimple($variant){
			$inputContent = new stdClass();
			$input = new stdClass();

			$input->pageTitle = $variant->title;
			$this->sendEmail($variant->title);
			// $section = '';
			// list($vimg,$mainText,$alternative) = $this->getPropertiesFromWeb($variant->url,$variant->image);
// 		
			// $section = $this->getParentCategory($variant->category);
			// $delete = '<p>
	// Kompletní nabídku této exkluzivní kávy naleznete na stránkách <a href="http://www.jablum.cz" target="_blanc"><strong> www.jablum.cz</strong></a><strong>.</strong></p>';
			// $mainText = str_replace($delete ,'',$mainText);
			// $inputContent->fck_html = $inputContent->html = preg_replace("/\<a([^>]*)\>([^<]*)\<\/a\>/i", "$2", $mainText);
			// $inputContent->parentSection = $section;
			// $inputContent->author = 'a';
			// $sk = 1;
			// if($variant->dostupnost == 0)
			// {
				// $sk = 1;	
			// }
			// elseif($variant->dostupnost == '-1')
			// {
				// $sk = 3;
			// }
			// $inputContent->skladem = 1;
			// $inputContent->souvisejici = $alternative; 
			// $inputContent->preText= $variant->description;
			// $inputContent->prodejny = 1;
			// $inputContent->extId =  $variant->extId; 
			// $inputContent->price  = $variant->price;
			// $inputContent->price2  = $variant->price2>$variant->price?$variant->price2:'';	
			// $inputContent->EAN = $variant->ean;
			// $inputContent->dphQuote = 21;
			// $dif = $variant->price - $variant->priceBezDPH;
			// if($dif){
				// $inputContent->dphQuote = round(($dif/$variant->priceBezDPH) * 100);
			// }
			// //if(count($vimg)){
				// //	$i = array();
					// //foreach ($vimg as $ii){
						// //$i[] = $this->importImage($ii);
					// //}
					// //$inputContent->photos = implode(';', $i);
			// //	}
			// //pr($inputContent);
			// $this->addNodesAction(3801, $input, $inputContent);
			// return true;
	}
	
	
	
	function addNodesAction($nodeAddTo, $input, $inputContent, $ctype = 'Product'){  
		$newNode = Node::init('ITEM', $nodeAddTo, $input, $this->view);	 
		$content = Content::init($ctype, $inputContent, false);	
		$content->getPropertyByName('parent')->value = $inputContent->parentSection;
		$content->getPropertyByName('photos')->value = $inputContent->photos;  
		$this->save($newNode, $content, $inputContent);
		
	}
		
	
	private function productExistsByFcode($name){
		e($name);
		$e =  $this->db->fetchOne(
			"SELECT id FROM `Nodes` WHERE `title` = ?",
			$name
		);
		return $e > 1;
	} 
	
	
	function addProducts($dataGrouped){		
		
		foreach ($dataGrouped as $variants){
				if($this->productExistsByFcode($variants->title)){
						//$this->updateSimple($variants);
				}
				else {					
						$this->addNewSimple($variants);
				}     
		}
	}
	
	
	
	function importImage($image,$oldPhotos, $returnFullPath = 10){ // 3880

		$addTo = 3880;				 
		if(in_array($image, $this->importedImages)){ 
			return $this->importedImages[$image];  
		} else {
			
		}		
		$config = $this->config;     
		if($image){
			$contents = file_get_contents($image);
			$imageName = str_replace('http://jura.jablum.cz/files/product/', '', $image);
			$imageName = str_replace('http://jura.jablum.cz/files/product_photo/', '', $imageName); 
			if($oldPhotos)
			{
				$od = explode(';', $oldPhotos);				
				$end = count($od)/2+1;
				$pos = 1;
					for ($i = 1; $i < $end ; $i++) {
						if($od[$pos].'.jpg' == $imageName && $od[$pos-1]){
							$this->importedImages[$od[$pos-1]] = $od[$pos];
							$path = $od[$pos-1].';'.$od[$pos];
							continue;
						}
						$pos+=2;
					}					
			}
			else{
			$filepath = $this->config->fsRoot .'/obrazky/produkty/'. $imageName;
			file_put_contents($filepath, $contents);

			$view = $this->view; 
			$view->input = new stdClass();
			$view->input->fullpath = substr($filepath, strlen($this->config->fsRoot));			
			$view->input->state = 'PUBLISHED';			
			$view->input->owner = 'a';	
 			  
			$file = helper_Nodes::initContent('SFSFile', $view->input, $view);	  
			if($file->getPropertyValue('fullpath')){
				$nnode = helper_Nodes::addNodeWithContent($file, $addTo, $imageName, $view, false, true);
			}
			
			if($returnFullPath === true){
				$path =  $filepath; 
			} elseif ($returnFullPath === 5){
				$path =  $config->sfFolder . '/' . content_SFSFile::getSFSPath($nnode->nodeId, $nnode->path); 
			} elseif ($returnFullPath === 10){   
				$path =  $config->sfFolder . '/' . content_SFSFile::getSFSPath($nnode->nodeId, $nnode->path) . ';' . content_SFSFile::getFileWithouExtension($nnode->title);  
			} else {			  
				$path =  $file->getPropertyValue('fullpath');    
			}
			
			$this->importedImages[$image] = $path;
			}
			return $path?$path:'';  
		}
	}
	
	
	
	
	private function isCategoryExist($title)
	{
		return $this->db->fetchOne("select id from Nodes as n, NodesContents as c where n_id = n.id and c_type = 'content_OverviewProducts' and title =?",$title);
	}
	
	
	private function getParentCategory($string)
	{
		$cat = explode('|', $string);
		$dbCat = array();
		foreach ($cat  as $value) {
			$dbCat[] = $this->db->fetchOne("select id from Nodes as n, NodesContents as c where n_id = n.id and  c_type = 'content_OverviewProducts' and title ='".trim($value)."'");
		}
		return implode('|', $dbCat); 
	}
	
	

	
	function groupProductsData($data, $min = 0, $max = 40000000){
		$newData = array();
		$newData2 = array();
		$cats  = array();
		$c = 0;
		foreach ($data as $prod){			
			if($c > $min && $c <= $max){
				$p = $prod;
				if(is_numeric(strpos($prod->category, 'Domácí kávovary'))){
					$p->title = 'Kávovar '.$prod->title ;
					
					}
					$newData2[] = $p; 
			} else {     
			}
				$c++;

		} 		
		return $newData2;
	}
		
	function loadXML($xmlPath, $min = 0, $max = 40000000){
		$reader = new XMLReader();
		$isopen = $reader->open($xmlPath); 
		$products = array();
		$data = new stdClass(); 
		$i = 0;
		$productsCount = 0;
		if($isopen){ 			 
		while ($reader->read()){
		   if ($reader->nodeType == XMLREADER::ELEMENT) {  
		   		$i++;
		   		switch ($reader->localName) { 
		   			case 'SHOPITEM': 
		   				$products[] = $data;
			   			$data = new stdClass();   
		   				//$data->extId = $reader->getAttribute('id');  
		   				break;  
		   			case 'PRODUCT':
		   				$data->title = $reader->readString();
		   				break; 
		   			case 'IMGURL':
		   				$data->image = $reader->readString();
		   				break;  
		   			case 'URL':
		   				$data->url = $reader->readString();
		   				break;  
		   			case 'PRICE_VAT':
		   				$data->price = $reader->readString();
		   				break;
		   			case 'PRICE':
		   				$data->priceBezDPH = $reader->readString();
		   				break;
		   			case 'PRICE2':
		   				$data->price2 = $reader->readString();
		   			case 'DESCRIPTION':
		   				$data->description = $reader->readString();
		   				break;
		   			case 'FULL_DESCRIPTION':
		   				$data->fullDescription= $reader->readString();
		   				break;		   				
		   			case 'EAN':
		   				$data->ean = $reader->readString();
		   				break;
		   			case 'AVAILABILITY':
		   				$data->dostupnost = $reader->readString();
		   				break;
		   			case 'CATEGORY':
		   				$data->category = $reader->readString();
		   				break;
		   		}
		   		
		   		if($productsCount > $max){
		   			break;
		   		} 
		   		
		   } 
		} 
		return ($products);
		} 
	}
	

	function save($newNode, $content, $inputContent){		 
		$err2 = $content->save(); 
    	$this->tree->addNode($newNode, false, false); 
    	$this->tree->pareNodeAndContent($newNode->nodeId, $content->id, $content->_name);	
	}
	
	
	
function updateNodesAction($node, $content, $inputContent){  
		$node->save(null, null, false); 
		$content->update();
    	     
    	// pr($this->view->requestParams); die();       
    
	}
 	
	
	
}