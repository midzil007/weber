<?php
class security_CmsAuthPlugin extends Zend_Controller_Plugin_Abstract
{
	private $_auth;
	private $_acl;
	
	/**
     * Const - No controller exception; controller does not exist
     */
    const EXCEPTION_NO_CONTROLLER = 'EXCEPTION_NO_CONTROLLER';

    /**
     * Const - No action exception; controller exists, but action does not
     */
    const EXCEPTION_NO_ACTION = 'EXCEPTION_NO_ACTION';

    /**
     * Const - Other Exception; exceptions thrown by application controllers
     */
    const EXCEPTION_OTHER = 'EXCEPTION_OTHER';

    /**
     * Module to use for errors; defaults to default module in dispatcher
     * @var string
     */
    protected $_errorModule = 'cms';

    /**
     * Controller to use for errors; defaults to 'error'
     * @var string
     */
    protected $_errorController = 'error';

    /**
     * Action to use for errors; defaults to 'error'
     * @var string
     */
    protected $_errorAction = 'error';

    /**
     * Flag; are we already inside the error handler loop?
     * @var bool
     */
    protected $_isInsideErrorHandlerLoop = false;

    /**
     * Exception count logged at first invocation of plugin
     * @var int
     */
    protected $_exceptionCountAtFirstEncounter = 0;
	
	private $_noauth = array('module' => 'cms',
	'controller' => 'login',
	'action' => 'index');

	private $_noacl = array('module' => 'cms',
	'controller' => 'error',
	'action' => 'privileges');

	public function __construct($auth, $acl)
	{
		$this->_auth = $auth;
		$this->_acl = $acl;
	}
	
	/*
    
	public function preDispatch($request)
	{
		
	}
	*/
	public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
    	
        $frontController = Zend_Controller_Front::getInstance();
        $dispatcher = $frontController->getDispatcher();
        
        if ($frontController->getParam('noErrorHandler') || $this->_isInsideErrorHandlerLoop) {
            return;
        }
        
        $error = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
        
        if (!$dispatcher->isDispatchable($request)) {
            $error->type = self::EXCEPTION_NO_CONTROLLER;
        } elseif (!$this->isProperAction($dispatcher, $request)) {
            $error->type = self::EXCEPTION_NO_ACTION;
        }
        
        
        if (isset($error->type)) {
            $this->_isInsideErrorHandlerLoop = true;
            
            $error->request = clone $request;
            $request->setParam('error_handler', $error)
                    ->setModuleName($this->getErrorHandlerModule())
                    ->setControllerName($this->getErrorHandlerController())
                    ->setActionName($this->getErrorHandlerAction());                    
                 
        } else {
        	//pr($this->_auth->getIdentity());
        	if ($this->_auth->hasIdentity()) {
				$role = $this->_auth->getIdentity()->group;
				//$role = 'Administrators';
			} else {
				//$role = 'server';
			}  
			
			if(!$role){
				$role = 'Administrators';
			}
			
			$controller = $request->controller;
			$action = $request->action;
			$module = $request->module;
			$resource = $controller;
	
			if (!$this->_acl->has($resource)) {
				$resource = null;
			}
			
			/*
			e($controller);
			e($action);
			e($module);
			e($resource);
			
			e($this->_acl->isAllowed($role, $resource, $action));
			die();
			*/
			//e($module.'/'.$controller.'/'.$action);
			if(!in_array($module.'/'.$controller.'/'.$action, $this->_acl->noAuthNeeded )){
				if (!$this->_acl->isAllowed($role, $resource, $action)) {
					if (!$this->_auth->hasIdentity()) {			
						$module = $this->_noauth['module'];
						$controller = $this->_noauth['controller'];
						$action = $this->_noauth['action'];
					} else {
						
						//e($this->_noacl); exit();
						
						$module = $this->_noacl['module'];
						$controller = $this->_noacl['controller'];
						$action = $this->_noacl['action'];
					}
				}
			}
			
			$request->setModuleName($module);
			$request->setControllerName($controller);
			$request->setActionName($action);
        }

    }
    
    public function isProperAction($dispatcher, $request)
    {
        $className = $dispatcher->loadClass($dispatcher->getControllerClass($request));
        $actionName = $request->getActionName();        
        if (empty($actionName)) {
            $actionName = $dispatcher->getDefaultAction();
        }
        $methodName = $dispatcher->formatActionName($actionName);
        
        $class = new ReflectionClass($className);
        if ($class->hasMethod($methodName)) {
            return true;
        }
        return false;
    } 
    
    /**
     * Retrieve the current error handler module
     *
     * @return string
     */
    public function getErrorHandlerModule()
    {
        if (null === $this->_errorModule) {
            $this->_errorModule = Zend_Controller_Front::getInstance()->getDispatcher()->getDefaultModule();
        }
        return $this->_errorModule;
    }

    /**
     * Retrieve the current error handler controller
     *
     * @return string
     */
    public function getErrorHandlerController()
    {
        return $this->_errorController;
    }

    /**
     * Retrieve the current error handler action
     *
     * @return string
     */
    public function getErrorHandlerAction()
    {
        return $this->_errorAction;
    }
}