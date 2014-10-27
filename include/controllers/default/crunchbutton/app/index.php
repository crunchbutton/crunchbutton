<?php

class Controller_app extends Cana_Controller {
	public function init() {
		if (c::app()->detect()->match('android')) {
			header('Location: https://play.google.com/store/apps/details?id=com.crunchbutton');
		} else {
			header('Location: https://itunes.apple.com/app/id721780390');
		}
		exit;
	}
}