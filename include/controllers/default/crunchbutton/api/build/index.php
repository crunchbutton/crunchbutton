<?php

class Controller_api_build extends Crunchbutton_Controller_Rest {
	public function init() {
	
		// generate a list of assets to be used for the app
		$files = [];

		// views
		foreach (Crunchbutton_Util::frontendTemplates(true) as $file) {
			$files[] = 'view/'.$file.'.html';
		}

		// images
		$exclude = [
			'/admin',
			'/addme',
			'/social',
			'/mprinter',
			'/giftcard',
			'/food',
			'/datepicker',
			'/compat',
			'/admin',
			'/theme',
			'/landing',
			'/sky',
			'/like'
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
		
		$files[] = 'audio/cafe-win.mp3';
		$files[] = 'audio/cafe-win.ogg';
		
		$files[] = 'audio/cafe-start.mp3';
		$files[] = 'audio/cafe-start.ogg';
		
		$files[] = 'audio/cafe-lose.mp3';
		$files[] = 'audio/cafe-lose.ogg';
		
		$files[] = 'audio/cafe-fail.mp3';
		$files[] = 'audio/cafe-fail.ogg';
		
		$files[] = 'audio/cafe-good.mp3';
		$files[] = 'audio/cafe-good.ogg';
		
		// fonts
		$use = '/fontawesome|opensans/i';
		foreach (new DirectoryIterator(c::config()->dirs->www.'assets/fonts') as $fileInfo) {
			if (!$fileInfo->isDot() && preg_match($use, $fileInfo->getBasename())) {
				$files[] = 'fonts/'.$fileInfo->getBasename();
			}
		}
		
		// css
		$files[] = 'css/bundle.css';
		
		// javascript
		$files[] = 'js/bundle.js';
		
		echo json_encode($files);
	}
}
