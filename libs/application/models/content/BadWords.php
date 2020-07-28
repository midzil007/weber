<?
/**
 * Třída reprezentuje Článek
 * @see model_Content
 * @package content
 */
 
class content_BadWords extends Content {
    
	public $fileName = 'badwords.txt';
    
    public function __construct($config = array())
    {
    	$this->_name =  get_class($this);  
    	$this->userName = 'Slovník zakázaných slov';
       	$this->properties[] = new ContentProperty('badwords','Textarea','', array(), array(), array('style' => 'width:550px; height:360px;') );           
     
       	$this->fileName = Zend_Registry::getInstance()->config->dataRoot . '/' . $this->fileName;
    }
    
    function show($view, $node){   	
    	header('Content-type: text/html; charset=utf-8');
    	e($this->_getBadWords());
    	die();    	
    }
    
    function init(){
    	$this->badWords = $this->getBadWords();
    }
    
    function isValid($word){
    	return $this->censor($word, true);
    }
    
    function censor($text, $onlyCheck = false){
    	$okText = array();
    	$words = explode(' ', $text);
		foreach ($words as $word){
			foreach ($this->badWords as $bword){
				$pos = stripos($word, $bword);
				if ($pos === false) {
					continue;
				} else {
					if($onlyCheck){
						return false;
					}
					$word = str_repeat('-', strlen($word));
					break;
				}
			}
			$okText[] = $word;
		}   
		
		if($onlyCheck){
			return true;
		}
		return implode(' ', $okText);
    }
    
    function getBadWords(){
    	return unserialize(Utils::readFromFile($this->fileName));
    }
    
    function _getBadWords(){
    	$txt = $this->getPropertyByName('badwords')->value;   
    	$words = nl2br($txt);
    	$words = explode('<br />', $words);
    	foreach ($words as $k => $v){
    		$v = trim($v);
    		$words[$k] = $v;
    	}
    	$words = array_unique($words);
    	return $words;
    }
    
    function saveBadWords(){
    	$badWords = $this->_getBadWords();
    	Utils::writeToFile(serialize($badWords), $this->fileName );
    }
    
    function onSave(){    	
    	$this->saveBadWords();		    	
    	parent::onSave();
    }
        
    function onUpdate(){ 
    	$this->saveBadWords();
    	parent::onUpdate();
    }
    
}
?>
