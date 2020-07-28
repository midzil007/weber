<?

class Email extends Zend_Mail
{
	public $charset = 'utf-8';
	public function __construct()
	{
		parent::__construct($this->charset);
		// $this->setHeaderEncoding(Zend_Mime::ENCODING_BASE64); 
	}
	
	public function save($sender, $recipients, $head, $text, $sent = false){
		if($sent){
			$sendAt = new Zend_Db_Expr('NOW()');
		} else {
			$sendAt = '0000-00-00 00:00:00';
		}
		
		$data = array(
			'sender' => $sender,
			'recipients' => $recipients,
			'head' => $head,
			'text' => $text,
			'sendAt' => $sendAt 
		);
		return Zend_Registry::getInstance()->db->insert('Mails', $data);
	}
	
	public static function parseEmail($email){
		$email = str_replace('(', '<', $email);
		$email = str_replace(')', '>', $email);
		if(strpos($email, '<')){
			$m = explode('<',$email);
			$name = trim($m[0]);
			$adress = substr(trim($m[1]), 0, -1);
		} else {
			$adress = trim($email);
			$name = '';
		}
		return array(
			'adress' => $adress,
			'name' => $name
		);
	}
	
	public function addToQueue(){	
		$data = array(
			'email' => serialize($this),
			'process_state' => 3
		);
		return Zend_Registry::getInstance()->db->insert('MailsQueue', $data);
	}
}
?>