<?php

class module_XMLFeed2 
{    	 
	public $newLine = "\n";
	public $sitemap = '';
	
	private $_vlastnostiHeureka = array(
		1,
		2,  
		3,
		4,
		5,
		6,
		7, 
		8,
		) ;
		
	private $_vlastnostZbozi = array(    
		5 => 'samočistící funkce',
		12 => 'nastavení hrubosti mletí',
		11 => 'nastavení výšky výpusti', // zbozi
		9 => 'podpora PoDů', // zbozi
		10 => 'parní tryska', // zbozi
		3 => 'připojení na zdroj vody',
		
	);
		
    function __construct()
    {    
    	$this->tree =  Zend_Registry::getInstance()->tree;
        $this->db =  Zend_Registry::getInstance()->db;
        $this->config =  Zend_Registry::getInstance()->config; 
		$this->mProducts = new module_Products();
    	$this->mVarianta = new module_Varianta();
    }         
	
	
    
    function append($txt){
    	$this->sitemap .= iconv('utf-8', 'windows-1250', $txt) . $this->newLine;  
    }
    
	function append2($txt){
    	$this->sitemap .= $txt . $this->newLine;  
    } 
    
    function append3($txt){ 
    	$this->sitemap .= iconv('utf-8', 'iso-8859-2', $txt) . $this->newLine;  
    } 
     
    function makeIso8601TimeStamp ($dateTime = 0) {
	    if (!$dateTime) {
	        $dateTime = date('Y-m-d H:i:s');
	    }
	    if (is_numeric(substr($dateTime, 11, 1))) {
	        $isoTS = substr($dateTime, 0, 10) ."T"
	                 .substr($dateTime, 11, 8) ."+00:00";
	    }
	    else {
	        $isoTS = substr($dateTime, 0, 10);
	    }
	    return $isoTS;
	} 
	
	private function cleanText($html)
	{
		$html = trim(strip_tags($html)); 
		$html = str_replace('×', ' x' , $html);          
		//$html = str_replace('
//','',$html);    
		 return html_entity_decode(str_replace('  
 ', ' ', str_replace('
 
','',str_replace('°','',str_replace('&','',str_replace('©','',trim(strip_tags($html))))))));        
		      
	}    
	  	
    function renderZbozi($view){  
		$params['onWeb'] = 1;         
		$params['showFirstVariant'] = true; 
		$params['cms'] = false;     
		//$params['skryto'] = true;              
		$params['dateArchived'] = true;  
		$pages = 	$this->mProducts->getProducts('title', 'asc',0, 9999, $params);
    	$basePath = Utils::getWebUrl();      
		$mZbozi = new module_ZboziTree();
    	$this->append2('<?xml version="1.0" encoding="utf-8"?>');  
		$this->append2('<SHOP xmlns="http://www.zbozi.cz/ns/offer/1.0">');  
		foreach ($pages as $page){
			$n = $view->tree->getNodeById($page['id']);
			$c = $n->getTheRightContent();
			if(!$c)
			{
				continue; 
			}
			if($c->getPropertyValue('disable_product'))
			{     
				continue;    
			}
			$znacka = $this->mProducts->getZnacky($page['znacka']);
			$img = helper_FrontEnd::getPhoto('photos', $c, $c->fotoFullName);
			$html = $page['html'];
			$html = str_replace(array('<p>','</p>', '<div>', '</div>'), '', $html);
			
			list($categories,$this->listCategories) = $mZbozi->getCategories($this->tree ,$c->getPropertyValue('parent'),$this->listCategories);
			$photo = $this->mVarianta->getResizedPhotos($page['obrazky']);
			$pathPhoto = helper_FrontEnd::getFirstPhoto($photo,'pFull' , false);
			$this->append2('<SHOPITEM>');      
				$this->append2('<PRODUCTNAME><![CDATA[' . str_replace("'" , '', $page['title'])  . ']]></PRODUCTNAME>'); 
					if($page['zboziProduct']){  
					$this->append2('<PRODUCT>'.  html_entity_decode(strip_tags(str_replace('&','',str_replace('P.E.P.©','P.E.P.',$page['zboziProduct'])))) . '</PRODUCT>');               
				}      
				$this->append2('<DESCRIPTION><![CDATA[' . $this->cleanText($html) . ']]></DESCRIPTION>');      
				$this->append2('<PRICE_VAT>' . $page['price'] . '</PRICE_VAT>');     
						$this->append2('<ITEM_ID>' . $page['id'] . '</ITEM_ID>');    
			//	$this->append2('<VAT>' . $c->getPropertyValue('dphQuote') . '</VAT>'); 
				if(strlen($page['EAN'])>7 && is_numeric($page['EAN'])){   
					$this->append2('<EAN>' . $page['EAN'] . '</EAN>'); 
				}
				  
					
				$this->append2('<URL>' . $basePath . $page['path'] . '</URL>');    
				if($c->state == 'PUBLISHED' && $page['skladem'] == '1')
				{
					$this->append2('<DELIVERY_DATE>0</DELIVERY_DATE>');
				}  
				$this->append2('<DELIVERY>
							<DELIVERY_ID>TOPTRANS</DELIVERY_ID>');
				if($page['price']>999){
					$this->append2('<DELIVERY_PRICE>0</DELIVERY_PRICE>
									<DELIVERY_PRICE_COD>0</DELIVERY_PRICE_COD>');
						
				}   
				else{
					$this->append2('<DELIVERY_PRICE>49</DELIVERY_PRICE>
									<DELIVERY_PRICE_COD>0</DELIVERY_PRICE_COD>');
				}              
				$this->append2('</DELIVERY>');  
				if($categories){
					foreach($categories as $ca){
						if($ca)		
						$this->append2('<CATEGORYTEXT>'.$ca.'</CATEGORYTEXT>');
					}
				} 
				if(is_numeric(strpos($page['parent'], '7234')))
				{
					$this->append2('<PARAM>');
						$this->append2('<PARAM_NAME>Způsob přípravy kávy</PARAM_NAME>');
						$this->append2('<VAL>espresso</VAL>');
					$this->append2('</PARAM>');
				}
				if(is_numeric(strpos($page['parent'], '7234')))
				{
					$this->append2('<PARAM>');
						$this->append2('<PARAM_NAME>Typ kávovaru</PARAM_NAME>');
						$this->append2('<VAL>automatický</VAL>');
					$this->append2('</PARAM>');
				}
				if($page['tlak'] > 0){
					$this->append2('<PARAM>');
					$this->append2('<PARAM_NAME>Tlak čerpadla</PARAM_NAME>');
					$this->append2('<VAL>'.$page['tlak'].'</VAL>');
					$this->append2('<UNIT>bar</UNIT>');
					$this->append2('</PARAM>');
				}
				if($page['vlastnosti']){
					$vala = explode('|', $page['vlastnosti']);
				
					foreach ($vala as $key => $value) {
			
						foreach ($this->_vlastnostZbozi as $k => $val) {

							if($k == $value){
							$this->append2('<PARAM>');
							$this->append2('<PARAM_NAME>Vlastnosti</PARAM_NAME>');
							$this->append2('<VAL>'.$this->_vlastnostZbozi[$value].'</VAL>');
							$this->append2('</PARAM>');		
							}
						}
					}
				}
				if($page['objem'])
				{
					$this->append2('<PARAM>');
						$this->append2('<PARAM_NAME>Objem zásobníku na vodu</PARAM_NAME>');
						$this->append2('<VAL>'.$page['objem'].'</VAL>');
							$this->append2('<UNIT>l</UNIT>');
					$this->append2('</PARAM>');
				}
				if($page['prikon'])
				{
					$this->append2('<PARAM>');
						$this->append2('<PARAM_NAME>Příkon</PARAM_NAME>');
						$this->append2('<VAL>'.$page['prikon'].'</VAL>');
							$this->append2('<UNIT>W</UNIT>');
					$this->append2('</PARAM>');
				}
				$znacka = $this->mProducts->getZnacky($page['znacka']);     
				if($znacka['nazev']) 
				{
					$this->append2('<MANUFACTURER>'.$znacka['nazev'].'</MANUFACTURER>');  
				}
				if($pathPhoto['path']){
					$this->append2('<IMGURL>' . $basePath . $pathPhoto['path'] . '</IMGURL>');
				}     
				if($page['price'] > 10000){
						$this->append2('<EXTRA_MESSAGE>free_accessories</EXTRA_MESSAGE>');     
						$this->append2('<FREE_ACCESSORIES_TEXT>Smart connect</FREE_ACCESSORIES_TEXT>');
						$this->append2('<EXTRA_MESSAGE>free_gift</EXTRA_MESSAGE>');
						$this->append2('<FREE_GIFT_TEXT>Asistence při spuštění kávovaru</FREE_GIFT_TEXT>');
						
				}  
				 
			     
			$this->append2('</SHOPITEM>');
		}
		$this->append2('</SHOP>');
			ob_clean();  
		header('Content-type: application/xml; charset="utf-8"',true);  
		echo $this->sitemap; 
		die(); 
    } 


	
		
    
    function renderHeureka($view){
    	$kavacat = 'Jídlo a nápoje | Nápoje | Nealkoholické nápoje | Káva'; 
		$pristkav = 'Bílé zboží | Malé spotřebiče | Kuchyňské spotřebiče | Příslušenství ke kuchyňským spotřebičům | Příslušenství ke kávovarům';
    	$cisticat = 'Bílé zboží | Malé spotřebiče | Kuchyňské spotřebiče | Příslušenství ke kuchyňským spotřebičům | Čistící tablety do kávovarů';
		$filtcat = 'Bílé zboží | Malé spotřebiče | Kuchyňské spotřebiče | Příslušenství ke kuchyňským spotřebičům | Filtry do kávovarů';
    	$mp = new module_Products();
    	$this->mVarianta = new module_Varianta();
    	
		$params['onWeb'] = 1;   
		$params['showFirstVariant'] = true; 
		$params['cms'] = false;     
		//$params['skryto'] = true;              
		$params['dateArchived'] = true;  
		$pages = $mp->getProducts('title', 'asc',0, 9999, $params);

    	$basePath = Utils::getWebUrl();  
	
    	//$this->getCategories($this->root);
		//$this->setCategoryList($this->heurekaRoot, '', true);

		// pr($this->categoryList); die(); 
		
    	$this->append2('<?xml version="1.0" encoding="utf-8"?>');  
		$this->append2('<SHOP>');  
		
		$znacky = array();
		$z = $mp->getZnacky(false, false);
		foreach ($z as $zz){
			$znacky[$zz['id']] = $zz;
		}

		// pr($pages);  die();    
		$mHeureka = new module_HeurekaTree();
		foreach ($pages as $page){
			
			$znacka = $mp->getZnacky($page['znacka']);     
			$page['dph'] = $this->actualDph;   
			$html = $page['html'];
			$html = str_replace(array('<p>','</p>', '<div>', '</div>'), '', $html);
			$cats = $page['parent'];
	
			
			
			$znacka = $mp->getZnacky($page['znacka']);
			$n = $view->tree->getNodeById($page['id']);
			$c = $n->getTheRightContent();   
			if(!$c)
			{
				continue; 
			}
			list($categories,$this->listCategories) = $mHeureka->getCategories($this->tree ,$c->getPropertyValue('parent'),$this->listCategories);
		
			$znacka = $znacky[$page['znacka']];
			$c->getPropertyByName('photos')->value = $page['obrazky'];
			if($c->getPropertyValue('disable_product'))
			{     
				continue;  
			}
			$heurekaTitle = $c->getPropertyValue('heureka-title');   

			$photo = $this->mVarianta->getResizedPhotos($page['obrazky']);
			$pathPhoto = helper_FrontEnd::getFirstPhoto($photo,'pFull' , false);

			//$img = helper_FrontEnd::getPhoto('photos', $c, $c->fotoFullName);
			//e($page);
			$title2 = $page['title'];      
			if($page['title2'])
			{
				$title2 = $page['title2']; 
			} 
			$producName = '';
			if(is_numeric(strpos($page['title'], 'Šálek')) ||  is_numeric(strpos($page['title'], 'Sklenice')) || 
			is_numeric(strpos($page['title'], 'Chladnička')) || is_numeric(strpos($page['title'], 'Coolcontrol')))    
				{
					$producName = $znacka['nazev'].' | ';	 
						
				}
			
			$page['dphQuote'] = $page['dphQuote']? $page['dphQuote'] : 21; 
			$page['title'] = $heurekaTitle ? $heurekaTitle : $page['title'];  
			$nno = "";   
			if($page['price'] > 10000)  
			{
				//$nno = ' - odeslání DNES'; 
			}   
			$sub = '';
			if($page['SmartConnector'])
			{
				$sub = " + Smart connect, okamžitá expedice zboží"; 
			}   
			else{  
				$sub = " - Okamžitá expedice zboží";    
			}           
			$this->append2('<SHOPITEM>'); 
				$this->append2('<PRODUCTNAME><![CDATA[' .$producName . $page['title']. ']]></PRODUCTNAME>');  
				//$nno = $page['price'] > 999 ? ', DOPRAVA ZDARMA' : '';           
				if(is_numeric(strpos($page['title'], 'Káva')) || is_numeric(strpos($page['title'], 'káva')))   
				
				{ 
					$this->append2('<PRODUCT><![CDATA[' . $page['title'] . $nno.']]></PRODUCT>');     
				}
				else{   
					$this->append2('<PRODUCT><![CDATA[' . $page['title'] . $sub.']]></PRODUCT>'); 
				}
				
				$this->append2('<PRICE>' . $page['price'] . '</PRICE>');   
				$this->append2('<PRICE_VAT>' . $page['price'] . '</PRICE_VAT>'); 
			   
				
				$this->append2('<VAT>' . $page['dphQuote'] . '</VAT>');   
				$this->append2('<ITEM_ID>' . $page['id'] . '</ITEM_ID>');
				if($page['EAN']){
					$this->append2('<EAN>' . $page['EAN'] . '</EAN>'); 
				}
				$this->append2('<DESCRIPTION><![CDATA[' . ($this->cleanText($html)) . ']]></DESCRIPTION>');      
				$workAround="";       
				$max=0;  
				if($page['id'] == '74721'){  
					$this->append2('<CATEGORYTEXT>Bílé zboží | Malé spotřebiče | Kuchyňské spotřebiče | Příslušenství ke kuchyňským spotřebičům | Čistící tablety do kávovarů</CATEGORYTEXT>');
				//	$this->append2('<CATEGORYTEXT>Bílé zboží | Malé spotřebiče | Kuchyňské spotřebiče | Příslušenství ke kuchyňským spotřebičům | Filtry do kávovarů</CATEGORYTEXT>');
				} 
				elseif(is_numeric(strpos($page['title'], 'káva')) || is_numeric(strpos($page['title'], 'Káva')))
						{
							$this->append2('<CATEGORYTEXT>'.$kavacat.'</CATEGORYTEXT>');	
						}
				elseif(is_numeric(strpos($page['title'], 'tablety'))) 
					{
						$this->append2('<CATEGORYTEXT>'.$cisticat.'</CATEGORYTEXT>');	
					} 
				elseif(is_numeric(strpos($page['title'], 'Chladnička')) || is_numeric(strpos($page['title'], 'Coolcontrol')))   
					{
						$this->append2('<CATEGORYTEXT>'.$pristkav.'</CATEGORYTEXT>');     	
					}
				elseif(is_numeric(strpos($page['title'], 'šlehač')))
					{
						$this->append2('<CATEGORYTEXT>Bílé zboží | Malé spotřebiče | Kuchyňské spotřebiče | Pěniče mléka</CATEGORYTEXT>');   	
					} 
				elseif(is_numeric(strpos($page['title'], 'Termo nádoba')))
					{
						$this->append2('<CATEGORYTEXT>'.$pristkav.'</CATEGORYTEXT>');   	  
					} 
				elseif(is_numeric(strpos($page['title'], 'Smart Connector')) || is_numeric(strpos($page['title'], 'ohřívač')) || is_numeric(strpos($page['title'], 'Coolcontrol ')))
					{
						$this->append2('<CATEGORYTEXT>'.$pristkav.'</CATEGORYTEXT>');   	
					}  
				elseif(is_numeric(strpos($page['title'], 'filtr')))  
					{
						$this->append2('<CATEGORYTEXT>'.$filtcat.'</CATEGORYTEXT>');    	
					}
				elseif(is_numeric(strpos($page['title'], 'Šálek')) ||  is_numeric(strpos($page['title'], 'Sklenice')))
					{
						$this->append2('<CATEGORYTEXT>Dům a zahrada | Bydlení a doplňky | Kuchyně | Stolování | Hrnky a šálky</CATEGORYTEXT>');    	
					} 
				elseif($categories){   
					foreach ($categories as $va) {
							$this->append2('<CATEGORYTEXT>'.$va.'</CATEGORYTEXT>');	
						continue;
					}
				}  
				else{
						$this->append2('<CATEGORYTEXT>'.$pristkav.'</CATEGORYTEXT>');
						}	
				 
				$this->append2('<MANUFACTURER>'.$znacka['nazev'].'</MANUFACTURER>');   
				if($page['prikon'])
				{
					$this->append2('<PARAM>');
						$this->append2('<PARAM_NAME>Příkon</PARAM_NAME>');
						$this->append2('<VAL>'.$page['prikon'].'</VAL>');
					$this->append2('</PARAM>');
				}
				if($page['objem']) 
				{
					$this->append2('<PARAM>');
						$this->append2('<PARAM_NAME>Objem vody</PARAM_NAME>');
						$this->append2('<VAL>'.$page['objem'].'</VAL>');
					$this->append2('</PARAM>');
				}
				if($page['tlak'])
				{
					$this->append2('<PARAM>');
						$this->append2('<PARAM_NAME>Parní tlak</PARAM_NAME>');
						$this->append2('<VAL>'.$page['tlak'].'</VAL>');
					$this->append2('</PARAM>');
				}
				if($page['hmotnost'])
				{
					$this->append2('<PARAM>');
						$this->append2('<PARAM_NAME>Hmotnost</PARAM_NAME>');
						$this->append2('<VAL>'.$page['hmotnost'].'</VAL>');
					$this->append2('</PARAM>');
				}
				if($page['ovladani'] > 0){
					$this->append2('<PARAM>');
					$this->append2('<PARAM_NAME>Typ ovládání</PARAM_NAME>');
					$this->append2('<VAL>'.$this->mVarianta ->variantProperty['ovladani']['selection'][$page['ovladani']].'</VAL>');
					$this->append2('</PARAM>');
				}
		
				if($page['typ']){
					$val = explode('|', $page['typ']);
					foreach ($val as $key => $value) {
					$this->append2('<PARAM>');
					$this->append2('<PARAM_NAME>Typ</PARAM_NAME>');
					$this->append2('<VAL>'.$this->mVarianta->variantProperty['typ']['selection'][$value].'</VAL>');
					$this->append2('</PARAM>');		
					}
				}
				if($page['vlastnosti']){
					$val = explode('|', $page['vlastnosti']);
					foreach ($val as $key => $value) {
						if(in_array( $value, $this->_vlastnostiHeureka)){
							$this->append2('<PARAM>');
							$this->append2('<PARAM_NAME>Požadované vlastnosti</PARAM_NAME>');
							$this->append2('<VAL>'.$this->mVarianta->variantProperty['vlastnosti']['selection'][$value].'</VAL>');
							$this->append2('</PARAM>');		
						}
					}
				}
				
				$this->append2('<PARAM>');
				$this->append2('<PARAM_NAME>Distribuce</PARAM_NAME>');
				$this->append2('<VAL>CZ</VAL>');
				$this->append2('</PARAM>');
				$this->append2('<URL>' . $basePath . $page['path'] . '</URL>');
				if($page['price'] > 10000){
						$this->append2('<GIFT>Asistence při spuštění kávovaru</GIFT>');
				}  
				if($c->state == 'PUBLISHED' && $page['skladem'] == '1')
				{
					$this->append2('<DELIVERY_DATE>0</DELIVERY_DATE>');
				} 
  
				$this->append2('<DELIVERY>');
				$this->append2('<DELIVERY_ID>TOPTRANS</DELIVERY_ID>');   
				$delPrice = $page['price']>999? '0' : '49';
				$this->append2('<DELIVERY_PRICE>'.$delPrice.'</DELIVERY_PRICE>');
				$this->append2('<DELIVERY_PRICE_COD>'.$delPrice.'</DELIVERY_PRICE_COD>');
				$this->append2('</DELIVERY>');
				if($pathPhoto['path']){  
					$this->append2('<IMGURL>' . $basePath . $pathPhoto['path'] . '</IMGURL>');
				}  
			$this->append2('</SHOPITEM>');
			 
		} 
		$this->append2('</SHOP>');
		 
		ob_clean();  
		header('Content-type: text/xml; charset="utf-8"',true);      
		echo $this->sitemap; 
		die(); 
    } 

	
function renderBig($view){
    	$kavacat = 'Jídlo a nápoje | Nápoje | Nealkoholické nápoje | Káva';
		$pristkav = 'Bílé zboží | Malé spotřebiče | Kuchyňské spotřebiče | Příslušenství ke kuchyňským spotřebičům';
    	$cisticat = 'Bílé zboží | Malé spotřebiče | Kuchyňské spotřebiče | Příslušenství ke kuchyňským spotřebičům | Čistící tablety do kávovarů';
		
		
    	$mp = new module_Products();
    	$this->mVarianta = new module_Varianta();
		$mBasket = new module_Eshop_Basket();
    	$doprava = $mBasket->getDeliveries();
		
		$params['onWeb'] = 1;   
		$params['showFirstVariant'] = true; 
		$pages = $mp->getProducts('title', 'asc',0, 9999, $params);
		 
    	$basePath = Utils::getWebUrl();  
	
    	//$this->getCategories($this->root);
		//$this->setCategoryList($this->heurekaRoot, '', true);

		// pr($this->categoryList); die(); 
		
    	$this->append2('<?xml version="1.0" encoding="utf-8"?>');  
		$this->append2('<shop>');  
		
		$znacky = array();
		$z = $mp->getZnacky(false, false);
		foreach ($z as $zz){
			$znacky[$zz['id']] = $zz;
		}

		// pr($znacky); pr($pages);  die();  
		$mHeureka = new module_HeurekaTree();

		foreach ($pages as $page){
			
			
			$page['dph'] = $this->actualDph;   
			$html = $page['html'];
			$html = str_replace(array('<p>','</p>', '<div>', '</div>'), '', $html);
			$cats = $page['parent'];
	
			
			
			$znacka = $mp->getZnacky($page['znacka']);
			$n = $view->tree->getNodeById($page['id']);
			$c = $n->getPublishedContent();   
			list($categories,$this->listCategories) = $mHeureka->getCategories($this->tree ,$c->getPropertyValue('parent'),$this->listCategories);
		
			$znacka = $znacky[$page['znacka']];
			$c->getPropertyByName('photos')->value = $page['obrazky'];
			  
			
			$photo = $this->mVarianta->getResizedPhotos($page['obrazky']);
			$pathPhoto = helper_FrontEnd::getFirstPhoto($photo,'pFull' , false);

			//$img = helper_FrontEnd::getPhoto('photos', $c, $c->fotoFullName);
			//e($page);
			$title2 = $page['title'];      
			if($page['title2'])
			{
				$title2 = $page['title2'];
			}
			$page['dphQuote'] = $page['dphQuote']? $page['dphQuote'] : 21; 
			$this->append2('<SHOPITEM>'); 
				$this->append2('<PRODUCT><![CDATA[' . $page['title'].' ]]></PRODUCT>'); 
				$this->append2('<PRICE>' . $page['price'] . '</PRICE>');   
				$this->append2('<PRICE_VAT>' . $page['price'] . '</PRICE_VAT>'); 
				$this->append2('<VAT>' . $page['dphQuote'] . '</VAT>');   
				$this->append2('<ITEM_ID>' . $page['id'] . '</ITEM_ID>');
				if($page['EAN']){
					$this->append2('<EAN>' . $page['EAN'] . '</EAN>'); 
				}
				$html = str_replace('href="', 'target="_blank" href="http://www.nivona-eshop.cz', $html);
				$this->append2('<DESCRIPTION><![CDATA[' . htmlspecialchars(str_replace('/data/sharedfiles/', 'http://www.nivona-eshop.cz/data/sharedfiles/', $html)) . ']]></DESCRIPTION>'); 
				$workAround="";
				$max=0;

				if($categories){
					foreach ($categories as $va) {
							$this->append2('<CATEGORYTEXT>'.$va.'</CATEGORYTEXT>');	
					}
				}
				else{
					if(is_numeric(strpos($page['title'], 'káva')))
						{
							$this->append2('<CATEGORYTEXT>'.$kavacat.'</CATEGORYTEXT>');	
						}
					elseif(is_numeric(strpos($page['title'], 'tablety')))
					{
						$this->append2('<CATEGORYTEXT>'.$cisticat.'</CATEGORYTEXT>');	
					}
					else{
						$this->append2('<CATEGORYTEXT>'.$pristkav.'</CATEGORYTEXT>');
						}	
				}
				if($znacka['nazev']){
					$this->append2('<MANUFACTURER>'.$znacka['nazev'].'</MANUFACTURER>');
				}
				$this->append2('<PARAM>');
				$this->append2('<PARAM_NAME>Distribuce</PARAM_NAME>');
				$this->append2('<VAL>CZ</VAL>');
				$this->append2('</PARAM>');
				foreach ($doprava as $key => $value) {
						
					if($page['price'] >= $value['cena_od'] && $value['do'] > $page['price']){
					$this->append2('<DELIVERY>');
						$this->append2('<DELIVERY_ID>'.htmlspecialchars($value['nazev']).'</DELIVERY_ID>');
						$this->append2('<DELIVERY_PRICE>'.$value['price'].'</DELIVERY_PRICE>');
						$this->append2('<DELIVERY_ZDARMA_OD>'.$value['zdarma_nad'].'</DELIVERY_ZDARMA_OD>');
					$this->append2('</DELIVERY>');
					}
				}
				$this->append2('<URL>' . $basePath . $page['path'] . '</URL>');  
				$this->append2('<DELIVERY_DATE>0</DELIVERY_DATE>');   
				if($pathPhoto['path']){  
					$this->append2('<IMGURL>' . $basePath . $pathPhoto['path'] . '</IMGURL>');
				}  
			$this->append2('</SHOPITEM>');
			 
		}
		$this->append2('</shop>');
		 
		ob_clean();  
		header('Content-type: application/xml; charset="utf-8"',true);  
		echo $this->sitemap; 
		die(); 
    } 



  function renderSrovna($view){
  		$mD = new module_Delivery();
		$deliveries = $mD->getValidDeliveries();
		foreach ($deliveries as  $value) {
			$delivery[$value['od']] = array('title'=>$value['nazev'], 'price' => $value['price']);
		}
    	$mp = new module_Products();
    	$this->mVarianta = new module_Varianta();
    	
		$params['onWeb'] = 1;   
		$params['showFirstVariant'] = true; 
		$pages = $mp->getProducts('title', 'asc',0, 9999, $params);
		 
    	$basePath = Utils::getWebUrl();  
	
    	//$this->getCategories($this->root);
		//$this->setCategoryList($this->heurekaRoot, '', true);

		// pr($this->categoryList); die(); 
		
    	$this->append2('<?xml version="1.0" encoding="utf-8"?>');  
		$this->append2('<shop>');  
		
		$znacky = array();
		$z = $mp->getZnacky(false, false);
		foreach ($z as $zz){
			$znacky[$zz['id']] = $zz;
		}

		// pr($znacky); pr($pages);  die();  
		$mHeureka = new module_HeurekaTree();

		foreach ($pages as $page){
			
			
			$page['dph'] = $this->actualDph;   
			$html = $page['html'];
			$html = str_replace(array('<p>','</p>', '<div>', '</div>'), '', $html);
			$cats = $page['parent'];
	
			
			
			$znacka = $mp->getZnacky($page['znacka']);
			$n = $view->tree->getNodeById($page['id']);
			$c = $n->getPublishedContent();   
			list($categories,$this->listCategories) = $mHeureka->getCategories($this->tree ,$c->getPropertyValue('parent'),$this->listCategories);
		
			$znacka = $znacky[$page['znacka']];
			$c->getPropertyByName('photos')->value = $page['obrazky'];
			  
			
			$photo = $this->mVarianta->getResizedPhotos($page['obrazky']);
			$pathPhoto = helper_FrontEnd::getFirstPhoto($photo,'pFull' , false);

			//$img = helper_FrontEnd::getPhoto('photos', $c, $c->fotoFullName);
			//e($page);
			$title2 = $page['title'];      
			if($page['title2'])
			{
				$title2 = $page['title2'];
			}
			$page['dphQuote'] = $page['dphQuote']? $page['dphQuote'] : 21; 
			$this->append2('<SHOPITEM>'); 
				$this->append2('<PRODUCT><![CDATA[' . $page['title'] . ']]></PRODUCT>'); 
				$this->append2('<PRODUCTNAME><![CDATA[' . $title2 . ']]></PRODUCTNAME>'); 
				$this->append2('<PRICE>' . $page['price'] . '</PRICE>');   
				$this->append2('<PRICE_VAT>' . $page['price'] . '</PRICE_VAT>'); 
				$this->append2('<VAT>' . $page['dphQuote'] . '</VAT>');   
				$this->append2('<ITEM_ID>' . $page['id'] . '</ITEM_ID>');
				if($page['EAN']){
					$this->append2('<EAN>' . $page['EAN'] . '</EAN>'); 
				}
				$this->append2('<DESCRIPTION><![CDATA[' . strip_tags($html) . ']]></DESCRIPTION>'); 
				$workAround="";
				$max=0;

				if($categories){
					foreach ($categories as $va) {
							$this->append2('<CATEGORYTEXT>'.$va.'</CATEGORYTEXT>');	
					}
				}
				if($znacka['nazev']){
					$this->append2('<MANUFACTURER>'.$znacka['nazev'].'</MANUFACTURER>');
				}
				foreach ($delivery as $key => $value) {
					if($key <= $page['price'])
					{
						$showDel = $value;
						break;
					}
				}
			$this->append2('<DELIVERY>');
            $this->append2('<DELIVERY_ID>VLASTNI_PREPRAVA</DELIVERY_ID>');
            $this->append2('<delivery_cost>'.$showDel['price'].'</delivery_cost>');
            $this->append2('</DELIVERY>');
				$this->append2('<PARAM>');
				$this->append2('<PARAM_NAME>Distribuce</PARAM_NAME>');
				$this->append2('<VAL>CZ</VAL>');
				$this->append2('</PARAM>');
				$this->append2('<URL>' . $basePath . $page['path'] . '</URL>');  
				$this->append2('<DELIVERY_DATE>0</DELIVERY_DATE>');   
				if($pathPhoto['path']){  
					$this->append2('<IMGURL>' . $basePath . $pathPhoto['path'] . '</IMGURL>');
				}  
			$this->append2('</SHOPITEM>');
			 
		}
		$this->append2('</shop>');
		 
		ob_clean();  
		header('Content-type: application/xml; charset="utf-8"',true);  
		echo $this->sitemap; 
		die(); 
    } 
    
	
	
    function renderHeldejceny($view){
    	$pages = $this->getPages();
    	$basePath = Utils::getWebUrl();  	
    	
    
    	$this->append3('<?xml version="1.0" encoding="iso-8859-2"?>');  
		$this->append3('<shop>');  
		foreach ($pages as $page){
			$photos = $page['photos'];
			if($photos{0} == ';'){
				$photos = substr($photos, 1);
			}
			$part = explode(';', $photos); 
			$photo = $part[0] . ';' . $part[1];
			$img = content_SFSFile::getFileFromProperty($photo);
			
			 
			$html = $page['html'];
			$html = str_replace('</li>', 'xxxx', $html);
			$html = strip_tags($html);
			$html = str_replace('xxxx', '<br/>', $html);  
			 			  
			$this->append3('<SHOPITEM>'); 
				$this->append3('<PRODUCT>' . $page['title'] . '</PRODUCT>'); 
				$this->append3('<PRICE>' . $page['price'] . '</PRICE>');
				$this->append3('<PRICE_VAT>' . $page['price2'] . '</PRICE_VAT>');
				$this->append3('<VAT>' . $page['dph'] . '</VAT>'); 
				$this->append3('<DESCRIPTION><![CDATA[' . $html . ']]></DESCRIPTION>');   
				$this->append3('<URL>' . $basePath . $page['path'] . '</URL>');
				$this->append3('<DELIVERY_DATE>0</DELIVERY_DATE>');   
				if($img['path']){
					$this->append3('<IMGURL>' . $basePath . $img['path'] . '</IMGURL>');
				}   
			$this->append3('</SHOPITEM>');
			 
		}
		$this->append3('</shop>');
		 
		ob_clean();  
		header('Content-type: application/xml; charset="iso-8859-2"',true);  
		echo $this->sitemap; 
		die(); 
    } 

	public function renderDostupnost($view)
	{
		$dtz = new DateTimeZone("Europe/Prague"); //your timezone
		$now = new DateTime(date("Y-m-d H:i:s"), $dtz);
		$modify = new DateTime(date("Y-m-d H:i:s"), $dtz);
		if(date('w')==6)
		{
			$modify->modify('+2 day');
		} 
		elseif(date('w')==0)
		{
			$modify->modify('+3 day');
		}
		if(date('H')>9)
		{
			$modify->modify('+2 day');
		}
		else{
			$modify->modify('+1 day');
		}
		$set = $now->format("Y-m-d 09:00");
		if(date('H')>9)
		{
			$now->modify('+1 day');
			$set = $now->format("Y-m-d 09:00");
		}
		$pages = $this->getPages();
    	$basePath = Utils::getWebUrl();  
		$this->append2('<?xml version="1.0" encoding="utf-8"?>');  
		$this->append('<item_list>');
		foreach ($pages as $page)
		{	 
			$this->append('<item id="'.$page['id'].'">');
			$this->append('<stock_quantity>10</stock_quantity>');
			$this->append('<delivery_time orderDeadline="'.$set.'">'.$modify->format("Y-m-d 17:00").'</delivery_time>');
			$this->append('</item>');
		} 
		$this->append('</item_list>');
		ob_clean();  
		header('Content-type: application/xml; charset="utf-8"',true);  
		echo $this->sitemap; 
		die(); 
	}
    
    function renderMerchant($view){
    		$mp = new module_Products();
    	$this->mVarianta = new module_Varianta();
    	
		$params['onWeb'] = 1;   
		$params['showFirstVariant'] = true; 
		$params['skryto'] = true;            
		$pages = $mp->getProducts('title', 'asc',0, 9999, $params);
    	$basePath = Utils::getWebUrl(); 
    	$mHeureka = new module_Merchant();
    	$this->append2('<?xml version="1.0" encoding="utf-8"?>');
    	$this->append2('<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">');
    	$this->append2('<channel>');
		     
    	foreach ($pages as $page){
   			if($page['id'] == 74717 || $page['id'] == 74973 ){   
   				continue;    
   			}
    		$photos = $page['photos'];
    		  
    		 
    		$page['dph'] = $this->actualDph;
    		$html = $page['html'];
    		$html = str_replace(array('<p>','</p>', '<div>', '</div>'), '', $html);
    		$cats = $page['parent'];
    		$znacka = $mp->getZnacky($page['znacka']);  
    		 
    		$n = $view->tree->getNodeById($page['id']);
			$c = $n->getTheRightContent();  
			if($c->state != 'PUBLISHED')
				{ 
					continue;    
				}  
			if(!$c)
			{
				continue;  
			} 
				list($categories,$this->listCategories) = $mHeureka->getCategories($this->tree ,$c->getPropertyValue('parent'),$this->listCategories);
    			$photo = $this->mVarianta->getResizedPhotos($page['obrazky']);
			$pathPhoto = helper_FrontEnd::getFirstPhoto($photo,'pShow' , false);
    		//7234 
			if($c->getPropertyValue('disable_product'))
			{     
				continue;  
			}
    		$price = $page['price'];
    		$price2  = $page['price2'];
    		$dif = $price2 - $price;
    		$p = $price2/100;
    		$dif= round($dif/$p, 1);
    		$dif = str_replace('.', ',', $dif);
    		$difp = $dif ? ''.round($dif).'% ':'';
			$t = $page['title'];
    		if($page['price'] > 999)
    		{
    			$t = $t.' + DOPRAVA ZDARMA';
    		}    
     
    		$this->append2('<item>');
    		$this->append2('<title><![CDATA[' . $t . ']]></title>');
    		if($page['price'] < $page['price2'])
    		{
    			$this->append2('<g:price>' . $page['price2'] . '  Kč</g:price>');
    			$this->append2('<g:sale_price>' . $page['price'] . '  Kč</g:sale_price>');
    		}
    		else{
    			$this->append2('<g:price>' . $page['price'] . '  Kč</g:price>');
    		}
    		$this->append2('<g:id>'.$page['id'].'</g:id>');
    		$this->append2('<g:condition>new</g:condition>');
    		$this->append2('<g:availability>in stock</g:availability>');
			$znacka = $mp->getZnacky($page['znacka']);    
			if($znacka['nazev']){
    			$this->append2('<g:brand>'.$znacka['nazev'].'</g:brand>');
			}
				if($categories)   
			{  
			   
					$this->append2('<g:google_product_category>'.end($categories).'</g:google_product_category>');
    			$this->append2('<g:product_type>'.($categories).'</g:product_type>');
    				//$this->append2('<g:product_type>Dům a zahrada &gt; Prádlo &gt; Kávovary a automaty na espreso &gt; Kávovary</g:product_type>');		
			
			}  
    		if($img['path']){
    			$this->append2('<g:image_link>' . $basePath . $img['path'] . '</g:image_link>');
    		}
			if(strlen($page['EAN'])>12){ 
    			$this->append2('<g:gtin>' . $page['EAN'] . '</g:gtin>');
    		}     
			else{
				$this->append2('<g:identifier_exists>FALSE</g:identifier_exists>');
			}   
    		$this->append2('<description><![CDATA[' .strip_tags(helper_FrontEnd::reduceText($this->cleanText($html),5000,true)) . ']]></description>');                
    		$workAround="";    
    		$max=0; 
    		 
			if($page['price'] > 999){
				$this->append2('<g:shipping>');
				$this->append2('<g:country>CZ</g:country>');
   				$this->append2('<g:service>Doprava Jura</g:service>');
   				$this->append2('<g:price>0 Kč</g:price>');
				$this->append2('</g:shipping>');
			}
			else{
				$this->append2('<g:shipping>');
				$this->append2('<g:country>CZ</g:country>');
   				$this->append2('<g:service>Doprava Jura</g:service>');
   				$this->append2('<g:price>49 Kč</g:price>');
				$this->append2('</g:shipping>');		
			}
			if($page['price']> 9999)   
			{ 
				$this->append2('<g:custom_label_0>od_10000Kc</g:custom_label_0>');
			}
			elseif($page['price'] < 10000 && $page['price'] > 999) 
			{ 
				$this->append2('<g:custom_label_0>od_1000Kc_az_9999Kc</g:custom_label_0>');
			}   
			else{  
				$this->append2('<g:custom_label_0>zbytek</g:custom_label_0>');
			}
    		if($workaround){ 
    			//	$this->append2('<CATEGORYTEXT>'.$workaround.'</CATEGORYTEXT>');
    		}
    		$this->append2('<link>' . $basePath . $page['path'] . '</link>');
    		//$this->append2('<DELIVERY_DATE>0</DELIVERY_DATE>');
    		if($pathPhoto['path']){
    			$this->append2('<g:image_link>' . $basePath . $pathPhoto['path'] . '</g:image_link>');
    		}
    		$this->append2('</item>');
    
    	}
    	$this->append2('</channel>');
    	$this->append2('</rss>');
    	  
    	ob_clean();
    	header('Content-type: application/xml; charset="utf-8"',true);
    	echo $this->sitemap;
    	die();
    } 
 

 

function renderFacebook($view){
    		$mp = new module_Products();
    	$this->mVarianta = new module_Varianta();
    	 
		$params['onWeb'] = 1;   
		$params['showFirstVariant'] = true; 
		$params['skryto'] = true;            
		$pages = $mp->getProducts('title', 'asc',0, 9999, $params);
    	$basePath = Utils::getWebUrl();
    	$mHeureka = new module_Merchant();
    	$this->append2('<?xml version="1.0" encoding="utf-8"?>');
    	$this->append2('<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">');
    	$this->append2('<channel>');
    	foreach ($pages as $page){
   
    		$photos = $page['photos'];
    		
    		 
    		$page['dph'] = $this->actualDph;
    		$html = $page['html'];
    		$html = str_replace(array('<p>','</p>', '<div>', '</div>'), '', $html);
    		$cats = $page['parent'];
    		$znacka = $mp->getZnacky($page['znacka']);  
    		 
    		$n = $view->tree->getNodeById($page['id']);
			$c = $n->getTheRightContent();   
			if(!$c)
			{
				continue; 
			}    
			$categories = array();   
		   
				list($categories,$this->listCategories) = $mHeureka->getCategories($this->tree ,$c->getPropertyValue('parent'),$this->listCategories);
    			$photo = $this->mVarianta->getResizedPhotos($page['obrazky']);  
				
			$pathPhoto = helper_FrontEnd::getFirstPhoto($photo,'pFull' , false);
    		//7234

    		$price = $page['price'];
    		$price2  = $page['price2'];
    		$dif = $price2 - $price; 
    		$p = $price2/100;
    		$dif= round($dif/$p, 1);
    		$dif = str_replace('.', ',', $dif);
    		$difp = $dif ? ''.round($dif).'% ':'';
    		 
    		$t = $page['title'];
    		if($price2>0){
    			$t = $difp?$t .= ', SLEVA '.$difp:$t;
			} 
    		 
     
    		$this->append2('<item>');       
    		$this->append2('<title><![CDATA[' . strtolower($t) . ']]></title>');
    		// if($page['price'] < $page['price2'])
    		// {
    			// $this->append2('<g:price>' . $page['price'] . '  CZK</g:price>');
    			// $this->append2('<g:sale_price>' . $page['price'] . '  CZK</g:sale_price>');
    		// }
    		// else{
    			$this->append2('<g:price>' . $page['price'] . '  CZK</g:price>');
    		//}    
    		$this->append2('<g:id>jura_cz_'.$page['id'].'</g:id>');
    		$this->append2('<g:condition>new</g:condition>');
    		$this->append2('<g:availability>in stock</g:availability>');
			if($znacka['nazev']){
    			$this->append2('<g:brand>'.$znacka['nazev'].'</g:brand>');
			}
			if($categories)   
			{  
			   
					$this->append2('<g:google_product_category>'.end($categories).'</g:google_product_category>');
    			$this->append2('<g:product_type>'.($categories).'</g:product_type>');
    				//$this->append2('<g:product_type>Dům a zahrada &gt; Prádlo &gt; Kávovary a automaty na espreso &gt; Kávovary</g:product_type>');		
			
			}
    		if($img['path']){
    			$this->append2('<g:image_link>' . $basePath . $img['path'] . '</g:image_link>');
    		}
    		if($page['EAN']){
    			$this->append2('<g:gtin>' . $page['EAN'] . '</g:gtin>');
    		}
    		$this->append2('<description><![CDATA[' . strip_tags($html) . ']]></description>');
			if($page['price'] > 999){
				$this->append2('<g:shipping>');
				$this->append2('<g:country>CZ</g:country>');
   				$this->append2('<g:service>Doprava Jura</g:service>');
   				$this->append2('<g:price>0 CZK</g:price>'); 
				$this->append2('</g:shipping>'); 
			}
			else{
				$this->append2('<g:shipping>'); 
				$this->append2('<g:country>CZ</g:country>');
   				$this->append2('<g:service>Doprava Jura</g:service>');
   				$this->append2('<g:price>49 CZK</g:price>'); 
				$this->append2('</g:shipping>');		
			} 
    		if($workaround){
    			//	$this->append2('<CATEGORYTEXT>'.$workaround.'</CATEGORYTEXT>');
    		}
    		$this->append2('<link>' . $basePath . $page['path'] . '</link>');
    		//$this->append2('<DELIVERY_DATE>0</DELIVERY_DATE>');
    		if($pathPhoto['path']){
    			$this->append2('<g:image_link>' . $basePath . $pathPhoto['path'] . '</g:image_link>');
    		}
    		$this->append2('</item>');
    
    	}
    	$this->append2('</channel>');
    	$this->append2('</rss>');
    	 
    	ob_clean();
    	header('Content-type: application/xml; charset="utf-8"',true);
    	echo $this->sitemap;
    	die();
    }
    
	private function replaceHTML($text)
	{
		$text = str_replace('</', '<|||', $text);
		$text = str_replace('<', '||||', $text);
		$text = str_replace('>', '|||||>', $text);
		return $text; 
	}
		 
	
	   function renderBigEshop($view){		
    	$mp = new module_Products();
    	$this->mVarianta = new module_Varianta();
    	$params['dateArchived'] = true; 
		$params['onWeb'] = 1;   
		$params['showFirstVariant'] = true; 
		$params['cms'] = true; 
		$pages = $mp->getProducts('title', 'asc',0, 9999, $params);

    	$basePath = Utils::getWebUrl();  
	
    	//$this->getCategories($this->root);
		//$this->setCategoryList($this->heurekaRoot, '', true);

		// pr($this->categoryList); die(); 
		
    	$this->append2('<?xml version="1.0" encoding="utf-8"?>');  
		$this->append2('<shop>');  
		
	
		$znacky = array();
		$z = $mp->getZnacky(false, false);
		foreach ($z as $zz){
			$znacky[$zz['id']] = $zz;
		}

		// pr($znacky); pr($pages);  die();  
		$mHeureka = new module_HeurekaTree();

		foreach ($pages as $page){
			$page['dph'] = $this->actualDph;   
			$html = $page['html'];
			$html = str_replace(array('<p>','</p>', '<div>', '</div>'), '', $html);
			$cats = $page['parent'];
	
			 
			
			$znacka = $mp->getZnacky($page['znacka']);
			$n = $view->tree->getNodeById($page['id']);
			$c = $n->getTheRightContent();
			if(!$c)
			{
				continue;
			}   
			//související
					$souvisejiciTitle = '';  
				$souvisejici = $c->getPropertyValue('souvisejici');
			if($souvisejici){
            			$tsouvisejici = helper_MultiSelect::getMultiSelectValues($souvisejici);
            			foreach ($tsouvisejici as $value) {
            				 $nodesAlt = $this->tree->getNodeById($value);
							///pr($nodesAlt);  
							if(!is_object($nodesAlt)) 
							{
								continue;    
							}
							$contAlt = $nodesAlt->getTheRightContent();     
		 					if(!$contAlt){ continue; }    
            				$art[] = $nodesAlt->title;  
            			}
            			$souvisejiciTitle = implode('|', $art);  
            		}   
			 
			
	 
			
			$znacka = $znacky[$page['znacka']];
			$c->getPropertyByName('photos')->value = $page['obrazky'];
			  
			
			$photos = $this->mVarianta->getResizedPhotos($page['obrazky']);
			$images = helper_FrontEnd::getResizedPhotos($photos, 'pShow', 'pFull');
			$im = array();
			foreach ($images as $value) {
				$im[] = $basePath.$value['fullPath'];
			}
			
			$photo = implode(';', $im); 
			//$img = helper_FrontEnd::getPhoto('photos', $c, $c->fotoFullName);
			//e($page);
			$title2 = $page['title'];      
			if($page['title2']) 
			{
				$title2 = $page['title2'];
			}
			$page['dphQuote'] = $page['dphQuote']? $page['dphQuote'] : 21; 
			$this->append2('<SHOPITEM>'); 
				$this->append2('<PRODUCT><![CDATA[' . $page['title'] .']]></PRODUCT>');  
				$this->append2('<PRICE>' . $page['price'] . '</PRICE>');   
				$this->append2('<PRICE2>' . $page['price2'] . '</PRICE2>');   
				$this->append2('<DISCOUNT>' . $page['discount'] . '</DISCOUNT>');    
				$this->append2('<PRICE_VAT>' . $page['price'] . '</PRICE_VAT>');   
				$this->append2('<VAT>' . $page['dphQuote'] . '</VAT>');  
				if($souvisejiciTitle){
					$this->append2('<SOUVISEJICI>' . $souvisejiciTitle. '</SOUVISEJICI>');
				}    
				$this->append2('<TITLERAW>' . $n->getPropertyValue('pageTitle'). '</TITLERAW>');     
				$this->append2('<PRETEXT><![CDATA[' . serialize($c->getPropertyValue('preText')). ']]></PRETEXT>');    
				$this->append2('<PARAMETRY>' . $this->replaceHTML($c->getPropertyValue('parametry')). '</PARAMETRY>');    
					$this->append2('<IMGURL>' . serialize($photo). '</IMGURL>');
					$this->append2('<SKLADEM>' . $page['skladem']. '</SKLADEM>');  
					$this->append2('<STATUS>' . $page['state']. '</STATUS>'); 
				$this->append2('<ITEM_ID>' . $page['id'] . '</ITEM_ID>');
				if($page['EAN']){
					$this->append2('<EAN>' . $page['EAN'] . '</EAN>'); 
				} 
				$this->append2('<DESCRIPTION><![CDATA[' .  serialize($html) . ']]></DESCRIPTION>'); 
				$workAround="";
				$max=0;

				if($categories){
					foreach ($categories as $va) {
							$this->append2('<CATEGORYTEXT>'.$va.'</CATEGORYTEXT>');	
					}
				}
				else{
					if(is_numeric(strpos($page['title'], 'káva')))
						{
							$this->append2('<CATEGORYTEXT>'.$kavacat.'</CATEGORYTEXT>');	
						}
					elseif(is_numeric(strpos($page['title'], 'tablety')))
					{
						$this->append2('<CATEGORYTEXT>'.$cisticat.'</CATEGORYTEXT>');	
					}
					else{
						$this->append2('<CATEGORYTEXT>'.$pristkav.'</CATEGORYTEXT>');
						}	
				}
				if($znacka['nazev']){
					$this->append2('<MANUFACTURER>'.$znacka['nazev'].'</MANUFACTURER>');
				}
				if($page['prikon'])
				{
					$this->append2('<PRIKON>'.$page['prikon'].'</PRIKON>');
				}
				if($page['objem'])
				{
					$this->append2('<OBJEM>'.$page['objem'].'</OBJEM>');
				}
				if($page['tlak'])
				{
						$this->append2('<TLAK>'.$page['tlak'].'</TLAK>');

				}
				if($page['hmotnost'])
				{
					$this->append2('<HMOTNOST>'.$page['hmotnost'].'</HMOTNOST>');
				}
				if($page['ovladani'] > 0){

					$this->append2('<OVLADANI>'.$page['ovladani'].'</OVLADANI>');
				} 
		
				if($page['typ']){ 
					$this->append2('<TYP>'.$page['typ'].'</TYP>'); 
				}
				if($page['vlastnosti']){

					$this->append2('<VLASTNOSTI>'.$page['vlastnosti'].'</VLASTNOSTI>');		
				}
				$this->append2('<PARAM>');
				$this->append2('<PARAM_NAME>Distribuce</PARAM_NAME>');
				$this->append2('<VAL>CZ</VAL>');
				$this->append2('</PARAM>');
				$this->append2('<URL>' . $basePath . $page['path'] . '</URL>');
				if($c->state == 'PUBLISHED')
				{
					$this->append2('<DELIVERY_DATE>0</DELIVERY_DATE>');
				}
			
			$this->append2('</SHOPITEM>');
			 
		}
		$this->append2('</shop>'); 
		 
		ob_clean();  
		header('Content-type: application/xml; charset="utf-8"',true);  
		echo $this->sitemap; 
		die(); 
    }  
    
    
}