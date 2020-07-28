<?php
if ($_GET['stateAgmo']) {
	echo 'code=0&message=OK';
	die;
}
if($_SERVER['REQUEST_URI'] == '/')
{       
	//header('Location: https://www.svycarskekavovary.cz/jura' , true, "301");
	//exit();           
}  
register_shutdown_function("fatal_handler");
//$_GET['benchmark'] = 1;
$t1 = microtime();
require_once 'init.php';
require_once 'Utils.php';
if ($_GET['benchmark']) {
	$startTime = Utils::getMicrotime();
}

include ('cmsInit.php');

/*
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
if (!(error_reporting() & $errno)) {
return;
}
switch ($errno) {
case E_USER_ERROR:
echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
echo "  Fatal error on line $errline in file $errfile";
echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
echo "Aborting...<br />\n";
exit(1);
break;

case E_USER_WARNING:
echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
break;

case E_USER_NOTICE:
echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
break;

default:
echo "Unknown error type: [$errno] $errstr<br />\n";
break;
}

return true;
}

$old_error_handler = set_error_handler("myErrorHandler");
 */

try {
	$controller->dispatch();
} catch (Exception $e) {
	echo 'Caught exception: ', $e->getMessage(), "\n";
}

if ($_GET['benchmark']) {
	$processTime = round(Utils::getMicrotime()-$startTime, 4);
	e('vygenerovano za: '.$processTime.' s');
}

function fatal_handler() {
	$errfile = "unknown file";
	$errstr  = "shutdown";
	$errno   = E_CORE_ERROR;
	$errline = 0;
	$error   = error_get_last();
	if ($error !== NULL && $error['type'] != 8 && !is_numeric(strpos($_SERVER['REQUEST_URI'], '/cms'))) {
		$errno   = $error["type"];
		$errfile = $error["file"];
		$errline = $error["line"];
		$errstr  = $error["message"];
		$trace   = print_r(debug_backtrace(false), true);
		$content = "
  <table>
  <thead><th>Item</th><th>Description</th></thead>
  <tbody>
  <tr>
    <th>URL</th>
    <td><pre>".$_SERVER['REQUEST_URI']."</pre></td>
  </tr>
  <tr>
    <th>QUERY_STRING</th>
    <td><pre>".$_SERVER['QUERY_STRING']."</pre></td>
  </tr>
  <tr>
    <th>Error</th>
    <td><pre>$errstr</pre></td>
  </tr>
  <tr>
    <th>Errno</th>
    <td><pre>$errno</pre></td>
  </tr>
  <tr>
    <th>File</th>
    <td>$errfile</td>
  </tr>
  <tr>
    <th>Line</th>
    <td>$errline</td>
  </tr>
  <tr>
    <th>Trace</th>
    <td><pre>$trace</pre></td>
  </tr>
  </tbody>
  </table>";
		$fatak = 'fatak - '.$_SERVER['SERVER_NAME'];
		mail('debug@specshop.cz', $fatak, $content);
	}
}
