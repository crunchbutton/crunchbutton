<?php

class Controller_view extends Cana_Controller {
	public function init() {
		$file = preg_replace('/\.html/','',c::getPagePiece(1));
		$file = preg_replace('/[^a-z]/','',$file);
		$file = c::config()->dirs->view.'default/crunchbutton/frontend/'.$file.'.phtml';

		if (file_exists($file)) {
			echo file_get_contents($file);
		}	
		exit;
	}
}
