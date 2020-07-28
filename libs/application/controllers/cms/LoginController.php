<?php

class Cms_LoginController extends CmsController
{

	public function init() 
	{
		$this->fields = array('username', 'pass', 'requestUrl');		
		parent::init();	
	}

	public function indexAction()
	{
		if ($this->auth->hasIdentity() && $this->session->logged) {	
			//$this->_redirector->goto( $this->view->requestUrl);			
			 $this->_redirector->goto('index', 'index');  
		} else {			
			$this->view->title = '= CMS = Login';
			$this->view->webTitle = $this->config->instance->title;		  
		}
		//e($this->view); 
		if($this->request->isPost()){			
			$filter = new Zend_Filter_StripTags();
			$username = trim($filter->filter($this->request->getPost('username')));
			$pass = trim($filter->filter($this->request->getPost('pass')));			
			$this->doLogin($username,$pass);
		}
		
		// Render page 
		parent::indexAction();
	}


	function doLogin($username,$pass) {		
		
		$adapter = new security_CmsAuth($this->context, $username, sha1($pass));
		$result = $this->auth->authenticate($adapter);
		
		switch ($result->getCode()) {
		
		    case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
		        /** do stuff for nonexistent identity **/
		         parent::addErrorInstantMessage('Uživatel nenalezen..');
		        break;
		
		    case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
		        /** do stuff for invalid credential **/		       
		        parent::addErrorInstantMessage('Špatně zadané jméno nebo heslo.');
		        break;
		
		    case Zend_Auth_Result::SUCCESS:		    	
		    	$path = explode('/',str_replace('/cms','',$this->input->requestUrl));
		    			    	
		    	$this->session->logged = true;
		    	$this->view->user = $this->session->user = $this->auth->getIdentity();	
		    		
		 	   //	e($this->session);	     exit();
		 	    			
    			parent::audit();  
				
    			// $this->_redirector->goto('index', 'eshop');
    			/*
				//Zend_Session::rememberMe($this->config->sessionAlive);
				if($this->session->user->group == 'employees' || $this->session->user->group == 'employees_RR' || $this->session->user->group == 'employees_MV' ){
						$this->_redirector->goto('index', 'eshop');
				} else {
		    		$this->_redirector->goto($path[2],$path[1]);
				}*/  
    			
    			if($this->view->user->group == 'Redactors')
    			{
    				$this->_redirector->goto('index', 'pages');
				}
				elseif($this->view->user->group == 'OnlyEshop' ||  $this->view->user->group == 'onlyProducts')
				{
					$this->_redirector->goto('listorders', 'eshop');
				}     
				else{
					$this->_redirector->goto('listorders', 'eshop');
				}
		        break;
		
		    default:
		        /** do stuff for other failure **/
		        parent::addErrorInstantMessage('Přihlášení se nezdařilo.');
		        break;
		}

				
		// Retrieve row of user info and store inside Identity object (including role!)
		//$userTable = new MyUser_Table; // Instance of Zend_Db_Table or similar...
		//$identity->setUser($userTable->find($identity->getIdentifier()));
			
		
		//$this->_redirector->goto( $this->view->requestUrl );
	}
	
	public function logoutAction()
	{
		$this->auth = null;
		Zend_Session::forgetMe();
		Zend_Session::expireSessionCookie();
		//Zend_Session::destroy();
		
		$this->session->logged = false;		
		$this->_redirector->goto('', 'index');
	}


}
