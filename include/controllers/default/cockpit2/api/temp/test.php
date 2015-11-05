<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){
		$test = Order::o( 219755 );
		$test->notifyDriverAboutCustomerChanges( [ 'name' => 'NAME', 'phone' =>  555, 'address' => 'Address' ] );
	}
}