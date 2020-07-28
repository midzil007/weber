<?php

class module_AdminReport   
{   
	
	function __construct($domain = ''){
		$this->db =  Zend_Registry::getInstance()->db3;     
		$this->tree =  Zend_Registry::getInstance()->tree;
		$this->_tableName = 'content_Product';
		$this->_tableNameVariants = 'module_eshop_variants';
		$this->_tableHistorySearch ='historySearch';
		$this->_tableNameOver = 'content_OverviewProducts'; 
		$this->_tableNameCache = 'cacheSearch';
		$this->_tableNameOption = 'module_eshop_variants_options';
		$this->_tableNameZnacky = 'module_eshop_marks'; 
	} 
	
	
	public function sendNotify()
	{
		///
		//if(date)      
		$filePath = '/'.$this->getStockXLS();
		$subject = 'Výpis skladu za '.date("d.m.y");    
		$mail = new Email(); 
		$emailText = Helpers::prepareEmail(
			$mail, 
			$mainText, 
			false, 
			false,
			'484848',  
			'000000'     
		);  
		$mail->setBodyText(strip_tags(urldecode($mainText)));
		$mail->setBodyHtml(urldecode($emailText));			
		$mail->setSubject($subject);		 
		$s = Zend_Registry::getInstance()->settings;    
		$mail->addTo('michal.nosil@gmail.com','Sklad');               
		$filePath1 = Utils::getWebUrl().$filePath;    
	       
		$fileContents1 = file_get_contents($filePath1);
		$filePdf = $mail->createAttachment($fileContents1);     
		$filePdf->filename = 'report'.date("d.m.y").'.xls';                                                 
		$mail->setFrom('prodej@specshop.cz' , 'Sklad');
		try {        
			$mail->send();             
		} catch (Exception $e) { }	      
	}
	  
	
	public function getStockXLS()
	{
		$mProducts = new module_Products();
		$params['available'] = true;     
		$products = $this->getProducts('markasc','markasc',0,99999,$params);       
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document")
							 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Test result file"); 
		$list = $objPHPExcel->getActiveSheet(); //získáme aktivní list
		$list->setCellValue('A1', 'Produkt')  
            ->setCellValue('B1', 'Cena jednotková')  
            ->setCellValue('C1', 'Počet kusů')
            ->setCellValue('D1', 'Celkem Kč')      
			->getColumnDimension("A")->setWidth(80);
		$list->getColumnDimension("B")->setWidth(20);
		$list->getColumnDimension("C")->setWidth(20);
		$list->getColumnDimension("D")->setWidth(20);           
		$style = $list->getStyle("A1:D1");  
		$style->getFont()->setBold(true);   
		$inc = 2; 
		foreach ($products as $value) {
			if($value['country'] == '2')
			{
				$value['price'] = $value['price'] * 25.5;  
			}    
				$list->setCellValue('A'.$inc, $value['title']) 
					->setCellValue('B'.$inc, $value['price']) 
					->setCellValue('C'.$inc, $value['amount'])
					->setCellValue('D'.$inc, $value['price']  * $value['amount']); 			
				$inc++;    	
			}
  		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); 
//  		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');   
//		header('Content-Disposition: attachment;filename="01simple.xls"');
	//	header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		//header('Cache-Control: max-age=1');
	// If you're serving to IE over SSL, then the following may be needed
		//header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	//	header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	//	header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
	//	header ('Pragma: public'); // HTTP/1.0         
	//	$objWriter->save('php://output');                    
		//$filename = $_SERVER['DOCUMENT_ROOT'].'/test.xls';
		//header('Content-type: application/ms-excel;' );       
		//header('Content-Disposition: attachment; filename="test.xls"');        
		//pr($_SERVER['DOCUMENT_ROOT'].'/test.xls');      
		// Redirect output to a client’s web browser (Excel2007)
          ob_end_clean();             
		  $file = 'attachment/report/report'.date("d.m.y").'.xls';
		$objWriter->save(str_replace(__FILE__,$file ,__FILE__));                                    
		return $file;                  
	}                 
	
	
 	
	public function initSearch( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params,$view, $count = false)    
    {
    	$select =  $this->db->select();
		$bind = array();  

		if($count){    		 
    		$c =  new Zend_Db_Expr("count('id') as pocet");  
    		$select->from(array('cm' => $this->_tableName), array( $c,  $disc ));  
    	} elseif($params['allcol']) { 
    		$select->from(array( 'cm' => $this->_tableName), array('*')); 		   
    	}  elseif ($params['stats']) {
			$select->from(array( 'cm' => $this->_tableName), array());
		}   
		else{      
    		$select->from(array( 'cm' => $this->_tableName), array($disc, 'cid' => 'cm.id','id_eshop', 'mark', 'n.id', 'n.title', 'n.path', 'n.parent', 'dateModif', 'dateCreate', 'html', 'files', 'parent', 'mark',  'dphQuote','url as ext_url')); 		  
    	}      
    	
		$select->join(       
			array('nc' => 'NodesContents'), 
        	'cm.id = nc.c_id',
        	array() 
        );
        if ($params['stats']) {
        	$select->join(
			array('n' => 'Nodes'),
        	'n.id = nc.n_id',    
        	array('') 
        	);
        }
        else{
        	$select->join(
			array('n' => 'Nodes'),
        	'n.id = nc.n_id',
        	array('n.title') 
        	);
			}
		$price = "price";
		if($params['sleva'])
		{ 
			$price = "round(price *".$params['sleva'].") as price";    
		}    
        if($params['showAllV'])
        {         
        	$select->join(
        			array('var' => $this->_tableNameVariants),       
        			'var.id_product = cm.id',
        			array('id as variantId','id_eshop','insta_code','net_weight','inv_title','insta_country','in_order', 'country','eshop_item_id', 'EAN','alert_amount', 'availability', 'purchase_price','ext_id','poradi', $price, 'price2',  'currency','amount','plan_amount')
        	);  
        }  
		elseif ($params['stats']) 
        {         
        	$select->join(
        			array('var' => $this->_tableNameVariants),       
        			'var.id_product = cm.id',
        			array('sum(amount * purchase_price) as suma')         
        	);  
        }       
        else{                   
        $select->join(         
        		array('var' => $this->_tableNameVariants),  
        		'var.id_product = cm.id',           
        			array('id as variantId','id_eshop','insta_code','net_weight','inv_title','insta_country','in_order', 'country','eshop_item_id', 'EAN','alert_amount', 'availability','purchase_price','ext_id', 'poradi', 'obrazky',  $price, 'price2',  'currency','availability','amount','plan_amount')   
        );
        }
          $select->join(
			array('ep' => 'module_eshop_product'),
        	'ep.id_product = var.id_product',
        	array() 
        );
		  $select->join(
			array('ma' => 'module_eshop_marks'),
        	'ma.id = cm.mark',  
        	array('title as markTitle') 
        );
		  
		$select->join(
			array('eshop' => 'content_Eshop'),
        	'eshop.id_eshop = var.id_eshop',
        	array() 
        );
		           
        if($sort == 'title')  {
        	$sort = 'n.' . $sort;      
        }     
 
        if($params['category']){ 
        	if(is_array($params['category'])){
	        	$childrenIds = $params['category'];
	        } else { 
	        	$childrenIds = $this->tree->getNodeChildrenIds($params['category'], array(), 'FOLDER');
	        	$childrenIds[] = $params['category'];
	        }  
	        
	        if(count($childrenIds)){
	        	$w = array();
	        	foreach ($childrenIds as $id){
	        		$w[] = " cm.parent like '%$id%' ";
	        	}
	        	$select->where(implode('OR', $w));    
	    	}  
        }         
         if($params['alert_amount']){     
         	$select->where('alert_amount  >= plan_amount + amount');
         }

		 if($params['id_eshop']){
         	$select->where('var.id_eshop  = ?', $params['id_eshop']);
         }
		 
		  if($params['id_webareal']){
         	$select->where('var.id_webareal  = ?', $params['id_webareal']);
         }
 		if($params['skryto']>0){    
        	 $select->where('skryto = ?', '0');
		}  
		if($params['available']){     
        	 $select->where('amount >?', '0');
		}             
    	 if($params['from']){   
         	$select->where('cm.dateCreate  >= ?', $params['from']);  
         	}  
		 //pr($params);    
		// die;    
		 if($params['variantId']){   
         	$select->where('var.id  = ?', $params['variantId']);  
         	} 
		if($params['country']){      
         	$select->where('eshop.country  = ?', $params['country']);  
         	}  
		if($params['cz']){          
         	//$select->where('var.country  = ?', 1);     
         	} 
		if($params['alert']){             
         	$select->where('var.amount < alert_amount');      
         	}     
		if($params['eshop']){      
         	$select->where('var.id_eshop  = ?', $params['eshop']);  
         	}   
   		if($params['mark']){   
         	$select->where('mark  = ?', $params['mark']);    
         	}  
		
       if($params['dateArchived']){
         	$select->where("dateArchived is null or dateArchived = '0000-00-00 00:00:00' or dateArchived > '".date("Y-m-d H:i:s")."'");
       }  
              
     if($params['souvisejici']) 
     {
     	$select->where('n.id in ('.$params['souvisejici'].' )');
     }   
    	 
    if($params['search']){
			$params['search'] = str_replace('&quot;', '"', $params['search']);
			$select->where('inv_title LIKE ? OR `n`.`title` LIKE ?', '%' . $params['search'] . '%');         
		}         
		// $select->where('dateShow <= ?', new Zend_Db_Expr('NOW()'));	
		$select->where('c_type = ?', $this->_tableName);
		$sortType = $sortType?$sortType:'Asc'; 
        $availableSorts = array(
       		'priceasc' => 'price', 
       		'markasc' => 'mark', 
       		'price' => 'price',
        	'rating' => 'rating',
       		'dateCreate' => 'dateCreate',  
        );
        $availableSortTypes = array( 
       	 	'markasc' => 'Desc',  
        	'soldPrice' => 'DESC',
        	'priceasc' => 'ASC',
        	'price' => 'DESC',
        	'rating' => 'DESC',   
        	'dateCreate' => 'ASC',
    	);
        
    	     
    		$sortType = $availableSortTypes[$sort];
	        $sort = $availableSorts[$sort];
    	
    	if($sort && $sortType){
	        $select->order($sort . ' ' . $sortType);
    	}
		
		if($params['gTitle'] && !$count){   
			$select->GROUP('n.title');      
		} 
		if($_GET['rwwe']){
			$select->__toString();  
		}    
		  
	//	$select->order('n.id DESC'); 
			//pr($select->__toString());/      
			if($_GET['rwwe']){ 
			   
		}              
	  	if(!$params['alert_amount'] && !$count){                
			$select->GROUP('var.eshop_item_id');         
		  }      	    
		$select->limit($limitCount, $limitStart);		    
		return array($select, $bind); 
    }    
    
 
    public function getProducts( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params = array(),$view)   
    {
		list($select, $bind) = $this->initSearch($sort, $sortType, $limitStart, $limitCount, $params,$view);	
		if($select){
			return $this->db->fetchAll($select, $bind);	 
		}  	    
    }   

   
   
  

    
  


	 
	
  

	
}