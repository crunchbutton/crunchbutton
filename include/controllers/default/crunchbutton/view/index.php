<?php

class Controller_view extends Cana_Controller {
	public function init() {
		$file = preg_replace('/\.html/','',c::getPagePiece(1));
		$file = preg_replace('/[^a-z\.\-_]/','',$file);

		if ($file == 'body') {
			$file = 'layout/html.body';
			c::view()->content = c::view()->render('home/index');
		} else {
			$file = 'frontend/'.$file;
		}
		
		if ($_REQUEST['theme']) {
			c::config()->site->theme = preg_replace('/[^a-z]/','',$_REQUEST['theme']);
			c::buildView(['layout' => c::config()->defaults->layout]);
			$theme = $_REQUEST['theme'];
		} else {
			$theme = 'crunchbutton';
		}

		if (file_exists(c::config()->dirs->view.'default/crunchbutton/'.$file.'.phtml')) {
			c::view()->layout('layout/blank');
			c::view()->export = true;
			c::view()->display($file, ['display' => true, 'filter' => false]);
		}
		exit;
	}
}
