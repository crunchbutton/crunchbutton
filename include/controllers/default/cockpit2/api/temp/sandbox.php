<?php

class Controller_api_temp_sandbox extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$q = '
			select * from `order`
			where date >= now()
			limit 10
		';
		$orders = Order::q($q);

		foreach ($orders as $order) {
			echo 'runing rules for '.$order->id_order."\n";

			$rules = new Crunchbutton_Order_Rules();
			$rules->run($order);
		}

		echo 'done ruling.';

		// it always must call finished method at the end
		// $this->finished();


		die( "hard" );
		$n = new Crunchbutton_Admin_Notification;


		$order = Crunchbutton_Order::o( 171290 );
		echo '<pre>';var_dump( $order->status()->last()[ 'status' ] );exit();

		sleep(3);

		$sm = new Support_Message;
		$sm->type = 'note';
		$sm->from = 'system';
		$sm->visibility = 'internal';
		$sm->id_support = 81744;
		$sm->phone = '***REMOVED***';
		$sm->date = date( 'Y-m-d H:i:s' );
		$sm->body = 'testing system: ' .  date( 'Y-m-d H:i:s' );
		$sm->save();

		sleep(5);

		$sm = new Support_Message;
		$sm->type = 'sms';
		$sm->from = 'client';
		$sm->visibility = 'external';
		$sm->id_support = 81744;
		$sm->name = 'Daniel';
		$sm->phone = '***REMOVED***';
		$sm->date = date( 'Y-m-d H:i:s' );
		$sm->body = 'testing as a customer: ' .  date( 'Y-m-d H:i:s' );
		$sm->save();

		//
		// $sm = Support_Message::o( 519347 );
		//
		// $res = Event::emit([
		// 	'room' => [
		// 		'ticket.'.$sm->id_support,
		// 		'tickets'
		// 	]
		// ], 'message', $sm->exports($guid));
		// echo '<pre>';var_dump( $res );exit();
	}
}
