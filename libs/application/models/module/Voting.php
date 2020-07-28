<?

class module_Voting
{		
	
	function __construct() {
		$this->db = Zend_Registry::getInstance()->db;
		$this->_tableName = 'module_VotesAll';
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
    
}

?>