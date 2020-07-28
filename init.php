<?php
if ((float) phpversion() < 5) {
	die('PHP 5.0 or higher required');
}

//povol schema.org anotaci v šablonách ... presunout do configu
//define('SHOW_SEMANTIC', true);

setlocale(LC_COLLATE, 'cs_CZ.ISO8859-2');
if ($_GET['fuck'] == 1) {
	error_reporting(E_ERROR | E_ALL);
} else {
	error_reporting(E_ERROR | E_PARSE);
}
ini_set('display_errors', On);

$root = str_replace('/public', '', $_SERVER['DOCUMENT_ROOT']);

define('SERVER_ROOT', $root);
define('LIBS_ROOT', $root . '/libs');
define('COMMON_ROOT', $_SERVER['DOCUMENT_ROOT']);

// init
set_include_path('.'
	. PATH_SEPARATOR . SERVER_ROOT . '/application/models/'
	. PATH_SEPARATOR . SERVER_ROOT . '/application/modules/'
	. PATH_SEPARATOR . SERVER_ROOT . '/application/controllers/'
	. PATH_SEPARATOR . SERVER_ROOT . '/application/'
	. PATH_SEPARATOR . SERVER_ROOT . '/application/classes'
	. PATH_SEPARATOR . SERVER_ROOT . '/Zend'
	. PATH_SEPARATOR . SERVER_ROOT . '/PEAR'
	. PATH_SEPARATOR . LIBS_ROOT . '/application/models/'
	. PATH_SEPARATOR . LIBS_ROOT . '/application/modules/'
	. PATH_SEPARATOR . LIBS_ROOT . '/application/controllers/'
	. PATH_SEPARATOR . LIBS_ROOT . '/application/controllers/cms/'
	. PATH_SEPARATOR . LIBS_ROOT . '/application/'
	. PATH_SEPARATOR . LIBS_ROOT . '/application/classes'
	. PATH_SEPARATOR . LIBS_ROOT . '/Zend'
	. PATH_SEPARATOR . LIBS_ROOT . '/PEAR'
	. PATH_SEPARATOR . SERVER_ROOT
	. PATH_SEPARATOR . LIBS_ROOT
	. PATH_SEPARATOR . get_include_path());

require_once('Zend/Loader.php');
require_once('cmsLoader.php');
Zend_Loader::registerAutoload();

// KONFIG
$pos     = strpos($_SERVER['REQUEST_URI'], '/cms');
$isAdmin = true;
if ($pos === false) {
	$isAdmin = false;
}

$time = 60 * 60 * 24 * 365;/*= 31536000 */

$configArray = array(
	'isAdmin'            => $isAdmin,
	'throwExceptions'    => true,
	'useCommonResources' => true,
	'common'             => COMMON_ROOT,
	'commonCmsPath'      => COMMON_ROOT . '/cms',
	'cmsFolderPath'      => COMMON_ROOT . '/cms',
	'sessionAlive'       => 1440000, /* seconds */ //40h
	'sessionWebAlive'    => 3600, /* seconds */ //1h
	'webhost'            => 'www.americkygril.cz',
	'protocol'           => 'https://',
	'protocolhttp'       => 'http://',
	'serverRoot'         => SERVER_ROOT,
	'htdocsRoot'         => $_SERVER['DOCUMENT_ROOT'],
	'dataRoot'           => $_SERVER['DOCUMENT_ROOT'] . '/data',
	'fsRoot'             => $_SERVER['DOCUMENT_ROOT'] . '/data/sharedfiles',
	'sfFolder'           => '/data/sharedfiles',
	'form'               => array(
		'useDojo'           => true
	),
	'agmo'         => array(
		'merchant'    => '135511',
		'paymentsUrl' => 'https://payments.comgate.cz/v1.0/create',
		'test'        => false,
		'secret'      => 'rrsNhZr0WoUN5IpfvHOvN5u4RRyKWvyz',
	),
	'view'                    => array(
		'templatesDir'           => 'templates/',
		'containerDir'           => 'templates/Containers/',
		'contentsDir'            => 'templates/Contents/',
		'appDir'                 => 'templates/Contents/App/',
		'overviewsDir'           => 'templates/Contents/Overviews/',
		'adminTemplatesFullpath' => '/application/views/scripts/cms/',
		'showSemantic'           => true
	),
	'cache'          => array(
		'useCache'      => 0,
		'lifetime'      => 1440, // 4h
		'frontend'      => 'Output',
		'backend'       => 'Memcached',
		'identificator' => 'americkygril',
	),
	'TableHelper'     => array(
		'listingOptions' => array('5' => '5', '10' => '10', '15' => '15', '20' => '20', '50' => '50', '100' => '100', '500' => '500', '99900' => 'Vše'),
		'defaultListing' => 500
	),
	'tree'       => array(
		'structure' => $_SERVER['DOCUMENT_ROOT'] . '/data/tree/structure.json',
		'files'     => $_SERVER['DOCUMENT_ROOT'] . '/data/tree/files.json',
		'help'      => $_SERVER['DOCUMENT_ROOT'] . '/data/tree/help.json',
		'helpFull'  => $_SERVER['DOCUMENT_ROOT'] . '/data/tree/helpFull.json',
		'sysPages'  => $_SERVER['DOCUMENT_ROOT'] . '/data/tree/syspages.json',
		'intranet'  => $_SERVER['DOCUMENT_ROOT'] . '/data/tree/intranet.json',
	),
	'treeNodeIdMap' => array(
		'structure'    => 1,
		'files'        => 2,
		'help'         => 3,
		'helpFull'     => 3,
		'sysPages'     => 4,
		'intranet'     => 99,
	),
	'superTypeControllerMap' => array(
		'structure'             => 'structure',
		'pages'                 => 'pages',
		'files'                 => 'sf',
		'help'                  => 'help',
		'sysPages'              => 'structure',
		'intranet'              => 'intranet',
	),
	'superTypeUsernameMap' => array(
		'structure'           => 'Struktura webu',
		'files'               => 'Soubory',
		'help'                => 'help',
		'sysPages'            => 'Systémové stránky',
		'intranet'            => 'Intranet',
		'pages'               => 'Stránky - správa obsahu',
	),
	'controllerIdentificatorMap' => array(
		'structure'                 => 'node',
		'pages'                     => 'node',
		'sf'                        => 'filenode',
		'help'                      => 'helpnode',
		'pages'                     => 'node',
		'intranet'                  => 'intranetnode',
	),
	'defaultNodeSort'    => 'dateCreate',
	'contentTypes'       => array(
		'HtmlFile'          => 'Prázdná stránka',
		'HtmlFileWithFiles' => 'Článek s možností připojení souborů',
		'UserLink'          => 'Odkaz',
		'Hyperlink'         => 'Zástupce',
		'Product'           => 'Produkt',
		'Video'             => 'Video',

		'Application'     => 'Aplikace',
		'ApplicationHtml' => 'Aplikace s textovým obsahem',
		'Article'         => 'Článek',
		'Basket'          => 'Nákupní košík',
	),
	'overviewTypes'     => array(
		'OverviewProducts' => 'Přehled produktů / kategorie',
		'Overview'         => 'Pouze popis stránky',
		'OverviewList'     => 'Přehled podstránek',
		'OverviewNews'     => 'Přehled aktualit',
		'OverviewSubnodes' => 'Výpis podsložek',
		'Application'      => 'Aplikace',
		'ApplicationHtml'  => 'Aplikace s textovým obsahem',
		'OverviewHomepage' => 'Úvodní stránka',
		'OverviewArticles' => 'Články',
		'OverviewGallery'  => 'Galerie',
		'OverviewContact'  => 'Kontakty',
		'OverviewVideos'   => 'Videa',
	),
	'hasTags'          => array(
		'content_Product' => 'Produkt',
		'content_Article' => 'Článek',
	),
	'database'  => array(
        'type'     => 'Pdo_Mysql',     
        'host'     => '127.0.0.1',
        'username' => 'root',
        'password' => '',    
        'dbname'     => 'weber', 
        'encoding'  => 'utf8' 
	),
	/*'database2' => array(
		'type'     => 'Pdo_Mysql',
		'host'     => '127.0.0.1',
		'username' => 'nivona-eshopc002',
		'password' => 't61by8T2',
		'dbname'   => 'nivona-eshopcz01',
		'encoding' => 'utf8',
	),
	'database3'  => array(
		'type'     => 'Pdo_Mysql',
		'host'     => '127.0.0.1',
		'username' => 'specshopcz006',
		'password' => '7Zn3D0Gt',
		'dbname'   => 'specshopcz05',
		'encoding' => 'utf8',
	),*/
	'session'              => array(
		'cookie_lifetime'     => $time,
		'gc_maxlifetime'      => $time,
		'remember_me_seconds' => $time,
	),
	'instance'    => array(
		'workflow'   => array(
			'NEW'       => 'Nový',
			'PUBLISHED' => 'Publikovaný',
			'ARCHIVED'  => 'Archivovaný',
			'DELETED'   => 'Smazat',
		),
		'eventsAllowFiles' => true,

		'title'               => 'svycarskekavovary',
		'bannersFolderNodeId' => 3079,
		'prodejnyNodeId'      => 3105,
		'photosFolderNodeId'  => 3083,
		'detailsFolderNodeId' => 288,
		'userPhotosNodeId'    => 1052,

		'salesPass'  => 'h007c',
		'merchantid' => 250421,
		'curency'    => 203,

		'eventsNodeId'         => 1294,
		'containers'           => array(
			''                    => 'Určena nadřazenou stránkou',
			'page'                => 'Stránka',
			'pageWithRight'       => 'Stránka s pravou stranou',
			'pageWithoutLeftMenu' => 'Stránka bez levého menu',
		),
		'languages' => array(
			'cz'       => 'česky',
			//'en' => 'anglicky',
		),
		'defaultLanguage'     => 'cz',
		'defaultPathLanguage' => 'cz'
	),
	'modules'         => array(
		'events'         => array(
			'commingNodeId' => 1622,
			'passedNodeId'  => 1295,
		),
		'enquiry'    => array(
			'precision' => 1,
		),
		'advertising' => array(
			'clickthru'  => true
		)
	),
	'images'   => array(
		'quality' => 90,
	),
	'showSemantic' => array(
		'Zapnuto'     => true,
		'Vypnuto'     => false
	)
);

// Create the object-oriented wrapper upon the configuration data
$config = new Zend_Config($configArray);

if (isset($_POST["PHPSESSID"])) {
	session_id($_POST["PHPSESSID"]);
}

// SESSION
Zend_Session::setOptions($config->session->toArray());
Zend_Session::start();
// Zend_Session::rememberMe($config->sessionAlive);

//$db = Zend__dsd_Db;

// DATABAZE
// Automatically load class Zend_Db_Adapter_Pdo_Mysql and create an instance of it.
$db = Zend_Db::factory($config->database->type, $config->database->toArray());
$db->query('SET NAMES ' . $config->database->encoding);
/*$db2 = Zend_Db::factory($config->database2->type, $config->database2->toArray());
$db2->query('SET NAMES ' . $config->database2->encoding);
$db3 = Zend_Db::factory($config->database3->type, $config->database3->toArray());
$db3->query('SET NAMES ' . $config->database3->encoding);
*/
//$registry->set('db', $db);  

Zend_Loader::loadClass('Zend_Db_Table');
Zend_Db_Table::setDefaultAdapter($db);

$request = new Zend_Controller_Request_Http();

$registry          = Zend_Registry::getInstance();
$registry->config  = $config;
$registry->request = $request;
$registry->db      = $db;
//$registry->db2     = $db2;
//$registry->db3     = $db3;
$tree              = new Tree();
$registry->tree    = $tree;
