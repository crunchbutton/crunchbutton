<?php

class Controller_Api_Test_Sandbox extends Crunchbutton_Controller_Account {

	public function init() {

		// $order = Order::o( 121265 );
		// $order->pexcardFunds();


		if( $_GET && $_GET[ 'id_queue' ] ){
			echo $_GET[ 'id_queue' ];
			$q = Crunchbutton_Queue_Order_PexCard_Funds::o( $_GET[ 'id_queue' ] );
			$q->run();
		}

		// Crunchbutton_Queue::process();

	}
}