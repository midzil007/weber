<?php
/**  Stitky. */

class module_Tags {	
	
	public $subdomain = false;
	public $pagesSelect =  array('n.id', 'n.title', 'n.path', 'n.parent', 'dateCreate', 'author', 'photos', 'tags');
	public $articles = true;  
	 
	function __construct($subdomain = false) {
		$this->db = Zend_Registry::getInstance()->db;
		$this->_tableName = 'module_Tags';
		$this->subdomain = $subdomain; 
	}
	
	function saveDescriptions(){
		$tags = $this->db->fetchAll("SELECT `tag`, `description` from `" . $this->_tableName ."`");  
		$uTags = array();
		foreach ($tags as $tag){
			$uTags[$tag['tag']] = $tag['description'];
		}  
		return $uTags;
	}
	 
	function buildTagsList($tables = array('content_Article')){
		$descr = $this->saveDescriptions(); 
		
		if($this->subdomain){
			$allTags = array();
			$tagDomainsMap = array();
			foreach ($tables as $tableName){
				$tags = $this->db->fetchAll("SELECT `tags`, `domain` from `" . $tableName ."` WHERE state = :p", array('p' => 'PUBLISHED'));
				foreach ($tags as $tagList){
					$t = explode(',', $tagList['tags']);
					$domain = $tagList['domain'];
					foreach ($t as $tag){
						$tag = trim($tag);
						if($tag){
							// $tag = strtolower($tag);
							if($allTags[$domain][$tag]){  
								$allTags[$domain][$tag]++;
							} else {
								$allTags[$domain][$tag] = 1;
							}
						}
					}
				}			
			}
			
			// SAVE
			$this->db->query("TRUNCATE TABLE `module_Tags`");  			
			foreach ($allTags as $domain => $data){
				foreach ($data as $tag => $used){
					$this->addTag($tag, $used, $domain, $descr[$tag]);  
				}  
			} 
		} else {
			$allTags = array();
			foreach ($tables as $tableName){
				$tags = $this->db->fetchAll("SELECT `tags` from `" . $tableName ."` WHERE state = :p", array('p' => 'PUBLISHED'));
				foreach ($tags as $tagList){
					$t = explode(',', $tagList['tags']);
					foreach ($t as $tag){
						$tag = trim($tag);
						if($tag){
							// $tag = strtolower($tag);
							if($allTags[$tag]){  
								$allTags[$tag]++;
							} else {
								$allTags[$tag] = 1;
							}
							
						}
					}
				}			
			}  
			
			// SAVE
			$this->db->query("TRUNCATE TABLE `module_Tags`");  
			
			foreach ($allTags as $tag => $used){
				$this->addTag($tag, $used, '', $descr[$tag]);    
			}  
		}
	}
	
	function addTag($tag, $used = 1, $domain = '', $description = ''){
		$description = $description?$description:''; 
		$this->db->insert(
			$this->_tableName,
			array(
				'tag' => $tag, 
				'used' => $used,
				'domain' => $domain,
				'description' => $description 
			) 
		);
	}
	
	function getUsedTags($sort = 'tag', $sortyType = 'asc', $limit = 150, $allDomains = false){
		// e($this->subdomain);
		
		if($allDomains){
			$w = 1;
		} else {
			$w = "`domain` = '$this->subdomain'";    
		}  
		 
		// e("SELECT `tag`, `used` from `" . $this->_tableName ."` WHERE $w ORDER BY $sort $sortyType");  
		$tags = $this->db->fetchAll("SELECT `tag`, `used` from `" . $this->_tableName ."` WHERE $w ORDER BY $sort $sortyType LIMIT 0, $limit");  
		$uTags = array();
		foreach ($tags as $tag){
			if($tag['used']){
				$uTags[$tag['tag']] = $tag['used'];
			}
		}   
		return $uTags;
	}
	
	function getUsedTagsBE($sort = 'tag', $sortyType = 'asc', $limit = 150, $allDomains = false){
		if($allDomains){
			$w = 1;
		} else {
			$w = "`domain` = '$this->subdomain'";    
		}  
		 
		$tags = $this->db->fetchAll("SELECT `tag`, `used`, `description` from `" . $this->_tableName ."` WHERE $w ORDER BY $sort $sortyType LIMIT 0, $limit");  		
		return $tags; 
	}
	
	function getTagDetail($tag){
		if($this->subdomain){
			$w = "`domain` = '$this->subdomain'";    
		} else {
			$w = 1; 
		}
		
		return $this->db->fetchRow("SELECT `tag`, `used`, `description`, `domain` from `" . $this->_tableName ."` WHERE $w AND tag = ?", $tag);    
	}
	 
	function getUsedTagsSelect($sort = 'tag', $sortyType = 'asc', $limit = 550){  
		$tags = $this->getUsedTags($sort, $sortyType, $limit);
		$sTags = array();
		foreach ($tags as $tag => $used){
			$sTags[', ' . $tag] = $tag; 
		}
		return $sTags;  
	}
	 
	function getTagsSizes($tags){
		
		$sizes = array(); 
		if(count($tags)){
			
			$maxSize = 1.7;         
			$minSize = 0.9; 
			
			$counts = array_values($tags);
			$max = max($counts);
			$min = min($counts);
			 
			$dif = $max - $min;
			$dif = $dif?$dif:1; 
			$step = round(($maxSize - $minSize) / $dif, 1); 
			
			foreach ($tags as $tag => $count){ 
				$size = round($count * $step, 1);
				$size = max($minSize, $size); 
				$size = min($maxSize, $size); 
				$sizes[$tag] = $size;      
			} 
		 
		}
		
		return $sizes;   
	}
	
	
	function getRelevantPages($tags, $tables = array('content_Article'), $limit = 10, $strict = false){ 
		$all = array();
		foreach ($tables as $tableName){
			
			$select =  $this->db->select();
			$select->from(array( 'a' => $tableName), $this->pagesSelect);   
 			$select->where('state = ?', 'PUBLISHED');	  
 			
 			if($strict){
 				$select->where('tags LIKE ? ', '%' . $tags [0] . '%');
 				if($this->articles){
 					$select->order('dateShow DESC'); 
 				}	    
 			} else {
 				$select->where('(MATCH (tags) AGAINST (?)) > 0', implode(' ', $tags));	   
 			}
 			   
 			if($this->subdomain){ 
 				$select->where('`domain` = ?', $this->subdomain);	  
 			}

 			if($this->articles){  
 				$select->where('dateShow <= ?', new Zend_Db_Expr('NOW()'));
 			}	
 			
 			// SELECT * FROM  `content_Article` WHERE MATCH (tags) AGAINST ('xxx nábytek');
 			
			$select->join( 
				array('nc' => 'NodesContents'), 
	        	'a.id = nc.c_id',
	        	array()  
	        ); 
	        
	        $select->join(
				array('n' => 'Nodes'),
	        	'n.id = nc.n_id',
	        	array('n.title') 
	        );
        
			$select->limit($limit);   
			
			// e($select->__toString());   
			
			$all = array_merge($all, $this->db->fetchAll($select));		 
			
		}
		return $all; 
	}  
	
	function updateTag($view, $tagOrig, $tag, $description){  
		$conf = Zend_Registry::getInstance()->config; 		
		$contents = $conf->modules->tags->contents->toArray();
		
		$t = $this->getTagDetail($tagOrig);
		if($t['domain']){
			$this->subdomain = $t['domain'];
		}
		  
		$where = "`tag` = '$tagOrig'"; 
		if($this->subdomain){ 
			$where .= " AND `domain` = '" . $this->subdomain . "'";	   
		} 
		$this->db->update(
			$this->_tableName,
			array(
				'tag' => $tag, 
				'description' => $description 
			), 
			$where
		);
		
		if($tag == $tagOrig){
			return 1;
		}  
		
		$relevant = $this->getRelevantPages(array($tagOrig),$contents , 8000, true);
		if(count($relevant)){  
			foreach ($relevant as $article){
				$oldTags = explode(',', $article['tags']);		
				$newTags = array();
				foreach ($oldTags as $oTag){
					$oTag = trim($oTag);
					if($oTag == $tagOrig){
						$oTag = $tag;						
					}
					$newTags[] = $oTag;
				} 
				$newTags = implode(', ', $newTags);
				$nArticle = $view->tree->getNodeById($article['id']);
				
				if($nArticle){
					
					if($view->cache){
						$ident = $view->cache->identificator . "node_" .  $nArticle->nodeId;
						$view->cache->remove($ident);   
					} 
					
					$cArticle = $nArticle->getPublishedContent();
					$tgs = $cArticle->getPropertyValue('tags');
					if($tgs == $article['tags']){
						$t = $cArticle->getPropertyByName('tags');
						$t->value = $newTags;
						$cArticle->update(true);  
					}					
				}
//				pr($article);  
			}  
		} 
		$this->buildTagsList($contents);
		return 1;  
	}
	
	function deleteTag($view, $tag){  
		$conf = Zend_Registry::getInstance()->config; 		
		$contents = $conf->modules->tags->contents->toArray();
		
		$t = $this->getTagDetail($tag);
		if($t['domain']){
			$this->subdomain = $t['domain'];
		}
				  
		$where = "`tag` = '$tag'"; 
		if($this->subdomain){ 
			$where .= " AND `domain` = '" . $this->subdomain . "'";	   
		} 
		// echo $where;
		$this->db->delete(
			$this->_tableName,
			$where
		);
				
		$relevant = $this->getRelevantPages(array($tag),$contents , 8000, true);
		//e(count($relevant)); return 1;
		if(count($relevant)){    
			foreach ($relevant as $article){ 
				$oldTags = explode(',', $article['tags']);		
				$newTags = array(); 
				foreach ($oldTags as $oTag){
					$oTag = trim($oTag);
					if($oTag == $tag){
						continue;			
					}
					$newTags[] = $oTag;
				}
				$newTags = implode(', ', $newTags);
				$nArticle = $view->tree->getNodeById($article['id']);
				
				if($nArticle){
					
					if($view->cache){
						$ident = $view->cache->identificator . "node_" .  $nArticle->nodeId;
						$view->cache->remove($ident);   
					} 
					
					$cArticle = $nArticle->getPublishedContent();
					$tgs = $cArticle->getPropertyValue('tags');
					if($tgs == $article['tags']){
						$t = $cArticle->getPropertyByName('tags');
						$t->value = $newTags;
						$cArticle->update(true);  
					}					
				} 
//				pr($article);  
			}    
		}   
		$this->buildTagsList($contents);
		return 1;  
	}
}


?>  