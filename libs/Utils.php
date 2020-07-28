<?
// +----------------------------------------------------------------------+
// | CMS                                                                  |
// +----------------------------------------------------------------------+
// | Copyright (c) 2007-2008 mo                                           |
// +----------------------------------------------------------------------+

/**
 * Třída Utils je souborem pomocných statických metod používaných napříč celou aplikací.
 * Obsahuje mimojiné soubor metod pro práci s tzv. univerzálním časem v textovém formátu 
 * 'Y-m-d H:i:s'. 
 */

final class Utils {
	
	public static $months = array(
		'leden',
		'únor',
		'březen',
		'duben',
		'květen',
		'červen',
		'červenec',
		'srpen',
		'září',
		'říjen',
		'listopad',
		'prosinec'
	);
	public  static function getWebUrlHttp(){
		return Zend_Registry::getInstance()->config->protocolhttp . Zend_Registry::getInstance()->config->webhost;
	} 
	
	
	 
		static function formatDateDb2($time)
	{ 
		$array = explode('.', $time);  
		return $array['2'].'-'.$array[1].'-'.$array[0];
	}   

	
	public static $months2 = array(
		"ledna",
		"února",
		"března",
		"dubna",
		"května",
		"června",
		"července",
		"srpna",
		"září",
		"října",
		"listopadu",
		"prosince"
	);
	
	public static $months3 = array(
		1 => 'leden',
		'únor',
		'březen',
		'duben',
		'květen',
		'červen',
		'červenec',
		'srpen', 
		'září',
		'říjen',
		'listopad',
		'prosinec'
	);
	
	public static $monthsEN3 = array(
		1 => 'January',
		'February',
		'March',
		'April',
		'May',
		'June',
		'July',
		'August',
		'September',
		'October',
		'November',
		'December'
	); 
	
	public static $dayNames=array(
		"Neděle",
		"Pondělí",
		"Úterý",
		"Středa",
		"Čtvrtek",
		"Pátek",
		"Sobota"
	);
	
	static function parsePath($fullpath) {
				
		$fullpath = substr($fullpath, 1);						
		$parts = explode('/',$fullpath);
		$_path = array();
		
		foreach($parts as $part){
			if(trim($part)){
				$noHtml = current(explode('.',$part));
				$noHtml = current(explode('?',$noHtml));
				$_path[] = $noHtml;
			}
		}
		
		return '/' . implode("/", $_path);
	}
	
	public static function onlyME(){
		if($_SERVER['REMOTE_ADDR'] == '213.220.255.68'){
			return true;
		} else {
			return false;
		}
	}

	public function getDataSource() {				
		return Zend_Registry::getInstance()->db;
	}
	
	static  function  getRedirectPath($path){	
	//	die($path);	
		$u =  str_replace('//', '/', $path);
		if($u == '/cms/login'){
			$u = '/cms';
		} 
		return $u;		
	}
	
	/**
	 * Přidá GET parametry do URL 
	 *
	 * @param string $url
	 * @param array $paramsToAdd
	 * @return string $url
	 */
	static function addUrlParams($url, $paramsToAdd = array()){
		 		 
		$paramsToAdd = (array) $paramsToAdd;
		if(!count($paramsToAdd)){
			e('dsd');
			return $url;
		}
		$params = '';		
		
		foreach ($paramsToAdd as $k => $v){
			if(is_array($v)){
				foreach ($v as $val){ 
					$params .= '&' . $k . '[]=' . urlencode($val);
				} 
			} else { 
				$params .= '&' . $k . '=' . urlencode($v); 
			}
		}
		if(strpos($url, '?') === false){
			$url .= '?' . $params;
		} else {
			$url .= $params;
		}		
		
		return $url;
	}
	
	static function getPath($path){	
		return  $path . '.phtml';		
	}
	
	static function getWebPath($path){
		if($path == '/'){
			$path = '/';
		}
		return  $path;		
	}
	
	static function getCmsPath($path){
		if($path == '/'){
			$path = '';
			return  $path;
		} 
		return  $path . '.html';		
	}
		
	public  static function getWebUrl(){
		return Zend_Registry::getInstance()->config->protocol . Zend_Registry::getInstance()->config->webhost;
	}
	
	
		static function formatDateDb1($time)
	{ 
		$array = explode('.', $time);  
		return $array['2'].'-'.$array[1].'-'.$array[0];
	}    
	
	static function formatDateSaveDB($date)
	{
		list($d, $m, $y) = explode('.', $date);
		return "$m-$d-$y";
	}
			
	static function getFrontEndUrl($path, $view){ 
		$conf = Zend_Registry::getInstance()->config;
		if(self::getExtension($path)){ // soubor
			if(strpos($path, $conf->sfFolder) === false){				
				$path = Zend_Registry::getInstance()->config->sfFolder . '/' . $path;
				$path =  str_replace('//', '/', $path);
			}
			
			return  self::getWebUrl() .  $path;
		} else{	
			
			if($conf->subdomainFirstLevel){				
				$p = helper_FrontEnd::getPath(0, $path);
			} elseif ($conf->domainPath){
				$p = helper_FrontEnd::getPathOneAdmin($view, $path);
			} else {
				$p = self::getWebPath($path);
			}
			return $p;
		}	
	}
	
	static function getFrontEndLink($path, $showLink = false, $forceTitle = '', $preview = false, $id = 0, $view = 0){
		
		$p =  self::getFrontEndUrl($path, $view);  
		if($forceTitle){
			$linkTitle = $forceTitle;
		} else {
			$linkTitle = $p;
		}
		
		// $p .= '?adm7=1';  		  
		
		if($preview){
			if(content_SFSFile::isSFSFile($path)){
				$id = ' id="' . $id . '" ';
			}
		}
		
		return '<a '. $id .' title="zobrazit v prohlížeči - \'' . $linkTitle . '\'" target="_blank" class="ico2 frontEndUrl" href="' . $p . '">&nbsp;' . ($showLink?'&nbsp;&nbsp; &nbsp;' . $linkTitle:'') . '</a> ';
	}
	
	
	public static function getPathsLastPart($path){
		return array_pop(explode('/',$path));
	}

	static function getStateIcon($node){
		$content = $node->getTheRightContent();
		return self::getContentStateIcon($content);
	}
	
	static function getContentStateIcon($content){
		//e($content);
		$published = ($content->state == 'PUBLISHED') ;		
		
		$class = $published?'published':'notPublished';
		$title = $published?'publikováno':'nepublikováno';
		
		if(!$content){ // sfs folder
			$class = 'folder';
			$title = '';
		} elseif($content->_name == 'content_SFSFile'){			
			return  self::getFileIcoImage($content->getPropertyValue('fullpath')) . '&nbsp;';
		}
		
		return '<span title="' . $title . '" class="ico2 ' . $class . '">&nbsp;</span>';
	}
	
	static function setActive($path, $nodePath, $class = 'active'){		
		$c = '';
		if($path == $nodePath){
			$c = ' class="' . $class . '"';
		}
		return $c;		
	}
	
	
	static function getHelpIco($helpText){		
		return '<a class="helpico" href="#" title="' . $helpText . '">&nbsp;</a>';		
	}
	
		
	static function getFileIco($path){
		//ikona typu souboru
		
		$ico = '/images/icons/filetype/' . self::getExtension($path) . '.gif';   
		return $ico;
	}
	
	static function getFileIcoImage($path){
		return '<img align="absmiddle" src="' . self::getFileIco($path) . '" alt="" />';
	}
	
	public static function getWebFileSize($path){
		return self::getFileSize(Zend_Registry::getInstance()->config->htdocsRoot . $path);
	}
	
	public static function getFileSize($fullpath){
		$size = filesize(urldecode($fullpath));
		return self::getReadableSize($size);
	}
	
	public static function getReadableSize($size){
		$i=0;
		$iec = array("B", "kB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
		while (($size/1024)>1) {
			$size=$size/1024;
			$i++;
		}
		
		$s = substr(round($size,1),0,strpos(round($size,1),'.')+4).' '.$iec[$i];
		return str_replace(".", ",", $s) ;
	}

	public  static function getWebUrlHttps(){
		return Zend_Registry::getInstance()->config->protocol . Zend_Registry::getInstance()->config->webhost;
	} 
	
	static function checkUrl($url){		  
		if(strlen($url) >= 3){
			if(strpos($url, 'http://') !== 0){
				$url = 'http://' . $url;
			}
		}
		return $url;		
	}
	
	
	public static function getWYSIWYGHtml($html){
		
		
   		return stripslashes($html);
   	}
   	
	public static function saveFileDownloadStats($db, $filename,$user='') {
		$db->query('
			INSERT INTO `Files_downloads` (
				`name` , `user` 
			)
			VALUES ( ?, ? );
			',
			array($filename, $user));                
	}
	
	public  static function getAdminUrl($node, $view){
		$controllerIdentificatorMap = Zend_Registry::getInstance()->config->controllerIdentificatorMap->toArray();
		$superTypeControllerMap = Zend_Registry::getInstance()->config->superTypeControllerMap->toArray();
		
		$nodeId = $node->nodeId;
		
		$nsType = $node->getSupertype();
		//e($nsType);
		if($nsType == 'structure'){
			if($node->type == 'ITEM'){ // pak smeruju do pages
				$nsType = 'pages';
			}
		}
		if($nsType == 'pages' || $nsType == 'intranet' || $nsType == 'files'){
			if($node->type == 'ITEM'){
				$nodeId = $node->parentId;
			}
			$controller = $superTypeControllerMap[$nsType];
			$identificator = $controllerIdentificatorMap[$controller];
		} else {
			
		}
				
		$controller = $superTypeControllerMap[$nsType];
		$identificator = $controllerIdentificatorMap[$controller]; 
		$url = $view->url(array('controller' => $controller,'action' => 'index', $identificator => $nodeId, 'hln' => $node->nodeId  ));   
		
		return $url;
	}
	
	
	function echo_memory_usage() {
        $mem_usage = memory_get_usage(true);
       
        if ($mem_usage < 1024)
            echo $mem_usage." bytes";
        elseif ($mem_usage < 1048576)
            echo round($mem_usage/1024,2)." kilobytes";
        else
            echo round($mem_usage/1048576,2)." megabytes";
           
        echo "<br/>";
    }  
	
	
	public static function trimArray($a) {
		foreach ($a as $k => $v){
			$a[$k] = trim($v);
		}
		return $a;
	}
	
	public static function checkArray($a) {
		$a = trimArray($a);
		foreach ($a as $k => $v){
			$a[$k] = htmlspecialchars ($v);
		}
		return $a;
	}
	
	public static function generatePassword($length = 6){
		return substr((md5(uniqid(rand()))), 1, $length);
	}
	
	function test4IE6()
	{
	    if (isset($_SERVER['HTTP_USER_AGENT']) && 
	    (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.') !== false))
	        return true;
	    else
	        return false;
	}
	
	static $dateTimePatterns = array(
		'date' => array(
			'cs'	=> 'd.m.Y',
			'en'	=> 'Y/m/d',
		),
		'time' => array(
			'cs'	=> 'd.m.Y H:i:s',
			'en'	=> 'Y/m/d H:i:s'
		)
	);
	
	
	static function addChooseOption($array){
		return  array_merge(array('-1' => 'Vyberte'), $array);	
	}
	
	private function __construct() {
	}
	
	private static function getDateTimePattern($name) {
		$language = 
			isset($_COOKIE['CMS_LANGUAGE']) ? $_COOKIE['CMS_LANGUAGE'] :
				(isset($_SESSION['CMS_LANGUAGE']) ? $_SESSION['CMS_LANGUAGE'] : 'cs');
		return self::$dateTimePatterns[$name][$language];
	}
	
	/**
	 * Otestuje a vrátí nezneužitelné jméno souboru (neobsahující cestu mimo filesystém webu) 
	 */
	static function validFilename($filename) {
		$parts = explode('/', $filename);
		$parts2 = array();
		for ($i = 0; $i < count($parts); $i++)
			if ($parts[$i] != '..')
				$parts2[] = $parts[$i];
		return implode('/', $parts2);
	}
	
	
	/**
	 * Vytvori akce pro presouvani - razeni
	 * @return string
	 */
	public static $rowCount = 0;
	static function getOrderActions($view, $nodeId, $count) {
		self::$rowCount++;
		$upClass = $downClass = '';
		$onclick = "return refreshTab('" . $view->tableParentTab . "', '" . $view->url(array('action' => 'userSort', 'move' => '%A%', 'id' => $nodeId )) . "' )";
		$u1 = $u2 = $u3 = $u4 = 'href="" onclick="' . $onclick . '"';
		
		if(self::$rowCount > 1){
			$upClass = '-a';
			$u1 = str_replace('%A%', 'orderUp', $u1);
			$u2 = str_replace('%A%', 'orderTop', $u2);
		} else {
			$u1 = $u2 = '';
		}
		
		if(self::$rowCount < $count){
			$downClass = '-a';			
			$u3 = str_replace('%A%', 'orderDown', $u3);
			$u4 = str_replace('%A%', 'orderBottom', $u4);
		} else {
			$u3 = $u4 = '';
		}
		$xhtml = '
			<div class="order-div">
				<a  class="order-up' . $upClass . '" ' . $u1	 . ' title="posunout výše">&nbsp;</a>
				<a class="order-top' . $upClass . '" ' . $u2	 . ' title="posunout na začátek">&nbsp;</a>
				<a class="order-down' . $downClass . '" ' . $u3	 . ' title="posunout níže">&nbsp;</a>
				<a class="order-bottom' . $downClass . '" ' . $u4	 . ' title="posunout na konec">&nbsp;</a>
			</div>
		';
		
    	return $xhtml; 
	}
	
	/**
	 * Vrací čas v milisekundách.
	 * @return float
	 */
	static function getMicrotime() { 
		list($usec, $sec) = explode(' ', microtime()); 
    	return ((float) $usec + (float) $sec); 
	}
	
	/* univerzalni funkce pro prace s casem v textovem formatu 'Y-m-d H:i:s' */
	
	/**
	 * Vytvoří nový univerzální čas.
	 * @param int $time čas v sekundách (pokud je předáno false, použije se čas aktuální)
	 * @return string univerzální čas
	 */
	static function mkTime($time = false) {
		if (!$time) $time = time();
		return date('Y-m-d H:i:s', $time);
	}
	
	static function mkDate($time = false) {
		if (!$time) $time = time(); 
		return date('d. m. Y', $time);
	}
	/**
	 * Formátuje univerzální čas na řetězec podle zadané masky
	 * @param string $time univerzální čas
	 * @param string $format formátovací maska
	 * @return string naformátovaný čas
	 */
	static function formatTime($time, $format = false) {
		if (!$format)
			$format = self::getDateTimePattern('time');
		$_timeMaskChars = array(1 => 'Y', 'm', 'd', 'H', 'i', 's');
		preg_match("/(\d{4})-(\d{1,2})-(\d{1,2}) (\d{1,2}):(\d{1,2})\:(\d{1,2})/", $time, $matches);
		if (empty($matches)) preg_match("/(\d{4})-(\d{1,2})-(\d{1,2})/", $time, $matches);
		if (empty($matches)) {			
			$format = '?';
		} else {
			foreach ($_timeMaskChars as $i => $char)
				if (isset($matches[$i]))	
					$format = str_replace($char, $matches[$i], $format);
		}  
		return $format;
	}
	
	/**
	 * Formátuje univerzální čas na řetězec podle masky
	 * @param string $time univerzální čas
	 * @return string naformátovaný čas
	 */
	static function formatDate($time, $format = '') {
		if(!$format){
			$format = self::getDateTimePattern('date');
		}

		return Utils::formatTime($time, $format);
	}
	/*
	static function formatDateWithoutYear($time) {
		 Utils::formatTime($time, self::getDateTimePattern('date'));
		 return
	}
	
	*/
	
	static function formatDateDb($time)
	{
		$array = explode('.', $time);
		array_reverse($array);
		return $array;
	}
	
	
	static function formatDateFromDb($time)
	{
		$array = explode('-', $time);
		return $array[2].'.'.$array[1].'.'.$array[0]; 
	}
	
	
	
	static function getReducedText($text, $limit = 200) {
		$text = strip_tags($text);
		if (strlen($text) <= $limit) { 
			return $text;
		} else { 
			$text = mb_substr($text, 0, $limit+10, 'utf-8'); 
			$pos = strrpos($text, " ");
			return mb_substr($text, 0, ($pos ? $pos : -1), 'utf-8') . '…'; 
		}
	}
	
	
	/**
	 * Parsuje univerzální čas z řezězce podle zadané masky
	 * @param string $str parsovaný řetězec
	 * @param string $format formátovací maska
	 * @return string univerzální čas
	 */
	static function parseTime($str, $format = false) {
		if (!$format)
			$format = self::getDateTimePattern('time');
		if (empty($str) || $str == '?') return false;
		$format = str_replace(':', ' ', $format);
		$format = str_replace('.', ' ', $format);
		$format = str_replace('/', ' ', $format);
		$str = str_replace(':', ' ', $str);
		$str = str_replace('.', ' ', $str);
		$str = str_replace('/', ' ', $str);
		$arr = explode(' ', $str);
		$time = array('Y' => '0000', 'm' => '01', 'd' => '01', 'H' => '00', 'i' => '00', 's' => '00');
		foreach (explode(' ', $format) as $i => $char)
			if (isset($arr[$i]))
				$time[$char] = strlen($arr[$i]) < 2 ? '0'.$arr[$i] : $arr[$i];
		return $time['Y'].'-'.$time['m'].'-'.$time['d'].' '.$time['H'].':'.$time['i'].':'.$time['s'];
	}
	
	/**
	 * Parsuje univerzální čas z řezězce podle masky
	 * @param string $str parsovaný řetězec
	 * @return string univerzální čas
	 */
	static function parseDate($str) {
		return Utils::parseTime($str, self::getDateTimePattern('date'));
	}
	
	/**
	 * Formátuje zprávu, tzn. nahradí v jejím textu každý výskyt řetězce {X} prvkem s klíčem X
	 * z předaného pole argumentů. Využívá se typicky při formátování chybových zpráv.
	 * @param string $msg formátovaná zpráva
	 * @param array $args pole argumentů (buď lineární - pak jsou klíče 0 až n, nebo asotiativní)
	 * @return string naformátovaná zpráva
	 */
	static function formatMsg($msg, $args) {
		for ($i = 0; $i < count($args); $i++)
			$msg = str_replace('{'.$i.'}', $args[$i], $msg);
		return $msg;
	}
	
	/**
	 * Konvertuje lineární pole na asociativní tak, že klíče se rovnají hodnotám.
	 * Metoda se využívá při definici množiny hodnot v combo boxu.
	 * @param array $arr lineární pole
	 * @return array asociativní pole (klíč = hodnota)
	 */
	static function assocArray($arr) {
		$arr2 = array();
		foreach ($arr as $value) $arr2[$value] = $value;
		return $arr2;
	}
	
	/**
	 * Přečte obsah souboru.
	 * @param string $filename jméno souboru
	 * @return mixed obsah souboru
	 */
	static function &readFromFile($filename) {
		return @file_get_contents($filename);
	}
	
	/**
	 * Zapíše proměnnou do souboru.
	 * @param mixed $var proměnná, která má být zapsána do souboru
	 * @param string $filename jméno souboru
	 * @return void
	 */
	static function writeToFile(&$var, $filename) {		
		$fp = fopen($filename, 'w');
		if ($fp === false)
			echo $filename . ' nelze otevrit';
		fwrite($fp, $var);
		fclose($fp);
	}
	
	static public function cleanTitle($name)
	{
		$name = trim($name);
		$name = str_replace('  ', ' ', $name);
		$name = str_replace('   ', ' ', $name);
		$name = str_replace('/', '_', $name);
		return $name;
	}
	
	/**
	 * Vygeneruje validní jméno (cestu). Používá se pro vytváření URI obsahů.
	 * Standardně odstraňuje diakritiku a s výjimkou znaků a číslic nahradí ostatní znaky 
	 * neutrálním znakem (podtržítkem). Metoda se využívá při vytváření cesty k obsahu z jeho titulku.
	 * @param string $name jméno (cesta)
	 * @return string validní jméno (cesta)
	 */
	static function generatePathNameSimple($name, $isValid = '-', $lower = true) {
		$name = trim($name);
		$name = str_replace('  ', ' ', $name);
		$name = str_replace('   ', ' ', $name);
		$name = str_replace('/', '_', $name);
		$name = strtolower($name);
		$isValid = $isValid?$isValid:'-';
		$t7 = array(
			'ě' => 'e', 'š' => 's', 'č' => 'c', 'ř' => 'r', 'ž' => 'z', 'ý' => 'y',
			'á' => 'a', 'í' => 'i', 'é' => 'e', 'ó' => 'o', 'ů' => 'u', 'ú' => 'u',
			'É' => 'E', 'Ě' => 'E', 'Š' => 'S', 'Č' => 'C', 'Ř' => 'R', 'Ž' => 'Z',
			'Ý' => 'Y', 'Á' => 'A', 'Í' => 'I', 'É' => 'E', 'Ó' => 'O', 'Ů' => 'U',
			'Ú' => 'U', 'ď' => 'd', 'Ď' => 'D', 'ť' => 't', 'Ť' => 'T', 'ň' => 'N',
			'Ň' => 'N' 
		);
		$delim = '-'; 
		$abc = 'abcdefghijklmnopqrstuvwxyz0123456789'.$isValid;
		foreach ($t7 as $needle => $str){
			$name = str_replace($needle, $str, $name);
		}
		
		$name = trim($name);
		if($lower){
			$name = strtolower($name);
		}  
		$name2 = "";
		$prevchar = false;
		for ($i = 0; $i < strlen($name); $i++) {
			$char = substr($name, $i, 1);
			if ($char == ' ' && $prevchar == ' ') ; else
				$name2 .= (stripos($abc, $char) === false) ? ($char == ' ' ? $delim : '') : $char;
			$prevchar = $char;
		}  
		return $name2;
	}
	
	static function generatePathName($path, $isValid= '-',  $prefix='/', $num=1){
		//e(Node::isPathAvailable($prefix . $path));

		$path = self::generatePathNameSimple(trim($path), $isValid);		
		$newPath = $path;
		
		if($num>1){
			$newPath .= '_'.$num;
		} 		

		$newPath = str_replace('--', '-', $newPath);
						
		if(!Node::isPathAvailable($prefix . $newPath)) {
			$num++;
			$newPath = self::generatePathName($newPath, $isValid, $prefix, $num);	
		}
		
		return $newPath;
		
	}
	
	static function getStrictUrl($view, array $requestParams, $unset = array()) {
		foreach ($unset as $param){
			unset($requestParams[$param]);
		}
		return $view->url($requestParams, null, true);
	}
	
	
	
	/**
	 * Vrátí seznam souborů v adresáři se zadanou příponou.
	 * @param string $dirname jméno adresáře
	 * @param string $ext přípona hledaných souborů
	 * @return array seznam názvů hledaných souborů
	 */
	static function getFiles($dirname, $ext = '', $onlyDirs = false) {
		//e($dirname);
		$dir = opendir($dirname);
		$files = array();		  
		$extlen = strlen($ext);
		//while ($filename = readdir($dir)){ // lol  
		while (false !== ($filename = readdir ($dir))){ 

			//e($filename); 
			if($ext){
				if (strrpos($filename, $ext) == strlen($filename) - $extlen){
					$files[] = $filename;
				}
			} else {
				if($onlyDirs){
					if(strpos($filename, '.') !== false){
						continue;
					}
				}
				$files[] = $filename;
			}
		}
				
		closedir($dir);
		sort($files);
		return $files;	
	}
	
	/**
	 * Smaze adrsář a jeho obsah
	 *
	 * @param string $dirname
	 * @return bool
	 */ 
	static function deleteFolder($dirname){
		if (is_dir($dirname)) {
			$entries = array();
			$entries = scandir($dirname);
			
			foreach ($entries as $entry) {
				if ($entry != '.' && $entry != '..') {
					self::deleteFolder($dirname . '/' . $entry);
				}
			}

			return @rmdir($dirname);
		} else {
			return @unlink($dirname);
		}
	}

	/**
	 * Vrátí URI aktuálního požadavku s odstraněním případného v ní obsaženého parametru action.
	 * Používá se např. v back-endu při bookmarkování aktuálně zobrazené stránky.
	 * @return string URI
	 */
	static function getClearRequestURI($append = false) {
		$uri = ereg_replace('&{0,1}action=[^&]*', '', $_SERVER['REQUEST_URI']);
		if ($append)
			$uri .= (strpos($uri, '?') === false) ? '?' : '&';
		return $uri; 
	}
	
	/**
	 * Převede HTML řetězec na posloupnost Javascriptových příkazů zapisujících tento řetězec
	 * na výstup. Používá se při exportu HTML dat do Javascriptu.
	 * @param string $html HTML kód
	 * @return string Javascriptový kód
	 */
	static function html2javascript($html) {
		$lines = explode("\n", $html);
		for ($i = 0; $i < count($lines); $i++)
			$lines[$i] = 'document.writeln("'.addslashes(trim($lines[$i])).'");';
		return implode("\n", $lines);
	}

	static function pad($str, $width, $right = false) {
		for ($i = strlen($str); $i < $width; $i++)
			$str = $right ? ' '.$str : $str.' ';
		return $str;
	}
	
	static function copyProperties($source, $dest) {
		foreach (get_object_vars($source) as $property => $value)
			$dest->$property = $value;
	}
	
		
	private static function compareMembers($member1, $member2) {
		$name1 = !empty($member1->fullname) ? $member1->fullname : (!empty($member1->groupname) ? $member1->groupname : (!empty($member1->rolename) ? $member1->rolename : $member1->username));
		$name2 = !empty($member2->fullname) ? $member2->fullname : (!empty($member2->groupname) ? $member2->groupname : (!empty($member2->rolename) ? $member2->rolename : $member2->username));
		return strcmp($name1, $name2);
	}
	
	static function sortMembers($members) {
		usort($members, array('Utils', 'compareMembers'));
		return $members;
	}
	
	static function hashtable($arrays) {
		$hashtable = array();
		foreach ($arrays as $item)
			if (is_array($item))
				$hashtable[$item[0]] = $item[1]; else
				$hashtable[$item] = $item;
		return $hashtable;
	}
	
	static function getMimeTypeByExtension($ext) {
		$file = file(LIBS_ROOT.'/application/mime-types.txt');
		foreach ($file as $line) {
			$matches = preg_split("/[\t]+/", $line);
			$mimeType = $matches[0];   
			$extensions = explode(",", $matches[1]);
			for ($i = 0; $i < count($extensions); $i++){
				$extensions[$i] = trim($extensions[$i]);
			}
			if (in_array($ext, $extensions)){ 
				return $mimeType;
			}
		} 
		return "application/octet-stream";
	}   
	
	static function getExtension($path) {
		$pieces = explode('.',$path);
		if(count($pieces) > 1){
			$ext = array_pop($pieces);			
		} else {
			$ext = '';
		}
		
		return $ext;
	}
	
	static function debug($var, $email = false){
		if(!$email){
			$email = 'orlik.michal@gmail.com';
		}
		//self::writeToFile(Zend_Debug::dump($var, null, false), SERVER_ROOT.'/application/debug.txt');
		mail($email, 'debug', Zend_Debug::dump($var));     
	}
	
	static function isMe(){
		return $_SERVER['REMOTE_ADDR'] == '213.220.255.68';
	} 
	
	static function getIP(){
	    /*
	    This function checks if user is coming behind proxy server. Why is this important?
	    If you have high traffic web site, it might happen that you receive lot of traffic
	    from the same proxy server (like AOL). In that case, the script would count them all
	        as 1 user.
	    This function tryes to get real IP address.
	    Note that getenv() function doesn't work when PHP is running as ISAPI module
	    */
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        }
        elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        }
        elseif (getenv('HTTP_X_FORWARDED')) {
            $ip = getenv('HTTP_X_FORWARDED');
        }
        elseif (getenv('HTTP_FORWARDED_FOR')) {
            $ip = getenv('HTTP_FORWARDED_FOR');
        }
        elseif (getenv('HTTP_FORWARDED')) {
            $ip = getenv('HTTP_FORWARDED');
        }
        else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
	}
	
	function loadUrl($url, $timeout = 1){    
		
		//CURLOPT_CONNECTTIMEOUT = $timeout;    
		define('CURLOPT_CONNECTTIMEOUT', $timeout); 
		define('CURLOPT_TIMEOUT', $timeout);   
		
		 // create curl resource
        $ch = curl_init();

        // set url
        curl_setopt($ch, CURLOPT_URL, $url);
        
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); 
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);   
        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $output = curl_exec($ch);

        // close curl resource to free up system resources
        curl_close($ch);    
        return $output;   
	}
	
}

function array_swap(&$array, $key1, $key2) {
   $v1 = $array[$key1];
   $v2 = $array[$key2];
   $out = array();
   foreach($array as $i => $v) {
		if ($i == $key1) {
			$i = $key2;
			$v = $v2;
		} elseif ($i == $key2) {
			$i = $key1;
			$v = $v1;
		}
		$out[$i] = $v;
	}
	return $out;
}

function pr($a){
	echo '<xmp>';
	print_r($a);
	echo '</xmp>';
}

function e($v){
	var_dump($v);
	// Zend_Debug::dump($v, null, true);	
}

function ee($v){	
	Zend_Debug::dump($v, null, true);	 
}
?>