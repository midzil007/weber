<?

/**
 * Třída reprentuje zalogovaneho uzivatele webu
 */
class module_WebUser extends User {
		
	public function __construct($username = null, $id = false) {
		parent::__construct($username, $id);
		$this->session = Zend_Registry::getInstance()->session;
	}
	

}
?>