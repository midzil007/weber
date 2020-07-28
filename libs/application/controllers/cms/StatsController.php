<?php
/*
Statistiky


Statistika
1, Využívanost administrace
Klasický krivkový graf  (možná i s cplochou pod krivkou)
Aktivita uživatelů (title: počet uživatelských akcí za den) – jen akce při kterých něco udělali mimo login
Přístupy (title: počet přihlášení za den)

2, Aktivita uživatelů  (monitorovat jen ty kteří mohou něco delat – asi „uživatelé administrace“)
Koláčový graf
Aktivní uživatelé (title: 34 uživatelů použilo administraci (za posledních 10 dnů))
Pasivní uživatelé (title: 13 uživatelů v posledních 10 nech vůbec nepoužilo administraci)

3, rozsah webu
Křivkový graf (ukazující celou časovou osu), nebo sloupcový graf ukazující aktuální stavy
Sekce (title: počet sekcí webu)
obsah (title: celkový počet stránek na webu) – počítej vše co je v obsahu
soubory (title: počet souborů v modulu soubory)
velikost webu (MB) (title: celková velikost webových stránek a administrace)
Aktivní akce:
Nadcházející akce v kalendáři

*/
class Cms_StatsController extends CmsController
{
	public function init()
	{
		parent::init();

		if($this->doPageInit){
			$this->initPage();
		}
	}

	private function initPage()
	{		
		$this->view->title .= ' - Statistiky a přehledy';
		$this->template = 'controls/admin/Stats.phtml';

		$this->view->selectedLeftHelpPane = false;
		$this->view->showTree = false;
						
		$this->view->leftPanes[] = array(
			'title' => 'Statistiky stránek',
			'id' => 'statsPaneWeb',
			'class' => '',
			'url' => $this->view->url(array('action' => 'showWebWidget'))		
		);
		
		$this->view->leftPanes[] = array(
			'title' => 'Statistiky systému',
			'id' => 'statsPane',
			'class' => '',
			'url' => $this->view->url(array('action' => 'showWidget'))		
		);
		
		
		 
		$this->initHelp('stats');   
		
		$this->view->leftColl = $this->view->render('parts/leftStats.phtml'); 
		
	}

	public function indexAction()
	{
		//$this->contentsauditAction(); 
		$this->userauditAction();
		parent::indexAction($this->template);

	}

	public function showWidgetAction()
	{
		echo $this->view->render('/controls/admin/forms/StatsWidget.phtml');
	}
	
	public function showWebWidgetAction()
	{
		echo $this->view->render('/controls/admin/forms/StatsWebWidget.phtml');
	}


	public function homeAction()
	{
		echo $this->view->render('/controls/admin/forms/StatsHome.phtml');
	}


	public function userauditAction()
	{
		//$this->systemUsers);
		$users = new Users(); 
		
		$getItems = $this->request->getParam('getItems');
		$isAjax = $this->request->getParam('ajax');
		
		
		$dg = new DataGrid('customersTable');
		$dg->setRefresUrl($this->view->url(array('action' => 'useraudit', 'getItems' => 1, 'ajax' => 1)))
			->setTitle('Audit uživatelských akcí')  
			->setHeight(400) 
			->setItemsPerPageOptions('10, 15, 20, 30, 50, 500', 50) 
			; 
			
		$dg->setHeaders(
			array(
				array('Modul', 'controller', 150, 'true', 'left', 'false'),
				array('Akce', 'action', 120, 'true', 'left', 'false'), 
				array('Čas', 'time', 100, 'true', 'left', 'false')  
			)
		)->setSearchableColls(   
			array(     
				array('Akce', 'action', 'true')  
			)
		)->setButtons( 
			array(    
			)
		); 
		 
		
		$this->view->defaultSort = 'time'; 
		$this->view->defaultSortType = 'DESC'; 
		$this->view->input->filter1 = $this->session->user->username;   
		
		$this->view->filter1 = helper_Input::addNotDefinedOption($users->getUsersSelect(), 'Všichni uživatelé', 0 );
			 
		  
		if($getItems){ 
			
			$sort = 'time';
			$sortType = 'DESC'; 
			
			list($currentPage, $rp, $sortname, $sortorder, $query, $qtype, $start) = $dg->getParams('time', 'DESC');
			
			$user = $this->input->filter1;

			
				$audit = $this->audit->getUserAudit(  
					$user,  
					$sortname,
					$sortorder, 
					$start,
					$rp
				); 
				$rowsTotalCount = $this->audit->getUserAuditCount($user);
				  
			  
			
			$rowsFormated = array();
			foreach ($audit as $a){ 
				
				$entry = array(
						'id'=> 0,
						'cell' => $a 
				);
				
				$rowsFormated[] = $entry;
			}  
			
			 
			if($isAjax){ 
				$dg->renderAjax($currentPage, $rowsTotalCount, $rowsFormated);
				die();  
			};
		}
 
		$this->view->userAuditTableActions = array();  
 	 	
		$this->view->renderFilter = 'controls/admin/lists/filter/ListStatsAudit.phtml'; 
    	$this->view->statsUserHistory = $dg->render($this->view, 'controls/admin/lists/PagesStandard.phtml');  
    	  
		 
	}
	 

	public function nodeauditAction()
	{
		//$this->systemUsers);
		$nodeToAudit = $this->request->getParam('node');
		$this->view->tableParentTab = 'nodeHistory';
		$this->view->disableForm = true;

		$this->view->curentViewState['action'] = 'nodeAudit';
		$this->view->tableSort = $this->request->getParam('sort')?$this->request->getParam('sort'):'time';
		$this->view->tableSortType = $this->request->getParam('sortType')?$this->request->getParam('sortType'):'Desc' ;
		$this->view->tableFilter0 = $this->request->getParam('tableFilter0')?$this->request->getParam('tableFilter0'):'0';

		$this->view->tableFilters = array();

		$this->view->nodeAuditTable = $this->audit->getNodeAudit(
		$nodeToAudit,
		$this->view->tableSort,
		$this->view->tableSortType
		);

		//pr($this->view->nodeAuditTable);

		$this->view->nodeAuditTableHead = array(
		'username' => array(
		'title' => 'Uživatel',
		'atribs' => array(),
		'sortUrlType' => 'refresh-tab',
		'parentTab' => 'statsUsers'
		),
		'controller' => array(
		'title' => 'Modul',
		'atribs' => array(),
		'sortUrlType' => 'refresh-tab',
		'parentTab' => 'statsUsers'
		),
		'action' => array(
		'title' => 'Akce',
		'atribs' => array(),
		'sortUrlType' => 'refresh-tab',
		'parentTab' => 'statsUsers'
		),
		'time' => array(
		'title' => 'Čas',
		'atribs' => array(),
		'sortUrlType' => 'refresh-tab',
		'parentTab' => 'statsUsers'
		)
		);

		$this->view->nodeAuditTableActions = array();

		echo $this->view->render('/controls/admin/forms/StatsNodeActions.phtml');
	}

	public function fileauditAction()
	{

		$this->view->tableDonotShowOrder = true;

		$this->view->tableParentTab = 'statsHome';

		$this->view->curentViewState['action'] = 'fileAudit';
		$this->view->tableSort = $this->request->getParam('sort')?$this->request->getParam('sort'):'time';
		$this->view->tableSortType = $this->request->getParam('sortType')?$this->request->getParam('sortType'):'' ;
		$this->view->tableFilter0 = $this->request->getParam('tableFilter0')?$this->request->getParam('tableFilter0'):'0';

		$this->view->tableFilters = array();

		$this->view->fileAuditTable = $this->audit->getFileAudit();

		//pr($this->view->userAuditTable);

		$this->view->fileAuditTableHead = array(
		'count' => array(
		'title' => 'Počet stažení',
		'atribs' => array('style' => 'width:90px;'),
		'sortUrlType' => 'refresh-tab',
		'parentTab' => 'statsUsers'
		),
		'path' => array(
		'title' => 'Název souboru',
		'atribs' => array(),
		'sortUrlType' => 'refresh-tab',
		'parentTab' => 'statsUsers'
		)
		);

		$this->view->fileAuditTableActions = array();
		
 
		$this->view->pageContent = $this->view->render('/controls/admin/forms/StatsFiles.phtml');
		
		parent::indexAction($this->template);
	}

	public function webusersAuditAction()
	{
		//pr($Dataset);
		//$webusers = new module_WebUsers();
		//module_WebUser::$sector
		//module_WebUser::$region
		/*
		$g = new Graph();
		$g->init(400,400);


		$g->draw();

		return ;
		$all = $webusers->getUsers();
		*/
		/*

		$Graph = new Image_Graph(800, 600);
		$Plotarea =& $Graph->add(new Plotarea());
		$Plot =& $Plotarea->addPlot(new Image_Graph_Plot_Line(
		new Image_Graph_Dataset_Random(10, 20, 100))
		);
		$Graph->done();

		//pr($all);
		*/
		echo $this->view->render('/controls/admin/forms/StatsWebUserActions.phtml');
	}

	public function webusersCountGraphAction()
	{
		// create the graph
		$Graph =& Image_Graph::factory('graph', array(400, 250));
		// add a TrueType font
		$Font =& $Graph->addNew('ttf_font', SERVER_ROOT.'/Fonts/Arial.ttf');
		// set the font size to 11 pixels
		$Font->setSize(8);

		$Graph->setFont($Font);


		$Graph->add(
		Image_Graph::vertical(
		Image_Graph::factory('title', array('Počet registrovaných uživatelů po měsících', 8)),
		Image_Graph::vertical(
		$Plotarea = Image_Graph::factory('plotarea'),
		$Legend = Image_Graph::factory('legend'),
		90
		),
		5
		)
		);

		//$Legend->setPlotarea($Plotarea);

		// create the dataset
		$wu = $this->db->fetchOne("
			SELECT count( * ) AS pocet
			FROM `module_WebUsers`
			WHERE `active` = '1'
			GROUP BY DATE_FORMAT( `added` , '%m' ) "
			);

			$Dataset =& Image_Graph::factory('dataset');
			$Dataset->addPoint('Listopad', $wu);
			$Dataset->addPoint('Prosinec', $wu);
			$Dataset->addPoint('Leden', $wu);
			$Dataset->addPoint('Únor', 0);
			$Dataset->addPoint('Březen', 0);
			$Dataset->addPoint('Duben', 0);
			/*

			*/

			// create the 1st plot as smoothed area chart using the 1st dataset
			$Plot =& $Plotarea->addNew('bar', array(&$Dataset));

			// set a line color
			$Plot->setLineColor('gray');

			// set a standard fill style
			$Plot->setFillColor('skyblue@0.2');

			// output the Graph
			$Graph->done();
	}
	
	
	public function contentsGraphAction()
	{
		// create the graph
		$Graph =& Image_Graph::factory('graph', array(400, 250));
		// add a TrueType font
		$Font =& $Graph->addNew('ttf_font', SERVER_ROOT.'/Fonts/Arial.ttf');
		// set the font size to 11 pixels
		$Font->setSize(8);

		$Graph->setFont($Font);


		$Graph->add(
		Image_Graph::vertical(
		Image_Graph::factory('title', array('Přehled obsahů v systému', 8)),
		Image_Graph::vertical(
		$Plotarea = Image_Graph::factory('plotarea'),
		$Legend = Image_Graph::factory('legend'),
		90
		),
		5
		)
		);

		//$Legend->setPlotarea($Plotarea);

		
		$data = $this->audit->getContentsStats();
	
		$Dataset =& Image_Graph::factory('dataset');
		$Dataset->addPoint('počet sekcí', $data['sectionsCount']);
		$Dataset->addPoint('počet stránek', $data['pagesCount']);
		$Dataset->addPoint('počet souborů', $data['filesCount']);
		$Dataset->addPoint('počet uživatelů', count($this->systemUsers));

		// create the 1st plot as smoothed area chart using the 1st dataset
		$Plot =& $Plotarea->addNew('bar', array(&$Dataset));

		// set a line color
		$Plot->setLineColor('gray');

		// set a standard fill style
		$Plot->setFillColor('skyblue@0.2');

		// output the Graph
		$Graph->done();
	}

	public function contentsauditAction() 
	{
		//$this->systemUsers);
		$users = new Users();
		$getItems = $this->request->getParam('getItems');
		$isAjax = $this->request->getParam('ajax');
		
		$this->view->defaultSort = 'day'; 
		
		$dg = new DataGrid('contentsAuditTable');
		$dg->setRefresUrl($this->view->url(array('action' => 'contentsaudit', 'getItems' => 1, 'ajax' => 1)))
			->setTitle('Publikace po dnech') 
			->setHeight(400) 
			->setItemsPerPageOptions('10, 15, 20, 30, 50, 500', 50)  
			; 
			
		$dg->setHeaders( 
			array(  
				array('Den', 'day', 150, 'true', 'left', 'false'), 
				array('Počet', 'c', 100, 'true', 'left', 'false') 
			)
		)->setSearchableColls(   
			array(     
				array('Den', 'day', 'true')  
			)
		)->setButtons( 
			array(  
			)
		); 
		  
		$contentTypes = array_merge($this->config->overviewTypes->toArray(), $this->config->contentTypes->toArray());  
		
		$this->view->tableFilter0 = $this->input->filter1?$this->input->filter1:date('m');
		$this->view->tableFilter1 = $this->input->filter2?$this->input->filter2:date('Y');
		$this->view->tableFilter2 = $this->input->filter3?$this->input->filter3:'0';
		if($this->input->filter2 != ''){  
			$this->input->filter3 = $this->input->filter2;
		} else { 
			$this->input->filter2 = 1;
		} 
		 
		$this->view->tableFilter4 = $this->input->filter4?$this->input->filter4:'0';  		
		$this->view->tableFilter5 = $this->input->filter5?$this->input->filter5:key($contentTypes);  
 		  
		$this->view->filter1 = helper_Input::getMonthsSelecDataLeden();
		$this->view->filter2 = helper_Input::getDateBornSelectData(0, 5); 		 
		$this->view->filter3 = array_merge(array('0' => 'Vyberte uživatele'), $users->getUsersSelect());
		$this->view->filter4 = array('1' => 'Pouze publikované', '0' => 'Všechny'); 
		// $this->view->tableFilters[] = helper_Input::addNotDefinedOption($this->view->domains, 'Všechny domény', '0');  
		$this->view->filter5 = $contentTypes;  
		  
		if($getItems){ 	
				 
			list($currentPage, $rp, $sortname, $sortorder, $query, $qtype, $start) = $dg->getParams('day', 'desc');
			$sortname = $sortname ? $sortname : 'day';  
			
			// return array(); 
			$month = $this->view->tableFilter0;  
			$year = $this->view->tableFilter1; 
			$time = mktime(date("H"), date("i"), date("s"), $month, 1, $year);   
			$daysInMonth = date('t', $time);
			$days = range(1, $daysInMonth);   
			$this->view->userAuditTable = array(0 => 0);
			$countAll = 0;
			$rowsFormated = array();
			foreach ($days as $day){ 
				$date = "$year-$month-$day"; 
				$dateUser = "$day. $month. $year"; 
				$count = $this->audit->getStatsByContent('content_' . $this->view->tableFilter5 , $date, 'dateCreate', 'DESC', $this->view->tableFilter3, $this->view->tableFilter2, false, 1);   
				
				$entry2 = array(
					$dateUser, 
					$count
				);
				   
				 
				$entry = array(
						'id'=> $day,
						'cell' => $entry2 
				);
				
				$rowsFormated[] = $entry;
				
				$countAll += $count;
			} 
			  
			$this->view->userAuditTable[0] = array( 
				'CELKEM', 
				$countAll 
			); 	 

			$rowsTotalCount = 1; 
			if($isAjax){ 
				$dg->renderAjax($currentPage, $rowsTotalCount, $rowsFormated);
				die();  
			};
		}  
		 
		$this->view->renderFilter = 'controls/admin/lists/filter/ListContentsAudit.phtml'; 
    	$this->view->statsPublicated = $dg->render($this->view, 'controls/admin/lists/PagesStandard.phtml');  
    	  
	}
}
