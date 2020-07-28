<?php
header('Content-Type: text/html; charset=utf-8'); 

/**
 * 
 * 
 * 
tag availability, vrací id stavu skladem, viz:
1 = Skladem
2 = Vyprodáno
3 = Dostupno u dodavatele
4 = Zpožděno u dodavatele

 * 
 * 
 * import zbozi  
 * @author Mitch
 *
 */

class module_ImportMagnet
{   
	private $availabilityMap = array(
		1 => 'SKLADEM', 
		2 => 'Není skladem', 
		3 => 'Produkt je dočasně nedostupný',
		4 => 'Předobjednávky'       
	);
	 
	private $_tableNameColors = 'module_eshop_colors';
	private $_tableNameSizes = 'module_eshop_sizes';
	private $_tableNameSkladem = 'module_eshop_skladem';
	
	function __construct($view){
		$this->db =  Zend_Registry::getInstance()->db;  
		$this->config =  Zend_Registry::getInstance()->config; 
		$this->view = $view; 
		$this->tree =  Zend_Registry::getInstance()->tree;  
	}
	
	function productExistsByFcode($fcode){
		$e =  $this->db->fetchOne(
			"SELECT id FROM `content_Product` WHERE `varianta_1_family_code` = ?",
			$fcode
		);
		
		return $e > 1;
	} 
	
	function getProductByFcode($fcode){  
		$p =  $this->db->fetchRow("
			SELECT `cm`.`id` AS `cid`, `n`.`id`, `n`.`title`, `n`.`path`
			
			FROM `content_Product` AS `cm` 
				INNER JOIN `NodesContents` AS `nc` ON cm.id = nc.c_id 
				INNER JOIN `Nodes` AS `n` ON n.id = nc.n_id 
				
			WHERE (c_type = 'content_Product' AND `varianta_1_family_code` = ?)    
			",
			$fcode
		);
		
		$product = false;
		if($p['id']){
			$product = $this->tree->getNodeById($p['id']);
		}
		return $product;
	}
	

 
	function import($vyprodej = false, $importColors = false, $start = 0, $stop = 10){ 
		 
   		/* 
   		/e-shop/povlaky-a-potahy  = 7373
   		/e-shop/deky-a-polstare = 7372
   		/e-shop/povleceni-na-jednoluzka = 7371 
   		/e-shop/povleceni-na-dvouluzka = 7370
   		*/
   		
   		if($vyprodej){
   			$this->patern = array(
   				'> Ložnice >'  
   			);
   			$this->AddToo = array(
   				'Domov > Ložnice > Povlečení' => array(7371, 7370),
   				'Domov > Ložnice > Prostěradla, matrace' => array(7373),
   				'Domov > Ložnice > Přikrývky, polštáře, deky' => array(7372)
   			); 
   		} else {
   			$this->patern = array(
   				'> Povlečení >',
	   			'> Lužkoviny a doplňky >', 
	   			'> Lůžkoviny a doplňky >'  
   			);
   			
   			$this->AddToo = array(
   				'Bytový textil > Povlečení > S květinovým motivem' => array(7371, 7370),
   				'Bytový textil > Povlečení > Originální' => array(7371, 7370),
   				'Bytový textil > Povlečení > Originální ' => array(7371, 7370),
   				'Bytový textil > Povlečení > S motivem zvířat' => array(7371, 7370),
   				'Bytový textil > Povlečení > Jednobarevné a vícebarevné' => array(7371, 7370),
   				'Bytový textil > Povlečení > Jednobarevné a vícebarevné ' => array(7371, 7370),
   				'Bytový textil > Lužkoviny a doplňky > Povlaky a potahy na matrace' => array(7373),
   				'Bytový textil > Lužkoviny a doplňky > Přikrývky' => array(7372),
   				'Bytový textil > Lužkoviny a doplňky > Deky' => array(7372),  
   				'Bytový textil > Lužkoviny a doplňky > Ložní doplňky' => array(8675),
   				'Bytový textil > Lužkoviny a doplňky > Polštáře a podhlavníky' => array(8675),
   				'Bytový textil > Lužkoviny a doplňky > Chrániče matrací a roštů' => array(8675),
   				'Bytový textil > Lužkoviny a doplňky > Chrániče matrací a roštů ' => array(8675) 
   			); 
   		}
   		
		if($vyprodej){  
   			$file = $this->config->dataRoot . '/vyprodej.xml'; 
   		} else {
   			$file = $this->config->dataRoot . '/blancheporte.xml';
   		}
   			
   		if($importColors){ // tak to i stahnu
   			if($vyprodej){   
   				$path = 'http://www.vyprodej-slevy.cz/export/feed-images-vyprodej.xml';
   			} else {
   				$path = 'http://www.blancheporte.cz/export/feed-images.xml'; 
   			}
   			unlink($file);   
   			file_put_contents($file, Utils::loadUrl($path, 30));  
   		}
   		
   		
   		
		$xmlPathVyprodej = 'http://www.vyprodej-slevy.cz/export/feed-images-vyprodej.xml';
		$xmlPathBlancheporte = 'http://www.blancheporte.cz/export/feed-images.xml'; 

		if($vyprodej){  
			$data = $this->loadXML($file, $vyprodej);      
			$dataGrouped = $this->groupProductsData($data, $start, $stop );             //220, 240    
		} else {        
			$data = $this->loadXML($file, $vyprodej);
			$dataGrouped = $this->groupProductsData($data, $start, $stop);                               
		}  
	 	
		if($importColors){
			$colors = $sizes = array();  
			foreach ($data as $d){
				$p1 = 'id-color';     
				$p2 = 'id-size';
				
				$d->size = str_replace(array(', ', '+', ','), ' ', $d->size);
				$d->size = str_replace(array('  '), ' ', $d->size);
				 
				$d->color = str_replace(array(', ', '+', ','), ' ', $d->color);
				$d->color = str_replace(array('  '), ' ', $d->color);
				
				$colors[$d->$p1] = $d->color;
				$sizes[$d->$p2] = $d->size;
			}
			$this->addColors($colors, $vyprodej);
			$this->addSizes($sizes, $vyprodej); 
			 
			$this->saveSkladem($fcode, $dataGrouped, $vyprodej);
		
			e(count($colors)); e(count($sizes)); pr($colors);pr($sizes); die();
		}   
		 
		//e(count($data));  
		// e(count($dataGrouped)); 
		//  pr($data); pr($dataGrouped );  die(); 
		$this->addProducts($dataGrouped, $vyprodej); 
	}
	
	function addNew($fcode, $variants, $isVyprodej){
		$inputContent = new stdClass();
			$input = new stdClass();
			$variantId = 0; 
			$jednoluzko = $dvouluzko = false;
			$skladem = 2;
			foreach ($variants as $v){
				$variantId++;
				$input->pageTitle  = $v->title;
				$inputContent->fck_preText = $inputContent->preText = $v->preview;
				$inputContent->fck_html = $inputContent->html = $v->content?$v->content:$v->preview;
				
				$inputContent->fck_html = str_replace('Blancheporte', 'Eshop-povlečení', $inputContent->fck_html);
				$inputContent->fck_preText = str_replace('Blancheporte', 'Eshop-povlečení', $inputContent->fck_preText);
				  
				if(!$inputContent->price  && $v->availability == 1){
					$inputContent->price = $v->price;
					$inputContent->price2 = $v->price_old?$v->price_old:0;
				} 
				
				// id 6
				// bavlna 1, polycoton 2, polyester 3, flanel 4
				$katka = 0;  
				if(mb_strpos($fck_html, 'polycoton', 0, 'utf-8')!== false || mb_strpos($fck_html, 'Polycoton', 0, 'utf-8')!== false){  
					$katka = 2; 
				} elseif(mb_strpos($fck_html, 'flanel', 0, 'utf-8') !== false || mb_strpos($fck_html, 'Flanel', 0, 'utf-8')!== false) {
					$katka = 4;     
				} elseif(0 && mb_strpos($fck_html, 'polyester', 0, 'utf-8')) {
					$katka = 3;
				} elseif(mb_strpos($fck_html, 'bavlna', 0, 'utf-8')!== false || mb_strpos($inputContent->fck_html, 'bavlny', 0, 'utf-8')!== false || mb_strpos($fck_html, 'Bavlna', 0, 'utf-8')!== false || mb_strpos($inputContent->fck_html, 'Bavlny', 0, 'utf-8')!== false) {
					$katka = 1;
				} 
				
				$pv20 = "varianta_" . $variantId . "_kategorie_1"; 
				$inputContent->$pv20 = 0;
				$pv21 = 'kategorie_1_id';
				
				if($katka > 0){ 
					$inputContent->$pv20 = array($katka); 
					$inputContent->$pv21 = 6;
				}
				
				$n1 = 'id-color';
				$n2 = 'id-size';
				   
				$inputContent->EAN = $v->ean;  
				$inputContent->author = 'a';
				$inputContent->prodejny = 1;
				$inputContent->colorId = $v->$n1; 
				$inputContent->sizeId = $v->$n2;
				$inputContent->isBlancheporte = $v->isBlancheporte;
				
				$pv1 = "varianta_" . $variantId . "_nazev";
				$pv2 = "varianta_" . $variantId . "_id";
				$pv3 = "varianta_" . $variantId . "_EAN";
				$pv4 = "varianta_" . $variantId . "_dostupnost";
				$pv5 = "varianta_" . $variantId . "_poradi"; 
				$pv6 = "varianta_" . $variantId . "_obrazky";
				$pv7 = "varianta_" . $variantId . "_sizeId"; 
				$pv8 = "varianta_" . $variantId . "_colorId";  
				
				$pv9 = "varianta_" . $variantId . "_extId";
				$pv10 = "varianta_" . $variantId . "_family_code";  
				$pv11 = "varianta_" . $variantId . "_logistic_number";   
				$pv12 = "varianta_" . $variantId . "_item_group_id";
				$pv13 = "varianta_" . $variantId . "_cena";  
				$pv14 = "varianta_" . $variantId . "_cena2";  
				
				$v->size = str_replace(array(', ', '+', ','), ' ', $v->size);
				$v->size = str_replace(array('  '), ' ', $v->size);
				if($v->size == '70x90 140x200cm'){
					$v->size = 'jednolůžko 70x90 140x200cm'; 
				}
				if($v->size == 'jednolůžko 140x200 70x90cm'){
					$v->size = 'jednolůžko 70x90 140x200cm'; 
				}
				if($v->size == '70x90 140x200 140x230cm'){
					$v->size = '70x90cm 140x200cm 140x230cm'; 
				}   
			
				$inputContent->$pv1 = $v->size?$v->size:$v->title;  
				$inputContent->$pv3 = $v->ean;   
				$inputContent->$pv2 = $v->id; // $variantId; //$v->logistic_number;   
				$inputContent->$pv4 = ($v->availability == 1?1:2);
				if($skladem == 2 && $v->availability == 1){
					$skladem = 1;
				}
				$inputContent->$pv5 = $variantId;
				$inputContent->$pv7 = $v->$n2;
				$inputContent->$pv8 = $v->$n1;
				$inputContent->$pv9 = $v->extId;
				$inputContent->$pv10 = $v->family_code;
				$inputContent->$pv11 = $v->logistic_number;
				$inputContent->$pv12 = $v->item_group_id;
				$inputContent->$pv13 = $v->price?$v->price:$inputContent->price;  
				$v->price_old = $v->price_old?$v->price_old:$inputContent->price2;
				
				$inputContent->$pv14 = $v->price_old?$v->price_old:0;
								 
				if(is_array($v->vimgs) && count($v->vimgs)){
					$vimg = $v->vimgs;
				} else {   
					$vimg = array($v->image_link); 
				}
				
				// FOTKY //
				if(count($vimg)){
					$i = array();
					foreach ($vimg as $ii){
						$i[] = $this->importImage($ii, $isVyprodej);
					}
					$inputContent->$pv6 = implode(';', $i);
				}
				if($v->imgs && !$inputContent->photos){
					
					if(is_array($v->imgs) && count($v->imgs)){
						$imgs = $v->imgs; 
					} else {
						$imgs = array($v->image_link); 
					}
					
					$i2 = array();
					foreach ($imgs as $ii){
						$i2[] = $this->importImage($ii, $isVyprodej);
					}
					$inputContent->photos = implode(';', $i2); 
				} 
				
				$inputContent->colorId = $v->$n1;
				$inputContent->sizeId = $v->$n2;
				
				if(strpos($v->size, 'jednolůžko') !== false){
					$jednoluzko = true;
				} elseif(strpos($v->size, 'dvoulůžko') !== false){
					$dvouluzko = true;
				} 
				
				//pr($v);
			}
			
			$cats = $this->AddToo[$v->cat];   
 			if($isVyprodej){    
				if($v->cat == 'Domov > Ložnice > Povlečení'){
					if(!$jednoluzko){ unset($cats[0]); }
					if(!$dvouluzko){ unset($cats[1]); }
				}
 			} else {
 				if(mb_strpos($v->cat, '> Povlečení >', 0, 'utf-8')){    
					if(!$jednoluzko){ unset($cats[0]); }
					if(!$dvouluzko){ unset($cats[1]); }
				}
 			}
			
			
			$inputContent->parentSection = implode('|', $cats); 	    
			$inputContent->skladem = $skladem; 	    
			
			
			$this->addNodesAction(3801, $input, $inputContent);
	}
	
	function getVariants($node){
		$content = $node->getPublishedContent();
		$varianty = array(); 
		//pr($content->properties);
		foreach ($content->properties as $p){
			if(strpos($p->name, 'varianta_') === 0){
				$cv = intval(substr($p->name, 9, 2));
				if(($p->value != '' && $p->value != 0) || count($varianty[$cv])){  
					$varianty[$cv][$p->name] = $p->value; 
				}
			}
		}
		$varianty2 = array();
		foreach ($varianty as $v){
			$cv = intval(substr(current(array_keys($v)), 9, 2)); 
			if($v['varianta_' . $cv . '_EAN'] > 0){
				$varianty2[] = $v;
			}
		}
		
		return $varianty2;  
	}
	
	function updateOld($fcode, $variants, $isVyprodej){
		$nodeToUpdate = $this->getProductByFcode($fcode); 
		$contentToUpdate = $nodeToUpdate->getPublishedContent();
		$variantsAll = $variants; 
		 
		$variantyOld = $this->getVariants($nodeToUpdate); 
		
		
				// pr($contentToUpdate);    
		  
			
		// ZMENA CEN 
		// ZMENA VARIANT 
		$n1 = 'id-color'; 
		$n2 = 'id-size'; 
				
		$input = new stdClass();
		$variantId = 0;
		$jednoluzko = $dvouluzko = false;	 	
		$skladem = 2; 
		foreach ($variants as $v){     
			if($skladem == 2 && $v->availability == 1){
				$skladem = 1;
			}  
		} 
		$inputContent = new stdClass(); 
		$inputContent->skladem = $skladem; 	  
		
		
		// stavajici varianty pouze update, nove nakonec, musi zustat zachovano i poradi i stare kuli obj atd  
		$eanExists =$notAvailablePhotos =  array();
		foreach ($variantyOld as $vId => $data){
			$variantId++;  
			$v = ($variants[$data['varianta_' . $variantId . '_extId']]);    
			if(!$v){ continue; }
			$pv1 = "varianta_" . $variantId . "_nazev"; 
			$pv2 = "varianta_" . $variantId . "_id";  
			$pv3 = "varianta_" . $variantId . "_EAN"; 
			$pv4 = "varianta_" . $variantId . "_dostupnost";
			$pv5 = "varianta_" . $variantId . "_poradi"; 
			$pv6 = "varianta_" . $variantId . "_obrazky";
			$pv7 = "varianta_" . $variantId . "_sizeId";
			$pv8 = "varianta_" . $variantId . "_colorId";
			
			$pv9 = "varianta_" . $variantId . "_extId";
			$pv10 = "varianta_" . $variantId . "_family_code";  
			$pv11 = "varianta_" . $variantId . "_logistic_number";  
			$pv12 = "varianta_" . $variantId . "_item_group_id";
			$pv13 = "varianta_" . $variantId . "_cena";  
			$pv14 = "varianta_" . $variantId . "_cena2";
			
			$v->size = str_replace(array(', ', '+', ','), ' ', $v->size);
			$v->size = str_replace(array('  '), ' ', $v->size);
			if($v->size == '70x90 140x200cm'){
				$v->size = 'jednolůžko 70x90 140x200cm'; 
			} 
			if($v->size == 'jednolůžko 140x200 70x90cm'){
				$v->size = 'jednolůžko 70x90 140x200cm';   
			}  
			if($v->size == '70x90 140x200 140x230cm'){
				$v->size = '70x90cm 140x200cm 140x230cm'; 
			}   
			
			
			$inputContent->$pv1 = $v->size?$v->size:$v->title;
			$inputContent->$pv3 = $v->ean;  
			$inputContent->$pv2 = $v->id;
			$inputContent->$pv6 = $data[$pv6];  
			$inputContent->$pv2 = $data[$pv2];
			 
			$inputContent->$pv4 = ($v->availability == 1?1:2);
			
			if(!$inputContent->price && $v->availability == 1){
					$inputContent->price = $v->price;
					$inputContent->price2 = $v->price_old?$v->price_old:0;
				}
				
			$inputContent->$pv5 = $variantId;
			$inputContent->$pv7 = $v->$n2;
			$inputContent->$pv8 = $v->$n1;   
			$inputContent->$pv9 = $v->extId;
			$inputContent->$pv10 = $v->family_code;
			$inputContent->$pv11 = $v->logistic_number;
			$inputContent->$pv12 = $v->item_group_id; 
			
			$inputContent->$pv13 = $v->price?$v->price:$inputContent->price;
			$v->price_old = $v->price_old?$v->price_old:$inputContent->price2;
			$inputContent->$pv14 = $v->price_old?$v->price_old:0;
			/*
			if($v->family_code){ // existuje -> update 
				
			} else { // neexistuje ale existovala = skladovost 0  
			} 
			*/  
			
			if(!$v->image_variant_link && !$isVyprodej){
				$notAvailablePhotos[] = $variantId;
			}

			$fck_html = $v->content?$v->content:$v->preview; 
			
			$katka = 0;  
			if(mb_strpos($fck_html, 'polycoton', 0, 'utf-8')!== false || mb_strpos($fck_html, 'Polycoton', 0, 'utf-8')!== false){  
				$katka = 2; 
			} elseif(mb_strpos($fck_html, 'flanel', 0, 'utf-8') !== false || mb_strpos($fck_html, 'Flanel', 0, 'utf-8')!== false) {
				$katka = 4;     
			} elseif(0 && mb_strpos($fck_html, 'polyester', 0, 'utf-8')) {
				$katka = 3;
			} elseif(mb_strpos($fck_html, 'bavlna', 0, 'utf-8')!== false || mb_strpos($inputContent->fck_html, 'bavlny', 0, 'utf-8')!== false || mb_strpos($fck_html, 'Bavlna', 0, 'utf-8')!== false || mb_strpos($inputContent->fck_html, 'Bavlny', 0, 'utf-8')!== false) {
				$katka = 1;
			}  
			
			$pv20 = "varianta_" . $variantId . "_kategorie_1"; 
			$inputContent->$pv20 = 0;
			$pv21 = 'kategorie_1_id';  
			
			if($katka > 0){
				//e('$variantyOld'); 
				//pr($variantyOld);
				$inputContent->$pv20 = array($katka);
				$inputContent->$pv21 = 6;
			}
				
			unset($variants[$data['varianta_' . $variantId  . '_extId']]); 
		}

		
		
		
		 
				 
		// konttrola na demence
		$variantId2 = 0; 
		foreach ($variantyOld as $vId => $data){
			$variantId2++; 
			$pnn =  "varianta_" . $variantId2 . "_logistic_number";  
			$pnnE =  "varianta_" . $variantId2 . "_extId";   
			$pnnD =  "varianta_" . $variantId2 . "_dostupnost";   
			$val = $data[$pnn];  
			$valE = $data[$pnnE];  
			foreach ($variantsAll as $ext => $data){ 
				$val2 = $data->logistic_number; 
				if($val == $val2 && $ext != $valE){   
					$inputContent->$pnn = 'none'; 
					$inputContent->$pnnD = 2; 
				}
			}
		}   
		
		
		   
		$variantId2 = 0; 
		foreach ($variantyOld as $vId => $data){
			$key = current(array_keys($data));
			$key = explode('_', $key);
			$variantId2 = $key[1]; 
			// e($data['varianta_' . $variantId2 . '_extId']);
			$v = ($variantsAll[$data['varianta_' . $variantId2 . '_extId']]);    
			if(!$v){ continue; } 
			$pv4 = "varianta_" . $variantId2 . "_dostupnost";  
		
			if($eanExists[$v->ean]){
				$v = new stdClass(); 
				$data = array();
				  
			
				
				$pv1 = "varianta_" . $variantId2 . "_nazev"; 
				$pv2 = "varianta_" . $variantId2 . "_id"; 
				$pv3 = "varianta_" . $variantId2 . "_EAN";
				$pv4 = "varianta_" . $variantId2 . "_dostupnost";
				$pv5 = "varianta_" . $variantId2 . "_poradi"; 
				$pv6 = "varianta_" . $variantId2 . "_obrazky";
				$pv7 = "varianta_" . $variantId2 . "_sizeId";
				$pv8 = "varianta_" . $variantId2 . "_colorId";
				
				$pv9 = "varianta_" . $variantId2 . "_extId";
				$pv10 = "varianta_" . $variantId2 . "_family_code";  
				$pv11 = "varianta_" . $variantId2 . "_logistic_number";  
				$pv12 = "varianta_" . $variantId2 . "_item_group_id";
				$pv13 = "varianta_" . $variantId2 . "_cena";  
				$pv14 = "varianta_" . $variantId2 . "_cena2";     
				
				$inputContent->$pv1 = ''; 
				$inputContent->$pv3 = '';   
				$inputContent->$pv2  = ''; 
				$inputContent->$pv4 = 2;
				$inputContent->$pv5 = 0;
				$inputContent->$pv7  = ''; 
				$inputContent->$pv8  = ''; 
				$inputContent->$pv9 = '';   
				$inputContent->$pv10 = '';   
				$inputContent->$pv11 = ''; 
				$inputContent->$pv12 = ''; 
			} 
			if($v->ean){
				$eanExists[$v->ean] = $v->ean;
			}  
			$inputContent->$pv4 = ($v->availability == 1?1:2); 
		}      
		
		$variantId = count($variantyOld);
		
		foreach ($variants as $v){ 
				$variantId++;
				$input->pageTitle  = $v->title; 
				
				if(!$inputContent->price && $v->availability == 1){
					$inputContent->price = $v->price;
					$inputContent->price2 = $v->price_old?$v->price_old:0;
				}
				$fck_html = $v->content?$v->content:$v->preview;
				$katka = 0;  
				if(mb_strpos($fck_html, 'polycoton', 0, 'utf-8')!== false || mb_strpos($fck_html, 'Polycoton', 0, 'utf-8')!== false){  
					$katka = 2; 
				} elseif(mb_strpos($fck_html, 'flanel', 0, 'utf-8') !== false || mb_strpos($fck_html, 'Flanel', 0, 'utf-8')!== false) {
					$katka = 4;     
				} elseif(0 && mb_strpos($fck_html, 'polyester', 0, 'utf-8')) {
					$katka = 3;
				} elseif(mb_strpos($fck_html, 'bavlna', 0, 'utf-8')!== false || mb_strpos($inputContent->fck_html, 'bavlny', 0, 'utf-8')!== false || mb_strpos($fck_html, 'Bavlna', 0, 'utf-8')!== false || mb_strpos($inputContent->fck_html, 'Bavlny', 0, 'utf-8')!== false) {
					$katka = 1;
				} 
				
				$pv20 = "varianta_" . $variantId . "_kategorie_1"; 
				$inputContent->$pv20 = 0;
				$pv21 = 'kategorie_1_id';
				
				if($katka > 0){ 
					$inputContent->$pv20 = array($katka);
					$inputContent->$pv21 = 6;
				}
				
				$inputContent->EAN = $v->ean;  
				$inputContent->author = 'a';
				$inputContent->prodejny = 1;
				$inputContent->colorId = $v->$n1; 
				$inputContent->sizeId = $v->$n2;
				$inputContent->isBlancheporte = $v->isBlancheporte;
				
				$pv1 = "varianta_" . $variantId . "_nazev"; 
				$pv2 = "varianta_" . $variantId . "_id"; 
				$pv3 = "varianta_" . $variantId . "_EAN";
				$pv4 = "varianta_" . $variantId . "_dostupnost";
				$pv5 = "varianta_" . $variantId . "_poradi"; 
				$pv6 = "varianta_" . $variantId . "_obrazky";
				$pv7 = "varianta_" . $variantId . "_sizeId";
				$pv8 = "varianta_" . $variantId . "_colorId";
				
				$pv9 = "varianta_" . $variantId . "_extId";
				$pv10 = "varianta_" . $variantId . "_family_code";  
				$pv11 = "varianta_" . $variantId . "_logistic_number";  
				$pv12 = "varianta_" . $variantId . "_item_group_id";
				$pv13 = "varianta_" . $variantId . "_cena";  
				$pv14 = "varianta_" . $variantId . "_cena2";    
				
				$v->size = str_replace(array(', ', '+', ','), ' ', $v->size);
				$v->size = str_replace(array('  '), ' ', $v->size);
				
				
				if(!$v->image_variant_link && !$isVyprodej){
					$notAvailablePhotos[] = $variantId;
				}
			
				$inputContent->$pv1 = $v->size?$v->size:$v->title; 
				$inputContent->$pv3 = $v->ean;   
				$inputContent->$pv2 = $v->id; //$variantId; //$v->logistic_number;
				$inputContent->$pv4 = ($v->availability == 1?1:2);  
				$inputContent->$pv5 = $variantId;
				$inputContent->$pv7 = $v->$n2;    
				$inputContent->$pv8 = $v->$n1;
				$inputContent->$pv9 = $v->extId;
				$inputContent->$pv10 = $v->family_code;
				$inputContent->$pv11 = $v->logistic_number;
				$inputContent->$pv12 = $v->item_group_id;
				
				$inputContent->$pv13 = $v->price?$v->price:$inputContent->price;
				$v->price_old = $v->price_old?$v->price_old:$inputContent->price2;
				$inputContent->$pv14 = $v->price_old?$v->price_old:0;   
				  
				if(is_array($v->vimgs) && count($v->vimgs)){
					$vimg = $v->vimgs;
				} else {
					$vimg = array($v->image_link); 
				}
				
				// FOTKY //
				if(count($vimg)){
					$i = array();
					foreach ($vimg as $ii){
						$i[] = $this->importImage($ii, $isVyprodej);
					}
					$inputContent->$pv6 = implode(';', $i);
				}
				if($v->imgs && !$inputContent->photos){
					
					if(is_array($v->imgs) && count($v->imgs)){
						$imgs = $v->imgs; 
					} else {
						$imgs = array($v->image_link); 
					}
					
					$i2 = array();
					foreach ($imgs as $ii){
						$i2[] = $this->importImage($ii, $isVyprodej);
					}
					$inputContent->photos = implode(';', $i2); 
				} 
				
				$inputContent->colorId = $v->$n1;
				$inputContent->sizeId = $v->$n2;
				
				if(strpos($v->size, 'jednolůžko') !== false){
					$jednoluzko = true;
				} elseif(strpos($v->size, 'dvoulůžko') !== false){
					$dvouluzko = true;
				} 
				
				//pr($v);				
			} 
			
			$cats = $this->AddToo[$v->cat];   
 
			if($isVyprodej){    
				if($v->cat == 'Domov > Ložnice > Povlečení'){
					if(!$jednoluzko){ unset($cats[0]); }
					if(!$dvouluzko){ unset($cats[1]); }
				}
 			} else {
 				if(mb_strpos($v->cat, '> Povlečení >', 0, 'utf-8')){     
					if(!$jednoluzko){ unset($cats[0]); }
					if(!$dvouluzko){ unset($cats[1]); }
				}
 			}   
 			
			// $inputContent->notAvailablePhotos = implode(',', $notAvailablePhotos);     
		
			foreach ($inputContent as $k => $v){   
				$contentToUpdate->getPropertyByName($k)->value = $v; 
			}  
			//pr($inputContent); pr($contentToUpdate); die(); 
			$this->updateNodesAction($nodeToUpdate, $contentToUpdate, $inputContent); 
	}
	
	function addProducts($dataGrouped, $isVyprodej){ 
		foreach ($dataGrouped as $fcode => $variants){  
			if($this->productExistsByFcode($fcode)){
				$this->updateOld($fcode, $variants, $isVyprodej);
			} else {
				$this->addNew($fcode, $variants, $isVyprodej);
			}
		}     
	}
	
	function saveSkladem($fcode, $dataGrouped, $isVyprodej){
		
		$this->db->delete( $this->_tableNameSkladem , "vyprodej = '" . ($isVyprodej?1:0) . "'"); 
		
		foreach ($dataGrouped as $fcode => $variants){  
			foreach ($variants as $v){
				 
				if($isVyprodej){ } else { 
					if($v->availability == 1){
						$v->count = 100;
					} else {
						$v->count = 0;
					} 
				}
				$c_data = array(
					'ident' => $v->logistic_number, 
					'sklademKS' => $v->count, 
					'vyprodej' => ($isVyprodej?1:0) 
				); 
				//$this->db->delete( $this->_tableNameSkladem , "ident = '" . $v->logistic_number . "'"); 
				$this->db->insert( $this->_tableNameSkladem , $c_data);   
				 
			}
		}
		
	}
	
	
	function getProductByLogisticNumber($logId){
		$w = array();
		for($i = 1; $i <= 15; $i++){
			$w[] = " varianta_" . $i . "_item_group_id = ? ";
		}
		$ww = implode(' OR ', $pieces);
		return $this->db->fetchRow("SELECT * FROM content_Product WHERE ( $ww )", $logId);
	}
	
	
	function checkProduct($contentId){
		 $prod = $this->db->fetchRow("SELECT * FROM content_Product where id=? ",$contentId);
		 $count = 0;
		 for ($i = 1; $i < 16; $i++) {
		 	if($prod['varianta_'.$i.'_logistic_number']){
		 		$actCount = 0;
		 		$count += $actCount = $this->db->fetchOne("SELECT sklademKS FROM module_eshop_skladem where ident = ?", $prod['varianta_'.$i.'_logistic_number']);
		 		$where = $this->db->quoteInto('id = ?', $contentId);
		 		$data = array('varianta_'.$i.'_dostupnost' => ($actCount>0?'1':'2'));
				$this->db->update(
					'content_Product',
					$data,
					$where);
		 	}
		 }
		 if($count==0)
		 {
		 	$where = $this->db->quoteInto('id = ?', $contentId);
		 	$data = array('skladem' => 2);
			$this->db->update(
				'content_Product',
				$data,
				$where);
		 }
	}
	
	function checkAllProducts(){
		$all = $this->db->fetchAll("SELECT * FROM content_Product");
		foreach ($all as $p){ 
			$this->checkProduct($p['id']);
		}
	} 
	
	
	function removeSkladem($logId, $sold){
		$skladem = $this->getSkladem($logId);
		$this->db->delete( $this->_tableNameSkladem , "ident = '" . $logId . "'"); 
		$new = max($skladem - $sold, 0);
		$c_data = array(
			'ident' => $logId, 
			'sklademKS' => $new  
		);
		$this->db->insert( $this->_tableNameSkladem , $c_data); 
		$this->checkProduct($logId);  
		
		// $this->checkProduct($logId);
		return $new;
	}
	
	function getSkladem($logId){
		$s = $this->db->fetchOne("SELECT sklademKS FROM " . $this->_tableNameSkladem . " WHERE ident = ?", $logId);
		return ($s>0?$s:0);
	}
	
	
	function importImage($image, $isVyprodej, $returnFullPath = 10){ // 3880
		
		if($isVyprodej){
			$addTo = 3880;
		} else {
			$addTo = 12217;
		}
				 
		if(in_array($image, $this->importedImages)){ 
			return $this->importedImages[$image];  
		} else {
			
		}		
		$config = $this->config;     
		if($image){
			$contents = file_get_contents($image);
			
			if($isVyprodej){
				$imageName = str_replace('http://www.vyprodej-slevy.cz/vs_data/imgs/', '', $image); 
			} else {
				$imageName = str_replace('http://www.blancheporte.cz/data/imgs/', '', $image);  				
			}
			//
			 
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
			return $path;  
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
	
	function  addColors($colors, $vyprodej = true){ 
		foreach ($colors as $id => $color){  
			if(!$id && !$color){ continue; }  
			$c_data = array(
				'cid' => $id, 
				'ctitle' => $color
			);
			$this->db->delete( $this->_tableNameColors , "cid = $id"); 
			$this->db->insert( $this->_tableNameColors , $c_data); 
		}
	}
	
	function  addSizes($sizes, $vyprodej = true){ 
		foreach ($sizes as $id => $size){  
			if(!$id && !$size){ continue; }    
			$s_data = array(
				'sid' => $id,  
				'stitle' => $size
			);
			$this->db->delete( $this->_tableNameSizes , "sid = $id"); 
			$this->db->insert( $this->_tableNameSizes , $s_data);
		}
	}
	
	function groupProductsData($data, $min = 0, $max = 40000000){
		$newData = array();
		$newData2 = array();
		$cats  = array();
		
		foreach ($data as $prod){
			$cats[$prod->cat] = $prod->cat;
			
			$newData[$prod->family_code][$prod->extId] = $prod;  
			$c = count($newData);
			
			if($c > $min && $c <= $max){
				$newData2[$prod->family_code][$prod->extId] = $prod;
			} else {     
				
			}
		} 
		//pr($cats);  
		e(count($newData));
		
		return $newData2;
	}
	
	function loadXML($xmlPath, $vyprodej = true, $min = 0, $max = 40000000){
		$reader = new XMLReader();
		$isopen = $reader->open($xmlPath);   
		$products = array();
		$data = new stdClass(); 
		$i = 0;
		$productsCount = 0; 
		 
		$p1 = 'id-color';      
		$p2 = 'id-size'; 
						
		while ($reader->read()){
		   if ($reader->nodeType == XMLREADER::ELEMENT) {  
		   		$i++;
		   		
		   		switch ($reader->localName) { 
		   			case 'channel': 
		   			case 'description':
		   				break;
		   			case 'item': 
		   				if($data->extId){
		   					$add = false;
		   					foreach ($this->patern as $p){
		   						if(mb_strpos($data->cat, $p, 0, 'utf-8')){
		   							$add = true;  
		   							break;
		   						}
		   					}
		   					 		   					
		   					if($data->availability != 1){
		   						//  $add = false;      // musime vsechny kuli update, aby sme poznali co uz je vyprodany 
		   					}
		   					
		   					if(!mb_strpos($data->title, 'Katia-flanel', 0,  'utf-8')){
		   					 	//  $add = false;                   
		   					}   
		   					   
		   					//if(count($data->imgs) < 3){ $add = false; }
		   					
		   					if($add){
		   						if($vyprodej){
		   							$data->isBlancheporte = 0;
		   						} else {
		   							$data->isBlancheporte = 1;  
		   						}
		   						
		   						if($productsCount < $min){
		   			 				 
		   						} else {
				   					$products[] = $data;    
		   						}
		   						
				   				$productsCount++;
				   				$data = new stdClass();   
		   					}
			   			} 
			   			$data = new stdClass();   
		   				//$data->extId = $reader->getAttribute('id');  
		   				break;  
		   			case 'id':
		   				$data->extId = $reader->readString();
		   				break;  
				
		   			case 'id-size': 
		   				$i = $reader->readString();
		   				if(!$vyprodej){ $i = $i + 10000;  } // stejna id, odlisime +10000, v obj pak -10000
		   				$data->$p2 = $i;
		   				break;
		   			case 'id-color':
		   				$i = $reader->readString(); 
		   				if(!$vyprodej){ $i = $i + 10000;  } // stejna id, odlisime +10000, v obj pak -10000
		   				$data->$p1 = $i;
		   				break;   			
		   			case 'image_variant_link':
		   				if(!$data->vimgs){ $data->vimgs = array(); }
		   				$data->vimgs[] = $reader->readString();
		   				$n = $reader->localName;
		   				$data->$n = $reader->readString();
		   				break;   
		   			case 'image_link': 
		   				if(!$data->imgs){ $data->imgs = array(); }
		   				$data->imgs[] = $reader->readString(); 
		   				$n = $reader->localName;
		   				$data->$n = $reader->readString();
		   				break;   
		   			default:
		   				$n = $reader->localName;
		   				$data->$n = str_replace(' CZK', '', $reader->readString()); 
		   		}
		   		
		   		if($productsCount > $max){
		   			break;
		   		} 
		   }
		} 
		return ($products); 
	}
	
	
	/*******************  
			SAVE 
	********************/  
	
	function updateNodesAction($node, $content, $inputContent){  
		$node->save(null, null, false); 
		// pr($node);    pr($content); die();
		$content->update();    
		   
		$_POST = $_GET = (array) $inputContent;      
    	
    	$this->view->requestParams = $this->view->request->getParams();
    	     
    	// pr($this->view->requestParams); die();       
    	$this->saveData($this->view, $node, $content);
	}
	
	function addNodesAction($nodeAddTo, $input, $inputContent, $ctype = 'Product'){  
		// pr($inputContent); die(); 
		  
		$newNode = Node::init('ITEM', $nodeAddTo, $input, $this->view); 
		$content = Content::init($ctype, $inputContent, false);	   
		
		$content->getPropertyByName('parent')->value = $inputContent->parentSection;
		$content->getPropertyByName('photos')->value = $inputContent->photos;  
			   
		$this->save($newNode, $content, $inputContent);
		   
	}
	

	function save($newNode, $content, $inputContent){		 
		$err2 = $content->save();	 
    	$this->tree->addNode($newNode, false, false); 
    	$this->tree->pareNodeAndContent($newNode->nodeId, $content->id, $content->_name);   
    	
    	$_POST = $_GET = (array) $inputContent;      
    	
    	$this->view->requestParams = $this->view->request->getParams();
    	     
    	$this->saveData($this->view, $newNode, $content);
	}
 	
	function saveData($view, $node, $content){
		$m_v = new module_Varianta();
		$idVarianty = $m_v->save($view, $node->nodeId);
		$db = Zend_Registry::getInstance()->db;
		$db->delete($content->_tableName, 'idProduct = ' . $node->nodeId);
		$i=1;
		while($_POST['varianta_' . $i . '_nazev'])
		{ 
			$j=1;
			while($_POST['kategorie_' . $j . '_id'])
			{
				$katId = $_POST['kategorie_' . $j . '_id'];
				$data = array();  
				$data['idProduct'] = $node->nodeId; 
				$data['cislo_varianty'] = $i;
				$data['idVarianta'] = $idVarianty[$i];
				$data['idKategorie'] = $katId;
				foreach($_POST['varianta_' . $i . '_kategorie_' . $j++] as $index)
				{
					if(!$index){
						continue;
					}
					//echo 'vlastnost_' . $index;  
					$data['vlastnost_' . $index] = 1;
					//print_r($data);   
				}
				// pr($data); die(); 
				$db->insert($content->_tableName,$data);
			}
			$i++;
		}
	}
	 
	function rename(){ 
		$contents = Utils::readFromFile($this->config->dataRoot. '/Rename.csv');
		$lines = explode('<br />', nl2br($contents));
		echo $contents;   
		pr($lines);
		die();
	}
}