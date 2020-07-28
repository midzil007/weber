<?php
header ( 'Content-Type: text/html; charset=UTF-8' );

 

/**
 *
 * @author midzil
 *        
 */
class module_ImportAstra {
	// předělat do nodeId
	private $_categories = array(
		'CBN' => '53063',
		'SBS' => '53066',
		'GT' => '53069',
		'IK' => '53072',
		'IG' => '53073',
		'IC' => '53071',
		'Wk' => '53077',
		'CT' => '53065',
		'T' => '53062',
		'K' => '53062',
		'C' => '53064',
		'G' => '53068',
		'W' =>'53076',
			);
	
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
	
	public function readPropertyCsv($sk = false, $start = 0, $end = 1)
	{
		// není madlo
		$file = $this->config->dataRoot . '/DATAP.csv';
		if($sk){
			$file = $this->config->dataRoot . '/DATASK.csv';
		}
		$handle = fopen($file,"r");
		$inc = $i = 0;
		while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
			if(count($data)>1 && $i >= $start && $end >= $i){
				$inc = 0;
				foreach ($data as $value)
				{
					$piece[$inc] = explode(';', $value);
					$inc++;
				}
				$item = new stdClass();
				$item->model = $piece[3][0];
				$item->rada = $piece[4][0];
				$item->ean = $piece[6][0];
				//Rozměry (cm)
				$item->rozmer = str_replace(',','.', $piece[74][0]);
				//Čistý obsah celkem (l)
				$item->obsahMraziciCasti = $piece[43][0];
				$item->enerClass = $piece[9][0];
				// Hlučnost (dB)
				$item->hlucnost = $piece[12][0];
				//Čistý obsah mrazicí část (l)
				$item->cistyObsah = $piece[48][0];
				//FrostSafe
				$item->FrostSafe = $piece[120][0];
				//Čistý obsah chladící části celkem (l)
				$item->obsahChladCel = $piece[44][0];
				
				//Spotřeba elektrické energie za 365 dní
				$item->spotrebaEner365 = $piece[11][0];
				//Počet kompresorů
				$item->pocKom = $piece[13][0];
				//Počet chladicích okruhů
				$item->pocChlad = $piece[14][0];
				//Systém chlazení v chladicí části
				$item->chlazeni =  iconv('windows-1250', 'UTF-8', $piece[19][0]);
		
				//Šířka při otevřených dveřích (cm)
				$item->sirkaOtev = str_replace(',','.', $piece[63][0]);
				//Hloubka při otevřených dveřích bez madla (cm)
				$item->hloubkaOtev = $piece[65][0]; ///Hloubka při otevřených dveřích s madlem (cm): 117,6
				//Doba skladování při výpadku proudu (h)
				$item->dobaSklad = $piece[26][0];
				//Zmrazovací kapacita (kg)
				$item->zmrKapalina = $piece[27][0];
				$item->bioFresh = $piece[16][0];
				$item->noFrost = $piece[17][0];
				//SmartFrost-Systém
				$item->smartFrost = $piece[122][0];
				//Výrobník ledových kostek
				$item->vyrobniLedKostek = $piece[232][0];
				//Barevné provedení
				$item->color = iconv('windows-1250', 'UTF-8',$piece[34][0]);
				
				$item->obsahChladiciCasti =$piece[45][0];
	
				
				
				
				//Spotřeba elektrické energie za 24 h
				$item->spotrebaEner = array('text'=> 'Spotřeba elektrické energie za 24 h', iconv('windows-1250', 'UTF-8', $piece[10][0]));
				//Systém mražení
				$item->mrazeni = array('text'=> 'Systém mražení', iconv('windows-1250', 'UTF-8',  $piece[20][0]));
				//Systém chlazení v části na víno
				$item->chlazeniVino = array('text'=> 'Systém chlazení v části na víno', iconv('windows-1250', 'UTF-8',  $piece[21][0]));
				//Počet teplotních zón
				$item->pocTepZon =  array('text'=> 'Počet teplotních zón', 'value'=> iconv('windows-1250', 'UTF-8', $piece[15][0]));
				//Index energetické efektivnosti 2011
				$item->indexEner = array('text'=> 'Index energetické efektivnosti', 'value'=> iconv('windows-1250', 'UTF-8', $piece[8][0]));
				//Způsob odmrazování v chladicí části
				$item->odrCastiCh = array('text'=> 'Způsob odmrazování v chladicí části', 'value'=>  iconv('windows-1250', 'UTF-8',$piece[22][0]));
				//Způsob odmrazování v mrazící části
				$item->odrCastiMr = array('text'=> 'Způsob odmrazování v mrazící části', 'value'=>  iconv('windows-1250', 'UTF-8',$piece[24][0]));
				//Počet hvězdiček mrazící části
				$item->pocHverMr = array('text'=> 'Počet hvězdiček mrazící části', 'value'=>  iconv('windows-1250', 'UTF-8',$piece[28][0]));
				//Barva rámu
				$item->colorRam = array('text'=> 'Barva rámu', 'value'=>  iconv('windows-1250', 'UTF-8',$piece[35][0]));
				//Klimatická třída
				$item->klimClass = array('text'=> 'Klimatická třída', 'value'=> iconv('windows-1250', 'UTF-8', $piece[25][0]));
				//Třída klasifikace
				$item->tridaKlas = array('text'=> 'Třída klasifikace', 'value'=> iconv('windows-1250', 'UTF-8', $piece[7][0]));
				// Obsah brutto (l)
				$item->obsahBrut = array('text'=> 'Obsah brutto (l)', 'value'=> iconv('windows-1250', 'UTF-8', $piece[36][0]));
				
				//
				$item->cisHmotnost = array('text'=> 'Čistá hmotnost (kg)', 'value'=> iconv('windows-1250', 'UTF-8', $piece[86][0]));

				//Čistý obsah chladící části celkem (l)
				$item->obsahChladCel1 = array('text'=> 'Čistý obsah chladící části celkem (l)', 'value'=> iconv('windows-1250', 'UTF-8',$piece[44][0]));
				//z toho chladící část (l)
				$item->obsahChladCast = array('text'=> 'z toho chladící část (l)', 'value'=> iconv('windows-1250', 'UTF-8',$piece[45][0]));
				//z toho BioFresh (l) AU
				$item->obsahBio = array('text'=> 'z toho BioFresh (l)', 'value'=> iconv('windows-1250', 'UTF-8',$piece[46][0]));
				//z toho Winw
				$item->zWine = array('text'=> 'z toho Wine (l)', 'value'=> iconv('windows-1250', 'UTF-8',$piece[47][0]));
				

				//Čistý obsah mrazicí část (l)
				$item->cistyObsah1 = array('text'=> 'Čistý obsah mrazicí část (l)', 'value'=> iconv('windows-1250', 'UTF-8',$piece[48][0]));
				//Čistý obsah Wine (l)
				$item->cistyWine = array('text'=> 'Čistý obsah Wine (l)', 'value'=> iconv('windows-1250', 'UTF-8',$piece[49][0]));
				///Čistý obsah části BioFresh (l)
				$item->cistyBioFresh = array('text'=> 'Čistý obsah části BioFresh (l)', 'value'=> iconv('windows-1250', 'UTF-8',$piece[50][0]));
				
				//Obsah brutto chladicí části celkem (l)
				$item->obsahBrutChlad = array('text'=> 'Obsah brutto chladicí části celkem (l)', 'value'=> iconv('windows-1250', 'UTF-8', $piece[37][0]));
				//z toho chladicí část (l)
				$item->zchladBrutCast = array('text'=> 'z toho chladicí část (l)', 'value'=> iconv('windows-1250', 'UTF-8', $piece[38][0]));
				//z toho BioFresh (l)
				$item->zBrutBioFresh = array('text'=> 'z toho BioFresh (l)', 'value'=> iconv('windows-1250', 'UTF-8', $piece[39][0]));
				//z toho Wine (l)
				$item->zBrutWine = array('text'=> 'z toho Wine (l)', 'value'=> iconv('windows-1250', 'UTF-8', $piece[40][0]));
				
				//Obsah brutto mrazicí část (l)
				$item->brutoMrazCast = array('text'=> 'Obsah brutto mrazicí část (l)', 'value'=> iconv('windows-1250', 'UTF-8',$piece[41][0]));
				//Obsah brutto Wine (l)
				$item->brutoWine = array('text'=> 'Obsah brutto Wine (l)', 'value'=> iconv('windows-1250', 'UTF-8',$piece[42][0]));
				//VarioSpace
				$item->varioSpace = array('text'=> 'VarioSpace', 'value'=> iconv('windows-1250', 'UTF-8',$piece[121][0]));
				
				//Osvětlení
				$item->osvetleni = array('text'=> 'Osvětlení', 'value'=> iconv('windows-1250', 'UTF-8',$piece[151][0]));
				///Osvětlení mrazící části
				$item->osvetLetMrazC = array('text'=> 'Osvětlení mrazící části', 'value'=> iconv('windows-1250', 'UTF-8',$piece[123][0]));
				///Osvětlení chladící části
				$item->OsvetleniChladic =  array('text'=> 'Osvětlení chladící části', 'value'=> iconv('windows-1250', 'UTF-8',$piece[101][0]));
				///Osvětlení části na víno
				$item->osvetleniCastVino = array('text'=> 'Osvětlení části na víno', 'value'=> iconv('windows-1250', 'UTF-8',$piece[142][0]));
				//Osvětlení stmívatelné
				$item->osvetleniSmivat = array('text'=> 'Osvětlení stmívatelné', 'value'=> iconv('windows-1250', 'UTF-8',$piece[143][0]));
				///Možnost trvalého osvětlení
				$item->moznostTrvalehoOsv = array('text'=> 'Možnost trvalého osvětlení', 'value'=> iconv('windows-1250', 'UTF-8',$piece[144][0]));
				///Osvětlení misek na ovoce a zeleninu
				$item->osvetleniMisek = array('text'=> 'Osvětlení misek na ovoce a zeleninu', 'value'=> iconv('windows-1250', 'UTF-8',$piece[106][0]));
				//Provedení dveří
				$item->provDveri = array('text'=> 'Provedení dveří', 'value'=> iconv('windows-1250', 'UTF-8',$piece[31][0]));
				//Provedení boků
				$item->provBoku = array('text'=> 'Provedení boků', 'value'=> iconv('windows-1250', 'UTF-8',$piece[32][0]));
				
				//SuperFrost
				$item->superFrost = array('text'=> 'SuperFrost', 'value'=> iconv('windows-1250', 'UTF-8',$piece[162][0]));
				//DuralFreeze
				$item->duralFreeze = array('text'=> 'DuralFreeze', 'value'=> iconv('windows-1250', 'UTF-8',$piece[163][0]));
				//SuperCool
				$item->superCool = array('text'=> 'SuperCool', 'value'=> iconv('windows-1250', 'UTF-8',$piece[164][0]));
				//CoolPlus
				$item->coolPlus = array('text'=> 'CoolPlus', 'value'=> iconv('windows-1250', 'UTF-8',$piece[165][0]));
				//VarioSafe
				$item->varioSafe = array('text'=> 'VarioSafe', 'value'=> iconv('windows-1250', 'UTF-8',$piece[108][0]));
				//Příkon
				$item->prikon = array('text'=> 'Příkon', 'value'=> iconv('windows-1250', 'UTF-8',$piece[215][0]));
				
				//Počet zásuvek BioFresh
				$item->pocZasBioFresh = array('text'=> 'Počet zásuvek BioFresh', 'value'=> iconv('windows-1250', 'UTF-8',$piece[114][0]));
				//Počet zásuvek v mrazící části
				$item->pocZasMrazC = array('text'=> 'Počet zásuvek v mrazící části', 'value'=> iconv('windows-1250', 'UTF-8',$piece[115][0]));
				///Počet polic v mrazící části
				$item->pocPolicMrazC = array('text'=> 'Počet polic v mrazící části', 'value'=> iconv('windows-1250', 'UTF-8',$piece[118][0]));
				//Dóza na máslo
				$item->dozaNaMaslo =  array('text'=> 'Dóza na máslo', 'value'=> iconv('windows-1250', 'UTF-8',$piece[98][0]));
				//Dóza na vejce
				$item->dozaNaVejce =  array('text'=> 'Dóza na vejce', 'value'=> iconv('windows-1250', 'UTF-8',$piece[99][0]));
				//Miska na byliny a bobule
				$item->miskaBylinyBob = array('text'=> 'Miska na byliny a bobule', 'value'=> iconv('windows-1250', 'UTF-8',$piece[126][0]));
				///Ventilátor
				$item->ventilator =  array('text'=> 'Ventilátor', 'value'=> iconv('windows-1250', 'UTF-8',$piece[100][0]));
			
				//Police na láhve
				$item->policeNaLahve =  array('text'=> 'Police na láhve', 'value'=> iconv('windows-1250', 'UTF-8',$piece[102][0]));
				//Boxy na ovoce a zeleninu
				$item->boxNaOvoce =  array('text'=> 'Boxy na ovoce a zeleninu', 'value'=> iconv('windows-1250', 'UTF-8',$piece[103][0]));
				
				//z toho na pojezdu
				$item->zTohoNaPojezdu = array('text'=> 'z toho na pojezdu', 'value'=> iconv('windows-1250', 'UTF-8',$piece[104][0]));
				//z toho na výjezdech
				$item->zTohoNaVyjezdu = array('text'=> 'z toho na výjezdech', 'value'=> iconv('windows-1250', 'UTF-8',$piece[105][0]));
				//Polička na jižní plody
				$item->polickaNajizni = array('text'=> 'Polička na jižní plody', 'value'=> iconv('windows-1250', 'UTF-8',$piece[107][0]));
				///Police na lahve
				$item->policeNaLahve = array('text'=> 'Police na lahve', 'value'=> iconv('windows-1250', 'UTF-8',$piece[244][0]));
				//Skladovací kapacita lahví 0,2 l sklo
				$item->skladKap02 = array('text'=> 'Skladovací kapacita lahví 0,2 l sklo', 'value'=> iconv('windows-1250', 'UTF-8',$piece[51][0]));
				//Skladovací kapacita lahví 0,33 l plech
				$item->skladKap033 = array('text'=> 'Skladovací kapacita lahví 0,33 l plech', 'value'=> iconv('windows-1250', 'UTF-8',$piece[52][0]));
				///Skladovací kapacita lahví 0,5 l PET
				$item->skladKap05 = array('text'=> 'Skladovací kapacita lahví 0,5 l PET', 'value'=> iconv('windows-1250', 'UTF-8',$piece[53][0]));
				///Skladovací kapacita lahví 1,0 l PET
				$item->skladKap1 = array('text'=> 'Skladovací kapacita lahví 1,0 l PET', 'value'=> iconv('windows-1250', 'UTF-8',$piece[54][0]));
				//Skladovací kapacita lahví 1,5 l PET
				$item->skladKap15 = array('text'=> 'Skladovací kapacita lahví 1,5 l PET', 'value'=> iconv('windows-1250', 'UTF-8',$piece[55][0]));
				
				//Dětská pojistka
				$item->detPojistka = array('text'=> 'Dětská pojistka', 'value'=> iconv('windows-1250', 'UTF-8',$piece[166][0]));
				//Program Prázdniny
				$item->progPraz = array('text'=> 'Program Prázdniny', 'value'=> iconv('windows-1250', 'UTF-8',$piece[167][0]));
				//StopFrost
				$item->stopFrost = array('text'=> 'StopFrost', 'value'=> iconv('windows-1250', 'UTF-8',$piece[168][0]));
				//Umístění pantů
				$item->umisteniPantů = array('text'=> 'Umístění pantů', 'value'=> iconv('windows-1250', 'UTF-8',$piece[221][0]));
				///Typ ovládání
				$item->typOvladani = array('text'=> 'Typ ovládání', 'value'=> iconv('windows-1250', 'UTF-8',$piece[152][0]));
				//Beznapěťový kontakt
				$item->bezNapetovyKontakt = array('text'=> 'Beznapěťový kontakt', 'value'=> iconv('windows-1250', 'UTF-8',$piece[153][0]));
				//Počet poliček
				$item->pocetPolicek = array('text'=> 'Počet poliček', 'value'=> iconv('windows-1250', 'UTF-8',$piece[150][0]));
				//Ukazatel teploty pro:
				$item->ukazatelTeploty = array('text'=> 'Ukazatel teploty pro', 'value'=> iconv('windows-1250', 'UTF-8',$piece[155][0]));
				//Ukazatel teploty chladící části
				$item->ukazatelTeplotyChladCasti = array('text'=> 'Ukazatel teploty chladící části', 'value'=> iconv('windows-1250', 'UTF-8',$piece[156][0]));
				//Ukazatel mrazící části
				$item->ukazatelTeplotyMrazCasti = array('text'=> 'Ukazatel mrazící části', 'value'=> iconv('windows-1250', 'UTF-8',$piece[157][0]));
				//Ukazatel teploty části na víno
				$item->ukazatelTeplotyVinoCasti = array('text'=> 'Ukazatel teploty části na víno', 'value'=> iconv('windows-1250', 'UTF-8',$piece[158][0]));
				//Teplotní rozsah chladicí části
				$item->teplotniRozsahChladCas = array('text'=> 'Teplotní rozsah chladicí části', 'value'=> iconv('windows-1250', 'UTF-8',$piece[159][0]));
				//Teplotní rozsah mrazici části
				$item->teplotniRozsahMrazCas = array('text'=> 'Teplotní rozsah mrazici části', 'value'=> iconv('windows-1250', 'UTF-8',$piece[160][0]));
				//Teplotní rozsah v části na víno
				$item->teplotniRozsahVinoCas = array('text'=> 'Teplotní rozsah v části na víno', 'value'=> iconv('windows-1250', 'UTF-8',$piece[161][0]));
				//Délka přívodního kabelu cm
				$item->delkaPrivodnihoKab = array('text'=> 'Délka přívodního kabelu cm', 'value'=> iconv('windows-1250', 'UTF-8',$piece[218][0]));
				///Volně stojící/podstolové
				If(iconv('windows-1250', 'UTF-8',$piece[219][0]) != '###NEPŘELOŽENO###'){
				$item->volneStojici = array('text'=> 'Volně stojící/podstolové', 'value'=> iconv('windows-1250', 'UTF-8',$piece[219][0]));
				}
				///Kolečka
				$item->kolecka = array('text'=> 'Kolečka', 'value'=> iconv('windows-1250', 'UTF-8',$piece[197][0]));
				///Transportní kolečka vzadu
				$item->transKoleckaVzad = array('text'=> 'Transportní kolečka vzadu', 'value'=> iconv('windows-1250', 'UTF-8',$piece[198][0]));
				///Transportní držadla vzadu
				$item->transDrzadlaVzad = array('text'=> 'Transportní držadla vzadu', 'value'=> iconv('windows-1250', 'UTF-8',$piece[199][0]));
		
				///Zámek
				$item->zamek = array('text'=> 'Zámek', 'value'=> iconv('windows-1250', 'UTF-8',$piece[201][0]));
				//Odtok vody
				$item->odtokVody = array('text'=> 'Odtok vody', 'value'=> iconv('windows-1250', 'UTF-8',$piece[202][0]));
				///Samozavírací dveře
				$item->samozaviraciDvere = array('text'=> 'Samozavírací dveře', 'value'=> iconv('windows-1250', 'UTF-8',$piece[203][0]));
				///Vario Sockel
				$item->varioSockel = array('text'=> 'Vario Sockel', 'value'=> iconv('windows-1250', 'UTF-8',$piece[204][0]));
				//Zásuvky BioFresh na teleskopických výjezdech
				$item->zasuvkyBioFreshTele = array('text'=> 'Zásuvky BioFresh na teleskopických výjezdech', 'value'=> iconv('windows-1250', 'UTF-8',$piece[109][0]));
				//z toho DrySafe
				$item->zTohoNaDrySafe = array('text'=> 'z toho DrySafe', 'value'=> iconv('windows-1250', 'UTF-8',$piece[110][0]));
				//z toho HydroSafe
				$item->zTohoNaHydroSafe = array('text'=> 'z toho HydroSafe', 'value'=> iconv('windows-1250', 'UTF-8',$piece[111][0]));
				//Zás.BioFresh plnovýsuv, tlumení dojezdu zasouvání
				$item->zasBioFreshTlum = array('text'=> 'Zás.BioFresh plnovýsuv, tlumení dojezdu zasouvání', 'value'=> iconv('windows-1250', 'UTF-8',$piece[112][0]));
				//Vnitřní osvětlení části BioFresh
				$item->vnitrOsvelBio = array('text'=> 'Vnitřní osvětlení části BioFresh', 'value'=> iconv('windows-1250', 'UTF-8',$piece[113][0]));
				//Tlumené uzavírání
				$item->tlumeneUzavirani = array('text'=> 'Tlumené uzavírání', 'value'=> iconv('windows-1250', 'UTF-8',$piece[205][0]));
				//Úhel otevření dveří
				$item->uhelOtevreni = array('text'=> 'Úhel otevření dveří', 'value'=> iconv('windows-1250', 'UTF-8',$piece[206][0]));
				//Kapacita ledových kostek
				$item->kapacitaLedKost = array('text'=> 'Kapacita ledových kostek', 'value'=> iconv('windows-1250', 'UTF-8',$piece[133][0]));
				//Maximální zásoba ledových kostek (kg)
				
				$item->maxZasLedKost = array('text'=> 'Maximální zásoba ledových kostek (kg)', 'value'=> iconv('windows-1250', 'UTF-8',$piece[134][0]));
				
				///Počet sáčků s ledem
				$item->pocSacLed = array('text'=> 'Počet sáčků s ledem', 'value'=> iconv('windows-1250', 'UTF-8',$piece[125][0]));
				//IceCenter
				$item->iceCenter = array('text'=> 'IceCenter', 'value'=> iconv('windows-1250', 'UTF-8',$piece[230][0]));
				//Výrobník ledové tříště
				$item->vyrobniLedove = array('text'=> 'Výrobník ledové tříště', 'value'=> iconv('windows-1250', 'UTF-8',$piece[231][0]));
				//Způsob ovládání
				$item->zpusobOvladani = array('text'=> 'Způsob ovládání', 'value'=> iconv('windows-1250', 'UTF-8',$piece[233][0]));
				//Nastavitelná teplota části BioFresh
				$item->nastavitelnaTeplotaCBioFresh = array('text'=> 'Nastavitelná teplota části BioFresh', 'value'=> iconv('windows-1250', 'UTF-8',$piece[234][0]));
				///Umístění ovládání
				$item->umisteniOvladani = array('text'=> 'Umístění ovládání', 'value'=> iconv('windows-1250', 'UTF-8',$piece[235][0]));
				///Vodní filtr
				$item->vodniFilr = array('text'=> 'Vodní filtr', 'value'=> iconv('windows-1250', 'UTF-8',$piece[236][0]));
				//Zásobník na vodu
				$item->zasobnikVodu = array('text'=> 'Zásobník na vodu', 'value'=> iconv('windows-1250', 'UTF-8',$piece[237][0]));
				//Odvětrání
				$item->odvetrani = array('text'=> 'Odvětrání', 'value'=> iconv('windows-1250', 'UTF-8',$piece[207][0]));
				///Odvětrání zepředu, zasunutí těsně ke stěně
				$item->odvetraniZepredu = array('text'=> 'Odvětrání zepředu, zasunutí těsně ke stěně', 'value'=> iconv('windows-1250', 'UTF-8',$piece[200][0]));
				//otevírací nožní pedál
				$item->oteviraciNozniPedal = array('text'=> 'Otevírací nožní pedál', 'value'=> iconv('windows-1250', 'UTF-8',$piece[208][0]));
				//Vytápění rámu dveří
				$item->vytapeniRamuDveri = array('text'=> 'Vytápění rámu dveří', 'value'=> iconv('windows-1250', 'UTF-8',$piece[209][0]));
				//Vytápění v boční stěně
				$item->vytapeniBocniStene= array('text'=> 'Vytápění v boční stěně', 'value'=> iconv('windows-1250', 'UTF-8',$piece[210][0]));
				//Bezpečnostní termostat
				$item->bezpecnostiTermostat = array('text'=> 'Bezpečnostní termostat', 'value'=> iconv('windows-1250', 'UTF-8',$piece[211][0]));
				///zásuvka na pomůcky
				$item->zasuvkaNaPomucky = array('text'=> 'Zásuvka na pomůcky', 'value'=> iconv('windows-1250', 'UTF-8',$piece[212][0]));
				//SmartSteel
				$item->smartSteel = array('text'=> 'SmartSteel', 'value'=> iconv('windows-1250', 'UTF-8',$piece[213][0]));
				///Vyrovnávací lyžiny
				$item->vyrovnaciLyz = array('text'=> 'Vyrovnávací lyžiny', 'value'=> iconv('windows-1250', 'UTF-8',$piece[214][0]));
			
				//Frekvence
				$item->frekvence = array('text'=> 'Frekvence', 'value'=> iconv('windows-1250', 'UTF-8',$piece[216][0]));
				//Napětí
				$item->napeti = array('text'=> 'Napětí', 'value'=> iconv('windows-1250', 'UTF-8',$piece[217][0]));
	
				///Dekorační rám
				$item->dekoracniRam = array('text'=> 'Dekorační rám', 'value'=> iconv('windows-1250', 'UTF-8',$piece[220][0]));
				
				//Způsob montáže dveří
				$item->zpusobMontazDver = array('text'=> 'Způsob montáže dveří', 'value'=> iconv('windows-1250', 'UTF-8',$piece[222][0]));
				//Výška mrazicí zásuvky
				$item->vyskaMrazZas = array('text'=> 'Výška mrazicí zásuvky', 'value'=> iconv('windows-1250', 'UTF-8',$piece[127][0]));
				//Počet košů standardně
				$item->pocKosStand = array('text'=> 'Počet košů standardně', 'value'=> iconv('windows-1250', 'UTF-8',$piece[128][0]));
				//Maximální počet košů
				$item->maxPocKos = array('text'=> 'Maximální počet košů', 'value'=> iconv('windows-1250', 'UTF-8',$piece[129][0]));
				///Zmrazovací podnos jako dělící stěna
				$item->zmrPodnos = array('text'=> 'Zmrazovací podnos jako dělící stěna', 'value'=> iconv('windows-1250', 'UTF-8',$piece[130][0]));
				//Zmrazovací podnos jako příslušenství
				$item->zmrPodnosPris = array('text'=> 'Zmrazovací podnos jako příslušenství', 'value'=> iconv('windows-1250', 'UTF-8',$piece[131][0]));
				//Způsob připojení IceMaker
				$item->zpusPripojeniIceMaker = array('text'=> 'Způsob připojení IceMaker', 'value'=> iconv('windows-1250', 'UTF-8',$piece[132][0]));
				
				///Nastavení teploty mrazící části
				$item->nastaveniTeplotMraz = array('text'=> 'Nastavení teploty mrazící části', 'value'=> iconv('windows-1250', 'UTF-8',$piece[177][0]));
				///Nastavení teploty mraznicky a chladnicky nezávisle
				$item->nastaveniTeplotMrazAChladNez = array('text'=> 'Nastavení teploty mraznicky a chladnicky nezávisle', 'value'=> iconv('windows-1250', 'UTF-8',$piece[178][0]));
				//Nastavení mrazničky společně s chladící části
				$item->nastaveniMraznickySpolAChlad = array('text'=> 'Nastavení mrazničky společně s chladící části', 'value'=> iconv('windows-1250', 'UTF-8',$piece[179][0]));
				//Ukazatel provozu chladničky
				$item->ukazatelProvozu = array('text'=> 'Ukazatel provozu chladničky', 'value'=> iconv('windows-1250', 'UTF-8',$piece[180][0]));
				//Ukazatel provozu mrazničky
				$item->ukazatelProvozu = array('text'=> 'Ukazatel provozu mrazničky', 'value'=> iconv('windows-1250', 'UTF-8',$piece[181][0]));
				//Signalizace SuperCool
				$item->signalizaceSuperCool = array('text'=> 'Signalizace SuperCool', 'value'=> iconv('windows-1250', 'UTF-8',$piece[182][0]));
				///
				$item->signalizaceSuperFrost = array('text'=> 'Signalizace SuperFrost', 'value'=> iconv('windows-1250', 'UTF-8',$piece[183][0]));
				//Signalizace Frost
				$item->signalizaceFrost = array('text'=> 'Signalizace Frost', 'value'=> iconv('windows-1250', 'UTF-8',$piece[184][0]));
				//Signalizace  ventilátoru
				$item->signalizaceVentilarotu = array('text'=> 'Signalizace  ventilátoru', 'value'=> iconv('windows-1250', 'UTF-8',$piece[185][0]));
				///Varovný signál otevřených dveří chladničky
				$item->signalOtevDverChlas = array('text'=> 'Varovný signál otevřených dveří chladničky', 'value'=> iconv('windows-1250', 'UTF-8',$piece[186][0]));
				//Varovný signál otevřených dveří  mrazničky
				$item->signalOtevDverMraz = array('text'=> 'Varovný signál otevřených dveří  mrazničky', 'value'=> iconv('windows-1250', 'UTF-8',$piece[187][0]));
				//Varovný signál otevřených dveří  části na víno
				$item->signalOtevDverVino = array('text'=> 'Varovný signál otevřených dveří  části na víno', 'value'=> iconv('windows-1250', 'UTF-8',$piece[188][0]));
				//Varovný signál otevřených dveří  části na vín
				$item->signalOtevDverVino = array('text'=> 'Varovný signál otevřených dveří  části na vín', 'value'=> iconv('windows-1250', 'UTF-8',$piece[189][0]));
				//Výstražný signál při otevřených dveřích
				$item->signalOtevDver = array('text'=> 'Výstražný signál při otevřených dveřích', 'value'=> iconv('windows-1250', 'UTF-8',$piece[190][0]));
				//Design
				$item->design = array('text'=> 'Design', 'value'=> iconv('windows-1250', 'UTF-8',$piece[191][0]));
				//Rukojeť
				$item->rukojet = array('text'=> 'Rukojeť', 'value'=> iconv('windows-1250', 'UTF-8',$piece[192][0]));
				//Výškově stavitelné nohy
				$item->vyskoveStavitelneNohy = array('text'=> 'Výškově stavitelné nohy', 'value'=> iconv('windows-1250', 'UTF-8',$piece[193][0]));
				//Barevné provedení stěn
				$item->barevneProvedeniSten = array('text'=> 'Materiál poliček', 'value'=> iconv('windows-1250', 'UTF-8',$piece[227][0]));
				///Dveřní Vario Boxy
				$item->dverniVarioBox = array('text'=> 'Dveřní Vario Boxy', 'value'=> iconv('windows-1250', 'UTF-8',$piece[245][0]));
				//Počet polic v lednici
				$item->pocetPolic = array('text'=> 'Počet polic v lednici', 'value'=> iconv('windows-1250', 'UTF-8',$piece[87][0]));
				//Počet nastavitelných polic
				$item->pocetNasPolic = array('text'=> 'Počet nastavitelných polic', 'value'=> iconv('windows-1250', 'UTF-8',$piece[88][0]));
				///Počet pevných polic
				$item->pocetPevPolic = array('text'=> 'Počet pevných polic', 'value'=> iconv('windows-1250', 'UTF-8',$piece[89][0]));
				//z toho mohou být použity na láhve
				///Dveřní police na máslo a sýry s krytem
				$item->dverniPoliceNaMaslo = array('text'=> 'Dveřní police na máslo a sýry s krytem', 'value'=> iconv('windows-1250', 'UTF-8',$piece[246][0]));
				//Dveřní police na lahve
				$item->dverniPoliceNaLahve = array('text'=> 'Dveřní police na lahve', 'value'=> iconv('windows-1250', 'UTF-8',$piece[247][0]));
				////Dveřní police na konzervy
				$item->dverniPoliceNaKonzervy = array('text'=> 'Dveřní police na konzervy', 'value'=> iconv('windows-1250', 'UTF-8',$piece[248][0]));
				///Dveřní police na konzervy dělené
				$item->dverniPoliceNaKonzervyDelene = array('text'=> 'Dveřní police na konzervy dělené', 'value'=> iconv('windows-1250', 'UTF-8',$piece[249][0]));
				//Počet polic na uskladnění vína
				$item->pocPolicVina = array('text'=> 'Počet polic na uskladnění vína', 'value'=> iconv('windows-1250', 'UTF-8',$piece[135][0]));
				//Dřevěné poličky na teleskopických výsuvech
				$item->drevenePolicky = array('text'=> 'Dřevěné poličky na teleskopických výsuvech', 'value'=> iconv('windows-1250', 'UTF-8',$piece[136][0]));
				//Počet sklopných dřevěných polic
				$item->pocSklopnychDrevPolic = array('text'=> 'Počet sklopných dřevěných polic', 'value'=> iconv('windows-1250', 'UTF-8',$piece[137][0]));
				//- z toho částečně sklopné
				$item->zTohoCastecneSklopne = array('text'=> 'z toho částečně sklopné', 'value'=> iconv('windows-1250', 'UTF-8',$piece[138][0]));
				//Materiál poliček části na vino
				$item->marerialPolicekCast = array('text'=> 'Materiál poliček části na vino', 'value'=> iconv('windows-1250', 'UTF-8',$piece[140][0]));
				//Výškově nastavitelné poličky v části na víno
				$item->vyskoveNastavitelnéPolicky = array('text'=> 'Výškově nastavitelné poličky v části na víno', 'value'=> iconv('windows-1250', 'UTF-8',$piece[141][0]));
				
				//Výška bez pracovní desky (cm)
				$item->vyskaBezPrac = array('text'=> 'Výška bez pracovní desky (cm)', 'value'=> iconv('windows-1250', 'UTF-8',$piece[59][0]));
				///Výška při otevřeném víku (cm)
				$item->vyskaPriOtevViku = array('text'=> 'Výška při otevřeném víku (cm)', 'value'=> iconv('windows-1250', 'UTF-8',$piece[61][0]));
				
				//Hloubka včetně odstupu od zdi (cm)
				$item->hloubkaOdtup = array('text'=> 'Hloubka včetně odstupu od zdi (cm)', 'value'=> iconv('windows-1250', 'UTF-8',$piece[66][0]));
				//Čistá hloubka interiéru (cm)
				$item->hloubkaCista = array('text'=> 'Čistá hloubka interiéru (cm)', 'value'=> iconv('windows-1250', 'UTF-8',$piece[69][0]));
				//Čistá šířka interiéru (cm)
				$item->sirka = array('text'=> 'Čistá šířka interiéru (cm)', 'value'=> iconv('windows-1250', 'UTF-8',$piece[70][0]));
				//Čistá výška interiéru (cm)
				$item->vyskaCista = array('text'=> 'Čistá výška interiéru (cm)', 'value'=> iconv('windows-1250', 'UTF-8',$piece[71][0]));
				//Hloubka polic
				$item->hloubkaPolic = array('text'=> 'Hloubka polic', 'value'=> iconv('windows-1250', 'UTF-8',$piece[72][0]));
				//Šířka vnitřní části
				$item->sirkaVnit = array('text'=> 'Šířka vnitřní části', 'value'=> iconv('windows-1250', 'UTF-8',$piece[73][0]));
				
				//rozmer obalu
				$item->rozmerObalu = array('text'=> 'Rozměr obalu', 'value'=> iconv('windows-1250', 'UTF-8',$piece[78][0]));
				//Rozměry výklenku
				$item->rozmerVyklenku =array('text'=> 'Rozměry výklenku', 'value'=> iconv('windows-1250', 'UTF-8', $piece[83][0]));
				//Doporučená výška
				$item->doporucVys = array('text'=> 'Doporučená výška', 'value'=> iconv('windows-1250', 'UTF-8',$piece[84][0]));
				//čistá bruto hmotnost
				$item->brutHmotnost = array('text'=> 'Čistá bruto hmotnost', 'value'=> iconv('windows-1250', 'UTF-8',$piece[85][0]));
				///Filtr s aktivním uhlím
				$item->filtrSAktivnimUh = array('text'=> 'Filtr s aktivním uhlím', 'value'=> iconv('windows-1250', 'UTF-8',$piece[146][0]));
				///Lávový kámen
				$item->lavovyKamen = array('text'=> 'Lávový kámen', 'value'=> iconv('windows-1250', 'UTF-8',$piece[147][0]));
				//Montáž na zeď
				$item->montazNaZed = array('text'=> 'Montáž na zeď', 'value'=> iconv('windows-1250', 'UTF-8',$piece[148][0]));
				
				///Akumulátor chladu
				$item->akumChladu = array('text'=> 'Akumulátor chladu', 'value'=> iconv('windows-1250', 'UTF-8',$piece[124][0]));
				//Net@Home
				$item->netHome = array('text'=> 'Net@Home', 'value'=> iconv('windows-1250', 'UTF-8',$piece[169][0]));
				//HomeDialog
				$item->homeDialog = array('text'=> 'HomeDialog', 'value'=> iconv('windows-1250', 'UTF-8',$piece[170][0]));
				//SmartGrid-ready
				$item->smartGridReady = array('text'=> 'SmartGrid-ready', 'value'=> iconv('windows-1250', 'UTF-8',$piece[171][0]));
				///Maximální počet lahví Bordeaux 0,75 l
				$item->moxPocLahvi = array('text'=> 'Maximální počet lahví Bordeaux 0,75 l', 'value'=> iconv('windows-1250', 'UTF-8',$piece[145][0]));
				//Způsob odmrazování chladničky na víno
				$item->zpusobOdmrazovaniChladVino = array('text'=> 'Způsob odmrazování chladničky na víno', 'value'=> iconv('windows-1250', 'UTF-8',$piece[251][0]));
				///Počet výsuvných zásuvek
				$item->pocetVysuvnychZas = array('text'=> 'Počet výsuvných zásuvek', 'value'=> iconv('windows-1250', 'UTF-8',$piece[252][0]));
				///Z toho s tlumeným a automatickým dojezdem
				$item->zTohoSTlumenymAutDoj = array('text'=> 'Z toho s tlumeným a automatickým dojezdem', 'value'=> iconv('windows-1250', 'UTF-8',$piece[253][0]));
				//HomeDialogSystem
				$item->homeDialogSystem = array('text'=> 'HomeDialogSystem', 'value'=> iconv('windows-1250', 'UTF-8',$piece[254][0]));
				//Zásuvka na příslušenství
				$item->zasuvkaPris = array('text'=> 'Zásuvka na příslušenství', 'value'=> iconv('windows-1250', 'UTF-8',$piece[255][0]));
				//Zap/Vyp chlazení
				$item->zapVypChlaz = array('text'=> 'Zap/Vyp chlazení', 'value'=> iconv('windows-1250', 'UTF-8',$piece[172][0]));
				///Zap/Vyp spotřebič
				$item->zapVypSpot = array('text'=> 'Zap/Vyp spotřebič', 'value'=> iconv('windows-1250', 'UTF-8',$piece[173][0]));
				///Zap/Vyp BioFresh
				$item->zapVypBioFresh = array('text'=> 'Zap/Vyp BioFresh', 'value'=> iconv('windows-1250', 'UTF-8',$piece[174][0]));
				//Zap/Vyp výrobník ledových kostek
				$item->vyrobnikLedKost = array('text'=> 'Zap/Vyp výrobník ledových kostek', 'value'=> iconv('windows-1250', 'UTF-8',$piece[175][0]));
				//Zap/Vyp části na víno
				$item->zapVypCastiVino = array('text'=> 'Zap/Vyp části na víno', 'value'=> iconv('windows-1250', 'UTF-8',$piece[176][0]));
				//izolace
				$item->izolace = array('text'=> 'Izolace', 'value'=> iconv('windows-1250', 'UTF-8',$piece[67][0]));
				$item->pocetPevPouzPolic = array('text'=> 'z toho mohou být použity na láhve', 'value'=> iconv('windows-1250', 'UTF-8',$piece[90][0]));
				///Spodní drátěná police
				$item->spodniDratePol = array('text'=> 'Spodní drátěná police	', 'value'=> iconv('windows-1250', 'UTF-8',$piece[91][0]));
				//Dveřní police
				$item->dverniPol = array('text'=> 'Dveřní police', 'value'=> iconv('windows-1250', 'UTF-8',$piece[92][0]));
				//z toho polička na mléčné výrobky s otočným víkem
				$item->polMlecneVyr = array('text'=> 'z toho polička na mléčné výrobky s otočným víkem', 'value'=> iconv('windows-1250', 'UTF-8',$piece[93][0]));
				//Druh dveřní police
				$item->druhDverPol = array('text'=> 'Druh dveřní police', 'value'=> iconv('windows-1250', 'UTF-8',$piece[94][0]));
				//Nosnost polic v chladicí části
				$item->nostonostPolicChlad = array('text'=> 'Nosnost polic v chladicí části', 'value'=> iconv('windows-1250', 'UTF-8',$piece[96][0]));
				//Matné sklo s nerezovým lemem
				$item->matneSkloSnerezovymLemem = array('text'=> 'Matné sklo s nerezovým lemem', 'value'=> iconv('windows-1250', 'UTF-8',$piece[238][0]));
				//Matné sklo s plastovým lemem
				$item->matneSkloPlastovymLemem = array('text'=> 'Matné sklo s plastovým lemem', 'value'=> iconv('windows-1250', 'UTF-8',$piece[239][0]));
				///Průhledné sklo s nerezovým lemem
				$item->pruhledneSkloSNerezLemem = array('text'=> 'Průhledné sklo s nerezovým lemem', 'value'=> iconv('windows-1250', 'UTF-8',$piece[241][0]));
				///Průhledné sklo s plastovým lemem
				$item->pruhledneSkloSPlastLemem = array('text'=> 'Průhledné sklo s plastovým lemem', 'value'=> iconv('windows-1250', 'UTF-8',$piece[242][0]));
				///Průhledné sklo bez lemu
				$item->pruhledneSkloSBezLemu = array('text'=> 'Průhledné sklo bez lemu', 'value'=> iconv('windows-1250', 'UTF-8',$piece[243][0]));
				//Rozhraní
				$item->rozhrani = array('text'=> 'Rozhraní', 'value'=> iconv('windows-1250', 'UTF-8',$piece[154][0]));
				//Chladivo
				$item->chladivo = array('text'=> 'Chladivo', 'value'=> iconv('windows-1250', 'UTF-8',$piece[29][0]));
				//gastronorma
				$item->gastronorma = array('text'=> 'Gastronorma', 'value'=> iconv('windows-1250', 'UTF-8', $piece[18][0]));
				//Okolní teplota
				$item->okolTeplota = array('text'=> 'Okolní teplota', 'value'=> iconv('windows-1250', 'UTF-8',$piece[30][0]));
				
				//Materiál odkládacích ploch mrazící části
				$item->materialOdkladPloch = array('text'=> 'Materiál odkládacích ploch mrazící části', 'value'=> iconv('windows-1250', 'UTF-8',$piece[119][0]));
				//Materiál vnitřních stěn
				$item->materialVnit = array('text'=> 'Materiál vnitřních stěn', 'value'=> iconv('windows-1250', 'UTF-8',$piece[33][0]));
				///Materiál dveřních polic
				$item->materialDverniPolic = array('text'=> 'Materiál dveřních polic', 'value'=> iconv('windows-1250', 'UTF-8',$piece[97][0]));
				//Materiál polic v chladící části
				$item->materialPolicChlad = array('text'=> 'Materiál polic v chladící části', 'value'=> iconv('windows-1250', 'UTF-8',$piece[95][0]));
				//Mateeriál poliček
				$item->materialPolicek = array('text'=> 'Materiál poliček', 'value'=> iconv('windows-1250', 'UTF-8',$piece[149][0]));
				///Materiál nožiček
				$item->materialNozicek = array('text'=> 'Materiál nožiček', 'value'=> iconv('windows-1250', 'UTF-8',$piece[194][0]));
				///Výška nožiček
				$item->vyskaNozicek = array('text'=> 'Výška nožiček', 'value'=> iconv('windows-1250', 'UTF-8',$piece[195][0]));

			///tady pokracovat		
				$product[] = $item;
				if($inc == 20){
					break;
				}
				$inc++;
			}
			$i++;
		}
		return $product;
	}
	
	public function readCsv($start = 0, $end = 5)
	{
		$file = $this->config->dataRoot . '/lie.csv';
		$handle = fopen($file,"r");
		$data = fgetcsv($handle,1000,",","'");
		$inc = $i = 0;
		while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
			if(count($data)>1 && $i >= $start && $end >= $i){
				$inc = 0;
				foreach ($data as $value)
				{
					$piece[$inc] = explode(';', $value);
					$inc++;
				}
				$item = new stdClass();
				$item->idExt = $piece[0][0];
				$item->VyrId = $piece[1][0];
				$item->title = $piece[2][0];
				$item->title2 = $piece[3][0];
				$item->descr = $piece[4][0];
				$item->price = $piece[6][0];
				$item->price2 = $piece[5][0];
				$item->obrazky[] = 'http://www.shop-liebherr.cz/fotky19418/fotos/'.$piece[7][0];
				$item->files['http://www.shop-liebherr.cz/fotky19418/fotov/'.$piece[9][0]] = $piece[10][0];
				$item->color = $piece[13][0];
				$item->BioFresh = $piece[14][0];
				$item->enerClass = $piece[15][0];
				$item->obrazky[] = $piece[16][0]?'http://www.shop-liebherr.cz/fotky19418/fotos/'.$piece[16][0]:'';
				$item->obrazky[] = $piece[17][0]?'http://www.shop-liebherr.cz/fotky19418/fotos/'.$piece[17][0]:'';
				$item->obrazky[] = $piece[18][0]?'http://www.shop-liebherr.cz/fotky19418/fotos/'.$piece[18][0]:'';
				$item->obrazky[] = $piece[20][0]?'http://www.shop-liebherr.cz/fotky19418/fotos/'.$piece[20][0]:'';
				$item->obrazky[] = $piece[21][0]? 'http://www.shop-liebherr.cz/fotky19418/fotos/'.$piece[21][0]:'';
				$item->ean = $piece[35][0];
				$item->weight = $piece[37][0];
				$product[] = $item;
				if($inc == 20){
					break;
				}
				$inc++;
			}
			$i++;
		}
		return $product;
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
			
				
			$dataGrouped = $data = $this->readPropertyCsv(false,15,20);
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
			$this->setDostupnost($data);
			die ();
		}
		
		
		
		// dokoukat proč néé všechny
		if ($price) {
			$dataGrouped = $this->loadXMLPrice();
			foreach ($dataGrouped as $v)
			{
				$id = $this->db->fetchOne("select id_Product from module_eshop_variants where ean = ?",$v->EANCode);
				if(!$id)
				{
					$temp = explode(' ', $v->Subgroup);
					$model = $temp[0].' '.$temp[1];
					$id = $this->isExist($model);
				}
				$isNeauto =  round($v->UnitPriceIncludingVAT/($v->priceWVAT/100),2);
				if($id){
					$data = array();
					if($v->PricelistCategory == 'neautorizovanř model')
					{
						$v->priceWVAT = round($v->priceWVAT*0.9);
					}
					elseif('83.34' == $isNeauto){
						$v->priceWVAT = round($v->priceWVAT*0.9);
					}
					if($v->ActionPriceInclVAT>0)
					{
						$data['price'] = $v->ActionPriceInclVAT;
					}
					else{
						$data['price'] = $v->priceWVAT;
					}
					$data['priceNakup'] = $v->UnitPriceIncludingVAT;
						
				$where = $this->db->quoteInto ( 'id_Product = '.$id );
					$this->db->update('module_eshop_variants',$data,$where);
				}
			}
			die ();
		}
	}
	
	private function setDostupnost($data)
	{
		$datab['skladem'] = '0';
		$this->db->update('module_eshop_variants',$datab);
		
		foreach ($data as $v)
		{
			$contenId = $this->isExistByEan($v->EAN);
			if(!$contenId)
			{
				$temp = explode(' ', $v->Subgroup);
				$model = $temp[0].' '.$temp[1];
				$contenId = $this->isExist($model);
			}
			if($contenId){
				$datab['skladem'] =  1;
				$realDost = implode(';', $v->AvailabilityAtDate);
						
				$datab['realDost'] =  $realDost;
				pr($datab);
				$where = $this->db->quoteInto ( 'id_product = '.$contenId );
				$this->db->update($this->_tableVariants,$datab,$where);
				}
			else{
				/// napsat email? ulozit do table a pak poslat email?
			}
		}
	}
	
	private function isExistByEan($ean)
	{
		return $this->db->fetchOne("select id_product from module_eshop_variants where  ean = ?",$ean);
	}
	
	function addProducts($dataGrouped,$onlyUpdate) {

		foreach ( $dataGrouped as $variants ) {
			$idContent = $this->isExist ( trim($variants->model));
			$temp = explode('-', trim($variants->model));
			$secIdContent = $this->isExist ($temp[0]);
			if($variants->ean){
				$idContentEan = $this->isExistByEan($variants->ean);
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
		if(is_numeric(strpos($html2, 'Spotřeba elektrické energie za 365 dní:')))
		{
			$tt = explode('<li>Spotřeba elektrické energie za 365 dní:', $html2);
			$ttt = explode('</li>',$tt[1]);
			$values->spotrebaEl = str_replace('"', '', preg_replace('/[^0-9]/i','', $ttt[0]));
		}
		return $values;
	}
	

	
	private function addNewSimple($variant) {
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
			$text[] = 'Spotřeba elektrické energie za 365 dní: '.$data->spotrebaEner365;
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
			$server_file = 'CenikK004490_004.xml';
		}
		else{
			$local_file = $this->config->dataRoot.'/Dostupnost.xml';
			$server_file = 'DostupnostK004490_004.xml';
		}
		
		
		// set up basic connection
		$conn_id = ftp_connect('e-k004490-004.mctb2b.cz' );
		
		// login with username and password
		$login_result = ftp_login($conn_id, 'e-k004490-004_mctb2b_cz', 'VF4R3M2L');
		
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
		
		$this->view->requestParams = $this->view->request->getParams ();
		
		$this->saveData ( $this->view, $newNode, $content,$variant);
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