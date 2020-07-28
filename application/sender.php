<?
/**
 * Zajišťuje odesilani emailu
 */
require_once('../public/init.php');
require_once 'Utils.php';   

$sender = new module_EmailSender();
$sender->sendPack(); 		
$sender->clear();
?>