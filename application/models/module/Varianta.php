<?
  /**
	@author Michal Nosil
  */
class module_Varianta{ 
	
	
	public $skladOptions = array(    	
		1 => 'SKLADEM',  
		2 => 'Není skladem', 
		3 => 'Produkt je dočasně nedostupný',
		4 => 'Předobjednávky',
	);
	// varianta_'name' -> property
	public $variantProperty = array  
		(
	 	//'title' =>  array('cMap'=> 'Název', 'type' => 'Text', 'options'=>false),
		'EAN' =>  array('cMap'=> 'EAN', 'type' => 'Text', 'options'=>false),
		'skladem' =>  array('cMap'=> 'Dostupnost', 'type' => 'Select', 'selection'=> array(    	
		1 => 'SKLADEM',  
		2 => 'Není skladem',  
		3 => 'Produkt je dočasně nedostupný',
		4 => 'Předobjednávky'             
	)),
	//	'obrazky' =>  array('cMap'=> 'Obrázky', 'type' => 'MultiFileSelect','options' => array('showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 20, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 3880 )),
		'price' =>  array('cMap'=> 'Cena', 'type' => 'Text', 'options'=> array('class' => 'validate[required,custom[onlyFloatVarPrice]] text-input')),
		'price2' =>  array('cMap'=> 'Původní cena', 'type' => 'Text', 'options'=>false),
		'purchase_price' =>  array('cMap'=> 'Nákupní cena', 'type' => 'Text', 'options'=>false),
		'heureka-hlid' => array('cMap'=> 'Cena pro vystravu Heureky', 'type' => 'Text', 'options'=>false),
		'hmotnost' => array('cMap'=> 'Hmotnost (kg)', 'type' => 'Text', 'options'=> array()),
	//	'sold' =>  array('cMap'=> 'Prodávanost', 'type' => 'Text', 'options'=>false),
			'id' =>  array('cMap'=> '', 'type' => 'Hidden', 'options'=>false),
				'typ' =>  array('cMap'=> 'Typ', 'type' => 'CheckboxGroup', 'selection'=> array( 
		1 => 'espresso',  
		2 => 'kombinované',  
		3 => 'Kávovar',
		4 => 'moka',
		5 => 'pákový',
		6 => 'Vestavné',
		)),
		
		'tlak' =>  array('cMap'=> 'Parní tlak (bar)', 'type' => 'Text', 'options'=> false),
		'objem' =>  array('cMap'=> 'Objem vody (l)', 'type' => 'Text', 'options'=> false),
		'ovladani' =>  array('cMap'=> 'Ovladaní', 'type' => 'Select', 'selection'=> array( 
		0 => 'Není vybráno',
		1 => 'automatické',  
		2 => 'elektronické',  
		3 => 'mechanické',
		4 => 'otočné',
		5 => 'plnoautomatické',
		6 => 'poloautomatické',
		7 => 'pákové',
		8 => 'Tlačítkové',
		)),
	
		'vlastnosti' =>  array('cMap'=> 'Vlastnosti', 'type' => 'CheckboxGroup', 'selection'=> array( 
		1 => 'Časovač',  
		2 => 'Zásobník na kávu',  
		3 => 'Připojení na vodu',
		4 => 'Automatické odvápňování',   
		5 => 'Automatické čištění',
		6 => 'Mlýnek',
		7 => 'Displej',
		8 => 'Kapslové',
		9 => 'podpora Podů', // zbozi
		10 => 'parní tryska', // zbozi
		11 => 'nastavení výšky výpusti', // zbozi
		12 => 'nastavení hrubosti mletí', // zbozi            
		)),  
		      
		'prikon' =>  array('cMap'=> 'Přikon (W)', 'type' => 'Text', 'options'=> false),    
			'smartConnector' =>  array('cMap'=> 'SmartConnector', 'type' => 'Checkbox', 'options'=> false),  
	 	'mainExport' =>  array('cMap'=> 'Zobrazeni v první sadě pro mobilní app', 'type' => 'Hidden', 'options'=> false),
		'secondExport' =>  array('cMap'=> 'Zobrazeni v druhé sadě pro mobilní app', 'type' => 'Hidden', 'options'=> false),
		
		'obrazky' => array('cMap'=> 'Obrázky', 'type' => 'MultiFileSelect','options'=> array('accordionId' => 'accordion', 'noContent' => true, 'showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 60, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 51340 )),
		'ext_id' => array('cMap'=> 'AdminId - Nosilka', 'type' => 'Text', 'options'=> false),
		'inv_title' =>  array('cMap'=> 'Název na fakturu', 'type' => 'Text', 'options'=>false),      
		); 
	public $variantCount = 2;    
	private $_tableKategorie = 'eshop_kategorie';
	private $_tableProductKategorie = 'eshop_product_kategorie';
	private $_tableCategories = 'module_eshop_categories';
	private $_tableName = 'module_eshop_variants';
	private $_tableNameVariantProduct = 'module_eshop_variants_products';
	private $_tableNameVariantaOption = 'module_eshop_variants_options';
	private $_tableNameOptions = 'module_eshop_categories_options';
	private $_tableProduct = 'content_Product';
	private $_tableNodes = 'Nodes';

	private $_tableColors = 'module_eshop_colors';
	private $_tableNameVariantOptions = 'module_eshop_variants_options';
	public function __construct() {
		$this->db = Zend_Registry::getInstance ()->db;
		$this->tree = Zend_Registry::getInstance ()->tree;
		$this->sklaOptions = $skladOptions;
	}

	public function getProductByExtId($ext_id)
	{  
		$select = $this->db->select();
		$select->from ( array (  
			'var' => $this->_tableName), array (     
			'*',) );    
		$select->join ( array (
			'p' => $this->_tableProduct 
			), 'var.id_product = p.id', array (
			'p.id as id_product' ));

		$select->join ( array (
			'nodes' => $this->_tableNodes 
			), 'p.n_id = node.id', array (
			'p.id as id_product' ));

		

  

		$select->where(" ext_id =?",$ext_id);

		$variant = (object)$this->db->fetchRow("select * from ".$this->_tableName." where ext_id =?",$ext_id);
		
		pr($this->tree->getContent($variant->id_product));  
		die;
	}
	
	public function deleteVariantsByContentId($contentId) {
		$where = $this->db->quoteInto ( 'id_product = ?', $contentId );
		$this->db->delete ( $this->_tableName, $where );
	}
	
	
	public function rePriceVariant($input)
	{
		
		$where = $this->db->quoteInto('id =?',$input->variantId);
		$this->db->update($this->_tableName,array('price'=>$input->price),$where);
	}

  
	public function setPurchaseVariant($input) 
	{
		$where = $this->db->quoteInto('id_product =?',$input->variantId);
	  
		$this->db->update($this->_tableName,array('purchase_price'=>$input->price),$where);
	} 
	
	   
	public function setEANVariant($input)    
	{
		$where = $this->db->quoteInto('id_product =?',$input->variantId);
		$this->db->update($this->_tableName,array('ext_id'=>$input->price),$where);	
	}   

	public function setinvtitle($input)    
	{
		$where = $this->db->quoteInto('id =?',$input->variantId);           
		$this->db->update($this->_tableName,array('inv_title'=>$input->data),$where); 	
	}     

	public function getSkladem($value, $p = false, $showTxt = true)
	{
		if($value == '-1'){
			$value = 2;
		}
		if($p){
			$el = 'p';
		} else {
			$el = 'span';
		}   
		
		if($value == 1){    
			$class = 'skladem greenText';
		} else {
			$class = 'neniSkladem';
		}
		
		$head = $this->variantProperty['skladem']['selection'][$value];
		$xhtml = '
			<' . $el . ' class="' . $class . '">' . $head . '
		';		 
		$xhtml .= '
			</' . $el . '>  
		';  	
		return $xhtml;  
	}
	
	public function saveVariats($input, $contentid) {

		$inc = 1;
		foreach ( $input->sort as $i => $v ) {
			$pp = 'varianta_price_' . $i;
			if (! $input->$pp) {
				continue;
			}
			$row = array ();
			$row ['id_product'] = $contentid;
			
			foreach ( $this->variantProperty as $key => $value ) {
				$p = 'varianta_' . $key . '_' . $i;
				$row [$key] = $input->$p;
				if ($key == 'id' && $row [$key] > 0) {
					$id = $row [$key];
					// test na obrázky
					$dbVarianta = $this->getVariantsById ( $id );
					// smaže staré vlastnosi kategorie
					$this->db->delete ( $this->_tableNameVariantOptions, ' id_variant = ' . $id );
				}
				if ($key == 'obrazky' && $dbVarianta ['obrazky'] != $row [$key]) {
					$this->resizePhotos ( $row [$key] );
				}
				if ($row ['price2']) {
					$dif = $row ['price2'] - $row ['price'];
					$p = $row ['price2'] / 100;
					$row ['discount'] = round ( $dif / $p, 0 );
				}
				if(is_array($row[$key])){
					$new = array();
					foreach ($row[$key] as $keyCheck => $value) {
						$new[] = $keyCheck;
					}
					$row[$key] = implode('|', $new);
				}
			}
			$row['poradi'] = $inc;
			$inc++;
			
			if ($id > 0) {
				// test na price > 0 protože jinak set deleted = 1
				if (! $row ['price']) {
					$row ['deleted'] = 1;
				}
				$where = $this->db->quoteInto ( 'id = ?', $id );
				$this->db->update ( $this->_tableName, $row, $where );
				
			} else {
				$this->db->insert ( $this->_tableName, $row );
				$id = $this->db->lastInsertId ();
			}
			
			for ($j = 1; $j < 20; $j++){ //variants loop
				for ($zz = 1; $zz < 500; $zz++){  
					$name = "varianta_obrazky_$j";
					$pn = $name . '_fileSelect'.$zz;
					
					if($input->$pn){
						$pn1 = $name . '_fileSelect'.$zz.'_title';
						$pn2 = $name . '_fileSelect'.$zz.'_alt';
						$pn3 = $name . '_fileSelect'.$zz.'_url';
						$fnid = content_SFSFile::getFileNodeId($input->$pn);
						 
						$nm = Zend_Registry::getInstance()->nodeMeta;  
						$cm = $nm->getMetaById($fnid);
						$cm['fileAlt'] = trim($input->$pn2);   
						$cm['fileUrl'] = trim($input->$pn3);  
						$nm->setMetaById($fnid, $cm);  
					} 
				}   
			}     
			$cat = 'varianta_options_' . $i;
			if (count ( $input->$cat ) > 0) {
				foreach ( $input->$cat as $idOption ) { 
					$this->saveOptions ( $id, $idOption );
				}
				;
			} 
			$id = 0; 
			;

		}

		// pr($input); die();
	}

	public function getEditableProperty($view,$name,$id,$value)
	{
		$variantProp = $this->variantProperty[$name];	
		$url = $view->url(array('cms'=>'inlineedit','setpropertiesvariant'=>'variant',$id=>''), '', true);
		$property = new ContentProperty($name,$variantProp['type'],$value, array(), $variantProp['selection'], $variantProp['options'],false);
	//	
	if($variantProp['selection']){
		$params['urlLoad'] = $view->url(array('cms'=>'inlineedit','getvariantselect'=>'name',$name=>''), '', true);
	}
		return $property->renderInlineEdit($view,$url,$params);
	}
	
	public function renderOption($name, $value)
	{
		return $this->variantProperty[$name]['selection'][$value];
	}
	
	public function isCheckVariantOption($idVariant, $idOption) {
		$select = $this->db->select ();
		$select->from ( array (
				$this->_tableNameVariantaOption 
		), array (
				'id_variant' 
		) );
		$select->where ( 'id_option = ? ', $idOption );
		$select->where ( 'id_variant = ? ', $idVariant );
		return $this->db->fetchOne ( $select );
	}
	
	private function saveOptions($idVariant, $idOption) {
		$data = array (
				'id_variant' => $idVariant,
				'id_option' => $idOption 
		);
		$this->db->insert ( $this->_tableNameVariantOptions, $data );
	}
	
	private function deleteVariantaByIdProduct($id)
	{
		$variants = $this->db->fetchAll('select id,obrazky from '.$this->_tableName.' where id_product =?',$id);
		// smaže i z kategorií
		foreach ($variants  as $value) {
			$nodeId = content_SFSFile::getFileNodeId($value['obrazky']);
			if($nodeId>0){
				$node = $this->tree->getNodeById($nodeId);
				$node->removeNode();
			}
			$this->db->delete($this->_tableNameVariantOptions, ' id_variant = '.$value['id']);
		};
		$this->db->delete($this->_tableName, ' id_product = '.$id);
	}
	
	public function getVariantsById($id, $sleva = false) {
		 
	$price = 'price';   
	if($sleva)
	{
		$price = "round(price *".$sleva.") as price";    
  
	}
		return $this->db->fetchRow ( 'select id,EAN,skladem,poradi,obrazky,'.$price.',price2,discount,realSold,sold,deleted,
				model,id_product,typ,tlak,objem,vlastnosti,prikon,hmotnost,ovladani,alert, 
				mainExport,secondExport   from ' . $this->_tableName . ' where id=?', $id );
		    
	}
	
	public function getJSONVariantSelections($name)
	{

		return json_encode($this->variantProperty[$name]['selection']);
	}
	
	public function saveProperties($id,$name,$value)
	{			
		$where = $this->db->quoteInto('id =? ',$id);
		$data[$name] = $value;
		$this->db->update($this->_tableName,$data,$where);
	}
	
	public function getVariantsByIdProduct($contentId, $onlyOne = false, $adminCms = false, $params = false) {
		$select = $this->db->select ();
		$price = "price";
		if($params['sleva'])
		{  
			$price = "round(price *".$params['sleva'].") as price";    
		}   
		$select->from ( array (  
				'var' => $this->_tableName        
		), array (     
				'*',     
		) );    
		$select->join ( array (
				'p' => $this->_tableProduct 
		), 'var.id_product = p.id', array (
				'p.id as id_product' 
		) );
		if(!$adminCms){
			//$select->where ( 'var.skladem = ? ', 1 ); 
		}
		$select->where ( 'p.id = ? ', $contentId );
		$select->order ( 'poradi ASC ' );
		$varinats = $this->db->fetchAll ( $select );
		if ($onlyOne) {
			return $varinats [0];
		} else {
			return $varinats;
		}
	}
	
	function resizePhotos($strObrazky) {
		$c = new content_Product ();
		$c->properties = array (
				new ContentProperty ( 'obrazky', 'MultiFileSelect', '', array (), array (), array (
						'showSelectFile' => true,
						'inputWidth' => '300',
						'maxFiles' => 10,
						'showUploadFile' => true,
						'uploadFileDirectoryNodeId' => 3880 
				) ) 
		);
		$c->getPropertyByName ( 'obrazky' )->value = $strObrazky;
		$c->createFilesVariant ();
	}
	
	public function getResizedPhotos($strObrazky) {
		$c = new content_Product ();
		$c->properties = array (
				new ContentProperty ( 'obrazky', 'MultiFileSelect', '', array (), array () ) 
		);
		$c->getPropertyByName ( 'obrazky' )->value = $strObrazky;
		return $c->getFilesNames ( 'obrazky' );
	}
	
	public function getVariantById($variantId,$optionId = false) {
		$select = $this->db->select ();
		$select->from ( array (
				'var' => $this->_tableName 
		), array (
				'*' 
		) );
		if($optionId)
		{
			$select->joinLeft ( array (
					'v' => $this->_tableNameVariantaOption
			), 'var.id = v.id_variant', array () );
			$select->joinLeft ( array (
					'o' => $this->_tableNameOptions
			), 'v.id_option = o.id', array ('title as titleOption') );
			$select->where ( 'id_option = ? ', $optionId );
		}
		$select->where ( 'var.id = ? ', $variantId ); 
		$select->where ( 'var.skladem = ? ', 1 );
		
		$select->order ( 'id  asc '  );
		 
		return $this->db->fetchRow ( $select );
	}
	
	public function initCategoriesOptions($idProduct) {
		$categorie = $this->db->fetchAll ( 'select * from ' . $this->_tableCategories . ' ORDER BY title' );
		foreach ( $categorie as $value ) {
			$options = $this->getOptionsByIdCategory ( $value ['id'] );
			$catOptions = array();  
			// tes to jestli je vybraná
			foreach ( $options as $option ) {
				$check = $option ['id_variant'] > 0 ? true : false;
				$catOptions [$option ['id']] = array (
						'name' => $option ['title'],
						'idVariant' => $option ['id_variant'] 
				);
			}
			$value ['options'] = $catOptions;
			$item [] = $value;
		}
		return $item;
	}
	
	public function getOptionsByIdCategory($id) {
		$select = $this->db->select ();
		$select->from ( array (
				'o' => $this->_tableNameOptions 
		), array (
				'*' 
		) );
		$select->joinLeft ( array (
				'v' => $this->_tableNameVariantaOption 
		), 'o.id = v.id_option', array (
				'v.id_variant' 
		) );
		$select->where ( 'o.id_category = ? ', $id );
		$select->order ('title asc');
		return $this->db->fetchAll ( $select );
	}
	
	public function getVariantsByOptions($ids, $contentId, $idVariant = false, $showArray) {
		if (! is_array ( $ids )) {
			$dbIds [] = $ids;
		} else {
			$dbIds = $ids;
		}
		$select = $this->db->select ();
		$select->from ( array (
				'var' => $this->_tableName 
		), array (
				'*' 
		) );
		$select->joinLeft ( array (
				'v' => $this->_tableNameVariantaOption 
		), 'var.id = v.id_variant', array () );
		foreach ( $dbIds as $val ) {
			$select->where ( 'id_option = ? ', $val );
		}
		if ($idVariant) {
			$select->where ( "var.id = ? ", $idVariant );
		}
		$select->where ( 'var.skladem = ? ', 1 );
		$select->where ( "var.id_product = ? ", $contentId );
		$select->group("var.id");
		//pr($select->__toString());
		if ($showArray) {
			$varinats = $this->db->fetchAll ( $select );
			//pr($select->__toString());
			$retArray = array ();
			foreach ( $varinats as $value ) {
				$retArray [] = $value ['id'];
			}
			return $retArray;
		} else {
			return $this->db->fetchAll ( $select );
		}
	}
	
	public function getOptionsVariantAsArray($idCategory,$parentCategory,$idsVariants = false)
	{
		$options = $this->getOptionsVariant(false, $idCategory, false,false ,$parentCategory,false,false,$idsVariants);
		$retOption = array();
		foreach ($options as $value)
		{
			$retOption[$value['id']] = $value['title'];
		}
		return $retOption;
	}
	
	public function getOptionById($idOption, $onlyName = false)
	{
		if($onlyName)
		{
			$option = $this->db->fetchOne("select title from ".$this->_tableNameOptions." where id =?", $idOption);
		}
		else{
			$option = $this->db->fetchRow("select * from ".$this->_tableNameOptions." where id =?", $idOption);
		}
		return $option;
	}
	
	
	
	public function showSelectedOptions($view)
	{
		$string = '<span id="arrowFilter" class="fLeft"></span><span class="fLeft marTop5 marLeft10 marRight5">vybráno:</span>';
		$showString = false;
		if($view->inputGet->colors)
		{
			$showString = true;
			foreach ($view->inputGet->colors as $v)
			{
				$op = $this->getOptionById($v);
				$string .= '<span onclick="deleteOption('.$op['id'].')" class="fLeft marTop5 i-block optionName" rel="'.$op['id'].'">'.$op['title'].' </span>';
			}
		}
		if($view->inputGet->sizes)
		{
			$showString = true;
			foreach ($view->inputGet->sizes as $v)
			{
				$op = $this->getOptionById($v);
				$string .= '<span onclick="deleteOption('.$op['id'].')" class="fLeft marTop5 i-block optionName" rel="'.$op['id'].'">'.$op['title'].' </span>';
			}
		}
		if($view->inputGet->others)
		{
			$showString = true;
			foreach ($view->inputGet->others as $v)
			{
				$op = $this->getOptionById($v);
				$string .= '<span onclick="deleteOption('.$op['id'].')" class="fLeft marTop5 i-block optionName" rel="'.$op['id'].'">'.$op['title'].' </span>';
			}
		}
		if($showString){
			return  $string;
		}
	}


	function setPreText($variant,$parentLink)
	{
		 
		$string = '<table id="prevprod">';
		if($variant['kapLahvi'] > 0)
		{
			$text[] = '<tr><td>Kapacita bordó lahví: </td><td>'.round($variant['kapLahvi']).'</td></tr>';
		}
		if($variant['chlazeni'])
			$text[] =  '<tr><td>Typ chlazení: </td><td><a href="'.$parentLink.'?chlazeni[]='.$variant['chlazeni'].'&showFilter=1#ChildVerticalTab_12">'.$this->variantProperty['chlazeni']['selection'][$variant['chlazeni']].'</a><td></tr>';
		
		if($variant['pocZon'])
			$text [] =  '<tr><td>Počet tep. zón: </td><td>'.$variant['pocZon'].'<td></tr>';
		
		if($variant['vyska'])
			$text [] =  '<tr><td>Rozměry (v/š/h) cm: </td><td>'.$variant['vyska'].'/'.$variant['sirka'].'/'.$variant['hloubka'].'<td></tr>';
		
		if($variant['color'])
			$text[] =  '<tr><td>Barva: </td><td><a href="'.$parentLink.'?color[]='.$variant['chlazeni'].'&showFilter=1#ChildVerticalTab_11">'.$this->variantProperty['color']['selection'][$variant['color']].'</a><td></tr>';
		
		if($variant['hlucnost'] > 0)
		{
			$text[] = '<tr><td>Hlučnost (dB): </td><td>'.round($variant['hlucnost']).'<td></tr>';
		}
		$string .= implode('', $text);
		$string .= '</table>';
		return $string;
	}


function setPreText111()
	{
		$all = $this->db->fetchAll('select * from '.$this->_tableName);
		foreach ($all as $key => $variant) {
			
		$string = $text = array();
		$string = '<ul>';
		if($variant['color'])
			$text[] =  '<li>Barva: '.$this->variantProperty['color']['selection'][$variant['color']].'</li>';
		if($variant['kapLahvi'] > 0)
		{
			$text[] = '<li>Kapacita bordó lahví (0,75 l): '.round($variant['kapLahvi']).'</li>';
		}

		if($variant['chlazeni'])
			$text[] =  '<li>Typ chlazení: '.$this->variantProperty['chlazeni']['selection'][$variant['chlazeni']].'</li>';		
		
		
		
		$string .= implode('', $text);
		$string .= '</ul>';
		$d['preText'] = $string;
		pr($d['preText']);
		$where = $this->db->quoteInto ( "id = ?", $variant['id_product'] );	
		pr($where);
		pr($this->db->update($this->_tableProduct, $d,$where));
		}
die;

	}



	public function getOptionsVariant($contentId = false, $idCategory = false, $idVariant = false, $showArray = false,$parentCategory = false,$showSelect = false, $optionId= false,$idsVariants = false)
	{
		$select = $this->db->select ();
		$select->from ( array (
				'o' => $this->_tableNameOptions
		), array (
				'*'
		) );
		$select->joinLeft ( array (
				'v' => $this->_tableNameVariantaOption
		), 'o.id = v.id_option', array (
				'v.id_variant'
		) );
		$select->joinLeft ( array (
				'var' => $this->_tableName
		), 'v.id_variant = var.id', array (
		) );
		$select->joinLeft ( array (
				'cat' => $this->_tableCategories
		), 'o.id_category = cat.id', array ('cat.title as catTitle'
		) );
		if($parentCategory){
			$select->joinLeft ( array (
					'c' => $this->_tableProduct
			), 'var.id_product = c.id', array () );
		}
		
		if($idCategory)
		{
			$select->where ( 'o.id_category = ? ', $idCategory );
		}
		if($contentId)
		{
			$select->where ( "var.id_product = ? ", $contentId );
		}
		if($idVariant)
		{
			$select->where ( "var.id = ? ", $idVariant );
		}
		if($optionId>0)
		{
			$select->where("v.id_option =?",$optionId);
		}
		if($idsVariants)
		{
			$ids = implode(',', $idsVariants);
        	//pr($ids)
        	$select->where(' var.id in ('.$ids.' )');
		}
		if($parentCategory)
		{
			if(is_array($parentCategory)){
				$childrenIds = $parentCategory;
			} else {
				$childrenIds = $this->tree->getNodeChildrenIds($parentCategory, array(), 'FOLDER');
				$childrenIds[] = $parentCategory;
			}
			 
			if(count($childrenIds)){
				$w = array();
				foreach ($childrenIds as $id){
					$w[] = " c.parent like '%$id%' ";
				}
				$select->where(implode('OR', $w));
			}
		}
		$select->where ( 'var.skladem = ? ', 1);
		$select->order('o.id_category asc');
		$select->order('o.title asc');
		
		$select->group('id');
		if($showArray){
			$option =  $this->db->fetchAll ( $select );
			$retArray = array();
			foreach ($option as $value)
			{
				$retArray[] = $value['id'];
			}
			return $retArray;
		}
		elseif($showSelect)
		{
			//pr($select->__toString());
			$option =  $this->db->fetchAll ( $select );
			$retArray = array();
			foreach ($option as $value)
			{
				$retArray[$value['id']] = $value['title'];
			}
			return $retArray;
		}
		else
		{
			return $this->db->fetchAll ( $select );
		}
		
	}
}	
?>