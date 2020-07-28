<?php
class Cms_ServiceController extends CmsController
{

	public function init()
	{
		parent::init();

	}

	public function indexAction($content = null)
	{
		parent::indexAction($content);
	}
	
	public function insertReferencesAction()
	{
		//$table = 'content_HtmlFileWithFiles';
		$table = 'content_Resolution';
		//$table = 'content_Person';
		
		$nodes = $this->db->fetchAll("SELECT id FROM `Nodes` ");
		foreach ($nodes as $n){
			$n = $this->tree->getNodeById($n['id']);
			if($n->contents){
				foreach ($n->contents as $c){					
					$c->onSave();
				}
			}
		}
		e('ok');
		//pr($filerows);
	}
	
	public function setNodeParentAction()
	{
		
		$node = $this->tree->getNodeById($this->inputGet->nodeId);
		$node->parentId = $this->inputGet->parentId;
		$node->save();
		e('ok');
		//pr($filerows);
	}
	
	function removeNodeAction(){
		$id = $this->inputGet->nodeId;
		if($id > 99){
			$this->tree->removeNode($id);
			e($id . ' deleted');
		}
	}
	
	public function addFilesNodesAction()
	{		
		$folder = $this->inputGet->folder;
		$parentNode = $this->inputGet->folderId;
		if(!$folder || !$parentNode){
			die('folder + folderId missing');
		}
		$files = Folder::getFolderFiles($folder);
		
		//pr($files); die();
	
		
		foreach ($files as $file){

			$ext = Utils::getExtension($file);
			$name = array_pop(explode('/', content_SFSFile::getFileWithouExtension($file)));
							
			$content = $contentName = 'content_SFSFile';
			$content = new $content();	    				
			$content->id = $content->getNextContentId($this->dbAdapter);;
	    	$content->localId = 1;
			$content->dateCreate = $content->dateModif = Utils::mkTime();
			$content->owner = $content->modifiedBy = $this->session->user->username;
			$content->state = 'PUBLISHED';
			$content->properties[0]->value = $file; 						
			$content->save();
			
			$n = new Node();
	    	$n->type = 'ITEM';
	    	$n->nodeId = $n->getNextNodeId($this->dbAdapter);
	    	$n->parentId = $parentNode;	
	    	$n->dateCreate = $n->dateModif = Utils::mkTime();
			
	    	$n->owner = $n->modifiedBy = $this->session->user->username;
	    		    	
	    	$n->title = $name;
	    	
	    	$n->description = '';				
	    	$n->path =  $content->properties[0]->value;
			$n->intranetAprroved = true;
						
			
	    	$this->tree->addNode($n, true, false);	   
	    	$this->tree->pareNodeAndContent($n->nodeId, $content->id, $contentName);
	    	
		}
		e('done');

	}
	
	
		
}
