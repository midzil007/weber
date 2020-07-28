<?
/**
 * Lokace, mesta ulice
 *  
 */
class module_IPTracing
{
	private static $countryCodes = array(
		'CZ' => 'Česká republika',
		'SK' => 'Slovenská republika'
	);
	
	private static $regionCodes = array(
		'CZ03' => 'Blansko',
		'CZ04' => 'Břeclav',
		'CZ20' => 'Hradec Králové',
		'CZ21' => 'Jablonec nad Nisou',
		'CZ23' => 'Jičín',
		'CZ24' => 'Jihlava',
		'CZ30' => 'Kolín',
		'CZ33' => 'Liberec',
		'CZ36' => 'Mělník',
		'CZ37' => 'Mladá Boleslav',
		'CZ39' => 'Náchod',
		'CZ41' => 'Nymburk',
		'CZ45' => 'Pardubice',
		'CZ52' => 'Praha',
		'CZ61' => 'Semily',
		'CZ70' => 'Trutnov',
		'CZ78' => 'Jihomoravský kraj',
		'CZ79' => 'Jihočeský kraj',
		'CZ80' => 'Vysočina',
		'CZ81' => 'Karlovarský kraj',
		'CZ82' => 'Královéhradecký kraj',
		'CZ83' => 'Liberecký kraj',
		'CZ84' => 'Olomoucký kraj',
		'CZ85' => 'Moravskoslezský kraj',
		'CZ86' => 'Pardubický kraj',
		'CZ87' => 'Plzeňský kraj',
		'CZ88' => 'Středočeský kraj',
		'CZ89' => 'Ústecký kraj',
		'CZ90' => 'Zlínský kraj',
		
		'SK01' => 'Banská Bystrica', 
		'SK02' => 'Bratislava',
		'SK03' => 'Košice',
		'SK04' => 'Nitra',
		'SK05' => 'Prešov',
		'SK06' => 'Trenčín',
		'SK07' => 'Trnava',
		'SK08' => 'Žilina'
	); 
	 
	public $seoOkresToRegions = array(
		'CZ03' => 'jihomoravsky-kraj',
		'CZ04' => 'jihomoravsky-kraj',
		'CZ20' => 'kralovehradecky-kraj',
		'CZ21' => 'liberecky-kraj',
		'CZ23' => 'kralovehradecky-kraj',
		'CZ24' => 'kraj-vysocina',
		'CZ30' => 'stredocesky-kraj',
		'CZ33' => 'liberecky-kraj',
		'CZ36' => 'stredocesky-kraj',
		'CZ37' => 'stredocesky-kraj',
		'CZ39' => 'kralovehradecky-kraj',
		'CZ41' => 'stredocesky-kraj',
		'CZ45' => 'pardubicky-kraj',
		'CZ52' => 'praha',
		'CZ61' => 'liberecky-kraj',
		'CZ70' => 'kralovehradecky-kraj',
		'CZ78' => 'jihomoravsky-kraj',
		'CZ79' => 'jihomoravsky-kraj',
		'CZ80' => 'kraj-vysocina',
		'CZ81' => 'karlovarsky-kraj',
		'CZ82' => 'kralovehradecky-kraj',
		'CZ83' => 'liberecky-kraj',
		'CZ84' => 'olomoucky-kraj',
		'CZ85' => 'moravskoslezsky-kraj',
		'CZ86' => 'pardubicky-kraj',
		'CZ87' => 'plzensky-kraj',
		'CZ88' => 'stredocesky-kraj',
		'CZ89' => 'ustecky-kraj',
		'CZ90' => 'zlinsky-kraj'
	);
	
	private $lookupHref = 'http://api.ip-adress.com/?u=5e4f2234b9ef55492cbe140e22f7413e7507a&h=';
	function __construct() {
		$this->reg = Zend_Registry::getInstance();	
		$this->session = $this->reg->session;
		$this->reg->IPTracing = $this;
		
	}
	
	function _getData($ip){
		$url = $this->lookupHref . urlencode($ip);
		$contents = Utils::loadUrl($url); 
		$data = array();
		if(strlen($contents) > 10){
			$contents = str_replace('"', '', $contents); 
			$loaded = explode(',', $contents); 		
			 
			$data = array(
				'ipOrig' => $loaded[0],
				'ip' => $loaded[1],
				'cCode' => $loaded[2],
				'cName' => $loaded[3],
				'rCode' => $loaded[4],
				'rName' => $loaded[5],
				'city' => $loaded[6],
				'zip' => $loaded[7],
				'latitude' => $loaded[8], 
				'longtitude' => $loaded[9],
				'ISP' => $loaded[10], 
				'company' => $loaded[11],
				'kraj' => $this->seoOkresToRegions[$loaded[2].$loaded[4]]
			); 
		}
		return $data;
	}
	
	function parseInfo($ip){
		
	}
	
	function getIPInfo($ip){
		
	}
	
	function getUserInfo(){
		$ip = Utils::getIP();
		// $this->session->ipUserData = 0; 
		if($this->session->ipUserData){
			$data = $this->session->ipUserData;
		} else {
			$data = $this->session->ipUserData = $this->_getData($ip);  
		} 
		$this->data = $data;
		return $data;  
	}
	
	function setRegion($kraj = 0){ 
		if(!$kraj){ // autoset
			if(!isset($_COOKIE['myRegion'])){ 
				$kraj = $this->data['kraj'];
				setcookie ("myRegion", $kraj, time() + 3600 * 24* 365);
			} 
		} else {
			$this->data['kraj'] = $kraj;  
			$this->newKraj = true;
			setcookie ("myRegion", $kraj, time() + 3600 * 24* 365);
		}		 
	} 
	
	function getMyRegion(){ 
		if($this->newKraj){
			return $this->data['kraj'];
		} else {
			return $_COOKIE['myRegion']?$_COOKIE['myRegion']:$this->data['kraj'];
		}
		 
	}
	
}


?>