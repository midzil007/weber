<?
/**
 * Třída reprezentuje Článek
 * @see model_Content
 * @package content
 */
 
use Zend\Validator\Explode;
use Zend\Di\Di;
class content_Product extends content_HtmlFile {
    
	
	public $fotoThumbName = 'pThumb'; 	

	public $fotoCropMini4Name = 'pMinic4';
	public $fotoCropThumbName = 'pThumbc';
	public $fotoCropShowName = 'pShowc';	
	public $fotoShow3Name = 'pShow3';
	 
   	public $fotoCropShow2Name = 'pShowc2'; 
	
   	
   	//používám
   	public $fotoFullName = 'pFull';
   	public $fotoShowName = 'pShow';
   	public $fotoShowName2 = 'pShow2';
   	public $fotoCropMini2Name = 'pMinic2';
	public $variantShow = 0;   
	
   	public $_tableName = 'eshop_product_kategorie';  
	public $_tableName2 = 'eshop_priznaky';
	public $_tableName3 = 'eshop_kategorie';
   	
   	public $variantProperietes = array();
   	public $variantCategories = array();
	
    public   $aptions = array(    
    /*	
		1 => 'AKCE', 
		2 => 'NOVINKA',
		3 => 'TRHÁK', // = TRHAK
		4 => 'NEJPRODÁVANĚJŠÍ',
		5 => 'Výprodej'*/ 
	);   
	 
	public   $skladOptions = array(    	
		1 => 'SKLADEM',  
		2 => 'Není skladem', 
		3 => 'Produkt je dočasně nedostupný',
		4 => 'Předobjednávky'            
	); 
	
	public   $skladOptionsTxt = array(    	
		1 => 'Skladem', 
		2 => 'Není skladem',
		3 => 'Produkt je dočasně <Br />  nedostupný',
		4 => 'Předobjednávky'          
	); 
	
	
	public   $skladOptionsDesc = array(    	
		1 => 'Příští den u Vás', 
		2 => 'Dodání do 10 dnů',
		3 => '',
		4 => ''          
	);  
	 
	function getSkladem($val = 0, $p = false, $showTxt = true){
		if(!$val){
			$val = $this->getPropertyValue('skladem');
		} 
		if($p){
			$el = 'p';
		} else {
			$el = 'span';
		}
		
		if($val == 1){
			$class = 'skladem';
		} else {
			$class = 'neniSkladem';
		}
		
		$head = $this->skladOptionsTxt[$val]; 
		$txt =  $this->skladOptionsDesc[$val];
		$xhtml = '
			<' . $el . ' class="' . $class . '">' . $head . '
		';
		
		if($showTxt){
			$xhtml .= '
				<span>' . $txt . '</span>
			';
		}
		 
		$xhtml .= '
			</' . $el . '>  
		';  	
		return $xhtml;   
	}
	
    public function __construct($config = array())
    {
    	parent::__construct();     
    	$this->_name =  get_class($this);  
    	$this->userName = 'Produkt';
    	$dph = array(21 => 21, 15 => 15);
    	$this->properties[] = new ContentProperty('preText','Text','', array(),array(), array(  'height' => 250));     
    	$this->properties[] = new ContentProperty('parametry','Wysiwyg','', array(),array(), array(  'height' => 250));
    	$this->properties[] = new ContentProperty('parent','MultiPageSelect','', array(), array('root' => 3801, 'display' => 'FOLDER'));  		            	   
    	$this->properties[] = new ContentProperty('files','MultiFileSelect','', array(), array(), array('showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 10, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 53101));
    	
    	
    	$mProducts = new module_Products();
    	$znacky =  $mProducts->getZnacky(false, true);
    	//pr($znacky);
    	if($znacky){
			$this->properties[] = new ContentProperty('znacka','Select', '', array(), $znacky, array(), false);
    	}   
    	
    	$this->properties[] = new ContentProperty('dphQuote','Select', '', array(), $dph, array(), false);
		$this->properties[] = new ContentProperty('video','Text','', array(), array(), array(), false); 
		$this->properties[] = new ContentProperty('onHomePage','Checkbox','', array(), array(), array(), false);
		$this->properties[] = new ContentProperty('onSection','Checkbox','', array(), array(), array(), false);
	//	$this->properties[] = new ContentProperty('akce','CheckboxGroup','', array(), array(), array(), false); 
		$this->properties[] = new ContentProperty('souvisejici','MultiPageSelect','', array(), array('root' => 3801, 'display' => 'ITEM', 'sort'=>'title'));  
    	//$this->properties[] = new ContentProperty('alternativy','MultiPageSelect','', array(), array('root' => 3801, 'display' => 'ITEM'));     
		$this->properties[] = new ContentProperty('sold','Text','', array(), array(), array(), false);	
		$this->properties[] = new ContentProperty('rating','Hidden','', array(), array(), array(), false);
		
		
    } 
    
    function getDetailUrl($view, $node, $detail = true){
    	
    	if($detail){
    		return $node->path;
    	}
    	
    	$path = $this->getSectionPathNode($view);
    	
    	//  pr($vals);  
        return  $path->path;  
    }
      
    function getSectionPathNode($view){
    	$p = $this->getPropertyValue('parent');
    	$vals = helper_MultiSelect::getMultiSelectValues($p); 
    	if($view->inputGet->dvouluzka){
    		//$isDvouluzko = $this->node->nodeId==7370;
			//$isDetske = $this->node->nodeId==39104;  
    		$path = $view->tree->getNodeById(7370);  
    	} elseif($view->inputGet->detske){
    		//$isDvouluzko = $this->node->nodeId==7370;
			//$isDetske = $this->node->nodeId==39104;  
    		$path = $view->tree->getNodeById(39104);  
    	} else {
    		$path = $view->tree->getNodeById(current($vals)); 
    	}
    	return $path; 
    }
    
    private function getShowCount()
    {
    	for ($i = 1; $i < 11; $i++) {
    		$array[$i] = $i ; 
    	}
    	return $array;
    }
    
	function getDetailUrl2($view, $parent, $id, $title){
    	
		/*$p = $parent;      
    	$vals = helper_MultiSelect::getMultiSelectValues($p); 
    	$path = $view->tree->getNodeById(current($vals));   
    	*/
		$path = $view->tree->getNodeById($id);    
        return  $path->path;
    }
    
    public function renderCategories($vals,$view)
    {
    	if($vals)
    	{
    		$text = '';
    		foreach ($vals as $value) {
    			$nodeCat = $view->tree->getNodeSimple($value);
    			if($nodeCat->parent != 3801)
    			{
    				$nodeMain = $view->tree->getNodeSimple($nodeCat->parent);
    				$text .= '<a class="folderBig fs15" href="'.$nodeMain->path.'">'.$nodeMain->title.'</a>';
    			}
    			$text .= '<a class="folderBig fs15" href="'.$nodeCat->path.'">'.$nodeCat->title.'</a>'; 
    		}
    	}
    	return $text;
    }
   
    function show($view, $node){
    	// nasetovat sklad
    	$view->content = $this;  
    	$view->node = $node;      
    	$view->showCount = $this->getShowCount();
    	$template = 'Overviews/Product.phtml';    	
    	// kvůli filteru
		$view->showBorder = true;
    	$view->selectedVariant = $view->mVarianta->getVariantsByIdProduct($this->id,true);
    	  	// test na parent node dodelat
    	  	
     	$view->path = $view->curentPath = $this->getDetailUrl($view, $node, false);
    	$p = $this->getPropertyValue('parent'); 
     	 
    	$vals = helper_MultiSelect::getMultiSelectValues($p);
 		$view->renderParents = $this->renderCategories($vals, $view);
    	if(!$path){
    		$path = $view->tree->getNodeById(max($vals));
    	} 
    	$html = $this->getPropertyValue('html');
    	$htmlProp = $this->renderHTML($view);
    	if($htmlProp){
    		$view->html = '<p>Hlavní parametry<p><ul>'.$htmlProp.'</ul>';
    	}
    	$view->html .= $html;
    	$minParent  = $view->tree->getNodeById(min($vals));
 		foreach ($vals as $v)
 		{
 			if($v > 0)
 			{
 				$parents[] = $v;
 			}
 		}
 		$minParent  = $view->tree->getNodeSimple(min($parents));
 		$view->parentTitle = $minParent->title;
     	$view->curentNode = $path;
     	$view->curentPath = $path->path;
    	return $view->render(Zend_Registry::getInstance()->config->view->contentsDir . $template);
	}
    

	public function renderHTML($view)
	{
		$html = '';
		$variantaContent = $view->mVarianta->variantProperty;
		$variants = $view->mVarianta->getVariantsByIdProduct($this->id);
		$var = $variants[0];
		if($var['smartFrost']>0)
		{
			$html .= '<li>SmartFrost: ano</li>';
		}
		if($var['bioFresh']>0)
		{
			$html .= '<li>Biofresh: ano</li>';
		}
		if($var['noFrost']>0)
		{
			$html .= '<li>Nofrost: ano</li>';
		}
		if($var['enerClass']>0)
		{
			$html .= '<li>Úspornost: '.$variantaContent['enerClass']['selection'][$var['enerClass']].'</li>';
		}
		if($var['color']>0)
		{
			$html .= '<li>Barva: '.$variantaContent['color']['selection'][$var['color']].'</li>';
		}
		if($var['vyska']>0)
		{
			$html .= '<li>Výška (cm): '.$var['vyska'].'</li>';
		}
		if($var['sirka']>0)
		{
			$html .= '<li>Šířka (cm): '.$var['sirka'].'</li>';
		}
		if($var['hloubka']>0)
		{
			$html .= '<li>Hloubka (cm): '.$var['hloubka'].'</li>';
		} 
		if($var['hmotnost']>0)
		{
			$html .= '<li>Hmotnost přístroje (kg): '.$var['hmotnost'].'</li>';
		}
		if($var['spotreba']>0)
		{
			$html .= '<li>Spotřeba elektrické energie za 365 dní: '.$var['spotreba'].'</li>';
		}
		if($var['hlucnost']>0)
		{
			$html .= '<li>Hlučnost (dB): '.$var['hlucnost'].'</li>';
		}
		if($var['pocChlad']>0)
		{
			$html .= '<li>Počet chladicích okruhů: '.$var['pocChlad'].'</li>';
		}
		if($var['chlazeni']>0)
		{
			$html .= '<li>Systém chlazení: '.$variantaContent['chlazeni']['selection'][$var['chlazeni']].'</li>';
		}
		if($var['dobaSklad']>0)
		{
			$html .= '<li>Doba skladování při výpadku proudu (h): '.$var['dobaSklad'].'</li>';
		}
		if($var['obsahCelkem']>0)
		{
			$html .= '<li>Čistý obsah celkem (l): '.$var['obsahCelkem'].'</li>';
		}
		if($var['obsahLed']>0)
		{
			$html .= '<li>Čístý obsah lednice (l): '.$var['obsahLed'].'</li>';
		}
		if($var['obsahMraz']>0)
		{
			$html .= '<li>Čístý obsah mrazáku (l): '.$var['obsahMraz'].'</li>';
		}
		if($var['pocKom']>0)
		{
			$html .= '<li>Počet kompresorů: '.$var['pocKom'].'</li>';
		}
		
		if($var['zmrKapalina']>0)
		{
			$html .= '<li>Zmrazovací kapacita za 24 h(kg): '.$var['zmrKapalina'].'</li>';
		}
		return $html;
		
	}
    
	function initOptions($view)
    {
    	$mVar = new module_Varianta();
    	//$this->id = 1;	
    	$count = 1;
    	$this->variantShow = 1;
    	if($this->id>0)
    	{
    		$variants = $mVar->getVariantsByIdProduct($this->id);
    	}
    	if($variants){
    		$this->variantShow = 0;
    		foreach ($variants as $key=>$value) {
    			$count++;
    			$this->variantShow++;
    			$key++;	
    			$variant = array();
    			foreach ($mVar->variantProperty as $pk=>$prop) {
    				$name = 'varianta_'.$pk.'_'.$key;
    				$value[$pk] = $view->input->$name ?$view->input->$name :$value[$pk];
    				$variant[] = new ContentProperty($name,$prop['type'],$value[$pk], array(), $prop['selection'], $prop['options'],false);
    			
    			}
	   			$this->variantProperietes[] = $variant;    
	   						
    		}
    	}    
    	//pr($variant);
    	//doplnění to prázdné
    	for ($i = $count; $i < $mVar->variantCount; $i++) {
    		$variant = array();
    		foreach ($mVar->variantProperty as $pk=>$prop) {
    			$name = 'varianta_'.$pk.'_'.$i;
    			$val = $view->input->$name ? $view->input->$name :'';
    			$variant[] = new ContentProperty($name,$prop['type'],$val, array(), $prop['selection'], $prop['options'],false);
    		}
			$this->variantProperietes[] = $variant;
    	}
    	$this->variantCategories = $mVar->initCategoriesOptions($view->content->id);
    }
    
     
	function showAdmin($view){		
		parent::showAdminInit($view);
    	$d = $this->getPropertyByName('dateShow')->value;
    	$this->getPropertyByName('dateShow')->value = $d?$d:date('Y-m-d');
    	$this->initOptions($view);
    //	parent::renderAdmin($view);
    	
    } 
    
    function getPriznaky(){
    	$priznaky  = array(); 
    	$selected = $this->getPropertyValue('akce'); 
    	if($selected){ 
    		$selected = helper_MultiSelect::getMultiSelectValues($selected);
    		if(count($selected)){
    			$priznakyAll = $this->getPriznakyAll(); 
    			foreach ($selected as $id){
    				if($priznakyAll[$id]){
    					$priznaky[] = $priznakyAll[$id];
    				}
    			}
    		} 
    	}
    	return $priznaky; 
    }
    
    function getPriznakyAll(){
    	$db = Zend_Registry::getInstance()->db; 
    	$priznaky = $db->fetchAll('SELECT * FROM ' . $this->_tableName2);  
    	$pr = array();
    	foreach ($priznaky as $p){
    		$pr[$p['id']] = $p;
    	}
    	return $pr;
    }
    
  function initOptions_0($view){  
			$m_v = new module_Varianta();
    	$reg = Zend_Registry::getInstance();
			$db = Zend_Registry::getInstance()->db;
    	$tags = new module_Tags();
    	
    	$this->getPropertyByName('tags')->options = $tags->getUsedTagsSelect();
    	$this->getPropertyByName('znacka')->options = $view->tree->getNodesAsSelect(7240, 'FOLDER');
     	if($this->id)
				$nodeId = $db->fetchOne('SELECT n_id FROM NodesContents WHERE c_id = ' . $this->id);
			if($nodeId)
				$view->varianty = $m_v->getVariantyByProductId($nodeId);
			
    	$priznaky = $kategorie = $db->fetchAll('SELECT id,nazev FROM ' . $this->_tableName2);  
			foreach($priznaky as $priznak)
					$this->aptions[$priznak['id']] = $priznak['nazev'];
    	$d = $this->getPropertyByName('akce'); 
    	$d->options = $this->aptions;  
			
			$kategorie = $db->fetchAll('SELECT * FROM ' . $this->_tableName3);

			$view->kategorie = array();
			$x=1;
			$this->katCiselnik = array();
			foreach ($kategorie as $item)
			{
				$this->katCiselnik[$item['id']] = $x;
				$kat['id'] = $item['id'];
				$kat['multiOption'] = $item['multiOption'];
				$kat['nazevKategorie'] = $item['nazevKategorie'];
				for($i=1; $i<=10; $i++)
				{
					if($item['nazevVlastnost_' . $i])
						$vlastnosti[$i] = array(
								'nazev'	=> $item['nazevVlastnost_' . $i],
								'obrazek'	=> $item['obrazek_' . $i]
						);
				}
				$kat['vlastnosti'] = $vlastnosti;
				$view->kategorie[$x++] = $kat;
			}
			
						
			if($nodeId)
				$selectedCats = $db->fetchAll('SELECT * FROM ' . $this->_tableName . ' WHERE idProduct = ' . $nodeId);
			$view->selectedCats = $this->processCats($selectedCats);
			//echo 'omg123';
			//print_r($view->kategorie);
			//die;
    }
    
   
    
		
		private function processCats($cats)
		{
			if(!$cats)
				return null;
			$ret = array();
			foreach($cats as $cat)
			{
				for($i=1; $i<=10; $i++)
					if($cat['vlastnost_' . $i])
						$ret[$cat['cislo_varianty']][$this->katCiselnik[$cat['idKategorie']]][$i] = 1;
			}
			return $ret;
		}
		
		
	function afterUpdate(){   
    	$mTags = new module_Tags();     
    	$mTags->buildTagsList(array_keys(Zend_Registry::getInstance()->config->hasTags->toArray()));   
    }
      
    function createFilesVariant(){
    	$this->createPropertyThumbs(  
    			array(  
    					array(
    							'name' => $this->fotoShowName, 
    							'width' => 330,
    							'height' => 460,
    							'autosize' => false  
    					),
    					array( 
    							'name' => $this->fotoShow3Name,
    							'width' => 85,
    							'height' =>120,  
    							'autosize' => false
    					), 
    					array(
		    				'name' => $this->fotoCropMini2Name, 
		    				'crop_width' => 31,    
		    				'crop_height' => 31,      
		    				'autosize' => false
		    			),
    					array(
    							'name' => $this->fotoShowName2,
    							'width' => 221,
    							'height' => 237,
    							'autosize' => false
    					),
    					
    					array(
    							'name' => $this->fotoFullName,
    							'width' => 780, 
    							'height' => 660,
    							'autosize' => false
    					)  
    			),
    			'obrazky'
    	);
    }
    	
  function onSave(){   	 	     	
    	//parent::onSave();
    }
         
    function onUpdate(){ 
    	//parent::onUpdate();
//    	$mVarianta = new module_Varianta();
//    	$this->updateData = $mVarianta->setOrderVariant($this->updateData);
//    	$mMagnet = new module_ImportMagnet();
//		$mMagnet->checkAllProducts();
//		$mProducts = new module_Products();
//		$mProducts->getSetPropertiesCat();
    	// zmenit poradi v $this->updateData
    
    	
    }
		
		
		function beforeSave($view)
		{
			return $this->checkInput($view);
		}
		
		
		function beforeUpdate($view)
		{
			return $this->checkInput($view);
		}
		
		
		private function checkInput($view)
		{
			//TODO dodelat kontrolu vstupu, zkontrolovat poradi atd...
			/*echo"print view:";
			print_r($view); 
			die;*/
			//konzistence poradi
			$poradiArray = array();
			$i=1;

			while($view->requestParams['varianta_' . $i . '_nazev']){
				$poradiArray[] = $view->requestParams['varianta_' . $i . '_poradi'];
				$i++;
			}
			$test = array_count_values($poradiArray);
			foreach($test as $key => $value)
				if($value > 1)
					return "$value varinty produktu mají nastavené stejné pořadí ($key).";
//			if(!array_key_exists(1, $test))
//							return "Alespoň jedna varianta produktu musí mít nastaveno pořadí 1";
		}
		  
		
		function afterNodeSave($view, $node)
		{   
			parent::onUpdate();
    }
		
         
		function afterNodeUpdate($view, $node)
		{  
			//$this->saveData($view, $node);
			parent::onUpdate();
			
    }
		
    
    private function saveData($view, $node)
		{
			//echo "omg";  
			//print_r($view);
			$m_v = new module_Varianta();
			$idVarianty = $m_v->save($view, $node->nodeId);
			$db = Zend_Registry::getInstance()->db;
			$db->delete($this->_tableName, 'idProduct = ' . $node->nodeId);
			$i=1;
			while($view->requestParams['varianta_' . $i . '_nazev'])
			{ 
				$j=1;
				while($view->requestParams['kategorie_' . $j . '_id'])
				{
					$katId = $view->requestParams['kategorie_' . $j . '_id'];
					$data = array();
					$data['idProduct'] = $node->nodeId;
					$data['cislo_varianty'] = $i;
					$data['idVarianta'] = $idVarianty[$i];
					$data['idKategorie'] = $katId;
						foreach($view->requestParams['varianta_' . $i . '_kategorie_' . $j++] as $index)
						{
							if(!$index)
								continue;
							//echo 'vlastnost_' . $index;
							$data['vlastnost_' . $index] = 1;
							//print_r($data);
						}
						$db->insert($this->_tableName,$data);
				}
				$i++;
			}
		} 
}

?>
