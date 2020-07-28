<?php
class security_CmsAuth implements Zend_Auth_Adapter_Interface
{
	protected $username;
    protected $password;
    /**
     * Sets username and password for authentication
     *
     * @return void
     */
    public function __construct( $context, $username, $password)
    {
    	$this->context  = $context;
        $this->username =  $username;
        $this->password = $password;
    }

    /**
     * Performs an authentication attempt
     *
     * @throws Zend_Auth_Adapter_Exception If authentication cannot be performed
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {    	
		$users = new Users();			
		$res = $users->loginUser($this->username, $this->password);
			
		//pr($users);	
		if($res){
			$user = new User($this->username);			
			//$this->context->session['user'] = $user;	 
		 	
					
			$result = new Zend_Auth_Result(true, $user);			
            return $result;
		} else {
			$result = new Zend_Auth_Result(false, $user);
            return $result;
		}        
		
    }
    /*
    public function authenticate()
    {
    	
        $DB = Zend_Registry::get('dbAdapter');
        
        $select = $DB->select();
        $select->from('tbl_users', 'username', 'password');
        $select->where('Username = ?', $this->_username);
        $select->where('Password = ?', $this->_password);
        $sql = $select->__toString();

        $rowsFound = $DB->fetchAll($select);
        if (isset($rowsFound[0]['username']))
        {
            $msg=array();
            $msg[1]="Authenticated";
            $result = new Zend_Auth_Result(true, $this->_username, $msg);
            return $result;
        }
        else
        {
            $msg=array();
            $msg[1]="Sorry, you are not authenticate to access this website. Please check your username and password";
            $result = new Zend_Auth_Result(false, $this->_username, $msg);
            return $result;
        }
    }
    */
}

