<?php

class Controller_api_build extends Crunchbutton_Controller_Rest {
	public function init() {
	
		// generate a list of assets to be used for the app
		$files = [];

		// views
		foreach (new DirectoryIterator(c::config()->dirs->view.'default/crunchbutton/frontend') as $fileInfo) {
			if (!$fileInfo->isDot()) {
				$files[] = 'view/'.$fileInfo->getBasename('.phtml').'.html';
			}
		}

		// images
		$exclude = [
			'/admin',
			'/social',
			'/mprinter',
			'/micro',
			'/giftcard',
			'/food',
			'/datepicker',
			'/compat',
			'/admin'
		];
		$path = c::config()->dirs->www.'assets/images';
		$dir  = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
		$fs = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::SELF_FIRST);
		
		foreach ($fs as $fileInfo) {
			$p = str_replace($path,'',$fileInfo->getPath());
			if ($fileInfo->isFile() && ((!$p && substr($fileInfo->getBasename(),0,1) != '.') || ($p && !in_array($p, $exclude)))) {
				$files[] = str_replace('//','/','images/'.$p.'/'.$fileInfo->getBasename());
			}
		}
		
		// audio
		$files[] = 'audio/crunch.mp3';
		$files[] = 'audio/crunch.ogg';
		
		// javascript
		$files[] = 'js/bundle.js';
		
		// css
		$files[] = 'css/bundle.css';
		
		// fonts
		$use = '/fontawesome|opensans/i';
		foreach (new DirectoryIterator(c::config()->dirs->www.'assets/fonts') as $fileInfo) {
			if (!$fileInfo->isDot() && preg_match($use, $fileInfo->getBasename())) {
				$files[] = 'fonts/'.$fileInfo->getBasename();
			}
		}
		
		echo json_encode($files);
	}
}
