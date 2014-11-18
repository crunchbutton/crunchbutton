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
		set_exception_handler([$this, 'exception']);
	
		if (!$_SERVER['SERVER_NAME']) {
			$cli = true;
			// get the env send by parameter
			$a = (object)getopt('s::c::r::f::e::');
			if( $a->e ){
				$_env = $a->e;
			}
		}

		$host = $_SERVER['SERVER_NAME'];

		$params['postInitSkip'] = true;
		switch ($_SERVER['SERVER_NAME']) {
			case 'staging.crunchr.co':
				$env = 'staging';
				break;
			case 'crunchr.co':
			case '_DOMAIN_':
			case 'cockpit.crunchr.co':
			case 'cockpit._DOMAIN_':
			case 'cbtn.io':
			case 'cockpit.la':
			case 'dispatch.la':
			case 'staging.kenneth.crunchr.co':
			case 'cockpit.kenneth.crunchr.co':
			case 'cockpit3.kenneth.crunchr.co':
			case 'staging.manish.crunchr.co':
			case 'cockpit.manish.crunchr.co':
			case 'cockpit3.manish.crunchr.co':
				$env = 'live';
				break;
			case 'wenzel.beta.crunchr.co':
			case 'beta.crunchr.co':
			case 'beta.cockpit.crunchr.co':
			case 'beta.cockpit._DOMAIN_':
			case 'beta.kenneth.crunchr.co':
			case 'beta.cockpit.kenneth.crunchr.co':
			case 'beta.cockpit3.kenneth.crunchr.co':
			case 'beta.manish.crunchr.co':
			case 'beta.cockpit.manish.crunchr.co':
			case 'beta.cockpit3.manish.crunchr.co':
			case 'beta.cockpit.la':
			case 'cockpitbeta._DOMAIN_':
				$env = 'beta';
				break;
			case 'dev.crunchr.co':
			case 'dev.kenneth.crunchr.co':
			case 'kenneth.crunchr.co':
			case 'dev.manish.crunchr.co':
			case 'manish.crunchr.co':
			case 'dev.cockpit.la':
				$env = 'dev';
				break;
			case 'cockpit.localhost':
			case 'crunchbutton.localhost':
				$env = 'local';
				break;
			default:
				$env = 'local';
				break;
		}

		switch ($_SERVER['SERVER_NAME']) {
			case 'crunchbutton.localhost':
			case 'wenzel.localhost':
			case 'seven.localhost':
				$params['env'] = 'local';
				break;
			case 'crunchr.co':
			case '_DOMAIN_':
			case 'staging._DOMAIN_':
			case 'spicywithdelivery.com':
			case 'cockpit.la':
			case 'dispatch.la':
			case 'cockpit.kenneth.crunchr.co':
			case 'cockpit3.kenneth.crunchr.co':
			case 'staging.kenneth.crunchr.co':
			case 'cockpit.manish.crunchr.co':
			case 'cockpit3.manish.crunchr.co':
			case 'staging.manish.crunchr.co':
				$isStaging = true;
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
			case 'beta.spicywithdelivery.com':
			case 'dev.spicywithdelivery.com':
			case 'beta.cockpit.crunchr.co':
			case 'beta.cockpit._DOMAIN_':
			case 'beta.cockpit3._DOMAIN_':
			case 'wenzel.beta.crunchr.co':
			case 'beta.kenneth.crunchr.co':
			case 'dev.kenneth.crunchr.co':
			case 'kenneth.crunchr.co':
			case 'beta.manish.crunchr.co':
			case 'dev.manish.crunchr.co':
			case 'manish.crunchr.co':
			case 'beta.cockpit.la':
			case 'dev.cockpit.la':
			case 'beta.cockpit3.kenneth.crunchr.co':
			case 'beta.cockpit3.manish.crunchr.co':
			case 'beta.cockpit.kenneth.crunchr.co':
			case 'beta.cockpit.manish.crunchr.co':
			case 'cockpitbeta._DOMAIN_':
				$params['env'] = 'beta';
				break;

			default:
				switch ($_SERVER['SERVER_ADDR']) {
					case '74.207.245.57':
					case '_IP_':
					case '66.175.217.154':
						$params['env'] = 'live';
						break;
					default:
						switch (dirname(__FILE__)) {
							case '/home/beta.crunchbutton/include/library/Crunchbutton':
							case '/home/dev.crunchbutton/include/library/Crunchbutton':
								$params['env'] = 'beta';
								$_SERVER['SERVER_NAME'] = 'beta.crunchr.co';
								$host_callback = 'beta.crunchr.co';
								break;
							case '/home/crunchbutton/include/library/Crunchbutton':
							case '/home/_DOMAIN_/include/library/Crunchbutton':
								$params['env'] = 'live';
								$_SERVER['SERVER_NAME'] = '_DOMAIN_';
								$host_callback = '_DOMAIN_';
								break;
							case '/home/staging.crunchbutton/include/library/Crunchbutton':
								$params['env'] = 'live';
								$_SERVER['SERVER_NAME'] = '_DOMAIN_';
								$host_callback = 'staging.crunchr.co';
								break;
							case '/Users/arzynik/Sites/crunchbutton/include/library/Crunchbutton':
								$params['env'] = 'local';
								$_SERVER['SERVER_NAME'] = 'crunchbutton.localhost';
								$host_callback = 'dev.crunchr.co';
								break;
							default:
								if (getenv('TRAVIS')) {
									$params['env'] = 'travis';
									$_SERVER['SERVER_NAME'] = 'dev.crunchr.co';
									$host_callback = 'dev.crunchr.co';
								} else {
									$params['env'] = 'local';
									$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'];
									$host_callback = $_SERVER['HTTP_HOST'];
								}

								break;
						}
						
						break;
				}
		}

		if( $cli && $_env ){
			$params[ 'env' ] = $_env;
			$env = $_env;
		}

		// Force the host_callback - sometimes it is empty
		switch (dirname(__FILE__)) {
			case '/home/beta.crunchbutton/include/library/Crunchbutton':
			case '/home/dev.crunchbutton/include/library/Crunchbutton':
				$host_callback = 'beta.crunchr.co';
				break;
			case '/home/crunchbutton/include/library/Crunchbutton':
			case '/home/_DOMAIN_/include/library/Crunchbutton':
				$host_callback = '_DOMAIN_';
				break;
			case '/home/staging.crunchbutton/include/library/Crunchbutton':
				$host_callback = 'staging.crunchr.co';
				break;
			case '/home/cockpit.crunchbutton/include/library/Crunchbutton':
				switch ( $_SERVER['SERVER_NAME'] ) {
					case 'cockpit.crunchr.co':
					case 'cockpit._DOMAIN_':
						$host_callback = '_DOMAIN_';
						break;
					case 'beta.cockpit.crunchr.co':
					case 'beta.cockpit._DOMAIN_':
					case 'cockpitbeta._DOMAIN_':
						$host_callback = 'beta.crunchr.co';
						break;
				}
				break;
			default:
				$host_callback = $_SERVER['HTTP_HOST'];
				break;
		}

		if ($_SERVER['SERVER_NAME'] == 'crunchr.co') {
			header ('HTTP/1.1 301 Moved Permanently');
			header('Location: http://_DOMAIN_/');
			exit;
		}

		if ($params['env'] == 'live' && !$cli && ($_SERVER['SERVER_NAME'] == '_DOMAIN_' || $_SERVER['SERVER_NAME'] == 'spicywithdelivery.com')) {
			error_reporting(E_ERROR | E_PARSE);

			if ($_SERVER['HTTPS'] != 'on') {
				header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
				exit;
			}
		}

		parent::init($params);
		
		$config = $this->config();
		$config->site = Crunchbutton_Site::byDomain();
		
		if ($config->site->name == 'redirect' && $config->site->theme && php_sapi_name() !== 'cli') {
			header('Location: '.$config->site->theme.$_SERVER['REQUEST_URI']);
			exit;
		}

		if ($config->site->name == 'Cockpit' || $config->site->name == 'Cockpit2') {
			array_unshift($GLOBALS['config']['libraries'], 'Cockpit');
		}

		$config->host_callback = $host_callback;

		switch ($_SERVER['SERVER_NAME']) {
			case 'seven.localhost':
				$config->facebook->app = $config->facebook->seven->app;
				$config->facebook->secret = $config->facebook->seven->secret;
				break;
				
			case 'dev.crunchr.co':
				$config->facebook->app = $config->facebook->dev->app;
				$config->facebook->secret = $config->facebook->dev->secret;
				break;

			default:
				$config->facebook->app = $config->facebook->{$env}->app;
				$config->facebook->secret = $config->facebook->{$env}->secret;
				break;
		}
		
		$config->github->id = $config->github->{$params['env']}->id;
		$config->github->secret = $config->github->{$params['env']}->secret;

		$this->config($config);

		$this->buildAuth($this->db());
		
		if ($params['env'] != 'local' && $_SERVER['SERVER_NAME'] != 'dev.crunchr.co' && !preg_match('/cockpit.la|cockpit.kenneth|cockpit.manish|cockpit3/',$_SERVER['SERVER_NAME'])) {
			$config->bundle = true;
		}
		
		// debug shit
		if ($_REQUEST['_bundle']) {
			$config->bundle = true;
			$config->viewExport = true;
		}

		$this
			->config($config)
			->postInit($params);

		require_once c::config()->dirs->library . '/Cana/Stripe.php';			
		Stripe::setApiKey(c::config()->stripe->dev->secret);

		switch ($_SERVER['SERVER_NAME']) {
			case 'spicywithdelivery.com':
			case 'beta.spicywithdelivery.com':
			case 'dev.spicywithdelivery.com':
				$r = Restaurant::o(74);

				if (!c::getPagePiece(0)) {
					// forward to jos page
					header('Location: http://'.$_SERVER['HTTP_HOST'].'/'.$r->community()->permalink.'/'.$r->permalink);
					exit;

				} elseif (c::getPagePiece(0) == 'api' || c::getPagePiece(0) == 'assets') {
					// pass

				} elseif (c::getPagePiece(0) != 'providence' || (c::getPagePiece(0) == 'providence' && c::getPagePiece(1) != $r->permalink)) {

					header('Location: https://_DOMAIN_'.$_SERVER['REQUEST_URI']);
					exit;

				} else {
					// ??
				}

				break;

		}
		
		header('X-Powered-By: '.$this->config()->powered);

	}
	
	public function exception($e) {
		if ($this->env == 'live') {
			echo
				'<title>Error</title><style>body {font-family: sans-serif; }.wrapper{ width: 400px; margin: 0 auto; margin-top: 25px;}</style>'.
				'<div class="wrapper">'.
				'<h1>Crunchbutton</h1>'.
				'<p style="color: #666;">HEY! Your broke it! No just kidding. There was some sort of error we did not expect. An admin has been notified.</p>'.
				'<br><p style="background: #fff7e0; color: #ff0000; padding: 3px;">Error: '.$e->getMessage().
				'</div>';
			mail('_EMAIL','CRUNCHBUTTON CRITICAL ERROR',$e->getMessage());
			exit;
		} else {
			$this->config()->db = null;
			echo "\n<br />".$e->getMessage()."\n<br /><pre>";
			foreach($e->getTrace() as $k=>$v){ 
				if ($v['function'] == "include" || $v['function'] == "include_once" || $v['function'] == "require_once" || $v['function'] == "require"){ 
					$backtracel .= "#".$k." ".$v['function']."(".$v['args'][0].") called at [".$v['file'].":".$v['line']."]<br />"; 
				} else { 
					$backtracel .= "#".$k." ".$v['function']."() called at [".$v['file'].":".$v['line']."]<br />"; 
				} 
			} 
			echo $backtracel;
			exit;
		}
	}
	
	public function isCompat() {
		if (preg_match('/(Firefox\/1\.)|(MSIE (1|2|3|4|5|6|7|8|9)\b)/i',$_SERVER['HTTP_USER_AGENT'])) {
			return false;
		} else {
			return true;
		}
	}
	
	public function user() {
		return $this->auth()->user();
	}
	
	public function admin($admin = null) {
		if ($admin !== null) {
			$this->_admin = $admin;
		}
		return $this->_admin;
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

		// UI2
		if ($this->config()->site->config('ui2-mobile-force')->value && $this->isMobile() && $this->config()->site->theme == 'crunchbutton') {
			$this->config()->site->theme =  'seven';
		}
		
		$params['theme'][] = $this->config()->defaults->version.'/'.$this->config()->defaults->theme.'/';
		if (is_array($themes = json_decode($this->config()->site->theme,'array'))) {
			$themes = array_reverse($themes);
			foreach ($themes as $theme) {
				$params['theme'][] = $this->config()->defaults->version.'/'.$theme.'/';
			}
		} else {
			$params['theme'][] = $this->config()->defaults->version.'/'.$this->config()->site->theme.'/';
		}
		
		if (isset($this->config()->site->version)) {
			$params['theme'][] = $this->config()->site->version.'/'.$this->config()->defaults->theme.'/';
		}
		if (is_array($themes = json_decode($this->config()->site->theme,'array'))) {
			$themes = array_reverse($themes);
			foreach ($themes as $theme) {
				$params['theme'][] = $this->config()->site->version.'/'.$theme.'/';
			}
		} elseif (isset($this->config()->site->version)) {
			$params['theme'][] = $this->config()->site->version.'/'.$this->config()->site->theme.'/';
		}
		$stack = array_reverse($params['theme']);
		$params['layout'] =  $this->config()->defaults->layout;

		foreach ($stack as $theme) {
			$this->controllerStack($theme);
		}

		if (!$this->isCompat()) {
			$params['layout'] =  'layout/compat';
		} elseif ($this->isDownloadable() && !$this->isCockpit()) {
			$params['layout'] =  'layout/download';
		} else {
			$params['layout'] =  $this->config()->defaults->layout;
		}

		parent::buildView($params);
		
		if ($this->config()->viewExport) {
			$this->view()->export = true;
		}
		
		return $this;
	}
	
	public function isCockpit() {
		return preg_match('/^(.*\.?)cockpit(.*?)\.(crunchbutton\.com|crunchr\.co|localhost|la)$/i',$_SERVER['HTTP_HOST']) ? true : false;
	}
	
	public function isDownloadable() {
		if (preg_match('/ios|iphone|ipad|android/i',$_SERVER['HTTP_USER_AGENT']) && !$_COOKIE['_viewmobile2']) {
			return true;
		} else {
			return false;
		}
	}
	
	public function getTheme($config = null) {
		$config = $config ? $config : $this->config();
		
		if (is_array($themes = json_decode($config->site->brand,'array'))) {
			return $themes;
		} else {
			return $config->site->brand;
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
		$this->auth(new Auth($db));
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
	
	public function appConfig($output = ['base']) {
		$config = [];

		if (in_array('base', $output)) {
			$config['user'] = c::user()->exports();
			$config['env'] = $this->env();
			$config['ab'] = json_decode($this->auth()->get('ab'));
			
			// export the processor info
			$config[ 'processor' ][ 'type' ] = Crunchbutton_User_Payment_Type::processor();
			$config[ 'processor' ][ 'stripe' ] = c::config()->stripe->{c::getEnv()}->{'public'};
//			$config[ 'processor' ][ 'balanced' ] = c::balanced()->href;

			if (!$this->auth()->get('loc_lat')) {
				$geo = new Crunchbutton_Geo([
					'adapter' => 'Geoip_Binary',
					'file' => c::config()->dirs->root.'db/GeoLiteCity.dat'
				]);
				$geo->setIp($_SERVER['REMOTE_ADDR'])->populateByIp();
				$this->auth()->set('loc_lat', $geo->getLatitude());
				$this->auth()->set('loc_lon', $geo->getLongitude());
				$this->auth()->set('city', $geo->getCity());
				$this->auth()->set('region', $geo->getRegion());
			}
	
			$config['loc']['lat'] = $this->auth()->get('loc_lat');
			$config['loc']['lon'] = $this->auth()->get('loc_lon');
			$config['loc']['city'] = $this->auth()->get('city');
			$config['loc']['region'] = $this->auth()->get('region');
			
			$config['version'] = Cana_Util::gitVersion();
		}
		
		if (in_array('extended', $output)) {
			$config['aliases'] = Community_Alias::all(['id_community', 'prep', 'name_alt', 'permalink', 'image']);
			$config['locations'] = Community::all_locations();
			$config['facebookScope'] = c::config()->facebook->default->scope;

			$config['communities'] = [];
			foreach (Community::all(c::getPagePiece(0)) as $community) {
				$c = $community->properties();
				$c['stored'] = true;
				$config['communities'][$community->permalink] = $c;
			}
			
			$config['topCommunities'] = [];
			foreach (Community_Alias::q('select * from community_alias where top="1" order by `sort`') as $community_alias) {
				$config['topCommunities'][] = [
					'alias' => $community_alias->alias,
					'name' => $community_alias->name_alt
				];
			}
		}
		
		$config['site'] = $this->config()->site->exposedConfig();

		return $config;
	}
	
	public function getEnv($d = true) {
		if (c::user()->debug) {
			$env = 'dev';
		} elseif (c::env() == 'live') {
			$env = 'live';
		} elseif ($d) {
			$env = 'dev';
		} else {
			$env = c::env();
		}
		return $env;
	}
	
	public function balanced() {
		if (!$this->_balanced) {
			\Balanced\Settings::$api_key = c::config()->balanced->{c::getEnv()}->secret;
			$marketplace = Balanced\Marketplace::mine();
			$this->_balanced = $marketplace;
		}
		return $this->_balanced;
	}
	
	public function lob($d = true) {
		if (!$this->_lob) {
			if (c::env() == 'live') {
				$env = 'live';
			} elseif ($d) {
				$env = 'dev';
			} else {
				$env = c::env();
			}

			$this->_lob = $lob = new \Lob\Lob(c::config()->lob->{$env}->key, c::config()->lob->{$env}->account);
		}
		return $this->_lob;
	}
	
	public function isBot() {
		if (!isset($this->_isBot)) {
			$this->_isBot = preg_match('/googlebot|slurp|yahoo|bingbot|jeeves|scoutjet|webcrawl/i',$_SERVER['HTTP_USER_AGENT']);
		}
		return $this->_isBot;
	}
	
	public function detect() {
		if (!isset($this->_detect)) {
			$this->_detect = new Crunchbutton_Detect;
		}
		return $this->_detect;
	}
	
	public function isMobile() {
		return $this->detect()->isMobile();
	}
	
	public function rep($rep = null) {
		if (!isset($this->_rep)) {
			$this->_rep = $rep;
		}
		return $this->_rep;
	}
	
	public function facebook() {
		if (!$this->_facebook) {
			$this->_facebook = new Cana_Facebook([
				'appId'	=> Cana::config()->facebook->app,
				'secret' => Cana::config()->facebook->secret
			]);
		}
		return $this->_facebook;
	}
	
	public function twilio() {
		if (!isset($this->_twilio)) {
			$env = c::getEnv();
			$this->_twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
		}
		return $this->_twilio;
	}
	
	public function mailgun() {
		if (!isset($this->_mailgun)) {
			$this->_mailgun = new \Mailgun\Mailgun(c::config()->mailgun->key);
		}
		return $this->_mailgun; 
	}
	
} 
