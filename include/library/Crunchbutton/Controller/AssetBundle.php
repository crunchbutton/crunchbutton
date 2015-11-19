<?php

class Crunchbutton_Controller_AssetBundle extends Cana_Controller {
	private $_cacheId;

	public function assets($dir) {
		$directory = c::config()->dirs->www.'assets/'.$dir.'/';
		$iterator = new DirectoryIterator($directory);

		foreach ($iterator as $fileinfo) {
			if ($fileinfo->isFile()) {
				$files[] = $directory.$fileinfo->getFilename();
			}
		}
		return $files;
	}

	public function cacheServe($id) {

		$git = Deploy_Server::currentVersion();
		$v = $git ? $git : $_REQUEST['v'];

		$headrs = apache_request_headers();
		foreach ($headrs as $head => $er) {
			$headers[strtolower($head)] = $er;
		}
		$nocache = ($_REQUEST['nocache'] || $headers['pragma'] == 'no-cache' || $headers['cache-control'] == 'no-cache') ? true : false;

		$cacheid = $this->cacheId() ? $this->cacheId() : $id.$v.$_REQUEST['s'];

		if (c::app()->cache()->cached($cacheid)) {
			$mtime = c::cache()->mtime($cacheid);

			// doesnt work
			/*
			if (isset($headers['if-modified-since']) && !$nocache) {
				header('Last-Modified: '.gmdate('D, d M Y H:i:s',$mtime).' GMT', true, 304);
				exit;
			}
			*/
			$cached = true;
		}


		if ($cached && !$_REQUEST['nocache']) {
			$data = c::app()->cache()->read($cacheid);

		} else {
			$res = $this->getData();
			c::app()->cache()->write($cacheid, $res['data']);
			$mtime = $res['mtime'];
			$data = $res['data'];
		}
		if (is_array($data)) {
			echo $cacheid."\n";
			print_r(array_keys($data));
			exit;
		}

		header('HTTP/1.1 200 OK');
		header('Date: '.date('r'));
		header('Last-Modified: '.gmdate('D, d M Y H:i:s',$mtime).' GMT');
		header('Accept-Ranges: bytes');
		header('Content-Length: '.strlen($data));
		header('Content-type: text/css');
		header('Vary: Accept-Encoding');
		header('Cache-Control: max-age=290304000, public');

		echo $data;
		exit;
	}

	public function serve($files, $quiet = true) {
		foreach ($files as $key => $file) {
			if (!file_exists($file)) {
				unset($files[$key]);
			}
		}

		Minify::setCache(c::config()->dirs->cache.'/min/');
		return Minify::serve('Files', [
			'files'  => $files,
			'maxAge' => 86400,
			'quiet' => $quiet,
			'invalidate' => true
		]);
    }

    public function cacheId($id = null) {
    	if ($id) {
	    	$this->_cacheId = $id;
    	}
    	return $this->_cacheId;
    }
}
