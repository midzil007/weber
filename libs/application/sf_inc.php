<?
/**
 * Zajišťuje poskystování souborů 
 */
try {
	$file = substr($_SERVER['REQUEST_URI'], strlen($config->sfFolder) + 1);
	$filename = $config->fsRoot . content_SFSFile::getSFSFullPath($file); 
	//$file = controls_admin_SF_File::getFile( $input->path);
	$TYPE =  Utils::getMimeTypeByExtension(substr($filename, strrpos($filename, '.') + 1));
	  
	if(file_exists($filename) && is_file($filename)) {
		 
		if(Zend_Session::isReadable()){
			$auth =  Zend_Session::namespaceGet('Zend_Auth');
			$user =  $auth['user']->username;		
		} else { 
			$user = ''; 
		}
		 		 
		if($config->downloadStats){
			Utils::saveFileDownloadStats($db,$file,$user);
		}
		
	//	e($TYPE); exit();
		$offset = 60 * 60 * 24 * 1;
		$ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
		Header($ExpStr);
		header("Vary: Accept-Encoding");
		
		
		header('pragma: ');
		header('Content-Type: '.$TYPE);

                $etag = md5($filename);
                header('ETag: ' . $etag);
                
                $last_modified_time = filemtime($filename);
                header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified_time)." GMT");
                
                if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time ||
                   trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag) {
                    header("HTTP/1.1 304 Not Modified");
                    exit;
                } 
		
		//$file->readContent($context);
		
		if (!@readfile($filename)){		
				//err
		}
	} else {
		header ("Status: 404 Not Found");
		?>
		<h2>Soubor nenalezen / File not found</h2>
		<?
	}
} catch (Exception $e) {
	header ("Status: 404 Not Found");
	?>
	<h2>Soubor nenalezen / File not found</h2>
	<?
}


?>
