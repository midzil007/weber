<?

/**
 * Třída reprentuje export do Helios Orange
 */
class module_Eshop_Helios {
	
	
	public function __construct($view = null) {
		$this->db = Zend_Registry::getInstance()->db;	
		$this->tree = Zend_Registry::getInstance()->tree;	
		$this->server = 'linux.celimed.cz';
		$this->username = 'new20web08';
		$this->pass = 'f2x4ygr0htz8q45JKGT';
		$this->dbname = 'Helios002';
	}
	
	function addOrg($hc, $MU1, $CisloOrg, $nazev, $nazev2, $userdata, $prijemnce1, $CisloOrgNad = '', $useDelivery = 0){
		
		$nazev = iconv("UTF-8", "Windows-1250", $nazev);
		$nazev2 = iconv("UTF-8", "Windows-1250", $nazev2);
		$userdata['city2'] = iconv("UTF-8", "Windows-1250", $userdata['city2']);
		$userdata['city'] = iconv("UTF-8", "Windows-1250", $userdata['city']);
		$userdata['street'] = iconv("UTF-8", "Windows-1250", $userdata['street']);
		$userdata['street2'] = iconv("UTF-8", "Windows-1250", $userdata['street2']);
		$userdata['nazev2'] = iconv("UTF-8", "Windows-1250", $userdata['nazev2']);
		
		if($useDelivery){
			$fakturacni = 0;
		} else {
			$fakturacni = 1;
		}
		
		if($useDelivery){
			$insert = "
				INSERT INTO TabUniImportOrg 
				(
					CisloOrg, MU, Nazev, DruhyNazev, IdZeme, Misto, Ulice, PSC, ICO, DIC, JeOdberatel, Fakturacni, Prijemce, Telefon, Email, Poznamka, InterniVerze
				)
				VALUES 
				('" . $CisloOrg . "','" . $MU1 . "','" . $userdata['nazev2'] . "', '" . $nazev2 . "', '" . $userdata['country2']  . "', '" . $userdata['city2']  . "', '" . $userdata['street2']  . "', '" . $userdata['zip2']  . "', '" . $userdata['ico']  . "', 
				'" . $userdata['dic']  . "', 1, $fakturacni, '" . $prijemnce1  . "', '" . $userdata['tel']  . "', 
				'" . $userdata['email']  . "', '" . $CisloOrgNad  . "', 1)
			";
		} else {
			$insert = "
				INSERT INTO TabUniImportOrg 
				(
					CisloOrg, MU, Nazev, DruhyNazev, IdZeme, Misto, Ulice, PSC, ICO, DIC, JeOdberatel, Fakturacni, Prijemce, Telefon, Email, Poznamka, InterniVerze
				)
				VALUES 
				('" . $CisloOrg . "','" . $MU1 . "','" . $nazev . "', '" . $nazev2 . "', '" . $userdata['country']  . "', '" . $userdata['city']  . "', '" . $userdata['street']  . "', '" . $userdata['zip']  . "', '" . $userdata['ico']  . "', 
				'" . $userdata['dic']  . "', 1, $fakturacni, '" . $prijemnce1  . "', '" . $userdata['tel']  . "', 
				'" . $userdata['email']  . "', '" . $CisloOrgNad  . "', 1)
			";
		}
		
		Utils::debug($insert);
		try {
			$r = mssql_query($insert);
		} catch (Exception $e) {
			Utils::debug($e->getMessage());
	    }
		
	}
	
	function addExistsOrg($hc, $CisloOrg){
		$insert = "
			INSERT INTO TabUniImportOrg 
			(
				CisloOrg
			)
			VALUES 
			('" . $CisloOrg . "')
		";
		//e($insert);
		try {
			$r = mssql_query($insert);
		} catch (Exception $e) {
			Utils::debug($e->getMessage());
	    }
	}
	
	function exportBasket($basket){
				
		$hc = mssql_pconnect($this->server, $this->username, $this->pass);
		mssql_select_db($this->dbname, $hc);		
		
		//user
		$userdata = (array) $basket->getShopper();
		$userdata['zip'] = trim(str_replace(' ', '', $userdata['zip']));
		$userdata['zip2'] = trim(str_replace(' ', '', $userdata['zip2']));
		
		if($userdata['nazev']){
			$nazev = $userdata['nazev'];
			$nazev2 = $userdata['firstname'] . ' ' . $userdata['surname'];
		} else {
			$nazev = $userdata['firstname'] . ' ' . $userdata['surname'];
		}

		
		$exists = $this->checkIfCompanyExists(iconv("UTF-8", "Windows-1250", $nazev), $userdata['city']);
					
		if($userdata['useDeliveryAdress']){
			$deliveryAdress = 1;	
			
			$nadrazena = $this->useCisloOrg?$this->useCisloOrg:111111111111111;			
			$existsDelivery = $this->checkIfCompanyExists(iconv("UTF-8", "Windows-1250", $nazev), $userdata['city'], $nadrazena);
						
			if($this->useCisloNadrizenaOrg){ // vlozim primo do spravne
				$CisloOrgOld = $this->useCisloNadrizenaOrg;
				$poznamka .= 'D,';
				$poznamka .=  $CisloOrgOld;
				$CisloOrg = $this->getNextOrgId();
				$this->addOrg($hc, 1, $CisloOrg, $nazev, $nazev2, $userdata, 1, $poznamka, 1);
				//$this->addExistsOrg($hc, $CisloOrg);
			} else {		
				$poznamka = '';		
				if($exists) { // org existuje ale nema jeste dodaci firmu zalozenou = zakladam 1
					if($existsDelivery){
						$poznamka .= 'D,';
					}
					if($this->useCisloOrg){
						$poznamka .= $this->useCisloOrg;
					}
					
					
					
					$CisloOrg = $this->getNextOrgId();
					$this->addOrg($hc, 1, $CisloOrg, $nazev, $nazev2, $userdata, 1, $poznamka, 1);
				} else { // org NEexistuje a nema jeste dodaci firmu zalozenou = zakladam 2
					/* NADRAZENA*/
					$CisloOrg = $CisloOrgNad = $this->getNextOrgId();	
					$this->addOrg($hc, 0, $CisloOrg, $nazev, $nazev2, $userdata, 0);
					$poznamka .=  $CisloOrgNad . ', ';
					/* DORUCOVACI*/
					$CisloOrg = $this->getNextOrgId();
					$this->addOrg($hc, 1, $CisloOrg, $nazev, $nazev2, $userdata, 1, $poznamka, 1);
				}				
			}
			
		} else {
			$deliveryAdress = 0;
			$poznamka = '';
			// 1 organizece
			if($this->useCisloOrg){
				$CisloOrg = $this->useCisloOrg;
				//$this->addExistsOrg($hc, $CisloOrg);
				$poznamka .= 'D,';
				$poznamka .=  $CisloOrg;
				
				$CisloOrg = $this->getNextOrgId();
				$this->addOrg($hc, 1, $CisloOrg, $nazev, $nazev2, $userdata, 1, $poznamka);
			} else {
				$poznamka = '';
				if($exists){
					$poznamka = 'D';
				}
				$CisloOrg = $this->getNextOrgId();
				$this->addOrg($hc, 1, $CisloOrg, $nazev, $nazev2, $userdata, 1, $poznamka);
			}
		}
				
		// polozky
		foreach ($basket->getItems() as $id => $odata){ 		
			$items = array();
    		$product = $odata['item'];
    		$node = $odata['node'];
    		
    		if(!$product){
    			continue;
    		}
    		    		
    		$items['o_id'] = $oid;    	
    		$items['count'] = $odata['count'];    		
    		$items['title'] = $node->title;    	
    		$items['dph'] = $product->getPropertyValue('dph');    
    		$items['price'] = $product->getPrice(); 	
    		
    		$insert = "
				INSERT INTO TabUniImportOZ 
				(IDHlavicky, DruhPohybuZbo, IDSklad, RadaDokladu, CisloOrg, SkupZbo, RegCis, Mnozstvi, InterniVerze )
				VALUES 
				($CisloOrg, 10, '001', '002', $CisloOrg, '" . $product->getPropertyValue('skupina') . "', '" . $product->getPropertyValue('regCislo') . "', " . $items['count'] . ", 1 )
			";	
    		//e($insert);
    		try {
				$r = mssql_query($insert);
			} catch (Exception $e) {
				Utils::debug($e->getMessage());
		    }
		}	
			
	}
	
	/*
	function checkIfCompanyExists($firma, $mesto){	
		$exists = false;	
		$sql = $this->db->quoteInto("select CisloOrg, Firma, Misto, UliceSCisly,  FROM TabCisOrg WHERE Firma = ?", $firma);
		$r = mssql_query($sql);
		$this->useCisloOrg = 0;
		while($o = mssql_fetch_object($r)){
			$exists = true;
			if($o->Firma == $firma){
				if($o->Misto == $mesto){
					$this->useCisloOrg = $o->CisloOrg;
					break;
				}
			}
			$i++;
		}	
		
		return $exists;
	}
	*/
	
	function checkIfCompanyExists($firma,  $mesto, $NadrizenaOrg = 0){	
		$exists = false;	
		$sql = $this->db->quoteInto("select CisloOrg, Firma, Misto, UliceSCisly, NadrizenaOrg FROM TabCisOrg WHERE Firma = ?", $firma);
		//e($sql);
		$r = mssql_query($sql);
		$this->useCisloOrg = 0;
		while($o = mssql_fetch_object($r)){
			if(!$NadrizenaOrg){
				$exists = true;
			}
			if($o->Firma == $firma){
				
				if($NadrizenaOrg && $NadrizenaOrg == $o->NadrizenaOrg){
					$exists = true;
				}
				
				if($o->Misto == $mesto){
					if($NadrizenaOrg){
						if($NadrizenaOrg == $o->NadrizenaOrg){
							$this->useCisloNadrizenaOrg = $o->NadrizenaOrg;
							break;
						}
					} else {
						$this->useCisloOrg = $o->CisloOrg;
						break;
					}
				}
			}
			$i++;
		}	
		
		return $exists;
	}
	
	
	
	function getNextOrgId(){
		$this->db->insert('module_HeliosNextId', array('dummy' => '0'));
		return $this->db->lastInsertId();	
	}
	
	
	
	/*
	
	object(stdClass)#8116 (128) {
  ["ID"] => int(4127)
  ["CisloOrg"] => int(8)
  ["NadrizenaOrg"] => int(100008)
  ["Nazev"] => string(23) "Prodejna Centrum zdrav�"
  ["DruhyNazev"] => string(1) " "
  ["Misto"] => string(24) "Louny     Mgr.Kohoutkov�"
  ["IdZeme"] => string(2) "CZ"
  ["Region"] => NULL
  ["Ulice"] => string(21) "Bene�e z Loun 185-321"
  ["OrCislo"] => string(1) " "
  ["PopCislo"] => string(1) " "
  ["PSC"] => string(5) "44001"
  ["PoBox"] => NULL
  ["Kontakt"] => NULL
  ["DIC"] => string(10) "CZ49903012"
  ["LhutaSplatnosti"] => int(0)
  ["Stav"] => int(0)
  ["PravniForma"] => int(0)
  ["DruhCinnosti"] => NULL
  ["ICO"] => string(8) "49903012"
  ["Sleva"] => float(0)
  ["OdHodnoty"] => float(0)
  ["CenovaUroven"] => int(3)
  ["IDSOZsleva"] => NULL
  ["IDSOZnazev"] => NULL
  ["Poznamka"] => NULL
  ["FormaUhrady"] => string(18) "P�evodn�m p��kazem"
  ["JeOdberatel"] => int(0)
  ["JeDodavatel"] => int(0)
  ["VernostniProgram"] => int(0)
  ["OdpOs"] => NULL
  ["Upozorneni"] => NULL
  ["CisloOrgDos"] => string(1) " "
  ["Mena"] => NULL
  ["DatPorizeni"] => string(26) "Apr  1 2006 08:51:58:373AM"
  ["Autor"] => string(2) "sa"
  ["DatZmeny"] => NULL
  ["Zmenil"] => NULL
  ["BlokovaniEditoru"] => NULL
  ["Fakturacni"] => int(0)
  ["MU"] => int(1)
  ["Prijemce"] => int(0)
  ["UdajOZapisuDoObchRej"] => string(1) " "
  ["IDBankSpojeni"] => NULL
  ["CarovyKodEAN"] => NULL
  ["PostAddress"] => string(1) " "
  ["Kredit"] => float(0)
  ["Saldo"] => float(0)
  ["UhrazenoPredSpl1"] => float(0)
  ["UhrazenoPredSpl2"] => float(0)
  ["UhrazenoPredSpl3"] => float(0)
  ["UhrazenoPredSpl4"] => float(0)
  ["UhrazenoPredSpl5"] => float(0)
  ["UhrazenoPredSpl6"] => float(0)
  ["UhrazenoPredSpl0"] => float(0)
  ["UhrazenoPoSpl1"] => float(0)
  ["UhrazenoPoSpl2"] => float(0)
  ["UhrazenoPoSpl3"] => float(0)
  ["UhrazenoPoSpl4"] => float(0)
  ["UhrazenoPoSpl5"] => float(0)
  ["UhrazenoPoSpl6"] => float(0)
  ["UhrazenoPoSpl0"] => float(0)
  ["NeuhrazenoPredSpl1"] => float(0)
  ["NeuhrazenoPredSpl2"] => float(0)
  ["NeuhrazenoPredSpl3"] => float(0)
  ["NeuhrazenoPredSpl4"] => float(0)
  ["NeuhrazenoPredSpl5"] => float(0)
  ["NeuhrazenoPredSpl6"] => float(0)
  ["NeuhrazenoPredSpl0"] => float(0)
  ["NeuhrazenoPoSpl1"] => float(0)
  ["NeuhrazenoPoSpl2"] => float(0)
  ["NeuhrazenoPoSpl3"] => float(0)
  ["NeuhrazenoPoSpl4"] => float(0)
  ["NeuhrazenoPoSpl5"] => float(0)
  ["NeuhrazenoPoSpl6"] => float(0)
  ["NeuhrazenoPoSpl0"] => float(0)
  ["FaSumaCelkem"] => float(0)
  ["PozastavenoCelkem"] => float(0)
  ["FaAktualizovano"] => NULL
  ["PlneniBezDPH"] => int(0)
  ["Jazyk"] => NULL
  ["DatumNeupominani"] => NULL
  ["CenovaUrovenNakup"] => NULL
  ["TIN"] => string(1) " "
  ["EvCisDanovySklad"] => NULL
  ["DICsk"] => NULL
  ["SlevaSozNa"] => int(2)
  ["SlevaSkupZbo"] => int(2)
  ["SlevaKmenZbo"] => int(2)
  ["SlevaStavSkladu"] => int(2)
  ["SlevaZbozi"] => int(2)
  ["SlevaOrg"] => int(2)
  ["IdTxtPenFak"] => NULL
  ["Firma"] => string(23) "Prodejna Centrum zdrav�"
  ["UliceSCisly"] => string(22) "Bene�e z Loun 185-321 "
  ["DatPorizeni_D"] => int(1)
  ["DatPorizeni_M"] => int(4)
  ["DatPorizeni_Y"] => int(2006)
  ["DatPorizeni_Q"] => int(2)
  ["DatPorizeni_W"] => int(13)
  ["DatPorizeni_X"] => string(26) "Apr  1 2006 12:00:00:000AM"
  ["DatZmeny_D"] => NULL
  ["DatZmeny_M"] => NULL
  ["DatZmeny_Y"] => NULL
  ["DatZmeny_Q"] => NULL
  ["DatZmeny_W"] => NULL
  ["DatZmeny_X"] => NULL
  ["PostovniAdresa"] => string(79) "Prodejna Centrum zdrav�
Bene�e z Loun 185-321 
44001 Louny     Mgr.Kohoutkov�"
  ["KreditZustatek"] => float(0)
  ["FaAktualizovano_D"] => NULL
  ["FaAktualizovano_M"] => NULL
  ["FaAktualizovano_Y"] => NULL
  ["FaAktualizovano_Q"] => NULL
  ["FaAktualizovano_W"] => NULL
  ["FaAktualizovano_X"] => NULL
  ["DatumNeupominani_D"] => NULL
  ["DatumNeupominani_M"] => NULL
  ["DatumNeupominani_Y"] => NULL
  ["DatumNeupominani_Q"] => NULL
  ["DatumNeupominani_W"] => NULL
  ["DatumNeupominani_X"] => NULL
  ["Logo"] => NULL
  ["KorekceSplatnoAuto"] => NULL
  ["KorekceSplatnoUziv"] => NULL
  ["NapocetProPT"] => int(0)
  ["IDBankSpojPlatak"] => NULL
  ["Logo_BGJ"] => string(1) " "
  ["Logo_DatLen"] => int(0)
}


*/

	//$r = mssql_query('SELECT * FROM TabStrom'); // sklad
		//$r = mssql_query('SELECT Nazev, RadaDokladu FROM TabDruhDokZbo'); // rada dokladu
		//$r = mssql_query('select top 10 * FROM TabCisOrg'); // odberatel
		/*
		while($o = mssql_fetch_object($r)){
			pr($o);
		}
		*/
		/*
		$r = mssql_query("
				SELECT *
				FROM INFORMATION_SCHEMA.Tables T JOIN INFORMATION_SCHEMA.Columns C
				ON T.TABLE_NAME = C.TABLE_NAME
				WHERE T.TABLE_NAME = 'TabUniImportOZ'
				ORDER BY C.COLUMN_NAME
		");
		*/
		//while($o = mssql_fetch_object($r)){
			//pr($o);
			//echo $o->COLUMN_NAME.',';
			//CisloOrg,CisloOrgDOS,CisloUctu,DatumImportu,DIC,DruhyNazev,Email,ExtAtr1,ExtAtr1Nazev,ExtAtr2,ExtAtr2Nazev,ExtAtr3,ExtAtr3Nazev,ExtAtr4,ExtAtr4Nazev,Fakturacni,Fax,Chyba,Chyba_255,IBANElektronicky,ICO,ID,IdZeme,InterniVerze,JeDodavatel,JeOdberatel,KodUstavu,KOEmail,KOFax,KOJmeno,KOMobil,KOPrijmeni,KOTelefon,LhutaSplatnosti,Misto,Mobil,MU,Nazev,NazevBankSpoj,OrCislo,PoBox,PopCislo,Poznamka,Poznamka_255,Prijemce,PSC,Telefon,Ulice,WWW,            
		//}	
		
		
		/* IMPORT ORGANIZACE */
		//BarCode,Cena,CisloOrg,CisloZakazky,CisloZakazkyPol,CisloZam,DatPorizeni,DatPovinnostiFa,DatumImportu,DIC,DodFak,DruhPohybuZbo,DUZP,ExtAtr1,ExtAtr1Nazev,ExtAtr2,ExtAtr2Nazev,ExtAtr3,ExtAtr3Nazev,ExtAtr4,ExtAtr4Nazev,FormaDopravy,FormaUhrady,Chyba,Chyba_255,ID,IDHlavicky,IDSklad,InterniVerze,JednotkaMeny,Kurz,Mena,MistoUrceni,MJ,Mnozstvi,NabidkaCenik,NavaznaObjednavka,NazevSozNa1,NOkruhCislo,PoradoveCislo,Poznamka,Poznamka_255,PoznamkaPol,PoznamkaPol_255,Prijemce,RadaDokladu,RegCis,SazbaDPH,SazbaDPHPol,SazbaSD,SazbaSDPol,SkupZbo,Sleva,Splatnost,StredNaklad,StredNakladPol,TextPolozka,Ukod,VstupniCena,Zaokrouhleni,            
		
		/* IMPORT POLOZEK */
		//CisloOrg,CisloOrgDOS,CisloUctu,DatumImportu,DIC,DruhyNazev,Email,ExtAtr1,ExtAtr1Nazev,ExtAtr2,ExtAtr2Nazev,ExtAtr3,ExtAtr3Nazev,ExtAtr4,ExtAtr4Nazev,Fakturacni,Fax,Chyba,Chyba_255,IBANElektronicky,ICO,ID,IdZeme,InterniVerze,JeDodavatel,JeOdberatel,KodUstavu,KOEmail,KOFax,KOJmeno,KOMobil,KOPrijmeni,KOTelefon,LhutaSplatnosti,Misto,Mobil,MU,Nazev,NazevBankSpoj,OrCislo,PoBox,PopCislo,Poznamka,Poznamka_255,Prijemce,PSC,Telefon,Ulice,WWW,            
		
}
?>