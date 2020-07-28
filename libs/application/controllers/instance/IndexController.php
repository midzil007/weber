<?php
class IndexController extends Zend_Controller_Action {
	protected $_flashMessenger = null;

	private $_excetionPage =
	array(
		'Mé objednávky', 'e-shop', 'Emaily - neveřejné', 'heureka', 'Rychlý kontakt:', 'Patička', 'Štítky', 'Platba kartou', 'Validation',
	);  

	public function init() {
		$this->view     = $this->getInvokeArg('view');
		$this->registry = Zend_Registry::getInstance();
		$this->request  = $this->view->request  = $this->getRequest();
		$this->config   = $this->view->config   = $this->registry->config;
		$this->session  = new Zend_Session_Namespace('WEB07');
		$this->session->setExpirationSeconds($this->config->sessionWebAlive);
		//  $this->audit = new Audit();
		$this->dbAdapter = $this->db = $this->registry->db;
		$this->filter    = new Zend_Filter_StripTags();
		$this->acl       = $this->view->acl       = $this->getInvokeArg('acl');

		$this->registry               = Zend_Registry::getInstance();
		$this->tree                   = $this->view->tree                   = $this->registry->tree;
		$this->registry->config       = $this->view->config       = $this->config;
		$this->registry->session      = $this->view->session      = $this->session;
		$this->view->logged           = $this->session->logged;
		$this->view->curentAction     = $this->request->action;
		$this->view->curentController = $this->request->controller;
		$this->view->curentModule     = $this->request->module;
		// helpers
		$this->_flashMessenger      = $this->_helper->getHelper('FlashMessenger');
		$this->registry->redirector = $this->_redirector = $this->_helper->getHelper('Redirector');
		$this->_redirector->setUseAbsoluteUri();

		$this->view->addHelperPath(SERVER_ROOT.'/application/views/helpers/Web/', 'Web_View_Helper_');
		$this->view->addHelperPath(LIBS_ROOT.'/application/views/helpers/Web/', 'Web_View_Helper_');
		$this->view->addHelperPath(SERVER_ROOT.'/application/views/helpers/cms/', 'Cms_View_Helper_');
		$this->view->addHelperPath(LIBS_ROOT.'/application/views/helpers/cms/', 'Cms_View_Helper_');

		//inputs
		$this->initFormValuesFromGet();
		$this->initFormValuesFromPost();

		//view
		$this->view->baseUrl = $this->_request->getBaseUrl();

		// aktualni cesta
		$path                = Utils::parsePath($this->request->getRequestUri());
		$this->curentPath    = $this->view->curentPath    = $this->view->requestUrl    = $path;
		$this->view->path    = helper_FrontEnd::getPath($this->view, $this->curentPath);
		$this->renderContent = true;

		// jazyky webu
		$this->languages = $this->view->languages = $this->registry->languages = new module_languages_Languages($this->view);
		$this->language  = $this->view->language  = $this->registry->language  = $this->languages->getCurentLanguage();

		$this->registry->settings = $this->view->settings = new Settings();

		if ($_SERVER['REQUEST_URI'] == '/sitemap.xml') {
			$sitemap = new module_XMLSiteMap();
			$sitemap->render($view);
			die;
		}

		if ($_SERVER['REQUEST_URI'] == '/jura/sitemap.xml') {
			$sitemap = new module_XMLSiteMap();
			$sitemap->render($view);
			die;
		}

		if ($_SERVER['REQUEST_URI'] == '/jura/sitemapimage.xml') {
			$sitemap = new module_XMLSiteMap();
			$sitemap->renderImage($view);
			die;
		}

		// instance
		$this->instance = $this->view->instance = $web = new Instance($this);
		$web->init();

		//redirects
		if ($this->view->inputGet->redirect) {
			$stats = new module_Advertising_AdvertStats();
			$stats->bannerAction($this->view->inputGet->b, 'clicked');
			$fp    = $_SERVER['REQUEST_URI'];
			$parts = explode('&amp;redirect=', $fp);

			$this->view->inputGet->redirect = $parts[1];
			helper_FrontEnd::redirect($this->view->inputGet->redirect, 301);
		}
		// kvůli &amp;
		if ($this->view->inputGet->b) {
			$stats = new module_Advertising_AdvertStats();
			$stats->bannerAction($this->view->inputGet->b, 'clicked');
			$fp    = $_SERVER['REQUEST_URI'];
			$parts = explode('&amp;redirect=', $fp);

			$this->view->inputGet->redirect = $parts[1];
			helper_FrontEnd::redirect($this->view->inputGet->redirect, 301);
		}

		// TODO tyhle stromy pak prijdou dat nekam jinam, nebo smazat
		/**
		 * @param $nameTag variable name for the name of node ('name' or 'title')
		 */
		function renderTreeXML($node, $nameTag = "name") {
			foreach ($node as $item) {
				echo "<item id=\"{$item['id']}\">";
				echo "<content><name><![CDATA[{$item[$nameTag]}]]></name></content>";
				renderTreeXML($item['children'], $nameTag);
				echo "</item>";
			}
		}

		if ($this->view->inputGet->zboziczTree == 1) {
			// 6430 id of root node for zbozicz (table Nodes)
			$zboziczTree = $this->tree->getTree(6430, $deep = true);

			header("Content-Type:text/xml");
			echo "<root>";
			renderTreeXML(array($zboziczTree), 'title');
			echo "</root>";
			die;
		}
		if ($this->view->inputGet->zboziczTree == 2) {
			echo $this->view->render("parts/treeTestLazyZbozicz.phtml");
			die;
		}
		if ($this->view->inputGet->heurekaTree) {
			$tree = new module_HeurekaTree();
			/*$parsedData = $tree->loadData();
			$tree->rewriteDb($parsedData);*/

			//$parsedData = $tree->loadTree("0");
			//print_r($parsedData);
			/*foreach ($parsedData as $a){
			print_r($a);
			break;
			}*/
			if ($this->view->inputGet->heurekaTree == 2) {
				echo $this->view->render("parts/treeTestLazy.phtml");
				die;
			}
			if ($this->view->inputGet->heurekaTree == 3) {
				$data = $tree->getSubTrees(array(971, 1513));
				renderTree1234($data, false);
				die;
			}
			if ($this->view->inputGet->heurekaTree == 4) {
				$data = $tree->getSubTrees(array(971, 1513));
				header("Content-Type:text/xml");

				echo "<root>";
				renderTreeXML($data);
				echo "</root>";
				die;
			}
			//$this->view->data = array( "971" => $parsedData[971] );
			$this->view->data = $tree->getSubTrees(array(971, 1513));
			echo $this->view->render("parts/treeTest.phtml");
			//print_r($parsedData);
			die;
		}
		// END zbozi,heureka stromky

		$this->preparePage($web);
		$web->postInit();

		if ($_GET['profillerEnableXX']) {
			$profiler     = $this->db->getProfiler();
			$totalTime    = $profiler->getTotalElapsedSecs();
			$queryCount   = $profiler->getTotalNumQueries();
			$longestTime  = 0;
			$longestQuery = null;

			foreach ($profiler->getQueryProfiles() as $query) {
				if ($query->getElapsedSecs() > $longestTime) {
					$longestTime  = $query->getElapsedSecs();
					$longestQuery = $query->getQuery();
				}
				// var_dump($query->getQuery());
			}

			echo 'Executed '.$queryCount.' queries in '.$totalTime.
			' seconds'."\n <br />";
			echo 'Average query length: '.$totalTime/$queryCount.
			' seconds'."\n <br />";
			echo 'Queries per second: '.$queryCount/$totalTime."\n <br />";
			echo 'Longest query length: '.$longestTime."\n <br />";
			echo "Longest query: \n".$longestQuery."\n <br />";
		}
	}

	public function preparePage($instance = null) {

		if (!$this->view->pageTitle) {
			$this->view->pageTitle = $this->config->instance->title;
		}

		$s = Zend_Registry::getInstance()->settings;

		if ($s->getSettingValue('domainPageTitle')) {
			$this->view->pageTitle = $s->getSettingValue('domainPageTitle');
		} 

		$view->webTitle     = $this->view->pageTitle;
		$this->view->webUrl = Utils::getWebUrl();
		// aktualni uzel
		$this->view->curentNode = $this->tree->getNodeByPath($this->view->requestUrl);
		

	 	if($this->view->curentNode->nodeId == 74515)      //   Kávovar JURA IMPRESSA Z6  
	 	{
	 		helper_FrontEnd::redirect(Utils::getWebUrl()."/jura/e-shop/kavovar-jura-impressa-z6-aluminium", 301);
	 	}    
  
  
		list($path, $qs) = explode("?", $_SERVER["REQUEST_URI"], 2);
		parse_str($qs, $urlvars);
		if ($path == "/jura/") {
			helper_FrontEnd::redirect(Utils::getWebUrl()."/jura", 301);
			exit;
		}
		// test na lomítko na konci
		if (substr($path, -1) == "/") {
			$u                      = substr_replace($path, "", -1);
			$this->view->curentNode = $this->tree->getNodeByPath($u);
			$params                 = $qs?"?".$qs:"";
			if ($this->view->curentNode) {
				helper_FrontEnd::redirect(Utils::getWebUrl().$this->view->curentNode->path.$params, 301);
			}
		}
		
		   
		if(!$this->view->showPrice && $_SESSION['CMS7']['logged'] != 1 && $this->view->curentNode->nodeId ==75717){
				die;   // quick  
			header('HTTP/1.0 410 Not Found');
		
			$this->view->h1Title   = 'Chyba -stránka neexistuje';
			$this->view->page      = $this->view->render("parts/error.phtml");
			$this->view->container = $this->config->view->containerDir.'page.phtml';  
		} 

		if (!$this->view->curentNode || in_array($this->view->curentNode->title, $this->_excetionPage)) {
			header('HTTP/1.0 410 Not Found');
			$this->view->h1Title   = 'Chyba -stránka neexistuje';
			$this->view->page      = $this->view->render("parts/error.phtml");
			$this->view->container = $this->config->view->containerDir.'page.phtml';
		} else {
			$this->view->containerName = $this->view->curentNode->getTemplate();
			$this->view->container     = $this->config->view->containerDir.$this->view->containerName.'.phtml';

			$seoTitle                    = $this->view->curentNode->getPropertyValue('pageTitle');
			$this->view->h1Title         = $this->view->curentNode->title;
			$this->view->pageKw          = $this->view->curentNode->getPropertyValue('pageKw');
			$this->view->pageDescription = $this->view->curentNode->getPropertyValue('pageDescription');

			if (isset($this->view->curentNode->showH1) && $this->view->curentNode->showH1 < 1) {
				$this->view->disableH1 = true;
			}

			if (strlen($this->view->pageDescription) < 4) {
				$this->view->pageDescription = $this->view->h1Title;
			}
			$this->view->pageTitleRaw = ($seoTitle?$seoTitle:$this->view->h1Title);

			$curentContentName = $this->view->curentNode->getPublishedContent()->_name;

			// aktualni content

			if ($this->view->inputGet->adm7 || $this->session->adm7 = 1) {
				$publishedContent    = $this->view->content    = $this->view->curentNode->getTheRightContent();
				$this->session->adm7 = 1;
			} else {
				$publishedContent = $this->view->content = $this->view->curentNode->getTheRightContent();
			}

			if ($instance) {
				if (method_exists($instance, 'preRender')) {
					$instance->preRender();
				}
			}
			//try {
			if (!is_object($publishedContent)) {
				helper_FrontEnd::redirect(Utils::getWebUrl().''.$m605->path, 301);
				exit();

			}
			$this->view->page = $publishedContent->show($this->view, $this->view->curentNode);
			if ($this->inputGet->vyhledat && $this->curentPath != '/vyhledavani') {
				$pattern          = '(>[^<]*)('.quotemeta($this->inputGet->vyhledat).')';
				$replacement      = '\\1<span class="result">\\2</span>';
				$this->view->page = eregi_replace($pattern, $replacement, $this->view->page);
			}

			if (strpos($this->view->page, '</h1>')) {
				$this->view->disableH1 = true;
			}

			if ($s->getSettingValue('showDomainPageTitle') != 1) {
				$this->view->disableDomainNameTitle = true;
			}

			if ($this->view->disableDomainNameTitle == true) {
				$this->view->pageTitle = $this->view->pageTitleRaw;
			} elseif ($this->view->disablePageTitle == true) {

			} else {
				if ($this->view->pageTitleRaw) {
					$this->view->pageTitle = $this->view->pageTitleRaw.' &raquo; '.$this->view->pageTitle;

				} else {
					$this->view->pageTitle = $this->view->pageTitle;
				}
			}
			if ($this->view->inputGet->search) {
				$this->view->pageTitle .= ': '.$this->view->inputGet->search;
			}

			/*
		} catch (Exception $e) {
		$this->view->page = $this->view->render("parts/apperror.phtml");
		}
		 */
		}

	}

	public function indexAction() {
		if ($this->renderContent) {
			if ($this->view->indexTemplate) {
				echo $this->view->render($this->view->indexTemplate);
			} elseif ($this->view->isMobile) {
				echo $this->view->render('indexMobile.phtml');
			} else {
				echo $this->view->render('index.phtml');
			}
		}
	}

	public function initFormValuesFromPost() {
		foreach ($_POST as $f => $val) {
			$this->input->{ $f} = helper_Security::secureInput($this->request->getPost($f), false);
		}
		$this->view->input = $this->input;
	}

	public function initFormValuesFromGet() {
		foreach ($_GET as $k => $val) {
			$this->inputGet->{ $k} = helper_Security::secureInput(($_GET[$k]), true);
		}
		$this->view->inputGet = $this->inputGet;
	}

}
