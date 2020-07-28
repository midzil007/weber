<?
class module_RenderPDF { 
	
	public function __construct($orient = 'p', $unit = 'mm', $size = 'A4') { 			
		require_once('libs/tcpdf/config/lang/eng.php'); 
		require_once('libs/tcpdf/tcpdf.php');
		$this->pdf = $pdf = new TCPDF($orient, $unit, $size); 	 
	}	

	function generateAndSave($path = false, $html,$title){
		//e($customer);  die(); 
		//e($reservations); die();    
		if(!$path){
			$conf = Zend_Registry::getInstance()->config;
			$conf = Zend_Registry::getInstance()->config;
			$path = $conf->htdocsRoot .'/attachment/';    
		  
		}        
		return $this->generatePdf(true, $path,$html,$title);   
	}       

	private function teaser( $html ) { 
		$html = preg_replace("/<img[^>]+\>/i", " ", $html);    
    	$html = str_replace( '&nbsp;', ' ', $html );
    	do {
        	$tmp = $html;
        	$html = preg_replace(
            	'#<([^ >]+)[^>]*>[[:space:]]*</\1>#', '', $html );
    	} while ( $html !== $tmp );

    return $html;
}
	
	function generatePdf($save = false, $path, $html,$title,$returnFullPath = false){	    
		$this->pdf->SetFont("freesans", "", 11);   	
		$this->pdf->SetPrintHeader(false);   
		$this->pdf->SetPrintFooter(false);
		$this->pdf->AddPage();	 	      
	    $html = $this->teaser($html);       
		$this->pdf->writeHTML($html, true, 0, true, 0);   
		if($save){
			$this->pdf->Output($path.$title . '.pdf', 'F'); 
		} else { 
			$this->pdf->Output($path.$title . '.pdf', 'D');    
		} 
		return $title . '.pdf';   
	}	
}
?>