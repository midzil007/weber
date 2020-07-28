<?
/**
	slouží pro uložení a poslaní objednávek do xls
  */
class module_ExportOrder {
	
	private $start = '';
	private $end = '';
	private $marze = '';
	private $provize = 0;
	
	public function __construct($view) {
		$this->db = Zend_Registry::getInstance ()->db;
		$this->_tableName = 'module_eshop_orders';
		$this->_tableNameItems = 'module_eshop_order_items';
		$this->objPHPExcel = new PHPExcel ();
		$settings = Zend_Registry::getInstance()->settings;
		$this->provize = $settings->getSettingValue('provize');
		$this->setDate($view->inputGet->start,$view->inputGet->end);
	
		

	}
	
	
	function setDate($startMonth = false,$endMonth = false)
	{
		$year = date("Y");
		$mnout = date("n");
		$this->start = $startMonth ? $year.'-'.$startMonth.'-01 00:00:00' : $year.'-'.$mnout.'-01 00:00:00';
		$this->end = $endMonth ? $year.'-'.$endMonth.'-31 00:00:00' :  $year.'-'.$mnout.'-31 00:00:00';
		$dtz = new DateTimeZone("Europe/Prague"); //your timezone
		if(!$startMonth){
			$now = new DateTime(date($this->start), $dtz);
			$now->modify('-1 month');
			$this->start = $now->format("Y-m-d H:i:s");
		}
		if(!$endMonth){
			$nowTR = new DateTime(date($this->end), $dtz);
			$nowTR->modify('-1 month');
			$this->end = $nowTR->format("Y-m-d H:i:s");
		}	
		$this->saveToFile();
	}
	
	function sendEmail($limits)
	{
	
		$mail = new Email();
		$title ='Objednávky na '.Utils::getWebUrl();
		$mainText = 'Sumarizace objednávek z '.Utils::getWebUrl().'  od  '.$this->start.' do '.$this->end;
		$emailText = Helpers::prepareEmail(
				$mail,
				$mainText,
				false,
				false,
				'484848',
				'000000'
		);
		
	
		$mail->setBodyText(strip_tags(urldecode($emailText)));
		$mail->setBodyHtml(urldecode($emailText));
	
		$mail->setSubject($title);
		//$mail->addTo('miloslav.skrha@gmail.com','mil');
		$mail->addTo('obchod@specshop.cz','souhrn','souhrn');
		//$mail->addTo('nosil@eportaly.cz', 'misa', 'misa');
		$mail->setFrom('debug@specshop.cz', 'Souhrn - objednávky');
		$filePath = Utils::getWebUrl().'/objednavky.xls';
		$fileContents = file_get_contents($filePath);
		$file = $mail->createAttachment($fileContents);
		$file->filename = "objednavkyJura.xls";
		try {
			$mail->send();
			
		} catch (Exception $e) { }
	}
	
	
	public function getOrders() {
			
		
		$all = $this->db->fetchAll ( "select *, suma / (1+((`dph`)/count(o_id)/100)) as sumaBezDPH from " . $this->_tableName . " as orders," .$this->_tableNameItems. " as items  where
				orders.id = items.o_id and
				 created >=:star and created <=:e and state !=:s group by orders.id", array (
				's' => 'DELETED', 'star' => $this->start, 'e'=> $this->end 
		) );
// 		e($this->start);
		//pr($all);
		return $all;
	}
	
	
	public function saveToFile() {
		// Set document properties
		$this->objPHPExcel->getProperties ()->setCreator ( "Specshop" )->setLastModifiedBy ( "Specshop" )->setTitle ( "Objednávky" )->setSubject ( "Objednávky" )->setDescription ( "Objednávky" )->setKeywords ( "Objednávky" )->setCategory ( "Objednávky" );
		
		
		/// set format
		
		$styleArray = array(
				'font' => array(
						'bold' => true,
						'color' => array(
								'argb' => 'ffffff',
						)
				),
				'fill' => array(
						'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
						'rotation' => 90,
						'startcolor' => array(
								'argb' => 'fc000f',
						),
						'endcolor' => array(
								'argb' => 'f63f47',
						),
				),
		);
		 
		$this->objPHPExcel->getActiveSheet()->getStyle('A1:P2')->applyFromArray($styleArray);
		// Add some data
		
		$this->objPHPExcel->setActiveSheetIndex ( 0 )
		->setCellValue ( 'A1', 'specSHOP.cz - '.Utils::getWebUrl());
		
		$this->objPHPExcel->setActiveSheetIndex ( 0 )
			->setCellValue ( 'A2', 'Číslo' )
			->setCellValue ( 'B2', 'Jméno' )
			->setCellValue ( 'C2', 'Příjmení' )
			->setCellValue ( 'D2', 'Firma' )
			->setCellValue ( 'E2', 'Celkem s DPH Kč')
			->setCellValue ( 'F2', 'Celkem bez DPH Kč')
			->setCellValue ( 'G2', 'Provize Kč')
			->setCellValue ( 'H2', 'Datum')
			->setCellValue ( 'I2', 'Doprava')
			->setCellValue ( 'J2', 'Cena dopravy Kč')
			->setCellValue ( 'K2', 'Platba')
			->setCellValue ( 'L2', 'Cena platby Kč')
			->setCellValue ( 'M2', 'Ulice')
			->setCellValue ( 'N2', 'Město')
			->setCellValue ( 'O2', 'PSČ')
			->setCellValue ( 'P2', 'Poznámka');
		
		// Miscellaneous glyphs, UTF-8
		
		$orders = $this->getOrders();
		
		$line = 3;
		$suma = 0;
		foreach ($orders as $item)
		{
			$provize =  round($item['sumaBezDPH'] / (100/$this->provize));
			$suma += $item['suma'];
			$sumBez += $item['sumaBezDPH'];
			$sumProvize += $provize;
			$name = explode(' ',$item['fu_jmeno']);
			$this->objPHPExcel->setActiveSheetIndex ( 0 )
			->setCellValue ( 'A'.$line, $item['id'])
			->setCellValue ( 'B'.$line, $name[0])
			->setCellValue ( 'C'.$line, $name[1])
			->setCellValue ( 'D'.$line, $item['fu_firma'])
			->setCellValue ( 'E'.$line, $item['suma'])
			->setCellValue ( 'F'.$line, $item['sumaBezDPH'])
			->setCellValue ( 'G'.$line, $provize)
			->setCellValue ( 'H'.$line, Utils::formatDate($item['created']))
			->setCellValue ( 'I'.$line, $item['deliveryText'])
			->setCellValue ( 'J'.$line, $item['deliveryPrice'])
			->setCellValue ( 'K'.$line, $item['paymentText'])
			->setCellValue ( 'L'.$line, $item['paymentPrice'])
			->setCellValue ( 'M'.$line, $item['fu_ulice'])
			->setCellValue ( 'N'.$line, $item['fu_mesto'])
			->setCellValue ( 'O'.$line, $item['fu_psc'])
			->setCellValue ( 'P'.$line, $item['note']);
			$line++;
		}
		

		
		$styleArraya = array(
				'font' => array(
						'bold' => true,
				)
		);
		
		$this->objPHPExcel->getActiveSheet()->getStyle('E'.$line.':G'.$line)->applyFromArray($styleArraya);
		
		$this->objPHPExcel->setActiveSheetIndex ( 0 )
		->setCellValue ( 'A'.$line, 'Celkem Kč')
		->setCellValue ( 'E'.$line, $suma)
		->setCellValue ( 'F'.$line, $sumBez)
		->setCellValue ( 'G'.$line, $sumProvize);
		
		// Rename worksheet
		$this->objPHPExcel->getActiveSheet ()->setTitle ( 'Objednávky' );
		// šířka pro všechny sloupce
		for ($ch = 'A'; $ch != 'AA'; $ch++) {
			$this->objPHPExcel->getActiveSheet()->getColumnDimension($ch)->setAutoSize(true);
		}
		
		// Set active sheet index to the first sheet, so Excel opens this as the
		// first sheet
		$this->objPHPExcel->setActiveSheetIndex ( 0 );
		

		
		$objWriter = PHPExcel_IOFactory::createWriter ( $this->objPHPExcel, 'Excel5' );
		$objWriter = new PHPExcel_Writer_Excel2007 ( $this->objPHPExcel );
		$objWriter->setOffice2003Compatibility ( true );
		
		e($objWriter->save ( "objednavkyJura.xls" ));
	}
}

?>