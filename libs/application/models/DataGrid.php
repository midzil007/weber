<?php
/**
 * 
 * Enter description here ...
 * @author Mitch
 *
 *
 * napr
 * 
 * $dg = new DataGrid()
    ->setTableName( ... )
    ->setSelectCols( ... )
    ->setOrder( ... )
    ->getSelect( $params );
    
   $row = $dg->fetchRow();
 */

class DataGrid
{   
	private $db;
	private $tree;
	private $tableName;
	private $tableTitle; 
	private $tableIdent;
	private $selectCols = array(); 
	private $bind = array();
	private $select;
	private $isDebug = false;
	private $tableId; 
	private $template;
	private $tableHeaders = array();
	private $tableButtons = array();
	private $tableSearchColls = array();
	private $itemsPerPageOptions = '10, 20, 100, 500';
	private $itemsPerPage = 20; 
	private $tableHeight = '250'; 
	private $tableWidth = 'auto';
	private $joins = array();  
	 
	function __construct($tableId, $select = false){
		$this->db =  Zend_Registry::getInstance()->db; 
		$this->tree =  Zend_Registry::getInstance()->tree; 
		if(!$select){
			$this->select =  $this->db->select();
		} else {
			$this->select = $select; 
		}
		$this->tableId = $tableId;
	}
	
	function isDebug($is = true){
		$this->isDebug = $is;
		return $this;
	}
	
	function setTableName($tableIdent, $tn){
		$this->tableName = $tn;
		$this->tableIdent = $tableIdent;
		return $this;
	}
	
	function setBind($bind){
		$this->bind = $bind;
		return $this;
	}
	
	function setLimit($limitStart = 0, $limitCount = 10){ 
		$this->select->limit($limitCount, $limitStart); 
		return $this;
	}
	
	function setSelectCols(array $cols){
		$this->selectCols = $cols;
		return $this;
	}
	
	function join($tableIdent, $tableName, $on, $select = array()){
		$this->joins[] = array(
			array($tableIdent => $tableName), 
        	$on,
        	$select
        );  
        return $this;
	}
	
	function setOrder($sort, $sortType = 'ASC'){
		$this->select->order($sort . ' ' . $sortType);
		return $this;
	} 
	
	function addWhere($where){
		$this->select->where($where);
		return $this;
	}
	
	function addWhereBind($col, $mark, $val){ 
		$this->select->where("$col $mark ?", $val);
		return $this;  
	} 
	
	function getSelect($params = array()){  
		$this->select2 = clone $this->select; 
		if($params['getCount']){ 
    		$c =  new Zend_Db_Expr("count('*')");  
    		$this->select2->from(array($this->tableIdent => $this->tableName), array( $c ));  
			$this->select2->limit(1, 0);   

    	} else { 
    		$this->select2->from(array( $this->tableIdent => $this->tableName), $this->selectCols); 		  
    	}
    	
		if(count($this->joins)){
			foreach ($this->joins as $join){
				$this->select2->join($join[0], $join[1], $join[2]); 
			}
		}
		
    	return $this;  
	}
	
	function fetchOne(){    
		if($this->isDebug){  
			echo($this->select2);
			pr($this->bind);  
			return $this->db->fetchOne($this->select2, $this->bind);	
		} else {
			try{
				return $this->db->fetchOne($this->select2, $this->bind);	
			} catch (Exception $e) {
				
			}
		}	   
	}
	
	function fetchRow(){  
		if($this->isDebug){ 
			echo($this->select2);
			pr($this->bind);
			return $this->db->fetchRow($this->select2, $this->bind);	
		} else {
			try{
				return $this->db->fetchRow($this->select2, $this->bind);	
			} catch (Exception $e) { 
				
			}
		}	  	 
	}
	
	function fetchAll(){   
		if($this->isDebug){  
			echo($this->select2); 
			pr($this->bind);
			return $this->db->fetchAll($this->select2, $this->bind);	
		} else {
			try{
				return $this->db->fetchAll($this->select2, $this->bind);	
			} catch (Exception $e) {
				
			}
		}
	}
	
	/************ ZOBRAZENI *************/
	
	function setRefresUrl($url){
		$this->rUrul = $url;
		return $this;
	}
	function setTitle($tableTitle){
		$this->tableTitle = $tableTitle;
		return $this;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param array $header - display, name, width, sortable, align, hide
	 */
	function setHeaders($header){  
		$this->tableHeaders = $header;  
		return $this;
	} 
	
	function setSearchableColls($search){ 
		$this->tableSearchColls= $search;
		return $this;
	}
	
	function setButtons($buttons){ 
		if(count($buttons)){ // je predpoklad ze tma jsou checkboxy
			$this->tableButtons = array(array('Označit vše na stránce', 'selectall', 'onpress', 'selectall')); 
		} else {
			$this->tableButtons = array(); 
		}
		
		$this->tableButtons = array_merge($this->tableButtons, $buttons);  
		return $this; 
	}
	function setItemsPerPageOptions($options, $selected){
		$this->itemsPerPageOptions = $options;
		$this->itemsPerPage = $selected;
	}
	
	function setTemplate($template){
		$this->template = $template; 
		return $this; 
	}
	
	function setHeight($height){
		$this->tableHeight = $height; 
		return $this; 
	}
	function setWidth($width){
		$this->tableWidth = $width; 
		return $this; 
	}
	
	function render($view, $template){ 
		$view->tableId = $this->tableId;
		$view->rUrul = $this->rUrul;  
		$view->tableTitle = $this->tableTitle;  
		$view->itemsPerPageOptions = $this->itemsPerPageOptions;
		$view->itemsPerPage = $this->itemsPerPage;
		$view->tableHeight = $this->tableHeight; 
		$view->tableWidth = $this->tableWidth;  
		
		
		$view->tableButtons = $this->tableButtons;
		$view->tableHeaders = $this->tableHeaders;
		$view->tableSearchColls = $this->tableSearchColls;  
		  
		// pr($this );
		
		return $view->render($template);
	}
	
	function renderAjax($currentPage, $itemsTotal, $rows){ 
		header("Content-type: application/json");
		$jsonData = array('page'=>$currentPage,'total'=>$itemsTotal,'rows'=>array()); 
		$jsonData['rows'] = $rows;
		echo json_encode($jsonData); 
	} 

	
	function getRows($defaultSort = 'title', $defaultSortType = 'desc'){
		
		list($page, $rp, $sortname, $sortorder, $query, $qtype, $start) = $this->getParams($defaultSort, $defaultSortType); 
		$params = array();
		// $this->isDebug(true);    
		// COUNT  
		$params['getCount'] = true;  
		$total = $this->getSelect($params)->fetchOne();
		
		// ROWS
		$params['getCount'] = false;
		$rows = $this->setLimit($start, $rp)
				->setOrder($sortname, $sortorder)
				->getSelect($params)->fetchAll();   
		return array($total, $rows, $page); 
	}
	
	function getParams($defaultSort = 'title', $defaultSortType = 'desc'){
		$page = isset($_POST['page']) ? $_POST['page'] : 1;   
		$rp = isset($_POST['rp']) ? $_POST['rp'] : 10;  
		$sortname = isset($_POST['sortname']) ? $_POST['sortname'] : $defaultSort;
		$sortorder = isset($_POST['sortorder']) ? $_POST['sortorder'] : $defaultSortType;
		$query = isset($_POST['query']) ? $_POST['query'] : false;
		$qtype = isset($_POST['qtype']) ? $_POST['qtype'] : false;
		$start = (($page-1) * $rp);
		 
		// pr(array($page, $rp, $sortname, $sortorder, $query, $qtype, $start));  
		
		return array($page, $rp, $sortname, $sortorder, $query, $qtype, $start);
	}
	
	
}