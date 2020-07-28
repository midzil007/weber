<?php
/**
 * pro praci se stromy a ukladani do DB pomocí "Materialized Path" (= u kazdeho prvku uklada cestu)
 * @author Jakub Kratena
 * 25.11.2012
 *
 */
abstract class module_TreeMPath
{
	protected $tableName;
	protected $tab;
	protected $db;
	/**
	 * definuje strukturu dat kazdeho node
	 * @var array (dbColName => variableName)
	 */
	protected $structure;
	/**
	 * jmeno promenne ktera obsahuje potomky uzlu
	 * @var String
	 */
	protected $childrenVarName;

	function __construct()
	{
		$this->db =  Zend_Registry::getInstance()->db;
	}

	
	public function getCategories($tree,$parents,$listCategories,$nameValue)
	{
		$cateArray = explode('|', $parents);
		foreach ($cateArray as $value) {
			if(!$listCategories[$value]){
			
				$catenode = $tree->getNodeById($value);
				if(!is_object($catenode))
				{
					continue;
				}
				$contentcate = $catenode->getPublishedContent();
				$srovnaceIds = $contentcate->getPropertyValue($nameValue);
				if($srovnaceIds){
					$heurekacategries = explode('|', $srovnaceIds);

					foreach ($heurekacategries as $item) {
						$listCategories[$value] = $arrayName[] =  $this->getCategoryNamePrint($item);
					}
				}
			}
			else{
				$arrayName[] = $listCategories[$value];
			}
		}
		return array($arrayName,$listCategories);
	}

	public function getCategoryNamePrint($id)
	{
		return $this->db->fetchOne("select printTitle from ".$this->tableName." where id = ?",$id);
	}
	/**
	 * smaze vsechna data a ulozi nova
	 * @param array $data zpracovany strom ve forme pole
	 */
	public function rewriteDb($data)
	{
		$this->db->delete($this->tableName);
		foreach ($data as $node){
			$this->saveNode($node);
		}
	}
	

	/**
	 * rekurzivne ulozi cely strom
	 * @param array $node
	 */
	protected function saveNode($node)
	{
		$data = array();
		foreach ($this->structure as $colName => $varName){
			$data[$colName] = $node[$varName];
		}

		$this->db->insert($this->tableName, $data);
		foreach ($node[$this->childrenVarName] as $child){
			$this->saveNode($child);
		}
	}

/**
 * nacte strom z databaze,
 * @todo neotestovano pro $path != 0
 * @param string $path rootId.childId. ...
 * @return array ( array(name,id, childern), ... )
 */
	public function loadTree($path = 0)
	{
		$select = $this->db->select();
		$select->from($this->tableName);
		$select->where('path like ?', $path . '%');
		$stmt = $this->db->query($select);
		$result = $stmt->fetchAll();

		$pathArr = explode('.', $path);
		$last = $pathArr[ count($pathArr) - 1 ];
		//print_r($last);
		return $this->parseFromDB($result, $last);
	}

	/**
	 * Prochazi data z DB a rekurzivne sestavi strom.
	 * @param array $data z databaze
	 * @param unknown_type $rootId
	 * @return array
	 */
	protected function parseFromDB($data, $rootId)
	{
		$ret = array();

		foreach ($data as $key => $row){
			if($row['parent'] == $rootId){
				unset($data[$key]);
				$ret[$row['id']] = array(
						'name' 		=> $row['name'],
						'id'		=> $row['id'],
						'pathString' => $row['pathString'],
						'children' => $this->parseFromDB($data, $row['id'])
						);
			}
		}

		return $ret;
	}


	public function getSubTrees($nodeIds)
	{
		$ret = array();
		foreach($nodeIds as $id){
			$select = $this->db->select();
			$select->from($this->tableName);
			$select->where('id = ?', $id);
			$stmt = $this->db->query($select);
			$node = $this->db->fetchRow($select);
			$node['children'] = $this->loadTree($node['path'] . '.' . $node['id']);
			$ret[$node['id']] = $node;
		}

		return $ret;
	}

}