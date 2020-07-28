<?php
class Cms_LoadController extends CmsController
{

	public function init()
	{
		parent::init();		
		$this->view->curentController = $this->request->getParam('cController');
    	

	}

	public function indexAction()
	{
	}
	
	public function getAction()
	{
		$template = str_replace( '_', '/', $this->request->getParam('template'));
		echo $this->view->render('/controls/admin/'.$template.'.phtml');
	}	
}
