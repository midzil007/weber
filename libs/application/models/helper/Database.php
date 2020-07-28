<?
/**
 * Database
 * @package helper
 */
 
class helper_Database {
    		
	static function getTableColumns( $tableName ){		    	
    	$columns = Zend_Registry::getInstance()->db->fetchAll('
    		SELECT table_name, column_name, is_nullable, data_type, character_maximum_length
			FROM INFORMATION_SCHEMA.Columns
			WHERE table_name = :tn AND TABLE_SCHEMA = :ts',
    		array('tn' => $tableName, 'ts' => Zend_Registry::getInstance()->config->database->dbname) 
		);	
		$tableColumns = array();
		foreach ($columns as $coll){
			$tableColumns[] = $coll['column_name'];
		}
		return $tableColumns;
    }
    
    static function addColl( $tableName, $collToAdd, $after, $extra = 'VARCHAR( 255 ) NOT NULL' ){		
    	//ALTER TABLE `Nodes` ADD `en_title` VARCHAR( 255 ) NOT NULL AFTER `title` ;    	
    	Zend_Registry::getInstance()->db->query("ALTER TABLE `$tableName` ADD `$collToAdd` $extra AFTER `$after`;");	
    }
    
    
    static function addColl2( $tableName, $after, $create ){		
    	Zend_Registry::getInstance()->db->query("ALTER TABLE `$tableName` ADD $create AFTER `$after`;");	
    }
    
}
?>
