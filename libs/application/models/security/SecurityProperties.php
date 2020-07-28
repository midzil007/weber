<?
/**
 * @package security
 */
class security_SecurityProperties {
	
	public $roles = array();
	
	public $allow = array();
	
	public $deny = array();
	
	public function __construct($roles = array(), $allow = array(), $deny = array()) {
		$this->roles = $roles;
		$this->allow = $allow;
		$this->deny = $deny;
	}
	
	function getCopy() {
		return new security_SecurityProperties($this->roles, $this->allow, $this->deny);
	}
}
?>