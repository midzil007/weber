<?php
header ( 'Content-Type: text/html; charset=UTF-8' );
/**
 *
 * @author midzil
 *        
 */
class module_ImportWeb {
    private $_categories = array(
            'https://www.weber.com/CZ/cs/grily/'
    );

    

    function __construct($view)
    {
        $this->db = Zend_Registry::getInstance ()->db;
        $this->config = Zend_Registry::getInstance ()->config;
        $this->_tableSepareteProd = 'separeProduct';
    }
    // upravit aby to vzalo vše z cyklu
    public function separeteProduct($url = false)
    {
        $url = 'https://www.weber.com/CZ/cs/grily/grily-na-d%C5%99ev%C4%9Bn%C3%A9-uhl%C3%AD/';
        $html = file_get_contents($url); 
        $t = explode('<div class="template-product-all-grills-featured js-all-grills-featured">', $html);
        $tt = explode('<div class="template-product-all-grills-list is-hidden js-all-grills-list', $t[1]);
        $htmlDom = new DOMDocument();
        $htmlDom->loadHTML($tt[0]);
        $links = $htmlDom->getElementsByTagName('a');
        $extractedLinks = array();
        foreach($links as $link){
            if(is_numeric(strpos($link->getAttribute('href'),'https')))
                $extractedLinks[$link->getAttribute('href')] = $link->getAttribute('href');
        }
 
//var_dump the array for example purposes
pr($extractedLinks);
        die;    
        preg_match_all('~<a(.*?)href="([^"]+)"(.*?)>~', $t[1], $matches);
        $url = var_dump($matches[2][0]);      
        (pr($matches));
        die;
    }

    private function helperChart($text)
    {
        $text = str_replace("&eacute;","ě",$text);
        $text = str_replace("&iacute;","í",$text);
        $text = str_replace("&aacute;","á",$text);
        return trim($text);
    }
    // todo řata.... přidat
    public function separeteData($url = false) 
    {
        $url = 'https://www.weber.com/CZ/cs/grily/grily-na-d%C5%99ev%C4%9Bn%C3%A9-uhl%C3%AD/%C5%99ada-master-touch/17401004.html';
        $html = file_get_contents($url); 
        $t = explode('<div class="bee__pdp-hero__description">', $html);   
        $tt = explode('</div>', $t[1]);   
        $mainText = trim(strip_tags($tt[0])); // text nahoře
        $dataSaveToDB = array();
        //title
        $t = explode('<h2 class="bee__pdp-hero__product-name">', $html);   
        $tt = explode('<br/>', $t[1]);        
        $title =  $this->helperChart($tt[0]); 
        $dataSaveToDB['title'] = $title;
        //barva  
        $tte = explode('</h2>', $tt[1]);     
        $color = $this->helperChart(strip_tags($tte[0]));
        $dataSaveToDB['color'] = $color;
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
        $dataSaveToDB['params'] = $tt[0];

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
        $this->db->insert($this->_tableSepareteProd,$dataSaveToDB);  
        die;   
    }  
}
?>