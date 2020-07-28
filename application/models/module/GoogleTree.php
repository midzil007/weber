<?php
/**
 * import stromu kategorii z Heureky (XML)
 * @author Jakub Kratena
 * 25.11.2012
 *
 */
class module_GoogleTree extends module_TreeMPath
{
	/**
	 * url souboru se strukturou stromu
	 * @var string
	 */
	private $sourceFile;

	/**
	* 
	*/
	private $categoryIDs;

	function __construct()
	{
		parent::__construct();
		$this->tableName = 'module_GoogleTree';
		$this->tableName2 = 'module_GoogleTree_IDMap';
		$this->sourceFile = 'http://www.google.com/basepages/producttype/taxonomy.cs-CZ.txt';
		$this->categoryIDs = $this->loadIDs();
			$this->tableNameMerchant = $this->tableName =  'SrovnavaceCen__merchant';
	}
	
	
	public function getCategories($tree,$parents,$listCategories)
	{
		return parent::getCategories($tree,$parents,$listCategories,'zbozicz');
	}

	private function loadIDs()
	{
		$select = $this->db->select()
					->from($this->tableName2);
		$stmt = $this->db->query($select);
		$result = $stmt->fetchAll();

		$ret = array();
		foreach ($result as $row) {
			$ret[$row['category_name']] = $row['id'];
		}
		return $ret;
	}

	/**
	 * nacte textovy soubor,
	 * '#' je pro komentar,
	 * kazdy novy radek je dalsi zaznam
	 * @return array (id => (CATEGORY_ID, CATEGORY_NAME, PATH, SUBCATEGORIES => (...)), id => (), .. )
	 */
	function loadData()
	{
		e($this->config->dataRoot);
		$this->db->delete($this->tableName);
		try {
			$lines = file($this->sourceFile, FILE_IGNORE_NEW_LINES);
		} catch (Exception $e) {
			echo "Can't open file at location: " . $this->sourceFile;
			echo $e;
			die;
		}
		
		$structuredLines = array();
		foreach ($lines as $line) {
			$withComments = explode('#', $line);
			$data = trim($withComments[0]);
			if(!$data){
				continue;
			}

			$structuredLines[] = array_map("trim", explode('>', $data));
			//$structuredLines = array_unique($structuredLines, SORT_REGULAR);
		}

		$ret = array();
		$prevLine = null;
		foreach ($structuredLines as $structuredLine){
			if($structuredLine == $prevLine){
				continue;
			}
			$prevLine = $structuredLine;

			// categopryname1 > categoryname2 > categoryname2 zpusobi chybu
			$unique = array_unique($structuredLine);
			if(count($unique) != count($structuredLine)){
				continue;
			}
			$name = array_pop($structuredLine);
			$id = $this->getIdByName($name);
			$path = '0';
			foreach ($structuredLine as $catName) {
				$path .= '.' . $this->getIdByName($catName);
			}
			$parent = '0';
			$cnt = count($structuredLine);
			if($cnt > 0){
				$parent = $this->getIdByName($structuredLine[$cnt - 1]);
			}
			$pathString = implode(' | ', $structuredLine);
			try {
				$this->db->insert($this->tableName, array(
					'id' 	=>	$id,
					'path'	=>	$path,
					'name'	=>	$name,
					'pathString' => $pathString,
					'parent'=>	$parent 
				));
			} catch (Exception $e) {
				// DO NOTHING...
			}
			
		}
		return $ret;
	}

	private function getIdByName($name)
	{
		if(isset($this->categoryIDs[$name])){
			return $this->categoryIDs[$name];
		}
		else{
			$this->db->insert($this->tableName2, array( 'category_name' => $name ));			
			$id = $this->db->lastInsertId();
			$this->categoryIDs[$name] = $id;
			return $id;
		}
	}
}
