<?

class module_Excel {
	
	public function __construct() {
		$this->xls = new PHPExcel();
		$this->init();
	}
	
	function init(){
		$this->colLabels = range('A', 'Z');
		$this->xls->setActiveSheetIndex(0);
	}
	
	function setData($data = null, $sheet = 0, $header = array(), $startLetter = 'A', $startNumber = 1){
		/*		
		foreach ($this->colLabels as $cl){
			if($cl == $startLetter){
				
				break;
			}
		}
		prev($this->colLabels);
		*/
		$colLabels = range($startLetter, 'Z');
		
		$this->xls->setActiveSheetIndex($sheet);
		
		/* HEADERS */
		if(count($header)){
			$letter = current($colLabels);
			foreach ($header as $k => $v){				
				$this->xls->getActiveSheet()->setCellValue($letter . $startNumber, $v);
				$letter = next($colLabels);			
			}
			$startNumber++;
			reset($colLabels);
		}
		
		switch (gettype($data)){
			case 'array':
				foreach ($data as $k => $row){
					$letter = current($colLabels);
					$number = $startNumber;
					foreach ($row as $k2 => $v){	  
						if($k2 && $k2 == 'cellStyle'){ 
							$this->xls->getActiveSheet()->getStyle($letter . $number)->applyFromArray($v);
						} else {
							$this->xls->getActiveSheet()->setCellValue($letter . $number, $v);
							$letter = next($colLabels);
							
							// $this->xls->getActiveSheet()->getColumnDimension($letter . $number)->setWidth(20);;
						}  
						 
					} 
					reset($colLabels);
					$startNumber+= 1;					
				}
				break;
			default:
				e(gettype($data));
				return;
				break;
		}
	}
	
	function writeToFile2007($filename = null, $output = false, $outputName = ''){		
		$objWriter = new PHPExcel_Writer_Excel2007($this->xls);
		$objWriter->setPreCalculateFormulas(false);
		if($output){	
			ob_clean();
	        header('Content-Type: application/vnd.ms-excel;');
			header("Content-type: application/x-msexcel");  // application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
	        header("Content-Disposition: inline; filename=$outputName");
	        header("Expires: 0");
	        header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
	        header("Pragma: public");	        
			$objWriter->save($filename);
			if (!@readfile($filename)){		
				die($filename);
			} else {
				
			}
			die();
		} else {
			$objWriter->save($filename);
		}
	}
	
	
	
	function writeToFile2003($filename = null, $output = false, $outputName = '', $tempdir = ''){		   
		
		$objWriter = new PHPExcel_Writer_Excel5($this->xls);
		//  $objWriter->setTempDir($tempdir);   
		if($tempdir){ 
			$objWriter->setTempDir($tempdir);    
		}
		//$objWriter->setPreCalculateFormulas(false); 
		if($output){	
			ob_clean();
	        header('Content-Type: application/vnd.ms-excel;');
			header("Content-type: application/x-msexcel");  // application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
	        header("Content-Disposition: inline; filename=$outputName");
	        header("Expires: 0");
	        header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
	        header("Pragma: public");	        
			$objWriter->save($filename);
			if (!@readfile($filename)){	 	
				die($filename);
			} else { 
				
			}
			die();
		} else {
			$objWriter->save($filename);
		}
	}
}
?>