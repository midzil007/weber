<?
/**
 * Třída reprezentuje Článek
 * @see model_Content
 * @package content
 */
 
use Zend\Validator\Explode;
use Zend\Di\Di;
class content_Product extends Content {
    
	
	public $fotoFullName = 'pFull';
	public $fotoShowName = 'pShow';
	public $fotoShow4Name = 'pShow4';
    public $fotoThumbName = 'pThumb'; 
    public $fotoMiniName3 = 'pMini';
    public $fotoMiniName = 'pMini3';
    public $fotoCropMini2Name = 'pMinic2';
    public $fotoCropMini3Name = 'pMinic3';
    public $fotoCropMini4Name = 'pMinic4';
    public $fotoThump4Name =	'pMini4';
   	public $fotoCropThumbName = 'pThumbc';  
	public $fotoShow2Name = 'pShow2';
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
		 

    public function __construct($config = array())
    {
    	$this->_name =  get_class($this);   
    	$this->userName = 'Produkt'; 
    	$dph = array(21 => 21, 15 => 15); 
		//$this->properties[] = new ContentProperty('title2','Text','', array(), array(), array(), false); 
    	$this->properties[] = new ContentProperty('preText','Wysiwyg','', array(),array(), array(  'height' => 250));     
    	$this->properties[] = new ContentProperty('parametry','Wysiwyg','', array(),array(), array(  'height' => 250));
		$this->properties[] = new ContentProperty('html','Wysiwyg','', array(),array(), array(  'height' => 250));  

		     
		$this->properties[] = new ContentProperty('heureka-title','Text','', array(), array(), array(), false);   
		
    	$this->properties[] = new ContentProperty('parent','MultiPageSelect','', array(), array('root' => 3801, 'display' => 'FOLDER')); 
		$this->properties[] = new ContentProperty('video','Wysiwyg','', array(),array(), array(  'height' => 250));  
		$this->properties[] = new ContentProperty('zboziProduct','Wysiwyg','', array(),array(), array(  'height' => 250));    		 		            	   
    	$this->properties[] = new ContentProperty('files','MultiFileSelect','', array(), array(), array('showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 10, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 53098));
    	// $this->properties[] = new ContentProperty('photos','MultiFileSelect','', array(), array(), array('showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 20, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 3880 ));
    	
    	$mProducts = new module_Products();
    	$znacky =  $mProducts->getZnacky(false, true);    
    	//pr($znacky); 
    	// if($znacky){   
			$this->properties[] = new ContentProperty('znacka','Select', '', array(), $znacky, array(), false);
		//	$this->properties[] = new ContentProperty('rada','Select', '', array(), $znacky, array(), false);
    	//}      
    		
    	$this->properties[] = new ContentProperty('dphQuote','Select', '', array(), $dph, array(), false);   
		$this->properties[] = new ContentProperty('akce','CheckboxGroup','', array(), array(), array(), false); 
		$this->properties[] = new ContentProperty('souvisejici','MultiPageSelect','', array(), array('root' => 3801, 'display' => 'ITEM', 'sort'=>'title'));
		$this->properties[] = new ContentProperty('prop','MultiPageSelect','', array(), array('root' => 76947, 'display' => 'ITEM', 'sort'=>'title'));
		$this->properties[] = new ContentProperty('pece','MultiPageSelect','', array(), array('root' => 3801, 'display' => 'ITEM', 'sort'=>'title'));
		//$this->properties[] = new ContentProperty('filty','MultiPageSelect','', array(), array('root' => 3801, 'display' => 'ITEM', 'sort'=>'title')); 

    	//$this->properties[] = new ContentProperty('alternativy','MultiPageSelect','', array(), array('root' => 3801, 'display' => 'ITEM'));     
	// $this->properties[] = new ContentProperty('poradi','Text','', array(), array(), array(), false);	
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
    	$path = $view->tree->getNodeById(current($vals)); 
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
    				//$text .= '<a class="folderBig fs15" href="'.$nodeMain->path.'">'.$nodeMain->title.'</a>';
    			}
    			$text .= '<a class="folderBig fs15" href="'.$nodeCat->path.'">'.$nodeCat->title.'</a>'; 
    		}
    	}
    	return $text;
    }
	
	 public function renderOptionsLink($view)
    {
    	$idZnacka = $this->getPropertyValue('znacka');
		$optionKateorie = $view->mVarianta->getOptionsVariant($this->id,false,$view->selectedVariant->id);
		if($optionKateorie)
		{
			$string = '<p>Kategorie</p>';
			$string .= '<table class="">';
			$idCat = '';
			$options = array();
			foreach ($optionKateorie as $key=>$value) {
				if($value['id_category'] != $idCat)
				{
					$idCat = $value['id_category'];
					$string .= '<tr>';
					if($value['photo']){
						$string .= '<td><img src="'.$value['photo'].'"/ height="50" alt="'.$value['catTitle'].'">';
					}
					$string .= '<td>'.$value['catTitle'].': </td>';
					
					$options[] = $value['title'];
				}
				else{
					$options[] = $value['title'];
				}
				if($optionKateorie[$key+1] != $idCat){
					$string .= '<td>'.implode(',', $options).'</td>';
					$options = array();
    				$string .= '</td>';
					$string .= '</tr>';	
				}
			}
			
    		
			$string .= '</table>';
		}
	
    	if($idZnacka > 0)
    	{
    		$string .= '<div class="spacer10"></div>';
    		$string .= '<p>Značka</p>';
    		$string .= '<table>';
    		$znacka = $view->mProducts->getZnacky($idZnacka);
    		$string .= '<tr>';
    			$string .= '<td>Výrobce:</td>';
 				$string .= '<td><a class="" href="/znacky?znacka='.$znacka['id'].'">'.$znacka['nazev'].'</a></td>';
 			$string .= '</tr>';
		
			if($znacka['logo']):
				$string .= '<tr>';
    			$string .= '<td>'.helper_FrontEnd::showPhoto($znacka['logo'],'sysThumb').'</td>';
				$string .= '</tr>';
			endif;
			if($znacka['popis']):
				$string .= '<tr>';
				$string .= '<td>'.$znacka['popis'].'</td>';
 				$string .= '</tr>';
			endif;
			
    	}
    	
 		return $string;
    }


	function setSleva()
	{
		$p = $this->getPropertyValue('parent'); 
		$vals = helper_MultiSelect::getMultiSelectValues($p);
		$sleva = array(7237,7767,7764,74680,75261);  
		$slevaState = false; 
		foreach ($vals as $v)
 		{
			if(in_array($v, $sleva))
			{
				return true;
			}
 		}  
		return false;
	}
	
   
    function show($view, $node){
    	// nasetovat sklad
    	$view->content = $this;  
    	$view->node = $node;      
		$view->isgallery = true;
			$view->noDeteteParent = 1;
    	$view->showCount = $this->getShowCount();
		
		
        
    	$template = 'Overviews/Product1.phtml';    	
    	// kvůli filteru    
    	$p = $this->getPropertyValue('parent'); 
		$view->showBorder =  $view->isProduct = true;
		$vals = helper_MultiSelect::getMultiSelectValues($p); 
		$minParent  = $view->tree->getNodeById(min($vals));
	  
 		foreach ($vals as $v)
 		{
 			if($v > 0)
 			{ 
 				$parents[] = $v;  
 			}
 		}
		$params = array();   
     
		if($_SESSION['sl'] && $this->setSleva())   
		{  
			$params['sleva'] = $_SESSION['sl'];
		}
    	$view->selectedVariant = $view->mVarianta->getVariantsByIdProduct($this->id,true,false,$params);
		/// není skladem
		if(!$view->selectedVariant)
		{
			$view->selectedVariant = $view->mVarianta->getVariantsByIdProduct($this->id,true,true,$params);	
		}
    	  	// test na parent node dodelat
    	  	
    	$view->files = $view->content->getFilesNames('files');
     	$view->path = $view->curentPath = $this->getDetailUrl($view, $node, false);
		$view->priznaky = $this->getPriznaky(); 
     	$arraysNodeKavovary = array(7391,74760,7234);
		$view->isKavovary = false;
		foreach ($arraysNodeKavovary as $value) {
			if(in_array($value, $vals)){
				$view->isKavovary = true;
			}
		}      
 		$view->renderParents = $this->renderCategories($vals, $view);
    	if(!$path){
    		$view->parentMax = $path = $view->tree->getNodeById(max($vals));
    	} 
    	$view->virtualni = $this->getPropertyValue('virtualni');
		$view->virtualniPocet = $this->getPropertyValue('pocet-virtualni');
    	$view->souvisejici = $this->getSouvisejici($view); 
		 $view->pece = $view->content->getSouvisejici($view,'pece'); 
		
    	$view->html= helper_FrontEnd::prepareResizeText($this->getPropertyValue('html'));
		$minParent  = $view->tree->getNodeById(min($vals));
 		foreach ($vals as $v)
 		{
 			if($v > 0)
 			{
 				$parents[] = $v;
 			}
 		}
		if($this->state == 'ARCHIVED')
		{
			$view->isArchived = true;
		} 
 		$minParent  = $view->tree->getNodeSimple(min($parents));
 		$view->parentTitle = $minParent->title;
     	$view->curentNode = $path;
     	$view->curentPath = $path->path;
		$view->productNode = $view->node;    
		$vsechnyZnacky = $view->tree->getNodeSimple(74592);
		$view->pretext = $view->mVarianta->setPreText($view->selectedVariant,$vsechnyZnacky->path);
     	$view->mBasket->saveLastVisitedToCookie($view->node->nodeId,$view->selectedVariant['id']);
		if($_GET['galll'])
		$view->showHPBan = false;
		return $view->render(Zend_Registry::getInstance()->config->view->contentsDir . $template);

	}
    

	private function getSouvisejici($view,$pece = false)
	{
		$souvisejici = $pece ? "pece":"souvisejici";
		$souvisejici = $this->getPropertyValue($souvisejici);   
		if($souvisejici) 
		{
			$souv = array();  
			$vals = helper_MultiSelect::getMultiSelectValues($souvisejici); 
			$params['souvisejici']= implode(',',$vals); 
			 
			return $view->mProducts->getProducts('soldPrice', 'desc', 0,12, $params,$view);
		}
		
	}
       
	
	
	function initOptions($view)
    {
    	   
    	$db = Zend_Registry::getInstance()->db;
    	$mVar = new module_Varianta();
    	//$this->id = 1;	
    	$count = 1;
    	$this->variantShow = 1;
    	if($this->id>0)
    	{
    		$variants = $mVar->getVariantsByIdProduct($this->id, false, true);
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
    	$priznaky = $kategorie = $db->fetchAll('SELECT id,nazev FROM ' . $this->_tableName2);
    	foreach($priznaky as $priznak)
    		$this->aptions[$priznak['id']] = $priznak['nazev'];
    	$d = $this->getPropertyByName('akce');
    	$d->options = $this->aptions;
		  
    }
    
     
	function showAdmin($view){
		//parent::renderAdmin($view);		
		parent::showAdminInit($view);
    	$d = $this->getPropertyByName('dateShow')->value;
    	$this->getPropertyByName('dateShow')->value = $d?$d:date('Y-m-d');
    	$this->initOptions($view);
    	
    	
    } 
    
     function getPriznaky($top = false){
    	$topPriznaky = array(13,6,21);
    	$priznaky  = array(); 
    	$selected = $this->getPropertyValue('akce'); 
    	if($selected){ 
    		$selected = helper_MultiSelect::getMultiSelectValues($selected);
    		if(count($selected)){
    			$priznakyAll = $this->getPriznakyAll();   
    			foreach ($selected as $id){
    				if(in_array($id,$topPriznaky) && $top == true && $priznakyAll[$id])
    				{   
    					$priznaky[] = $priznakyAll[$id];   
					}       
					elseif((!in_array($id, $topPriznaky)) && !$top && $priznakyAll[$id]){ 
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
    
 
    
   
		
	function afterUpdate(){   
    	$mTags = new module_Tags();     
    	$mTags->buildTagsList(array_keys(Zend_Registry::getInstance()->config->hasTags->toArray()));   
    }
      
	  
	  
    function createFilesVariant(){
    	$this->createPropertyThumbs(  
    			array(  
    					   			array(
    				'name' => $this->fotoShowName, 
    				'width' => 320,         
    				'height' => 320,       
    				'autosize' => false
    			),   
    			array(
    				'name' => $this->fotoShow4Name, 
    				'width' => 272,         
    				'height' =>180,     
    				'autosize' => false
    			), 
    			array(
    				'name' => $this->fotoShow2Name, 
    				'width' => 215,        
    				'height' =>215,     
    				'autosize' => false
    			),  
    			array(
    				'name' => $this->fotoCropThumbName, 
    				'crop_width' => 280,    
    				'crop_height' => 215,      
    				'autosize' => false
    			),    		
    			array(
    				'name' => $this->fotoCropMini3Name, 
    				'crop_width' => 82,    
    				'crop_height' => 82,      
    				'autosize' => false
    			),
    			array(
    				'name' => $this->fotoCropMini4Name, 
    				'crop_width' => 100,    
    				'crop_height' =>100,      
    				'autosize' => false
    			),
    			array(
    				'name' => $this->fotoCropMini2Name, 
    				'crop_width' => 31,    
    				'crop_height' => 31,      
    				'autosize' => false
    			),
    			array(
    				'name' => $this->fotoThump4Name, 
    				'width' => 44,    
    				'height' => 31,      
    				'autosize' => false
    			),
    			array(
    				'name' => $this->fotoThumbName, 
    				'width' => 90,    
    				'height' => 2000,
    				'autosize' => false 
    			),
    			array(
    				'name' => $this->fotoMiniName3, 
    				'width' => 112,    
    				'height' => 2000,   
    				'autosize' => false 
    			),
    			array(
    				'name' => $this->fotoFullName, 
    				'width' => 1000,  
    				'height' => 770,       
    				'autosize' => false  
    			) 
    			),
    			'obrazky'
    	);
    }
    	
		
	
    
    function onDelete(){
    	
    	
    }
	
	function setDateArchived($state)
	{
		if($state== 'ARCHIVED')
		{
			$this->getPropertyByName('dateArchived')->value  = (date('Y')+1).'-'.date("m-d H:i:s");
		}
		if($state == 'PUBLISHED')
		{
			$this->getPropertyByName('dateArchived')->value = 'NULL';
		}
		$this->update();
	}
    
  function onSave(){   	 	     	
    	//parent::onSave();
    	
    }
         
    function onUpdate(){
    
    	//
    	
    
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
		 
			$mP = new module_Products();
			$mP->addToCache(); 
			return $this->checkInput($view);
		}
		
		
		function beforeUpdate($view)
		{
			// pr($view);
			// pr($this->checkInput($view))
			// die;
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
