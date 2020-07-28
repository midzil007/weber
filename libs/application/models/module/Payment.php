<?
  /**
	uzivatelsky editovatelne zpusoby dopravy
	@author Jakub Kratena
  */
class module_Payment{ 
	
	private $_tableName = 'module_eshop_Payment';

	
	public function __construct() {
		$this->db =  Zend_Registry::getInstance()->db; 
	} 
	
	public function getPayments()
	{
		return $this->db->fetchAll("select * from `" . $this->_tableName ."` ORDER BY poradi"); 
	}
	
	
	public function renderPropertiesPayments($idPayment,$input)
	{ 
		$properties = array();
		$id = $idPayment?$idPayment:$input->id;
		if($id){
			$pay = $this->getPaymentById($id);
			$properties[] = new ContentProperty('id','Hidden',$id);
			$properties[] = new ContentProperty('poradi','Hidden',$pay['poradi']);
		}
		$payment = array();
		$nazev = $input->nazev?$input->nazev:$pay['nazev'];
		$popis = $input->popis?$input->popis:$pay['popis'];
		$paymentFirst = $input->paymentFirst?$input->paymentFirst:$pay['paymentFirst'];
		$huseEmailOR = $input->useEmailOR?$input->useEmailOR:$pay['useEmailOR'];
		$emailORSubject = $input->emailORSubject?$input->emailORSubject:$pay['emailORSubject'];
		$emailOrderReceived = $input->emailOrderReceived?$input->emailOrderReceived:$pay['emailOrderReceived'];
		$useEmailOS = $input->useEmailOS?$input->useEmailOS:$pay['useEmailOS'];
		$emailOSSubject= $input->emailOSSubject?$input->emailOSSubject:$pay['emailOSSubject'];
		$emailOrderSent= $input->emailOrderSent?$input->emailOrderSent:$pay['emailOrderSent'];
		$emailPRSubject= $input->emailPRSubject?$input->emailPRSubject:$pay['emailPRSubject'];
		$useEmailPR= $input->useEmailPR?$input->useEmailPR:$pay['useEmailPR'];
		$emailPaymentReceived= $input->emailPaymentReceived?$input->emailPaymentReceived:$pay['emailPaymentReceived'];
		
		$price = $input->price?$input->price:$pay['cena'];
		$deletable = $input->deletable?$input->deletable:$pay['deletable'];
		$chDeleteble = $deletable ? array('checked' => 'checked') :'';	
		$valid = $input->valid?$input->valid:$pay['valid'];
		$chValid = $valid ? array('checked' => 'checked') :'';
		$properties[] = new ContentProperty('nazev','Text',$nazev, array(), array(), array(), false);		
		$properties[] = new ContentProperty('popis','Textarea',$popis, array(), array(), array(), false);
		$properties[] = new ContentProperty('price','Text',$price, array(), array(), array(), false);
		$properties[] = new ContentProperty('deletable','Checkbox',$deletable,array(),array(),$chDeleteble);
		$properties[] = new ContentProperty('valid','Checkbox',$valid,array(),array(), $chValid);
		$properties[] = new ContentProperty('paymentFirst','Select',$paymentFirst, array(), array('1' => 'Před', '0' => 'Po'), false);
		$properties[] = new ContentProperty('useEmailOR','Checkbox',$huseEmailOR, array(), array(), array(), false);
		$properties[] = new ContentProperty('emailORSubject','Text',$emailORSubject, array(), array(), array(), false);
		$properties[] = new ContentProperty('emailOrderReceived','Wysiwyg',$emailOrderReceived, array(), array(), array(), false);
		$properties[] = new ContentProperty('useEmailOS','Checkbox',$emailOrderReceived, array(), array(), array(), false);
		$properties[] = new ContentProperty('emailOSSubject','Text',$emailOSSubject, array(), array(), array(), false);
		$properties[] = new ContentProperty('emailOrderSent','Wysiwyg',$emailOrderSent, array(), array(), array(), false);		
		$properties[] = new ContentProperty('useEmailPR','Checkbox',$useEmailPR, array(), array(), array(), false);
		$properties[] = new ContentProperty('emailPRSubject','Text',$emailPRSubject, array(), array(), array(), false);
		$properties[] = new ContentProperty('emailPaymentReceived','Wysiwyg',$emailPaymentReceived, array(), array(), array(), false);		
		return $properties;
	}
	
	public function saveSort($string)
	{
		$values = str_replace('row', '', $string);
		$sort = explode(',',$values);
		$position = 1;
		foreach ($sort as $key=>$valeu) {
			$where = $this->db->quoteInto('id = ?', $valeu);
			$data = array('poradi' => $position);
			$this->db->update($this->_tableName, $data,	$where);
			$position++;
		}
	}
	
	public function getAdminPayments($sort = 'poradi', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params = false)
	{
		if($sort =='title' )
		{
			$sort = 'nazev';
		}
		$select =  $this->db->select();
		$select->from(array('del' => $this->_tableName), array('*'));
		$sortType = $sortType?$sortType:'Asc';
		$select->order($sort . ' ' . $sortType); 
		$select->limit($limitCount, $limitStart);
		return $this->db->fetchAll($select); 
		
	}
	
	public function getPaymentById($id)
	{
		return $this->db->fetchRow( 'select * from `'. $this->_tableName .'` where id = :id', array('id' => $id) );
	}
	
	public function getValidPayments()
	{
		return $this->db->fetchAll("select * from `" . $this->_tableName ."` where valid = 1 ORDER BY poradi"); 
	}
	
	/**
		zkontroluje vstup a ulozi / updatuje dopravu
		@return nic, nebo chybovou hlášku
	*/
	public function save( $data )
	{		

		$test = $this->checkInput($data);
		if(!$data->poradi)
			$poradi = $this->db->fetchOne('SELECT max(poradi) FROM ' . $this->_tableName) + 1;
		else
			$poradi = $data->poradi;
		if($test)
			return $test;
		
		if(isset($data->deletable))
		{
			$data->deletable = 1;
		} else {
			$data->deletable = 0;
		}
		if(isset($data->valid))
		{  
			$data->valid = 1;
		}
		else{
			$data->valid = 0;
		}
		
		 
		
		$c_data = array(
							'nazev' => $data->nazev,
							'popis' => $data->popis,
							'cena' => $data->price,
							'valid' => $data->valid, 
							'deletable' => $data->deletable,
							'useEmailOR' => $data->useEmailOR,
							'emailOrderReceived' => $data->emailOrderReceived,
							'useEmailOS' => $data->useEmailOS,
							'emailOrderSent' => $data->emailOrderSent,
							'useEmailPR' => $data->useEmailPR,
							'emailPaymentReceived' => $data->emailPaymentReceived,
							'emailORSubject' => $data->emailORSubject,
							'emailOSSubject' => $data->emailOSSubject,
							'emailPRSubject' => $data->emailPRSubject,
							'paymentFirst' => $data->paymentFirst,
							'poradi' => $poradi
							);

		if(!$data->id)
			$this->db->insert( $this->_tableName , $c_data);
		else{
			
			$where = $this->db->quoteInto('id= ?', $data->id);
			$this->db->update($this->_tableName, $c_data, $where);
		}
	
		 
	}
	
	private function checkInput( $data ) // TO DO: dodelat osetreni vstupu
	{
		if(!$data->nazev)
			return 'Zadejte název';
		if(!is_numeric($data->cena) AND $data->cena)
			return 'Cena musí být číslo';	
		if($data->cena < 0)
			return 'Pouze nezáporná čísla';
	}
	
		public function deleteById($id)
	{
		$this->db->delete($this->_tableName, 'id = ' .$id. ' AND deletable = 1');
	}
	
	
		public function changeOrder($id, $direction)
	{
		$selected = $this->db->fetchRow('SELECT id, poradi FROM ' . $this->_tableName . ' WHERE id = ' . $id);
		switch ($direction) {
			case 'top':
				$change = $this->db->fetchRow('SELECT id, poradi FROM ' . $this->_tableName . ' ORDER BY poradi');
				if($selected['id'] == $change['id'])
					return;
				break;
			case 'bottom':
				$change = $this->db->fetchRow('SELECT id, poradi FROM ' . $this->_tableName . ' ORDER BY poradi DESC');
				if($selected['id'] == $change['id'])
					return;
				break;
			case 'up':
				$change = $this->db->fetchRow('SELECT id, poradi FROM ' . $this->_tableName . ' WHERE poradi < ' . $selected['poradi'] . ' ORDER BY poradi DESC');
				if(!$change)
					return;
				break;
			case 'down':
				$change = $this->db->fetchRow('SELECT id, poradi FROM ' . $this->_tableName . ' WHERE poradi > ' . $selected['poradi'] . ' ORDER BY poradi');
				if(!$change)
					return;
				break;
		}
		
		$tmp = $selected['poradi'];
		$selected['poradi'] = $change['poradi'];
		$change['poradi'] = $tmp;
		$this->db->update($this->_tableName, array('poradi' => null), 'id = ' . $change['id']);
		$this->db->update($this->_tableName, array('poradi' => $selected['poradi']), 'id = ' . $selected['id']);
		$this->db->update($this->_tableName, array('poradi' => $change['poradi']), 'id = ' . $change['id']);
	}
	 
}	
?>