<?php
/**
 * import stromu kategorii z Heureky (XML)
 * @author Jakub Kratena
 * 25.11.2012
 * 
 *
 */
class module_ZboziTree extends module_TreeMPath{
	protected $tableName;
	protected $tab = 'test';
	
	public function __construct() { 			
		$this->db = Zend_Registry::getInstance()->db;
		$this->config = Zend_Registry::getInstance ()->config;
		$this->mechantFile = $this->config->dataRoot.'/srovnavace/zbozi.csv';
		$this->itemLevel = 5;
		$this->tableNameMerchant = $this->tableName =  'SrovnavaceCen__zbozi';
	} 
	
	public function getCategories($tree,$parents,$listCategories)
	{
		return parent::getCategories($tree,$parents,$listCategories,'zbozicz');
	}
	
	
	public function getTree()
	{
		$all = $this->db->fetchAll("select id,title,level from ".$this->tableNameMerchant);
         return $all;   
	}
	
	private function getItemByParentId($parentId)
	{
		return $this->db->fetchRow("select * from ".$this->tableNameMerchant." where parentId = ?",$parentId);
	}
	
	
	
	private function isExit($title, $parentId)
	{
		// zjistí zda tam je už stejný název, pokud ano bude se testovat
		
		return $this->db->fetchOne("SELECT id from `" . $this->tableNameMerchant ."` WHERE title=:t and parentId=:p", array('t' => $title, 'p' => $parentId));
		//e($parentId);
		
	}
	
	private function getParentId($title, $level = false)
	{
		if($level)
		{
			$parentId = $this->db->fetchOne("SELECT parentId from `" . $this->tableNameMerchant ."` WHERE title=:t and level=:l", array('t' => $title, 'l' => $level));
		}
		else
		{
			$parentId = $this->db->fetchOne("SELECT parentId from `" . $this->tableNameMerchant ."` WHERE title=:t", array('t' => $title));
		}	
		return $parentId;
	}
	
	private function saveItem($title,$parentId,$level,$printTitle)
	{
		$data['title'] = $title;
		$data['printTitle'] = $printTitle;
		$data['parentId'] = $parentId ;
		$data['level'] = $level;
		$this->db->insert($this->tableNameMerchant, $data);
		return $this->db->lastInsertId();
					// 
					// $data['level'] = $i;	
	}
	
	private function setMerchantTree($items)
	{
		foreach ($items as $value) {
			 for ($i=1; $i <= $this->itemLevel; $i++) {
			 	$level = 'level_'.$i;
				$value->$level = $this->correntTitle(iconv('windows-1250', 'UTF-8', ($value->$level)));
			 	if($value->$level){
			 		// test jenom na 1 uzle
			 		if($i == 1)
			 		{
			 			$printTitle = $value->$level;
			 			$parentId = $this->getParentId($value->$level, 1);
						if(!$parentId)
						{						
							
						 	$parentId = $this->saveItem($value->$level,1,1,$printTitle);
						}
						else{
							$parentId = $this->isExit($value->$level,1);
						}
			 		}
					else{
						$exist = $this->isExit($value->$level,$parentId);
						//pr($parentId);
							$printTitle .= ' | '.$value->$level;
						if(!$exist)
							{
							 
								$parentId = $this->saveItem($value->$level,$parentId,$i,$printTitle);
							

							}
						else{
							$parentId = $exist;
						}
						}
					}
			 }
		}
	}

	/* oprava chyb z načítání csv*/
	private function correntTitle($title)
	{
		$titleNew = str_replace('isti', 'Čisti', $title);
		return $titleNew;
	}
	
	
	public function readPropertyCsv($sk = false, $start = 0, $end = 100000)
	{
		// není madlo
		$file = $this->config->dataRoot . '/DATAP.csv';
		if($sk){
			$file = $this->config->dataRoot . '/DATASK.csv';
		}
		$items = array();
		$handle = fopen($this->mechantFile,"r");
		$inc = $i = 0;
		while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
			if(count($data)>1 && $i >= $start && $end >= $i){
				$inc = 0;
				foreach ($data as $value)
				{
					$piece[$inc] = explode(';', $value);
					$inc++;
				}
				
				if($i>0){
					$temp = ($piece[1][0]);
					$ara = explode('/', $temp);
					$parent = str_replace('/', ' | ', $temp);
					$ikey = 1;
					$item = new stdClass();
					foreach ($ara as $value) {
						$name = 'level_'.$ikey;
						$item->$name = $value;
						$item->printTitle = $parent;
		
						$ikey++;
					}
					$items[] = $item;
				}
				if($end == $inc){
					die;
				}
				$inc++;
			}
			$i++;
		}
		//pr($items);
		$this->setMerchantTree($items);
		return $items;
	}
	
	
	
	
	
	
}

?>

