<?php

class Cana_Cache extends Cana_Model {
	public function __construct($params) {
		if (isset($params['dir'])) {
			$this->dir = $params['dir'];
		}
		if (isset($params['expire'])) {
			$this->expire = $params['expire'];
		}
	}
	
	public function cached($fileName, $expire = null) {
		if (file_exists($this->dir.sha1($fileName).'.cache') && filemtime($this->dir.sha1($fileName).'.cache') < time()+(!is_null($expire) ? $expire : $this->expire)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function read($fileName)  {
		return unserialize(file_get_contents($this->dir.sha1($fileName).'.cache'));
	}

	public function write($fileName, $file)  {
		return file_put_contents($this->dir.sha1($fileName).'.cache', serialize($file));
	}

}