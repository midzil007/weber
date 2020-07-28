<?php
/**
 * import stromu kategorii z Heureky (XML)
 * @author Jakub Kratena
 * 25.11.2012
 *
 */
class module_HeurekaTree extends module_TreeMPath
{
	/**
	 * url XML souboru
	 * @var string
	 */
	private $XMLPath;
	private $level;

	function __construct()
	{
		parent::__construct();
		$this->tableName = 'module_HeurekaTree';
		$this->XMLPath = 'http://www.heureka.cz/direct/xml-export/shops/heureka-sekce.xml';
		$this->structure = array(	'id' 		=> 	'CATEGORY_ID',
									'path'		=>	'PATH',
									'name'		=>	'CATEGORY_NAME',
									'pathString'=>	'PATH_STRING',
									'parent'	=>	'PARENT');
		$this->childrenVarName = 'SUBCATEGORIES';
		$this->tableName = 'SrovnavaceCen__heureka';
	}
	
	
	private function getLevelById($id)
	{
		return $this->db->fetchOne("select level from ".$this->tableName. " where id =?",$id);
	}
	
	public function saveTree()
	{
		$items = $this->loadData();
		$this->level = 1;
		$this->saveLevel($items,$this->level);
		return FALSE;
		
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
		$all = $this->db->fetchAll("select id,title,level from ".$this->tableName);
		// $text = '';
		// $text .= '<select data-placeholder="Your Favorite Types of Bear" multiple="" name="chosen[]" class="chosen-select-width" tabindex="-1" style="display: none;">';
		// $text .= '<option value=""></option>';
		// foreach ($all as $value) {
			// $text .= '<option value="'.$value['id'].'">'.$value['title'].'</option>';
		// }
		// $text .= '</select>';
         return $all;   
	}
	
	private function saveLevel($items)
	{
		foreach ($items as $key => $value) {
			$data['title'] = $value['CATEGORY_NAME'];
			$data['printTitle'] = $value['CATEGORY_NAME'];
			$data['parentId'] = $value['PARENT'];
			if($value['PARENT'] == 0)
			{
				$level = 1;
			}
			else
			{
				$level = $this->getLevelById($data['parentId']) + 1;
			}
			$data['level'] = $level;
			$data['id'] = $value['CATEGORY_ID'];
			if(!$this->isExit($value['CATEGORY_NAME'], $value['PARENT'])){
				$this->db->insert($this->tableName, $data);
				if($value['SUBCATEGORIES'])
				{
					$this->saveLevel($value['SUBCATEGORIES']);
				}
			}
		}
	}
	
	private function isExit($title, $parentId)
	{
		// zjistí zda tam je už stejný název, pokud ano bude se testovat
		
		return $this->db->fetchOne("SELECT id from `". $this->tableName ."` WHERE title=:t and parentId=:p", array('t' => $title, 'p' => $parentId));
		//e($parentId);
		
	}

	/**
	 * nacte xml soubor $this->XMLPath, vrati stromovou strukturu v XML jako pole
	 * @return array (id => (CATEGORY_ID, CATEGORY_NAME, PATH, SUBCATEGORIES => (...)), id => (), .. )
	 */
	function loadData()
	{
		$xml = simplexml_load_file($this->XMLPath);
		$ret = array();
		foreach ($xml->CATEGORY as $cat){
			$arr = $this->processNode($cat, '0', '', '0');
			$ret[ $arr['CATEGORY_ID'] ] = $arr;
		}

		return $ret;
	}

	/**
	 *
	 * @param SimpleXMLElement $node CATEGORY z xml
	 * @param String $path cesta k $node 0.gParentId.ParentId
	 * @param String $pathString textovy vypis cesty rootCategoryName | childCategoryName | ...
	 * @return array (id => (CATEGORY_ID, CATEGORY_NAME, PATH, SUBCATEGORIES => (...)), id => (), .. )
	 */
	private function processNode($node, $path, $pathString, $parent)
	{
		if ($node == null){
			return null;
		}
		$arr = array(	'CATEGORY_ID' 	=> 	(string) $node->CATEGORY_ID,
				  		'CATEGORY_NAME'	=>	(string) $node->CATEGORY_NAME,
						'PATH'			=>	$path,
						'PATH_STRING'	=>	$pathString,
						'PARENT'		=>	$parent,
						'SUBCATEGORIES'	=>	array());

		foreach ($node->CATEGORY as $child){
			$newPath = $path . '.' . $arr['CATEGORY_ID'];
			$newPathString = $pathString ? $pathString . ' | ' . $arr['CATEGORY_NAME'] : $arr['CATEGORY_NAME'];
			$childArr = $this->processNode($child, $newPath, $newPathString, $arr['CATEGORY_ID']);
			if($childArr){
				$arr['SUBCATEGORIES'][ $childArr['CATEGORY_ID'] ] = $childArr;
			}
		}

	return $arr;
	}
}
