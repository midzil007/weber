<?php

class References
{    
	public function __construct($content)
    {
        $this->_tableName = 'References';
        $this->db = Zend_Registry::getInstance()->db;
        $this->tree = Zend_Registry::getInstance()->tree;
        $this->content = $content;
    }
    
    public function getNodeReferences($nodeId = 0){
    	$nodeId = $nodeId?$nodeId:$this->content->getNodeId();
    	$r = $this->db->fetchAll(
    		"SELECT node, content FROM `" . $this->_tableName . "` WHERE refNode = :refNode", 
    		array('refNode' => $nodeId)
    	);
    	$nodeReferences = array();
    	foreach ($r as $row){
    		$nodeReferences[$row['node']] = $row['content'];
    	}
    	return $nodeReferences;
    }
    
    public function getContentReferences(){
    	//MultiFileSelect, FileSelect, Wysiwyg, PageSelect
    	$references = array();
		    	
		foreach ( $this->content->properties as $property) {
			
			if ($property->type == 'PageSelect') { 							
				if (!empty($property->value) && $node = $this->tree->getNodeById($property->value)) {
					$references[] = $node;						
				}
			}
			elseif ($property->type == 'FileSelect') { 
				$f = content_SFSFile::getFileFromProperty($property->value);								
				$node = $this->tree->getNodeById(content_SFSFile::getFileNodeId($f['path']));
				if (!empty($property->value) && $node) {
					$references[] = $node;					
				}
			}			 	
			elseif($property->type == 'MultiFileSelect') { 
				$files = $this->content->getFilesNames($property->name);								
				foreach ($files as $filePath => $fileName) {
					$node = $this->tree->getNodeById(content_SFSFile::getFileNodeId($filePath));
					if (!empty($property->value) && $node) {
						$references[] = $node;					
					}
				}
			} 
			elseif ($property->type == 'Wysiwyg'){ // nic nevraci, rovnou vklada				
				$htmlCallback = new callback_HtmlCallback($this->content, $this);				
				preg_replace_callback('/(src|href|data|value)\s*=\s*"([^"]*)"/i', array($htmlCallback, 'call'), $property->value);				
			}
		}	
		
		return $references;		
	}
	
	
	function addReference($nodeId, $contentId, $refNodeId){
		if(!$this->db->fetchOne("SELECT node FROM `" . $this->_tableName . "` WHERE node = :node AND content = :content AND refNode = :refNode", array('node' => $nodeId, 'content' => $contentId, 'refNode' => $refNodeId))){
			$data = array(
				'node' => $nodeId,
				'content' => $contentId,
				'refNode' => $refNodeId
			);
			
			$this->db->insert($this->_tableName, $data);
		}		
	}

	    	
	public function insertReferences() {
		$references = $this->getContentReferences();
		//pr($references); die();
				
		foreach ($references as $reference) {	
			$this->addReference(
				$this->content->getNodeId(), 
				$this->content->id,  
				$reference->nodeId
			);	
		}		
	}
	
	public function deleteReferences() {
		$contentNodeId = $this->content->getNodeId();
		if (!$contentNodeId) {
			return false;
		}
									
		$where =  $this->db->quoteInto(" node = ? ", $contentNodeId);
		$where .= $this->db->quoteInto(" AND content = ? ", $this->content->id);
						
		$this->db->delete(			
			$this->_tableName,
			$where
		);
		
	}
	    
}