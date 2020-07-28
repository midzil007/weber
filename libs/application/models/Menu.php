<?

class Menu {

	private  $menuitems;
	
	function __construct() {
		
		$this->init();		
	}
	
	function init() {
		require_once(SERVER_ROOT . '/application/config/cmsMenu.php');
		$this->menuitems = $menuitems;		
	}
	
	function getItems() {
		return $this->menuitems;
	}
		
}
?>