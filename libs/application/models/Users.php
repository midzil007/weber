<?php

class Users extends Zend_Db_Table
{    
	public $fotoName = 'userThumb';
	
    protected function _setupTableName()
    {
        $this->_name = 'Users';        
        $this->adapter = $this->getAdapter();
        
        parent::_setupTableName();
    }
    
    public function getUsers($where = 'all', $sort = 'fullname', $sortType = 'ASC', $getAll = false)
    {	    
    	$select = $this->adapter->select(); 
    	if($getAll){
    		$select->from('Users', array( 'u' => 'username', '*'));
    	} else{
    		$select->from('Users', array( 'u' => 'username', 'fullname', 'username', 'id'));	
    	}
		
		
    	if($where == 'all'){
		} else {
			if($where){				
				$value = $where;  
				$coll = '`group` = ?';
			} else {
				$value = 'Webuser';
				$coll = '`group` != ?';
			}
			$select->where($coll, $value);
		}
		
		//return $this->fetchAll($where)->toArray();
		$sortType = $sortType?$sortType:'ASC';
		$select->order($sort . ' ' . $sortType);
				
		return $this->adapter->fetchAll($select);
    }
    
    public function getUsersSelect($where = 'all')
    {	    
    	$select =array();
    	$u = $this->getUsers($where);
    	foreach ($u as $uData){
    		$select[$uData['id']] = $uData['fullname'];
    	}
		return $select;  
    }
    
    public function getUser($username)
    {	
    	$where = $this->adapter->quoteInto("username = ?", $username);
    	$u = $this->fetchRow($where);
    	if($u){
    		return $u->toArray();
    	} else {
			return array();
    	}  
    }
    
    public function loginUser($username,$pass)
    {	
    	$where = $this->adapter->quoteInto("username = ?", $username) . ' AND ' . $this->adapter->quoteInto("password = ?", $pass) . " AND locked='0'";
    	$rowset = $this->fetchAll($where);
		$row = $rowset->current();
		
		if($row)
			return $row->toArray();
		else
			return array();
    }
    
    function createPhoto($filePath){
    			
    	$settings = Zend_Registry::getInstance()->settings;    	
    	$autosize = true;
    	
    	$photoWidth = $settings->getSettingValue('user_photo_width')?$settings->getSettingValue('user_photo_width'):80;
    	    	
    	$props = array(
    				'name' => $this->fotoName, 
    				'width' => $photoWidth,
    				'autosize' => $autosize
    			);
    	content_SFSFile::createFileThumbs($filePath, $props);	
    	    	
    }
    
}