<?
/**
 * Třída reprezentuje Článek
 * @see model_Content
 * @package content
 */
 
class content_Product extends content_HtmlFile {
    
	public $fotoFullName = 'pFull';
	public $fotoShowName = 'pShow'; 
	public $fotoThumbName = 'pThumb'; 	
	public $fotoCropMini2Name = 'pMinic2';
	public $fotoCropMini4Name = 'pMinic4';
	public $fotoCropThumbName = 'pThumbc';
	public $fotoCropShowName = 'pShowc';	
	public $fotoShow3Name = 'pShow3';
	public $fotoShow2Name = 'pShow2';
   	public $fotoCropShow2Name = 'pShowc2'; 
	
	
	
   	public $_tableName = 'eshop_product_kategorie';  
	public $_tableName2 = 'eshop_priznaky';
	public $_tableName3 = 'eshop_kategorie';
   	
   	
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
    	
    	$this->properties[] = new ContentProperty('preText','hidden','', array(),array(), array(  'height' => 250));   
    	$this->properties[] = new ContentProperty('parametry','Wysiwyg','', array(),array(), array(  'height' => 250));  
    	            
    	$this->properties[] = new ContentProperty('parent','MultiPageSelect','', array(), array('root' => 3801, 'display' => 'FOLDER'));  
		     
    	$this->properties[] = new ContentProperty('photos','MultiFileSelect','', array(), array(), array('showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 20, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 3880 ));   
    	      
    	$this->properties[] = new ContentProperty('files','MultiFileSelect','', array(), array(), array('showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 10, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 7260)); 
		$this->properties[] = new ContentProperty('price','Text','', array(), array(), array(), false);
		$this->properties[] = new ContentProperty('price2','Text','', array(), array(), array(), false);  
		$this->properties[] = new ContentProperty('znacka','Select','', array(), array(), array(), false);  
		$this->properties[] = new ContentProperty('video','Text','', array(), array(), array(), false); 
		$this->properties[] = new ContentProperty('hmotnost','Text','', array(), array(), array(), false);     
		$this->properties[] = new ContentProperty('akce','CheckboxGroup','', array(), array(), array(), false); 
		$this->properties[] = new ContentProperty('souvisejici','MultiPageSelect','', array(), array('root' => 3801, 'display' => 'ITEM', 'sort'=>'title'));  
    	$this->properties[] = new ContentProperty('alternativy','MultiPageSelect','', array(), array('root' => 3801, 'display' => 'ITEM'));  
    	$this->properties[] = new ContentProperty('kod','Text','', array(), array(), array(), false);  
		$this->properties[] = new ContentProperty('EAN','Text','', array(), array(), array(), false);  
		$this->properties[] = new ContentProperty('skladem','Select','', array(), $this->skladOptions, array(), false); 
		$this->properties[] = new ContentProperty('oldid','Text','', array(), array(), array(), false);   
		$this->properties[] = new ContentProperty('sold','Text','', array(), array(), array(), false); 
		$this->properties[] = new ContentProperty('darek','MultiPageSelect','', array(), array('root' => 3801, 'display' => 'ITEM'));  
    	$this->properties[] = new ContentProperty('epay','Select','', array(), array(1 => 'ANO', 2 => 'NE'), array(), false); 		 
		$this->properties[] = new ContentProperty('prodejny','Select','', array(), array(1 => 'ANO', 2 => 'NE'), array(), false);
		$this->properties[] = new ContentProperty('notAvailablePhotos','Text','', array(), array(), array(), false);   
		// znacka        
		
		for($i = 1; $i <= 15; $i++){
			$this->properties[] = new ContentProperty('varianta_' . $i . '_nazev','Text','', array(), array(), array(), false);
			$this->properties[] = new ContentProperty('varianta_' . $i . '_id','Text','', array(), array(), array(), false);
			$this->properties[] = new ContentProperty('varianta_' . $i . '_EAN','Text','', array(), array(), array(), false);
			$this->properties[] = new ContentProperty('varianta_' . $i . '_dostupnost','Select','', array(), $this->skladOptions, array(), false);
			$this->properties[] = new ContentProperty('varianta_' . $i . '_poradi','Text','', array(), array(), array(), false);
			$this->properties[] = new ContentProperty('varianta_' . $i . '_obrazky','MultiFileSelect','', array(), array(), array('showSelectFile' => true, 'inputWidth' => '300', 'maxFiles' => 20, 'showUploadFile' => true, 'uploadFileDirectoryNodeId' => 3880 ));   
			
			$this->properties[] = new ContentProperty('varianta_' . $i . '_extId','Hidden','', array(), array(), array(), false);
			$this->properties[] = new ContentProperty('varianta_' . $i . '_family_code','Hidden','', array(), array(), array(), false);
			$this->properties[] = new ContentProperty('varianta_' . $i . '_logistic_number','Hidden','', array(), array(), array(), false);
			$this->properties[] = new ContentProperty('varianta_' . $i . '_item_group_id','Hidden','', array(), array(), array(), false);  
 			
			$this->properties[] = new ContentProperty('varianta_' . $i . '_colorId','Hidden','', array(), array(), array(), false);
			$this->properties[] = new ContentProperty('varianta_' . $i . '_sizeId','Hidden','', array(), array(), array(), false); 
			$this->properties[] = new ContentProperty('varianta_' . $i . '_cena','Hidden','', array(), array(), array(), false);   
			$this->properties[] = new ContentProperty('varianta_' . $i . '_cena2','Hidden','', array(), array(), array(), false);
			$this->properties[] = new ContentProperty('jednoPrice','Hidden','', array(), array(), array(), false);
			$this->properties[] = new ContentProperty('jednoPrice2','Hidden','', array(), array(), array(), false); 
			$this->properties[] = new ContentProperty('jednoVarinantId','Hidden','', array(), array(), array(), false);   
			$this->properties[] = new ContentProperty('dvojPrice','Hidden','', array(), array(), array(), false);
			$this->properties[] = new ContentProperty('dvojPrice2','Hidden','', array(), array(), array(), false);
			$this->properties[] = new ContentProperty('dvojVarinantId','Hidden','', array(), array(), array(), false); 
		      
			
		}	  
		
    	$this->properties[] = new ContentProperty('isBlancheporte','Hidden','', array(), array(), array(), false);    
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
    
	function getDetailUrl2($view, $parent, $id, $title){
    	
		/*$p = $parent;      
    	$vals = helper_MultiSelect::getMultiSelectValues($p); 
    	$path = $view->tree->getNodeById(current($vals));   
    	*/
		$path = $view->tree->getNodeById($id);    
        return  $path->path;
    }
   
    function show($view, $node){
    	$template = 'Project.phtml';	
    	$view->content = $this;  
    	$view->node = $node;      
    	$template = 'Overviews/Product.phtml';
    	$p = $this->getPropertyValue('parent');   
     	
     	
    	$path = $this->getSectionPathNode($view);
    	  
     	$view->curentNode = $path; 
     	$dif= round($dif/$p, 1); 
        $dif = str_replace('.', ',', $dif);  
        $view->difp =$this->difp = $dif ? '-'.$dif.'% ':'';
    	        
    	
//    	$view->photos = $view->content->getFilesNames('photos');    
//     	$view->files = $view->content->getFilesNames('files');    
//		$view->price = $price = $this->getPropertyValue('price');     
//       	$price2  = $this->getPropertyValue('price2');
//        $view->price2 = $price2;// ? ''.$price2.',-':''; 
//        $dif = $price2 - $price;
//         $p = $price2/100;
		$isBlanche = $this->getPropertyValue('isBlancheporte');
         $m_v = new module_Varianta();
         $view->sizes = $view->variants = $m_v->getGroupSizes($this->id);
        $view->allColors = $view->mVarianta->getGroupSizesColor($this->id);
         $view->allVariants = $m_v->getGroupSizes($this->id, true);
       //  $view->inputGet->logistic_number = $view->inputGet->logistic_number;
       
         if($view->inputGet->ajax) 
         {
         	if($view->inputGet->action == 'changeVariant')
         	{		
         		$m = $view->mVarianta->getVariants($this->id, false,$view->inputGet->logisticNumber);
         		$view->colors = $view->mVarianta->getColorsByLogNum($this->id, $view->inputGet->logisticNumber);
         		$view->inputGet->color = $m[0]['colorId'];
         		$view->variants = $view->mVarianta->getVariants($this->id, $view->inputGet->color, false);
         		$view->sizes = $view->mVarianta->setSize($view->sizes, $view->variants);
         		$view->prod = $view->mVarianta->getProductByProperties($view->allVariants,$view->inputGet->color,$view->inputGet->logisticNumber);
			 	$view->photos = $this->getFilesNames('varianta_'.$view->prod['variantId'].'_obrazky');
         		echo $view->render('templates/Contents/Overviews/ProductInner.phtml');
         		die();
         	}
         	if($view->inputGet->action == 'changeColor')
         	{
         		// dostupné varianty k barvě
         
         		$view->variants = $view->mVarianta->getVariants($this->id, $view->inputGet->color, false);
         		//pr($view->sizes);
         		$view->sizes = $view->mVarianta->setSize($view->sizes, $view->variants);
         		$m = $view->mVarianta->getVariants($this->id, false,$view->inputGet->logisticNumber);
         		//$view->prod = $view->mVarianta->getProductByProperties($view->variants,$view->inputGet->color,false,$this->id);
         		$view->prod = $view->mVarianta->getProductByTemp($view, $view->variants,$view->inputGet->color,$view->inputGet->logisticNumber,$this->id);
         		// když neexistuje varianta, načte varianty podle barvy a nasetujeme 1;
         		if(!$view->prod){
         			$t = $view->mVarianta->getVariants($this->id, $view->inputGet->color, false);
         			$view->prod = $t[0];
         			$view->colors = $view->mVarianta->getColorsByLogNum($this->id, $view->inputGet->logisticNumber);
         		}
         		$view->inputGet->logisticNumber = $view->prod['logisticNumber'];
         		$view->colors = $view->mVarianta->getColorsByLogNum($this->id, $view->inputGet->logisticNumber);
			 		$view->photos = $this->getFilesNames('varianta_'.$view->prod['variantId'].'_obrazky');
			 	
			 	    
         		echo $view->render('templates/Contents/Overviews/ProductInner.phtml');
         		die();
         	}	
         }
         else{
			$view->inputGet->logisticNumber = $view->sizes[0]['logisticNumber'];
			 if(!$view->inputGet->color){
			 	$m = $view->mVarianta->getVariants($this->id, false,false,$view->sizes[0]['nazev']);
			 	$view->colors = $view->mVarianta->getColorByArray($m);
			 	$view->inputGet->color = $view->sizes[0]['colorId'];
			 	if($view->inputGet->variantdId){
			 		$view->prod = $view->mProducts->getProductByNodeIdVariant($node->nodeId,$view->inputGet->variantdId);
			 		$view->inputGet->color = $view->prod['colorId'];
			 	}
			 	else{
			 		if($this->id == 27535){
			 		
			 			$view->prod = $view->sizes[1];
			 			$view->inputGet->logisticNumber= $view->prod['logisticNumber'];
			 		}
			 		else{
			 			$view->prod = $view->mVarianta->getProductByProperties($view->sizes,$view->inputGet->color,$view->inputGet->logisticNumber);
			 			
			 		}
			 	}
			 	
			 		$view->photos = $this->getFilesNames('varianta_'.$view->prod['variantId'].'_obrazky');
			 	
			 } 
         }
         
		if(!$view->prod)
			{
				 $view->prod  = $m_v->getFirstVariant($this->id);
				 $view->photos = $this->getFilesNames('varianta_'.$view->prod['variantId'].'_obrazky');
			}
			 
		
     	$view->path = $view->curentPath = $this->getDetailUrl($view, $node, false);     	
     	return $view->render(Zend_Registry::getInstance()->config->view->contentsDir . $template);
    }
    
    
    
    function getPriceVariant($id){
    	
    	$inc = 1;
    	$mVariant = new module_Varianta();
    	$sizes= $mVariant->getGroupSizes($id);
    	$color = $sizes[0]['colorId'];
    	// fix varianty 
		if($id == 27535){
		
			$prodVarinta = $sizes[1];
				}
		else{
    		$prodVarinta = $mVariant->getProductByProperties($sizes,$color,false);
				}    	
    	return $prodVarinta;
    }
	    
    function getPrice($view){ 
    	    
    	return $this->getPropertyValue('price'); 
    }
     
	function showAdmin($view){    	
    	parent::showAdminInit($view);
    	
  		 
    	$d = $this->getPropertyByName('dateShow')->value;
    	$this->getPropertyByName('dateShow')->value = $d?$d:date('Y-m-d');
    	$this->initOptions($view);    		 
    	
    	parent::renderAdmin($view);
    	
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
    
  function initOptions($view){  
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
     
    
       	
	function createFiles( ){
    	$settings = Zend_Registry::getInstance()->settings; 
    	     	    
    	$this->createPropertyThumbs(  
    		array(
    			array(
			        'name' => $this->fotoCropShow2Name, 
			        'crop_width' => 223,        
			        'crop_height' =>223,      
			        'autosize' => false 
			       ), 
    			array(
    				'name' => $this->fotoShowName, 
    				'width' => 320,         
    				'height' => 320,       
    				'autosize' => false
    			),   
    			array(
    				'name' => $this->fotoShow3Name, 
    				'width' => 212,        
    				'height' =>148,     
    				'autosize' => false
    			),       		
    			array(
    				'name' => $this->fotoCropMini4Name, 
    				'crop_width' => 112,    
    				'crop_height' => 112,      
    				'autosize' => false
    			),
    			array(
    				'name' => $this->fotoShow2Name, 
    				'width' => 215,        
    				'height' =>215,     
    				'autosize' => false
    			),
    			array(
    				'name' => $this->fotoCropMini2Name, 
    				'crop_width' => 31,    
    				'crop_height' => 31,      
    				'autosize' => false
    			),
    			array(
    				'name' => $this->fotoCropShowName, 
    				'crop_width' => 332,        
    				'crop_height' =>332,     
    				'autosize' => false
    			),
    			array( 
    				'name' => $this->fotoCropThumbName, 
    				'crop_width' => 106,    
    				'crop_height' => 106,      
    				'autosize' => false
    			),
    			array(
    				'name' => $this->fotoThumbName, 
    				'width' => 90,    
    				'height' => 2000,
    				'autosize' => false 
    			),
    			array(
    				'name' => $this->fotoFullName, 
    				'width' => 780,  
    				'height' => 660,       
    				'autosize' => false 
    			) 
    		),  
    		'photos'   
    	);   
    	  
    	for($i = 1; $i <= 15; $i++){ 
			$this->createPropertyThumbs(  
	    		array(
	    			array(
				        'name' => $this->fotoCropShow2Name, 
				        'crop_width' => 223,        
				        'crop_height' =>223,      
				        'autosize' => false 
				       ), 
	    			array(
	    				'name' => $this->fotoShowName, 
	    				'width' => 320,         
	    				'height' => 320,       
	    				'autosize' => false
	    			),   
	    			array(
	    				'name' => $this->fotoShow3Name, 
	    				'width' => 212,        
	    				'height' =>148,     
	    				'autosize' => false
	    			),       		
	    			array(
	    				'name' => $this->fotoCropMini4Name, 
	    				'crop_width' => 112,    
	    				'crop_height' => 112,      
	    				'autosize' => false
	    			),
	    			array(
	    				'name' => $this->fotoCropMini2Name, 
	    				'crop_width' => 31,    
	    				'crop_height' => 31,      
	    				'autosize' => false
	    			),
	    			array(
    					'name' => $this->fotoShow2Name, 
    					'width' => 215,        
    					'height' =>215,     
    					'autosize' => false
    				),
	    			array(
	    				'name' => $this->fotoCropShowName, 
	    				'crop_width' => 332,        
	    				'crop_height' =>332,     
	    				'autosize' => false
	    			),
	    			array( 
	    				'name' => $this->fotoCropThumbName, 
	    				'crop_width' => 106,    
	    				'crop_height' => 106,      
	    				'autosize' => false
	    			),
	    			array(
	    				'name' => $this->fotoFullName, 
	    				'width' => 780,  
	    				'height' => 660,       
	    				'autosize' => false 
	    			) 
	    		),  
	    		'varianta_' . $i . '_obrazky'  
	    	);
    	}  	
	    	
    }
     
    	
  function onSave(){   	
    	$this->createFiles();		     	
    	//parent::onSave();
    }
         
    function onUpdate(){
    	$this->createFiles();  
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
			if(!array_key_exists(1, $test))
							return "Alespoň jedna varianta produktu musí mít nastaveno pořadí 1";
		}
		
		
		function afterNodeSave($view, $node)
		{   
			$this->saveData($view, $node);
			$import = new module_ImportMagnet($view); 
    }
		
         
		function afterNodeUpdate($view, $node)
		{  
			$this->saveData($view, $node);
			$import = new module_ImportMagnet($view); 
   			$import->setPriceAsFirstActiveVariantPRice();
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
