<?php
/**
 * CMS
 * *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 */


/**
 * Helper for tables
 *
 * @uses Zend_View_Helper_FormElement
 * @category   Cms
 * @subpackage Helper
 */
class Cms_View_Helper_Table extends Zend_View_Helper_FormElement
{	
    public function Table( $rows, array $headers,  $actions, $order = false, $orderType = false, array $viewState , $parentTab = 'vypisDole', $attribs = null, $showListActions = true)
    {          
    
    	$this->view->randIdentifier = $randIdentifier = rand(0,99999999999);
        $table = $header = '';
        $this->parentTab = $parentTab;
        $this->filteredRequestParams = $this->view->request->getParams();
        $this->filteredRequestParams['action']  = $viewState['action'];
                 
        // akce a vyrbane radky nepredavame dal do url
        unset($this->filteredRequestParams['tableAction']);
        
        foreach ($this->filteredRequestParams as $ident => $val){			
			if(substr($ident,0,4) == 'row_'){
				unset($this->filteredRequestParams[$ident]);
			}			
		}
        		
        $this->listingPer = $this->view->request->getParam('tableListing');	
		$this->listingPer = $this->listingPer?$this->listingPer:$this->view->tableDefaultListing;
    	    	
    	$this->curentPage = $this->view->request->getParam('curentTablePage');	
		$this->curentPage = $this->curentPage?$this->curentPage:0;
		
		/* LISTING */	
        if(count($rows) > $this->view->tableDefaultListing){
        	list($listing, $startRow, $endRow) = $this->getListing(count($rows));
        } else {
        	$listing = '';
        	$startRow = 0;
        	$endRow = 9999;
        }
               		
		// header 
        $header .= '<tr>';
        if(count($this->view->tableActions)  || $this->view->tableForceCheckboxes){
        	 $header .= '<th style="width:20px;">' . $this->view->formCheckbox($this->view->randIdentifier . 'checkAll', '', array('nodojo' => 1, 'style'=> 'border:1px solid gray;', 'title' => 'Vybrat všechny na stránce', 'onClick' => "checkAll('form$randIdentifier', this.checked)")). '</th>';
        }
        foreach ($headers as $identifier => $rowHead) {
            
        	if ($rowHead['atribs']) {
	            $headAttribs = $this->_htmlAttribs($rowHead['atribs']);
	        } else {
	            $headAttribs = '';
	        }
        
        
        	$header .= '<th' . $headAttribs . '>';
            if(is_int($identifier) || $this->view->tableDonotShowOrder){
            	$header .= $rowHead['title'];
            } else {         
            	if($rowHead['sortUrlType'] == 'nohead'){
            		$header .= $rowHead['title'];  
            	} elseif($rowHead['title'] != 'nohead'){
	            	switch ($rowHead['sortUrlType']){  
		    			case 'refresh-tab':	    				
		    				$hrefAsc = 'href="#"  title="Seřadit vzestupně" onclick="return refreshTab(\'' . $parentTab . '\',\'' . Utils::getStrictUrl($this->view, array_merge($this->filteredRequestParams, array('sort' => $identifier, 'sortType' => 'asc'))) . '\');"';
		    				$hrefDesc = 'href="#"  title="Seřadit sestupně" onclick="return refreshTab(\'' . $parentTab . '\',\'' . Utils::getStrictUrl($this->view, array_merge($this->filteredRequestParams, array('sort' => $identifier, 'sortType' => 'Desc'))) . '\');"';
		    				break;	    			
		    			default:
		    				$href = 'href="' .  $baseUrl . '/sort/' . $identifier . '"';		    				
		    		}
		    		
		    		$orederedA = $orederedD = '';
		    		if($order == $identifier){
		    			if($orderType!='Desc'){
		    				$orederedA = '-selected';
		    			} else {
		    				$orederedD = '-selected';
		    			}
		    		}
		    		
	            	$header .= $rowHead['title'] . ' <a ' . $hrefAsc . ' class="sort-asc' . $orederedA .'">&nbsp;</a>' . '<a ' . $hrefDesc . ' class="sort-desc' . $orederedD . '">&nbsp;</a>';
            	}
            }
            
        	$header .= '</th>';
        }
        if(count($actions) > 1 ){
        	$header .= '<th>&nbsp;</th>';
        }
        
        $header .= '</tr>';
        $table .= $header;
        // header ends
       
		//actions		
		if(count($actions)){
			$firstAction['nocio'] = array_shift($actions);
		}
		 
		$rowActions = $this->getActions($actions);
        
		        	
        
        // actions ends
        $rowsTotal = count($rows);
        if($rowsTotal){        	
        
        	$hl = $this->view->request->getParam('hln'); 
        	
	        foreach ($rows as $i => $row) {
	        	
	        	if(!($i >= $startRow && $i < $endRow)){
	        		continue;
	        	}
	        	  
	        	
	        	  
	        	if($row['nodeId'] && $row['nodeId'] == $hl){  
	        		$table .= '<tr onMouseOver="mOver(this)" onMouseOut="mOut(this)" style="background-color:#c7eea1;">'; 
	        	} else {
	        		 $table .= '<tr onMouseOver="mOver(this)" onMouseOut="mOut(this)">'; 
	        	} 
	        	
	        	//pr($row);
	           
	            $identificated = false;
	           // pr($row);
	          
	            foreach ($row as $value) {
	            	
	            	if(!$identificated && (count($actions) || count($firstAction) || count($this->view->tableActions))){
	            		
	            		$identifier = $value;
	            		$identificated = true;
	            		if(count($this->view->tableActions) || $this->view->tableForceCheckboxes){
	            			$table .= '<td>' . $this->view->formCheckbox('row_' . $identifier, $this->{'rowId_' . $identifier}, array('nodojo' => 1)) . '</td>'; // checkbox
	            		}
	            		// prvni muze byt odkaz
	            		if((count($actions) || count($firstAction)) || $this->view->tableForceCheckboxes){	            			      			
	            			$firstCell = true;
	            		}
	            		continue;
	            	} else if (!$identificated && $this->view->tableForceCheckboxes){
	            		$identificated = true;	     
	            		$table .= '<td>' . $this->view->formCheckbox('row_' . $value, $this->{'rowId_' . $value}, array('nodojo' => 1)) . '</td>'; // checkbox
	            		continue;
	            	}
	            	
	            	if($firstCell){ // 1. je odkaz	            
	            		
	            		$identifierAction = $this->getActions($firstAction, ($value));

	            		$value = str_replace( '%$%', urlencode($identifier),  $identifierAction);
	            		$firstCell = false;
	            		//$value = str_replace("'", "\'", $value); 
	            	}
	            	
	            	
	           		$table .= '<td>' . $value . '</td>';
	           		$count++;
	            }
	            
	            if(count($actions) > 0){	            	 
	            	$table .= '<td>' . str_replace( '%$%', $identifier, $rowActions ) . '</td>';
	            }
	            
	            $table .= '</tr>';
	           
	        }
        }
        
        if ($attribs) {
            $attribs = $this->_htmlAttribs($attribs);
        } else {
            $attribs = '';
        }     
     
        if(count($this->view->tableFilters) || $this->view->showSearch){        	
	        $filtr = '	         
	        	<div class="filtr">
	        	&nbsp;&nbsp;&nbsp;filtr: ';
	        
	        foreach ($this->view->tableFilters as $filterKey => $filter){
	        	
	        	$curentFilter = 'tableFilter' . $filterKey;
	        	//e($this->view->{$curentFilter});
	        	$filtr .= $this->view->formSelect(
	        		$this->view->randIdentifier . $curentFilter, 
	        		$this->view->{$curentFilter}, 
	        		array(
	        			'autocomplete'=>'true',  
	        			'style' => ' font-size:0.9em; *font-size:90%; width:auto !important; ', 
	        			'onChange' => 'if(\'' . $this->view->{$curentFilter} . '\' != this.getValue() && this.getValue()) { return refreshTab(\'' . $parentTab . '\',\'' . Utils::getStrictUrl($this->view, $this->filteredRequestParams, array($curentFilter)) . '/' . $curentFilter . '/\' + this.getValue()) }',
	        			 
	        			'value'=>$this->view->{$curentFilter}
	        		), 
	        		$filter,
	        		  '', true 
	        	) . ' &nbsp; ';
	        } 
	        
         	if($this->view->showSearch){  
         		//$this->filteredRequestParams['tableSearch'] = str_replace('"','',$this->filteredRequestParams['tableSearch']);
         		//e($this->filteredRequestParams['tableSearch'] );	
			    $filtr .=    
			   		'hledat: ' . 
			   		$this->view->formText('tableSearch', $this->filteredRequestParams['tableSearch'], array('style' => '; width:80px !important;'))
			   		.  
			   		$this->view->formSubmit(
						$this->view->randIdentifier . 'doSearch', 
						'OK', 
						array( 
							'class'=>'fsubmit2', 
							'style' => ' font-size:0.9em; *font-size:90%',
							'onMouseOver' => 'return false;', 
							'onClick' => 'return refreshTab(\'' . $parentTab . '\',\'' . Utils::getStrictUrl($this->view, $this->filteredRequestParams, array($curentFilter)) . '/tableSearch/\' + dijit.byId(\'tableSearch\').getValue()) ',
	        			
							'iconClass' => "noteIcon"  
						)
				);  
	       } 
	        	
	       $filtr .=  '	    
	        	</div>
	        ';
        } else {
        	$filtr = '';
        }
		
        if($this->view->showSortInfo){
	        $filtr .= '<div class="filtr">';
	        if($this->view->isDefaultSorted){
	        	$filtr .= 'zobrazeno jako na webu ' . Utils::getHelpIco('Pořadí v jakém jsou zobrazeny položky, je shodné s pořadím na webu');
	        } else {
	        	$back = 'href="#"  onclick="return refreshTab(\'' . $parentTab . '\',\'' . Utils::getStrictUrl($this->view, array_merge($this->filteredRequestParams, array('sort' => '', 'sortType' => ''))) . '\');"';
	        	$filtr .= 'dočasně nastavené pořadí (<a ' . $back . '>zobrazit jako na webu</a> ) ' . Utils::getHelpIco('Dočasné zobrazení, nemění pořadí na webu.');
	        }
	        $filtr .= '</div>';
        }
        
        $submitFormUrl = Utils::getStrictUrl($this->view, $this->filteredRequestParams, array());
        
      // e($submitFormUrl);
      if(count($this->view->tableActions)){
      	
      	$this->view->tableAction = $this->view->tableAction?$this->view->tableAction:key($this->view->tableActions); 
        $multipleActions = '
        	<div class="tableActions">
        	označené: '  
        	. $this->view->formSelect(
        		'tableAction' . $randIdentifier, 
        		$this->view->tableAction, 
        		array(
        			'autocomplete'=>'true', 
        			'style' => 'font-size:0.9em; *font-size:90%;', 
        			'value'=>$this->view->tableAction        			
        		), 
        		$this->view->tableActions
        	);
      /*
       	$multipleActions .=  ' ' . $this->view->formSubmit(
			$this->view->randIdentifier . 'doActions', 
			'OK', 
			array(
				'class'=>'fsubmit', 
				'style' => ' font-size:0.6em; *font-size:60%;',
				'onMouseOver' => 'return false;',
				"onClick" => "if(confirmSubmit2('Opravdu provést tuto akci na všech vybraných záznamech?')){ return submitFormAjax(
					'form$randIdentifier', 
					'".$this->view->url(array('module' => 'cms', 'controller' => 'helper','action' => 'getAjaxFormData'), null, true)."' + '/tableAction/' + dijit.byId('" . 'tableAction' . $randIdentifier ."').getValue(), 
					'" . $this->parentTab . "', 
					'tab-sethref', 
					'" . $submitFormUrl . "',
					1
				)};", 
				'iconClass' => "noteIcon"
			)
		) . '</div>';
		*/
			if( !$attribs['noConfirm'] )
			{
				$mA_onClick="if(confirmSubmit2('Opravdu provést tuto akci na všech vybraných záznamech?')){ return submitFormAjax(
					'form$randIdentifier', 
					'".$this->view->url(array('action' => 'multi'))."' + '/tableAction/' + dijit.byId('" . 'tableAction' . $randIdentifier ."').getValue(), 
					'" . $this->parentTab . "', 
					'tab-submit-refresh', 
					0,
					1
					)} else {return false;};";
			}
			else
			{
				$mA_onClick="return submitFormAjax('form$randIdentifier', '".$this->view->url(array('action' => 'multi'))."' + '/tableAction/' + dijit.byId('" . 'tableAction' . $randIdentifier ."').getValue(),'" . $this->parentTab . "', 'tab-submit-refresh', 0,1);";
			}
			
      $multipleActions .=  ' ' . $this->view->formSubmit(
			$this->view->randIdentifier . 'doActions', 
			'OK', 
			array(
				'class'=>'fsubmit', 
				'style' => ' font-size:0.9em; *font-size:90%;',
				'onMouseOver' => 'return false;',
				"onClick" => $mA_onClick,
				'iconClass' => "noteIcon"
			)
		) . '</div>';
		
      } else {
      	$multipleActions = '';
      }
      /*
		if(count($this->view->messages)){
			$messages = $this->view->render('errors.phtml'); 
		} 
       */ 
      
        return '
        	' . ($showListActions?'<div class="listActions">' . $listing . $filtr . '</div>':'') . 
        	($this->view->disableForm?'':'<form id="form' . $randIdentifier . '" name="form' . $randIdentifier . '" action="' . $submitFormUrl . '" method="post">') .'
        	<table id="' . $randIdentifier . '" ' . $attribs . '>' . $table . '</table>        	
        	' . $multipleActions . 
        	($this->view->disableForm?'':'</form>') . '
        	' . $messages . '<div class="taright fs_xx">celkem řádků:' . $rowsTotal . '</div>' . $this->getExportDiv();
	  	
        
    }
    
    public function getExportDiv(){ 
    	if($this->view->hasExport){ 
    		$exp = '<div style="position:absolute; font-size:11px; color:#006e15; top:5px; right:5px;"><img align="absmiddle" src="/admin/files/xls.gif" /><a target="_blank" href="' . Utils::getStrictUrl($this->view, $this->filteredRequestParams, array()) . '/tableExport/1">export do XLS</a></div> ';
    	} else { 
    		$exp = ''; 
    	}  
        return $exp; 
    }     	
     	
    public function getActions($actions, $overideText = false){
    	$rowActions = '';	 
    	//pr($actions);      
    	//e($overideText);  
    	   
    	if(count($actions)){
	    	foreach ($actions as $ico => $action){
	    		$overideText = str_replace('"','',$overideText);
	    		if($action){
		    		switch ($action['type']){
		    			case 'modal':
		    				if($overideText){
		    				//$overideText =  str_replace('"','',$overideText);
		    				//$action['title'] = str_replace('"','',$action['title']);
		    				
		    				$href = 'href="#" onclick="' . ($ico=='delete'?'if(confirmSubmit())':'') . 'return showModal(\'' . addslashes($action['title']) . ' - ' . str_replace('"', '\"', strip_tags(addslashes($overideText))) . '\',\'' .  $action['url'] . '\');"';
		    				}
		    				break; 
		    			case 'tab-refresh':
		    				$aTab = $action['tabId']?$action['tabId']:$this->parentTab;
		    				$tabs = explode(';', $aTab);
		    				$action['url'] = str_replace('"','',$action['url']);
		    				$urls = explode(';', $action['url']);
		    				$jsRefreshTab = ''; 
		    				foreach ($tabs as $id => $tab){
		    					$jsRefreshTab .= "return refreshTab('" . $tab . "', '" .  $urls[$id] . "'); ";
		    				}
		    				
		    				$href = 'href="#" onclick="' . ($ico=='delete'?'if(confirmSubmit())':'') . ' ' . $jsRefreshTab . ' ;"';
		    				break;
		    			default:
		    				$href = 'href="' .  $action['url'] . '"';		    				
		    		}
		    		
		    		
		    		
		    		//$href = str_replace('"','',$href );
		    		$rowActions .= '<a title="' . $action['title'] . ' ' .  ($overideText?strip_tags($overideText):'') . '" class="' . $ico . '" ' . $href . '>' . ($overideText?$overideText:$action['title']) . '</a>';
	    		} else {
	    			$rowActions = $overideText;
	    		}
	    	}
	    }
	    return $rowActions;
    }
    
    
    function getListing($rowCount){
    	
    	
		$listing = '';
		$max = ceil($rowCount/$this->listingPer) - 1;
		
		if($max <= $this->curentPage){
			$this->curentPage = $max;	
		}
		// stranky	
		
		$url = Utils::getStrictUrl($this->view, $this->filteredRequestParams, array('curentTablePage')) . '/curentTablePage/';								
		for ($i=max(0,($this->curentPage-3)); $i<=min($max,($this->curentPage+2)); $i++) {
			$str .= ($this->curentPage==$i)?'<span class="tbold">'.($i+1).'</span> ¦ ':'<a href="#" onclick="return refreshTab(\'' . $this->parentTab . '\',\'' . $url . $i . '\' )">'.($i+1).'</a> ¦ ';
		}
		$str = substr($str ,0, -3);
			
        		
    	if(($max+1) > $i){
			$str .= '...';
		}
		
		$startRow = $this->curentPage*$this->listingPer;
		$endRow = ($this->curentPage+1)*$this->listingPer;
		
			
		$class = 'listPrev';
		if($this->curentPage>0){
			$hrefFirst = ' href="#' . (0) . '" ';
			$hrefPrev = ' href="#' . ($this->curentPage-1) . '" onclick="return refreshTab(\'' . $this->parentTab . '\',\'' . $url . ($this->curentPage-1) . '\' )" ';
			$class .= '-a';
		}
		$listing .= '
			<a class="ico2 ' . $class . '"' . $hrefPrev . 'title="Předchozí">&nbsp;</a>        		
    	';
		$listing .= ' ' . $str . ' ';
		$class = 'listNext';
		if($this->curentPage<$max){
			$hrefLast = ' href="#' . ($max) . '" ';
			$hrefNext = ' href="#' . ($this->curentPage+1) . '" onclick="return refreshTab(\'' . $this->parentTab . '\',\'' . $url . ($this->curentPage+1) . '\' )" ';
			$class .= '-a';
		}
		$listing .= '
    		<a class="ico2 ' . $class . '"' . $hrefNext  . 'title="Další">&nbsp;</a>
    	';			
		
		$listingDiv = '
        	<div class="listing">
        	' . $listing . '
        	zobrazovat po: '  . 
        	$this->view->formSelect(
        		$this->view->randIdentifier . 'tableListing', 
        		$this->listingPer, 
        		array(
        			'autocomplete'=>'true', 
        			'style' => 'width:80px; ',
        			'onMouseOver' => 'return false;',
        			'onChange' => '
        				if(\'' . $this->listingPer . '\' != this.getValue() && this.getValue()) { 
        					return refreshTab(
        						\'' . $this->parentTab . '\',
        						\'' . Utils::getStrictUrl(
    									$this->view, 
    									$this->filteredRequestParams, 
    									array('tableListing', 'curentTablePage')
    								) 
    							. '/curentTablePage/' . $this->curentPage . '/tableListing/\' + this.getValue()) }',        			
        			'value'=>$this->listingPer
        		), 
        		$this->view->tableListings
        	) . '
        	</div>
        ';	
        
        return array($listingDiv, $startRow, $endRow);
    }
}
//test
