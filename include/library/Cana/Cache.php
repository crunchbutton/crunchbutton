<?php

class Cana_Cache extends Cana_Model {
	public $suffix = '.cache';

	public function __construct($params) {
		if (isset($params['dir'])) {
			$this->dir = $params['dir'];
		}
		if (isset($params['expire'])) {
			$this->expire = $params['expire'];
		}
		if (isset($params['suffix'])) {
			$this->suffix = $params['suffix'];
		}
	}
	
	public function cached($fileName, $expire = null) {
		if (file_exists($this->dir.sha1($fileName).$suffix) && filemtime($this->dir.sha1($fileName).$suffix) < time()+(!is_null($expire) ? $expire : $this->expire)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function read($fileName)  {
		return unserialize(file_get_contents($this->dir.sha1($fileName).$suffix));
	}

	public function write($fileName, $file)  {
		return file_put_contents($this->dir.sha1($fileName).$suffix, serialize($file));
	}
	
	public function mtime($fileName)  {
		return filemtime($this->dir.sha1($fileName).$suffix);
	}

}