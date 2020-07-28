<?

class Image
{	
	public function __construct($file, $sf = true)	
	{
		$this->file = $file;
		$this->fsRoot = Zend_Registry::getInstance()->config->fsRoot;		
		$this->filename = content_SFSFile::getFileName($this->file);
		$this->filename2 = content_SFSFile::getFileWithouExtension(content_SFSFile::getFileName($this->file));
		$this->fileDir = content_SFSFile::getGeneratedImagePathNew($this->file);
		// pr($this);   
		if($sf){   
			if(strpos($this->file, $this->fsRoot) === false){
				$this->file = $this->fsRoot . $this->file;
				$this->fileDir = $this->fsRoot . $this->fileDir;
			}
		}
	}
	
	public function testThumbDir($thumb_name = ''){
		if(!$this->existsThumbDir($thumb_name)){
			if($thumb_name){
				mkdir($this->fileDir. '/' . $thumb_name, 0777); 
			} else {
				mkdir($this->fileDir, 0777);
			}  
		}
	}
	
	public function existsThumbDir($thumb_name = ''){	
		if($thumb_name){
				return is_dir($this->fileDir . '/' . $thumb_name);
			} else {
				return is_dir($this->fileDir);
			}	
		
	}
	
	public function generateThumbnail($thumb_name = '', $width = 800, $height = 0, $auto = false, $watermark = array(), $watermark2 = array()){
		
		
		 
		if(!$thumb_name){ // neni nazev, zmenusejm primo ten soubor
			
		} else {
			$this->testThumbDir($thumb_name);
		}
		
		$thumb = new Thumbnail($this->file);	        // Contructor and set source image file
		$thumb->memory_limit='64M';
		$thumb->max_execution_time='60';		
		
		if(count($watermark2)){
			$thumb->quality=100;
		} else {
			$quality =  Zend_Registry::getInstance()->config->images->quality;			
			$quality = $quality?$quality:100;
			$thumb->quality=$quality;                         // [OPTIONAL] default 75 , only for JPG format 
		}
		
		$thumb->output_format='jpg';                // [OPTIONAL] JPG | PNG
		$thumb->jpeg_progressive=0;               // [OPTIONAL] set progressive JPEG : 0 = no , 1 = yes
		$thumb->allow_enlarge=false;              // [OPTIONAL] allow to enlarge the thumbnail
		
		if($height){
			$thumb->size($width, $height);
		} else {
			if($auto){
				$thumb->size_auto($width);  
			} else {
				$thumb->size_width($width);
			}
		}
		
		if(count($watermark)){
			$thumb->img_watermark=$watermark['image'];	    // [OPTIONAL] set watermark source file, only PNG format [RECOMENDED ONLY WITH GD 2 ]
			$thumb->img_watermark_Valing=$watermark['Valing'];   	    // [OPTIONAL] set watermark vertical position, TOP | CENTER | BOTTOM
			$thumb->img_watermark_Haling=$watermark['Haling'];   	    // [OPTIONAL] set watermark horizonatal position, LEFT | CENTER | RIGHT
			/*
			$thumb->txt_watermark='Watermark text';	    // [OPTIONAL] set watermark text [RECOMENDED ONLY WITH GD 2 ]
			$thumb->txt_watermark_color='000000';	    // [OPTIONAL] set watermark text color , RGB Hexadecimal[RECOMENDED ONLY WITH GD 2 ]
			$thumb->txt_watermark_font=1;	            // [OPTIONAL] set watermark text font: 1,2,3,4,5
			$thumb->txt_watermark_Valing='TOP';   	    // [OPTIONAL] set watermark text vertical position, TOP | CENTER | BOTTOM
			$thumb->txt_watermark_Haling='LEFT';       // [OPTIONAL] set watermark text horizonatal position, LEFT | CENTER | RIGHT
			$thumb->txt_watermark_Hmargin=10;          // [OPTIONAL] set watermark text horizonatal margin in pixels
			$thumb->txt_watermark_Vmargin=10;           // [OPTIONAL] set watermark text vertical margin in pixels
			*/
		}
 		
		
		$thumb->process();  // generate image
		$resize = true;
		if(!$thumb_name){ // neni nazev, zmenusejm primo ten soubor
			$img_info =  getimagesize( $this->file );
			$img_w = $img_info[0];  //original dimensions
			$img_h = $img_info[1];
			if($width > $img_w){			 	
				$resize = false;
			}
			$thumbFilename = $this->file; 
		} else {
			$thumbFilename = $this->fileDir . '/' .$thumb_name . "/" . $this->filename2 .  "." . $thumb->output_format;
		}
		  
		
		if($resize){
			$thumb->save($thumbFilename);
		} 
		
		if(count($watermark2)){  
			$file = $this->file;
			$this->file = $thumbFilename;
			$this->generateThumbnail($thumb_name, $width, $height, $auto, $watermark2);
			$this->file = $file;
		}
 
		$img_info =  getimagesize( $thumbFilename );
		 
		$fnid = $this->nid;
		if($fnid){
			$nm = Zend_Registry::getInstance()->nodeMeta;   
			$cm = $nm->getMetaById($fnid);
			$thumb = array();
		// 	pr($cm); die();  
			if(!is_array($cm['thumbs'])){
				$cm['thumbs'] = array();
			}				 
			$cm['thumbs'][$thumb_name] = array(
				'width' => $img_info[0], 
				'height' => $img_info[1]
			);   
			$nm->setMetaById($fnid, $cm); 
		} 
	}	
	
	function create_grey($input,$output)
	{
	 $bild = imagecreatefromjpeg($input);
	 $x = imagesx($bild);
	 $y = imagesy($bild);
	
	 for($i=0; $i<$y; $i++)
	 {
	  for($j=0; $j<$x; $j++)
	  {
	   $pos = imagecolorat($bild, $j, $i);
	   $f = imagecolorsforindex($bild, $pos);
	   $gst = $f["red"]*0.15 + $f["green"]*0.5 + $f["blue"]*0.35;
	   $col = imagecolorresolve($bild, $gst, $gst, $gst);
	   imagesetpixel($bild, $j, $i, $col);
	  }
	 }
	 imagejpeg($bild,$output,90);
	}

	public function generateGrayscale($thumb_name){
		$this->testThumbDir();
		
		$ext = content_SFSFile::getFileExtension($this->file);		
		if($ext == 'jpg' || $ext == 'png' || $ext == 'gif'){				
			switch ($ext){
				case 'jpg':
					$image = imagecreatefromjpeg($this->file);
					break;
				case 'gif':
					$image = imagecreatefromgif($this->file);
					break;
				case 'png':					
					$image = imagecreatefrompng($this->file);
					break;
			}	
			
			//imagefilter($image, IMG_FILTER_BRIGHTNESS, 100);
			
			$x = imagesx($image);
			 $y = imagesy($image);
			
			 for($i=0; $i<$y; $i++)
			 {
			  for($j=0; $j<$x; $j++)
			  {
			   $pos = imagecolorat($image, $j, $i);
			   $f = imagecolorsforindex($image, $pos);
			   $gst = $f["red"]*0.15 + $f["green"]*0.5 + $f["blue"]*0.35;
			   $col = imagecolorresolve($image, $gst, $gst, $gst);
			   imagesetpixel($image, $j, $i, $col);
			  }
			 }
	 
			switch ($ext){
				case 'jpg':
					imagejpeg($image, $this->fileDir . '/' .$thumb_name . "." . $ext);
					break;
				case 'gif':
					imagegif($image, $this->fileDir . '/' .$thumb_name . "." . $ext);
					break;
				case 'png':					
					imagepng($image, $this->fileDir . '/' .$thumb_name . "." . $ext);
					break;
			}			
			//imagedestroy($image);
		}
	}
	
	function crop($input, $saveTo = ''){
		$x = $input->crop_x;
		$y = $input->crop_y;
		$width = $input->crop_width;
		$height = $input->crop_height;
		$percentSize = $input->crop_percent_size;
		$convertTo = $input->convertTo;
		$convertTo = $convertTo?$convertTo:'jpg';
	
		$x = preg_replace("/[^0-9]/si","",$x);
		$y = preg_replace("/[^0-9]/si","",$y);
		$width = preg_replace("/[^0-9]/si","",$width);
		$height = preg_replace("/[^0-9]/si","",$height);
		$percentSize = preg_replace("/[^0-9]/si","",$percentSize);
		
		$this->file = str_replace("../","",$this->file);
		if($percentSize>200){
			$percentSize = 200;
		}
		
		
		if(strlen($x) && strlen($y) && $width && $height && $percentSize){
			
			$thumb2 = new Thumbnail2($this->file);
			$thumb2->crop($x, $y, $width, $height);
			
			if(!$saveTo){
				$saveTo = $this->file;
			} else {
				$filename = content_SFSFile::getFileName($this->file);
				$saveTo .= '/' . $filename;
			}
			
			$thumb2->save($saveTo, 100);
			/*
			if($percentSize != 100){
				$x = $x * ($percentSize/100);	
				$y = $y * ($percentSize/100);	
				$width = $width * ($percentSize/100);	
				$height = $height * ($percentSize/100);	
			}
			*/			
		} else {
			$saveTo = false;
		}
		return $saveTo;
		
	}
}

?>