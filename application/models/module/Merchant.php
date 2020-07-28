<?
header ( 'Content-Type: text/html; charset=UTF-8' );

class module_Merchant extends module_TreeMPath{
	protected $tableName;
	
	public function __construct() { 			
		$this->db = Zend_Registry::getInstance()->db;
		$this->config = Zend_Registry::getInstance ()->config;
		$this->mechantFile = $this->config->dataRoot.'/srovnavace/taxonomy.cs-CZ.csv';
		$this->itemLevel = 5;
		$this->tableNameMerchant = $this->tableName =  'SrovnavaceCen__merchant';
		
	} 
	 
	
	public function getCategories($tree,$parents,$listCategories)
	{
		return parent::getCategories($tree,$parents,$listCategories,'merchant');
	}
	
	 
	public function getTree()
	{
		
		/*
		 * <link rel="stylesheet" href="/admin/css/chosen/chosen.css">	
	<script src="/admin/js/chosen/chosen.jquery.js" type="text/javascript"></script>
	<script src="/admin/js/chosen/prism.js" type="text/javascript" charset="utf-8"></script>
      
       <?$mod = new module_SrovnavaceCen();?>
       
    
          <em>Into This</em>
          <?=($mod->renderTree())?>
  
     
      


     
     

  <script type="text/javascript">
    var config = {
      '.chosen-select'           : {},
      '.chosen-select-deselect'  : {allow_single_deselect:true},
      '.chosen-select-no-single' : {disable_search_threshold:10},
      '.chosen-select-no-results': {no_results_text:'Oops, nothing found!'},
      '.chosen-select-width'     : {width:"95%"}
    }
    for (var selector in config) {
      $(selector).chosen(config[selector]);
    }
  </script>
		 * */
		$all = $this->db->fetchAll("select id,title,level from ".$this->tableNameMerchant);
		// $text = '';
		// $text .= '<select data-placeholder="Your Favorite Types of Bear" multiple="" name="chosen[]" class="chosen-select-width" tabindex="-1" style="display: none;">';
		// $text .= '<option value=""></option>';
		// foreach ($all as $value) {
			// $text .= '<option value="'.$value['id'].'">'.$value['title'].'</option>';
		// }
		// $text .= '</select>';
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
	
	private function saveItem($title,$parentId,$level)
	{
		$data['title'] = $title;
		$data['printTitle'] = $title;
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
			 			$parentId = $this->getParentId($value->$level, 1);
						if(!$parentId)
						{
						 	$parentId = $this->saveItem($value->$level,1,1);
						}
						else{
							$parentId = $this->isExit($value->$level,1);
						}
			 		}
					else{
						$exist = $this->isExit($value->$level,$parentId);
						//pr($parentId);
						if(!$exist)
							{
							$parentId = $this->saveItem($value->$level,$parentId,$i);

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
				$item = new stdClass();
				$item->level_1 = ($piece[0][0]);
				$item->level_2 = ($piece[1][0]);
				$item->level_3 = ($piece[2][0]);
				$item->level_4 = ($piece[3][0]);
				$item->level_5 = ($piece[4][0]);
				$item->level_6 = ($piece[5][0]);
				$items[] = $item;
				if($end == $inc){
					die;
				}
				$inc++;
			}
			$i++;
		}
		$this->setMerchantTree($items);
		return $items;
	}
	
	
	
	
	
	
}

?>