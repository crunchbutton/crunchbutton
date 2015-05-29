<?php

class Crunchbutton_Config extends Cana_Table {
	public static function store($key, $value, $site = null) {
		$q = '
			select * from config where `key`="'.$key.'" and id_site
		';
		if ($site) {
			$q .= ' = ?';
		} else {
			$q .= ' is null';
		}
		$config = Crunchbutton_Config::q($q, [$site]);
		if (!$config->id_config) {
			$config = Crunchbutton_Config::blank($key, $value, $site);
		}
		$config->set($value);
		return $config;
	}

	public static function getVal( $key ){
		$config = Crunchbutton_Config::q('SELECT * FROM config WHERE `key` = ?', [$key] );
		if( $config->value ){
			return $config->value;
		}
		return false;
	}

	public static function getConfigByKey( $key ){
		return Crunchbutton_Config::q('SELECT * FROM config WHERE `key` = ? LIMIT 1', [$key]);
	}

	public static function blank($key, $value, $site = null) {
		$config = new Crunchbutton_Config;
		$config->id_site = $site;
		$config->key = $key;
		return $config;
	}

	public function set($value) {
		if (is_array($value)) {
			$value = json_encode($value);
		}
		$this->value = $value;
		$this->save();
	}

	public function val() {
		$val = json_decode($this->value);
		if (is_array($val)) {
			return $val;
		}
		return $this->value;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('config')
			->idVar('id_config')
			->load($id);
	}
}