<?php

/**
 * Cana application class
 * 
 * @author	Devin Smith <devins@devin-smith.com>
 * @date	2009.06.11
 *
 */


class Crunchbutton_App extends Cana_App {
	private $_crypt;
	public function init($params = null) {
	
		if (!$_SERVER['__HTTP_HOST']) {
			$cli = true;
		}

		$params['postInitSkip'] = true;
		switch ($_SERVER['__HTTP_HOST']) {
			case 'crunchbutton.localhost':
				$params['env'] = 'local';
				break;
			case 'crunchr.co':
			case '_DOMAIN_':
			case 'staging._DOMAIN_':
				$params['env'] = 'live';
				break;
			case 'beta.crunchr.co':
			case 'alpha.crunchr.co':
			case 'test.crunchr.co':
			case 'dev.crunchr.co':
			case 'beta._DOMAIN_':
			case 'alpha._DOMAIN_':
			case 'test._DOMAIN_':
			case 'brad.crunchr.co':
			case 'dev._DOMAIN_':
				$params['env'] = 'beta';
				break;

			default:
				switch ($_SERVER['SERVER_ADDR']) {
					case '74.207.245.57':
					case '_IP_':
						$params['env'] = 'live';
						break;
					default:
						switch (dirname(__FILE__)) {
							case '/home/beta.crunchbutton/include/library/Crunchbutton':
							case '/home/dev.crunchbutton/include/library/Crunchbutton':
								$params['env'] = 'beta';
								$_SERVER['__HTTP_HOST'] = 'beta.crunchr.co';
								break;
							case '/home/crunchbutton/include/library/Crunchbutton':
							case '/home/staging.crunchbutton/include/library/Crunchbutton':
								$params['env'] = 'live';
								$_SERVER['__HTTP_HOST'] = '_DOMAIN_';
								break;
							default:
								$params['env'] = 'local';
								break;
						}
						
						break;
				}
		}
		
		if ($_SERVER['__HTTP_HOST'] == 'crunchr.co') {
			header ('HTTP/1.1 301 Moved Permanently');
			header('Location: http://_DOMAIN_/');
			exit;
		}

		if ($params['env'] == 'live' && !$cli && $_SERVER['__HTTP_HOST'] == '_DOMAIN_') {
			error_reporting(E_ERROR | E_PARSE);
			
			if ($_SERVER['HTTPS'] != 'on') {
				header('Location: https://_DOMAIN_'.$_SERVER['REQUEST_URI']);
				exit;
			}
		}

		parent::init($params);

		$domain = new Cana_Model;
		if (preg_match('/(iphone|android)/',$_SERVER['HTTP_USER_AGENT'])) {
			$domain->version = 'mobile';
		} else {
			$domain->version = 'default';
		}
		$domain->theme = 'default';

		$this->buildAuth($this->db());

		$config = $this->config();
		$config->domain = $domain;
		
		
		if ($params['env'] != 'local' && $_SERVER['__HTTP_HOST'] != 'dev.crunchr.co') {
			$config->bundle = true;
		}

		$this
			->config($config)
			->postInit($params);

		require_once c::config()->dirs->library . '/Cana/Stripe.php';			
		Stripe::setApiKey(c::config()->stripe->dev->secret);

	}
	
	public function user() {
		return $this->auth()->user();
	}
	
	public function displayPage($page = null) {

		if (is_null($page)) {
			$page = $this->pages();
			$page = isset($page[0]) ? $page[0] : '';
			switch ($page) {
				case '':
					$pageName = Cana::config()->defaults->page;
					break;
				default:
					$pageName = implode('/',$this->pages());
					break;
			
			}
		} else {
			$pageName = $page;
		}

		parent::displayPage($pageName == 'error' ? 'home' : $pageName);
		
		return $this;
	}

	
	public function buildView($params = array()) {

		// domain level setup
		$params['theme'][] = $this->config()->defaults->version.'/'.$this->config()->defaults->theme.'/';
		if (is_array($themes = json_decode($this->config()->domain->theme,'array'))) {
			$themes = array_reverse($themes);
			foreach ($themes as $theme) {
				$params['theme'][] = $this->config()->defaults->version.'/'.$theme.'/';
			}
		} else {
			$params['theme'][] = $this->config()->defaults->version.'/'.$this->config()->domain->theme.'/';
		}
		
		if (isset($this->config()->domain->version)) {
			$params['theme'][] = $this->config()->domain->version.'/'.$this->config()->defaults->theme.'/';
		}
		if (is_array($themes = json_decode($this->config()->domain->theme,'array'))) {
			$themes = array_reverse($themes);
			foreach ($themes as $theme) {
				$params['theme'][] = $this->config()->domain->version.'/'.$theme.'/';
			}
		} elseif (isset($this->config()->domain->version)) {
			$params['theme'][] = $this->config()->domain->version.'/'.$this->config()->domain->theme.'/';
		}
		
		$params['layout'] =  $this->config()->defaults->layout;

		foreach ($params['theme'] as $theme) {
			$this->controllerStack($theme);
		}

		parent::buildView($params);
		
		return $this;
	}
	
	public function getTheme($config = null) {
		$config = $config ? $config : $this->config();
		
		if (is_array($themes = json_decode($config->domain->brand,'array'))) {
			return $themes;
		} else {
			return $config->domain->brand;
		}
	}
	
	public function crypt($crypt = null) {
		if (is_null($crypt)) {
			return $this->_crypt = new Cana_Crypt($this->config()->crypt->key);
		} else {
			return $this->_crypt;
		}
	}

	public function buildAuth($db = null) {
		$this->auth(new Crunchbutton_Auth($db));
		return $this;
	}
	public function buildAcl($db = null) {
		$this->acl(new Crunchbutton_Acl($db, $this->auth()));
		return $this;
	}
	
	public function revision() {
		return isset($this->_revision) ? $this->_revision : Crunchbutton_Util::revision();
	}
	
	public function appDb() {
		return $this->_appDb;
	}
	
	public function appConfig() {
		$config = [];
		$config['user'] = c::user()->exports();
		$config['env'] = $this->env();
		return $config;
	}
	
	public function balanced() {
		if (!$this->_balanced) {
			$env = c::env() == 'live' ? 'live' : 'dev';
			\Balanced\Settings::$api_key = c::config()->balanced->{$env}->secret;
			$marketplace = Balanced\Marketplace::mine();
			$this->_balanced = $marketplace;
		}
		return $this->_balanced;
	}
	
	public function isBot() {
		if (!isset($this->_isBot)) {
			$this->_isBot = preg_match('/googlebot|slurp|yahoo|bingbot|jeeves|scoutjet|webcrawl/i',$_SERVER['HTTP_USER_AGENT']);
		}
		return $this->_isBot;
	}
	
} 
