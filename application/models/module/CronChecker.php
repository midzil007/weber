<?
/**
 * @author midzil
 *
 */
class module_CronChecker{
	
	private $start = 0;
	
	public function __construct() {
		$this->_tableName = 'CronChecker';
			$this->_tableNameCounterCron = 'module_eshop_couterCron';
		$this->db = Zend_Registry::getInstance()->db;
		// tři sloupce start, end, pokud nebude end tak je průser, url
	}
	
	
	public function start()
	{
		$data['url'] = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$data['start'] = $this->start = date("Y-m-d H:i:s");
		$this->db->insert($this->_tableName,$data);
	}
	
	
	
	public function checkProductCategories()
	{
		$exist = $this->db->fetchOne("select id from ".$this->_tableNameProducts." where parent = ''");
		if($exist)
		{
			$this->sendEmail($_SERVER['HTTP_HOST'].' - categorie','Chybí kategorie u produktů');
		}
	}
	
	public function end()
	{
		$where = $this->db->quoteInto('start = ?', $this->start);
		$data['end'] = date("Y-m-d H:i:s");
		$this->db->update($this->_tableName, $data, $where);
	}
	
	public function showUnCompleteCrons()
	{
		$all =  $this->db->fetchAll("select * from ".$this->_tableName." where end = '0000-00-00 00:00:00'");
		if($all)
		{
			$this->sendEmail();
		}
	}
	
	
	function sendEmail($title = false, $text = false,$reciver = false)
	{
		$mail = new Email();
		$mainText = 'Neprošel cron na '.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$emailText = $text ? $text : $emailText;
		$emailText = Helpers::prepareEmail(
				$mail,
				$emailText,
				false,
				false,
				'484848',
				'000000'
		);
		$mail->setBodyText(strip_tags(urldecode($mainText)));
		$mail->setBodyHtml(urldecode($emailText));
		$title = $title? $title : 'Neprošel cron na'.$_SERVER['HTTP_HOST'];
		$mail->setSubject($title);
		if($reciver){
			$mail->addTo($reciver,'shop-jura');
			//$mail->addBcc('jindrich.palupa@cz.jura.com', 'Jura');
			$mail->setFrom('debug@specshop.cz','shop-jura');
		}
		else{
		//	$mail->addTo('debug@specshop.cz','debug');
			//$mail->addTo('michal.nosil@gmail.com', 'shop-jura');   
			$mail->addTo('obchod@specshop.cz', 'Jura');   
			$mail->addTo('jindrich.palupa@cz.jura.com', 'Jura'); 
		}
		try { 
			//$mail->addTo('michal.nosil@gmail.com', 'shop-jura');  
				$mail->addTo('obchod@specshop.cz', 'Jura');    
			$mail->addTo('jindrich.palupa@cz.jura.com', 'Jura');
			$mail->addTo('michal.nosil@gmail.com', 'shop-jura');   
			$mail->send();     
		} catch (Exception $e) { } 
	}
	
	function getPosition() {  
		$position = $this->db->fetchOne ( "select position from " . $this->_tableNameCounterCron );
		if (! $position) {
			$position = 0;
		}
		return $position;
	}
	function incPosition($clean = false,$incPosition = 10) {
		$position = 0;
		$position = $this->getPosition ();
		$position += $incPosition;
		$this->db->delete ( $this->_tableNameCounterCron );
		if (! $clean) {
			$data = array (
					'position' => $position 
			);
			$this->db->insert ( $this->_tableNameCounterCron, $data );
		}
	}
	
}	
?>