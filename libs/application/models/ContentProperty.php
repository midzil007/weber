<?
/**
 * 
 */

class ContentProperty {
	
	/**
	 * Jméno vlastnosti.
	 * @var string
	 * @access public
	 */
	var $name;
	
	/**
	 * Typ vlastnosti.
	 * @var string
	 * @access public
	 */
	var $type = 'text';
	
	/**
	 * Hodnota vlastnosti.
	 * @var mixed
	 * @access public
	 */
	var $value;
	
	/**
	 * Spec. typ vlastnosti
	 * @var string
	 * @access public
	 */
	var $dojoAtributies = array();

	/**
	 * Zda se vlastnost preklada
	 * @var bool
	 * @access public
	 */
	var $translate;
	
	function __construct($name = false, $type = false, $value = false, $dojoAtributies = array(), $options = array(), $details = array(), $translate = false) {
		$this->name = $name;
		$this->type = $type;
		$this->value = $value;
		$this->dojoAtributies = $dojoAtributies;
		$this->options = $options;
		$this->details = $details;
		$this->translate = $translate;
	}
	
	
	function renderAdmin($view, $params = array('class' => 'ftext')){
		if(!is_array($view->htmlContentNames)){
			$view->htmlContentNames = array();
		}
		$input = '';
		if($this->type == 'Radio'){
			unset($params['class']); // nechci ftext
		}
		if(count($this->details) && is_array($this->details)){
			$params = array_merge_recursive($params, $this->details);	
		}
		$param3 = array_merge_recursive($params, $this->dojoAtributies);	
		 
		switch ($this->type) {
			case 'Wysiwyg':
				// $name = $this->name;   
				$name = $this->name;  // . '__X__' . rand(0, 9999999);  
				/*
				$name = $this->name;  // . '__X__' . rand(0, 9999999);   
				if($_SESSION['htmlContentNames'][date('i')][$name]){
					$param3['idecko'] = $_SESSION['htmlContentNames'][date('i')][$name];        
				} else {     
					$_SESSION['htmlContentNames'][date('i')][$name] = $name . '-' . rand(55, 99999);
					$param3['idecko'] = $_SESSION['htmlContentNames'][date('i')][$name];   
				}  
				
				$view->htmlContentNames[] = $param3['idecko']; // 'fck_' .   
				$input = $view->{'form'.$this->type}($name,$this->value, $param3['height'], 'fck_', $param3); 
				 */  
				
				if(isset($_SESSION['htmlContentNames'][$name])){
					$param3['idecko'] = $_SESSION['htmlContentNames'][$name];        
				} else { 
					$_SESSION['htmlContentNames'][$name] = $name . '-' . rand(55, 99999);
					$param3['idecko'] = $_SESSION['htmlContentNames'][$name];    
				} 
				//  pr($param3 );
				$view->htmlContentNames[] = $param3['idecko']; // 'fck_' .
				$input = $view->{'form'.$this->type}($name,$this->value, $param3['height'], 'fck_', $param3); 
				
				
				
				break;
			case 'CheckboxGroup':
			case 'MultiPageSelect':
			case 'Select':
			case 'ItemsStack': 
			case 'ComboBox':
				if(!count($this->options) && $this->value){
					$this->options[$this->value] = $this->value;
				}
				
				$input = $view->{'form'.$this->type}($this->name,$this->value, $param3, $this->options);
				break;
			case 'Radio':
				if(!count($this->options) && $this->value){
					$this->options[$this->value] = $this->value;
				}
				
				$input = $view->{'form'.$this->type}($this->name,$this->value, $param3, $this->options,  $this->details['listsep']);
				break;
			case 'FileSelect':
				$input = $view->{'form'.$this->type}($this->name,$this->value, $param3, array(), $this->details);
				break;
			case 'MultiFileSelect':
				$input = $view->{'form'.$this->type}($this->name,$this->value, $param3, $this->details);
				break;
			case 'Checkbox':
				$param3['checked'] = $this->value==1?'checked':'';
				$input = $view->{'form'.$this->type}($this->name,$this->value, $param3);
				break;
			case 'Chosen':
				$input = $view->{'form'.$this->type}($this->name,$this->value, $param3, $this->options);
			break;
			default:									 
				$input = $view->{'form'.$this->type}($this->name,$this->value, $param3);  
		}
		return $input;
	}
	
	function createTableRow($create4Alter = false){
		$length = 255;
		if($this->details['length']){
			$length = $this->details['length'];
		}
				
		$create = '`' . $this->name . '` ';		 
		switch ($this->type) {
			case 'Wysiwyg':
				$create .= 'longtext';
				break;
			case 'MultiFileSelect':
			case 'Textarea':				
				$create .= 'text';
				break;
			case 'Number':
				if(!$this->details['length']){
					$length = 7;
				}				
				if($this->details['precision']){					
					$create .= 'double(' . $length . ',' . $this->details['precision'] . ')';
				} else {
					$create .= 'int(' . $length . ')';
				}				
				break;
			case 'CheckboxGroup':
			case 'Select':
			case 'ComboBox':
				$create .= 'varchar(' . $length . ')';
				break;
			case 'TextSecured':
				$create .= 'varbinary(' . $length . ')';
				break;
			default:									
				$create .= 'varchar(' . $length . ')';
		}
		if(!$create4Alter){
			$create .= ', ';
		}
		return $create;
	}

}
?>
