<?php

class Controller_admin_test extends Crunchbutton_Controller_Account {
	public function init() {

		$order = Order::o(c::getPagePiece(2));

		echo '<pre>';
		foreach ( $order->restaurant()->notifications() as $n ) {
			echo $n->type;
			$n->send( $order );
			echo '<br/>';
		};

		/*
		c::config()->domain->theme = 'test';
		c::buildView(['layout' =>  c::config()->defaults->layout]);
		c::view()->useFilter(false);

		c::view()->display('admin/test/index');
		*/
	}
}