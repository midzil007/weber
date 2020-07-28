<?

class FileAudit extends Zend_Db_Table
{    
    protected function _setupTableName()
    {
        $this->_name = 'Files_downloads';        
        $this->adapter = $this->getAdapter();      
        
        parent::_setupTableName();
    }
            
    public function getFileAudit($filename){
    	$query = "SELECT `module`, `controller`, `action`, `nodeId`, `message`, DATE_FORMAT(`time`,'%d.%m.%Y %H:%i') as time FROM `Audit` WHERE `username` = ? AND controller != 'login' ";    	
  		$operations = $this->adapter->fetchAll($query, array($username));
		foreach ($operations as $id => $o){
			$operations[$id]['controller'] = $this->modules[$o['controller']];
		}
  		//pr($result);   
		//pr($this->modules);
		
		
		return $operations;
    }
}
?>
