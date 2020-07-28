<?
class Helpers {
	
	public static function getLastPathPart($path){
		$parts = explode('/', $path);
		$last = array_pop($parts);
		if(strlen($last) == 2){
			$last = array_pop($parts);
		}
		return $last;
	}
	
	public static function contactMail(){
		$s = Zend_Registry::getInstance()->settings;
		
		return '<a href="mailto:' . $s->getSettingValue('outcommingEmail') . '" title = "' . $s->getSettingValue('outcommingEmailName') . '">' . $s->getSettingValue('outcommingEmail') . '</a>';	
	}
	
	static function back($parentNodeId, $curentPath){
		$ref = $_SERVER['HTTP_REFERER'];
		if(strpos($ref, Zend_Registry::getInstance()->config->webhost) > 0 && $ref){
			return $ref;
		} else {
			return Zend_Registry::getInstance()->tree->getNodeById($parentNodeId)->path;
		}
	}
	
	public static function redirect($relativePath, $absolute = 0){
		if($absolute){
			$url = $relativePath;
		} else {
			$url = Zend_Registry::getInstance()->config->protocol . Zend_Registry::getInstance()->config->webhost . $relativePath;
		}
		header ("Location: $url");
		exit();
	}
	
	public static function initFormValuesFromObject($o = null){
   		$oo = new stdClass();
   		if(is_array($o)){
   			$data = $o;
   		} else {
   			$data = get_object_vars($o);
   		} 
   		if(is_array($data)){ 
	   		foreach ($data as $k => $v){		
	   			if(isset($v)){
					$oo->$k = $v;							
	   			}
			}
   		} 
		return $oo;
   	}
   	/*
   	public static function setValuesToObject($o, $data){
   		$oo = new stdClass();
   		if(is_array($data)){
   			$data = $data;
   		} else {
   			$data = get_object_vars($data);
   		}
   		foreach ($data as $k => $v){		
   			if(isset($v)){
				$oo->$k = $v;							
   			}
		}
		return $oo;
   	}
   	*/
   	public static function setValuesToObject($o, $data){
   		if(is_array($data)){
   			$data = $data;
   		} else {
   			$data = get_object_vars($data);
   		}
   		foreach ($data as $k => $v){		
   			//if(isset($v)){
				$o->$k = $v;	 						
   			//}
		}
		return $o;
   	}
   	
	
	public static function addMessage($state, $txt, $view)	
    { 
    	$view->currentState = $state;
    	
    	if(!is_array($view->messages)){
    		$view->messages = array();
    	}
    	
    	if ($state){
    		self::addInfoMessage($txt, $view);
    	} else {
    		self::addErrorMessage($txt, $view);
    	}
    }
    
	public static function addErrorMessage($txt, $view)
    {   
    	$view->messages[] = 'e-'.$txt;
    }
    
    public static function addInfoMessage($txt, $view)
    { 
    	$view->messages[] = 'i-'.$txt;
    }
    
    public static function prepareEmail($mail, $txt, $onlyContents = false, $showHead = true, $headColor = '0f4096', $textColor = '708490', $bgColor = 'ffffff'){
    	/*
	    	$image = file_get_contents(Zend_Registry::getInstance()->config->htdocsRoot . '/images/logo.gif');
	    	//e(Zend_Registry::getInstance()->config->htdocsRoot . '/images/logo.gif');
			$at = $mail->createAttachment($image);
			$at->type        = 'image/gif';
			$at->disposition = Zend_Mime::DISPOSITION_INLINE;
			$at->encoding    = Zend_Mime::ENCODING_BASE64;
			$at->filename    = 'logo.gif';
		*/
    	
    	$base = Utils::getWebUrl();
    	//$txt = str_replace('//', '/', $txt);
		$txt = str_replace('http:/', 'http://', $txt);
    	$txt = str_replace('"/data', '"' . $base . '/data', $txt);
    	$txt = str_replace('"/images/nl', '"' . $base . '/images/nl', $txt);
    	$txt = str_replace('"/data/nlimages', '"' . $base . '/data/nlimages', $txt);
    	$txt = str_replace('href="/', 'href="' . $base . '/', $txt);
    	      
    	$txt = str_replace('src="/', 'src="' . $base . '/', $txt);
    	
    	$t = '';
    	$t = '<div id="page" style="color:#' . $textColor . '; font-size:12px;">';
    	
    	if($showHead){
    		$t .= '<div id="head"><h1 style="color:#' . $headColor . '; font-size: 14px;">' . Zend_Registry::getInstance()->config->instance->title . '</h1></div>';
    	}
    	
    	$t .= '<div  style="color:#' . $textColor . '; font-size:12px;">' . $txt . '</div>
				
			</div>
    	';
    	if($onlyContents){
    		return $t;
    	}
    	
		return  '
    		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
			<title>' . Zend_Registry::getInstance()->config->instance->title . '</title>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<meta http-equiv="Content-Language" content="cs" />
			<style type="text/css" media="all">		 
				body {
					margin: 0px;
					padding: 0px 5px;
					background: #'. $bgColor . ';
					font-family: Arial, sans-serif;
					font-size: 12px; 
					color: #' . $textColor . ';
				}	 
				a {
					color:#' . $headColor . ';  
				}
				
				h1, h2, h3, h4, h5, h6 {
					color:#' . $headColor . ';
					padding:0px; 
				}
				
				p {
					padding:0px;
					margin:1px 0px 7px 0px;  
				}  
				 
				h1 {
					font-size: 16px;
					margin: 2px 0 3px 0;
					color:#' . $headColor . ';
					font-weight:bold;
				}
				
				h2 {
					font-size: 15px;  
					margin: 9px 0 1px 0; 
					color:#' . $headColor . ';
				}	
				
				h3 {
					font-size: 14px;
					color:#' . $headColor . ';
					margin: 11px 0 0px 0;  
				}	
				
				h4 {
					font-size: 13px;  
					margin: 5px 0 0px 0; 
					color:#' . $headColor . ';   
				}	
				
				table { font-size: 12px; color: #' . $textColor . '; }
				table td { font-size: 12px; color: #' . $textColor . '; vertical-align:top; padding:4px;}
				table td {
				    border-collapse: collapse; 
				} 
			</style>
			</head>
			
			<body>' . $t . '
			</body>
			</html>
    	';
    }
}
?>
