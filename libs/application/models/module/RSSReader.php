<?

/**
 * RSS
 */
class module_RSSReader {
	
	public function __construct() {
		$this->db = Zend_Registry::getInstance()->db;
		$this->_tableNameS = 'module_RssFeedsServers';
		$this->_tableNameP = 'module_RssFeedsPosts';
	}
	
	function getSourceServers(){
		$servers = $this->db->fetchAll(
			"SELECT * FROM `" .$this->_tableNameS . "`"
		);
		return $servers;
	}
	
}
?>