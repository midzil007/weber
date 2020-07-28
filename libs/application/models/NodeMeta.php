<? 
/*
 * dalsi info k node 
 */
class NodeMeta
{    
	private $table = 'NodesMeta';
	
	function __construct() {		 
	}
 	
	function getMetaById($nid) { 
		$sm = Zend_Registry::getInstance()->db->fetchOne("SELECT meta FROM $this->table WHERE nid = ?", $nid);
		if(!$sm){
			$sm = array();
		} else {
			$sm = unserialize($sm);
		}
		return $sm;
	}
	
	function deleteMetaById($nid) {
		$sm = Zend_Registry::getInstance()->db->query("DELETE FROM $this->table WHERE nid = ?", $nid);
	}
	
	function setMetaById($nid, $metaArray) {
		$dbAdapter = Zend_Registry::getInstance()->db; 
		$this->deleteMetaById($nid);
		
    	$data = array(  
			'nid' => $nid, 
			'meta' => serialize($metaArray) 
		);   
			
    	$dbAdapter->insert($this->table, $data);
	}
}

?>
