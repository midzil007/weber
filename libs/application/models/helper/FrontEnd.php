<?
/**
 * Třída pro praci s vystupy na webu
 * @package helper
 */
 
class helper_FrontEnd {   
	
	public static $tTime;  
	public static $tToday;
	public static $tYeasterday;
	
	static function getCurrentFirstLevel($path, $view){	
		$parts = explode('/', $path);
		if($view->language && $view->language != 'cz'){
			return '/' . $parts[1] . '/' . $parts[2];		
		} else {						  
			return '/' . $parts[1];		 
		}
		
	} 
	
	function isMobileDevice(){
		$useragent = $_SERVER['HTTP_USER_AGENT']; 
		return preg_match('/android|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i',substr($useragent,0,4));
	}
	
	static function getCurrentSecondLevel($path, $view){								
		$parts = explode('/', $path); 
		if($parts[2]){
			return '/' . $parts[1] . '/' . $parts[2];
		} else {
			return self::getCurrentFirstLevel($path, $view); 
		}		
	}

	function prepareResizeText($text)
	{
		preg_match_all( '@src="([^"]+)"@' , $text, $match );
		$src = array_pop($match);
		$arrayimages = array();
		 foreach ($src as $value) {
			$arrayimages[] = '<div data-src="'.$value.'" data-alt="crispy" class="img-wrap js-crispy"><noscript><img src="'.$value.'" alt="Crispy"></noscript>
        					</div>';
		 }
		$text2 = preg_replace("/<img[^>]+\>/i", "(image) ", $text); 
		$textitk = explode('(image)', $text2);
		$count = count($textitk);
		$inc = 0;
		 $textfin = array();

		 foreach ($textitk as $key => $value) {
			// otaznik
			if($key > 0 )
			{
				$textfin[] = $value.$arrayimages[$inc];
				$inc++;
		 	}
			else{
				$textfin[] = $value.$arrayimages[$inc];
				$inc++;
			}
		 }
		
		//pr(implode('', $textfin));
		// die;
		$textfin[] ='<p class="clear"></p>';
		//pr($textfin);
		 return implode('', $textfin);
	}
	
	static function isTemplateAwailable($view, $template){
		$path = $view->config->serverRoot . '/application/views/scripts/web/' . $template;
		return file_exists($path);
	}
	
	function initTimeVars(){
		self::$tTime = time();
		self::$tToday = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
		self::$tYeasterday = mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"));
	}
	
	
	static function getSubdomain(){
		$parts = explode('.', $_SERVER['HTTP_HOST']);
		if(count($parts == 3)){
			list($subdomain, $domain, $ext) = $parts;
		} else {
			$subdomain = 'www';
			list($domain, $ext) = $parts;
		}
		return $subdomain;
	}
		
	function reduceText($txt, $limit = 30, $title = true, $fullWords = false){
		 
		$txt = str_replace("&nbsp;&nbsp;", "&nbsp;", $txt); 
	
		//e($limit);  e(mb_substr($txt, $limit, 1));   
		if($fullWords){  
			for ($i = 0; $i < 30; $i++){ 
				// e($limit);    
				if(mb_substr($txt, $limit, 1, 'utf-8') != ' '){
					$limit++;
				} else { 
					break;   
				} 
			}
			
		}
		//e($limit);  e(mb_substr($txt, $limit, 1)); 
		if(mb_strlen($txt) > $limit){ 
			$nt = mb_substr($txt, 0, $limit, 'utf-8') . '&hellip;' ;     
		} else { 
			$nt = mb_substr($txt, 0, $limit, 'utf-8'); 
		}
		
		//$nt = EReg_Replace(" ", "&nbsp;", $nt);

		if($title){ 
			return '<span title="' . $txt . '">' . $nt . '</span>';
		} else {
			return $nt;
		}
	}
	
	static function getPath($view, $nodePath){
		if(!$view){
			$conf = Zend_Registry::getInstance()->config;
		} else {
			$conf = $view->config;
		}
		
		
		if($conf->domainPath){ 
			return self::getPathOneAdmin($view, $nodePath);
		}     
		
		if($conf->subdomainFirstLevel){
			
			$pathParts = explode('/', $nodePath);
			$subdomain = $pathParts[1];
			//$x = count($pathParts);
			//e($pathParts);
			unset($pathParts[0]);
			unset($pathParts[1]);
			if(count($pathParts)){
				$path = implode('/', $pathParts);
			} else {
				$path = '';
			}
			$subdomain = $subdomain?$subdomain:'www';
			return $conf->protocol . $subdomain . '.' . $conf->host . '/' . $path;			
		} else {
			return Utils::getWebPath($nodePath);
		}
	}
	
	static function getPathOneAdmin($view, $nodePath, $switch = false){  
		  
		if(!$view){
			$conf = Zend_Registry::getInstance()->config;
		} else {
			$conf = $view->config;
		}
				
		
		$parts = explode('/', $nodePath);
		$level = count($parts); 
		$d = array_shift($parts);
		$d = array_shift($parts); 
		
	
		if($switch && $conf->instance->domainsSwitch){  
			$switch = $conf->instance->domainsSwitch->toArray();
			if($switch[$d]){
				$d = $switch[$d]; 
			} 
		}
		
		$d = str_replace('-cz', '.cz', $d ); 
		
		$nodePath = implode('/', $parts); 
		$nodePath = '/' . $nodePath; 
		
		
		if($view->isMobile){ 
			return $conf->protocol . 'm.' . $d . $nodePath;	    
		} else {
			return $conf->protocol . 'www.' . $d . $nodePath;	   	
		}
		
	}
	 
	function getDomainFromPath($path){
		$parts = explode('/', $path);
		return $parts[1];  
	}
	
	function getPathSansDomain($view, $path){
		$parts = explode('/', $path);
		unset($parts[1]);
		return implode('/', $parts);    
	}
	
	static function getNodeFullPath($view){
		if($view->config->subdomainFirstLevel){ 
			$subdomain = self::getSubdomain();
			
			if($view->curentPath == '/'){
				$view->curentPath = '';
			}
			return '/' . $subdomain . $view->curentPath;			
		} else {
			return $nodePath;
		}
	}
	
	
	static function m2($txt){	
		return str_replace('m2', 'm&sup2;', $txt);		
	}	
		
	static function echoIfExists($txt, $term = '<br />', $NAtext = '', $prefix = '', $suffix = '', $echo = true){	
		if($txt && $txt!=-1){
			$txt = $prefix . trim($txt) . $suffix . $term; 
		} else {
			$txt = $NAtext;
		}
		
		if($echo){
			echo $txt;
		} else {
			return $txt;
		}
	}
	
	static function checkChildren( $children, $checkShownIn = 1 ){
		foreach ($children as $id => $item){
			if(!$item->hasPublishedContent()){
				unset($children[$id]);
			}
			if($checkShownIn){
				if($item->showInNavigation != $checkShownIn){
					unset($children[$id]);
				}
			}
		}
		return $children;
	}
	static function is_ie()
	{
	    if (isset($_SERVER['HTTP_USER_AGENT']) && 
	    (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false))
	        return true;
	    else
	        return false;
	}

	static function isActive($path, $nodePath, $fullComparison = false){		
		$c = '';
		if($fullComparison){
			$active = $path == $nodePath;
		} else {
			$active = self::isPathInActivePath($nodePath, $path);
		}
				
		return $active;		
	}
	
	static function setActive($path, $nodePath, $fullComparison = false, $class = 'active'){		
		$active = self::isActive($path, $nodePath, $fullComparison);		
		if($active){
			$c = ' class="' . $class . '"';
		}
		
		return $c;		
	}
	
	static function isPathInActivePath($path, $fullPath){
		$fullPath = $fullPath?$fullPath:'/';
		$path = $path?$path:'/';
		if(strpos($fullPath, $path) === false){
			return false;
		} else {
			return true;
		}		
	}
	
static function generateBreadCrumbs($view, $dontshow = array(), $homeTitle = 'Úvod', $replace = array()){   
		if($view->curentNode){
			$paths = $view->curentNode->getUserFriendlyPath($dontshow, $homeTitle);   
			$xhtml = '';
			// pr($replace);  
			foreach ($paths as $path => $title){
				if($replace[$path]){
					$title = $replace[$path][1]; 
					$path = $replace[$path][0];
					  
				}  
				if($view->domainName && $path != '/jura'){ 
					$path = self::getPathOneAdmin($view, $path); 
				} 
				if($title !='e-shop'){
					if($homeTitle && $path=="/jura")
					$title = $homeTitle;     
					$xhtml .= '<a href="' . $path . '">' . $title . '</a> » ';
					}   
			}
			if($view->curentNode->title == $view->config->webhost || $view->curentNode->title == $view->config->webhostDevel ){
				$view->curentNode->title = $homeTitle;
			}
			$parentTitle = $view->curentNode->getPropertyValue('pageTitle')?$view->curentNode->getPropertyValue('pageTitle'):$view->curentNode->title;
			$xhtml .= '<a href="' . $view->curentNode->path . '">' . $parentTitle . '</a>';
			if($view->productNode){   
				$xhtml .= ' » <a href="' . $view->productNode->path . '">' . $view->productNode->title . '</a>';
			}
			return 	$xhtml;				
		} else { 
			return 'Chyba'; 
		}	
	}
	
	
	static function redirect($url, $code = 0, $exit = true){		
		switch ($code){
			case 404: 
				header('HTTP/1.0 404 Not Found'); 
				header('Status: 404 Not Found');  
				if($exit){ exit(); }  
				break;	 
			default:
				$redirector = Zend_Registry::getInstance()->redirector;
				$redirector->gotoUrlAndExit($url, array('code' => $code));	
				break;		
		}
	}
	
	static function redirect2($url, $code = 0){		
		header("Location: " . $url, true, $code); exit(); 
	}
	  
	static function price($string, $rad = ' ',$round = false) {
		$price = ""; 
		
		$string = (string) $string;
		
		if(strpos($string, '.')){
			list($string, $float) = explode('.', $string);  
			$float = ','.$float;
		}
		
		
		
		$string = str_replace(array(' ', '.'), '', $string);
		 	
		$len = strlen($string)-1;
		$idx = $len;
		while($idx >= 0) {
			$price = $string{$idx}.$price;
			if(($len-$idx)%3 == 2 && $idx>0) $price = $rad.$price;
			$idx--;
		} 
		if($round)
		{ 
			return str_replace(" ", "&nbsp;", $price);
		}
		else{
			return str_replace(" ", "&nbsp;", $price) . $float;
		}
	
	}
	
	static function showPhoto($photoPropertyValue, $fotoName, $print = true){		
		$photo = content_SFSFile::getFileFromProperty($photoPropertyValue);		
		list($nodeId, $n) = content_SFSFile::parseSFSPath($photo['path']);
		$photoPath = Zend_Registry::getInstance()->config->sfFolder . '/' . $nodeId . '/' . $fotoName . '-' . content_SFSFile::getFileExtension($n) . '/' . $n;
		
		if ($photo['path']){		
			if($print){
	    		return '<img src="' . $photoPath . '" alt="' . $photo['name'] . '" />';
			} else {
				return array('path' => $photoPath, 'name' => $photo['name']);
			}
		}
	}
	
	static function showSinglePhoto($photoPropertyValue, $print = true){		
		$photo = content_SFSFile::getFileFromProperty($photoPropertyValue);					
		if ($photo['path']){		
			if($print){
	    		return '<img src="' . $photo['path'] . '" alt="' . $photo['name'] . '" />';
			} else {
				return array('path' => $photo['path'], 'name' => $photo['name']);
			}
		}
	}
	
	static function getPhoto($propertyName, $content, $fotoName, $fotoFullName = '' ){		
		$photos = $content->getFilesNames($propertyName);
		return self::getFirstPhoto($photos, $fotoName, $fotoFullName);
	}
	
	static function getFirstPhoto($photos, $fotoName, $fotoFullName = '' ){		
		return self::getResizedPhotos($photos, $fotoName, $fotoFullName, 1); 
	}
	
	static function getSecondPhoto($photos, $fotoName, $fotoFullName = '' ){		
		return self::getResizedPhotos($photos, $fotoName, $fotoFullName, 2);  
	} 
	
	static function getSinglePhotoFull($val){ 
		$img = content_SFSFile::getFileFromProperty($val); 
		return array('path' => content_SFSFile::getFileFullPath($img['path']), 'name' => $img['name']);  
	}
	
	static  function getResizedPhotos($photos, $fotoName, $fotoFullName = '', $only = 0, $limit = 999){ 
		$conf = Zend_Registry::getInstance()->config;
		$ret = array();
		$x = 0;
		foreach ($photos as $path => $name){
			if(!$path){ continue; }		
			$x++;		
			
			list($nodeId, $n) = content_SFSFile::parseSFSPath($path);
			if($fotoFullName){
		    	$photoPathFull = $conf->sfFolder . '/' . $nodeId . '/' . $fotoFullName . '-' . content_SFSFile::getFileExtension($n) . '/' . $n;
			}
		    $photoPath = $conf->sfFolder . '/' . $nodeId . '/' . $fotoName . '-' . content_SFSFile::getFileExtension($n) . '/' . $n; 
		    
		    $photoPath = content_SFSFile::getFileFullPath($photoPath); // fullPATH FIX        
		    $photoPathFull = content_SFSFile::getFileFullPath($photoPathFull); // fullPATH FIX    
		    $photoTitle = $name;   

		      
			$nm = Zend_Registry::getInstance()->nodeMeta;  
      		$cm = $nm->getMetaById($nodeId);
      
		    $photo = array( 
				'path' => $photoPath,
				'name' => $name,
				'meta' => $cm,
				'fullPath' => $photoPathFull
			);
			 
			if($only && $x == $only){
				return $photo;
			}
			$ret[] = $photo;
			if($x >= $limit){ 
				break; 
			}
		}
		return $ret; 
	}
	
	static function getFirstFile($files){		
		$conf = Zend_Registry::getInstance()->config;
		
		foreach ($files as $path => $name){
			
			if(!$path){ continue; }								
			break;
		} 
		return array($path, $name);
	}
	
	static function getTarget($content){		
		$xhtml = '';
		if($content){
			$target = $content->getPropertyValue('target');
			if(!$target){
				$target = '_self';
			}
			
			if($target != '_self'){
				$xhtml .= ' target="' . $target . '"';	
			}
		}
		return $xhtml;
	}
	
	static function getSelectedNodes($view, $propertyName){		
		$selected = helper_MultiSelect::getMultiSelectValues($view->content->getPropertyValue($propertyName));
    	$nodes = array();
    	if(count($selected)){
	    	foreach ($selected as $nodeId){
	    		$n = $view->tree->getNodeById($nodeId);
	    		if($n){
	    			$nodes[] = $n;
	    		}
	    	}
    	}
    	return $nodes;
	}
	
	static function back($parentNodeId, $curentPath, $view){
		$ref = $_SERVER['HTTP_REFERER'];
		$subdomain = helper_FrontEnd::getSubdomain();
		
		if($subdomain && $ref){
			return $ref;
		} elseif(strpos($ref, Zend_Registry::getInstance()->config->webhost) > 0 && $ref){
			return $ref;
		} else {
			return self::getPath($view, Zend_Registry::getInstance()->tree->getNodeById($parentNodeId)->path);
		}
	}
	
	static function drawChecboxGroupValue($value, $options){
		$values = helper_MultiSelect::getMultiSelectValues($value);
		$valuesTxt = array();
		foreach ($values as $val){
			$valuesTxt[] = $options[$val];
		}
		$value = implode(', ', $valuesTxt);
		return $value;
	}
	
	static function drawSelectValue($value, $options, $undefinedText = ''){
		if($value == -1){
			return $undefinedText;
		}		
		return $options[$value];
	}
	/*
	function getNiceDateTime($time){
		$time = strtotime($time);
		if($time >= self::$tToday){
			$t = substr($time, 10, 6);
		} elseif($time >= self::$tYeasterday){
			$t = 'včera ' . substr($time, 10, 6);
		} else {
			$t = Utils::formatDate($time, 'd.m.Y H:i');
		}
			
		return $t;
	}
	*/
	function getNiceDateTime($time, $format = 'd.m.Y H:i', $todayText = 0){
		$time = strtotime($time);
		if($time >= self::$tToday){
			$t = date('H:i', $time);
			if($todayText){
				$t = 'dnes ' . $t;
			}
		} elseif($time >= self::$tYeasterday){
			$t = 'včera ' . date('H:i', $time);
		} else {
			$t = date($format, $time);
		}
			
		return $t;
	}
	
	function getNiceDateTime2($time, $format = 'd.m.Y H:i', $todayText = 0){
		$time = strtotime($time);
		if($time >= self::$tToday){
			$t = date('H:i', $time);
			if($todayText){
				$t = 'dnes'; 
			}
		} elseif($time >= self::$tYeasterday){
			$t = 'včera ' . date('H:i', $time);
		} else {
			$t = date($format, $time);
		}
			
		return $t;
	}
	
	
	static function getUrl($view, $unset, $newVal){
	
		$a = (array) $_GET; 
		unset($a[$unset]);
		$a[$unset] = $newVal;  
		
		return Utils::addUrlParams($view->path, $a);
	}
	
	
	function getContentTypeByPath($view, $path) { 
		$reg = Zend_Registry::getInstance();		
		$db = $reg->db;		
		$_domain = $reg->config->webhost; 
		if($_domain && $reg->config->oneAdmin){   
			 $domain = str_replace('.cz', '-cz', $_domain); 
			 $domain = str_replace('www.', '', $domain);
			 $path = '/' . $domain . $path;
		}   
		  
		if($reg->languages->isMultiLanguage){
			$pathName = $reg->languages->fullLangPrefix . 'path';
		} else {
			$pathName = 'path';
		}
		 		 
		$id = $db->fetchOne("SELECT id FROM Nodes WHERE $pathName=? OR path=? ORDER BY id Asc",array($path, $path));	   
		
		if($id){
			$ctype = $db->fetchAll("SELECT c_type FROM NodesContents WHERE n_id = ?", $id);	   
			if(count($ctype) >= 1){ 
				$type = $view->tree->getNodeById($id)->getPublishedContent()->_name;
			} else { 
				$type = $ctype[0]['c_type']; 
			}
		} else {
			$type = false; 
		}
		return $type;
	}
	
}
?>
