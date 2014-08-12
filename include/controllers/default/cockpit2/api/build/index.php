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

		];
		$path = c::config()->dirs->www.'assets/cockpit/images';
		$dir  = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
		$fs = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::SELF_FIRST);
		
		foreach ($fs as $fileInfo) {
			if ($fileInfo->getBasename() == '.DS_Store') {
				continue;
			}
			$p = str_replace($path,'',$fileInfo->getPath());
			if ($fileInfo->isFile() && ((!$p && substr($fileInfo->getBasename(),0,1) != '.') || ($p && !in_array($p, $exclude)))) {
				$files[] = str_replace('//','/','images/'.$p.'/'.$fileInfo->getBasename());
			}
		}

		
		// fonts
		$use = '/fontawesome|opensans/i';
		foreach (new DirectoryIterator(c::config()->dirs->www.'assets/fonts') as $fileInfo) {
			if (!$fileInfo->isDot() && preg_match($use, $fileInfo->getBasename())) {
				$files[] = 'fonts/'.$fileInfo->getBasename();
			}
		}
		
		// css
		$files[] = 'css/bundle.css?s=cockpit';
		
		// javascript
		$files[] = 'js/bundle.js?s=cockpit';
		
		echo json_encode([
			'version' => Cana_Util::gitVersion(),
			'files' => $files
		]);
	}
}
