<?
/**
 * Třída reprezentuje typ obsahu - Přehled podstránek
 * @see model_Content
 * @package content
 */
 
class content_OverviewSubdomain extends content_OverviewList {
    
    public function __construct()
    {   
    	parent::__construct(); 	
    	$this->userName = 'Subodména';
    	foreach($this->properties as $property) {
			if ($property->name == "pathToTemplate"){
				$property->value = 'Subdomain';
			}
		}
		$this->properties[] = new ContentProperty('externalLinks','Textarea'); 
		
    }
    
    
}
?>