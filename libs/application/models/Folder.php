<?
/**
 * Sdílená složka.
 * @package SF
 */
class Folder {
	
	public static function makeFolder($fullPath) {
		$err = false;
		if (is_dir($fullPath)) {
			$err = 'Zadaná složka již existuje';
		} else {
			umask(0000);			
			if (!@mkdir($fullPath, 0777)){
				$err = 'Nepodařilo se vytvořit adresář.';
			} else {
				chmod ($fullPath, 0777); 
			}
		}
		return $err;
	}
	
	public static function getFolderFiles($folder, $fullpath = false) {
		 
		if($fullpath){  
			$folder = $folder;
		} else {
			$folder = Zend_Registry::getInstance()->config->fsRoot .  $folder;	
		}
		
		$files = array();
		foreach (scandir($folder) as $name) {
			if ($name{0} == '.'){
				continue;
			}
			$filename = $folder . '/' .$name;
						
			if (is_dir($filename)){
				continue;
			}
				
			if (is_file($filename)) { 
				if(!$fullpath){
					$files[] = substr($filename, strlen(Zend_Registry::getInstance()->config->fsRoot));
				} else {
					$files[] = $filename; 
				}
			}							
		}
				
		return $files;
	}
	
}
?>
