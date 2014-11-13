<?php

class Cockpit_Deploy_Server extends Cana_Table {
	public function exports() {
		$ex = $this->properties();
		$ex['version'] = $this->version() ? $this->version()->exports() : null;
		return $ex;
	}

	public function version() {
		if (!isset($this->_version)) {
			$this->_version = Cockpit_Deploy_Version::q('
				select * from deploy_version
				where status="success"
				and id_deploy_server="'.$this->id_deploy_server.'"
				order by date desc
				limit 1
			')->get(0);
		}
		return $this->_version;
	}
	
	public function versions() {
		if (!isset($this->_versions)) {
			$this->_versions = Cockpit_Deploy_Version::q('
				select * from deploy_version
				where id_deploy_server="'.$this->id_deploy_server.'"
				order by date desc
			');
		}
		return $this->_versions;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('deploy_server')
			->idVar('id_deploy_server')
			->load($id);
	}
}