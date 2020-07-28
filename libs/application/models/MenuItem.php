<?

class MenuItem  {

	function __construct( $title, $url, $description,  $submenu = null ) {
		$this->title = $title;
		$this->description = $description;
		$this->url = $url;
		$this->submenu = $submenu;
	}
		
	function  addItem( $title, $url, $description, $submenu = null ){
		$this->submenu[] = new controls_admin_MenuItem( $title, $url, $description, $submenu );
	}
	
}
?>