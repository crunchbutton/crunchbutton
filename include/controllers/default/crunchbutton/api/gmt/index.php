<?php

class Controller_api_gmt extends Crunchbutton_Controller_Rest {
	public function init() {
		$utc_str = gmdate( 'Y/n/d/H/i/s', time() );
		echo json_encode( [ 'gmt' => $utc_str ] );
		// echo json_encode(['gmt' => '2015/4/04/19/13/44 ']);
	}
}