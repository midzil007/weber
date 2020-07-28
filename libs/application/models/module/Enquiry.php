<?

class module_Enquiry
{		
	public $title;
	public $description;	
	public $end;
	public $options = array();
	
	public $colors = array(
		'3876da', '63b139', 'f23232', 'dfa73c', 'd13abe', '35e1e4', 'f23268', '64392a', '868686'
	);
	
	public $classes = array(
		'orange', 'purple1', 'green', 'blue', 'purple2', 'purple3'
	);
	
	function __construct() {
		$this->db = Zend_Registry::getInstance()->db;
		$this->_tableName = 'module_Enquiry';
	}
	
	function init($title, $description, $end, $nodeId, $options = array()) {
		$this->title = $title;
		$this->description = $description;
		$this->end = $end;
		$this->nodeId = $nodeId;
		
		foreach ($options as $option){
			$this->options[] = new EnquiryOption(0, 0, $option) ;	
		}
	}
	
	
	function get($id)
	{
		$e = $this->db->fetchRow(
			'SELECT * FROM `' . $this->_tableName . '` WHERE id=?',
			array($id)
		);
		
		$e['options'] = $this->db->fetchAll(
			'SELECT * FROM `module_EnquiryOptions` WHERE enq_id=? ORDER BY id asc',
			array($id)
		);
		
		return $e;
	}
    	
    	
	function add($title, $description, $end, $nodeId, $options) {
		$data = array(
			'title' => $title,
			'description' => $description,
			'end' => $end,
			'nodeId' => $nodeId
		);
		$this->db->insert($this->_tableName, $data);
		$eId = $this->db->lastInsertId($this->_tableName);
		
		
		foreach ($options as $option){
			$o = new EnquiryOption(0, $eId, $option);	
			$o->save();
		}
		
	}
	
	function update($id, $title, $description, $end, $options) {
		$data = array(
			'title' => $title,
			'description' => $description,
			'end' => $end
		);
		$this->db->update($this->_tableName, $data, $this->db->quoteInto(" id=? ", $id));
		
		$curentE = $this->get($id);
		$curentOptions = array();
		foreach ($curentE['options'] as $option){
			$curentOptions[$option['id']] = $option['question'];
		}
		
		foreach ($options as $option){
			if(is_array($option)){ // update
				
				if($curentOptions[$option['id']]){
					unset($curentOptions[$option['id']]);
				}
				
				$o = new EnquiryOption($option['id'], $id, $option['val']);
				$o->update();
			} else {
				$o = new EnquiryOption(0, $id, $option);	
				$o->save();
			}
		}
		
		if(is_array($curentOptions)){
			foreach ($curentOptions as $id => $v){
				$this->db->delete(
					'module_EnquiryOptions',
					$this->db->quoteInto(" id = ? ", $id)
				);
			}		
		}
		
	}
	
	function delete($eid) {
		
		$this->db->delete(
			$this->_tableName,
			$this->db->quoteInto(" id = ? ", $eid)
		);
		$this->deleteOptions($eid);
			
	}
	
	function deleteOptions($enq_id) {
		$this->db->delete(
			'module_EnquiryOptions',
			$this->db->quoteInto(" enq_id = ? ", $enq_id)
		);	
	}
	
	function getActiveEnquiry($section = 1)
    {    	
    	$all = $this->getAll('active', 'id', 'DESC', $section);
    	if(count($all)){
    		$e = array_shift($all);
    		return $this->get($e['id']);
    	} else {
    		return null;
    	}
    }
    
    function calcVotedPercent($sum, $voted)
    {
    	if($sum == 0 || $voted ==0){
    		return 1;
    	}
    	
    	$c = Zend_Registry::getInstance()->config;
    	$p = $voted/$sum * 100;
    	if($c->modules->enquiry->precision){
    		return round($p, $c->modules->enquiry->precision);
    	} else {
    		return floor($p);
    	}
    }
    
    function getVotedPercents($view, $options)
    { 
    	$votesSum = 0;
    	foreach ($options as $option){
    		$votesSum += $option['votes'];
    	}
    	
    	foreach ($options as $i => $option){
    		$options[$i]['portion'] = $this->calcVotedPercent($votesSum, $option['votes']);
    	}
    	$view->votedSum = $votesSum;
    	return $options;
    }
    
    function getWebEnuiry($view, $section = 1, $extra = '')
    { 
    	$enquiry = $this->getActiveEnquiry($section);    	
		if($enquiry){						
    		$enquiry['options'] = $this->getVotedPercents($view, $enquiry['options']);
			$view->enq = $enquiry;
			$view->enq_extra = $extra;
			echo $view->render('controls/admin/modules/Enquiry/EnquiryFE.phtml');
		}
    }
    
    function getEnuiryResults($view, $eId = 1, $extra = '')
    { 
    	$enquiry = $this->get($eId);   
		if($enquiry){						
    		$enquiry['options'] = $this->getVotedPercents($view, $enquiry['options']);
			//e($view->inputGet->vote);
			$view->enq = $enquiry;
			$view->enq_extra = $extra;
			echo $view->render('controls/admin/modules/Enquiry/EnquiryFE.phtml');
		}
    }
    /*
    function vote($view)
    {     	
    	if($view->inputGet->vote && $view->inputGet->e){
    		$identificator = "rv_" . $view->inputGet->e;
			
			if($_COOKIE[$identificator]){
				$voted[] = $view->inputGet->e . '_voted';
				return $voted;
			} else {
				$o = new EnquiryOption($view->inputGet->vote, $view->inputGet->e);
				$o->init();				
				$o->addVote();
				setcookie ($identificator, "1", time()+60*60*24*360 );
				header ("Location: " . $view->curentPath); 
				exit;   
			}
		} else {
			return false;
		}
    }
    */
    function vote($view)
    {     	
    	if($view->inputGet->vote && $view->inputGet->e){
    		$identificator = "rv_" . $view->inputGet->e;
			
			if($_COOKIE[$identificator]){
				$voted[] = $view->inputGet->e . '_voted';
				return $voted;
			} else {
				if($_COOKIE[session_name()] == session_id() && $_COOKIE[session_name()] == session_id() && strpos($_SERVER['HTTP_REFERER'], Utils::getWebUrl()) !== false){
					$o = new EnquiryOption($view->inputGet->vote, $view->inputGet->e);
					$o->init();				
					$o->addVote();
					setcookie ($identificator, "1", time()+60*60*24*360 );
				}
				header ("Location: " . $view->curentPath); 
				exit;   
			}
		} else {
			return false;
		}
    }
    
	function getAll($where = 'all', $sort = 'fullname', $sortType = 'DESC', $nodeId = 1)
    {	    
    	$select = $this->db->select();
    	$select->from($this->_tableName, array( 'id', 'title', 'end'));	
    	  
    	switch ($where){
    		default:
    		case 'all':
    			break;
    		case 'active':
    			$select->where('end >= ?', new Zend_Db_Expr('NOW()'));
    			break;
    		case 'past':
    			$select->where('end < ?', new Zend_Db_Expr('NOW()'));
    			break;    		
    	}
    	
    	$select->where('nodeId = ?', $nodeId);	
    	
		//return $this->fetchAll($where)->toArray();
		$sortType = $sortType?$sortType:'ASC';
		$select->order($sort . ' ' . $sortType);
				
		$all = $this->db->fetchAll($select);
		
		$enquiries = array();
		foreach ($all as $row){
			$row['end'] = Utils::formatDate($row['end']);
			$enquiries[] = $row;			
		}
		return $enquiries;
    }
    
    function parseOptions($input){    	
    	$options = array();
    	
    	if($input->oldOptionsMap){ // edit
    		$oldOptions = explode(';', $input->oldOptionsMap);
    		$oldOptionsMap = array();
    		foreach ($oldOptions as $i){
    			list($oldId, $oldI) = explode('-', $i);
    			$oldOptionsMap[$oldI] = $oldId;    			
    		}    		
    	}
    	
   		foreach ($input as $ident => $val){			
			if(substr($ident,0,strlen($input->optionPrefix)) == $input->optionPrefix && $val){
				$i = substr($ident,strlen($input->optionPrefix));
				if($input->oldOptionsMap){
					if($oldOptionsMap[$i]){
						$options[] = array(
							'val' => $val,
							'id' => $oldOptionsMap[$i]
						);
					} else {
						$options[] = $val;
					}
					
				} else {
					$options[] = $val;
				}
			}			
		}	
		return $options;
    }
	
}

class EnquiryOption {
	
	public $id;
	public $enq_id;
	public $question;
	public $votes;
		
	function __construct( $id = 0, $enq_id = 0, $question = '', $votes = 0 ) {
		$this->id = $id;
		$this->enq_id = $enq_id;
		$this->question = $question; 
		$this->votes  = $votes;		
		$this->_tableName = 'module_EnquiryOptions';
		$this->db = Zend_Registry::getInstance()->db;
	}
	
	function init(){
		$option = $this->db->fetchRow(
			'SELECT * FROM `' . $this->_tableName . '` WHERE id=?',
			array($this->id)
		);
		$this->id = $option['id'];
		$this->enq_id = $option['enq_id'];
		$this->question = $option['question'];
		$this->votes = $option['votes'];
	
	}

	function save(){
		//insert
		$data = array(
			'enq_id' => $this->enq_id?$this->enq_id:'',
			'question' => $this->question,
			'votes' => $this->votes
		);
		$this->db->insert($this->_tableName, $data);
	}
	
	function update(){
		$data = array(
			'question' => $this->question
		);
		
		$this->db->update($this->_tableName, $data, $this->db->quoteInto(" id=? ", $this->id));
	}
	
	function addVote(){
		//insert
		$data = array(
			'votes' => $this->votes + 1
		);
		
		$this->db->update($this->_tableName, $data, $this->db->quoteInto(" id=? ", $this->id));
		
	}
	
	/*
	function delete(){	
		Zend_Registry::getInstance()->db->delete(
			'module_Events',
			array(	'status' => '0' ), 
			Zend_Registry::getInstance()->db->quoteInto(" id=? ", $this->id)
		);
	}
	*/
	function __toString() {
		pr($this);
	}
}

?>