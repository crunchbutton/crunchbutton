<?php

class Controller_api_build_config extends Crunchbutton_Controller_Rest {
	public function init() {
		$env = $_REQUEST['__live'] ? 'live' : 'beta';
		$c = (object)[];
		switch ($env) {
			case 'live':
				$c->balanced = c::config()->balanced->live->uri;
				$c->facebook = c::config()->facebook->live->app;
				break;
			case 'beta':
				$c->balanced = c::config()->balanced->dev->uri;
				$c->facebook = c::config()->facebook->beta->app;
				break;
		}
		echo json_encode($c);
		
	}
}
	