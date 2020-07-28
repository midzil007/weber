<?php

class module_Products
{   
	function __construct($domain = ''){
		$this->db =  Zend_Registry::getInstance()->db;
		$this->tree =  Zend_Registry::getInstance()->tree;
		$this->_tableName = 'content_Product';
		$this->_tableName = 'content_Product';
		$this->_tableNameVariants = 'module_eshop_variants';
		$this->_tableHistorySearch ='historySearch';
		$this->_tableNameOver = 'content_OverviewProducts';
		$this->_tableNameZnacky = 'module_eshop_Znacky';
		$this->_tableNameCache = 'cacheSearch';
		$this->_tableNameOption = 'module_eshop_variants_options';
	} 
	
	
	
	
	public function initSearchOver( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params, $count = false)
	{
	
		if($sort=='sort')
		{
			$sort='cm.sort';
		}
		$select =  $this->db->select();
		$bind = array();
	
		if($count){
			$c =  new Zend_Db_Expr("count('*')");
			$select->from(array('cm' => $this->_tableNameOver), array( $c,  $disc ));
		}
		else {
			$select->from(array( 'cm' => $this->_tableNameOver), array('cid' => 'cm.id', 'n.id', 'n.title', 'n.path', 'n.parent', 'dateModif', 'dateCreate', 'html',  'photo'));
		}
		 
		$select->join(
				array('nc' => 'NodesContents'),
				'cm.id = nc.c_id',
				array()
		);
	
		$select->join(
				array('n' => 'Nodes'),
				'n.id = nc.n_id',
				array('n.title')
		);
		 
		if($sort == 'title')  {
			$sort = 'n.' . $sort;
		}
	
		if($params['category']){
			if(is_array($params['category'])){
				$childrenIds = $params['category'];
			} else {
				$childrenIds = $this->tree->getNodeChildrenIds($params['category'], array(), 'FOLDER');
				$childrenIds[] = $params['category'];
			}
			 
			if(count($childrenIds)){
				$w = array();
				foreach ($childrenIds as $id){
					$w[] = " cm.parent like '%$id%' ";
				}
				$select->where(implode('OR', $w));
			}
		}
	
	
		if($params['onHome'])
		{
			$select->where('showOnHome = ?', '1');
		}
		 
		if($params['topCategory'])
		{
			$select->where('topCategory = ?', '1');
		}
		$select->where('state = ?', 'PUBLISHED');
		$select->where('c_type = ?', 'content_OverviewProducts');
	
		 
		$sortType = $sortType?$sortType:'Asc';
		$select->order($sort . ' ' . $sortType);
		$select->order('n.id DESC');
		$select->limit($limitCount, $limitStart);
		//    	 if($_SERVER['REMOTE_ADDR'] == '94.138.107.65'){
		//     	}
		return array($select, $bind);
	}
	
	public function getProductsOver( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params = array())
	{
		list($select, $bind) = $this->initSearchOver($sort, $sortType, $limitStart, $limitCount, $params);
		return $this->db->fetchAll($select, $bind);
	}
      
	public function getWordProdukt($count)
	{
		if($count == 1)
		{
			return 'produkt';
		}
		else if($count>1 && $count<5)
		{
			return 'produkty';
		}
		else if($count>4)
		{
			return 'produktů';
		}
	}
	
public function getWordPieces($count)
	{
		if($count == 1)
		{
			return 'kus';
		}
		else if($count>1 && $count<5)
		{
			return 'kusy';
		}
		else if($count>4)
		{
			return 'kusů';
		}
	}
	
	public function renderBanerProduct($view, $product)
	{
		$v = $product;
				$photos = $view->mVarianta->getResizedPhotos($v['obrazky']);
				//$selectedVariant = $this->mVarianta->getVariantById($product['variantId']);
				
				$p = helper_FrontEnd::getFirstPhoto($photos,'pShow2' , false);
				$html .= '<a href="'.$v['path'].'">';
				$html .= '<div class="fLeft w220 marTop5 tAlignCenter">';
					$html .= '<img alt="'.$v['title'].'"  itemprop="image" src="'.$p['path'].'">';
				$html .= '</div>';
				$html .= '<div class="fLeft w550 posRelative">';
					$html .= '<span class="w450 fs30 i-block marTop10 blueText fBold">'.$v['title'].'</span>';
					$html .= '<p class="w450">'.$v['preText'].'</p>';
					$html .= '<div class="fLeft w260">';
					if($v['discount']>0){
						$html .= '<p class="fs16 marTop10">Běžná cena: <span class="tLineThrough colorGrey">'.helper_FrontEnd::price($v['price2']).' Kč</span>'; 
						$html .= '<span class="redBgr white fBold i-block marLeft5">SLEVA -'.$v['discount'].'%</span></p>';
					}
					$html .= '<p><span class="fBold blackText fs35">'.helper_FrontEnd::price($v['price']).'  </span><span class="fs16">vč. DPH</span></p>';
					$html .= '<span class="smallBuy blockDisplay"></a>';
				$html .= '</div>';
				$html .= '<div class="fLeft marTop11">';
				if($v['bioFresh'] || $v['noFrost'] || $v['enerClass'] > 2){
					$html .= '<p class="blackText fs14">Technologie:</p>';
					$html .= '<table class="marBottom20">';
					$html .= '<tr>';
						if($v['id']){
								$html .= '<td><img src="/images/activegreen.jpg.jpeg" alt="Active Green" width="67" height="44"></td>';
						}
						if($v['bioFresh']){
								$html .= '<td><img src="/images/biofresh.jpg.jpeg" alt="Bio Fresh" width="63" height="42"></td>';
						}
						if($v['noFrost']){
								$html .= '<td><img src="/images/nofrost.jpg.jpeg" alt="No Frost" width="63" height="42"></td>';
						}
						if($v['enerClass'] >2){
								$html .= '<td><img src="/images/usporne.jpg.jpeg" alt="Usporne" width="60" height="40"></td>';
						}
						if($v['id']){
								$html .= '<td><img src="/images/wine.jpg.jpeg" alt="Vinotéka" width="63" height="42"></td>';
						}
						if($v['smartFrost']){
								$html .= '<td><img src="/images/pkt_smartfrost.gif" alt="SmartFrost" width="63" height="41"></td>';
						}
					$html .= '</tr>';
					$html .= '</table>';
					}
				$html .= '</div>';
				$html .= '<div class="posAbsolute prodPriznakyHP">';
				if($v['discount']>0){
					$html .= '<span class="blockDisplay discount">';
					$html .= '<span class="fBold white fs16 marLeft10 i-block marTop5">-'.$v['discount'].'%</span></span>';
				}
				if($v['novinka']){
					$html .= '<span class="blockDisplay  novinka"></span>';
				}
				if($v['akce']){
					$html .= '<span class="blockDisplay  akce"></span>';
				}
				if($v['enerClass'] > 2 ){
					$html .= '<span class="blockDisplay  ECO" ></span>';
				}	
				if($v['enerClass'] > 0 ){
					$html .= '<span class="blockDisplay fRight  enerClass">';
					$html .= '<span class="fBold fs16 marLeft10 greenColor1 i-block">'.$view->mVarianta->variantProperty['enerClass']['selection'][$v['enerClass']].'</span></span>';
				}
			$html .='</div>';
			$html .='</div></a>';

		return $html;
	}
	
	public function getOptions($name,$view,$ids = false,$params = false)
	{
		$select =  $this->db->select();
		$select->from(array( 'cm' => $this->_tableName), array());
		$select->join(
				array('nc' => 'NodesContents'),
				'cm.id = nc.c_id',
				array()
		);
		
		$select->join(
				array('n' => 'Nodes'),
				'n.id = nc.n_id',
				array()
		);
		 
		$select->join(
				array('var' => $this->_tableNameVariants),
				'var.id_product = cm.id',
				array($name)
		);
		
		if($params['category']){
			if(is_array($params['category'])){
				$childrenIds = $params['category'];
			} else {
				$childrenIds = $this->tree->getNodeChildrenIds($params['category'], array(), 'FOLDER');
				$childrenIds[] = $params['category'];
			}
			 
			if(count($childrenIds)){
				$w = array();
				foreach ($childrenIds as $id){
					$w[] = " cm.parent like '%$id%' ";
				}
				$select->where(implode('OR', $w));
			}
		}
		$select->group($name);
		$select->order($name.' asc');
		if(is_array($view->mVarianta->variantProperty[$name]['selection']))
		{
			$options = $this->db->fetchAll($select);
			foreach ($options as $key=>$value)
			{
				$retArray[$key] = $view->mVarianta->variantProperty[$name]['selection'][$value[$name]];
			}
		}
		else{
			foreach ($options as $o)
			{
				$retArray[$key] = $value[$name];
			}
			
		}
		return $retArray;
	}

	
	public function initSearch( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params, $count = false)    
    {
    	$select =  $this->db->select();
		$bind = array();
		
		
		if($count){    		 
    		$c =  new Zend_Db_Expr("count('*')");  
    		$select->from(array('cm' => $this->_tableName), array( $c,  $disc ));  
    	} else if($params['allcol']) { 
    		$select->from(array( 'cm' => $this->_tableName), array('*')); 		  
    	}  else { 
    		$select->from(array( 'cm' => $this->_tableName), array($disc, 'cid' => 'cm.id', 'n.id', 'n.title', 'n.path', 'n.parent', 'dateModif', 'dateCreate', 'html', 'files', 'parent', 'znacka', 'video', 'hmotnost', 'akce', 'souvisejici',  'state', 'dphQuote', 'preText')); 		  
    	}  
    	
		$select->join(   
			array('nc' => 'NodesContents'), 
        	'cm.id = nc.c_id',
        	array() 
        );
        
        $select->join(
			array('n' => 'Nodes'),
        	'n.id = nc.n_id',
        	array('n.title') 
        );
               
        $select->join(
        		array('var' => $this->_tableNameVariants),
        		'var.id_product = cm.id', 
        		array('id as variantId', 'title as variantTitle', 'EAN', 'enerClass', 'priceNakup', 'poradi', 'obrazky', 'price', 'price2', 'discount', 'realSold', 'sold', 'weight','novinka','action','bioFresh','noFrost')
        );
       
		       
        if($sort == 'title')  {
        	$sort = 'n.' . $sort;
        }       
          
        
        
        
        if($params['category']){ 
        	if(is_array($params['category'])){
	        	$childrenIds = $params['category'];
	        } else { 
	        	$childrenIds = $this->tree->getNodeChildrenIds($params['category'], array(), 'FOLDER');
	        	$childrenIds[] = $params['category'];
	        }  
	        
	        if(count($childrenIds)){
	        	$w = array();
	        	foreach ($childrenIds as $id){
	        		$w[] = " cm.parent like '%$id%' ";
	        	}
	        	$select->where(implode('OR', $w));    
	    	}  
        }  
        
        
        if($params['znacka']){   
         	$select->where('cm.znacka  = ?', $params['znacka']);   
         }	
 
         
    	 if($params['from']){   
         	$select->where('cm.dateCreate  >= ?', $params['from']);  
         }	
         if($params['onWeb']){    
         	$select->where('var.deleted  = ?', '0');   
         }	 
         
         
         if($params['akce']){
         	$select->where('akce LIKE ?', '%1%');  
         }  
         if($params['onHomePage']){
         	$select->where('onHomePage =?', '1');
         }
         if($params['onSection']){
         	$select->where('onSection =?', '1');
         }
         
         //$select->where('zobrazovat = ?', '0');
         
         
    	 
	if($params['search']){

			$params['search'] = str_replace('&quot;', '"', $params['search']);
			$select->where("`n`.`title` LIKE  '%" . $params['search']."%'");
			$select->orwhere("`var`.`title` LIKE  '%" . $params['search']."%'");
		}         
		// $select->where('dateShow <= ?', new Zend_Db_Expr('NOW()'));	
		
		if($params['state']){  
			$select->where('state = ?', $params['state']); 
		} else { 
			$select->where('state = ?', 'PUBLISHED'); 
		}
		
		$select->where('c_type = ?', $this->_tableName);   
		  
		$sortType = $sortType?$sortType:'Asc';


        $availableSorts = array(
       		'soldPrice' => 'sold',
       		'priceasc' => 'price',
       		'price' => 'price',
       		'dateCreate' => 'dateCreate',  
        );
        $availableSortTypes = array( 
        	'soldPrice' => 'DESC',
        	'priceasc' => 'ASC',
        	'price' => 'DESC',
        	'dateCreate' => 'ASC',
    	);
        
    	if(!$this->inAdmin){
    		$sortType = $availableSortTypes[$sort];
	        $sort = $availableSorts[$sort];
    	}
    	if($sort && $sortType){
	        $select->order($sort . ' ' . $sortType);
    	}
		$select->order('n.id DESC');
		//pr($select->__toString());
		$select->limit($limitCount, $limitStart);		 
		return array($select, $bind); 
    }   
    
    
    
    
    public function getProducts( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params = array())   
    {
		list($select, $bind) = $this->initSearch($sort, $sortType, $limitStart, $limitCount, $params);	
		if($select){
			return $this->db->fetchAll($select, $bind);	 
		}  	 
    }   
    
    
    public function getZnacky($id = false, $toSelect = false){
    	if(!$id){
    		if($toSelect){
    			$znacky =  $this->db->fetchAll('select * from '.$this->_tableNameZnacky.' order by nazev ASC');
    			$retZnacky[0] = ' nemá '; 
    			foreach ($znacky as $v)
    			{
    					$retZnacky[$v['id']] = $v['nazev'];
    			}
    			return $retZnacky; 
    		}
    		else{
    			return $this->db->fetchAll('select * from '.$this->_tableNameZnacky.' order by nazev ASC');
    		}
    	}
    	else{
    			return $this->db->fetchRow('select * from '.$this->_tableNameZnacky.' where id= ? order by nazev ASC',$id);
    		}
    	
    }
      
    
    public function getProductsCout( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params = array())   
    {   
    	list($select, $bind) = $this->initSearch($sort, $sortType, $limitStart, $limitCount, $params, true); 
    	if($select)		
		return $this->db->fetchOne($select, $bind);	  	  
    }   
    
    function  getArticlesAsNodes($tree, $articles){  
    	$nodes = array();
    	foreach ($articles as $article){ 
    		$nodes[] = $tree->getNodeById($article['id']);
    	}
    	return $nodes; 
    }
    
    function addToCache($view)
    {
    	$this->db->delete($this->_tableNameCache);
    	$params['showFirstVariant'] = true;
    	$allProducts = $this->getProducts('title','Asc',0,20000,$params);
    	$menuitem = $view->tree->getNodeById(3801);
    	$leftmenu = helper_FrontEnd::checkChildren($menuitem->getChildren('FOLDER'));
    	foreach ($leftmenu as $value) {
    		$data = array(
    				'nodeId' => $value->nodeId,
    				'title' => $value->title,
    				'path' => $value->path,
    		);
    		$this->db->insert($this->_tableNameCache,$data);
    	}
    	foreach ($allProducts as $value) {
    		$node = $view->tree->getNodeById($value['id']);
    		$content = $node->getPublishedContent();
    		$variant = $view->mVarianta->getVariantsByIdProduct($content->id,true);
    		$photos = $view->mVarianta->getResizedPhotos($variant['obrazky']);
    		$p = helper_FrontEnd::getFirstPhoto($photos,'pMinic2' , false);
    		$data = array(
    				'nodeId' => $value['id'],
    				'title' => $value['title'],
    				'path' => $value['path'],
    				'photos' => $p['path'],
    				'isProduct' => 1,
    				'price' => helper_FrontEnd::price(round($variant['price'])).' Kč'
    		);
    		if($p['path']){
    			$this->db->insert($this->_tableNameCache,$data);
    		}
    	}
    	 
    	$where = $this->db->quoteInto ("`parent` LIKE  '%68792%'");
 
    	$this->db->update($this->_tableName,$dat,$where);
    }
    
    function updateCache($view,$contentId){
    	$nodeId = $db->fetchOne('SELECT n_id FROM NodesContents WHERE c_id = ' . $contentId);
    	$node = $view->tree->getNodeById($value['id']);
		$content = $node->getPublishedContent();
		$price = $content->getPriceVariant();
    	$p = helper_FrontEnd::getPhoto('photos', $content, $content->fotoCropMini2Name);			
   			$data = array(
   				'nodeId' => $value['id'],
   				'title' => $value['title'],
   				'path' => $value['path'],
   				'photos' => $p['path'],
   				'isProduct' => 1,
   				'price' => helper_FrontEnd::price($price[0]).' Kč'
   				);
   			$where = $this->db->quoteInto('nodeId = ?', $nodeId);
			$this->db->update(
				$this->_tableNameCache,
				$data,
				$where);
   	}
   	
   	public function showSelectedOptions($inputGet,$showText = false)
   	{
   		$ret = '';
   		foreach ($this->filterName as $key=>$value) {
   			if($inputGet->$key)
   			{
   				if($showText)
   				{
   					if(is_array($inputGet->$key)){
   						$ret .= $value.' - ';
   					}
   					else{
   						if($value == 'Palivo' || $value == 'palivo'){
   	
   							$at =  $this->palivo[$inputGet->$key];
   						}
   						elseif($key == 'vyvod' || $key == 'Vyvod')
   						{
   							$at =  $this->vyvod[$inputGet->$key];
   						}
   						else{
   							$at = $inputGet->$key;
   						}
   						$ret .= $value.' - '.$at.' '.$this->filterMetric[$key];;
   					}
   				}
   				else{
   					$ret .= '<span class="item"><span class="name">'.$value.': </span>';
   				}
   				 
   				$op = array();
   				foreach ($inputGet->$key as $val) {
   					if($showText){
   						if($key == 'palivo'){
   							$op[] = $this->palivo[$val];
   						}
   						elseif($key == 'vyvod')
   						{
   							$op[] = $this->vyvod[$val];
   						}
   						else
   						{
   							$op[] = $val.''.$this->filterMetric[$key];
   						}
   					}
   					else{
   						if($key == 'palivo'){
   							$op[] = '<a href="#" onclick="removeOption(\''.$key.'[]\','.$val.')">'.$this->palivo[$val].'  X </a>';
   						}
   						elseif($key == 'vyvod')
   						{
   							$op[] = '<a href="#" onclick="removeOption(\''.$key.'[]\','.$val.')">'.$this->vyvod[$val].' X </a>';
   						}
   						else
   						{
   							$op[] = '<a href="#" onclick="removeOption(\''.$key.'[]\','.$val.')">'.$val.'  '.$this->filterMetric[$key].' X </a>';
   						}
   					}
   				}
   				$ret .= implode(',', $op);
   				if(!$showText)
   				{
   	
   					$ret .= '</span>';
   				}
   				else{
   					$ret.=' | ';
   				}
   			}
   	
   		}
   		if($showText){
   			return substr($ret,0,-3);
   		}
   	
   		return $ret;
   	}
   	
   	public function showSlider($name,$min,$max,$inc,$limitRange = false)
   	{
   		$text ='<div class="slider_outer" >
  					<div class="slide_container" >
    					<div id="slider_minmax_min'.$name.'"  class="floatl">'.$min.'</div>
    					<div class="slider_gutter" id="minmax_slider" >
      					<div id="slider_minmax_gutter_l" class="slider_gutter_item iconsprite_controls"></div>
      					<div id="slider_minmax_gutter_m'.$inc.'" class="slider_gutter_item gutter iconsprite_controls">
				       <img id="slider_bkg_img'.$name.'" src="/images/bkg_slider.gif"/>
        				<div id="slider_minmax_minKnobA'.$name.'" class="knob"></div>
        			<div id="slider_minmax_maxKnobA'.$name.'" class="knob"></div>
      				</div>
      			<div id="slider_minmax_gutter_r" class="slider_gutter_item iconsprite_controls"> </div>
    			</div>
    			<div id="slider_minmax_max'.$name.'" class="floatl">'.$min.'</div>
    			<div class="clearfix"></div>
  				</div>
		</div>';
   		if($limitRange){
   			$text .='<span id="rangeMin'.$name.'" rel="'.$limitRange['min'.$name].'"></span>
			<span id="rangeMax'.$name.'" rel="'.$limitRange['max'.$name].'"></span>';
   		}
   		return $text;
   	}
   	
   	public function checkGetParams($url,$param,$val)
   	{
   		// kvůli lomenu
   		$val = str_replace('/', 'a', $val);
   		if(is_numeric(strpos($url,$param)) && is_numeric(strpos($url,'?')) )
   		{
   			$arrUrl = explode('&', $url);
   			$newArrUrl = array();
   			foreach ($arrUrl as $value) {
   				if(is_numeric(strpos($value,$param))){
   					$arrPar = explode('=', $value);
   					$newArrUrl[]=$arrPar[0].'='.$val;
   				}
   				else{
   					$newArrUrl[]=$value;
   				}
   			}
   			$url = implode('&', $newArrUrl);
   		}
   		elseif(is_numeric(strpos($url,'?')))
   		{
   			$url.='&'.$param.'='.$val;
   		}
   		else{
   			$url.='?'.$param.'='.$val;
   		}
   		 
   		return $url;
   	}
   	
   	function diakritika($text){
   		$prevodni_tabulka = Array(
   				'ä'=>'a',
   				'Ä'=>'A',
   				'á'=>'a',
   				'Á'=>'A',
   				'à'=>'a',
   				'À'=>'A',
   				'ã'=>'a',
   				'Ã'=>'A',
   				'â'=>'a',
   				'Â'=>'A',
   				'č'=>'c',
   				'Č'=>'C',
   				'ć'=>'c',
   				'Ć'=>'C',
   				'ď'=>'d',
   				'Ď'=>'D',
   				'ě'=>'e',
   				'Ě'=>'E',
   				'é'=>'e',
   				'É'=>'E',
   				'ë'=>'e',
   				'Ë'=>'E',
   				'è'=>'e',
   				'È'=>'E',
   				'ê'=>'e',
   				'Ê'=>'E',
   				'í'=>'i',
   				'Í'=>'I',
   				'ï'=>'i',
   				'Ï'=>'I',
   				'ì'=>'i',
   				'Ì'=>'I',
   				'î'=>'i',
   				'Î'=>'I',
   				'ľ'=>'l',
   				'Ľ'=>'L',
   				'ĺ'=>'l',
   				'Ĺ'=>'L',
   				'ń'=>'n',
   				'Ń'=>'N',
   				'ň'=>'n',
   				'Ň'=>'N',
   				'ñ'=>'n',
   				'Ñ'=>'N',
   				'ó'=>'o',
   				'Ó'=>'O',
   				'ö'=>'o',
   				'Ö'=>'O',
   				'ô'=>'o',
   				'Ô'=>'O',
   				'ò'=>'o',
   				'Ò'=>'O',
   				'õ'=>'o',
   				'Õ'=>'O',
   				'ő'=>'o',
   				'Ő'=>'O',
   				'ř'=>'r',
   				'Ř'=>'R',
   				'ŕ'=>'r',
   				'Ŕ'=>'R',
   				'š'=>'s',
   				'Š'=>'S',
   				'ś'=>'s',
   				'Ś'=>'S',
   				'ť'=>'t',
   				'Ť'=>'T',
   				'ú'=>'u',
   				'Ú'=>'U',
   				'ů'=>'u',
   				'Ů'=>'U',
   				'ü'=>'u',
   				'Ü'=>'U',
   				'ù'=>'u',
   				'Ù'=>'U',
   				'ũ'=>'u',
   				'Ũ'=>'U',
   				'û'=>'u',
   				'Û'=>'U',
   				'ý'=>'y',
   				'Ý'=>'Y',
   				'ž'=>'z',
   				'Ž'=>'Z',
   				'ź'=>'z',
   				'Ź'=>'Z'
   		);
   	
   		$ret = strtr($text, $prevodni_tabulka);
   		return $ret;
   	}
   	
    
   	
   function addToHistorySearch($view)
   {
   		$count = $this->db->fetchOne("SELECT count FROM ".$this->_tableHistorySearch." where keyword =?", $view->inputGet->search);
   		if($count){
			$count++;
   			$where = $this->db->quoteInto('keyword = ?', $view->inputGet->search);
   			$data = array(
   				'count' => $count);
   			$this->db->update(
				$this->_tableHistorySearch,
				$data,
				$where);
   		}
   		else{
   			$data = array(
   				'count' => 1,
   				'keyword' => $view->inputGet->search);
   			$this->db->insert($this->_tableHistorySearch,$data);
   		}
   }	
   
   public function getProductByContentId($contentId)
   {
   		return $this->db->fetchRow("SELECT * FROM ".$this->_tableName." where id =?", $contentId); 
   }
   
  
   
   function getLastSearchWords($view,$count = 2,$limit = 5)
   {
   		$select =  $this->db->select();
   		$select->from($this->_tableHistorySearch);
   		$select->where('count > ?', $count);
   		$select->order('count asc');
   		$select->order('lastSearch asc');
   		$select->limit($limit);  
   		$prod = $this->db->fetchAll($select);
   		if($prod){
   			foreach ($prod as $value) {
   				$text .= '<li><a href="'.$view->searchUrl.'?search='.$value['keyword'].'">Vyhledáno: '.$value['keyword'].'</a></li>';
   			}
   			return $text;
   			}   		
   }
    

     
  
    public function searchProduct(){
    	return $this->db->fetchAll("SELECT * from `" . $this->_tableNameCache);
    }
    
    function getSeachableWords($view){
    	
   	 if($view->cache){ 
			$ident = $view->cache->identificator . "zwords_" . str_replace('-', '_', $this->eshopType);  
			 		
			if($view->cache->test($ident) === false ){
				
			} else { 
				//return $view->cache->load($ident);     
			}  
		} 
				
		$all = $this->searchProduct();
			 
		$words = array(); 
		
						// DOPSAT
//		$eShop  = helper_FrontEnd::checkChildren($view->tree->getNodeChildren(3801, 'FOLDER'), 1);
//		foreach ($eShop as $value) {
//			$conEshop = $value->getPublishedContent();
//			$conEshop->getPropertyByName('pathToTemplate')->value=='Products';
//			{			
//				$textImage = '<img src="'.$img.'"/><span class="searchText">'.$value->title.'</span><br><span class="searchPrice">'.helper_FrontEnd::price($price).' Kč</span>';
//				$words= $this->addWord($words, $textImage);
//			}
//			 
//		}
		
		
		foreach ($all  as $text){
			if($text['isProduct']){
				$tempText = $text['price'];}
			else{
				$tempText = '(Kategorie)';
				$text['photos'] = '/images/icoFolderBig.png';
			};
            $textImage = '<img src="'.$text['photos'].'"/><span class="searchText">'.$text['title'].'</span><br><span class="searchPrice">'.$tempText.'</span>';
           //$textImage = $text['title'];
			$words= $this->addWord($words, $textImage); 
		} 
		
		if($view->cache){ 
			$view->cache->save($words, $ident, array(), 95);  
		}  
		
		return $words; 
		// pr($texts); 	  
	}
	 
	function addWord($words, $w){
		$w = trim($w); 
		if(!in_array($w, $words)){ 
			$words[] = $w;
		} 
		return $words;
	}
 
  

	
}