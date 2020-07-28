<?php
class security_CmsAcl extends Zend_Acl
{
    public function __construct(Zend_Auth $auth)
    {
    	
    	$this->noAuthNeeded = array(
    		'cms/mailing/sendEmails', 
    		'cms/structure/jstree'
    	);
    	
        //parent::__construct();
				
        //modules
        $this->controllers = new Controllers();		
		$this->allControllers =  $this->controllers->getControllers();
	
		foreach($this->allControllers as $name => $fullname){
			 $this->add(new Zend_Acl_Resource($name));
		}
		//$this->add(new Zend_Acl_Resource('login'));
		
        // groups		
		$this->groups = new Groups();		
		$this->allGroups = $this->groups->getGroups();
		
		// groups x modules
		$this->controllersGroups = new ControllersGroups();
				
		//pr($this->groups); exit();
		$roleGuest = new Zend_Acl_Role('Readers'); // odni dedi, daji se nastavit globalne prava
		$this->addRole($roleGuest);
		
		//  zakazani specifickych akci		
		$this->add(new Zend_Acl_Resource('changeAuthor'), 'pages'); 
		$this->add(new Zend_Acl_Resource('makePublishedContent'), 'pages'); 
		$this->add(new Zend_Acl_Resource('changeState'), 'pages'); 
		$this->add(new Zend_Acl_Resource('search')); 
		$this->add(new Zend_Acl_Resource('approveIntranetFiles'), 'intranet');
	
		//pr($this->allGroups);
		foreach($this->allGroups as $name => $fullname){			
			$this->addRole(new Zend_Acl_Role($name), 'Readers');			 
			$am = $this->controllersGroups->getGroupAccessibleControllers($name);
			//pr($am);
			foreach ($am as $m ){
				// přistup pro celej controller
				if(!$m['deny'] && !$m['allow']){
					$this->allow($name, $m['module']);
				}
				if($m['deny']){
					$this->deny($name, $m['module'], $m['deny']);
				}
				else{
					$this->allow($name, $m['module'], $m['allow']);
					
				}
				
				// přidá všechny a pak sejme jen denyOnly
				if($m['denyOnly'])
				{
					$this->deny($name, $m['module'], $m['denyOnly']);
				}
				
			}	
			// specifik
			$this->allow($name, 'login');
			$this->allow($name, 'help');	
			$this->allow($name, 'load');	
			$this->allow($name, 'index');
			$this->allow($name, 'changeAuthor');
			$this->allow($name, 'changeState');
			$this->allow($name, 'search');	
			$this->allow($name, 'makePublishedContent');				 
		}		
		
		/*
		try{
			//  zakazani specifickych akci		
			$this->deny('Redactors', 'changeAuthor'); // Remove specific privilege
			$this->deny('Redactors', 'changeState');		
			$this->deny('Redactors', 'makePublishedContent');
			
			$this->deny('employees', 'index');		
			//$this->removeAllow('Redactors', 'pages', array('changeAuthor'));
		} catch (ErrorException $e){
			
		}
		*/
		
		try{
			if($this->hasRole('Brokers')){
				$this->deny('Brokers', 'approveIntranetFiles');
			}
		} catch (ErrorException $e){
			
		}
		
		
		$this->allow('Administrators'); // unrestricted access
		try{
			if($this->hasRole('Administrators')){
				$this->deny('Administrators', 'eshop', 'conversion');
			}
		} catch (ErrorException $e){
			
		}
		
		
		//$this->removeAllow('Redactors', 'pages', array('changeAuthor', 'xxx'));

    }
}
