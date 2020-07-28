<?php
/*
	Shared files
*/
class Cms_sfController extends CmsController
{

	public function init()
	{
	
	
		$this->fields = array('filenode', 'fileName', 'fileDescription');
		parent::init();
		
		$this->template = 'controls/admin/SharedFiles.phtml';    	
		$this->view->title .= ' - Soubory';
		$this->view->selectedLeftHelpPane = false;
		
		if($this->doPageInit){
			$this->initPage();
		}
		    /*
		$imagine = new Imagine\Gd\Imagine(); 
		$image = $imagine->open($this->config->fsRoot . '/168.jpg');  
		$watermark = $imagine->open($this->config->fsRoot . '/ruzek-new-ok16.gif');
		$size      = $image->getSize();  
		// $image->draw()->ellipse(new Imagine\Image\Point(200, 150), 300, 225, new Imagine\Image\Color('fff'));
    	 
		//    $image->crop($start, $size) 
		$size      = $image->getSize();  
		$wSize     = $watermark->getSize();  
		
		$bottomRight = new Imagine\Image\Point($size->getWidth() - $wSize->getWidth(), $size->getHeight() - $wSize->getHeight());
		$top = 	new Imagine\Image\Point(0, 0);
		$image->paste($watermark, $bottomRight);
		$image->paste($watermark, $top);  
			  
		$image->show('jpg'); 
		*/   
		   /*
		    * 
		    if($size->getWidth() > 500) {
			    $image
			        ->resize($size->widen(500)) 
			        ->save("2.jpg")
			    ;
			} 
			
			
			$watermark = $imagine->open('/my/watermark.png');
			$image     = $imagine->open('/path/to/image.jpg');
			$size      = $image->getSize();
			$wSize     = $watermark->getSize(); 
			
			$bottomRight = new Imagine\Image\Point($size->getX() - $wSize->getX(), $size->getY() - $wSize->getY());
			
			$image->paste($watermark, $bottomRight);  
		    */
		// die();   
	}
	
	private function initPage()
	{  		
		$filenode = $this->request->getParam('filenode');
		
		$this->view->isPopup = $this->isPopup = $this->request->getParam('isPopup')?1:0; 
		$this->view->isPopupNotFromWysiwyg = $this->isPopupNotFromWysiwyg = $this->request->getParam('nowysiwyg')?1:0;
		$this->callBackInput = $this->request->getParam('callBackInput');
		
		$this->inVersions = $this->view->inVersions = $this->request->getParam('inVersions')?1:0;
		
		// pri uploadu to jde POSTEM
		if($this->input->filenode){
			$filenode = $this->input->filenode;
		} 
		
		//$filenode = $filenode?$filenode:2;    	
		if(!$filenode){
			if($this->session->curentSfDirectory){
				$filenode = $this->session->curentSfDirectory;
			} else {
				$filenode = 2;
			}
		}
		
		$this->view->parentnodeId = $filenode;
		$this->view->rootNodeId = 2;
				
		$this->isCalledRemotelly = $this->request->getParam('inRemoteModule')?true:false;
    	
		if($this->isCalledRemotelly){
	    	if($this->request->getParam('helpnode') ){
	    		$node = $this->request->getParam('helpnode');
	    		$this->rootNodeId = 3;
	    	} elseif ($this->request->getParam('intranetnode')){	    		 		
	    		$filenode = $this->request->getParam('intranetnode');
	    	}
    	}    		   
    	    	
    	
		if($filenode){	 
			$this->nodeId = $this->view->nodeId = $filenode;
			$this->view->filenode = $this->filenode = $this->tree->getNodeById($filenode);				    	
			$this->view->curentParent = $this->session->curentParent = $this->filenode->parentId;
			$this->view->curentTreeNode = $this->view->curentfilenode = $this->session->curentfilenode = $this->nodeId;
			//e($filenode);			
			$this->view->currentFsPath = $this->session->currentFsPath =  $this->tree->getnodeIdPath($filenode, true);	
			// e($this->view->currentFsPath  );
			if($this->filenode->type == 'FOLDER'){
				$this->session->curentSfDirectory = $this->filenode->nodeId;
			}
		}		
		
		 
		$this->view->leftColl = $this->view->render('parts/leftSf.phtml'); 
		  
		$this->view->showBottomPanel = true;  
		$this->view->bottomContentTitle = 'Výpis souborů';
		$this->view->bottomContentHref = $this->view->url(array('controler' => 'sf','action' => 'list'));
		  
		$this->initHelp('sf');   
		
		$this->view->hasUpload = true;  
	}
	
	public function multiAction(){		
		parent::performMultiAction();
	}
	
	public function indexAction()
	{  	
		if($this->input->newfoldername){
			$this->input->title_new = $this->input->newfoldername; 
			list($state, $message, $redir) = $this->makefolder();  
			if($state){ 
				$this->_redirector->gotoUrlAndExit($redir); 
			} else {
				$this->addErrorInstantMessage($message );
			}   
		}
		
		if($this->isPopup){
			$this->filebrowseAction();
		} else {
    		$this->listAction(); 
			parent::indexAction($this->template);
    				
		}    	
	}
	
	public function showTreeAction()
	{  		
    	echo $this->view->render('controls/admin/Tree.phtml');   	
	}
	
	// zobrazeni pro popup
	public function filebrowseAction()
    {       
    	$this->view->content = $this->template;   
		echo $this->view->render('filebrowser.phtml');
    }
	 
	
	public function deleteAction()	{	
		parent::audit($this->filenode->title, $this->nodeId);
		$this->tree->removeNode($this->nodeId);
		//parent::addInfoMessage('Složka odstraněna');
		$this->_redirector->goto('index', 'sf', null, array('filenode' => $this->filenode->parentId));
	}
	
	public function deleteFileAction()	{	
		$filenode = $this->request->getParam('fileContentNode');
		parent::audit($this->tree->getNodeById($filenode)->title, $filenode);
		$this->tree->removeNode($filenode, false);
		//parent::addInfoInstantMessage('Soubor odstraněn');
		//$this->listAction();
	}
	
	public function performMultiaction($action, $id){
		switch ($action){
			case 'delete':
				parent::audit('',$id, 'delete');
				$this->tree->removeNode($id, false);  
				break;	
		}
	}
	
	function jstreeAction(){
		$data = $_GET;    
		switch ($this->inputGet->operation){
			default: 
			case 'get_children':
				$result = array();   
				if($data["id"] == 0){
					$nodes = array($this->tree->getNodeById(2));
				} else {  
					$nodes = $this->tree->getNodeById($data["id"])->getChildren('FOLDER');
				}
				foreach($nodes as $n) {
					$k = $n->nodeId;
					    
					$result[] = array(
						"attr" => array("id" => "node_".$k, "rel" => ($k<4?"drive":"folder")),
						"data" => $n->title,   
						"state" => ($this->tree->hasChildren($k, 'FOLDER')?"closed":"")  
					); 
				} 
				$r =  json_encode($result); 
				break;
		}
		echo $r;
		die();  
	} 
	
	
	public function listAction()
	{ 
		
		parent::performMultiAction(); 
		  
		$getItems = $this->request->getParam('getItems');
		$isAjax = $this->request->getParam('ajax');
		$nodeId = $this->request->getParam('filenode');
		$nodeId = $nodeId?$nodeId:2;  
		 
		
		$this->view->defaultSortType = 'desc'; 
		$this->view->defaultSort = 'n.created';
		
		$params = array();
		$dg = new DataGrid('pagesTable');
		$dg->setRefresUrl($this->view->url(array('action' => 'list', 'getItems' => 1, 'ajax' => 1)))
			->setTitle('Výpis souborů')
			->setHeight(400)  
			->setItemsPerPageOptions('10, 15, 20, 30, 50, 500', 50) 
			; 
			
		$dg->setHeaders( 
			array( 
				array('Název', 'title', 350, 'true', 'left', 'false'),
				array('Náhled', 'file', 120, 'false', 'left', 'false'), 
				array('Vytvořeno', 'n.created', 60, 'true', 'left', 'false'),
				array('Změněno', 'n.dateModif',  60, 'true', 'left', 'false'),
				array('ID', 'n.id', 30, 'true', 'center', 'true')   
			)
		)->setSearchableColls(   
			array(   
				array('Název', 'title', 'true') 
			)
		)->setButtons(
			array(  
				array('Smazat označené', 'delete', 'onpress', 'deletep')  
			));  
			 
		/*
		 * ->setButtons(
			array(
				array('Btn1', 'add', 'onpress', 'null'),
				array('Btn11', 'add', 'onpress', 'null'), 
				array('Btn111', 'add', 'onpress', 'null'),
				array('Btn1111', 'add', 'onpress', 'null'), 
				array('Btn11141', 'add', 'onpress', 'null'),
				array('Btn2', 'delete', 'onpress', 'null') 
			)
		 */
		if($getItems){ 
			$dg->isDebug(false);   
			$dg->setTableName('n', 'Nodes')  
				->setSelectCols(array('n.*'))  
				->getSelect($params) 
				// ->join('nc', 'NodesContents', 'n.id = nc.n_id', 'n.id')  
				->addWhereBind('type', '=', 'ITEM') 
				->addWhereBind('deleted', '=', '0')  
				->addWhereBind('parent', '=', $nodeId);   
				 
			
			list($rowsTotalCount, $rows, $currentPage) = $dg->getRows();
			
			// e($rowsTotalCount);  pr($rows); 
			$rowsFormated = array();
			foreach($rows AS $row){
				//If cell's elements have named keys, they must match column names
				//Only cell's with named keys and matching columns are order independent.
				$node = $this->tree->getNodeById($row['id']);  
				$c = $publishedContent = $node->getTheRightContent(); 
				if(!$c){ continue; }  
				$fullpath = $publishedContent->getPropertyValue('fullpath'); 
				$ico = Utils::getFileIco($fullpath); 
			
				$editUrl = $this->view->url(array('controller' => 'sf','action' => 'detail', 'node'=> $row['id'], 'ajax' => 0));
				
				$img = '';
				$identif = 'im' . $ident;
				$ex = content_SFSFile::getFileExtension($node->path); 
				
				if($ex == 'jpg'){  
					$ppath = $this->view->config->sfFolder . '/' . $node->nodeId . '/sysThumb-' . $ex . '' . $child->path; 
		    		$photoPath = content_SFSFile::getFileFullPath($ppath); // fullPATH FIX        
					$img = '<img width="80" src="' . $photoPath .'" alt="" />';  
				} else {    
					$img = '<img src="' . $ico . '" /> Detaily ' ;  
				} 
				
				$entry = array(    
					'id'=>$row['id'], 
					'cell'=>array(   
						'title'=> '<input name="chbx[' . $row['id'] . ']" type="checkbox" />' . $row['title'] . ' '  . Utils::getFrontEndLink($node->path, false, '', false, 0, $this->view),  
						'file'=> $img,     
						'n.created'=> Utils::formatDate($row['created']),  
						'n.dateModif'=> Utils::formatDate($row['dateModif']),  
						'n.id'=> $row['id'], 
					),  
				);
				$rowsFormated[] = $entry;
			}
			
			if($isAjax){
				$dg->renderAjax($currentPage, $rowsTotalCount, $rowsFormated);
				die();  
			};
		}
		$this->view->pagesList = $dg->render($this->view, 'controls/admin/lists/PagesStandard.phtml');
	}
	public function multiUploadFileAction()
	{	

		$res = $this->uploadFileAction(true);
		echo trim($res);
		exit();
	}
		
	public function uploadFileAction($calledFromMulti = false)
	{
	
	
		if(!$calledFromMulti){
    		echo "<textarea>";
		}
		//$err = $this->checkFormFileUpload();		
		$err = false;
		fwrite($fh, $err);
		if(!$err){ // ok	
			$uploadPath = $this->filenode->path;
			 
			if(strpos($uploadPath, 'intranet/')){
				$inIntranet = true;
				$uploadPath = '/soubory-intranetu';
			}
			
			$uploadDir = $this->config->fsRoot . $uploadPath;
			
			
			$upload = new HTTP_Upload("cz");						
			$status = "failure";		
			if($calledFromMulti){
				$file = $upload->getFiles("Filedata");	
			} else {
				$file = $upload->getFiles("f");	
			}
			
			
			
			if ($file->isValid()) {
				$file->dirToUpload = $uploadDir;
				
				$filenameFull = $file->upload['name'];
				
				$file->setName('safe');     
				
				$file->upload['ext'] = strtolower($file->upload['ext']);
				if($file->upload['ext'] == 'jpeg'){
					$file->upload['ext'] = 'jpg';
				}
				
				$parts = explode('.', $file->upload['name']); 
				array_pop($parts); 
				$name = implode('', $parts);       
				
				$file->upload['name'] = str_replace(array('.', ';'), '', $name) . round(rand(0, 100)) . '.' . $file->upload['ext'];       
				$file->mode_name_selected = true;
				  
				$pPath = $uploadPath;		  
				if($uploadPath == '/'){
					$pPath = ''; 
				} 				
				
				$fullpath = $fullpathREAL = $pPath . '/' . $file->upload['name']; 
				$uploadfileName = $file->upload['name'];
				 
				if(!$file->isMissing() && !$file->isError()){
					$disableJPGTransformNodes = $this->config->instance->disableJPGTransformNodes;
					if($disableJPGTransformNodes){
						$disableJPGTransformNodes = $disableJPGTransformNodes->toArray();
						if(in_array($this->filenode->nodeId, $disableJPGTransformNodes)){
							$this->session->disableJPGTransformTemp = true; 
						}
					}
					if($this->settings->getSettingValue('disableJPGTransform') || $this->session->disableJPGTransformTemp || $this->config->instance->bannersFolderNodeId == $this->filenode->nodeId){ 
						$this->session->disableJPGTransformTemp = false;  							
					} else { 
						$transform = array( 
							'gif', 'png' 
						); 
						$ext = strtolower(content_SFSFile::getFileExtension($file->upload['name']));
						if(in_array($ext, $transform)){
							$uploadfileName  = content_SFSFile::getFileWithouExtension($file->upload['name']). '.jpg';
						}
					}
					$fullpath = $pPath . '/' . $uploadfileName; 
				//	e($file->upload);   						
				}	
				 		 //Utils::debug($fullpath); 
				 		// Utils::debug($this->tree->getNodeByPath($fullpath, true));  
				if($this->tree->getNodeByPath($fullpath, true)){					
					if($calledFromMulti){
						$err = -250;
					} else {
						$err = 'Tento soubor již existuje';	
					}
				} else {
				
					$moved = $file->moveTo($uploadDir);					
					
					if (!PEAR::isError($moved)) {			    			
						$status = "success";			
						
					} else {
						$err = $moved->getMessage();
						//parent::addInfoMessage($moved->getMessage());
					}
				}
			} elseif ($file->isMissing()) {				
				if($calledFromMulti){
					$err = -210;
				} else {
					$err = 'Prosím vyberte soubor který chcete nahrát.';		
				} 
			} elseif ($file->isError()) {
				$err = $file->errorMsg();				
			}
			
									
			if($err == false){ // ok	 
				if($fullpath != $fullpathREAL){
					$thumb = new Thumbnail2($this->config->fsRoot . $fullpathREAL); 
					$thumb->showAsJPG($this->config->fsRoot . $fullpath, 90);     
					@unlink($this->config->fsRoot . $fullpathREAL);	  		    
				}    
				
				// pokud neni zadan nazev pouziju nazev souboru 
				if(!$this->input->fileName){
					$this->input->fileName = content_SFSFile::getFileWithouExtension($filenameFull);
				}
				
				$content = $contentName = 'content_SFSFile';
				$content = new $content();			
		    			
				
				
				$content->id = $content->getNextContentId($this->dbAdapter);;
		    	$content->localId = 1;
				$content->dateCreate = $content->dateModif = Utils::mkTime();
				$content->owner = $content->modifiedBy = $this->session->user->username;
				$content->state = 'PUBLISHED';
				$content->properties[0]->value = stripslashes($fullpath); 
				// pr($content); 	return ;
				$content->save();
				
				$n = new Node();
		    	$n->type = 'ITEM';
		    	$n->nodeId = $n->getNextNodeId($this->dbAdapter);
		    	$n->parentId = $this->nodeId;	
		    	$n->dateCreate = $n->dateModif = Utils::mkTime();
		    	$n->owner = $n->modifiedBy = $this->session->user->username;
		    		    	
		    	$n->title = $this->input->fileName;
		    	$n->description = $this->input->fileDescription;				
		    	$n->path =  $fullpath;
				
		    	if($inIntranet){
					if ($this->acl->isAllowed($this->session->user->group, 'approveIntranetFiles')){
						$n->intranetAprroved = true;
					} else {
						$n->intranetAprroved = false;
					}
				}
				    	
		    	$this->tree->addNode($n);	   
		    	
		    	$this->tree->pareNodeAndContent($n->nodeId, $content->id, $contentName);	
		    	
		    	parent::audit($fullpath,$n->nodeId);
		    	
				//$this->_redirector->goto('newcontent', 'structure', null, array('filenode' => $n->nodeId, 'subaction' => 'newContent', 'contentType' => $this->input->contentType_new));
				$status = 'success';
				if(!$calledFromMulti){
					$err = 'Soubor úspěšně nahrán';	
				} else {
					$err =  trim($this->config->sfFolder . '/' . content_SFSFile::getSFSPath($n->nodeId, $n->path));
				}					
			} else {
				$status = "failure";
			}
		} else {
			$status = "failure";
		}	
						
		
		if(!$calledFromMulti){
			// yeah, seems you have to wrap iframeIO stuff in textareas?		
			$fileUrl = $this->config->sfFolder . '/' . content_SFSFile::getSFSPath($n->nodeId, $n->path);
			return array($status, $err, false, false, $n->title, $fileUrl);
    		echo "</textarea>"; 			
		} else {
			return $err;
		}
		
	}
	
	public function uploadFileVersionAction()
	{
		$status = "failure";	
				
    	echo "<textarea>";
    	
		$parent = $this->tree->getNodeById($this->filenode->parentId);
    	$oldFileName = content_SFSFile::getFileName($this->filenode->path);
    	$oldFileExtension = content_SFSFile::getFileExtension($oldFileName);
    			
    	$uploadPath = $parent->path;				
		$uploadDir = $this->config->fsRoot . $uploadPath;	
			
		$upload = new HTTP_Upload("cz");				
		$file = $upload->getFiles("f");	
		$file->setName('safe');
		$file->upload['name'] = $oldFileName;
				
		if(strtolower($file->upload['ext']) != $oldFileExtension){
			$err = 'Nová verze musí mít stejnou příponu jako původní soubor (' . $oldFileExtension  . ').';
		} elseif ($file->isValid()) {
			
			$file->upload['ext'] = strtolower($file->upload['ext']);
			$moved = $file->moveTo($uploadDir);					

			if (!PEAR::isError($moved)) {	
				$pPath = $uploadPath;		
				if($uploadPath == '/'){
					$pPath = '';
				}					
    			$fullpath = $pPath . '/' . $file->upload['name'];
				$status = "success";					
				
			} else {
				$err = $moved->getMessage();
				//parent::addInfoMessage($moved->getMessage());
			}
			
		} elseif ($file->isMissing()) {
			$err = 'Prosím vyberte soubor který chcete nahrát.';				
		} elseif ($file->isError()) {
			$err = $file->errorMsg();
		}

		
		if($status == 'success'){
			$err = 'Nová verze souboru úspěšně nahrána';		
			$this->tree->updateNode($this->filenode); 	    		    	
			$this->filenode->getPublishedContent()->update();
	   		parent::audit($this->filenode->title, $this->filenode->nodeId);	    
		} else {
			$status = "failure";
		}
				
		// yeah, seems you have to wrap iframeIO stuff in textareas?		
		return array($status,$err);
		echo "</textarea>"; 			
	}
	
	public function newFileAction()
	{
		
		
		if($this->request->getParam('directUpload')){
			$this->view->directUpload = true;
			$this->view->directUploadCallback = $this->request->getParam('callBackInput');
		}
		parent::renderModal('controls/admin/forms/SfNewFile.phtml');
	}
	
	public function newfilemultiAction()  
	{
		
		if(count($_FILES)){
			$folderId = $this->request->getParam('filenode');
			$filename = $this->request->getParam('filename');  
			$folderId = $folderId?$folderId:2;
			// e($filename);
			$uploadPath = $this->filenode->path;
			$uploadDir = $this->config->fsRoot . $uploadPath;
			$uploadDirWeb = $this->config->sfFolder . $uploadPath;
			$pPath = $uploadPath;		    
			if($uploadPath == '/'){
				$add = '';  
			} else {
				$add = '/';   
			}
		
			$resize = $this->request->getParam('noresize') == NULL ? false : true;  

			$options = array( 
	            'folderId' => $folderId,   
	            'upload_dir' => $uploadDir.$add,  
				'upload_url' => $uploadDirWeb.$add,  
	            'user_dirs' => false,     
	            'mkdir_mode' => 0755, 
	            'param_name' => $filename,   
	            'accept_file_types' => '/.+$/i', 
	            'max_width' => 2500,
	            'max_height' => 2500,  
	            'min_width' => 1,
	            'min_height' => 1, 
	            'show_medium' => $resize, 
				'extra' => serialize($_POST)
	        );
	        
			$upload_handler = new UploadHandler($options); 
		//	pr($options)  ;
			die(); 
		}
		
		if($this->request->getParam('directUpload')){
			$this->view->directUpload = true;
			$this->view->directUploadCallback = $this->request->getParam('callBackInput');			
			$this->view->refreshTab = false;
			echo $this->view->render('controls/admin/forms/SfNewFileMulti.phtml');
		} elseif ($this->view->inVersions){
			$this->view->refreshTab = false;  
			parent::renderModal('controls/admin/forms/SfNewFile.phtml');
		} else {
			$this->view->refreshTab = true;
			echo $this->view->render('controls/admin/forms/SfNewFileMulti.phtml');
		} 
	}
	
	function newupladhandlerAction(){
		$upload_handler = new UploadHandler();   
	}  
	
	public function newFolderAction()
	{	
		//pr($this->filenode);
		parent::renderModal('controls/admin/forms/SfNewFolder.phtml');
	}
	
   
    public function makefolder()
	{ 
		$err = $this->checkFormNewFolder();		
		if(!$err){ // ok	
			
			$parentId = $this->view->parentnodeId;		
			$pp = $this->tree->getNodeById($parentId); 
			$ppath = $pp->path;
			    
			
			$n = new Node();
	    	$n->type = 'FOLDER';
	    	$n->title = $this->input->newfoldername;
	    	$n->description = $this->input->folder_descr; 
	    	
	    	
	    	if($ppath == '/'){
	    		$n->path =  '/'. Utils::generatePathName($this->input->newfoldername, '', '/');
	    	} else {
	    		$n->path =  $ppath . '/'. Utils::generatePathName($this->input->newfoldername, '', $ppath . '/');
	    	}
	    	/*
	    	if($this->filenode->nodeId > 2){
	    		$n->path = $this->filenode->path .'/'. Utils::generatePathName($this->input->newfoldername,'',$this->filenode->path . '/');
	    	} else {
	    		$n->path = '/'. Utils::generatePathName($this->inputGet->newfoldername);
	    	} */
	    	   
	    	$err = Folder::makeFolder($this->config->fsRoot . $n->path );
			if(strlen($err)){
				return array(0,$err);   
				return;
			}
			
	    	$n->nodeId = $n->getNextnodeId($this->dbAdapter);
	    	$n->parentId = $parentId;	    	
	    	$n->dateCreate = $n->dateModif = Utils::mkTime();
	    	$n->owner = $n->modifiedBy = $this->session->user->username;	    	    	
	    	  	
	    	$this->tree->addNode($n, true); 
	    	parent::audit($n->path, $n->nodeId);
	    	
	    	$url = $this->view->url(array('action' => 'index', 'filenode' => $n->nodeId));
	    	 
			return array(1, 'Data uložena', $url);
			
		} else {  
			return array(0,$err); 
		}				
	}
	
	public function checkFormNewFolder()
	{		
		do{	
			if(!$this->input->newfoldername){
				$err = "Zadejte titulek";			   
			    break;
			}			
			if(!isset($this->view->parentnodeId)){
				$err = "Zadejte nadřazený uzel";			   
			    break;
			}
			
			return false;
		} while (false);			
		return $err;
	}
	
	public function checkFormFileUpload()
	{		
		do{	
			if(!$this->input->fileName){
				$err = "Zadejte titulek";			   
			    break;
			}			
			
			return false;
		} while (false);			
		return $err;
	}
}
