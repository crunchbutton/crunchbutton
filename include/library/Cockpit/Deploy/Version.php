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
			$this->_server = Cockpit_Deploy_Server::o($this->id_deploy_server);
		}
		return $this->_server;
	}
	
	public function admin() {
		if (!isset($this->_admin)) {
			$this->_admin = Admin::o($this->id_admin);
		}
		return $this->_admin;
	}
	
	public static function getQue($host) {
		if (!$host) {
			return;
		}

		$que = self::q('
			select deploy_version.* from deploy_version
			left join deploy_server using (id_deploy_server)
			where
				deploy_server.hostname="'.$host.'"
				and status="new"
				and date <= NOW()
			order by date desc
		');
		return $que;
	}
	
	public function save() {
		parent::save();

		Chat::emit([
			'room' => [
				'deploy.version.'.$this->id_deploy_version
			]
		], 'deploy.version.'.$this->id_deploy_version.'.update', $this->exports());
		
		Chat::emit([
			'room' => [
				'deploy.versions'
			]
		], 'deploy.versions.update', $this->exports());
		
		Chat::emit([
			'room' => [
				'deploy.server.'.$this->id_deploy_server.'.versions'
			]
		], 'deploy.server.'.$this->id_deploy_server.'.versions.update', $this->exports());
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('deploy_version')
			->idVar('id_deploy_version')
			->load($id);
	}
}