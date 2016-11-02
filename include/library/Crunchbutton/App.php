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

	public function envByHost($travis = true) {
		if (!$_SERVER['SERVER_NAME']) {
			putenv('CLI=true');
			$this->cli = true;
			// get the env send by parameter
			$a = (object)getopt('s::c::r::f::e::');
			if ($a->e) {
				$cliEnv = $a->e;
			}
		}

		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'];
echo '<pre>';var_dump( $_ENV );exit(1);

		if ($_ENV['TRAVIS']) {
			$db = 'travis';
		} elseif ($_ENV['ENV']) {
			$db = $_ENV['ENV'];
		} else {
			$db = null;
		}

		if ($cliEnv) {
			$db = $cliEnv;
		}

		return $db;
	}

	public function init($params = null) {
		set_exception_handler([$this, 'exception']);

		if ($_ENV['DEBUG']) {
			error_log('start init...');
		}

		new Crunchbutton_Headers;

		$db = $this->envByHost();

		if ($db === null) {
			//header('HTTP/1.1 301 Moved Permanently');
			echo 'bad configuration. check your .env';
			exit(1);
		}

		// special settings for live web views
		if ($db != 'heroku' &&  !$_ENV['DOCKER'] && preg_match('/^cockpit.la|cbtn.io|_DOMAIN_|spicywithdelivery.com$/',$_SERVER['SERVER_NAME']) && !$this->cli && !isset($_REQUEST['__host']) && $_SERVER['SERVER_NAME'] != 'old.cockpit._DOMAIN_' && $_SERVER['SERVER_NAME'] != 'cockpit._DOMAIN_') {
			error_reporting(E_ERROR | E_PARSE);

			if ((!$_SERVER['HTTP_X_FORWARDED_PROTO'] && $_SERVER['HTTPS'] != 'on') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] && $_SERVER['HTTP_X_FORWARDED_PROTO'] != 'https')) {
				header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
				exit;
			}
		}

		if (preg_match('/^old.cockpit._DOMAIN_$/',$_SERVER['SERVER_NAME']) && $_SERVER['HTTPS'] == 'on') {
			die('dont use https');
			header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
			exit;
		}

		// if we have jawsdb_url but not database_url
		if ((!$_ENV['DATABASE_URL'] || $_ENV['DATABASE_URL'] == 'null') && $_ENV['JAWSDB_MARIA_URL']) {
			$_ENV['DATABASE_URL'] = $_ENV['JAWSDB_MARIA_URL'];
		}

		// create a default+read database config
		if ($_ENV['DATABASE_URL']) {
			$params['config']->db->readDB = (object)[
				'url' => $_ENV['DATABASE_URL'],
				'type' => Cana_Db::typeByUrl($_ENV['DATABASE_URL'])
			];
			$db = 'readDB';
		}

		// create a write database config
		if ($_ENV['DATABASE_URL_WRITE']) {
			$params['config']->db->writeDB = (object)[
				'url' => $_ENV['DATABASE_URL_WRITE'],
				'type' => Cana_Db::typeByUrl($_ENV['DATABASE_URL_WRITE'])
			];
		}

		if ($_ENV['REDIS_URL']) {
			$params['config']->cache->default = $params['config']->cache->redis;
			$params['config']->cache->default->url = $_ENV['REDIS_URL'];
		} else {
			$params['config']->cache->default = $params['config']->cache->{$params['config']->cache->default};
		}

		if (!$write && $params['config']->db->{$db}->hostWrite) {
			$params['config']->db->writeDB = clone $params['config']->db->{$db};
			$params['config']->db->writeDB->host = $params['config']->db->writeDB->hostWrite;
		}

		$params['postInitSkip'] = true;
		$params['env'] = $db;

		if ($_ENV['DEBUG']) {

			error_log('>> INITING using DB: '.$db);

			try {
				parent::init($params);
			} catch (Exception $e) {
				print_r($db);
				print_r($_SERVER['SERVER_NAME'].$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
				print_r($e->getMessage());

			}
			error_log('>> Finished init');

		} else {
			// show the server-vacation page when we cant connect to the database on production
			try {
				parent::init($params);
			} catch (Exception $e) {
				$this->dbError();
			}
		}

		$config = $this->config();
		$config->site = Crunchbutton_Site::byDomain();

		if ($config->site->config('maintenance')->val()) {
			$this->dbError();
		}

		if ($config->site->name == 'redirect' && $config->site->theme && php_sapi_name() !== 'cli') {
			header('Location: '.$config->site->theme.$_SERVER['REQUEST_URI']);
			exit;
		}

		if ($_ENV['THEME']) {
			$config->site->theme = $_ENV['THEME'];
        }

		if ($config->site->name == 'Cockpit' || $config->site->theme == 'cockpit2' || $config->site->name == 'Cockpit2' || $this->cli) {
			array_unshift($GLOBALS['config']['libraries'], 'Cockpit');
		}

		// set host callback by hostname
		$config->host_callback = ($db == 'local' || $db == 'travis' || $db == 'travispostgres' || !$_SERVER['SERVER_NAME']) ? 'dev.crunchr.co' : $_SERVER['SERVER_NAME'];

		// set facebook config by hostname
		if ($config->facebook->{$_SERVER['SERVER_NAME'] || 'cli'}) {
			$config->facebook->app = $config->facebook->{$_SERVER['SERVER_NAME'] || 'cli'}->app;
			$config->facebook->secret = $config->facebook->{$_SERVER['SERVER_NAME'] || 'cli'}->secret;
		}

		$this->config($config);

		if ($params['config']->db->writeDB) {
			if ($_ENV['DEBUG']) {
				error_log('>> WRITEDB');
			}
			$write = $this->buildDb('writeDB');
			$this->dbWrite($write);
		}

		$this->buildAuth($this->db());

		// set bundle on everything except tests
		if ($db != 'local' && !preg_match('/^dev./',$_SERVER['SERVER_NAME'])) {
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

		c::stripe();
		c::s3();

		header('Server: '.$this->config()->powered);
		header('X-Powered-By: '.$this->config()->powered);
		header('X-Footprint: '.gethostname().'-'.$_SERVER['SERVER_NAME'].'-'.$db);

		c::db()->exec("set time_zone = '".$this->config()->timezoneOffset."';");


	}

	public function defaultExceptionHandler($e) {
		$this->config()->db = null;

		foreach($e->getTrace() as $k=>$v){
			if ($v['function'] == "include" || $v['function'] == "include_once" || $v['function'] == "require_once" || $v['function'] == "require"){
				$backtracels[] = "#".$k." ".$v['function']."(".$v['args'][0].") called at [".$v['file'].":".$v['line']."]";
			} else {
				$backtracels[] = "#".$k." ".$v['function']."() called at [".$v['file'].":".$v['line']."]";
			}
		}

		if ($_ENV['HEROKU']) {
			$stderr = fopen('php://stderr', 'w');

			fwrite($stderr, 'PHP EXCEPTION: '.$e->getMessage()."\n");

			foreach ($backtracels as $l) {
				fwrite($stderr, $l."\n");
			}
			fwrite($stderr, $e->getTraceAsString()."\n");

			fwrite($stderr, "\n");
			fclose($stderr);
		}

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
			if (php_sapi_name() === 'cli') {
				echo "\n\nFATAL ERROR\n\n".$e->getMessage()."\n";
				foreach ($backtracels as $l) {
					echo $l."\n";
				}
			} else {
				echo "\n<br />".$e->getMessage()."\n<br /><pre>";
				foreach ($backtracels as $l) {
					echo $l.'<br>';
				}
			}
			exit(1);
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
		if (!$admin && !$this->_admin && $_ENV['CLI']) {
			$this->_admin = new Admin;
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

		if ($_ENV['DEBUG']) {
			error_log('>> DISPLAYING PAGE: '.$pageName);
		}

		try {
			parent::displayPage($pageName == 'error' ? 'home' : $pageName);
		} catch (Exception $e) {
			$this->exception($e);
		}

		return $this;
	}

	public function exception($e) {
		$fn = $this->exceptionHandler();
		if ($fn) {
			$fn($e);
		} else {
			$this->defaultExceptionHandler($e);
		}
	}

	public function exceptionHandler($fn = null) {
		if (!is_null($fn)) {
			$this->_exceptionHandler = $fn;
		}
		return $this->_exceptionHandler;
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

		if ($this->isCockpit()) {
			$this->config()->viewfilter = false;
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
			return $this->_crypt = new Cana_Crypt(($_ENV['USE_ENCRYPTION_KEY'] && $_ENV['ENCRYPTION_KEY']) ? $_ENV['ENCRYPTION_KEY'] : $this->config()->crypt->key);
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
				$geo->setIp(c::getIp())->populateByIp();
				$this->auth()->set('loc_lat', $geo->getLatitude());
				$this->auth()->set('loc_lon', $geo->getLongitude());
				$this->auth()->set('city', $geo->getCity());
				$this->auth()->set('region', $geo->getRegion());
			}

			$config['loc']['lat'] = $this->auth()->get('loc_lat');
			$config['loc']['lon'] = $this->auth()->get('loc_lon');
			$config['loc']['city'] = $this->auth()->get('city');
			$config['loc']['region'] = $this->auth()->get('region');

			if($_ENV['HEROKU_SLUG_COMMIT']){
				$config['version'] = $_ENV['HEROKU_SLUG_COMMIT'];
			} else {
				$config['version'] = Deploy_Server::currentVersion();
			}

		}

		if (in_array('extended', $output)) {
			$config['aliases'] = Community_Alias::all(['id_community', 'prep', 'name_alt', 'permalink', 'image']);
			$config['locations'] = Community::all_locations();
			$config['facebookScope'] = c::config()->facebook->default->scope;

			$config['communities'] = [];
			foreach (Community::all(c::getPagePiece(0)) as $community) {
				$c = $community->configExports();
				$c['stored'] = true;
				$config['communities'][$community->permalink] = $c;
			}

			$config['topCommunities'] = [];
			foreach (Community::q('SELECT * FROM community c WHERE c.top IS NOT NULL AND c.top >= 1 ORDER BY c.top ASC') as $community) {
				$community_alias = Community_Alias::q( 'SELECT * FROM community_alias WHERE id_community = ? ORDER BY id_community_alias DESC LIMIT 1', [ $community->id_community ] )->get( 0 );
				if ( $community_alias->id_community_alias ){
					$config['topCommunities'][] = [ 'alias' => $community_alias->alias, 'name' => $community->name ];
				}
			}
		}

		$config['site'] = $this->config()->site->exposedConfig();
		$config['site']['ab']['share-text-referral'] = [
			[
				'name' => 'referral-hey',
				'line' => 'Enter my invite code %c in the Notes Section of your order, and get a food delivery free. '
			]
		];

		$config['site']['ab']['share-facebook-title'] = [
			[
				'name' => 'share-facebook-title',
				'line' => 'Crunchbutton - Food delivered from places that don\'t'
			]
		];

		$config['site']['ab']['share-facebook-description'] = [
			[
				'name' => 'share-facebook-description',
				'line' => 'Order your favorite food, with a click, even if they don\'t deliver. Check out Crunchbutton today!'
			]
		];
		$config['site']['ab']['share-facebook-url'] = [
			[
				'name' => 'share-facebook-url',
				'line' => 'http://_DOMAIN_/'
			]
		];

		$config['site']['ab']['share-twitter-text'] = [
			[
				'name' => 'share-twitter-text',
				'line' => 'Get food delivery from places that don\'t deliver. Check out '
			]
		];

		$config['site']['ab']['share-text-twitter'] = [
			[
				'name' => 'twitter-love-notes',
				'line' => 'i love @crunchbutton delivery :) use my code %c in the Notes section for free delivery!'
			]
		];

		$config['site']['ab']['share-order-text-twitter'] = [
			[
				'name' => 'twitter-order-url',
				'line' => 'Just ordered %r from @crunchbutton! - _DOMAIN_'
			],
			[
				'name' => 'twitter-order-hashtag',
				'line' => 'just ordered %r from @crunchbutton use my link for free delivery! #delivery'
			]
		];

		// quotes
		$config[ 'site' ][ 'quotes' ] = Crunchbutton_Quote::publicExports();

		return $config;
	}

	public function getEnv($d = true) {
		if ($_ENV['ENV']) {
			$env = $_ENV['ENV'];
		} elseif (c::user()->debug) {
			$env = 'dev';
		} elseif (c::env() == 'live') {
			$env = 'live';
		} elseif ($d === true) {
			$env = 'dev';
		} else {
			$env = c::env();
		}
		return $env;
	}

	public function stripe() {
		if (!$this->_stripe) {
			\Stripe\Stripe::setApiKey(c::config()->stripe->{c::getEnv()}->secret);
			$this->_stripe = true;
		}
		return $this->_stripe;
	}



	public function lob($d = true) {
		if (!$this->_lob) {
			if ($d) {
				$env = 'dev';
			} elseif (c::getEnv() == 'live' || c::getEnv() == 'crondb') {
				$env = 'live';
			} else {
				$env = c::getEnv();
			}
			$this->_lob = new \Lob\Lob(c::config()->lob->{$env}->key);
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
			$http = new Services_Twilio_TinyHttp(
				'https://api.twilio.com',
				['curlopts' => [
					CURLOPT_SSL_VERIFYPEER => false
				]]
			);

			$this->_twilio = new Services_Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token, '2010-04-01', $http);
		}
		return $this->_twilio;
	}

	public function mailgun() {
		if (!isset($this->_mailgun)) {
			$this->_mailgun = new \Mailgun\Mailgun(c::config()->mailgun->key);
		}
		return $this->_mailgun;
	}

	public function github() {
		if (!isset($this->_github)) {
			$this->_github = new \Github\Client();
			$this->_github->authenticate(c::config()->github->token, '', Github\Client::AUTH_HTTP_TOKEN);
		}
		return $this->_github;
	}

	public function dbError() {
		$date = new DateTime;
		$startDate = new DateTime('2015-12-01');
		$endDate = new DateTime('2016-01-05');
		if ($date > $startDate && $date < $endDate) {
			$holiday = '-holidays';
		}

		include(c::config()->dirs->www.'server-vacation'.$holiday.'.html');
		exit;
	}

	public function metricsDB() {
		if (!isset($this->_metricsDB)) {
			$this->_metricsDB = new Cana_Db_PostgreSQL_Db($this->config()->db->metrics);
		}
		//die('asd');
		//var_dump($this->_metricsDB);
		//exit;
		return $this->_metricsDB;
	}

	public function s3() {
		if (!isset($this->_s3)) {
			$this->_s3 = new \Aws\S3\S3Client([
				'version' => 'latest',
				'region'  => 'us-east-1',
				'credentials' => [
					'key'    => c::config()->s3->key,
					'secret' => c::config()->s3->secret,
				]
			]);
		}
		return $this->_s3;
	}

	public function getIp() {
		if (!isset($this->_ip)) {
			if ($_SERVER['HTTP_X_FORWARDED_FOR'] && strpos($_SERVER['REMOTE_ADDR'], '192.168.') === 0 ) {
				$this->_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$this->_ip = $_SERVER['REMOTE_ADDR'];
			}
		}
		return $this->_ip;
	}
}
