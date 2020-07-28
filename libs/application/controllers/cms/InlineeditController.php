<?php
class Cms_InlineeditController extends CmsController
{
		
	public function init()
	{
		parent::init();		    	
	}
	
		
	
	public function setpropertyAction(){
		$nodeId = $this->request->getParam('nodeId');
		$node = $this->view->tree->getNodeById($nodeId);
		$content = $node->getPublishedContent();
		if($this->request->getParam('disable'))
		{
			echo $content->getPropertyByName($this->input->name)->value;
		}
		else{
			$content->getPropertyByName($this->input->name)->value = $this->input->value;
			$content->update();
			echo $this->input->value;
		}
	}
	
	public function getvariantselectAction(){
		$mVarianta = new module_Varianta();	
		echo $mVarianta->getJSONVariantSelections($this->request->getParam('name'));
		die();
	}
	
	public function setpropertiesvariantAction()
	{
		if($this->input->value){
			$mVarianta = new module_Varianta();	
			$mVarianta->saveProperties($this->request->getParam('variant'),$this->input->pk,$this->input->value);
		}
	}

	public function settitleAction()
	{
		if($this->input->value){
			$nodeId = $this->request->getParam('nodeId');
			$node = $this->view->tree->getNodeById($nodeId);
			$parentNode = $this->view->tree->getNodeSimple($node->parentId);	
			$prefix  = $parentNode->path.'/';	
			$node->path = $prefix.Utils::generatePathName($this->input->value,null,$parentNode->path.'/');
			$node->title = $this->input->value;
			$this->view->tree->updateNode($node, false);
			echo $node->path;
			die;
		}
	}
	
}
