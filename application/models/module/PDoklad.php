<?php

class module_PDoklad
{   
	function __construct($view){
		$this->view = $view;
		$this->db =  Zend_Registry::getInstance()->db;
		$this->_tableName = "module_eshop_orders";
		$this->mOrders = new module_Eshop_Base($view);
		
	} 
	
	
	function getMaxId()
	{
		$id = $this->db->fetchOne('select max(`id-doklad`) from '.$this->_tableName) + 1 ;  
		return $id;  
	}
	
	
	function setDataPrint($id) 
	{     
		if(!$this->db->fetchOne("select `date-print` from ".$this->_tableName." where id =?", $id)){
			$where = $this->db->quoteInto('id =?',$id);
			$data['date-print'] = date("Y-m-d h:m:s");   
			$this->db->update($this->_tableName,$data,$where);
		}
	}
	  
	function prepareData()
	{      
		$id = ($this->view ->inputGet->order);         
		$this->setDataPrint($id);    
		$this->view->order = $this->mOrders->orders->getOrder($id);        
     
		if($this->view->order['id']){        
			      
			$this->view->items = $this->mOrders->orders->getItemsOrder($id); 
			return $this->view->render('parts/PDoklad.phtml');
		}   
	}
	
	function renderZalohovaFa()
	{
		$id = ($this->view ->inputGet->idorder);       
		$this->setDataPrint($id);    
		$this->view->order = $this->mOrders->orders->getOrder($id);        
  
		if($this->view->order['id']){            
			$this->view->items = $this->mOrders->orders->getItemsOrder($id); 
			return $this->view->render('parts/PFakturaPDF.phtml');
		}   
	}
	
	function prepareDataToPFD()
	{       
		$id = ($this->view ->inputGet->idorder);       
		$this->setDataPrint($id);    
		$this->view->order = $this->mOrders->orders->getOrder($id);        
  
		if($this->view->order['id']){           
			$this->view->items = $this->mOrders->orders->getItemsOrder($id); 
			return $this->view->render('parts/PDokladPDF.phtml');
		}   
	}
	
 	   
}