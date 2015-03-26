<?php

class Controller_api_test_sandbox extends Cana_Controller {
	public function init(){
		$order = Order::o( 99581 );
		echo '<pre>';var_dump( $order->hasGiftCardIssued() );exit();;
	}
}