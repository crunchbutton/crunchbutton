<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){
		// $a = Crunchbutton_Address::byAddress( 'Condomínio do Edifício Primula - R. Max Colin, 941 - Centro, Joinville - SC, 89204-040, Brazil' );
		// echo '<pre>';var_dump( $a );exit();
		// Order::ticketsForNotGeomatchedOrders();
		$order = Order::o( 233062 );
		$order->approve_address();
	}
}