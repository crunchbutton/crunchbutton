<?php

class Cockpit_Deploy_Server extends Cana_Table {
	public function exports() {
		$ex = $this->properties();
		$ex['version'] = $this->version() ? $this->version()->exports() : null;
		return $ex;
	}
	
	public static function byName($name) {
		return Cockpit_Deploy_Server::q('select * from deploy_server where name="'.$name.'"')->get(0);
	}
	
	public static function currentVersion() {
		return md5(self::byName($_SERVER['SERVER_NAME'])->version()->version);
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

	public function commits() {
		if (!isset($this->_commits)) {
			$this->_commits = [];
			$repo = explode('/', $this->repo);
			$logs = Github::commits($repo[0], $repo[1]);
			if ($logs) {
				$travis = new Travis;
				$status = $travis->status($repo[0],$repo[1]);

				foreach ($logs as $log) {
					$this->_commits[] = [
						'commit' => $log['sha'],
						'author' => $log['commit']['author']['name'],
						'date' => $log['commit']['author']['date'],
						'note' => $log['commit']['message'],
						'status' => $status[$log['sha']]
					];
				}
			}
		}
		return $this->_commits;
	}
	
	public function createTag($version) {
		$name = date('ymd');
		$inc = '00';
		$tags = $this->tags();

		while ($created == false) {
			$inc++;
			foreach ($tags as $tag) {
				$tagName = str_replace('refs/tags/', '', $tag['ref']);
				if ($tagName == $name.$inc) {
					$created = false;
					break;
				}
			}
			$created = true;
			$inc = sprintf('%02d', $inc);
			if ($inc == 99) {
				return false;
			}
		}
		
		$tag = $name.'.'.$inc;

		$repo = explode('/', $this->repo);
		$log = Github::createTag($repo[0], $repo[1], $tag, $version, 'Created from Cockpit Deployment Tool');
		
		$this->_commits = null;

		return $tag;
	}
	
	public function tags() {
		if (!isset($this->_tags) || $this->_tags === null) {
			$repo = explode('/', $this->repo);
			$this->_tags = Github::tags($repo[0], $repo[1]);
		}
		return $this->_tags;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('deploy_server')
			->idVar('id_deploy_server')
			->load($id);
	}
}