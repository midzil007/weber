<?

/**
 * RSS
 */
class module_RSS {
	
	public function __construct() {
		$this->rss = new RSSgenerator();
		$this->config = Zend_Registry::getInstance()->config;		 	
	}
	
	public function addNodeChilrenAsChanel($nodeChildren, $view, $chanelIdentificator, $chanelTitle, $description = '', $image = array(), $link = '')
	{
		$title = $chanelTitle?$chanelTitle:$node->title;	
		$link = $link?$link:$this->config->protocol . $this->config->webhost; 
		
		$chanel = array(
			'title'=> $title, 
			'description'=> $description,
			'link'=>$link,
			'language'=>'cs'
		);
		
		if(count($image)){
			$chanel['image'] = $image;
			/*
			image'=>array(
				'title'=>'Regionální rada regionu soudržnosti Severovýchod - logo',
				'url'=> $this->config->protocol . $this->config->webhost . '/images/logo.gif',
				'link'=>$this->config->protocol . $this->config->webhost,
				'width'=>'150',
				'height'=>'78',
				'description'=>'',
			)
			*/
		}		
		
		$webUrl = Utils::getWebUrl();  
		
		$this->rss->add_chanel($chanelIdentificator, $chanel);				
		$max = 10;
		$i = 0;
		foreach ($nodeChildren as $node){
			$content = $node->getPublishedContent();
			if(!$content){
				continue;
			}
			
			if($i > $max){
				break;
			}
			
			
			if(method_exists(helper_FrontEnd, 'getPath')){  
				$url = helper_FrontEnd::getPath($view, $node->path);
			} else {
				$url = Utils::getWebPath($node->path);
			}
			
			if(substr($url, 0, 4) != 'http'){ 
				$url = $webUrl . $url; 
			}
			
			$descr = Utils::getReducedText($content->getPropertyValue('html'));
			
			if(method_exists($content, 'getRSSImages')){ 
				$images = $content->getRSSImages(); 
				if(count($images)){		  
					$descr = '<![CDATA[' . $descr . '<br /><br />'; 
					foreach ($images as $img){
						/*
						 $data['image'] = array(
						      'title'=> $img['name'],
						      'url'=> $this->webUrl . $img['path'],
						      'link'=> $this->webUrl . $img['path'], 
						      'description'=> $node->title,
						    );*/
						$descr .= '<img src="' . $this->webUrl . $img['path'] . '" alt="' . $img['name'] . '" /> &nbsp; '; 
					} 
					$descr .= ']]>'; 
				}
			} 
			$data = array(
				'title'=>$node->title,
				'description'=> $descr, 
				'link'=>$url,
				'pubDate'=> date('c', strtotime($node->dateModif))  				
			);
			  
			
			/*
						      'width'=>'60',
						      'height'=>'60',*/ 
			
			
			$this->rss->add_item($chanelIdentificator,
			  $data
			);
			$i++;
		}		
	}
	
	public function addArrayAsChanel($data, $view, $chanelIdentificator, $chanelTitle, $description = '', $image = array(), $max = 50)
	{
		$title = $chanelTitle;	
		
		$chanel = array(
			'title'=> $title,
			'description'=> $description,
			'link'=>$this->config->protocol . $this->config->webhost,
			'language'=>'cs'
		);
		
		if(count($image)){
			$chanel['image'] = $image;
			/*
			image'=>array(
				'title'=>'Regionální rada regionu soudržnosti Severovýchod - logo',
				'url'=> $this->config->protocol . $this->config->webhost . '/images/logo.gif',
				'link'=>$this->config->protocol . $this->config->webhost,
				'width'=>'150',
				'height'=>'78',
				'description'=>'',
			)
			*/
		}		
		$this->rss->add_chanel($chanelIdentificator, $chanel);	
		
		$i = 0;
		
		$webUrl = Utils::getWebUrl(); 
		
		foreach ($data as $info){			
			if($i > $max){
				break;
			}			
			// e($info['url']); 
			if(substr($info['url'], 0, 4) != 'http'){
				$info['url'] = $webUrl . $info['url']; 
			}
			$this->rss->add_item($chanelIdentificator,
			  array(
			    'title'=> $info['title'],
			    'description'=> $info['description'],
			    'link'=> $info['url'] 
			  )
			);
			$i++;
		}		
	}
	
	function generate(){		
		header('Content-type: text/xml;');				
		echo $this->rss->create_rss();
	}

}
?>