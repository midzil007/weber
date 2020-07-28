<?
if($_GET['showMePHPinfo'] || $_GET['showmephpinfo']){ 
	phpinfo();
	die(); 
}

if((isset($_GET['XDEBUG_TRACE']) || strpos($_SERVER['REQUEST_URI'], 'XDEBUG_TRACE')) && function_exists("xdebug_start_trace")){  
	xdebug_start_trace();  
}   

ini_set("session.cookie_httponly", 1);
session_set_cookie_params(0, NULL, NULL, NULL, TRUE);  

// PRAVA
$auth = Zend_Auth::getInstance();
$acl = new security_CmsAcl($auth); // see   
 
// FRONTEND
$controller = Zend_Controller_Front::getInstance(); 
$controller->throwExceptions($config->throwExceptions);

$controller->setBaseUrl('');
$controller->setParam('noViewRenderer', true); 
$controller->setParam('auth', $auth);
$controller->setParam('acl', $acl);

$router = $controller->getRouter(); // returns a rewrite router by default
$view = new Zend_View();
$view->setEncoding('utf-8');
$view->setEscape('htmlentities');

if($isAdmin){
	$controller->registerPlugin(new security_CmsAuthPlugin($auth, $acl));    
	
	$view->addScriptPath( LIBS_ROOT . '/application/views/scripts/web/templates/Containers/');
	$view->addScriptPath( SERVER_ROOT . '/application/views/scripts/web/templates/Containers/');
	
	$view->addScriptPath( LIBS_ROOT . '/application/views/scripts/web/templates/Contents/App/');
	$view->addScriptPath( SERVER_ROOT . '/application/views/scripts/web/templates/Contents/App/');
	
	$view->addScriptPath( LIBS_ROOT . '/application/views/scripts/web/templates/Contents/App/');
	$view->addScriptPath( SERVER_ROOT . '/application/views/scripts/web/templates/Contents/App/');
	
	$view->addScriptPath( LIBS_ROOT . '/application/views/scripts/cms/templates/Containers/');
	$view->addScriptPath( SERVER_ROOT . '/application/views/scripts/cms/templates/Containers/');
	
	$view->addScriptPath( LIBS_ROOT . '/application/views/scripts/web/');
	$view->addScriptPath( SERVER_ROOT . '/application/views/scripts/web/');
	
	$view->addScriptPath( LIBS_ROOT . '/application/views/scripts/cms/');
	$view->addScriptPath( SERVER_ROOT . '/application/views/scripts/cms/');	  
	
	$controller->setControllerDirectory(		
		array(
			'cms' => LIBS_ROOT . '/application/controllers/cms',			
			'default' => LIBS_ROOT . '/application/controllers/cms'
		)		 
	);
	 		
} else {
	 
	$view->addScriptPath( LIBS_ROOT . '/application/views/scripts/cms/');
	$view->addScriptPath( SERVER_ROOT . '/application/views/scripts/cms/');
	$view->addScriptPath( LIBS_ROOT . '/application/views/scripts/web/');
	$view->addScriptPath( SERVER_ROOT . '/application/views/scripts/web/');
	$view->addScriptPath( LIBS_ROOT . '/application/views/scripts/web/templates/Containers/');
	$view->addScriptPath( SERVER_ROOT . '/application/views/scripts/web/templates/Containers/');
	$view->addScriptPath( LIBS_ROOT . '/application/views/scripts/web/templates/Contents/App/');
	$view->addScriptPath( SERVER_ROOT . '/application/views/scripts/web/templates/Contents/App/');
	 
	if($isMobile){
		$view->addScriptPath( LIBS_ROOT . '/application/views/scripts/web/m/');
		$view->addScriptPath( SERVER_ROOT . '/application/views/scripts/web/m/');
		$view->addScriptPath( LIBS_ROOT . '/application/views/scripts/web/m/templates/Containers/');
		$view->addScriptPath( SERVER_ROOT . '/application/views/scripts/web/m/templates/Containers/');
		$view->addScriptPath( LIBS_ROOT . '/application/views/scripts/web/m/templates/Contents/App/');
		$view->addScriptPath( SERVER_ROOT . '/application/views/scripts/web/m/templates/Contents/App/');
	}  
	   
		$controller->setControllerDirectory(
			array( 
				 SERVER_ROOT . '/application/controllers/instance',
				'instance' => LIBS_ROOT . '/application/controllers/instance'
			),  
			'default'
		);	
		
		$router->removeDefaultRoutes();	
		$route = new Zend_Controller_Router_Route(
		    '*',
		    array('module' => 'instance', 'controller'=>'web', 'action'=>'index')
		);
		$router->addRoute('default', $route);       
		$controller->setDefaultModule('instance');        
		$controller->setParam('useDefaultControllerAlways', true);
		
		// $controller->setControllerDirectory(SERVER_ROOT . '/application/controllers/instance');
		// pr($controller); die();    
}		
 
		 

/* CACHE */
if($config->cache->useCache){
	require_once 'Zend/Cache.php'; 	  
	
	//$oCacheLog =  new Zend_Log();
	//$oCacheLog->addWriter( new Zend_Log_Writer_Stream( $config->dataRoot . '/tmp/log.txt' ) );
 
	/*
	// FILE
	$frontendOptions = array(  
		'lifetime' => $config->cache->lifetime, // cache lifetime of 2 hours  
		'automatic_serialization' => true  
	);  
	$backendOptions = array(  
		'cache_dir' => $config->dataRoot . '/tmp/' // Directory where to put the cache files  
	);   
	 */
	
	/* MEMCACHE */			
	$frontendOptions = array(  
		'lifetime' => $config->cache->lifetime, // cache lifetime of 2 hours  
		'automatic_serialization' => true
	);  
	$backendOptions = array(
		'servers' => array(array(
		'host' => "127.0.0.1",
		'port' => "11211",
		'persistent' => Zend_Cache_Backend_Memcached::DEFAULT_PERSISTENT, //true by default
		'cache_id_prefix' => $config->cache->identificator,
		'doNotTestCacheValidity' => true
		)), 
		'compression' => false,
		);
		  
	$cache = Zend_Cache::factory($config->cache->frontend, $config->cache->backend, $frontendOptions, $backendOptions);
	$cache->identificator = $config->cache->identificator;
	$cache->isAdmin = $isAdmin; 
	$memcache = $view->cache = $registry->cache = $tree->cache = $cache;  
	
}

$registry->nodeMeta = new NodeMeta(); 

$controller->setParam('view', $view);
//e(session_get_cookie_params());
  
 
if($_GET['profillerEnableXX']){ 
	$db->getProfiler()->setEnabled(true); 
	//unset($_GET['profillerEnableXX']);  
	unset($view->inputGet->profillerEnableXX);   
}  
?>