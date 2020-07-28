<?php
header ( 'Content-Type: text/html; charset=UTF-8' );

 

/**
 *
 * @author midzil
 *        
 */
class module_Import {

	
	function __construct($view) {
		$this->db = Zend_Registry::getInstance ()->db;
		$this->config = Zend_Registry::getInstance ()->config;
		$this->view = $view;
		$this->tree = Zend_Registry::getInstance ()->tree;
		$this->mVarianta = new module_Varianta();
		$this->_tableNameSkladem = 'module_eshop_skladem';
		$this->_tableNameProducts = 'content_Product';
		$this->_tableNameZnacky = 'module_eshop_Znacky';
		$this->_tableProductCat = 'module_eshop_categories';
		$this->_tableProductCatOption = 'module_eshop_categories_options';
		$this->_tableProductOverProducts = 'content_OverviewProducts';
		$this->_tableVariants = 'module_eshop_variants';
		$this->_tableVariantsOption = 'module_eshop_variants_options';
		$this->_tableNameCounterCron = 'module_eshop_couterCron';
		$this->mCronChecker = new module_CronChecker();
	}
	
	  	
	
	private function getContentIdByCNumber($CNumber) {
		$e = $this->db->fetchOne ( "SELECT id FROM `content_Product` WHERE `CNumber` = ?", $CNumber );
	
		return $e;
	}
	
	private function setProuctsPrice($data)
	{
	
		foreach ($data as $v)
		{
			$contenId = $this->getContentIdByCNumber($v->Subgroup);
			if($contenId){
				$data = array();
				$data['price'] = round($v->priceWVAT);
				$variant = $this->mVarianta->getVariantsByIdProduct($contenId);
				
				if ($variant[0]['price2']> 0) {
					$dif = $variant[0]['price2'] - $data['price'];
					$p = $variant[0]['price2']/ 100;
					$d= round ( $dif / $p, 0 );
				}
				$data['discount'] =  $d;
				$where = $this->db->quoteInto ( 'id_product = '.$contenId );
				$this->db->update($this->_tableVariants,$data,$where);
			}
		}
	}
	
	
	/**
	 * @return array(pretext, contentProperty, html)
	 */
	public function getProperty($data)
	{
			$properties = get_object_vars($data);
			$text = '<p>Další parametry</p>';
			$text .= '<ul>';
				foreach($properties as $val){
					
					if(is_array($val) && $val['value'])
					{
						$text .= '<li><strong>'.$val['text'].':</strong> '.$val['value'].'</li>';
					}
					else{
						$contentProperties[] = $val;
					}
				}
				$text .= '<ul>';		
		return array($contentProperties,$text);
	}
	
	
	
	function import($saveFile = false, $price = false, $dostupnost = false, $products = false,$productsProp =false) {
		
		// produkty bez variant
		if ($products) {
			
			$start = $this->getPosition();
			$end = $start + 5;
			$this->incPosition(false,5);
			
			$dataGrouped = $this->readCsv($start,$end);
			
			$this->addProducts ( $dataGrouped );
		}
		if ($productsProp) {
				
			$start = $this->getPosition();
			$end = $start + 10;
			
				
			$dataGrouped = $data = $this->readPropertyCsv(false,$start,20);
			pr($dataGrouped);
			die();
			$this->addProducts ( $dataGrouped, true);
			$this->incPosition(false,10);
		}
		// kategorie k produktům
		if ($saveFile) {
			$this->saveFile(true);
			$this->saveFile(false);
			die ();
		}
		// související k produktům
		if ($dostupnost) {
			$data = $this->loadXMLDostupnost( $path ); 
			if($data){
				$this->setDostupnost($data);
			}
			else{
				$this->mCronChecker->sendEmail('Není xml soubor na Dostupnost na Topchlazeni.cz');
			}
			die ();
		}
		
		
		
		// dokoukat proč néé všechny
		if ($price) {
			$mVariant = new module_Varianta();
			$dataGrouped = $this->loadXMLPrice();
			//die;
			if($dataGrouped){
			foreach ($dataGrouped as $v)
			{
			//	$id = $this->db->fetchOne("select id_Product from module_eshop_variants where ean = ?",$v->EAN);
				$temp = explode(' ', $v->Subgroup);
				$model = $temp[0].' '.$temp[1];
				
				if(is_numeric(strpos($v->Subgroup, ' SP')))
				
				{
					$model .= ' SP'; 
					$id = $this->isExistByEan($v->EAN, $model);
				}
				else{
					$id = $this->isExistByEan($v->EAN);
				}
				if(!$id)
				{
					
					$id = $this->isExist($model);
				}
				$isNeauto =  round($v->UnitPriceIncludingVAT/($v->priceWVAT/100),2);
				if($id){
					$data = array();
					$data['price2'] = 0;
					$data['discount'] = 0;
					$data['action'] = $dat['akce'] = 0;
				
					// if($v->PricelistCategory == 'neautorizovanř model')
					// {
						// $v->priceWVAT = round($v->priceWVAT*0.95);
					// }
					// elseif('83.34' == $isNeauto){
						// $v->priceWVAT = round($v->priceWVAT*0.95);
					// } 
					if($v->priceAction>0)
					{
						$data['price'] = $v->priceAction;
						$data['price2'] = $v->priceWVAT;
						$dif = $data['price2'] - $data['price'];
						$p = $data['price2'] / 100;
						$data ['discount'] = round ( $dif / $p, 0 );
						$data['action'] = $dat['akce']= 1;
					}
					else{
						$data['price'] = $v->priceWVAT;
					}
					$data['priceNakup'] = $v->UnitPriceIncludingVAT;
					$data['autor'] = 0;
					if($v->PricelistCategory == 'autorizovaný model')
					{
						$data['autor'] = 1;
					}
					pr($v->Subgroup);
					pr($data['price']);
					$where = $this->db->quoteInto ( 'id_Product = '.$id );
					$data['skladem'] = 1;
					if($data['price'] == 0)
					{
						$data['skladem'] = 2;
					
					}
// 					pr($v);
// 					pr($data);
// 					e($id);
// 					pr($data);
// 					pr($v);
					$variants = $mVariant->getVariantsByIdProduct($id);
					
					if($variants[0]['update']!=' 1'){
						$this->db->update('module_eshop_variants',$data,$where);
						$wher = $this->db->quoteInto ( 'id = '.$id );
						$this->db->update('content_Product',$dat,$wher);
					}
				}
			}
			}
			die ();
		}
	}
	
	private function setDostupnost($data) 
	{
		
		$datab['skladem'] = '0';
		$where = $this->db->quoteInto ( "noFeedUpdate != '1'" );

		$this->db->update('module_eshop_variants',$datab,$where);
		foreach ($data as $v)
		{
			$temp = explode(' ', $v->Subgroup);
			$model = $temp[0].' '.$temp[1];
			if(is_numeric(strpos($v->Subgroup, ' SP')))
			{
				$model .= ' SP'; 
				$contenId = $this->isExistByEan($v->EAN, $model);

			
			}
			else{
				$contenId = $this->isExistByEan($v->EAN);
				
			}
		
			if(!$contenId)
			{
				
				$contenId = $this->isExist($model);
				if($v->Subgroup =='SBS 7252'){
					e('eassn');
					e($contenId);
					e($v->EAN);
				}
				
			}
		
			if($contenId){
				$datab['skladem'] =  1;
				$realDost = implode(';', $v->AvailabilityAtDate);	
				$datab['realDost'] =  $realDost;
				$datab['sklademNow'] = $v->AvailabilityAtDate[0] == 'Ano' ? '55' : $v->AvailabilityAtDate[0];
				$datab['sklademT'] = $v->AvailabilityAtDate[1] == 'Ano' ? '55' : $v->AvailabilityAtDate[1];
				$datab['skladem2T'] = $v->AvailabilityAtDate[2] == 'Ano' ? '55' : $v->AvailabilityAtDate[2];
				$datab['skladem4T'] = $v->AvailabilityAtDate[3] == 'Ano' ? '55' : $v->AvailabilityAtDate[3];
				$datab['skladem8T'] = $v->AvailabilityAtDate[4] == 'Ano' ? '55' : $v->AvailabilityAtDate[4];
				$where = $this->db->quoteInto ( 'id_product = '.$contenId );
 				pr($v);
 				pr($datab);
				e($contenId);
				if(!is_numeric(strpos($v->Subgroup,'NI'))){
					$this->db->update($this->_tableVariants,$datab,$where);
				}
				}
			else{
		
			
				if(!is_numeric(strpos($v->Subgroup,'NI'))){
	
					$this->addNewSimple ( $v );
				}
				

				/// napsat email? ulozit do table a pak poslat email?
			}
		}
	}
	
	private function isExistByEan($ean, $model = false)
	{
		if($model)
		{
			$w = "model = '".$model."' and";
		}
		return $this->db->fetchOne("select id_product from module_eshop_variants where ".$w." ean = ?",$ean);
	}
	
	function addProducts($dataGrouped,$onlyUpdate) {

		foreach ( $dataGrouped as $variants ) {
			$idContent = $this->isExist ( trim($variants->model));
			$temp = explode('-', trim($variants->model));
			$secIdContent = $this->isExist ($temp[0]);
			if($variants->ean){
				$idContentEan = $this->isExistByEan($variants->ean, $temp);
			}
			if ($idContent > 0 ) {
				$this->updateSimple ( $variants, $idContentEan); 
			}
			elseif($secIdContent>0)
			{
				$this->updateSimple ( $variants,$secIdContent);
			}
			elseif(!$onlyUpdate) {     
			
				$this->addNewSimple ( $variants );
			}
		}
	}
	/**
	 *
	 * @param string $prodTitleOrig
	 * @param sting $variantColor
	 */
	private function importFiles($file,$returnFullPath = 10) {
		$url = key($file);
		$fileName = $file[$url];
		$config = $this->config;
		$contents = file_get_contents ( $url );
		$imageName = str_replace ( 'http://www.shop-liebherr.cz/fotky19418/fotov/_', '', $url );
		
		$filepath = $this->config->fsRoot . '/docs/produkty/' . $imageName;
		file_put_contents ( $filepath, $contents );
		$view = $this->view;
		$view->input = new stdClass ();
		$view->input->fullpath = substr ( $filepath, strlen ( $this->config->fsRoot ) );
		$view->input->state = 'PUBLISHED';
		$view->input->owner = 'a';
		$file = helper_Nodes::initContent ( 'SFSFile', $view->input, $view );
		if ($file->getPropertyValue ( 'fullpath' )) {
			$nnode = helper_Nodes::addNodeWithContent ( $file, 53119, $fileName, $view, false, true );
		}
		if ($returnFullPath === true) {
			$path = $filepath;
		} elseif ($returnFullPath === 5) {
			$path = $config->sfFolder . '/' . content_SFSFile::getSFSPath ( $nnode->nodeId, $nnode->path );
		} elseif ($returnFullPath === 10) {
			$path = $config->sfFolder . '/' . content_SFSFile::getSFSPath ( $nnode->nodeId, $nnode->path ) . ';' . content_SFSFile::getFileWithouExtension ( $nnode->title );
		} else {
			$path = $file->getPropertyValue ( 'fullpath' );
		}
		return $path;
	}
	
	
	/**
	 *
	 * @param string $prodTitleOrig        	
	 * @param sting $variantColor        	
	 */
	private function importImage($img, $returnFullPath = 10,$isFile = false) {
		$config = $this->config;
		$contents = file_get_contents ( $img );
		$imageName = str_replace ( 'http://www.shop-liebherr.cz/fotky19418/fotos/_', '', $img );
		$filepath = $this->config->fsRoot . '/obrazky/produkty/' . $imageName;
		//pr($filepath);
		file_put_contents ( $filepath, $contents );
		$view = $this->view;
		$view->input = new stdClass ();
		$view->input->fullpath = substr ( $filepath, strlen ( $this->config->fsRoot ) );
		$view->input->state = 'PUBLISHED';
		$view->input->owner = 'a';
		$file = helper_Nodes::initContent ( 'SFSFile', $view->input, $view );
		if ($file->getPropertyValue ( 'fullpath' )) {
			$nnode = helper_Nodes::addNodeWithContent ( $file, 57022, $imageName, $view, false, true );
		}
		
		if ($returnFullPath === true) {
			$path = $filepath;
		} elseif ($returnFullPath === 5) {
			$path = $config->sfFolder . '/' . content_SFSFile::getSFSPath ( $nnode->nodeId, $nnode->path );
		} elseif ($returnFullPath === 10) {
			$path = $config->sfFolder . '/' . content_SFSFile::getSFSPath ( $nnode->nodeId, $nnode->path ) . ';' . content_SFSFile::getFileWithouExtension ( $nnode->title );
		} else {
			$path = $file->getPropertyValue ( 'fullpath' );
		}
		$this->mVarianta->resizePhotos($path);
		return $path;
	}
	
	private function unsetSkladem() {
		$data = array (
				'skladem' => 0 
		);
		$this->db->update ( $this->_tableNameProducts );
	}
	
	/**
	 * otestuje jestli je v kategori product
	 * *
	 */
	public function checkCategoriesProducts() {
		// nasetuju všechny na published
		$data = array (
				'state' => 'PUBLISHED' 
		);
		$where = $this->db->quoteInto ( 'id NOT IN ( SELECT Znacky  FROM  content_Product where skladem > 0 )' );
		// DELETE FROM `module_eshop_Znacky` WHERE id NOT IN ( SELECT Znacky
		// FROM content_Product where skladem > 0 )
		$this->db->delete ( '`module_eshop_Znacky`', $where );
		
		$this->db->update ( 'content_OverviewProducts', $data );
		$allCategoryNodes = $this->db->fetchAll ( "SELECT parent FROM  content_Product where state = 'PUBLISHED' and skladem>0" );
		$nodesIds = array ();
		
		foreach ( $allCategoryNodes as $item ) {
			$cats = explode ( '|', $item ['parent'] );
			foreach ( $cats as $c ) {
				if ($c > 0) {
					$nodesIds [$c] = $c;
					$count = 0;
					if (array_key_exists ( $c, $countCategory )) {
						$count = $countCategory [$c] + 1;
						$countCategory [$c] = $count;
					} else {
						$countCategory [$c] = 1;
					}
				}
			}
		}
		// nasetuju kategorii počty produktů
		foreach ( $countCategory as $key => $value ) {
			$n = $this->tree->getNodeById ( $key );
			$c = $n->getPublishedContent ();
			$c->getPropertyByName ( 'countProducts' )->value = $value;
			$c->update ();
		}
		$categories = implode ( ',', $nodesIds );
		$cateriesNodes = $this->db->fetchAll ( "select id from Nodes as n, NodesContents where id not in(" . $categories . ")  and n_id = id
				and c_type='content_OverviewProducts'" );
		foreach ( $cateriesNodes as $node ) {
			$n = $this->tree->getNodeById ( $node ['id'] );
			$c = $n->getPublishedContent ();
			$c->state = 'ARCHIVED';
			$c->update ();
		}
	}
	private function isVariantaExist($barva, $origNameProduct) {
		$id = false;
		$contentId = $this->productExistsByOrigName ( $origNameProduct );
		$id = $this->db->fetchOne ( "select id from " . $this->_tableVariants . " WHERE title=:t and id_product =:o", array (
				't' => $barva,
				'o' => $contentId 
		) );
		return $id;
	}
	
	private function addVariants($data) {
		foreach ( $data as $val ) {
			// if jestli exist
			$id = $this->isVariantaExist ( $val->barva, $val->origNameProduct );
			if ($id > 0) {
					$this->updateVariant($val, $id);
			} else {
				$this->addVariant ( $val );
			}
		}
	}
	
	/**
	 *
	 * @param unknown_type $varianta
	 *        	vytáhne s content->options kvůli kategoriiím, bude to
	 *        	groupovat podle barvy (jedna barva = jedna varianta)
	 *        	uloží options categorie, categorie jsou natvdo podle id,
	 *        	1-druh,2-velikos,3-ostatní, 4-barva
	 */
	
	
	function getProductByOrigNameProduct($origNameProduct, $onlyContent = false) {
		$p = $this->db->fetchRow ( "
			SELECT `cm`.`id` AS `cid`, `n`.`id`, `n`.`title`, `n`.`path`,`cm`.`options` 
			
			FROM `content_Product` AS `cm` 
				INNER JOIN `NodesContents` AS `nc` ON cm.id = nc.c_id 
				INNER JOIN `Nodes` AS `n` ON n.id = nc.n_id 
				
			WHERE (c_type = 'content_Product' AND `origNameProduct` = ?)    
			", $origNameProduct );
		if ($onlyContent) {
			return $p;
		}
		$product = false;
		if ($p ['id']) {
			$product = $this->tree->getNodeById ( $p ['id'] );
		}
		return $product;
	}
	private function checkZnacka($categoryText) {
		$id = $this->db->fetchOne ( 'select id from `module_eshop_Znacky` where nazev = ?', $categoryText );
		if ($id > 0) {
			return $id;
		} else {
			$data = array (
					'nazev' => $categoryText 
			);
			$this->db->insert ( 'module_eshop_Znacky', $data );
			$id = $this->db->fetchOne ( 'select id from `module_eshop_Znacky` where nazev =?', $categoryText );
			return $id;
		}
	}
	
	function better_strip_tags($text, $tags = false, $replace = '') {
		if ($tags === false) {
			return strip_tags($text);
		}
	
		if (!is_array($tags)) {
			$tags = array($tags);
		}
	
		foreach ($tags as $tag) {
			$text = preg_replace("/<[\/\!]*?" . $tag . "[^<>]*?>/si", $replace, $text);
		}
		 
		return $text;
	}
	
	
	private function getCategoriesByTitle($title)
	{
		foreach ($this->_categories as $key=>$val)
		{
			
			if(is_numeric(strpos($title, $key)))
			{
				return $val;
			}
		}
		if(is_numeric(strpos($title, 'Šuplíkový mrazák')))
		{
			return 53068;
		}
	}
	
	private function separeteValues($html)
	{
		$html2 = $this->better_strip_tags($html,'strong');
		
		$values = new stdClass();
		if(is_numeric(strpos($html2, 'NoFrost: Ano')))
		{
			$values->noFrost = 1;
		}
		elseif(is_numeric(strpos($html2, '<li>NoFrost: Ano</li>'))){
			$values->BioFresh = 1;
		}
		
		if(is_numeric(strpos($html2, 'Systém chlazení: dynamický')))
		{
			$values->chlazeni = 1;
		}
		elseif(is_numeric(strpos($html2, 'Systém chlazení v chladicí části: dynamický')))
		{
			$values->chlazeni = 1;
		}
		

		if(is_numeric(strpos($html2, 'SmartFrost: Ano')))
		{
			$values->smartFrost = 1;
			
		}
		elseif(is_numeric(strpos($html2, 'SmartFrost-Systém: Ano'))){
			$values->smartFrost = 1;
		}
		elseif(is_numeric(strpos($html2, '<li>SmartFrost: ano</li>'))){
			$values->smartFrost = 1;
		}
		
		if(is_numeric(strpos($html2, 'BioFresh: Ano')))
		{
			$values->BioFresh = 1;
		}
		elseif(is_numeric(strpos($html2, '<li>BioFresh: ano</li>'))){
			$values->BioFresh = 1;
		}
		
		if(is_numeric(strpos($html2, 'Hlučnost')))
		{
			$tt = explode('<li>Hlučnost', $html2);
			$ttt = explode('</li>',$tt[1]);
			$values->hlucnost = str_replace('"', '', preg_replace('/[^0-9]/i','', $ttt[0]));
		}
		
		if(is_numeric(strpos($html2, '<li>Počet chladicích okruhů')))
		{
			$tt = explode('<li>Počet chladicích okruhů:', $html2);
			$ttt = explode('</li>',$tt[1]);
			$values->pocChlad = str_replace('"', '', preg_replace('/[^0-9]/i','', $ttt[0]));
		}
		
		if(is_numeric(strpos($html2, '<li>Počet kompresorů')))
		{
			$tt = explode('<li>Počet kompresorů:', $html2);
			$ttt = explode('</li>',$tt[1]);
			$values->pocKom = str_replace('"', '', preg_replace('/[^0-9]/i','', $ttt[0]));
		}
		
		if(is_numeric(strpos($html, 'A+++')))
		{
			$values->trida = '4';
			$values->tridaText = 'A+++';
		}
		elseif(is_numeric(strpos($html, 'A++')))
		{
			$values->trida = '3';
			$values->tridaText = 'A++';
		}
		elseif(is_numeric(strpos($html, 'A+')))
		{
			$values->trida = '2';
			$values->tridaText = 'A+';
		}
		else{
			$values->tridaText = 'A';
			$values->trida = '1';
		}
		
		if(is_numeric(strpos($html2, 'Premium')))
		{
			$values->rada = 2;
		}
		
		if(is_numeric(strpos($html2, 'Výška (cm):')))
		{
			$tt = explode('<li>Výška (cm):', $html2);
			$ttt = explode('</li>',$tt[1]);
			$values->vyska = str_replace('"', '', preg_replace('/[^0-9]/i','', $ttt[0]));
		}
		elseif(is_numeric(strpos($html2, 'Rozměry (cm)')))
		{
			$tt = explode('<li>Rozměry (cm)', $html2);
			$ttt = explode('</li>',$tt[1]);
			$t = trim(str_replace(':', '', $ttt[0]));
			$ar = explode('/', $t);
			$values->vyska = $ar[0];
		}
		elseif(is_numeric(strpos($html2, 'Rozměry [cm]')))
		{
			$tt = explode('<li>Rozměry [cm]', $html2);
			$ttt = explode('</li>',$tt[1]);
			$t = trim(str_replace(':', '', $ttt[0]));
			$ar = explode('/', $t);
			$values->vyska = $ar[0];
		}
		elseif(is_numeric(strpos($html2, 'Rozměry (v x š x h):')))
		{
			$tt = explode('<li>Rozměry (v x š x h):', $html2);
			$ttt = explode('</li>',$tt[1]);
			$t = trim(str_replace('cm', '', $ttt[0]));
			$ar = explode(' x ', $t);
			$values->vyska = $ar[0];
		}
		
		
		if(is_numeric(strpos($html2, 'Šířka (cm):')))
		{
			$tt = explode('<li>Šířka (cm):', $html2);
			$ttt = explode('</li>',$tt[1]);
			$values->sirka = str_replace('"', '', preg_replace('/[^0-9]/i','', $ttt[0]));
		}
		elseif(is_numeric(strpos($html2, 'Rozměry (cm)')))
		{
			$tt = explode('<li>Rozměry (cm)', $html2);
			$ttt = explode('</li>',$tt[1]);
			$t = trim(str_replace(':', '', $ttt[0]));
			$ar = explode('/', $t);
			$values->sirka = $ar[1];
		}
		elseif(is_numeric(strpos($html2, 'Rozměry (v x š x h):')))
		{
			$tt = explode('<li>Rozměry (v x š x h):', $html2);
			$ttt = explode('</li>',$tt[1]);
			$t = trim(str_replace('cm', '', $ttt[0]));
			$ar = explode(' x ', $t);
			$values->vyska = $ar[1];
		}
		
		if(is_numeric(strpos($html2, 'Hloubka včetně odstupu od zdi')))
		{
			$tt = explode('<li>Hloubka včetně odstupu od zdi', $html2);
			$ttt = explode('</li>',$tt[1]);
			$values->hlubka = str_replace('"', '', preg_replace('/[^0-9]/i','', $ttt[0]));
		}
		elseif(is_numeric(strpos($html2, 'Rozměry (cm)')))
		{
			$tt = explode('<li>Rozměry (cm)', $html2);
			$ttt = explode('</li>',$tt[1]);
			$t = trim(str_replace(':', '', $ttt[0]));
			$ar = explode('/', $t);
			$values->hlubka = $ar[2];
		}
		elseif(is_numeric(strpos($html2, 'Rozměry (v x š x h):')))
		{
			$tt = explode('<li>Rozměry (v x š x h):', $html2);
			$ttt = explode('</li>',$tt[1]);
			$t = trim(str_replace('cm', '', $ttt[0]));
			$ar = explode(' x ', $t);
			$values->hlubka = $ar[2];
		}
				
		if(is_numeric(strpos($html2, 'Čistý obsah chladící části celkem')))
		{
			$tt = explode('<li>Čistý obsah chladící části celkem', $html2);
			$ttt = explode('</li>',$tt[1]);
			$values->obsahChladici = str_replace('"', '', preg_replace('/[^0-9]/i','', $ttt[0]));
		}
		
		if(is_numeric(strpos($html2, 'Čistý obsah chladící části')))
		{
			$tt = explode('<li>Čistý obsah chladící části celkem', $html2);
			$ttt = explode('</li>',$tt[1]);
			$values->obsahChladici = str_replace('"', '', preg_replace('/[^0-9]/i','', $ttt[0]));
		}		
		
		if(is_numeric(strpos($html2, 'Čistý obsah mrazicí část')))
		{
			$tt = explode('<li>Čistý obsah mrazicí část', $html2);
			$ttt = explode('</li>',$tt[1]);
			$values->obsahMrazici = str_replace('"', '', preg_replace('/[^0-9]/i','', $ttt[0]));
		}
		
		if(is_numeric(strpos($html2, 'Výrobník ledových kostek: Ano')))
		{
			$values->vyrobnik = 1;
		}
		elseif(is_numeric(strpos($html2, 'Výrobník ledu: Ano'))){
			$values->vyrobnik = 1;
		}
		elseif(is_numeric(strpos($html2, 'Výrobník ledu s napojením na vodu'))){
			$values->vyrobnik = 1;
		}
		
		$values->madlo = 1;
		if(is_numeric(strpos($html2, 'tyčové madlo s integrovanou mechanikou otevírání dveří')))
		{
			$values->madlo = 3;
		}
		
		if(is_numeric(strpos($html2, 'Čistý obsah celkem (l):')))
		{
			$tt = explode('<li>Čistý obsah celkem (l):', $html2);
			$ttt = explode('</li>',$tt[1]);
			$values->obsahCelkem = str_replace('"', '', preg_replace('/[^0-9]/i','', $ttt[0]));
		}
		if(is_numeric(strpos($html2, 'Spotřeba elektrické energie za 365 dní (kW/h):')))
		{
			$tt = explode('<li>Spotřeba elektrické energie za 365 dní (kW/h):', $html2);
			$ttt = explode('</li>',$tt[1]);
			$values->spotrebaEl = str_replace('"', '', preg_replace('/[^0-9]/i','', $ttt[0]));
		}
		return $values;
	}
	
	private function addNewSimple($variant) {

			$inputContent = new stdClass ();
			$input = new stdClass ();
			
			$input->pageTitle = $variant->Subgroup;
			
			//	$html = str_replace('""', '"', $variant->descr);
			//	$html = preg_replace("/<img[^>]+\>/i", " ", $html);
			//	$html = str_replace('<h2>Parametry produktu:</h2>', "", $html);
			//	$html = str_replace('<h2> </h2>', "", $html);
			//	$html = str_replace('<p> </p>', "", $html);
			///	$inputContent->fck_html = $inputContent->html = $html;
			$this->addNodesAction ( 3801, $input, $inputContent, $variant);
		
	}
	
	
	function saveDataVar($view, $node, $content,$variant) {
		$dataVariant = array(
				'model' => $variant->Subgroup,
				'EAN' => $variant->EAN,
				'id_product' => $content->id,
				'skladem' => '1'
		);
		$this->db->insert($this->_tableVariants, $dataVariant);
		$idVariant =  $this->db->lastInsertId ();
	}
	
	
	
	private function addNewSimpleOld($variant) {
		if($variant->VyrId != 'Nezobrazovat ve feedu')
		{
			$inputContent = new stdClass ();
			$input = new stdClass ();
			$input->pageTitle = $variant->title;
		//	$html = str_replace('""', '"', $variant->descr);
		//	$html = preg_replace("/<img[^>]+\>/i", " ", $html);
		//	$html = str_replace('<h2>Parametry produktu:</h2>', "", $html);
		//	$html = str_replace('<h2> </h2>', "", $html);
		//	$html = str_replace('<p> </p>', "", $html);
		///	$inputContent->fck_html = $inputContent->html = $html;
			$inputContent->parentSection = $this->getCategoriesByTitle($variant->title);
			$properties = $this->separeteValues($variant->descr);
			$inputContent->preText  = $this->generatePreText($properties);	
				
			$variant->enerClass = $properties->tridaText;
			//pr($images);
	// 		$inputContent->origId = $variant->idExt;
	 		$inputContent->author = 'a';
	// 		$inputContent->CNumber = $variant->VyrId;

			if($variant->files)
			{
				$inputContent->files = $this->importFiles($variant->files);
			}
			$this->addNodesAction ( 3801, $input, $inputContent, $variant);
		}
	}
	
	public function savePre()
	{
		$all = $this->db->fetchALL('select * from '.$this->_tableNameProducts);
		foreach ($all as $value) {
			$data = $this->db->fetchRow("select * from ".$this->_tableVariants." where id_product = ?",$value['id']);
			$pre = $this->generatePreText2($data );
			$d['preText'] = $pre;
			$where = $this->db->quoteInto ( 'id = '.$value['id']);
			$this->db->update($this->_tableNameProducts,$d,$where);
		}
		return false;
	}
	
	
	private function generatePreText2($data)
	{
		$text = array();
		//tady
		if($data['smartFrost']>0)
		{
			$text[] ='Smartfrost';
		}
		if($data['bioFresh']>0)
		{
			$text[] ='BioFresh';
		}

		if($data['noFrost'] > 0)
		{
			$text[] ='NoFrost';
		}
		if($data['vyska']>0)
		{
			
			$text[] ='Rozměry (cm): '.$data['vyska'].'/'.$data['sirka'].'/'.$data['hloubka'];
		}
	
		if($data['obsahCelkem'] > 0)
		{
			$text[] ='Čistý obsah celkem (l): '.round($data['obsahCelkem']);
		}
	
		if($data['obsahLed'] > 0)
		{
			$text[] = 'Čistý obsah chlad. části celkem (l): '.round($data['obsahLed']);
		}
		if($data['obsahMraz'] > 0)
		{
			$text[] ='Čistý obsah mraz. část (l): '.round($data['obsahMraz']);
		}
	
		if($data['enerClass'])
		{
			$text[] = $this->mVarianta->variantProperty['enerClass']['selection'][$data['enerClass']];
		}
	
		if($data['spotreba'] > 0)
		{
			$text[] = 'Spotřeba el. energie za 365 dní (kW/h): '.round($data['spotreba']);
		}
	
		if($data['hlucnost'] > 0)
		{
			$text[] = 'Hlučnost (dB): '.round($data['hlucnost']);
		}
		if($data['pocChlad']>0)
		{
			$text[] = 'Počet chlad. okruhů: '.round($data['pocChlad']);
		}
	
		/// tady pokračovat
		if($data['chlazeni']>0)
		{
			$text[] = 'Systém chlazení: '.$this->mVarianta->variantProperty['chlazeni']['selection'][$data['chlazeni']];
		}
		
		if($data['dobaSklad']>0)
		{
			$text[] = 'Doba skladování při výpadku proudu (h): '.round($data['dobaSklad']);
		}
	
	
		if($data['hmotnost']>0)
		{
			$text[] = 'Hmotnost (kg): '.round($data['hmotnost']);
		}
	
		if($data['sirkaOtev']>0)
		{
			$text[] = 'Šířka při otevřených dveřích s madlem (cm): '.round($data['sirkaOtev']);
		}
	
		if($data['hloubkaOtev']>0)
		{
			$text[] = 'Hloubka při otevřených dveřích s madlem (cm): '.round($data['hloubkaOtev']);
		}
		return implode(', ', $text);
	}
	
	
	private function generatePreText($data, $enerClass)
	{
		$text = array();
		if($data->rozmer)
		{	
			$data->rozmer = str_replace('.', ',', $data->rozmer);
			$temp = explode('/', $data->rozmer);
			$text[] ='Rozměry (cm): '.$temp[0].'/'.$temp[1].'/'.$temp[2];
		}
		
		
		
		//tady
		if($data->smartFrost == 'ano')
		{
			$text[] ='Smartfrost';
		}
		if($data->bioFresh == 'ano')
		{
			$text[] ='BioFresh';
		}
		if($data->noFrost == 'ano')
		{
			$text[] ='NoFrost';
		}
		if($data->obsahCelkem > 0)
		{
			$text[] ='Čistý obsah celkem (l): '.$data->obsahCelkem;
		}
		if($data->cistyObsah > 0)
		{
			$text[] ='Čistý obsah mrazicí část (l): '.$data->cistyObsah;
		}
	
		if($data->obsahChladCel > 0)
		{
			$text[] = 'Čistý obsah chladící části celkem (l): '.$data->obsahChladCel;
		}
	
		if($enerClass)
		{
			$text[] = $enerClass;
		}
	
		if($data->spotrebaEner365 > 0)
		{
			$text[] = 'Spotřeba elektrické energie za 365 dní (kW/h): '.$data->spotrebaEner365;
		}
	
		if($data->hlucnost > 0)
		{
			$text[] = 'Hlučnost (dB): '.$data->hlucnost;
		}
		if($data->pocChlad)
		{
			$text[] = 'Počet chlad. okruhů: '.$data->pocChlad;
		}
		if($data->pocChlad)
		{
			$text[] = 'Počet chlad. okruhů: '.$data->pocChlad;
		}
	
		if($data->chlazeni =='dynamický')
		{
			$text[] = 'Systém chlazení: '.$data->chlazeni;
		}
		
		
		if($variant->brutHmotnost['value'])
		{
			$text[] = 'Hmotnost včetně obalu (kg): '.$variant->brutHmotnost['value'];
		}
		
		if($variant->sirkaOtev)
		{
			$text[] = 'Šířka při otevřených dveřích bez madla (cm): '.$variant->sirkaOtev;
		}
		
		if($variant->hloubkaOtev)
		{
			$text[] = 'Hloubka při otevřených dveřích bez madla (cm): '.$variant->hloubkaOtev;
		}
		
		if($variant->dobaSklad)
		{
			$text[] = 'Doba skladování při výpadku proudu (h): '.$variant->dobaSklad;
		}
		
		
		
		return implode(', ', $text);
	}
	
	
	private function updateSimple($variant,$idProduct) {
		if($variant->model && $idProduct>0){
		
		/// if A+...]
		$variant = 	$this->mVarianta->getVariantsByIdProduct($idProduct,true);
		list($properties,$contentText) = $this->getProperty($variant);
		$enerClass = $this->mVarianta->variantProperty['enerClass']['selection'][$variant['enerClass']];
		$preText = $this->generatePreText($variant,$enerClass);
		$data['html'] = $contentText;
		$data['preText'] = $preText;

		
		$where = $this->db->quoteInto ( 'id = '.$idProduct );
		$this->db->update('content_Product',$data,$where);
		$rada = array(
				0 => 'Není vybráno',
				1 => 'Standart',
				2 => 'Premium',
				3 => 'PremiumPlus',
				4 => 'Comfort',
				5 => 'GrandCru',
				6 => 'Vinothek',
				7 => 'Vinidor',
				8 => 'ProfiPremiumline',
				9 => 'ProfiLine',
				10 => 'MediLine'
		
		);
		switch ($variant->color) {
			case 'bílé':
				$color = 1;
				break;
			case 'erné':
				$color = 2;
				break;
			case 'nerez':
				$color = 3;
				break;
				case 'stříbrné':
					$color = 4;
					break;
		}
		if(!$color)
		{
			if(is_numeric(strpos('stříbrná', $variant->descr)))
			{
				$color = 4;
			}
		}
		if($variant->enerClass ==  'A+++')
		{
			$variant->enerClass = '4';
		}
		elseif($variant->enerClass == 'A++')
		{
			$variant->enerClass = '3';
		}
		elseif($variant->enerClass ==  'A+')
		{
			$variant->enerClass = '2';
		}
		else{
			$variant->enerClass = '1';
		}
		$noFrost = $variant->noFrost == 'ano'?1:0;
		$rozmer = explode('/', str_replace(',', '.', $variant->rozmer));
		$bioFresh = $variant->bioFresh == 'ano'?1:0;
		$smartFrost = $variant->smartFrost == 'ano'?1:0;
		if($variant->chlazeni == 'statický')
		{
			$chlazeni = 2;
		}
		elseif($variant->chlazeni == 'dynamický')
		{
			$chlazeni = 1;
		};
		/// jeste rady
		$dataVariant = array(
				'EAN' => $variant->ean,
				'noFrost' => $noFrost,
				'chlazeni' => $chlazeni,
				'color' => $color,
				'smartfrost' => $smartFrost,
				'bioFresh' => $bioFresh,
				'hlucnost' => str_replace(',', '.', $variant->hlucnost),
				'pocChlad' => $variant->pocChlad,
				'pocKom' => $variant->pocKom,
				'line' => $rada[$variant->rada],
				'enerClass' => $variant->enerClass,
				'vyska' => $rozmer[0],
				'sirka' => $rozmer[1],
				'hloubka' => $rozmer[2],
				'obsahLed' => str_replace(',', '.', $variant->obsahChladiciCasti),
				'obsahCelkem' => str_replace(',', '.', $variant->obsahChladCel),
				'obsahMraz' => str_replace(',', '.', $variant->cistyObsah),
				'spotreba' => str_replace(',', '.', $variant->spotrebaEner365),
				'hmotnost' => str_replace(',', '.', $variant->brutHmotnost['value']),
				'zmrKapalina' => str_replace(',', '.', $variant->zmrKapalina),
				'hloubkaOtev' => str_replace(',', '.', $variant->hloubkaOtev),
				'sirkaOtev' => str_replace(',', '.', $variant->sirkaOtev),
				'dobaSklad'=> str_replace(',', '.', $variant->dobaSklad),
				'skladem' => '1'
		);
		$where2 = $this->db->quoteInto ( 'id_product = '.$idProduct );
		$this->db->update('module_eshop_variants',$dataVariant,$where2);
		}
		
	}
	function addNodesAction($nodeAddTo, $input, $inputContent, $variant) {
		$newNode = Node::init ( 'ITEM', $nodeAddTo, $input, $this->view );
		$content = Content::init ( 'Product', $inputContent, false );
		$content->getPropertyByName ( 'parent' )->value = $inputContent->parentSection;
		$content->getPropertyByName ( 'photos' )->value = $inputContent->photos;
		$this->save ( $newNode, $content, $inputContent,$variant );
	}
	private function isExist($model) {
		$model = trim($model);
		$model = str_replace('      ', ' ', $model);
		$model = str_replace('    ', ' ', $model);
		$model = str_replace('   ', ' ', $model);
		pr($model);
		$e = $this->db->fetchOne ( "SELECT id_product FROM `module_eshop_variants` WHERE `model` = ?", $model );	
		return $e;
	}

	private function isCategoryExist($title) {
		return $this->db->fetchOne ( "select id from Nodes as n, NodesContents as c where n_id = n.id and c_type = 'content_OverviewProducts' and title =?", $title );
	}
	private function saveSkladem($dataGrouped) {
		$this->db->delete ( $this->_tableNameSkladem );
		$this->db->delete ( $this->_tableNameCategoryColors );
		foreach ( $dataGrouped as $fcode => $variants ) {
			$data = array ();
			$data ['ident'] = $fcode;
			$data ['sklademKs'] = 100;
			$this->db->insert ( $this->_tableNameSkladem, $data );
		}
	}
	function groupProductsData($data, $min = 1, $max = 40) {
		$newData = array ();
		$newData2 = array ();
		$cats = array ();
		$c = 0;
		foreach ( $data as $prod ) {
			if ($c > $min && $c <= $max) {
				$newData2 [] = $prod;
			}
			$c ++;
		}
		return $newData2;
	}
	
	function saveFile($priceFile)
	{
		if($priceFile){
			$local_file = $this->config->dataRoot.'/Cenik.xml';
			$server_file = 'CenikK005434_003.xml';
		}
		else{
			$local_file = $this->config->dataRoot.'/Dostupnost.xml';
			$server_file = 'DostupnostK005434_003.xml';
		}
		
		
		// set up basic connection
		$conn_id = ftp_connect('e-k005434-003.mctb2b.cz' );
		
		// login with username and password
		$login_result = ftp_login($conn_id, 'e-k005434-003_mctb2b_cz', '2StavH9a');
		e($login_result);
		// try to download $server_file and save to $local_file
		if (ftp_get($conn_id, $local_file, $server_file, FTP_BINARY)) {
			echo "Successfully written to $local_file\n";
		} else {
		echo "There was a problem\n";	
		}
		ftp_close($conn_id);
	}
	
	
	function loadXMLDostupnost($xmlPath, $min = 0, $max = 40000000) {
		$xmlPath = $this->config->dataRoot . '/Dostupnost.xml';
		$reader = new XMLReader ();
		$isopen = $reader->open ( $xmlPath );
		$products = array ();
		$data = new stdClass ();
		$i = 0;
		$productsCount = 0;
		while ( $reader->read () ) {
			if ($reader->nodeType == XMLREADER::ELEMENT) {
				$i ++;
				switch ($reader->localName) {
					case 'Item' :
						if($data->NO){
							$products [] = $data;
						}
						$data = new stdClass ();
						// $data->extId = $reader->getAttribute('id');
						break;
					case 'No' :
						$data->NO = $reader->readString ();
						break;
					case 'EANCode' :
						$data->EAN = $reader->readString ();
						break;
					case 'AvailabilityDate' :
						$data->AvailabilityDate[] = $reader->readString ();
						break;
						case 'AvailabilityAtDate' : 
							$data->AvailabilityAtDate[] = $reader->readString ();
							break;
					case 'UnitPriceIncludingVAT' :
						$data->priceWVAT = $reader->readString ();
						break;
					case 'Subgroup' :
						$data->Subgroup = $reader->readString ();
						break;
	
				}
				if ($productsCount > $max) {
					break;
				}
			}
		}
		return ($products);
	}
	
	function loadXMLPrice($xmlPath, $min = 0, $max = 40000000) {
		$xmlPath = $this->config->dataRoot . '/Cenik.xml';
		$reader = new XMLReader ();
		$isopen = $reader->open ( $xmlPath );
		$products = array ();
		$data = new stdClass ();
		$i = 0;
		$productsCount = 0;
		while ( $reader->read () ) {
			if ($reader->nodeType == XMLREADER::ELEMENT) {
				$i ++;
				switch ($reader->localName) {
					case 'Item' :
						if($data->NO){
							$products [] = $data;
						}
						$data = new stdClass ();
						// $data->extId = $reader->getAttribute('id');
						break;
					case 'No' :
						$data->NO = $reader->readString ();
						break;
					case 'EANCode' :
						$data->EAN = $reader->readString ();
						break;
					case 'UnitPrice' :
						$data->price = round($reader->readString ());
						break;
					case 'RecommendedUnitPriceInclVAT' :
						$data->priceWVAT = round($reader->readString ());
						break;
					case 'UnitPriceIncludingVAT':
						$data->UnitPriceIncludingVAT = round($reader->readString());
						break;
					case 'ActionPriceInclVAT' :
							$data->priceAction = $reader->readString ();
						break;
					case 'Subgroup' :
						$data->Subgroup = $reader->readString ();
						break;
					case 'PricelistCategory':
						$data->PricelistCategory = $reader->readString ();
					break;
						
				}
				if ($productsCount > $max) {
					break;
				}
			}
		}
		return ($products);
	}
	
	
	

	function save($newNode, $content, $inputContent,$variant) {
		$err2 = $content->save ();
		$this->tree->addNode ( $newNode, false, false );
		$this->tree->pareNodeAndContent ( $newNode->nodeId, $content->id, $content->_name );
		
		$_POST = $_GET = ( array ) $inputContent;
	
		
		$this->saveDataVar ( $this->view, $newNode, $content,$variant);
	}
	
	function saveData($view, $node, $content,$variant) {
			$dataVariant = array();
			if($variant->price == $variant->price2){
				$variant->price2 = 0;
			}
			
			if ($variant->price2 > 0) {
				$dif = $variant->price2 - $variant->price;
				$p = $variant->price2 / 100;
				$d= round ( $dif / $p, 0 );
			}
			$color = null;
			
			switch ($variant->color) {
				case 'bílá':
					$color = 1;
				break;
				case 'černá':
					$color = 2;
				break;
				case 'nerez':
					$color = 3;
			}
			if(!$color)
			{
				if(is_numeric(strpos('stříbrná', $variant->descr)))
				{
					$color = 4;
				}
			}
			
			if($variant->enerClass ==  'A+++')
			{
				$variant->enerClass = '4';
			}
			elseif($variant->enerClass == 'A++')
			{
				$variant->enerClass = '3';
			}
			elseif($variant->enerClass ==  'A+')
			{
				$variant->enerClass = '2';
			}
			else{
				$variant->enerClass = '1';
			}
			
			$dataVariant = array(
					'title' => $variant->title2,
					'model' => strtoupper (trim($variant->VyrId)),
					'price' => round($variant->price),
					'price2' => round($variant->price2),
					'EAN' => $variant->ean,
					'discount'=> $d,
					'id_product' => $content->id,
					'noFrost' => $properties->noFrost,
					'chlazeni' => $properties->chlazeni,
					'color' => $color,
					'smartfrost' => $properties->smartFrost,					
					'bioFresh' => $properties->BioFresh,
					'hlucnost' => $properties->hlucnost,
					'pocChlad' => $properties->pocChlad,
					'pocKom' => $properties->pocKom,
					'line' => $properties->rada,
					'enerClass' => $variant->enerClass,
					'vyska' => str_replace(',','.', $properties->vyska),
					'sirka' => str_replace(',','.', $properties->sirka),
					'hloubka' => str_replace(',','.', $properties->hlubka),
					'obsahLed' => $properties->obsahChladici,
					'obsahMraz' => $properties->obsahMrazici,
					'vyrobnik' => $properties->vyrobnik,
					'madlo' => $properties->madlo,
					'line' => $variant->line,
					'skladem' => '1'
				);		
				$images = array();
				foreach ($variant->obrazky as $img){
					if($img && in_array($img, $images)){
						$images [] = $this->importImage($img);
					}
				}

				$db = implode(';', $images);
				$dataVariant['obrazky'] = $db;
				$this->db->insert($this->_tableVariants, $dataVariant);
				$idVariant =  $this->db->lastInsertId ();
	}
	
	function updateNodesAction($node, $content, $inputContent) {
		$node->save ( null, null, false );
		$content->update ();
		$_POST = $_GET = ( array ) $inputContent;
		$this->view->requestParams = $this->view->request->getParams ();
		
		// pr($this->view->requestParams); die();
		$this->saveData ( $this->view, $node, $content );
	}
	function getPosition() {
		$position = $this->db->fetchOne ( "select position from " . $this->_tableNameCounterCron );
		if (! $position) {
			$position = 0;
		}
		return $position;
	}
	function incPosition($clean = false,$incPosition = 10) {
		$position = 0;
		$position = $this->getPosition ();
		$position += $incPosition;
		$this->db->delete ( $this->_tableNameCounterCron );
		if (! $clean) {
			$data = array (
					'position' => $position 
			);
			$this->db->insert ( $this->_tableNameCounterCron, $data );
		}
	}
	
	
}