<?php

class module_Intranet
{    
	public function __construct()
    {
       $this->tree =  Zend_Registry::getInstance()->tree;
    }
    
    function cmp_dateCreateDesc($node1, $node2) {
			return strcasecmp($node1->dateCreate, $node2->dateCreate);
	}
	
    public function getLastAddedFiles($onlyApproved = true, $limit = null)
    {    	
    	/*
    	switch (Zend_Registry::getInstance()->session->user->group){
    		default:
    		case 'employees';
    			$path = '/intranet/soubory-ke-stazeni';
    			break;
    	}
    	
    	$sks =  $this->tree->getNodeByPath($path);
    	$fileNodes = $sks->getChildren('FOLDER');
    	
		$files = array();
		$count = 0;
		foreach ($fileNodes as $folder){
			$children = $folder->getChildren('ITEM', false);	
			//pr($children);
			foreach ($children as $fNode){
				if($fNode->intranetAprroved){
					if($content = $fNode->getPublishedContent()){
						
						$file = $content->getPropertyValue('fullpath');		
						$ico = $this->view->cmsFolderPath .'/images/icons/filetype/' . content_SFSFile::getFileExtension($file) . '.gif';
						if(!file_exists($this->config->htdocsRoot . $ico)){
							$ico = $this->view->cmsFolderPath . '/images/icons/filetype/file.gif';
						}
						$fNode->ico = $ico;
						$files[] = $fNode;			
						$count++;				
						if($limit){
							if($count >= $limit){
								break 2;
							}
						}
						
						
					}
				}			
			}
			
		}
		usort($files, array($this, "cmp_dateCreateDesc"));
		return $files;
		*/
    	return array();
    }   
	    
}