<?php

class Controller_Api_Test_Sandbox extends Crunchbutton_Controller_Account {

	public function init() {

		// $order = Order::o( 121265 );
		// $order->pexcardFunds();

		$q = Crunchbutton_Queue_Order_PexCard_Funds::o( 17953 );
		$q->run();
		// Crunchbutton_Queue::process();

	}
}