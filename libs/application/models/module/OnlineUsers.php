<?

class module_OnlineUsers
{		
	protected $timeout = 200;// in seconds
  	private $onlineUsersCount = 0;
  	
	function __construct($onlyWebUsers = true) {
		$this->onlyWebUsers = $onlyWebUsers;
		$this->db = Zend_Registry::getInstance()->db;
		$this->_tableName = 'module_OnlineUsers';
		
		$this->timestamp = time();
        $this->ip = Utils::getIP();        
	}
	
	function logMe($view, $username){
		if($this->onlyWebUsers){
			if($view->webUserLogged){				
				$this->new_user($username);
			}
		}
        $this->delete_users();
        $this->onlineUsersCount = $this->count_users();
	}
	
	function unlogMe($username){
		$this->db->query("DELETE FROM " . $this->_tableName . " WHERE ip LIKE '%=$username'");
	}
	
	function getOnlineUsersCount(){
		return $this->onlineUsersCount;
	}
	
	function getOnlineUsers($webusers, $limit){		
		$users = array();
		//e("SELECT distinct username FROM " . $this->_tableName . " as t1, `module_WebUsers` as t2 WHERE t1.ip = concat(t2.IP, '=', t2.username) ORDER BY t1.online DESC");
		$all = $this->db->fetchAll("SELECT distinct username FROM " . $this->_tableName . " as t1 ORDER BY t1.online DESC");
		foreach ($all as $user){
			$username = $user['username'];
			$users[$username] = new module_WebUser($username);
		}
		return $users;
	}
		
    function new_user($username) {
    	//$ip = $this->ip . '=' . $username;
    	$this->db->query("DELETE FROM " . $this->_tableName . " WHERE username = '$username'");
        $this->db->query("INSERT INTO " . $this->_tableName . "(online, ip, username) VALUES ( NOW(), '$this->ip', '$username')");
    }
   
    function delete_users() {    	
    	$del = date("Y-m-d H:i:s", mktime(date("H"), date("i"),   date("s") - $this->timeout, date("m"),   date("d"),   date("Y")));    	
        $this->db->query("DELETE FROM " . $this->_tableName . " WHERE online < '" . $del . "'");
    }
   
    function count_users() {
        return $this->db->fetchOne("SELECT count(ip) as pocet FROM " . $this->_tableName . "");
    }    
    
    function getIPStatus($ip, $username){
    	//$ip = $ip . '=' . $username;
    	return $this->db->fetchOne("SELECT count(ip) as pocet FROM " . $this->_tableName . " WHERE ip = '$ip' AND username = '$username'");
    }
    
    function getStatusByUsername($username){
    	$ip = $this->db->fetchOne("SELECT o.ip FROM " . $this->_tableName . " as o WHERE username = '$username'");
    	if($ip){
    		return true;
    	} else {
    		return false;
    	}
    }
}

?>