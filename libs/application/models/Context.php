<?

/**
 * Třída reprentuje kontext (souvislost), v němž je zpracováván aktuální požadavek 
 */
class Context{
	
	/**
	 * Příznak kontextu back-endu (administrace).
	 * @var boolean
	 */
	public $admin = false;
	
	public function __construct($registry, $request, $config, $session, $db) {
		$this->registry = $registry;
    	$this->request = $request;
    	$this->config = $config;
    	$this->session = $session;
    	$this->db = $db;
	}

	public function __toString() {
		return 'Context';
	}

}
?>