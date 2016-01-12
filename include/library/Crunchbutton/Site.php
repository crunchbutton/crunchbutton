<?php

class Crunchbutton_Site extends Cana_Table {
	public function config($key = null) {
		if (!isset($this->_config)) {
			$global = Crunchbutton_Config::q('select * from config where id_site is null');
			$site = Crunchbutton_Config::q('select * from config where id_site=?',[$this->id_site]);

			foreach ($global as $c) {
				$this->_config[$c->key] = $c;
			}

			foreach ($site as $c) {
				$this->_config[$c->key] = $c;
			}
		}
		if ($key) {
			return $this->_config[$key] ? $this->_config[$key] : Crunchbutton_Config::blank($key, $value, $this->id_site);
		} else {
			return $this->_config;
		}
	}

	public function set($key, $value) {
		$c = $this->config($key);

		if ($c) {
			$c->set($value);
		} else {
			$c = Crunchbutton_Config::store($key, $value, $this->id_site);
		}

		return $c;
	}

	public function exportConfig() {
		$config = $this->config();
		$conf = [];
		foreach ($config as $c) {
			$conf[$c->key] = $c->value;
		}
		return $conf;
	}

	public function exposedConfig() {
		$config = $this->config();
		$conf = [];
		foreach ($config as $c) {
			if ($c->exposed) {
				if (strpos($c->value, '{') > -1 || strpos($c->value, '[') > -1) {
					$conf[$c->key] = json_decode($c->value);
				} else {
					$conf[$c->key] = $c->value;
				}
			}
		}
		return $conf;
	}

	public static function byDomain($domain = null) {
		$domain = is_null($domain) ? $_SERVER['HTTP_HOST'] : $domain;

		$sites = Crunchbutton_Site::q('
			SELECT *
			FROM site
			WHERE active=true
			ORDER BY sort ASC
		');
		$tsite = null;
		foreach ($sites as $site) {
			if (preg_match($site->domain, $domain)) {
				$tsite = $site;
				break;
			}
		}

		// default if there is no site (only happens if the site table is empty which should never happen)
		if (!$tsite) {
			$tsite = new Site([
				'theme' => 'crunchbutton',
				'name' => 'Default'
			]);
		}

		if (preg_match('/(iphone|android)/',$_SERVER['HTTP_USER_AGENT'])) {
			$tsite->version = 'mobile';
		} else {
			$tsite->version = 'default';
		}

		return $tsite;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('site')
			->idVar('id_site')
			->load($id);
	}
}
