<?php

class Controller_assets_view extends Cana_Controller {
	public function init() {
		$file = preg_replace('/\.html/','',c::getPagePiece(2));
		$file = preg_replace('/[^a-z\.\-_]/','',$file);

		if ($file == 'body') {
			$filePath = 'layout/html.body';
			c::view()->content = c::view()->render('home/index');
		} else {
			$filePath = 'frontend/'.$file;
		}



		$path = c::config()->dirs->view.'default/cockpit2/frontend';
		$directory = new \RecursiveDirectoryIterator($path);
		$iterator = new \RecursiveIteratorIterator($directory);

		foreach ($iterator as $fileInfo) {
			$name = $fileInfo->getFilename();
			if ($name{0} == '.') {
				continue;
			}

			if (str_replace('.phtml', '', $fileInfo->getFileName()) == $file) {
				$filePath = substr(str_replace('.phtml','',str_replace($path, '',$fileInfo->getPathname())),1);
				break;
			}
		}

		if (!$filePath) {
			header('HTTP/1.0 404 Not Found');
			exit;
		}

		c::view()->layout('layout/blank');
		c::view()->export = true;
		c::view()->display('frontend/'.$filePath, ['display' => true, 'filter' => false]);
		exit;
	}
}
