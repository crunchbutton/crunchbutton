<?php

class Crunchbutton_Controller_AssetBundle extends Cana_Controller {
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
	public function __construct() {
		define('MINIFY_MIN_DIR', c::config()->dirs->library . 'Minify/old/lib');
		set_include_path(MINIFY_MIN_DIR . PATH_SEPARATOR . get_include_path());
		require 'Minify.php';
	}
	
	public function serve($files) {
		Minify::setCache(c::config()->dirs->cache.'/min/');
		Minify::serve('Files', [
			'files'  => $files,
			'maxAge' => 86400
		]);
		exit;

    }
}