<?php
class Cms_ErrorController extends CmsController
{
	public function init()
	{
			
		parent::init();		
		$this->view->title .= ' - Chyba';
		$this->template = 'controls/admin/Error.phtml';  	
		
	}
	
	public function errorAction()
	{
		$errors = $this->getRequest()->getParam('error_handler');
						
		switch ($errors->type) {
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
				$this->getResponse()
				->setRawHeader('HTTP/1.1 404 Not Found');
			default:
				$this->getResponse()
				->setRawHeader('HTTP/1.1 500 Internal Server Error');
				break;
		}
		
		//e($errors);
		parent::indexAction($this->template);	
	}
	
	public function privilegesAction()
	{		
		$this->template = 'controls/admin/Privileges.phtml';  	
		parent::indexAction($this->template);	
	}
}