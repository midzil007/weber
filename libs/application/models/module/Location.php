<?
/**
 * Lokace, mesta ulice
 *  
 */
class module_Location
{
	private static $sLocations = array(
		'3201' => 'Benešov',
		'3202' => 'Beroun',
		'3701' => 'Blansko',
		'3702' => 'Brno-město',
		'3703' => 'Brno-venkov',
		'3801' => 'Bruntál',
		'3704' => 'Břeclav',
		'3402' => 'Cheb',
		'3503' => 'Chomutov',
		'3603' => 'Chrudim',
		'3502' => 'Děčín',
		'3401' => 'Domažlice',
		'3501' => 'Česká Lípa',
		'3301' => 'České Budějovice',
		'3302' => 'Český Krumlov',
		'3802' => 'Frýdek-Místek',
		'3601' => 'Havlíčkův Brod',
		'3706' => 'Hodonín',
		'3602' => 'Hradec Králové',
		'3504' => 'Jablonec nad Nisou',
		'3811' => 'Jeseník',
		'3604' => 'Jičín',
		'3707' => 'Jihlava',
		'3303' => 'Jindřichův Hradec',
		'3403' => 'Karlovy Vary',
		'3803' => 'Karviná',
		'3203' => 'Kladno',
		'3404' => 'Klatovy',
		'3204' => 'Kolín',
		'3708' => 'Kroměříž',
		'3205' => 'Kutná Hora',
		'3505' => 'Liberec',
		'3506' => 'Litoměřice',
		'3507' => 'Louny',
		'3206' => 'Mělník',
		'3207' => 'Mladá Boleslav',
		'3508' => 'Most',
		'3605' => 'Náchod',
		'3804' => 'Nový Jičín',
		'3208' => 'Nymburk',
		'3805' => 'Olomouc',
		'3806' => 'Opava',
		'3807' => 'Ostrava-město',
		'3606' => 'Pardubice',
		'3304' => 'Pelhřimov',
		'3305' => 'Písek',
		'3406' => 'Plzeň-jih',
		'3405' => 'Plzeň-město',
		'3407' => 'Plzeň-sever',
		'3306' => 'Prachatice',
		'3209' => 'Praha-východ',
		'3210' => 'Praha-západ',
		'500054' => 'Praha 1',
		'500224' => 'Praha 10',
		'547034' => 'Praha 11',
		'547107' => 'Praha 12',
		'539694' => 'Praha 13',
		'547361' => 'Praha 14',
		'547387' => 'Praha 15',
		'539601' => 'Praha 16',
		'547174' => 'Praha 17',
		'547417' => 'Praha 18',
		'547344' => 'Praha 19',
		'500089' => 'Praha 2',
		'538213' => 'Praha 20',
		'538949' => 'Praha 21',
		'538931' => 'Praha 22',
		'500097' => 'Praha 3',
		'500119' => 'Praha 4',
		'500143' => 'Praha 5',
		'500178' => 'Praha 6',
		'500186' => 'Praha 7',
		'500208' => 'Praha 8',
		'500216' => 'Praha 9',	
		'3709' => 'Prostějov',
		'3808' => 'Přerov',
		'3211' => 'Příbram',
		'3212' => 'Rakovník',
		'3408' => 'Rokycany',
		'3607' => 'Rychnov nad Kněžnou',
		'3608' => 'Semily',
		'3409' => 'Sokolov',
		'3307' => 'Strakonice',
		'3609' => 'Svitavy',
		'3308' => 'Tábor',
		'3410' => 'Tachov',
		'3509' => 'Teplice',
		'3610' => 'Trutnov',
		'3710' => 'Třebíč',
		'3711' => 'Uherské Hradiště',
		'3510' => 'Ústí nad Labem',
		'3611' => 'Ústí nad Orlicí',
		'3810' => 'Vsetín',
		'3712' => 'Vyškov',
		'3705' => 'Zlín',
		'3713' => 'Znojmo',
		'3809' => 'Šumperk',
		'3714' => 'Žďár nad Sázavou'
	);
	
	public $krajeSEOMap = array(
		1 => 'praha',
		2 => 'stredocesky-kraj',
		3 => 'karlovarsky-kraj',
		4 => 'kralovehradecky-kraj',
		5 => 'kraj-vysocina',
		6 => 'jihomoravsky-kraj',
		7 => 'jihocesky-kraj',
		8 => 'plzensky-kraj',
		9 => 'moravskoslezsky-kraj',
		10 => 'liberecky-kraj',
		11 => 'ustecky-kraj',
		12 => 'olomoucky-kraj',
		13 => 'pardubicky-kraj',  
		14 => 'zlinsky-kraj'
	);   
	
	public $stateCodes = array(
		'cz' => 'ČR',
		'sk' => 'SR'
	);
	
	public $statesNames = array(
		'cz' => 'Česká republika',
		'sk' => 'Slovenská republika' 
	);
	
	public $statesNames2 = array(
		'ČR' => 'Česká republika',
		'SR' => 'Slovenská republika' 
	);
	
	public $locations = array();
	public $locationsSelect = array();
	public $locationsMap = array();
	
	function __construct() {
		$this->reg = Zend_Registry::getInstance();	
		$this->reg->location = $this; 
		$this->tableLocations = 'location_okresy';
	}
	
	function addToLocation($locationId, array $newLocations){
		foreach ($newLocations as $locName){
			$this->reg->db->insert(
    			$this->tableLocations,
    			array(
    				'parent' =>  $locationId,    		
    				'title' =>  $locName
    			)
			);
		}
	}
	/*
	function getLocations($depth = true){
		if(!count($this->locations)){
			$loc = $this->reg->db->fetchAll("SELECT id, parent, title FROM `" . $this->tableLocations . "`");
			
			$all = array();
			foreach ($loc as $data){
				$all[$data['parent']][] = $data;
			}
			if($depth){
				$this->locations = $this->initLocation($all, $all[0]);
			} else {
				$this->locations = $this->initLocation($all, $all[0], -100000);
			}
		}
		return $this->locations;
	}
	*/
	function getLocations($depth = true, $language = 0){ 
		
		if($depth && count($this->locations)){
			return $this->locations;
		}
		
		if($depth == false  && count($this->locations2)){
			return $this->locations2;
		}
		   
		if($language && $language != 'cz'){ 
			$s = $language . '_title';
		} else {
			$s = 'title'; 
		}
		
		$loc = $this->reg->db->fetchAll("SELECT id, parent, $s as title FROM `" . $this->tableLocations . "`");
		// pr($loc); 
		$all = array();
		foreach ($loc as $data){
			$all[$data['parent']][] = $data;  
		}
		if($depth){
			$this->locations = $this->initLocation($all, $all[0]);
		} else {
			$this->locations = $this->locations2 = $this->initLocation($all, $all[0], -100000);
		}
			
		return $this->locations;
	}
	
	function getLocationsAll(){
		return $this->reg->db->fetchAll("SELECT id, parent, title FROM `" . $this->tableLocations . "`");
	}
	 
	function getOkresFromLocation($loc, $locId){ 		
		$okresyParentsId = array(1, 2, 187, 188);
		
		$all = array();
		foreach ($loc as $data){
			$all[$data['parent']][$data['id']] = $data; 
		}
		
		if(in_array($locId, $okresyParentsId)){
			return $all[$locId];  
		}
		
		// pr($all);  
		foreach ($all as $id => $locations){
			if(array_key_exists($locId, $locations)){
				$location = $locations[$locId]; 
				if(!in_array($location['parent'], $okresyParentsId)){
					return $this->getOkresFromLocation($loc, $location['parent']); 
				} else {
					return $location;
				}
			}
		}  
	}
	
	
	function getStatesSelect(){
		$select = array();
		$loc = $this->reg->db->fetchAll("SELECT id, parent, title FROM `" . $this->tableLocations . "` WHERE parent = 0");
		foreach ($loc as $lo){
			$select[$lo['id']] = $lo['title'];
		}
		return $select;
	}
	
	function getAll($lang = 0){
		$select = array();
		if($lang && $lang != 'cz'){ 
			$s = $lang . '_title';
		} else {
			$s = 'title';
		}
		$loc = $this->reg->db->fetchAll("SELECT id, $s as title FROM `" . $this->tableLocations . "`");
		foreach ($loc as $lo){  
			$select[$lo['id']] = $lo['title'];
		} 
		return $select;  
	}
	
	function getLocationsSelect(){
		$this->getLocations();
		return $this->locationsSelect;
	}
	
	function getLocationsSelect2($language = 0){
		$this->getLocations(false, $language);
		return $this->locationsSelect;
	}
	
	function initLocation($locations, $map = array(), $depth = 0){
		if($_SERVER['REMOTE_ADDR'] == '217.195.175.149'){
			//e($map);
		}
		foreach ($map as $i => $data){
			if($depth >= 0){
				$this->locationsSelect[$data['id']] = $depth . '%' . $data['title'];
			} else {
				$this->locationsSelect[$data['id']] = $data['title'];
			}
			
			$this->locationsMap[$data['parent']][] = $data;
			
			if($locations[$data['id']]){
				$map[$i]['children'] = $this->initLocation($locations, $locations[$data['id']], $depth + 1);
			}
		}
		return $map;
	}
	
	function getSubLocations($locId){
		$idMap = array();
		$this->getLocations();
		
		foreach ($this->locationsMap as $k => $locations ){
			foreach ($locations as $x => $location ){
				if($location['id'] == $locId){
					$idMap[] = $location['id'];
					$locationSelected = $location;
					break 2;
				}
			}
		}
		
		//subsections
		$locs = $this->locationsMap[$locationSelected['id']];
		if(count($locs)){
			foreach ($locs as $x => $location ){
				$idMap[] = $location['id'];
				$locs2 = $this->locationsMap[$location['id']];
				if(count($locs2)){
					foreach ($locs2 as $z => $location2 ){
						$idMap[] = $location2['id'];
						
						$locs3 = $this->locationsMap[$location2['id']];
							if(count($locs3)){
								foreach ($locs3 as $z => $location3 ){
									$idMap[] = $location3['id'];
								}
							}
					}
				}
			}
		}
		return $idMap;
	}
	
	function drawLocationSelect($view, $class = 'w166', $color1 = '1a415d', $color2 = '185d8d', $undefinedText = 'lokalita', $inputName = 'okres', $extra = ''){		
		$locations = $this->getLocationsSelect();		
		
				
		$xhtml = '<select name="' . $inputName . '" class="' . $class . '" ' . $extra . '>'; 
		if($undefinedText){
			$xhtml .= '<option value="-1">' . $undefinedText . '</option>';
		} 
		
		$inputName = str_replace('[]', '', $inputName);
		
		if(isset($view->input->$inputName)){
			$selected = $view->input->$inputName;
		} else {
			$selected = $view->inputGet->$inputName;
		}
		 
		if(!is_array($selected)){
			$selected = array($selected);
		}
		//e($selected);
		
		 
		foreach ($locations as $id => $name){
			list($depth, $name) = explode('%', $name);
			$sel = (in_array($id, $selected))?'selected="selected"':'';
			
			$s = '';
			$padding = $depth * 4;
			if($depth == 0){
				$s = 'style = "font-weight:bold; background:#' . $color1 . '; "';
			} elseif ($depth == 1){
				$s = 'style = "background:#' . $color2 . ';"'; 
			} else {
				$s = 'style = "font-weight:normal;"'; //  padding-left:' . $padding  . 'px !important;
			}
			$prefix = str_repeat('&nbsp;', $depth * 2); 
			$xhtml .= '<option ' . $sel . ' value="' . $id . '" ' . $s. '>' . $prefix . $name . '</option>';
		}
		
		$xhtml .= '</select>';
		return $xhtml;
	}
	
	
	function getKraje($asIds = false, $stripKraj = false){ 
		 
		if(!is_array($this->reg->locaion->kraje)){			
			$kraje = array();
			$select = 'SELECT * FROM `location_kraje` ORDER BY `nazev` ASC';
			 
			foreach ($this->reg->db->fetchAll($select) as $k){
				if($stripKraj){
					$k['nazev'] = str_replace('kraj', '', $k['nazev']);
				}
				if($asIds){
					$kraje[$k['id']] = $k['nazev']; 
				} else {
					$kraje[$k['nazev']] = $k['nazev']; 
				}
			}
		
			$this->reg->location->kraje = $kraje;
		}
		
		return $this->reg->location->kraje;
	}
	
	
	function getLocationIdByName($name){ 	 	 		
		$kraje = array();
		$select = 'SELECT id FROM `location_okresy` WHERE title = ?';
		return $this->reg->db->fetchOne($select, $name);	
	}
	
	
	function getKrajeMap(){
		if(!is_array($this->reg->location->krajeMap)){			
			$kraje = array();
			$select = 'SELECT * FROM `location_kraje` ORDER BY `nazev` ASC';
			
			foreach ($this->reg->db->fetchAll($select) as $k){
				$kraje[$k['nazev']] = $k['id'];
			}
		
			$this->reg->location->krajeMap = $kraje;
		}
		
		return $this->reg->location->krajeMap;
	}
	
	function getStatFromLocation($loc){
		$all = $this->reg->db->fetchAll("SELECT id, parent, title FROM `" . $this->tableLocations . "`");
		$search = true;
		$stat = '';
		$id = $loc;
		$i = 0;
		while($search){
			$i++;
			if($i > 500){ break; }
			
			foreach ($all as $data){
				if($data['id'] == $id){
					$id = $data['parent'];
					if($data['parent'] == 0){
						$search = false;
						$stat = $data;
						break;
					}
				}
			}
		}
		
		return $stat;
	}
	
		
	function getStateIdent($stat){
		$staty = array_flip($this->getStaty());
		return $staty[$stat['title']];
	}
	
	function getStateCodeFromLocation($loc){
		$stat = $this->getStatFromLocation($loc);
		$ident = $this->getStateIdent($stat);
		return $this->stateCodes[$ident];
	}
	
	function getStaty(){
		return array(
			'cz' => 'Česká republika',
			'sk' => 'Slovenská republika'
		);
	}
	
	function getStaty2(){
		return array(
			0 => 'Česká republika',
			1 => 'Slovenská republika' 
		);
	}	
	
	function krajeSEOMapSelect($stripKraj = false){
		$kraje = $this->getKraje(true, $stripKraj); 
		$select = array();
		foreach ($kraje as $id => $title){
			$select[$this->krajeSEOMap[$id]] = $title; 
		}
		return $select;
	}
}


?>