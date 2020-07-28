<?
class module_FakturaPDF { 
	  
	public function __construct($orient = 'p', $unit = 'mm', $size = 'A4') { 			
		require_once('libs/tcpdf/config/lang/eng.php'); 
		require_once('libs/tcpdf/tcpdf.php');
		$this->pdf = $pdf = new TCPDF($orient, $unit, $size); 	 
	}	

	function generateAndSave($user,$title){
   
		//e($customer);  die(); 
		//e($reservations); die();    
		if(!$path){ 
			$conf = Zend_Registry::getInstance()->config;
			$conf = Zend_Registry::getInstance()->config;
			$path = $conf->htdocsRoot .'/attachment/';    
		  
		}         
		return $this->generatePdf($user, $path,$html,$title);    
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
	
	function generatePdf($user){
	     
		 
		$item = '<table>';
	foreach ($all as $key => $value) {  
		$par['idLoan'] = $value['id_loan'];
		$l = $mLoan->getLoans(false,false,0,10000,$par);
		$provizeAll += $value['provize'];  
		$dph += $value['provize']-$provize;
		$provize = $value['provize']*((100-19)/100);  
		$item .= '<tr>';  
		$item .= '<td>'.$l['id_loan'].'-'.$l['cust_jmeno'].' '.$l['cust_prijmeni'].'-'.$l['product_title'].'</td>';
		$item .= '<td>1</td>';
		$item .= '<td>'.($value['provize']-$provize).'</td>';   
		$item .= '<td>'.$provize.'</td>'; 
		$item .= '<td>'.$provize.'</td>';         
		$item .= '</tr>';
	}
 		$celkemBezDPH = $provizeAll-$dph;
		$item .= '<table>';

		$this->pdf->SetPrintHeader(false);   
		$this->pdf->SetPrintFooter(false);
		  
		  // set font 
	$this->pdf->SetFont("freesans", "", 8);   	 

// add a page
$this->pdf->AddPage();    

/* NOTE:
 * *********************************************************
 * You can load external XHTML using :
 *
 * $html = file_get_contents('/path/to/your/file.html');
 *
 * External CSS files will be automatically loaded.
 * Sometimes you need to fix the path of the external CSS.
 * *********************************************************
 */
 
$date = date("j.n.Y");  
$name = $user['jmeno'].' '.$user['prijmeni'];
$ulice = $user['ulice'].' '.$user['cp'];		
$mesto = $user['mesto'];
$psc = $user['psc'];		
$osobni = $user['cislo_osobni'];
// define some HTML content with style
$html = <<<EOF
<!-- EXAMPLE OF CSS STYLE -->
<style>
    h1 {   
        font-size: 24pt;
    }
    table.first {
 		height:10pt;     
        background-color: #000;

    }    
</style> 
<h1 style="text-align:right;" class="title">Provizní přehled</i></h1>  
<table style="font-size:8px;">    
	<tr>  
		<td><strong>Dodavatel</strong></td>   
		<td><strong>Variabilní symbol:</strong></td> 
	</tr>   
	<tr> 
		<td>&nbsp;&nbsp;&nbsp;&nbsp;Váš finanční dům s.r.o</td>  
		<td><strong>Konstantní symbol:</strong></td>   
	</tr>
	<tr>   
		<td>&nbsp;&nbsp;&nbsp;&nbsp;Branická 26/43</td> 
		<td></td>
	</tr>
	<tr>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;147 00 Praha 4</td>
		<td></td>
	</tr>
	<tr>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;IČ: 034 57 494</td>
		<td></td> 
	</tr>  
	<tr>  
		<td>&nbsp;&nbsp;&nbsp;&nbsp;IČ:60745347</td>
		<td></td> 
	</tr>
	<tr>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;ÚČET: 19-19062111 / 0100</td>
	</tr> 
</table > 
 <div class="padding-bottom: 15px;"></div> 
<table >        
	<tr> 
		<td><strong>Datum vystavení:</strong>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$date<br/>
		<strong>Datum splatnosti:</strong>  
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$date<br/>   
		<strong>Datum zdan. plnění:</strong> 
		&nbsp;&nbsp;&nbsp;$date</td>     
		<td> 
		<table  border="1">
		<tr>  
			<td><strong>&nbsp;<br>&nbsp;&nbsp;&nbsp;&nbsp;$name</strong><br>
			&nbsp;&nbsp;&nbsp;&nbsp;$osobni<br>
			&nbsp;&nbsp;&nbsp;&nbsp;$ulice<br> 
			&nbsp;&nbsp;$mesto<br>   
			&nbsp;&nbsp;$psc<br>&nbsp;</td>     
		</tr> 
</table>
		</td> 
	</tr>  
</table>
&nbsp;<br> 
&nbsp;<br> 


<p><strong></strong></p>  
<table>
	<tr>
		<td><strong>Označení položky</strong></td>
		<td><strong>Množství</strong></td>
		<td><strong>DPH %</strong></td>
		<td><strong>Jedn. cena</strong></td>
		<td><strong>Celkem bez DPH</strong></td> 
	</tr>
</table>   
$item

<table>
<tr>
<td>&nbsp;</td>
<td>&nbsp;</td>   
</tr>
<tr>
<td></td>
<td>
<table border="1" style="text-align:right;"  > 
	<tr> 
		<td width="250">&nbsp;<br>&nbsp;&nbsp;&nbsp;&nbsp;Celková částka bez DPH:$celkemBezDPH&nbsp;&nbsp;&nbsp;&nbsp;<br>

		&nbsp;&nbsp;&nbsp;&nbsp;DPH: $dph&nbsp;&nbsp;&nbsp;&nbsp;<br>     
  
		&nbsp;&nbsp;Částka k úhradě: $provizeAll&nbsp;&nbsp;&nbsp;&nbsp;<br>&nbsp;</td>      
	</tr>
	</table>  
</td>
</tr>
</table>   
<div class="padding-bottom: 100px;"></div>    
<p style="line-height:1px;text-align:right;"> 
&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;
&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>
&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br><br>&nbsp;<br>&nbsp;<br>&nbsp;<br>
&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>
&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>
&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br> 
&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>Razítko a podpis   
</p> 
EOF;

// output the HTML content
$this->pdf->writeHTML($html, true, false, true, false, '');

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
  
		if($save){
			$this->pdf->Output($path.$title . '.pdf', 'F'); 
		} else { 
			$this->pdf->Output($path.$title . '.pdf', 'I');     
		} 
		return $title . '.pdf';   
	}	
function generatePdfSestava($user){	     	
		$this->pdf->SetPrintHeader(false);   
		$this->pdf->SetPrintFooter(false);
		  
		  // set font 
	$this->pdf->SetFont("freesans", "", 8);   	 

// add a page
$this->pdf->AddPage();    

/* NOTE:
 * *********************************************************
 * You can load external XHTML using :
 *
 * $html = file_get_contents('/path/to/your/file.html');
 *
 * External CSS files will be automatically loaded.
 * Sometimes you need to fix the path of the external CSS.
 * *********************************************************
 */
 
$date = date("j.n.Y");  
$name = $user['jmeno'].' '.$user['prijmeni'];
$ulice = $user['ulice'].' '.$user['cp'];		
$mesto = $user['mesto'];
$psc = $user['psc'];		
$osobni = $user['cislo_osobni'];
// define some HTML content with style
$html = <<<EOF
<!-- EXAMPLE OF CSS STYLE -->
<style>
    h1 {   
        font-size: 24pt;
    }
    table.first {
 		height:10pt;     
        background-color: #000;

    }  
</style> 
<h1 style="text-align:right;" class="title">Faktura - daňový doklad č. </i></h1>  
<table style="font-size:8px;">    
	<tr>  
		<td><strong>Dodavatel</strong></td>   
		<td><strong>Variabilní symbol:</strong></td> 
	</tr>   
	<tr> 
		<td>&nbsp;&nbsp;&nbsp;&nbsp;Váš finanční dům s.r.o</td>  
		<td><strong>Konstantní symbol:</strong></td>   
	</tr>
	<tr>   
		<td>&nbsp;&nbsp;&nbsp;&nbsp;Branická 26/43</td> 
		<td></td>
	</tr>
	<tr>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;147 00 Praha 4</td>
		<td></td>
	</tr>
	<tr>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;IČ: 034 57 494</td>
		<td></td> 
	</tr>  
	<tr>  
		<td>&nbsp;&nbsp;&nbsp;&nbsp;IČ:60745347</td>
		<td></td> 
	</tr>
	<tr>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;ÚČET: 19-19062111 / 0100</td>
	</tr> 
</table > 
 <div class="padding-bottom: 15px;"></div> 
<table >        
	<tr> 
		<td><strong>Datum vystavení:</strong>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$date<br/>
		<strong>Datum splatnosti:</strong>  
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$date<br/>   
		<strong>Datum zdan. plnění:</strong> 
		&nbsp;&nbsp;&nbsp;$date</td>     
		<td> 
		<table  border="1">
		<tr>  
			<td><strong>&nbsp;<br>&nbsp;&nbsp;&nbsp;&nbsp;$name</strong><br>
			&nbsp;&nbsp;&nbsp;&nbsp;$osobni<br>
			&nbsp;&nbsp;&nbsp;&nbsp;$ulice<br> 
			&nbsp;&nbsp;$mesto<br>   
			&nbsp;&nbsp;$psc<br>&nbsp;</td>     
		</tr> 
</table>
		</td> 
	</tr>  
</table>
&nbsp;<br> 
<table>
	<tr>
		<td width="250"></td>
		<td><strong>Vyhodnocení produkce za období 2010/02</strong> 
		</td>  
	</tr>
</table>   
&nbsp;<br> 
<table>
	<tr>
		<td width="250"></td>      
		<td width="110">Pozice: 8</td>   
		<td> 
		<table width="170">     
			<tr>
				<td align="left">Nárok</td>
				<td align="right">2 166,08 Kč</td>
			</tr>
			<tr> 
				<td align="left">Proplaceno</td>
				<td align="right">2 166,08 Kč</td>
			</tr>
			<tr>
				<td align="left">Nárok celk</td> 
				<td align="right">2 166,08 Kč</td>
			</tr>
			<tr>
				<td align="left">Propl. celk.: </td>
				<td align="right">2 166,08 Kč</td>
			</tr>
			<tr>
				<td align="left">Propl. celk.: </td>
				<td align="right">2 166,08 Kč</td>
			</tr>
			<tr>
				<td align="left">Kredit </td>
				<td align="right">2 166,08 Kč</td>
			</tr>
			<tr> 
				<td align="left">Kredit </td>
				<td align="right">2 166,08 Kč</td>
			</tr>
			<tr>
				<td align="left">Saldo vyrovnané</td>
				<td align="right">2 166,08 Kč</td>
			</tr>
		</table> 
		</td>
	</tr>
</table>
<p><strong>Příkazy k úhradě</strong></p>  
<table>
	<tr>
		<td>Úč. období</td>
		<td>Nárok[Kč]</td>
		<td>Vráceno[Kč]</td>
		<td>Splaceno[Kč]</td> 
	</tr>
</table>
<table>  
	<tr>
		<td width="30">201002</td>
		<td>2 166,08</td>
		<td>0</td>
		<td>0</td> 
	</tr>
</table> 
<p><strong>Přímá produkce</strong></p>  
<table>
	<tr>
		<td>Rodné číslo</td>
		<td>Příjmení</td>
		<td>Program</td>
		<td>PZ</td>
		<td>Číslo smlouvy</td>
		<td>Tarif</td>
		<td>Částka[Kč]</td> 
	</tr>
</table>
<table>
	<tr>
		<td>511129011</td>
		<td>Beránek</td>
		<td>KOOP 3RG udržovací provize</td>
		<td>PZ1</td>
		<td>1930059144</td>
		<td>185,00</td>
		<td>60,36</td>
	</tr>
</table>
<p><strong>Nepřímá produkce</strong></p>  
<table>
	<tr style="border-bottom:1px solid #FF00FF;">
		<td>Osobní číslo</td>
		<td>Příjmení</td>
		<td>Tarif</td>
		<td>Částka[Kč]</td>
	</tr>
	</table>
<table> 
	<tr>  
		<td>664</td>
		<td>Procházka</td>
		<td>20,00</td>
		<td>-52,54</td>
	</tr>
</table>
<div class="padding-bottom: 15px;"></div>  
<p style="line-height:1px;">Smluvní vztah, provizní stav: OZ, provizněaktivní 
</p>
<p>Dosažená kvaliﬁkace pro postupv kariéře: RF(Ano), ORFP(Ano), 2-denníMA(Ano), Akademiegarantů(Ne), UkončenáMA(Ano), ŘOS(Ne)
</p> 
EOF;

// output the HTML content
$this->pdf->writeHTML($html, true, false, true, false, '');

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
  
		if($save){
			$this->pdf->Output($path.$title . '.pdf', 'F'); 
		} else { 
			$this->pdf->Output($path.$title . '.pdf', 'I');     
		} 
		return $title . '.pdf';   
	}	
}
?>