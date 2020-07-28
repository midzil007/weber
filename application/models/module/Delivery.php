<?
  /**
	uzivatelsky editovatelne zpusoby dopravy
	@author Jakub Kratena
  */
use Zend\Validator\Explode;
class module_Delivery{ 
	
	private $_tableName = 'module_eshop_Delivery';
	private $_tableName2 = 'eshop_delivery_payment';
	private $_tableName3 = 'module_eshop_Payment';
	
	
	public function __construct() {
		$this->db =  Zend_Registry::getInstance()->db; 
	} 
	
	public function saveSort($string)
	{
		$values = str_replace('row', '', $string);
	  
		$sort = explode(',',$values);
		$position = 1;
		foreach ($sort as $key=>$valeu) {
			$where = $this->db->quoteInto('id = ?', $valeu);
			$data = array('poradi' => $position);
			$this->db->update($this->_tableName, $data,$where);
			$position++;
		}
	}
	
		public function getDeliveries2()
	{
		$ress = $this->db->fetchAll("select * from `" . $this->_tableName ."` ORDER BY poradi"); 
		foreach($ress as $res)
		{
			$res['payments'] = $this->getValidPayments( $res['id'] );
			$r[] = $res;
			   
		}    
		return $r;
	}
	
	  
	
	
	
	public function getAdminDeliveries($sort = 'poradi', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params = false)
	{
		if($sort == 'title' )
		{
			$sort = 'nazev';
		}
		$select =  $this->db->select();
		$select->from(array('del' => $this->_tableName), array('*'));
		$sortType = $sortType?$sortType:'Asc';
		$select->order($sort . ' ' . $sortType); 
		$select->limit($limitCount, $limitStart);
		//pr($select->__toString());
		return $this->db->fetchAll($select); http://nahled.eportaly.cz/cms/eshop/delivery/getItems/1/ajax/1/id/16#tabs-1
	}
		
	public function renderPropertiesDelivery($idDelivery,$input)
	{ 
		$validPayments = $this->getPaymentValid(true);
		$properties = array();
		$id = $idDelivery?$idDelivery:$input->id;
		if($id>0 ){
			$delivery = $this->getDeliveryById($id);
			$paymentsIds = $this->getPaymentsIds($id);

			foreach ($paymentsIds as $value) {
				$pay[] = $value;
			}
			$ident = implode('|',$pay);

			$selected = isset($this->optionsChecked[$ident])?1:0;
			$properties[] = new ContentProperty('id','Hidden',$id);
			$properties[] = new ContentProperty('poradi','Hidden',$delivery['poradi']);
		}
		$nazev = $input->nazev?$input->nazev:$delivery['nazev'];
		$popis = $input->popis?$input->popis:$delivery['popis'];
		$hmotnost_od = $input->hmotnost_od?$input->hmotnost_od:$delivery['hmotnost_od'];
		$hmotnost_do = $input->hmotnost_do?$input->hmotnost_do:$delivery['hmotnost_do'];
		$zdarma_nad = $input->zdarma_nad?$input->zdarma_nad:$delivery['zdarma_nad'];
		$price = $input->price?$input->price:$delivery['price'];
		$deletable = $input->deletable?$input->deletable:$delivery['deletable'];
		$chValid = $deletable ? array('checked' => 'checked') :'';
		$properties[] = new ContentProperty('nazev','Text',$nazev, array('class' => 'validate[required,length[0,100]] text-input'), array(), array(), false);		
		$properties[] = new ContentProperty('popis','Wysiwyg',$popis, array(), array(), array(), false);
		$properties[] = new ContentProperty('hmotnost_od','Text',$hmotnost_od, array(), array(), array(), false);
		$properties[] = new ContentProperty('hmotnost_do','Text',$hmotnost_do, array(), array(), array(), false);
		$properties[] = new ContentProperty('zdarma_nad','Text',$zdarma_nad, array(), array(), array(), false);
		$properties[] = new ContentProperty('price','Text',$price, array('class' => 'validate[required,custom[onlyNumber]] text-input'), array(), array(), false);
		$properties[] = new ContentProperty('deletable','Checkbox',$deletable, $chValid);
		$properties[] = new ContentProperty('payments','CheckboxGroup',$ident, array(),$validPayments);
		
		return $properties;
	}
	
	public function getDeliveries()
	{
		$ress = $this->db->fetchAll("select * from `" . $this->_tableName ."` ORDER BY poradi"); 
		foreach($ress as $res)
			$res['payments'] = $this->getValidPayments( $res['id'] );
		return $ress;
	}

	    
	    
	/**
		vrati vsechny platne moznosti dopravy
	*/
	public function getValidDeliveries()
	{
		$ress = $this->db->fetchAll("select * from `" . $this->_tableName ."` WHERE valid=1 ORDER BY poradi "); 
		foreach($ress as $res)
		{
			if($res['payments'] = $this->getValidPayments( $res['id'] )) // k danemu doruceni exisuje nejaky zpusob platby
				$ress2[$res['id']] = $res;
		}
		return $ress2;  
	}
	
	
	public function getDeliveryIdByName($name)
	{
		return $this->db->fetchOne("select id from " . $this->_tableName. " where nazev = ?",$name);
	}
	
	public function showValidDeliveries()
	{
		$arr = array();
		$ress = $this->db->fetchAll("select * from `" . $this->_tableName ."` WHERE valid=1 ORDER BY poradi ");
		foreach ($ress as $value)
		{
			$arr[$value['id']] = $value['nazev'].' - ('.$value['price'].' Kč)';
		}
		return $arr;
	}
	
	/**
	vrátí moznosti dopravy podle vahy zbozi
	@var weight hmotnost zbozi
	*/
	public function getValidDeliveriesByWeight( $weight )
	{
		//print_r( $weight );
		$ress = $this->db->fetchAll("select * from `" . $this->_tableName ."` WHERE valid=1 AND hmotnost_od <= :w1 AND ( hmotnost_do >= :w2 OR hmotnost_do = 0 ) ORDER BY poradi", array('w1' => $weight , 'w2' => $weight)); 
		  
		foreach($ress as $res)
		{
			if($res['payments'] = $this->getValidPayments( $res['id'] )) // k danemu doruceni exisuje nejaky zpusob platby
				$ress2[] = $res;
		}
		
		return $ress2;
	}
	
	public function getDeliveryById($id) 
	{
		$res = $this->db->fetchRow( 'select * from `'. $this->_tableName .'` where id = :id', array('id' => $id) );
		$res['payments'] = $this->getValidPayments($id);
		return $res;
	}
	
	
	public function getPaymentValid($disable = false)
	{
		$ress = $this->db->fetchAll( 'SELECT id, nazev FROM ' .$this->_tableName3. ' WHERE deletable = 0 and valid = 1');
		$returnPay =  array();
		if(!$disable){
			$returnPay['0'] = 'Všechny platby';
		}
		foreach ($ress as $value)
		{
			$returnPay[$value['id']] = $value['nazev'];
		}
		return $returnPay;
	}
	
	/**
	@var deliveryId 
	 vrati pole id plateb
	    radsi pouzit getValidPayments ....
	*/
	private function getPaymentsIds( $deliveryId )
	{
		$ress = $this->db->fetchAll( 'SELECT payment_id FROM ' .$this->_tableName2. ' WHERE delivery_id = :id', array('id' => $deliveryId));
		foreach($ress as $res)
			$ret[] = $res['payment_id'];
		return $ret;
	}
	
	
	/**
		vrati pole pridelenych plateb (platných)
	*/
	public function getAppValidPayments( $deliveryId ) 
	{
		return $this->db->fetchAll( 'SELECT mp.id, mp.nazev, mp.popis, mp.cena FROM ' . $this->_tableName3 . ' as mp JOIN ' . $this->_tableName2 . ' as dp ON (mp.id=dp.payment_id) JOIN ' . $this->_tableName . ' as md ON (dp.delivery_id = md.id) WHERE agmo = "0" and  md.id=:id AND mp.valid=1  ORDER BY mp.poradi', array('id' => $deliveryId)); 
	} 
	
	/**
		vrati pole pridelenych plateb (platných)
	*/
	private function getValidPayments( $deliveryId ) 
	{
		$all = $this->db->fetchAll( 'SELECT mp.id, mp.nazev, mp.popis, mp.cena FROM ' . $this->_tableName3 . ' as mp JOIN ' . $this->_tableName2 . ' as dp ON (mp.id=dp.payment_id) JOIN ' . $this->_tableName . ' as md ON (dp.delivery_id = md.id) WHERE md.id=:id AND mp.valid=1  ORDER BY mp.poradi', array('id' => $deliveryId));
		
		$s = array();
		foreach ($all as $p){
			$s[$p['id']] = $p;
		}
		return $s; 
	}
	
	public function showValidPayments($deliveryId,$priceFirst = false)
	{
		$all = $this->getValidPayments($deliveryId);
		$arr = array();
		foreach ($all as $v)
		{
			if($priceFirst)
			{
				return $v['cena'];	
			}
			else{
				$arr[$v['id']] = $v['nazev'].' - '.$v['cena'].' Kč';
			}
		}
		return $arr;
	}
	
	/**
		zkontroluje vstup a ulozi / updatuje dopravu
		@return nic, nebo chybovou hlášku
	*/
	public function saveDelivery( $data )
	{
		$test = $this->checkInput($data);
		
		if($test)
			return $test;
		if(!$data->poradi)
			$poradi = $this->db->fetchOne('SELECT max(poradi) FROM ' . $this->_tableName) + 1;
		else
			$poradi = $data->poradi;
		if($data->deletable > 0)
		{
			$data->deletable = 1;
		}
		$c_data = array(
							'nazev' => $data->nazev,
							'hmotnost_od' => $data->hmotnost_od,
							'hmotnost_do' => $data->hmotnost_do,
							'zdarma_nad' => $data->zdarma_nad,
							'popis' => $data->popis,
							'price' => $data->price,
							'deletable' => $data->deletable,
							'poradi' => $poradi
							);
				
		if(!$data->id){
			$this->db->insert( $this->_tableName , $c_data);
			$id =  $this->db->lastInsertId();
		}
		else{
			$where = $this->db->quoteInto('id= ?', $data->id);
			$this->db->update($this->_tableName, $c_data, $where);
			$id = $data->id;
		}
		//echo "test:" . $id;
		$this->savePayments($id, $data->payments);
		
	}
	
	private function checkInput( $data ) // TO DO: dodelat osetreni vstupu
	{
		if(!$data->nazev)
			return 'Zadejte název';
		
		if(!is_numeric($data->hmotnost_od) AND $data->hmotnost_od)
			return 'Hmotnost musí být číslo';
		if(!is_numeric($data->hmotnost_do) AND $data->hmotnost_do)
			return 'Hmotnost musí být číslo';
		if(!is_numeric($data->price) AND $data->price)
			return 'Cena musí být číslo';	
		if($data->hmotnost_od < 0)
			return 'pouze nezáporná čísla';
		if($data->hmotnost_do < 0)
			return 'pouze nezáporná čísla';
		if($data->price < 0)
			return 'pouze nezáporná čísla';
		if($data->hmotnost_do)
			if($data->hmotnost_do < $data->hmotnost_od)
				return 'neplatný rozsah hmotnosti';
	}
	
	public function deleteById($id)
	{
		$this->db->delete($this->_tableName, 'id = ' .$id);
	}
	
	/**
	@var payments pole id plateb
	@var deliveryId id dopravy, pro kterou se ukladaji platby
	smaze vsechny stare zaznamy se stejnym delivery ID, a zapise nove
	*/
	public function savePayments( $deliveryId, $payments )
	{
		$where = $this->db->quoteInto('delivery_id = ?', $deliveryId);
		$this->db->delete($this->_tableName2, $where);
		
		foreach ($payments as $paymentId=>$value)
			$this->db->insert($this->_tableName2, array( 'delivery_id' => $deliveryId , 'payment_id' => $paymentId) );
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