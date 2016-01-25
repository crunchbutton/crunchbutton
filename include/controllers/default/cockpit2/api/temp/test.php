<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){

		$set = new Settlement;
		echo '<pre>';var_dump( $set->payDriver( 52919 ) );exit();;

	}
}
