<?php
class Controller_api_temp_test extends Crunchbutton_Controller_Rest {
	public function init() {
		$community = Community::o(6);
		echo '<pre>';var_dump( $community->communityDirectorGroup() );exit();
	}
}