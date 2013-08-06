<?php

class Controller_api_views extends Crunchbutton_Controller_Rest {
	public function init() {

		foreach (new DirectoryIterator(c::config()->dirs->view.'default/crunchbutton/frontend') as $fileInfo) {
			if (!$fileInfo->isDot()) {
				$files[] = $fileInfo->getBasename('.phtml');
			}
		}
		
		echo json_encode($files);
	}
}
