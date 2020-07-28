<?php

class module_Products
{   
	private $_filter = array('color','chlazeni','type','pocZonFilr');
	
	
	private $_filterNew = array(
			'color' => array('metric' => '','type'=>'checkBox','pos' => 1),
			'chlazeni' => array('metric' => '','type'=>'checkBox','pos' => 2),
			'type' => array('metric' => '','type'=>'checkBox','pos' => 3),
			'pocZonFilr' => array('metric' => '','type'=>'checkBox','pos' => 4),
			'znacka' => array('metric' => '','type'=>'checkBox','pos' => 5),			
			);
	
		private $_colors = array(
				1 => '#FFF', //bíla
				2 => '#000', // èerná
				3 => '#E0DFDB', // nerez
				4 => '#E6E8FA', //Støibrná
				5 => '#50021B', //bordeux
				6 => '#E2725B', //Terra (hnìdé
				7 => '#1E1E1E' //BlackSteel - (èerná ocel)'
				);
		
	
	
	function __construct($domain = ''){
		$this->db =  Zend_Registry::getInstance()->db;
		$this->tree =  Zend_Registry::getInstance()->tree;
		$this->_tableName = 'content_Product';
		$this->_tableNameVariants = 'module_eshop_variants';
		$this->_tableHistorySearch ='historySearch';
		$this->_tableNameOver = 'content_OverviewProducts'; 
		$this->_tableNameCache = 'cacheSearch';
		$this->_tableNameOption = 'module_eshop_variants_options';
		$this->_tableNameZnacky = 'module_eshop_Znacky';
		$this->_tableNameZnackyRady = 'module_eshop_ZnackyRady';
	} 
		
	
	function resize($newWidth, $targetFile, $originalFile) {

    $info = getimagesize($originalFile);
	if($info[0]>0){
	if($info[0]<$newWidth)
	{
		$newWidth = $info[0];
	}
	e($newWidth);
    $mime = $info['mime'];

    switch ($mime) {
            case 'image/jpeg':
                    $image_create_func = 'imagecreatefromjpeg';
                    $image_save_func = 'imagejpeg';
                    $new_image_ext = 'jpg';
                    break;

            case 'image/png':
                    $image_create_func = 'imagecreatefrompng';
                    $image_save_func = 'imagepng';
                    $new_image_ext = 'png';
                    break;

            case 'image/gif':
                    $image_create_func = 'imagecreatefromgif';
                    $image_save_func = 'imagegif';
                    $new_image_ext = 'gif';
                    break;

            default: 
                    throw Exception('Unknown image type.');
    }

    $img = $image_create_func($originalFile);
    list($width, $height) = getimagesize($originalFile);

    $newHeight = ($height / $width) * $newWidth;
    $tmp = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    if (file_exists($targetFile)) {
            unlink($targetFile);
    }
    $image_save_func($tmp, "$targetFile.$new_image_ext");
    }
}
	
	public function fixImg()
	{
		$options = array( 
	            'folderId' => 2,   
	            'upload_dir' => '/var/www/hosting/l/luxusnivinoteky.cz/web/www/public/data/editor/wtb-4212_01.jpg',  
				'upload_url' => '/data/sharedfiles/',  
	            'user_dirs' => false,     
	            'mkdir_mode' => 0755, 
	            'param_name' => files1,   
	            'accept_file_types' => '/.+$/i', 
	            'max_width' => 2500,
	            'max_height' => 2500,  
	            'min_width' => 1,
	            'min_height' => 1, 
	            'show_medium' => $resize, 
				'extra' => serialize($_POST)
		);
	        
		//	$upload_handler = new UploadHandler($options);
			$file = '/var/www/hosting/l/luxusnivinoteky.cz/web/www/public';
			$fileNew ='/var/www/hosting/l/luxusnivinoteky.cz/web/www/public/data/sharedfiles/thumb/';
			$fileNewll = '/data/sharedfiles/medium/';
			//$this->scaleImageFileToBlob($file) ;
			
			
			//pr($this->resize(869,$fileNew,$file));
			//die;
		
			//ImageJpeg($oldImage, $base . $asJPG,100); 
			//die;
			 // 3880    
		$all = $this->db->fetchALL('select html,id from '.$this->_tableName);
		foreach ($all as $value) {
			$html = $value['html'];
			//$d = array();
			$d['html'] = str_replace('/data/editor/', '/data/sharedfiles/medium/', $html);
			//preg_match_all( '@src="([^"]+)"@' , $html, $match );
			//pr($match[1]);
			//die;
			//pr($d);
			//pr($match[1]);
			$where = $this->db->quoteInto('id =?', $value['id']);
			// pr($html);
			// pr($where);
				// e($d['html']);
			//$this->db->update($this->_tableName,$d,$where);
			//pr($match[1]);
			foreach ($match[1] as $val) {
				
			//	pr($val);
			
				
				$t = explode('/', $val);

				if(is_numeric($t[3]))
				{
					unset($t[3]);
					pr($value['id']);
					$val = implode('/', $t);
		
				}
				$name = end($t);
				$new=  str_replace('.jpg','', $fileNew.$name);
				//pr($new);
				//pr($new);
				//pr($new);
			
				$f=$file.$val;
				
			//	pr($f);
				//$upload_handler->importImage($new, $t[3]);
				//$this->resize(200,$new,$f);
				
				//die;
			
			}
			//pr($value['id']);
		}
		return $all;
	}
	
	public function renderNamesOption($view,$start = 1)
	{
		$returnText = '';
 		$counter = $start;
 		foreach ($this->_filterNew as $key=>$item)
 		{
 			if(!$item['pos']){
 				continue;
 			}
			$selected = $view->inputGet->option==$item['pos']?"selected":"";
			if($key != 'znacka'){
 				$name = $item['name'] ? $item['name'] : $view->mVarianta->variantProperty[$key]['cMap'];
			}
			else{
 				$name = 'Znaèky';
			}
 			$returnText .= '<li>'.$name.'</li>';
 		}

		return $returnText;
	}
	
	public function showSimpleOption($view,$params,$name,$text = false)
	{
		// pro všechny možnosti nastetuju params
		
		$newParams['onWeb'] = 1;
    	$newParams['showFirstVariant'] = 1;
    	$newParams['category'] = $params['category'];
    	$newParams['notGet'] = true;
		$newParams['vyskaKey'] = false; 
		if($name == 'vyska'){
			foreach ($this->_rozmeryRange as $key => $value) {
				$newParams['vyskaKey'] = $key;
				$newParams['vyskaText'] = $value;
				$vyska = $this->getOptionsDB($view, $name, $newParams);
				unset($vyska[0]['vyska']);
				$v['vyska'] = $value;
				$v['pocet'] = reset($vyska[0]);
				if($v['pocet'] >0)
					$allParams[] = $v;
			}
		
		}else{
			$newParams['vyskaKey'] = false; 
		
    		$allParams = $this->getOptionsDB($view, $name, $newParams);
		}
		
		//pr($options);

 		if($allParams[0]){
 			if($name == 'vyska'){
			foreach ($this->_rozmeryRange as $key => $value) {
				$params['vyskaKey'] = $key;
				$params['vyskaText'] = $value;
				$vyska = $this->getOptionsDB($view, $name, $params);
				unset($vyska[0]['vyska']);
				$v['vyska'] = $value;
				$v['pocet'] = reset($vyska[0]);
				$options[] = $v;
			}
			}
			else{
				$params['vyskaKey'] = false; 
				$params['vyskaText'] = false;
				//pr($name);
				$options = $this->getOptionsDB($view, $name, $params);
			}
			$itemFilter = $this->_filterNew[$name];
			$returnText .= $this->getHtmlTextOption($view,$options,$name,$itemFilter,$allParams,$text);
			//pr($returnText);
			return $returnText;
 		}
	}

	public function initSearchOption($view, $name,$params = array(), $count = false)
	{
		$getView = clone $view->inputGet;
		$select =  $this->db->select();
		if($name != 'znacka'){
			$select->from(array( 'cm' => $this->_tableName), array());
		}
		else{
			$select->from(array( 'cm' => $this->_tableName), array('znacka', 'count(znacka) as pocet'));
		}
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
		
		if($name == 'priceMin')
		{
			$ke = str_replace('price', '', $name);
			$select->join(
					array('var' => $this->_tableNameVariants),
					'var.id_product = cm.id',
					array("min(price) as priceMinLimit")
			);
			$name = 'price';
			$noGroup = true;
		}
		elseif($name == 'priceMax')
		{
			$noGroup = true;
			$ke = str_replace('Max', '', $name);
			$select->join(
					array('var' => $this->_tableNameVariants),
					'var.id_product = cm.id',
					array("max(price) as priceMaxLimit")
			);
			$name = 'price';
			
		}
		elseif($name == 'vyska' && $params['vyskaText'])
		{
			$select->join(
					array('var' => $this->_tableNameVariants),
					'var.id_product = cm.id',
					array("vyska, count(var.".$name.") as ".$params['vyskaText'])
			);
		}
		elseif($name == 'znacka')
		{
			$select->join(
					array('var' => $this->_tableNameVariants),
					'var.id_product = cm.id',
					array()
			);
		}
		else{

			$select->join(
					array('var' => $this->_tableNameVariants),
					'var.id_product = cm.id',
					array($name,"count(var.".$name.") as pocet")
			);
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
		if(!$params['notGet']){

		foreach ($this->_filterNew as $k => $fil)
		{
		
			$k = str_replace('Min', '', $k);
			$k = str_replace('Max', '', $k);
			
			if($getView->{$k} > 0 && $name != $k && !is_array($getView->{$k}))
			{
				$select->where('var.'.$k. ' = ?', $getView->{$k});
				
			}
			
			// je to pole
			
			elseif($getView->{$k} && $name != $k)
			{
				
					
				{
					foreach ($getView->{$k} as $it)
					{
						
						if($k == 'sirka')
						{
							$max = max($getView->{$k});
							$select->where($k."  >= ?", $max);
						}
						elseif($k == 'vyska'){
				
							switch ($it) {
							case '1':
							$vysks[] =  '(var.vyska  < 88)';
								break;
							case '2':
							$vysks[] = '(var.vyska > 88 and var.vyska < 145 )';
							break;
							case '3':
							$vysks[] = '(var.vyska > 145 and var.vyska < 165)';
							break;
							case '4':
							$vysks[] = '(var.vyska > 165 and var.vyska < 185)';
							break;
							case '5':
							$vysks[] = '(var.vyska > 185 and var.vyska < 210)';
						break;
							}
						
						$select->where(implode(' or ', $vysks));
						}
						elseif($k == 'sirka'){
						foreach ($inputGet->{$k} as $e) {
							$tt1[] =  'sirka = '.$e;
						}
							$sel = '('.implode(' or ', $tt1).')';
							$select->where($sel);
						}
						else
							{
								$nn = 'var.'.$k;
								if($name = 'znacka')
								{
									$nn = 'cm.znacka';
								}
								$select->where($nn.' in ('.implode(',', $getView->{$k}).')');
							}
					}
				}
					
				unset($getView->{$k});
			}
		
		}
		}
		
		if($view->mVarianta->variantProperty[$name]['type']=='Checkbox' && $params['notGet'])
		{
			$select->where('var.'.$name. ' = ?', '1');
		}
		
		if($params['vyskaKey'])
		{
			switch ($params['vyskaKey']) {
				case '1':
					$select->where('var.vyska  < ?', 88 );
				break;
				case '2':
					$select->where('var.vyska > 88 and var.vyska < 145');
				break;
				case '3':
					$select->where('var.vyska > 145 and var.vyska < 165');
				break;
				case '4':
					$select->where('var.vyska > 165 and var.vyska < 185');
				break;
				case '5':
					$select->where('var.vyska > 185 and var.vyska < 210');
				break;
			}
		}
		
		
		$select->where("skladem = '1' or skladem = '4'");
		//$select->where('vyska > ?', '0');
		if(!$noGroup)
		{
			if(!$params['vyskaKey']){
				$select->group($name);
			}
		}	
		if($getView->ajax){
			//
		}
		if($params['vyskaKey']){
			
		}
		return array($select);
	
	}

		public function showSelectedOptionsFilter($view,$isH1 = FALSE,$isPageTitle = false)
   	{
   		$inputGet = $view->inputGet;
   		$html = array();
   		$ret = '';
   		foreach ($this->_filter as $value) {
   		
   			$input = $inputGet->$value;
   			if(is_array($input) && $input[0])
   			{
   			
   			
   				$item = $inputGet->$value;
   				$html[] = ($view->mVarianta->variantProperty[$value]['cMap']).'  ';
   				$htmlItems = array();
   				foreach ($input as $item)
   				{
   					
  					$metric = $this->_filterNew[$value]['metric'] ? ' '.$this->_filterNew[$value]['metric']: '';
   					if($view->mVarianta->variantProperty[$value]['selection'])
   					{
   						$htmlItems[] = '<a href="#" class="filterLinkDel" onclick="removeOption(\''.$value.''.$item.'\')">'.($view->mVarianta->variantProperty[$value]['selection'][$item]).'<span></span></a>';
   						
   					}
   					elseif($view->mVarianta->variantProperty[$value]['type']=='Checkbox')
   					{
   						
   						$htmlItems[] = '<a href="#" class="filterLinkDel" onclick="removeOption(\''.$value.''.$item.'\')">'.($view->mVarianta->variantProperty[$value]['cMap']).''.$this->_filterNew[$value]['metric'].'<span></span></a>';
   					}
   					else{
//    						e($this->_filterNew[$value]['metric']);
//    						e($value);
							$it =$ittext = $item;
						if('vyska' == $value){
   							$it = $item;
							$ittext = $this->_rozmeryRange[$item];
   						}
   						$htmlItems[] = '<a href="#" class="filterLinkDel" onclick="removeOption(\''.$value.''.$it.'\')">'.$ittext.''.$metric.'<span></span></a>';
   					}
   				}
   				
   				$html[] = implode(' ', $htmlItems);
   				
   			}
   			elseif($input>0)
   			{
   				
   				if($view->mVarianta->variantProperty[$value]['selection'])
   					{
   						$html[] = strtolower($view->mVarianta->variantProperty[$value]['cMap']).'  '.($view->mVarianta->variantProperty[$value]['selection'][$input]);
   					}
   					elseif($view->mVarianta->variantProperty[$value]['type']=='Checkbox')
   					{
   						$html[] = '<a href="#" class="filterLinkDel" onclick="removeOption(\''.$value.'1\')">'.$view->mVarianta->variantProperty[$value]['cMap'].'<span></span></a>';
   					}
   					else{
   					
   						$htmlItems[] = $item;
   					}
	
   			}
   		}
   		$htmlItems = array();
   		// kontrola ma min a max
   		
   		$getArray = (array)$inputGet;
   		$stejnyNazev = '';
		///pr($inputGet);
   		foreach ($getArray as $arK => $va)
   		{
   			$nazev = str_replace('Min', '', $arK);
   			$nazev = str_replace('Max', '', $nazev);
   			if($getArray[$nazev.'Min']>0 && $getArray[$nazev.'Max']>0)
   			{
   				$notSet = true;
   			}
   			if($va > 0 && (strpos($arK, 'Min') || strpos($arK, 'Max')) && strpos($arK, 'price'))
   			{
   				if(($notSet && count($htmlItems) == 0) || (!$notSet)){	
   					$htmlItems[] = $view->mVarianta->variantProperty[$nazev]['cMap'].'  <a href="#" onclick="removeOption(\''.$arK.'\')">'.$va.'</a>';
   				}
   				/// už je aby se nasetovali nazev když neni notset
   				else{
   					$htmlItems[] = '<a href="#" class="filterLinkDel" onclick="removeOption(\''.$arK.'\')">'.$va.'<span></span></a>';
   				} 
   			
   				if($notSet && count($htmlItems) == 2)
   				{
   					
   					$html[] = implode(' ', $htmlItems);
   					$htmlItems = array();
   				}
   				elseif(!$notSet)
   				{
   					//pr($htmlItems);
   					$html[] = implode(' ', $htmlItems);
   					$htmlItems = array();
   				}
   			}
   			
   			
   		//	pr($va );
   		}
   	//	pr($getArray);
   		if($isH1 || $isPageTitle)
   		{  			
   		
   			$first = $sec =0;
   			foreach ($html as $i)
   			{
   				$first++;
   				$sec++;
   				if ($first/2 == floor($first/2)) {
   					$text.= $i.', ';
   				}
   				else{
   					
   					$text.= $i.' - ';
   				}
   				
   			}
   			$text = strip_tags($text);
   			$text = substr($text,0,-2);
   			if($isH1 && $text){
   				$ret= ' <span class="grey fs16">('.$text.')</span>';
   			}
   			elseif($text){
   				$ret= $text;
   			}
   		}
   		else
   		{
   			$text = implode('  ',$html);
   			$ret = $text;
   		}
   		return $ret;
   	}

	public function setZnacka()
	{
		$all = $this->getProducts($view);
		return $all;
	}
	

public function getOptionsDB($view,$name, $params = array(), $count = false,$one = false)
	{
		list($select, $bind) = $this->initSearchOption($view,$name, $params);
		if($select){
			if($one)
			{
				return $this->db->fetchOne($select, $bind);
			}
			else{
				return $this->db->fetchAll($select, $bind);
			}
		}
	}
	
	
	
	private function setCustomOptions($view){
	
		$color .= '<div>';
		$color .= '<div class="colOption bigOption">';
			$color .= $this->showSimpleOption($view,$view->params,'color');
			$color .= '</div>';
		$color .= '</div>';
		
		$chlazeni .= '<div>';
		$chlazeni .= '<div class="colOption">';
			$chlazeni .= $this->showSimpleOption($view,$view->params,'chlazeni');
			$chlazeni .= '</div>';
		$chlazeni .= '</div>';
		
		$type .= '<div>';
		$type .= '<div class="colOption">';
			$type .= $this->showSimpleOption($view,$view->params,'type');
			$type .= '</div>';
		$type .= '</div>';
		
		$pocZon .= '<div>';
		$pocZon .= '<div class="colOption">';
			$pocZon .= $this->showSimpleOption($view,$view->params,'pocZonFilr');
			$pocZon .= '</div>';
		$pocZon .= '</div>';	
		
		$znacka .= '<div>';
		$znacka .= '<div class="colOption">';
		$znacka .= $this->showSimpleOption($view,$view->params,'znacka');
		$znacka .= '</div>';
		$znacka .= '</div>';
			
		$arr[1] = $color;
		$arr[2] = $chlazeni;
		$arr[3] = $type;
		$arr[4] = $pocZon;
		$arr[5] = $znacka;
		return $arr;
	}
	
	public function showOptions($view,$params)
	{
		$allOptions = $this->setCustomOptions($view);
		return implode('', $allOptions);
	}


private function getHtmlTextOption($view,$options,$filterName,$filterProp,$allOptions,$text =false)
	{
	
		$newArray = array();
		// abych nemusel sahat dotazem pro jednotlivé count a jestli ho mám dát disable
	
		foreach ($options as $ite)
		{
			$newArray[$ite[$filterName]] = $ite['pocet'];			
		}
		switch ($filterProp['type']) {
			case 'checkBox':
				$inc = 0;
// 				pr($newArray);
				foreach ($allOptions as $item){
						if(!$item[$filterName])
						{
							continue;
						}
						$disable = '';
						//pr($item[$filterName]);
						$disable = ' disabled ';
						
						$count = 0;
						if($newArray[$item[$filterName]])
						{
							$count = $newArray[$item[$filterName]];
							$disable = '';
						}
						//e($disable);
						$spanOpt = 'optNormal';
						$inputClass=$colorSpan = $colorInput = $cheched = $option = '';
						
						if($filterName == 'color')
						{
							$spanOpt = 'optBig';
							$inputClass=" colorInput ";
							$colorInput = 'class="colorInput"';
							$colorSpan = '<span class="colorFilter" style="background-color:'.$this->_colors[$item[$filterName]].'"></span>';
						}	
						
						$returnText .= '<span class="posRel" >';
						if($view->mVarianta->variantProperty[$filterName]['selection'])
						{
							$option =  $view->mVarianta->variantProperty[$filterName]['selection'][$item[$filterName]];
						}
				//	pr($item);
					//	e($option);
						$option = $option ? $option : $item[$filterName];
					//	e($item[$filterName]);
// 						e($item[$filterName]);
// 						e($filterName);
	//					e( $view->inputGet->{$filterName});
						if($filterName == 'vyska')
						{
							$item[$filterName]  = $this->_rozmeryRangeVal[$item[$filterName]];	
						}
						if(in_array($item[$filterName], $view->inputGet->{$filterName}))
						{
	
							$cheched = 'checked="checked"';	
						}
				
						$returnText .= '<input '.$disable.' class="'.$disable.''.$inputClass.'"  onclick="sendFilter(false)" '.$cheched.' id="'.$filterName.''.$item[$filterName].'"  type="'.$filterProp['type'].'" '.$colorInput.' name="'.$filterName.'[]" value="'.$item[$filterName].'">';
						if($filterName =='znacka')
						{
							$znacka = $this->getZnacky($option);
							$option = $znacka['nazev'];
						}
						$returnText .= '<span class="'.$spanOpt.''.$disable.'" onclick="selectOption(\''.$filterName.''.$item[$filterName].'\');">
								<a href="Javascript://">'.$text.''.$colorSpan.''.$option.' '.$filterProp['metric'].'</a>
										<span class="minCount">('.$count.')</span></span>';
						$returnText .='</span>';
						$inc++;
						if($inc%2 == 0 && $filterName == 'color')
						{
							$returnText .= '<br />';
						}
				}
			break;
			
			case 'checkBoxOneOption':
// 				pr($allOptions);
// 				pr($newArray);
				$option =  $view->mVarianta->variantProperty[$filterName]['cMap'];
				$returnText .= '<span class="posRel" >';
				$optionStyle = 'optNormal';
				$img = $color = '';
				if( $view->inputGet->{$filterName})
				{
					
				
					$cheched = 'checked="checked"';
				}
				if($filterProp['image'] != ''){
					$optionStyle = 'optBig';
					$img = '<img src="'.$filterProp['image'].'" width="50" alt="'.$filterName.'">';
				}
					$disable = ' disabled ';
					$count = 0;
// 					pr($newArray);
// 					pr($allOptions[0][$filterName]);
					if($newArray[$allOptions[0][$filterName]])
					{
						$count = $newArray[$allOptions[0][$filterName]]; 
						//e($count);
						$disable = '';
						
					}
					$returnText .= '';
					 
					$returnText .= '<table><tr><td>		
										<input onclick="sendFilter(false)" '.$disable.' class="topp '.$disable.'" type="checkBox" id="'.$filterName.'1" '.$cheched.' name="'.$filterName.'" value="1"></td>
									<td><span class="'.$optionStyle.''.$disable.'" onclick="selectOption(\''.$filterName.'1\');">'.$img.'
									<a class="eds" href="Javascript://">'.$option.' <span class="minCount">('.$count.')</span></a></span></span></td>
									</tr>
									</table>';
				$returnText .='';
				$returnText .='</span>';
			break;
			case 'between':
				$returnText .= 'od '.'<input type="text" id="'.$filterName.'Min" name="'.$filterName.'Min" value="'.$view->inputGet->{$filterName.'Min'}.'">';
				$returnText .= 'do '.'<input type="text" id="'.$filterName.'Max" name="'.$filterName.'Max" value="'.$view->inputGet->{$filterName.'Max'}.'">';
			break;
		}
		
		return $returnText;
	}
	
	
	   	public function checkGetParams($url,$param,$val)
   	{
   		// kvùli lomenu
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
	
	
	function addToCache()
    {
    	$mVarianta = new module_Varianta();
    	$this->db->delete($this->_tableNameCache);
    	$params['showFirstVariant'] = true;
    	$allProducts = $this->getProducts('title','Asc',0,20000,$params);
    	$menuitem = $this->tree->getNodeById(3801);
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
    		$node = $this->tree->getNodeById($value['id']);
    		$content = $node->getPublishedContent();
    		$variant = $mVarianta->getVariantsByIdProduct($content->id,true);
    		$photos = $mVarianta->getResizedPhotos($variant['obrazky']);
    		$p = helper_FrontEnd::getFirstPhoto($photos,'pMinic2' , false);
    		$data = array(
    				'nodeId' => $value['id'],
    				'title' => $value['title'],
    				'path' => $value['path'],
    				'photos' => $p['path'],
    				'isProduct' => 1,
    				'price' => helper_FrontEnd::price(round($variant['price'])).' Kè'
    		);
    		if($p['path']){
    			$this->db->insert($this->_tableNameCache,$data);
    		}
    	}
    	 
    	
    }
	
	private function getNextPosition()
	{
		$position = $this->db->fetchOne("select max(poradi) from ".$this->_tableName) + 1;
		return $position > 0 ? $position : 1;
		
	}	
	
	
	public function setCat()
	{
		$allProd = $this->getProducts(false,false, false, false);
		foreach ($allProd as $key => $value) {
			if(is_numeric(strpos($value['title'], 'PHILCO')))
			{
				$data['parent'] = 74532;
				$where = $this->db->quoteInto('id =?', $value['cid']);
				$this->db->update('content_Product', $data,$where);
				pr($value);
				
			}
			
		}
		return false; 
	}
	
	
	
	// projede zaškrtnuté kategorie a pokud není vloží, ty co nejsou v categories odmaže - pøidá se NodeId pro eshop
	public function savePositionCategories($idProduct,$categories)
	{
		$arrCat = explode('|',$categories);
		$arrCat[] = 3801;
		$where = array();
		$where[] = $this->db->quoteInto('idProduct = ?', $idProduct);
		foreach ($arrCat as $key => $value) {
			if(!$this->checkPositions($idProduct,$value)){
				$data = array();
				$data['idProduct'] = $idProduct;
				$data['idCategory'] = $value;
				$data['poradi'] = $this->getNextPosition($value);
				$this->db->insert($this->_tableNameSort,$data);
			}
			$idParents[] = $value;
		}
		$where[] = 'idCategory not in ('.implode(',', $idParents).')';
		$this->db->delete($this->_tableNameSort,$where);
		return false;
	}
	
	public function setPoradiFromInput($input)
	{
		$idContent = str_replace('reOrder', '', $input->id);
		$this->setPoradi(false, $input->poradi, $idContent);
	}
	
	private function setPoradi($id=false,$position,$idContent = false)
	{
		//e($id);
		if(!$idContent)
		$idContent = $this->db->fetchOne("SELECT c_id from `NodesContents` WHERE n_id=:n and c_type = 'content_Product'", array('n' => $id));
		//e($idContent);
		
		$oldPos = $this->getPosition($idContent);
		//e($oldPos);
		
		//die;
		
		$position = min(($this->getNextPosition()-1),$position);
		if($position < $oldPos){
			$condition = "IF(poradi>".$oldPos.", poradi, poradi+1)";
		}
		else{
			$condition = "IF(poradi<=".$position.", poradi-1, poradi)";
		} 
		e("UPDATE ".$this->_tableName." SET `poradi`= ".$condition." where poradi >= ".min($oldPos,$position)." and id!=".$idContent);
		//die;
		$this->db->query("UPDATE ".$this->_tableName." SET `poradi`= ".$condition." where poradi >= ".min($oldPos,$position)." and id!=".$idContent);	
		$where = $this->db->quoteInto('id = ?', $idContent);
		
		$data = array('poradi' => $position);
		$this->db->update($this->_tableName, $data,$where);
	}
	
	public function saveSort($string,$currentPage,$currentCountPage)
	{
		$values = str_replace('row', '', $string);
		$sort = explode(',',$values);
		$position =1;
		if($currentPage > 1)
			$position = $currentPage * $currentCountPage;

		foreach ($sort as $key=>$valeu) {
			$this->setPoradi($valeu,$position);
			$position++;
		}
	}
	
	private function getPosition($id){
		return $this->db->fetchOne("select poradi from ".$this->_tableName." WHERE  id=:idp", array( 'idp' => $id));
	}
	
	public function removeCompare($nodeId,$view)
	{
		
		unset($view->session->compareProduct[$nodeId]);
		if(($key = array_search($nodeId, $view->session->compareProduct)) !== false) {
			unset($view->session->compareProduct[$key]);
		}
		$saveCookie = base64_encode(serialize($view->session->compareProduct));
		$saveCookie = $path = ''; 
		if($view->session->compareProduct){
			$saveCookie = base64_encode(serialize($view->session->compareProduct));
			$path = '?products='.implode('_',$view->session->compareProduct);
		}
		
		$this->setcookielive("compare", $saveCookie, time()+36000000, '/', 'topchlazeni.cz');
		return  '/porovnani-produktu'.$path;
	}
	
	
	public function addToCompare($nodeId,$view)
	{
		$view->session->compareProduct[$nodeId] = $nodeId;
		
		$saveCookie = base64_encode(serialize($view->session->compareProduct));
		$this->setcookielive("compare", $saveCookie, time()+36000000, '/', 'topchlazeni.cz');
	}
	
	
	public function setCompareFromGet($view,$get=false)
	{
		$view->session->compareProduct = explode('_',$get);
	}
	
	
	public function setPathCompare($view)
	{
		if(!$view->session->compareProduct){
			$view->session->compareProduct = unserialize(base64_decode($_COOKIE['compare']));
		}
		if($view->session->compareProduct){
			$v = '?products=';
			foreach ($view->session->compareProduct as $value) {
				$re[$value] = $value;
			}
			$v .= implode('_',$re);
		}	
		return  '/porovnani-produktu'.$v;
	}
	
	public function showCompare($view)
	{		
		//pr($view->session->compareProduct);
		if(!$view->session->compareProduct)
		{
			$view->session->compareProduct = unserialize(base64_decode($_COOKIE['compare']));
		}
	//	unset($view->session->compareProduct);
		$html = '';
		if($view->session->compareProduct){
			$count = count($view->session->compareProduct);
			$html .= '<div class="fLeft">';
			$html .= $this->showCompareProducts(false,$view,true);
			$html .= '</div>';
			$inc = 0;
			foreach ($view->session->compareProduct as $value) {
				$inc++;
				$html .= '<div class="fLeft">';

					$html .= $this->showCompareProducts($value,$view,false);
				$html .= '</div>';
			}
			
		}
		return $html;
	}
	
	private function showCompareProducts ($nodeId = false,$view,$showName = false)
	{
		//BioFresh	1
		
		if($nodeId){
		$node = $this->tree->getNodeById($nodeId);
		$content = $node->getPublishedContent();
		$variant = $view->mVarianta->getVariantsByIdProduct($content->id,true);
		}
		if($showName){
			$html .= '<table id="par" class="">';
		}
		else{
			$html .= '<table class="params" id="compare'.$node->nodeId.'">';
		}
			$html .= '<tr>';
			if($showName){   
			}
			else{
				$html .= '<td><a href="'.$node->path.'">'.$node->title.'</a><a class="removeProd" atr="'.$node->nodeId.'" href="Javascript://" ></a></td>';
			}
			$html .= '</tr><tr >';
			$photos = $view->mVarianta->getResizedPhotos($variant['obrazky']);
			$p = helper_FrontEnd::getFirstPhoto($photos,'pShow3' , false);
			if($showName){
				$html .= '<td style="height:205px;"></td>';
			}
			else{
				$html .= '<td class="tAlignCenter" style="height:150px;"><a href="'.$node->path.'"><img src="'.$p['path'].'" alt="'.$node->title.'"></a></td>';
			}
			$html .= '</tr>';
			if($showName){
				$html .= '<td>Cena</td>';

			}
			else{
				$html .= '<td>'.helper_FrontEnd::price($variant['price']).' Kè</a></td>';
			}
				foreach ($this->_filter as $v)
					
				{
					$height = '';
					$html .= '<tr>';
					if($view->mVarianta->variantProperty[$v]['cMap'] == 'Madlo')
					{
						$height = 'style="height:59px;"';
					}
					if($showName){
						
						$html .= '<td '.$height.'>'.$view->mVarianta->variantProperty[$v]['cMap'].'</td>';
					}
					else{
						
						if($view->mVarianta->variantProperty[$v]['selection'])
						{	
							if($variant[$v]){
								$html .= '<td '.$height.'>'.$view->mVarianta->variantProperty[$v]['selection'][$variant[$v]].'</td>';
							}
							else{
								$html .= '<td '.$height.'>-</td>';
							}
						}
						elseif($view->mVarianta->variantProperty[$v]['type']=='Checkbox')
							{
								$n = $variant[$v]>0 ? 'ANO':'NE';
								$html .= '<td>'.$n.'</td>';
							}
						else{
							
							if($variant[$v]){
								$html .= '<td>'.$variant[$v].'</td>';
							}
							else{
								$html .= '<td>-</td>';
							}
						}
					}
					$html .= '</tr>';
				}
		$html .= '</table>';
		return $html; 
	}
	
	function getExistingZnackyRady($params){
		$select =  $this->db->select();
		$select->from(array( 'cm' => $this->_tableName), array($disc, 'rada','znacka')); 		  
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
        
		if($params['search']){

			$params['search'] = str_replace('&quot;', '"', $params['search']);
			$select->where("`n`.`title` LIKE  '%" . $params['search']."%'");
			$select->orwhere("`var`.`title` LIKE  '%" . $params['search']."%'");
		}    
	
		if($params['znacka']){
         	$select->where('znacka = ?', $params['znacka']);
        }
         
		$select->where('state = ?', 'PUBLISHED');  
		
		$select->where('c_type = ?', $this->_tableName); 
		
		$all =  $this->db->fetchAll($select, $bind);	 	
		
		$znacky = $rady = array();
		foreach($all as $p){
			$znacky[$p['znacka']] = $p['znacka'];
			$rady[$p['rada']] = $p['rada'];
		}
		return array($znacky, $rady);
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
			return 'produktù';
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
			return 'kusù';
		}
	}
	
	
	
	
	function setcookielive($name, $value='', $expire=0, $path='', $domain='', $secure=false, $httponly=false) {
		//set a cookie as usual, but ALSO add it to $_COOKIE so the current page load has access
		$_COOKIE[$name] = $value;
		return setcookie($name,$value,$expire,$path,$domain,$secure,$httponly);
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
		foreach ($this->_filter as $fil)
		{
			
			if($view->inputGet->{$fil} > 0 && $name != $fil)
			{
				
				$select->where('var.'.$fil. ' = ?', $view->inputGet->{$fil});
			}
			
		}
		
		
		
		
		$select->where('skladem = ?', '1');
		
		if($name != 'noFrost'){
			$select->group($name);
		}
			$select->order($name.' asc');
		$options = $this->db->fetchAll($select);
		if(is_array($view->mVarianta->variantProperty[$name]['selection']))
		{
			foreach ($options as $key=>$value)
			{
				if($value[$name])
				$retArray[$value[$name]] = $view->mVarianta->variantProperty[$name]['selection'][$value[$name]];
			}
		}
		else{
			foreach ($options as $o)
			{
				if($o>0)
				$retArray[$o[$name]] = $o[$name];
			}
			
		}
		return $retArray;
	} 
 public function renderBanerProduct()
      {
      }
	
	public function initSearch( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params,$view, $count = false)    
    {
    	$select =  $this->db->select();
		$bind = array();

		if($count){    		 
    		$c =  new Zend_Db_Expr("count('id')");  
    		$select->from(array('cm' => $this->_tableName), array( $c,  $disc ));  
    	} else if($params['allcol']) { 
    		$select->from(array( 'cm' => $this->_tableName), array('*')); 		  
    	}  else { 
    		$select->from(array( 'cm' => $this->_tableName), array($disc, 'cid' => 'cm.id', 'cm.poradi as prodporadi', 'n.id', 'n.title', 'n.path', 'n.parent', 'dateModif', 'dateCreate', 'html', 'files', 'parent', 'znacka', 'video',  'souvisejici',  'state', 'dphQuote', 'preText')); 		  
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
        if($params['showAllV'])
        {
        	$select->join(
        			array('var' => $this->_tableNameVariants),
        			'var.id_product = cm.id',
        			array('id as variantId',  'EAN',  'poradi', 'obrazky', 'price', 'price2', 'discount', 'realSold', 'sold')
        	);
        }   
        else{
        $select->join(
        		array('var' => $this->_tableNameVariants),
        		'var.id_product = cm.id', 
        		array('id as variantId',  'EAN',  'poradi', 'obrazky', 'price', 'price2', 'discount', 'realSold', 'sold')
        );
        }
       
		       
        if($sort == 'title')  {
        	$sort = 'n.' . $sort;
        }     

 	if($params['showFirstVariant'])
        {
        	if($params['skladem'])
        	{
        		$idsVariantDb = $this->db->fetchAll('SELECT id FROM (select * from '.$this->_tableNameVariants.'  where skladem = '. $params['skladem']. ' order by poradi asc) as s group by s.id_product');
        	}
        	else{
        		$idsVariantDb = $this->db->fetchAll('SELECT id FROM (select * from '.$this->_tableNameVariants.' order by poradi asc) as s group by s.id_product');
        	}
        	if(count($idsVariantDb)>0){
        	foreach ($idsVariantDb as $id)
        	{
        		$idsVar[] = $id['id'];
        	}
        	$ids = implode(',', $idsVar);
        	//pr($ids)
        	$select->where(' var.id in ('.$ids.' )');
        	}
        	else{
        		return false;
        	}
        }
      
	  
	  	if(!$view->inputGet->showFilter){
       
        foreach ($this->_filter as $fil)
        {
     
        	if($view->inputGet->{$fil} > 0)
        	{
				if(is_array($view->inputGet->{$fil}))
				{
					foreach ($view->inputGet->{$fil} as $e) {
							$tt1222[] =  'var.'.$fil .' = '.$e;
						}
						$sel = '('.implode(' or ', $tt1222).')';
						$select->where($sel);
				}
				else{
        			$select->where('var.'.$fil. ' = ?', $view->inputGet->{$fil});
				}
        		
        	}
       	 }
      	}
      	else{
      		
      		$inputGet = clone $view->inputGet;
      		
      		foreach ($this->_filterNew as $ke => $fil)
      		{
      			$k = $ke;
      			$k = str_replace('Min', '', $k);
      			$k = str_replace('Max', '', $k);
      			/// test jestli existuje min a max v getu a pak and když ne tak to poslat jen do jedné podle
      			if($ke == 'priceMin' && $inputGet->priceMin>0 && $inputGet->priceMax){
      			//	$select->where('var.'.$k. ' >= '.$inputGet->priceMin.' and var.'.$k. ' <= '.$inputGet->priceMax);
      			}
      			elseif(strpos($ke, 'Max') && !$inputGet->priceMin){
      		//		$select->where('var.'.$k. ' <= ?', $inputGet->{$ke});
      			}
      			elseif((strpos($ke, 'Min') && !$inputGet->priceMax && $inputGet->priceMin > 0) ) {
      				$select->where('var.'.$k. ' >= ?', $inputGet->{$ke});
      				
      			}
      			
      			elseif($inputGet->{$k} > 0  && !is_array($inputGet->{$k}))
      			{
      			
      				$select->where('var.'.$k. ' = ?', $inputGet->{$k});
      		
      			}
      				
      			// je to pole
      			elseif($inputGet->{$k})
      			{
      			
      				if($k == 'vyska'){
      					foreach ($inputGet->{$k} as $it)
      					{
							switch ($it) {
							case '1':
							$vysks[] =  '(var.vyska  < 88)';
								break;
							case '2':
							$vysks[] = '(var.vyska > 88 and var.vyska < 145 )';
							break;
							case '3':
							$vysks[] = '(var.vyska > 145 and var.vyska < 165)';
							break;
							case '4':
							$vysks[] = '(var.vyska > 165 and var.vyska < 185)';
							break;
							case '5':
							$vysks[] = '(var.vyska > 185 and var.vyska < 210)';
						break;
							}
						}
						$select->where(implode(' or ', $vysks));
					}
					elseif($k == 'sirka'){
						foreach ($inputGet->{$k} as $e) {
							$tt1[] =  'sirka = '.$e;
						}
						$sel = '('.implode(' or ', $tt1).')';
						$select->where($sel);
						//pr($sel);
					}
					else{
      					foreach ($inputGet->{$k} as $it)
      					{
      						$nn = 'var.'.$k;
      						if($k == 'znacka')
      						{
      							$nn = 'znacka';	
      						}
      						$select->where($nn.' in ('.implode(',', $inputGet->{$k}).')');
      					}
      			
					}
      				unset($inputGet->{$k});
      			}
      				
      		}
			
      		
      	}
		
       	if($view->inputGet->priceMax >0){
				$select->where("price <= ?" ,$view->inputGet->priceMax);
			}
			if($view->inputGet->priceMin > 0){
				$select->where("price >= ?",$view->inputGet->priceMin);
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
         
         

         if($params['action']){
         	$select->where('var.action  = ?', '1');
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
		  
		   
     {
     	//$select->where('n.id in ('.$params['souvisejici'].' )');
     }  
        
        
         
     if($params['souvisejici'])
     {
     	//$select->where('n.id in ('.$params['souvisejici'].' )');
     }    
    	 
	if($params['search']){

			$params['search'] = str_replace('&quot;', '"', $params['search']);
			
			$select->where("`n`.`title` LIKE  '%" . $params['search']."%'");
		//	$select->orWhere("`var`.`model` LIKE  '%" . $params['search']."%'");
			
		}         
		// $select->where('dateShow <= ?', new Zend_Db_Expr('NOW()'));	
		
		if($params['state']){  
			$select->where('state = ?', $params['state']); 
		} elseif($params['cms']) { 
			$select->where('state = ?', 'PUBLISHED');
			$select->orwhere('state = ?', 'ARCHIVED'); 
		}
		else{
			$select->where('state = ?', 'PUBLISHED');
		}
		
		$select->where('c_type = ?', $this->_tableName);
	
		
		

		
		  
		$sortType = $sortType?$sortType:'Asc';


        $availableSorts = array(
       		'soldPrice' => 'cm.sold',
       		'priceasc' => 'price',
       		'price' => 'price',
        	'rating' => 'rating',
       		'dateCreate' => 'dateCreate',  
        );
        $availableSortTypes = array( 
        	'soldPrice' => 'DESC',
        	'priceasc' => 'ASC',
        	'price' => 'DESC',
        	'rating' => 'DESC',
        	'dateCreate' => 'ASC',
    	);
        
    	if(!$this->inAdmin){
    		$sortType = $availableSortTypes[$sort];
	        $sort = $availableSorts[$sort];
    	}
    	if($sort && $sortType){
	        $select->order($sort . ' ' . $sortType);
    	}
	//	$select->order('n.id DESC');
		//pr($select->__toString());
		$select->limit($limitCount, $limitStart);		
	
		return array($select, $bind); 
    }   
    
    
    
    
    public function getProducts( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params = array(),$view)   
    {
		list($select, $bind) = $this->initSearch($sort, $sortType, $limitStart, $limitCount, $params,$view);	
		if($select){
			return $this->db->fetchAll($select, $bind);	 
		}  	 
    }   
    
    
    public function getZnacky($id = false, $toSelect = false){
    	if(!$id){
    		if($toSelect){
    			$znacky =  $this->db->fetchAll('select * from '.$this->_tableNameZnacky.' order by poradi ASC');
    			foreach ($znacky as $v)
    			{
    					$retZnacky[$v['id']] = $v['nazev'];  
    			}
    			return $retZnacky; 
    		}
    		else{
    			return $this->db->fetchAll('select * from '.$this->_tableNameZnacky.' order by poradi ASC');
    		}
    	}
    	else{
    			return $this->db->fetchRow('select * from '.$this->_tableNameZnacky.' where id= ? order by poradi ASC',$id);
    		}
    	
    }
    
	public function getZnackyRady($rady = true){   
		$sel = array();	
		$select = $this->db->select();
		$select->from($this->_tableNameZnacky);
		$select->order('poradi asc');   
		$z =  $this->db->fetchAll($select);		 
		
		foreach($z as $f => $zz){
			$sel[$zz['id']] = $zz['nazev'];
			if($rady){
				$select2 = $this->db->select();
				$select2->from($this->_tableNameZnackyRady);
				$select2->where('zid = ?', $zz['id'] );  
				
				$select2->order('nazev asc');  
			
				$rady =  $this->db->fetchAll($select2);	
				foreach ($rady as $r){ 
					$sel[$r['id']] = $zz['nazev'] .   ' - ' . $r['nazev'];
				}	 
			}	
		}
		return $sel; 
    }
    
	public function getRady($znacka){     
		$sel = array();	 
		$select = $this->db->select();
		$select->from($this->_tableNameZnackyRady); 
		$select->order('nazev asc');  
		if($znacka){ 
			$select->where('zid = ?', $znacka );  
		}
		$z =  $this->db->fetchAll($select);		 
		
		foreach($z as $f => $zz){
			$sel[$zz['id']] = $zz['nazev']; 
		}
		return $sel; 
    }
    
	public function getRada($id){     
		$sel = array();	 
		$select = $this->db->select();
		$select->from($this->_tableNameZnackyRady);
		$select->where('id = ?', $id );  
		return $this->db->fetchRow($select); 	 
    }
    
    public function getZnackaByRada($rid){   
    	$select2 = $this->db->select(); 
		$select2->from($this->_tableNameZnackyRady); 
		$select2->where('id = ?', $rid );     
		$row = $this->db->fetchRow($select2);	  
		return $row['zid'];
    } 
      
    
    public function getProductsCout( $sort = 'title', $sortType = 'Asc', $limitStart = 0, $limitCount = 5, $params = array(),$view)   
    {   
    	list($select, $bind) = $this->initSearch($sort, $sortType, $limitStart, $limitCount, $params,$view, true); 
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
   				'price' => helper_FrontEnd::price($price[0]).' Kè'
   				);
   			$where = $this->db->quoteInto('nodeId = ?', $nodeId);
			$this->db->update(
				$this->_tableNameCache,
				$data,
				$where);
   	}
   	
   	public function showSelectedOptions($inputGet,$view,$showText = false)
   	{
   		$html = array(); 
   		$ret = '';
   		foreach ($this->_filter as $value) {
   			if($inputGet->$value>0)
   			{
   			
   					if($view->mVarianta->variantProperty[$value]['selection'])
   						{
   							$html[] = strtolower($view->mVarianta->variantProperty[$value]['cMap']).' - '.strtolower($view->mVarianta->variantProperty[$value]['selection'][$inputGet->$value]);
   						}
   					if($view->mVarianta->variantProperty[$value]['type']=='Checkbox')
   						{
   							$html[] = strtolower($view->mVarianta->variantProperty[$value]['cMap']);
   						}
   				
   			}
   	
   		}
   		$text = implode(', ',$html);
   		if($showText && $text)
   		{
   			$ret= ' <span class="grey fs16">('.$text.')</span>';
   		}
   		elseif($text)
   		{
   			$ret = ', '.$text;
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
   	
   	
   	function diakritika($text){
   		$prevodni_tabulka = Array(
   				'ä'=>'a',
   				'Ä'=>'A',
   				'á'=>'a',
   				'Á'=>'A',
   				'a'=>'a',
   				'A'=>'A',
   				'a'=>'a',
   				'A'=>'A',
   				'â'=>'a',
   				'Â'=>'A',
   				'è'=>'c',
   				'È'=>'C',
   				'æ'=>'c',
   				'Æ'=>'C',
   				'ï'=>'d',
   				'Ï'=>'D',
   				'ì'=>'e',
   				'Ì'=>'E',
   				'é'=>'e',
   				'É'=>'E',
   				'ë'=>'e',
   				'Ë'=>'E',
   				'e'=>'e',
   				'E'=>'E',
   				'e'=>'e',
   				'E'=>'E',
   				'í'=>'i',
   				'Í'=>'I',
   				'i'=>'i',
   				'I'=>'I',
   				'i'=>'i',
   				'I'=>'I',
   				'î'=>'i',
   				'Î'=>'I',
   				'¾'=>'l',
   				'¼'=>'L',
   				'å'=>'l',
   				'Å'=>'L',
   				'ñ'=>'n',
   				'Ñ'=>'N',
   				'ò'=>'n',
   				'Ò'=>'N',
   				'n'=>'n',
   				'N'=>'N',
   				'ó'=>'o',
   				'Ó'=>'O',
   				'ö'=>'o',
   				'Ö'=>'O',
   				'ô'=>'o',
   				'Ô'=>'O',
   				'o'=>'o',
   				'O'=>'O',
   				'o'=>'o',
   				'O'=>'O',
   				'õ'=>'o',
   				'Õ'=>'O',
   				'ø'=>'r',
   				'Ø'=>'R',
   				'à'=>'r',
   				'À'=>'R',
   				'š'=>'s',
   				'Š'=>'S',
   				'œ'=>'s',
   				'Œ'=>'S',
   				''=>'t',
   				''=>'T',
   				'ú'=>'u',
   				'Ú'=>'U',
   				'ù'=>'u',
   				'Ù'=>'U',
   				'ü'=>'u',
   				'Ü'=>'U',
   				'u'=>'u',
   				'U'=>'U',
   				'u'=>'u',
   				'U'=>'U',
   				'u'=>'u',
   				'U'=>'U',
   				'ý'=>'y',
   				'Ý'=>'Y',
   				'ž'=>'z',
   				'Ž'=>'Z',
   				'Ÿ'=>'z',
   				''=>'Z'
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
    

     
  
    public function searchProduct($view){
    	$all = $this->db->fetchAll("SELECT * from " . $this->_tableNameCache. " where title LIKE '%".$view->inputGet->searchText."%'");
		$products = array();
		if($all)
		{
			foreach ($all as $value) {
				$product = new stdClass();
				$product->path = $value['path'];
				$product->price = $value['price'];
				$product->title = $value['title'];
				$value['photos'] = $value['photos'] ? $value['photos'] : '/images/icoFolderSmall.png';
				$product->photo = $value['photos'];
				$products[] = $product;
			} 	
		}
		return json_encode($products);
    }
    
    function getSeachableWords($view){				
		$all = $this->searchProduct($view);
			 
		$words = array(); 
		
						// DOPSAT
//		$eShop  = helper_FrontEnd::checkChildren($view->tree->getNodeChildren(3801, 'FOLDER'), 1);
//		foreach ($eShop as $value) {
//			$conEshop = $value->getPublishedContent();
//			$conEshop->getPropertyByName('pathToTemplate')->value=='Products';
//			{			
//				$textImage = '<img src="'.$img.'"/><span class="searchText">'.$value->title.'</span><br><span class="searchPrice">'.helper_FrontEnd::price($price).' Kè</span>';
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
            $textImage = '<img width="31" height="31" src="'.$text['photos'].'"/><span class="searchText">'.$text['title'].'</span><br><span class="searchPrice">'.$tempText.'</span>';
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