<?php
class Cms_HelperController extends CmsController
{
		
	public function init()
	{
		parent::init();		    	

	}

	public function getAjaxFormDataAction()
	{		
		// /cms/helper/getAjaxFormData?=Administr%C3%A1tor%20syst%C3%A9mu%20(Administrators)&user=administrator		
		
		$doNotPass = array("module", "controller", "action");
		$newUri = '';
		foreach ($this->request->getParams() as $ident => $val){
			
			if(in_array($ident, $doNotPass)){
				continue;
			}
			
			//$newParams[$ident] = $val;
			$newUri .= '/' . urlencode($ident) . '/' . urlencode($val);
		}
				
		echo $newUri;
		
	}
	
	function enableAllLanguagesAction(){
		$this->view->languages->copyDefaultToAll();     
	}
	

	function enableAllLanguages2Action(){  
		$this->view->languages->copyDefaultToAllNew();     
	} 
	
	function addNodesAction(){
		 
		$nodeAddTo = 6516;        
		$nodes = array(           
			'Aikido' => 'HtmlFile',      
			'Capoeira' => 'HtmlFile'  
		);  
		  
		$nodes = array(
				'Telefony, navigace',
				'Domácí spotřebiče',
				'Elektronika',
				'Auto-moto',
				'Dětské zboží',
				'Foto',
				'Dům, byt a zahrada',
				'Počítače',
				'Oblečení a móda',
				'Sport',
				'Volný čas',
				'Kosmetika a zdraví', 
				'Turmalín',
				'Ravenna šedá',
				'Mramor modrý',
				'Petra šedá',
				'Petra tmavě šedá',
				'Granit antracit',
				'Třešeň',
				'Buk',
				'Casablanca bílá',
				'Olše impulz',
				'Olše',
				'Dub světlý',
				'Dub tmavý',
				'Onyx',
				'Virginia',
				'Zrnitá šeď',
				'Ravenna',
				'Mramor',
				'Granit',
				'Rosa porino modrá',
				'Kokos bolo',
				'Granit červený',
				'Wenge Luiziana',
				'Titan',
				'Granito Goya',
				'Ořech butcherblock',
				'Pískovec',
				'Alhambra světlá',
				'Alhambra hnědá' 
		       );                                        
				
		$html = array(
		'Kuchyňská pracovní deska matná –  Ravenna pistácie, Kód: 38, Struktura: PE, Kód lišty: 38, Tloušťka: 28 a 38 mm',
		'Kuchyňská pracovní deska matná –  Ravenna azuro, Kód: 39, Struktura: PE, Kód lišty: 39, Tloušťka: 28 a 38 mm',
		'Kuchyňská pracovní deska matná –  Bílá, Kód: 101, Struktura: PE, Kód lišty: 101, Tloušťka: 28 mm',
		'Kuchyňská pracovní deska matná –  Salome, Kód: 1702, Struktura: PG, Kód lišty: 1702, Tloušťka: 28 a 38 mm',
		'Kuchyňská pracovní deska matná –  Hrušeň planá světlá, Kód: 1764, Struktura: BS, Kód lišty: 1764, Tloušťka: 28 mm',
		'Kuchyňská pracovní deska matná –  Buk butcherblock, Kód: 1786, Struktura: BS, Kód lišty: 1786, Tloušťka: 28 a 38 mm',
		'Kuchyňská pracovní deska matná –  Calvados, Kód: 1792, Struktura: BS, Kód lišty: 1792, Tloušťka: 28 a 38 mm',
		'Kuchyňská pracovní deska matná –  Mramor černý, Kód: 1944, Struktura: PE, Kód lišty: 1944, Tloušťka: 28 mm',
		'Kuchyňská pracovní deska matná –  Mramor zelený, Kód: 1945, Struktura: PE, Kód lišty: 1945, Tloušťka: 28 mm',
		'Kuchyňská pracovní deska matná –  Mramor losos, Kód: 1947, Struktura: PE, Kód lišty: 1947, Tloušťka: 28 a 38 mm',
		'Kuchyňská pracovní deska matná –  Malaga zelená, Kód: 1983, Struktura: PE, Kód lišty: 1983, Tloušťka: 28 mm',
		'Kuchyňská pracovní deska matná –  Atlantis, Kód: 232, Struktura: PE, Kód lišty: 232, Tloušťka: 28 mm',
		'Kuchyňská pracovní deska matná –  Turmalín, Kód: 255, Struktura: PE, Kód lišty: 255, Tloušťka: 28 a 38 mm',
		'Kuchyňská pracovní deska matná –  Ravenna šedá, Kód: 263, Struktura: PE, Kód lišty: 263, Tloušťka: 28 a 38 mm',
		'Kuchyňská pracovní deska matná –  Mramor modrý, Kód: 208, Struktura: BS, Kód lišty: 208, Tloušťka: 28 mm',
		'Kuchyňská pracovní deska matná –  Petra šedá, Kód: 280, Struktura: PE, Kód lišty: 280, Tloušťka: 28 mm',
		'Kuchyňská pracovní deska matná –  Petra tmavě šedá, Kód: 281, Struktura: PE, Kód lišty: 281, Tloušťka: 28 mm',
		'Kuchyňská pracovní deska matná –  Granit antracit, Kód: 288, Struktura: PE, Kód lišty: 288, Tloušťka: 28 a 38 mm',
		'Kuchyňská pracovní deska matná –  Třešeň, Kód: 344, Struktura: CF, Kód lišty: 344, Tloušťka: 28 a 38 mm',
		'Kuchyňská pracovní deska matná –  Buk, Kód: 381, Struktura: CF, Kód lišty: 381, Tloušťka: 28 a 38 mm',
		'Kuchyňská pracovní deska matná –  Casablanca bílá, Kód: 458, Struktura: PE, Kód lišty: 458, Tloušťka: 28 a 38 mm',
		'Kuchyňská pracovní deska matná –  Olše impulz, Kód: 637, Struktura: CF, Kód lišty: 637, Tloušťka: 28 mm',
		'Kuchyňská pracovní deska matná –  Olše, Kód: 685, Struktura: CF, Kód lišty: 685, Tloušťka: 28 a 38 mm',
		'Kuchyňská pracovní deska matná –  Dub světlý, Kód: 781, Struktura: CF, Kód lišty: 781, Tloušťka: 28 a 38 mm',
		'Kuchyňská pracovní deska matná –  Dub tmavý, Kód: 783, Struktura: CF, Kód lišty: 783, Tloušťka: 28 a 38 mm',
		'Kuchyňská pracovní deska matná –  Onyx, Kód: 906, Struktura: PE, Kód lišty: 906, Tloušťka: 28 mm',
		'Kuchyňská pracovní deska matná –  Virginia, Kód: 949, Struktura: BS, Kód lišty: 949, Tloušťka: 28 mm',
		'Kuchyňská pracovní deska matná –  Zrnitá šeď, Kód: 969, Struktura: BS, Kód lišty: 969, Tloušťka: 28 mm',
		'Kuchyňská pracovní deska matná –  Ravenna, Kód: 952, Struktura: PE, Kód lišty: 952, Tloušťka: 28 a 38 mm',
		'Kuchyňská pracovní deska matná –  Mramor, Kód: 990, Struktura: PE, Kód lišty: 990, Tloušťka: 28 a 38 mm',
		'Kuchyňská pracovní deska matná –  Granit, Kód: 994, Struktura: PE, Kód lišty: 994, Tloušťka: 28 a 38 mm',
		'Kuchyňská pracovní deska matná –  Rosa porino modrá, Kód: 996, Struktura: PE, Kód lišty: 996, Tloušťka: 28 mm',
		'Kuchyňská pracovní deska matná –  Kokos bolo, Kód: 8995, Struktura: CF, Kód lišty: 8995, Tloušťka: 28 a 38 mm',
		'Kuchyňská pracovní deska matná –  Granit červený, Kód: 9578, Struktura: PE, Kód lišty: 9578, Tloušťka: 28 mm',
		'Kuchyňská pracovní deska matná –  Wenge Luiziana, Kód: 9763, Struktura: BS, Kód lišty: 9763, Tloušťka: 28 a 38 mm',
		'Kuchyňská pracovní deska matná –  Titan, Kód: 853, Struktura: PE, Kód lišty: 853, Tloušťka: 28 mm',
		'Kuchyňská pracovní deska matná –  Granito Goya, Kód: 6520, Struktura: PE, Kód lišty: 6520, Tloušťka: 28 a 38 mm',
		'Kuchyňská pracovní deska matná –  Ořech butcherblock, Kód: 6521, Struktura: PE, Kód lišty: 6521, Tloušťka: 28 a 38 mm',
		'Kuchyňská pracovní deska matná –  Pískovec, Kód: 6522, Struktura: PA, Kód lišty: 6522, Tloušťka: 28 a 38 mm',
		'Kuchyňská pracovní deska matná –  Alhambra světlá, Kód: 6523, Struktura: PA, Kód lišty: 6523, Tloušťka: 28 a 38 mm',
		'Kuchyňská pracovní deska matná –  Alhambra hnědá, Kód: 6524, Struktura: PA, Kód lišty: 6524, Tloušťka: 28 a 38 mm'
		);
		$nodes = array(
				'Pánve',
				'Hrnce',
				'Kuchyňské nože',
				'Tlakové hrnce',
				'Pečící formy',
				'Kuchyňské potřeby',
				'Potravinové dózy',
				'ZNaběračky'		
						
						);
		                     
		$ctype = 'Overview';
		
		//foreach ($nodes as $title => $ctype){
		foreach ($nodes as $i =>  $title){
			// node 
			$input = new stdClass();
			$input->pageTitle = $title;
				
			$newNode = Node::init('FOLDER', $nodeAddTo, $input, $this->view); 
						
			//content    	  
			$input = new stdClass();	 
			if($html[$i]){
				$input->fck_html = '<p>' . $html[$i] . '</p>';
				$input->dateShow = date('Y-m-d');   
			}  
			$content = Content::init($ctype, $input, $this->acl);	
			//  pr($content); die();
			
			$this->save($newNode, $content);
		}
		$this->tree->save('sysPages');
		die('added'); 
		
	}
	

	function save($newNode, $content){		
		$err2 = $content->save();	 
    	$this->tree->addNode($newNode, false, false);
    	$this->tree->pareNodeAndContent($newNode->nodeId, $content->id, $content->_name);    		    	
    	parent::audit($newNode->title, $newNode->nodeId);
	}
}
