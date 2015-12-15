<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){
		$r = Restaurant::o(107);
		echo '<pre>';var_dump( $r->preOrderHours() );exit();
	}
}
