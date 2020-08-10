<?php
header ( 'Content-Type: text/html; charset=UTF-8' );
/**
 *
 * @author midzil 
 * skladovost????
 *        
 */
class module_ImportWeb {  
    private $_categories = array(

        //grily
        //   '76931|76932' => 'https://www.weber.com/CZ/cs/grily/grily-na-d%C5%99ev%C4%9Bn%C3%A9-uhl%C3%AD/',
      //     '76931|76933' => 'https://www.weber.com/CZ/cs/grily/plynov%C3%A9-grily/',
    //       '76931|76934' => 'https://www.weber.com/CZ/cs/grily/elektrick%C3%A9-grily/',
  //         '76931|76935' => 'https://www.weber.com/CZ/cs/grily/p%C5%99enosn%C3%A9-grily/',
//           '76931|76936' => 'https://www.weber.com/CZ/cs/grily/peletov%C3%A9-grily/',

           // přislušenství
           '78401|78667' => 'https://www.weber.com/CZ/cs/p%C5%99%C3%ADslu%C5%A1enstv%C3%AD/grilov%C3%A1n%C3%AD/grilovac%C3%AD-n%C3%A1%C4%8Din%C3%AD/',
           '78401|78668' => 'https://www.weber.com/CZ/cs/p%C5%99%C3%ADslu%C5%A1enstv%C3%AD/grilov%C3%A1n%C3%AD/gourmet-bbq-system/',
           '78401|78669' => 'https://www.weber.com/CZ/cs/p%C5%99%C3%ADslu%C5%A1enstv%C3%AD/grilov%C3%A1n%C3%AD/brikety--uhl%C3%AD-a-dal%C5%A1%C3%AD-p%C5%99%C3%ADslu%C5%A1enstv%C3%AD/',
           '78401|78670' => 'https://www.weber.com/CZ/cs/p%C5%99%C3%ADslu%C5%A1enstv%C3%AD/grilov%C3%A1n%C3%AD/igrill--teplom%C4%9Bry-a-%C4%8Dasova%C4%8De/',
           '78401|78671' => 'https://www.weber.com/CZ/cs/p%C5%99%C3%ADslu%C5%A1enstv%C3%AD/grilov%C3%A1n%C3%AD/ot%C3%A1%C4%8Dec%C3%AD-%C5%A1p%C3%ADzy/',
           '78401|78672' => 'https://www.weber.com/CZ/cs/p%C5%99%C3%ADslu%C5%A1enstv%C3%AD/grilov%C3%A1n%C3%AD/p%C3%A1nve-a-planchy/',
           '78401|78673' => 'https://www.weber.com/CZ/cs/p%C5%99%C3%ADslu%C5%A1enstv%C3%AD/grilov%C3%A1n%C3%AD/ko%C5%A1e--p%C3%A1nve-a-misky/',
           '78401|78674' => 'https://www.weber.com/CZ/cs/p%C5%99%C3%ADslu%C5%A1enstv%C3%AD/grilov%C3%A1n%C3%AD/stojany-na-grilov%C3%A1n%C3%AD-a-ro%C5%BEn%C4%9B/',
           '78401|78675' => 'https://www.weber.com/CZ/cs/p%C5%99%C3%ADslu%C5%A1enstv%C3%AD/grilov%C3%A1n%C3%AD/ud%C3%ADc%C3%AD-lup%C3%ADnky-a-%C5%A1pal%C3%ADky--ud%C3%ADc%C3%AD-prk%C3%A9nka-a-p%C5%99%C3%ADslu%C5%A1enstv%C3%AD/',
           '78401|78676' => 'https://www.weber.com/CZ/cs/p%C5%99%C3%ADslu%C5%A1enstv%C3%AD/grilov%C3%A1n%C3%AD/patrov%C3%BD-grilovac%C3%AD-syst%C3%A9m-elevations/',
           

           '78401|78677' => 'https://www.weber.com/CZ/cs/p%C5%99%C3%ADslu%C5%A1enstv%C3%AD/grilov%C3%A1n%C3%AD/keramika/',
           '78401|78678' => 'https://www.weber.com/CZ/cs/p%C5%99%C3%ADslu%C5%A1enstv%C3%AD/grilov%C3%A1n%C3%AD/grilovac%C3%AD-a-pizza-kameny/',
           '78401|78679' => 'https://www.weber.com/CZ/cs/p%C5%99%C3%ADslu%C5%A1enstv%C3%AD/grilov%C3%A1n%C3%AD/d%C5%99ev%C4%9Bn%C3%A9-pelety/',
           '78401|78680' => 'https://www.weber.com/CZ/cs/p%C5%99%C3%ADslu%C5%A1enstv%C3%AD/grilov%C3%A1n%C3%AD/weber-connect/',
           

    );

    private  $_tableSepareteProd = 'separeProduct';
    private $_tableVariants = 'module_eshop_variants';     

    function __construct($view)
    {
        $this->db = Zend_Registry::getInstance ()->db;
        $this->config = Zend_Registry::getInstance ()->config;
        $this->mVarianta = new module_Varianta();   
        $this->view = $view;
        $this->tree = Zend_Registry::getInstance ()->tree; 
    }

    public function addActicle()
    {
        $data = $this->db->fetchAll('select properties from '. $this->_tableSepareteProd);
        foreach($data as $item)
        {
            $array = (array)(json_decode($item['properties']));  
            foreach($array as $key => $it)
            {
                $isExist = $this->db->fetchOne("select id from Nodes where `parent` = 76947 AND `type` LIKE 'ITEM' and title =?",$it);   
                if(!$isExist)
                {    
                    $imageSave = $this->importImageArticles($key);
                    $input = $inputContent = new stdClass();
                    $input->title = $input->pageTitle = $it;  
                    $input->parent = 76947;
                    $inputContent->parentSection = 76947;  
                    $inputContent->photos = $imageSave;  
                    $inputContent->state = 'PUBLISHED';  
                    $inputContent->owner = 'a';    
                    $inputContent->dateShow = '1.1.2020';  
                    $newNode = Node::init ( 'ITEM', 76947, $input, $this->view );
		            $content = Content::init ( 'Article', $inputContent, false ); 
                    $content->getPropertyByName ( 'parent' )->value = $inputContent->parentSection;
                    $content->getPropertyByName ( 'photos' )->value = $imageSave;     
                    $content->getPropertyByName ( 'html' )->value = $inputContent->html;    
                    $err2 = $content->save ();        
		            $this->tree->addNode ( $newNode, false, false );          
                    $this->tree->pareNodeAndContent ( $newNode->nodeId, $content->id, $content->_name );	
                }    
            }
        }
    }

    private function getPropertiesId($title)
    {
        $id = $this->db->fetchOne("select id from Nodes where `parent` = 76947 AND `type` LIKE 'ITEM' and title =?",$title);   
        return $id > 0 ? $id : '';
    }

    private function importImageArticles($img) {
		$config = $this->config;
        $contents = file_get_contents ( $img ); 
        $imageTemp = explode("/",$img);
        $imageName = end($imageTemp);
        $filepath = $this->config->fsRoot . '/obrazky/v-clanich/' . $imageName;
        file_put_contents ( $filepath, $contents );  
		$view = $this->view;
		$view->input = new stdClass ();
		$view->input->fullpath = substr ( $filepath, strlen ( $this->config->fsRoot ) );
		$view->input->state = 'PUBLISHED';
        $view->input->owner = 'a';
		$file = helper_Nodes::initContent ( 'SFSFile', $view->input, $view );
		if ($file->getPropertyValue ( 'fullpath' )) {
			$nnode = helper_Nodes::addNodeWithContent ( $file, 3378, $imageName, $view, false, true );
        }    
        $path = $config->sfFolder . '/' . content_SFSFile::getSFSPath ( $nnode->nodeId, $nnode->path ) . ';' . content_SFSFile::getFileWithouExtension ( $nnode->title );
        $contentArticles = new content_Article();
		$contentArticles->properties = array (
            new ContentProperty ( 'photos', 'MultiFileSelect', '', array (), array (), array (
            'showSelectFile' => true,
           'inputWidth' => '300',
            'maxFiles' => 10,
            'showUploadFile' => true, 
            'uploadFileDirectoryNodeId' => 3378 
            ) ) 
        );   
    
        $contentArticles->getPropertyByName ( 'photos' )->value = $path; 
        $contentArticles->createFiles ();    
		return $path;
    }

    public function addNewSimple($data) {
        $input = new stdClass();
		$input->title = $input->pageTitle = $data->title;      
        list($inputContent,$variant) = $this->setInput($data);   
		$this->addNodesAction ( 3801, $input, $inputContent, $variant);   
    } 
    
    public function insertProducts()
    { 
        $data = $this->db->fetchAll("select * from ".$this->_tableSepareteProd." where insert_product = '0'");  
        foreach($data as $item)
        {
            $object = (object)$item;
            $this->addNewSimple($object);
            $dataSaveToDB['insert_product'] = '1'; 
            $where = $this->db->quoteInto('id =?',$object->id);  
            $this->db->update($this->_tableSepareteProd,$dataSaveToDB,$where);   
        }  
    }

    private function getRada($name)
    {
          
        $name = trim(str_replace("&reg;","®",$name));
        $id = $this->db->fetchOne("select id from Nodes where `type` LIKE 'FOLDER' and title =?",$name);   
        return $id > 0 ? '|'.$id : "";     
    }

    private function setInput($data)
	{
        $input = new stdClass();
		$inputContent = new stdClass (); 
		$mVarianta = new module_Varianta();  
		$varianta = array();        
        $input->pageTitle = $data->title;          
        $inputContent->html = $data->mainText;
        $inputContent->parametry = $data->params;
        $inputContent->dphQuote = 21;         
        $inputContent->znacka = 1;         
        $inputContent->rating = 0;       
        $inputContent->sold = 0;        
        $inputContent->garancy = $data->garanty;
        $varianta['model'] = $data->articl;     
		$varianta['skladem'] = 1;
        $varianta['price'] =  $data->price;
        foreach($mVarianta->variantProperty['color']['selection'] as $key=>$ite)
        {
            if($data->color == $ite)
            $varianta['color'] = $key;  
        }     
        foreach((array)json_decode($data->properties) as $it)
        { 
            if($it)
                $articles[] = $this->getPropertiesId($it);
        }
        $inputContent->prop = implode("|",$articles);
        $inputContent->parentSection =  $data->parent.$this->getRada($data->line); 
        $inputContent->state = 'PUBLISHED';       
        foreach(json_decode($data->images) as $item)
        {  
           $images[] = $this->importImage($item);
        }
        $varianta['obrazky'] = implode(";",$images);
		return array($inputContent,$varianta);          
    }

    function png2jpg($originalFile, $outputFile, $quality){
        $source = imagecreatefrompng($originalFile);
        $image = imagecreatetruecolor(imagesx($source), imagesy($source));
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);
        imagecopy($image, $source, 0, 0, 0, 0, imagesx($image), imagesy($image));
        imagejpeg($image, $outputFile, $quality);
        imagedestroy($image);
        imagedestroy($source);       
    }

	private function importImage($img) {
		$config = $this->config;
        $contents = file_get_contents ( $img ); 
        $imageTemp = explode("/",$img);
        $imageName = end($imageTemp);
        $filepathTemp = $this->config->fsRoot . '/obrazky/produkty1/' . $imageName;
        $filepath = $this->config->fsRoot . '/obrazky/produkty/' . str_replace(".png",".jpg",$imageName);
        file_put_contents ( $filepathTemp, $contents );
        $this->png2jpg($filepathTemp,$filepath,100);
		$view = $this->view;
		$view->input = new stdClass ();
		$view->input->fullpath = substr ( $filepath, strlen ( $this->config->fsRoot ) );
		$view->input->state = 'PUBLISHED';
        $view->input->owner = 'a';
          
		$file = helper_Nodes::initContent ( 'SFSFile', $view->input, $view );
		if ($file->getPropertyValue ( 'fullpath' )) {
			$nnode = helper_Nodes::addNodeWithContent ( $file, 76931, $imageName, $view, false, true );
        }    
		$path = $config->sfFolder . '/' . content_SFSFile::getSFSPath ( $nnode->nodeId, $nnode->path ) . ';' . content_SFSFile::getFileWithouExtension ( $nnode->title );
		$this->mVarianta->resizePhotos($path);
		return $path;
	}
    
    function addNodesAction($nodeAddTo, $input, $inputContent, $variant) {
		$newNode = Node::init ( 'ITEM', $nodeAddTo, $input, $this->view );
        $content = Content::init ( 'Product', $inputContent, false ); 
        
		$content->getPropertyByName ( 'parent' )->value = $inputContent->parentSection;
        $content->getPropertyByName ( 'html' )->value = $inputContent->html;        
         
		$this->save ( $newNode, $content, $inputContent,$variant );
    }
    
    function save($newNode, $content, $inputContent,$variant) {
		$err2 = $content->save ();   
        $this->tree->addNode ( $newNode, false, false );     
            
        $this->tree->pareNodeAndContent ( $newNode->nodeId, $content->id, $content->_name );	

        $_POST = $_GET = ( array ) $inputContent;
        $variant['ext_id'] = $newNode->nodeId;   
		$this->saveDataVar ( $this->view, $newNode, $content,$variant);
    }
    
    function saveDataVar($view, $node, $content,$variant) {
		$variant['id_product'] = $content->id; 
		$this->db->insert($this->_tableVariants, $variant);  
		$idVariant =  $this->db->lastInsertId ();
    }


    // upravit aby to vzalo vše z cyklu
    public function separeteProduct($url = false)
    {    
    foreach($this->_categories as $keyCat=>$url){
        pr($url);    
        $html = file_get_contents($url);  
        $t = explode('<div class="template-product-all-grills-featured js-all-grills-featured">', $html);
        $tt = explode('<div class="template-product-all-grills-list is-hidden js-all-grills-list', $t[1]);
        $htmlDom = new DOMDocument();
        $htmlDom->loadHTML($tt[0]);
        $links = $htmlDom->getElementsByTagName('a');  
        $extractedLinks = array();
        $data['parent'] = $keyCat;        
        foreach($links as $link){ 
            if(is_numeric(strpos($link->getAttribute('href'),'https')))
                $extractedLinks[$link->getAttribute('href')] = $link->getAttribute('href');
        }
        pr($extractedLinks);     
        die;      
        foreach ($extractedLinks as $item)
        {
            $data['url'] = $item;
            pr($item);  
            if($this->db->fetchOne("select id from ".$this->_tableSepareteProd." where url =?",$item));
            $this->db->insert($this->_tableSepareteProd,$data);  
        }  
    }
    }

    // upravit aby to vzalo vše z cyklu
    public function separeteProductCategory($url = false)
    {    
    foreach($this->_categories as $keyCat=>$url){
        pr($url);        
        $html = file_get_contents($url);  
        $t = explode('search-result-items tiles-container bee__search-result-items bee__tiles-container hide-compare', $html);
        $tt = explode('search-result-footer bee__search-result-footer', $t[1]);
       // pr($tt[0]);
       // die;     
        $htmlDom = new DOMDocument();
        $htmlDom->loadHTML($tt[0]);
        $links = $htmlDom->getElementsByTagName('a');  
             
        $extractedLinks = array();
        $data['parent'] = $keyCat;        
        foreach($links as $link){ 
            
            if(is_numeric(strpos($link->getAttribute('href'),'/CZ/cs/'))){
                    $extractedLinks[$link->getAttribute('href')] = 'https://www.weber.com/'.$link->getAttribute('href');
                }
        }       
        foreach ($extractedLinks as $item)
        {
            $data['url'] = $item;
            pr($item);  
            if($this->db->fetchOne("select id from ".$this->_tableSepareteProd." where url =?",$item));
            $this->db->insert($this->_tableSepareteProd,$data);  
        }  
    }
    }

    private function helperChart($text)
    {
        $text = str_replace("&eacute;","ě",$text);
        $text = str_replace("&iacute;","í",$text);
        $text = str_replace("&aacute;","á",$text);
        $text = str_replace("&reg;","®",$text);
        $text = str_replace("&yacute;","ý",$text);
        
        return trim($text);
    }

    public function setProducts(){
       $data = $this->db->fetchAll("select id,url from ".$this->_tableSepareteProd." where checked = '0'");  
       foreach($data as $item)
       {
           $this->separeteDataPrislusenstvi($item['url'],$item['id']);
       }  
    }   
   
       // todo řata.... přidat
       public function separeteDataPrislusenstvi($url = false,$id = false)   
       {     
          // $url = 'https://www.weber.com/CZ/cs/grily/grily-na-d%C5%99ev%C4%9Bn%C3%A9-uhl%C3%AD/%C5%99ada-master-touch/17401004.html';
           $html = file_get_contents($url); 
           $t = explode('<div class="bee__pdp-hero__description">', $html);     
           $tt = explode('</div>', $t[1]);   
           $dataSaveToDB = array();
           $dataSaveToDB['mainText'] = trim(strip_tags($tt[0])); // text nahoře 
           //title
           $t = explode('<h2 class="bee__pdp-hero__product-name">', $html);   
           $tt = explode('<br/>', $t[1]);        
           $title =  $this->helperChart($tt[0]);
           $tte = explode('</h2>', $this->helperChart($tt[0]));      
           $dataSaveToDB['title'] = $tte[0];
           // cena
           $t = explode('<span class="bee__price--sales">', $html);  
           $tt = explode('</span>', $t[1]);   
           $price = str_replace(",00 Kč","",trim($tt[0]));
           $price = str_replace(".","",$price);
           $dataSaveToDB['price'] = $price;    
           /// articl
           $t = explode('<p id="bee__pdp-hero__product-id">Artikl. č. #', $html);  
           $tt = explode('</p>', $t[1]);   
           $articl = $tt[0];  
           $dataSaveToDB['articl'] = $articl;
    
           //images        
           $t = explode('<div class="bee__pdp-hero--right">', $html);
           $tt = explode('</span></span></button>', $t[1]);     
           $dom = new DOMDocument();
           $dom->loadHTML( $tt[0] );
           foreach( $dom->getElementsByTagName( 'img' ) as $node )
           {            
               $images[] =  str_replace("?auto=compress,format&fit=fill&h=950&w=1000&trim=color&trimtol=10&bg=0FFF&pad=50","",$node->getAttribute( 'src' ) );
           }  
           $dataSaveToDB['images'] = json_encode($images);
   
           // parametry asi ještě doladit do jiného html
           $t = explode('<div class="flyout-content bee__flyout__content bee__flyout__part bee__flyout__content--full-width">', $html);
           $tt = explode('<li class="bee__flyout__elem flyout-ctr">', $t[1]);   
           $dataSaveToDB['params'] =  '
           <div class="flyout-content bee__flyout__content bee__flyout__part bee__flyout__content--full-width">'.($tt[0]).'';//$c->getPropertyValue('parametry');      ;
           $tt = (str_replace("</li>","",$dataSaveToDB['params']));
           $ttqw = explode('<div class="bee__pdp-hero--right">',  $tt);     
           $dataSaveToDB['params'] = (str_replace("</ul>","</li></ul>",$ttqw[0]));   
           // prozkoumejte vlastnosti
           $t = explode('<div class="template-details-items-list-wrapper js-slider-wrapper">', $html);
           $tt = explode('</section>', $t[1]);  
           // images k vlastnostem
           $ttt = explode("background-image: url('",$tt[0]);
           unset($ttt[0]);
           array_pop($ttt);
           $imagesVlastnosti = array();
           foreach($ttt as $it){
               $im = explode("?auto=compress",$it); 
               if(!in_array($im[0],$imagesVlastnosti))
               { 
                   $imagesVlastnosti[] = $im[0];
               }
           }
           $inc = 0;
           $dom = new DOMDocument();       
           $dom->loadHTML($tt[0]);
           $headings = $dom->getElementsByTagName('h3');
           foreach($headings as $item)
           {
               $vlastnosti[$imagesVlastnosti[$inc]] = trim(mb_convert_encoding( $item->nodeValue, "Windows-1252", "UTF-8" )); 
               $inc++;
           }
           $dataSaveToDB['properties'] = json_encode($vlastnosti);
           
           // doporučujeme dokoupit
           $t = explode('<span class="bee__product-title">', $html);
           unset($t[0]);
           foreach($t as $item)
           { 
               
               $temp = explode("</span>",$item); 
               $title = $temp[0];
               $souuviseji[$title] = $this->helperChart($title);
           }
           $newObject = new stdClass(); 
           $dataSaveToDB['shiping'] = json_encode($souuviseji);
           $dataSaveToDB['checked'] = '1';   
           //zaruka 
      
           //přislušentsví upozorněni
           if($dataSaveToDB['images'])
           {  
               $where = $this->db->quoteInto('id =?',$id);  
               $this->db->update($this->_tableSepareteProd,$dataSaveToDB,$where);   
           }
       }    
    

    // todo řata.... přidat
    public function separeteData($url = false,$id = false)   
    {   
       // $url = 'https://www.weber.com/CZ/cs/grily/grily-na-d%C5%99ev%C4%9Bn%C3%A9-uhl%C3%AD/%C5%99ada-master-touch/17401004.html';
        $html = file_get_contents($url); 
        $t = explode('<div class="bee__pdp-hero__description">', $html);     
        $tt = explode('</div>', $t[1]);   
        $dataSaveToDB = array();
        $dataSaveToDB['mainText'] = trim(strip_tags($tt[0])); // text nahoře 
        //title
        $t = explode('<h2 class="bee__pdp-hero__product-name">', $html);   
        $tt = explode('<br/>', $t[1]);        
        $title =  $this->helperChart($tt[0]); 
        $dataSaveToDB['title'] = $title;
        //barva  
        $tte = explode('</h2>', $tt[1]);     
        $color = $this->helperChart(strip_tags($tte[0]));
        $dataSaveToDB['color'] = $color;
        // řada
        $t = explode('<h1 class="bee__pdp-hero__series-name">', $html); 
        $tt = explode('</h1>', $t[1]);   
        $dataSaveToDB['line'] = $tt[0];  
        
        // cena
        $t = explode('<span class="bee__price--sales">', $html);  
        $tt = explode('</span>', $t[1]);   
        $price = str_replace(",00 Kč","",trim($tt[0]));
        $price = str_replace(".","",$price);
        $dataSaveToDB['price'] = $price;    
        /// articl
        $t = explode('<p id="bee__pdp-hero__product-id">Artikl. č. #', $html);  
        $tt = explode('</p>', $t[1]);   
        $articl = $tt[0];  
        $dataSaveToDB['articl'] = $articl;
 
        //images        
        $t = explode('<div class="bee__pdp-hero--right">', $html);
        $tt = explode('</span></span></button>', $t[1]);     
        $dom = new DOMDocument();
        $dom->loadHTML( $tt[0] );
        foreach( $dom->getElementsByTagName( 'img' ) as $node )
        {            
            $images[] =  str_replace("?auto=compress,format&fit=fill&h=950&w=1000&trim=color&trimtol=10&bg=0FFF&pad=50","",$node->getAttribute( 'src' ) );
        }  
        $dataSaveToDB['images'] = json_encode($images);

        // parametry asi ještě doladit do jiného html
        $t = explode('<div class="flyout-content bee__flyout__content bee__flyout__part bee__flyout__content--full-width">', $html);
        $tt = explode('<li class="bee__flyout__elem flyout-ctr">', $t[1]);   
        $dataSaveToDB['params'] =  '
        <div class="flyout-content bee__flyout__content bee__flyout__part bee__flyout__content--full-width">'.($tt[0]).'';//$c->getPropertyValue('parametry');      ;
        $tt = (str_replace("</li>","",$dataSaveToDB['params']));
        $dataSaveToDB['params'] = (str_replace("</ul>","</li></ul>",$tt));
        //zaruka --- přislušentsti upozorněni
        $tes = explode('<div class="bee__flyout__content bee__flyout__part flyout-content">',$t[1]);
        $test = explode('</dl>',$tes[1]);          
        $dataSaveToDB['garanty'] = $test[0].'</dl></div></div>';
        pr($dataSaveToDB['garanty']);      
        // prozkoumejte vlastnosti
        $t = explode('<div class="template-details-items-list-wrapper js-slider-wrapper">', $html);
        $tt = explode('</section>', $t[1]);  
        // images k vlastnostem
        $ttt = explode("background-image: url('",$tt[0]);
        unset($ttt[0]);
        array_pop($ttt);
        $imagesVlastnosti = array();
        foreach($ttt as $it){
            $im = explode("?auto=compress",$it); 
            if(!in_array($im[0],$imagesVlastnosti))
            { 
                $imagesVlastnosti[] = $im[0];
            }
        }
        $inc = 0;
        $dom = new DOMDocument();       
        $dom->loadHTML($tt[0]);
        $headings = $dom->getElementsByTagName('h3');
        foreach($headings as $item)
        {
            $vlastnosti[$imagesVlastnosti[$inc]] = trim(mb_convert_encoding( $item->nodeValue, "Windows-1252", "UTF-8" )); 
            $inc++;
        }
        $dataSaveToDB['properties'] = json_encode($vlastnosti);
        
        // doporučujeme dokoupit
        $t = explode('<span class="bee__product-title">', $html);
        unset($t[0]);
        foreach($t as $item)
        { 
            
            $temp = explode("</span>",$item); 
            $title = $temp[0];
            $souuviseji[$title] = $this->helperChart($title);
        }
        $newObject = new stdClass(); 
        $dataSaveToDB['shiping'] = json_encode($souuviseji);
        $dataSaveToDB['checked'] = '1'; 
        pr($dataSaveToDB);  
        die;     
        //zaruka 

        //přislušentsví upozorněni
        if($dataSaveToDB['images'])
        {  
            $where = $this->db->quoteInto('id =?',$id);  
            $this->db->update($this->_tableSepareteProd,$dataSaveToDB,$where);   
        }
    }    

    public function show()
    {

        return (object)$this->db->fetchRow("select * from ".$this->_tableSepareteProd." where id = 1");
    }
}
?>