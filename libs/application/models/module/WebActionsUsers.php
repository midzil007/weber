<?php

class module_WebActionsUsers
{    
    function __construct($actionContent = null)
    {
    	$this->action = $actionContent;    
        $this->_tableName = 'module_WebActionsUsers';    
        $this->db =  Zend_Registry::getInstance()->db;
    }
	
    public function sign($postData)    
    {	
    	$this->input = $postData;
    	$err = $this->checkRegistrationPage();	
		
		if(!$err){ // ok	
			
    		$this->db->insert(
    			$this->_tableName,
    			array(
    				'actionId' =>  $this->input->actionId,    		
    				'fullname' =>  $this->input->fullname,    				
    				'email' => $this->input->email,
    				'tel' => $this->input->tel,
    				'company' => $this->input->company
    			)
			);			
							
			return array(1, 'Registrace proběhla úspěšně.');
		} else {
			return array(0, $err);
		}
    }
    
    public function checkCapacity($aid)    
    {
    	$capacity = $this->action->getPropertyValue('aCapacity');
    	$alreadyRegistered = count($this->getActionUsers($aid));
    	if($capacity > $alreadyRegistered ){
    		return true;
    	} else {
    		return false;
    	}
    }
    
    public function getActions(){
    	$tree =  Zend_Registry::getInstance()->tree;
    	$actions = $tree->getNodeById(232)->getChildren();
    	$ret = array();
    	foreach ($actions as $node){
    		if($content = $node->getPublishedContent()){
    			if($content->_name == 'content_UserAction'){
    				$ret[$node->nodeId] = $node->title;
    			}
    		}
    	}
    	return $ret;
    	//pr($actions);
    }
   
       
    public function getActionUsers( $actionId, $regionFilter = null, $sort = 'fullname', $sortType = 'Desc')
    {
    	$users = array();
    	if(!$actionId){
    		return $users;
    	}
    	
    	$select =  $this->db->select();
		$select->from($this->_tableName, array( 'fullname', 'email'));
    	    	
		$select->where('actionId = ?', $actionId);				
		$sortType = $sortType?$sortType:'Asc';
		$select->order($sort . ' ' . $sortType);
	//	e($select->__toString());
		$users = $this->db->fetchAll($select);
		
		return $users;
		
    }
    
    public function checkRegistrationPage(){   	
    	
    	do{
			if(!$this->input->fullname || !$this->input->email){
				$err = "Zadejte prosím všechny povinné položky.";			   
			    break;
			}	
			$validator = new Zend_Validate_EmailAddress();			
			if (!$validator->isValid($this->input->email)) {			    
				$err = current($validator->getMessages());			   
			    break;
			}
			// neexistuje uz email ?
			$e = $this->db->fetchOne('SELECT email FROM `' . $this->_tableName . '` WHERE email = ?', $this->input->email);
			if($e){
				$err = 'Email ' . $e . ' již existuje.';			   
			    break;
			}
					
			return false;
		} while (false);			
		return $err;
    }
    
    /*
    public function getUsers( $sectorFilter = null, $regionFilter = null, $sort = 'fullname', $sortType = 'Desc')
    {
    	$select =  $this->db->select();
		$select->from($this->_tableName, array( 'id', 'fullname', 'email', 'region', 'sector'));
    	    	
		if($sectorFilter != 'allsectors' && $sectorFilter){				
			$select->where('sector = ?', $sectorFilter);
		} 
		
		if($regionFilter != 'allregions' && $regionFilter){				
			$select->where('region = ?', $regionFilter);
		} 
		
		$select->where('active = ?', '1');
		
		$sortType = $sortType?$sortType:'Asc';
		$select->order($sort . ' ' . $sortType);
		//e($select->__toString());
		$all = $this->db->fetchAll($select);
		//pr(module_WebUser::$sector);
		$users = array();
		foreach ($all as $id => $u){
			$users[$id] = $u;
			$users[$id]['sector'] = module_WebUser::$sector[$u['sector']];
			$users[$id]['region'] = module_WebUser::$region[$u['region']];
		}
		return $users;
		
    }
    */
    
    
}