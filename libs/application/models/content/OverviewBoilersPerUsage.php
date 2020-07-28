<?
/**
 * Třída reprezentuje typ obsahu - Přehled podstránek
 * @see model_Content
 * @package content
 */
 
class content_OverviewBoilersPerUsage extends content_OverviewBoilers {
    
    public function __construct()
    {   
    	parent::__construct();
    	
    	$this->userName = 'Přehled kotlů dle využití';
    	    	
    	foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){
				$property->value = 'BoilersPerUsage';
			}
		} 		       
    }
    
    
    function show($view, $node){ 
    	$reg = Zend_Registry::getInstance();    	   	
    	$template = $this->getPropertyByName('pathToTemplate')->value . '.phtml';     	
    	    	
    	$view->usageId = $node->nodeId;
    	self::initBoilers($view);
    	
    	return $view->render($reg->config->view->overviewsDir . $template);
    }
    
    static function initBoilers($view){
    	$reg = Zend_Registry::getInstance();    	 
    	$productsNode = $reg->tree->getNodeById($reg->config->instance->productsNodeId);
    	$productCategories = $productsNode->getChildren('BOTH', true);
    	  
    	$view->leftMenuTwoLevel = true;    	
    	$view->leftMenuData = array();
    	foreach ($productCategories as $cath){
    		$cpContent = $cath->getPublishedContent();
    		$boilers = array();
    		if($cpContent->_name == 'content_OverviewBoilers'){
    			
    			foreach ($cath->children as $boilerNode){
    				
    				if(! $boiler = $boilerNode->getPublishedContent()){
    					continue;
    				}    				
	    			$relations = helper_MultiSelect::getMultiSelectValues($boiler->getPropertyValue('relation'));	   			
	    			
	    			if(in_array($view->usageId, array_values($relations))){
		    			$boilers[] = $boilerNode;
	    			}
    			}
    			
    			$view->leftMenuData[$cath->path] = array(
    				'cath' => $cath,
    				'children' => $boilers
    			);
    			
    		}    		
    	}
    }
}
?>
