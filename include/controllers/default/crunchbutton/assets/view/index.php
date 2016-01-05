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

		if ($_REQUEST['theme']) {
			c::config()->site->theme = preg_replace('/[^a-z]/','',$_REQUEST['theme']);
			c::buildView(['layout' => c::config()->defaults->layout]);
			$theme = $_REQUEST['theme'];
		} else {
			$theme = 'crunchbutton';
		}

		if (file_exists(c::config()->dirs->view.'default/crunchbutton/'.$filePath.'.phtml')) {
			//$filePath = 'frontend/'.$file;

		} elseif(file_exists(c::config()->dirs->view.'default/crunchbutton/'.$file.'/index.phtml')) {
			$filePath = $file.'/index';

		} else {
			$filePath = null;
		}

		if ($filePath) {

			c::view()->layout('layout/blank');
			c::view()->export = true;
			c::view()->display($filePath, ['display' => true, 'filter' => false]);
		}


		exit;
	}
}
