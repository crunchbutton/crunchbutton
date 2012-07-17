<?php 

/**
 * Cana application class
 * 
 * @author		Devin Smith <devin@cana.la>
 * @date		2009.09.17
 * 
 *
 * This is the main application that is called by all controllers. The Cana class
 * is accesed staticly and used as a global application object. Upon a request, this
 * class routes the request to the proper controler file and class. The controllers
 * create and access the object models, populate them with data, and hand them off to
 * the Cana::view() object. This object is accessable via $this from within the
 * view phtmls.
 *
 */

class Cana_App extends Cana_Model {
	
	/**
	 * Private variables all have public accessor methods
	 */
	private $_db;
	private $_view;
	private $_config;
	private $_auth;
	private $_pages;
	private $_page;
	private $_constant;
	private $_env = 'live';
	private $_acl;
	private $_browser;
	private $_extended = array();
	
	public function init($params = null) {

		if (!is_null($params['config'])) {
			$this->_config = $params['config'];
		}
		
		// no host because its cli
		if (!isset($_SERVER['HTTP_HOST'])) {
			$_SERVER['HTTP_HOST'] = 'cli';
		}

		$this->_env = isset($params['env']) ? $params['env'] : 'live';

		// set up default timezone for strict data standards
		date_default_timezone_set($this->_config->timezone);
		
		try {
			$this->buildDb($this->_env);
		} catch (Exception $e) {
			// @todo: add gracefull db error
			echo 'Could not connect to the database';
			exit;
		}

		if (!isset($params['postInitSkip'])) {
			$this->postInit($params);
		}
	}
	
	/**
	 * Method to build display related config
	 */
	public function postInit($params) {
		$this->buildBrowser();
		$this->buildView(array('layout' => $this->_config->defaults->layout));
		$this->buildPages();
	}
		
	/**
	 * Method to build out the almost useless browser object
	 */
	public function buildBrowser() {
		$this->_browser = new Cana_Browser;
		return $this;
	}
	
	
	/**
	 * Method to build out the view object
	 */
	public function buildView($params = array()) {
	
		$params['base'] = $this->config()->dirs->view;
		
		$this->view(new Cana_View($params));
		
		$headers = array();
		$this->view()->headers->http = $headers;
		$this->view()->useFilter($this->_config->viewfilter);
		
		return $this;
	}
	
	
	/**
	 * Explode out the request params.
	 * change this function if modifying the htaccess config for bb
	 */
	public function buildPages($page = null) {
		// will not be set in crons and scripts
		if (!is_null($page)) {
			$this->pages(explode('/',$page));
		} elseif (isset($_REQUEST['__url'])) {
			$this->pages(explode('/',$_REQUEST['__url']));
		} else {
			$this->pages(array());
		}
		$this->view()->pages = $this->pages();
		$this->config()->controllerStack[] = $this->config()->defaults->version;
		
		return $this;
	}
	
	
	/**
	 * Set up the database connection
	 */
	public function buildDb($connection = 'live') {
		$connect = $this->_config->db->{$connection};

		if ($connect->encrypted) {
			$connect->user = $this->crypt()->decrypt($connect->user);
			$connect->pass = $this->crypt()->decrypt($connect->pass);
		}
		
		$this->_db = new Cana_Db($connect);
		
		return $this;
	}
	
	
	/**
	 * Create a new auth object that contains the users auth info
	 */
	public function buildAuth() {
		$this->_auth = new Cana_Auth;
		return $this;
	}
	
	/**
	 * Create a new acl object
	 */
	public function buildAcl() {
		$this->_acl = new Cana_Acl;
		return $this;
	}
	
	
	/**
	 * Set or return the page (controller/action)
	 */
	public function page($page = null) {
		if (!is_null($page)) {
			$this->_page = $page;
		}
		$this->displayPage();
		return $this;
	}
	
	
	/**
	 * Display a page
	 * This is how we parse the request string and determine which filename to include.
	 * /project/tasks will map to /controllers/projectTasks.php and call projectTasks class.
	 *
	 * @param	string		the page to load
	 */
	public function displayPage($page=null) {
		if (!isset($this->page)) {
			$this->_page = new Cana_Model;
		}

		if (!is_null($page)) {
			$pageClass = $page;
		} else {
			foreach ($this->pages() as $peice) {
				if (!isset($pageClass)) {
					$pageClass = $peice;
				} else { 
					$pageClass .= ucfirst($peice);
				}
			}
		}

		$this->view()->page = $pageClass;
		//$this->config()->controllerStack = array_reverse($this->config()->controllerStack);
		$this->includeFile($pageClass);
		$pageClass = explode('/',$pageClass);
		
		foreach ($pageClass as $posiblePage) {
			$posiblePages[] = 'Controller'.$fullPageNext.'_'.str_replace('.','_',$posiblePage);
			$fullPageNext .= '_'.$posiblePage;
		}	
		$posiblePages = array_reverse($posiblePages);

		foreach ($posiblePages as $posiblePage) {
			if (class_exists($posiblePage, false)) {
				$this->_page->controller = new $posiblePage;
				if (method_exists($posiblePage, 'init')) {
					$this->_page->controller->init();
				}
			}
		}

		return $this;
	}
	
	public function includeFileError($pageClass) {
		$this->view->headers->http[] = array(
			'value'		=> 'HTTP/1.0 404 Not Found'
		);
		$this->view()->display('error/404');
	}

	
	public function includeFile($pageClass) {
		$pageClass = explode('/',$pageClass);

		foreach ($pageClass as $posiblePage) {
			$posiblePages[] = $fullPageNext.'/'.$posiblePage.'.php';
			$posiblePages[] = $fullPageNext.'/'.$posiblePage.'/index.php';
			$fullPageNext .= '/'.$posiblePage;
		}	
		$posiblePages = array_reverse($posiblePages);

		foreach ($this->config()->controllerStack as $controller) {
			foreach ($posiblePages as $posiblePage) {
				if (file_exists($this->config()->dirs->controller.$controller.$posiblePage)) {
					$this->_page->fileName = $this->config()->dirs->controller.$controller.$posiblePage;
					break;
				}
			}
			if ($this->_page->fileName) break;
		}


		if (!isset($this->_page->fileName) || !file_exists($this->_page->fileName)) {
			$this->includeFileError($pageClass);
		} else {
			require_once $this->_page->fileName;
		}
		return $this;
	}
	
	
	public function crypt($crypt = null) {
		if (is_null($crypt)) {
			return $this->_crypt = new Cana_Crypt(mb_convert_encoding($this->config()->crypt->key,'7bit'));
		} else {
			return $this->_crypt;
		}
	}
	
	
	/**
	 * Accessor methods
	 */
	public function db() {
		return $this->_db;
	}

	public function acl($acl = null) {
		if (is_null($acl)) {
			return $this->_acl;
		} else {
			$this->_acl = $acl;
			return $this;
		}
	}
	
	public function auth($auth = null) {
		if (is_null($auth)) {
			return $this->_auth;
		} else {
			$this->_auth = $auth;
			return $this;
		}
	}
	
	public function view($view = null) {
		if (is_null($view)) {
			return $this->_view;
		} else {
			$this->_view = $view;
			return $this;
		}
	}
	
	public function config($config = null) {
		if (is_null($config)) {
			return $this->_config;
		} else {
			$this->_config = $config;
			return $this;
		}
	}
	
	public function env() {
		return $this->_env;
	}
	
	public function browser() {
		return $this->_browser;
	}
	
	public function pages($pages = null) {
		if (is_null($pages)) {
			return $this->_pages;
		} else {
			$this->_pages = $pages;
			return $this;
		}
	}
	
	public function controllerStack($value) {
		$this->_config->controllerStack[] = $value;
		return $this;
	}
	
	public function getApp() {
	}
	
	public function getTheme() {
	}
	
	public function factoryCount() {
		return $this->_factory->count();
	}

	public function factory($a = null, $b = null) {
		if (!$this->_factory) {
			$this->_factory = new Cana_Factory;
		}
		return $this->_factory->objectMap($a,$b);
	}
	
	public function extended($class, $name, $func = null) {
		if ($func != null) {
			$this->_extended[$class][$name] = $func;
		}
		return $this->_extended[$class][$name];
	}
	
	public function dbWrite() {
		return $this->db();
	}

}