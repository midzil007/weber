<?

class module_UserActions
{
				
	function __construct() {
		$this->db = Zend_Registry::getInstance()->db;		
	}
}

class UserAction {
	public $id;
	public $title;
	public $detail;
	public $startAt; // 2007-09-28
	public $within;
	public $type;
	public static $types = array(
		'normal' => 'Normální',
		'urgent' => 'Vysoká'
	);
	
	function __construct( $id = 0, $title = '', $detail = '', $startAt = '', $within = '', $type = '' ) {
		$this->id = $id;
		$this->title = $title;
		$this->detail = $detail;
		$this->startAt = $startAt; 
		$this->within  = $within;
		$this->type  = $type;
		
		if( !$this->within || $this->within == '0000-00-00' ){
			$this->within = $this->startAt;
		}
	}
	
	function init(){
		$event = Zend_Registry::getInstance()->db->fetchRow(
			'SELECT * FROM module_Events WHERE id=?',
			array($this->id)
		);
		return $event;
	}

	function save(){
		//insert
		$data = array(
			'title' => $this->title,
			'detail' => $this->detail,
			'startAt' => $this->startAt,
			'within' => $this->within,
			'type' => $this->type
		);
		Zend_Registry::getInstance()->db->insert('module_Events',$data);
	}
	
	function update(){
		$data = array(
			'title' => $this->title,
			'detail' => $this->detail,
			'startAt' => $this->startAt,
			'within' => $this->within,
			'type' => $this->type
		);
		Zend_Registry::getInstance()->db->update('module_Events',$data,  'id = ' . $this->id);
	}
	
	function delete(){	
		Zend_Registry::getInstance()->db->update(
			'module_Events',
			array(	'status' => '0' ), 
			Zend_Registry::getInstance()->db->quoteInto(" id=? ", $this->id)
		);
	}
	
	function __toString() {
		pr($this);
	}
}

?>