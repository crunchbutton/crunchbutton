<?php

class Controller_api_travisci extends Crunchbutton_Controller_Rest {
	public function init() {
		switch (c::getPagePiece(2)) {
			case 'build':
				// just send an event telling us to refresh
				$res = Event::create([
					'room' => [
						'travisci.builds'
					]
				], 'update');
				break;
		}

	}
}