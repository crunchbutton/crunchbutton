<?php

class Controller_Api_Test_Sandbox extends Crunchbutton_Controller_Account {
	public function init() {
		// $orders = Order::ticketsForNotGeomatchedOrders();
		$admin = Admin::o( 572 );
		$pay_type = Admin::o( 572 )->payment_type();
		echo '<pre>';var_dump( $pay_type->using_pex_date()->format( 'Ymd' ) );exit();
	}
}