<?
/**
 * Třída reprentuje export do Helios Orange
 */
class module_Eshop_Images{
	
	
	public function __construct() {
		
	}
	
	function resizeAll($view, $mEshop){
		$db = $mEshop->db;	
		$cats = $mEshop->getCategories(502,  -1, -1, -1, array('hasImage' => 1));    
		$images = array();  
		foreach ($cats as $c){
			$images[] = $c['Obrazek'];
		} 
		$this->resize($view, $images);  
	}
		
	function resize($view, $images){
		
		//pr($images); die();
		 
		foreach ($images as $i){
			copy($view->config->htdocsRoot . '/i/' . $i, $view->config->htdocsRoot . '/ir/' . $i);
			$filepath = $view->config->htdocsRoot . '/ir/' . $i;
			$img = new Image($filepath, false); 			
			$img->generateThumbnail('mini', 120, 120);  
			$miniImg = $img->fileDir . '/mini.jpg';   
			$img->generateThumbnail('show', 240, 240); 
			$showImg = $img->fileDir . '/show.jpg';  
			$img->generateThumbnail('full', 800, 600);    
			$fullImg = $img->fileDir . '/full.jpg';    
			
		} 			  
		
	} 
	
}
?>