<?

class Graph 
{
	public function __construct()
	{
		
	}
	
	public function init($g, $width = 300, $height = 300, $fontName = 'Arial', $fontSize = 7){
		$g =& Image_Graph::factory('graph', array($width, $height));
		$Font =& $g->addNew('ttf_font', SERVER_ROOT.'/Fonts/' . $fontName .'.ttf');
		$Font->setSize($fontSize);		
		$g->setFont($Font);	
		return $g;
	}
	
}
?>