<?php

class Controller_view extends Cana_Controller {
	public function init() {
		$file = preg_replace('/\.html/','',c::getPagePiece(1));
		$file = preg_replace('/[^a-z]/','',$file);
		
		if ($file == 'body') {
			$file = 'layout/html.body';
		} else {
			$file = 'frontend/'.$file;
		}
		if (file_exists(c::config()->dirs->view.'default/crunchbutton/'.$file.'.phtml')) {
			c::view()->layout('layout/blank');
			c::view()->display($file, ['display' => true, 'filter' => false]);
		}
		exit;
	}
}
