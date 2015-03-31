<?php

class Controller_Api_Test_Sandbox extends Cana_Controller {
	public function init(){

		$order = Order::o( $_GET[ 'id_order' ] );
		if( $order->id_order ){
			echo '<pre>';var_dump( $order->refund() );exit();;
		} else {
			echo '<pre>';var_dump( 'error' );exit();
		}

	}
}