<?php

class Cockpit_Deploy_Version extends Cana_Table {
	public function exports() {
		$ex = $this->properties();
		$ex['timestamp'] = strtotime($this->date);
		$ex['server'] = [
			'name' => $this->server()->name,
			'repo' => $this->server()->repo
		];
		$ex['admin'] = [
			'name' => $this->admin()->name
		];
		return $ex;
	}

	public function server() {
		if (!isset($this->_server)) {
			$this->_server = Deploy_Server::o($this->id_deploy_server);
		}
		return $this->_server;
	}
	
	public function admin() {
		if (!isset($this->_admin)) {
			$this->_admin = Admin::o($this->id_admin);
		}
		return $this->_admin;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('deploy_version')
			->idVar('id_deploy_version')
			->load($id);
	}
}