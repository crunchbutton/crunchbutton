<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){

		$driver = Admin::o( 5 );
		// $driver->stopHelpOutNotification();
		echo '<pre>';var_dump( $driver->couldReceiveHelpOutNotification() );exit();

		// $order = Order::o( 240724 );
		// $notification = Crunchbutton_Admin_Notification::o( 3 );
		// echo $notification->getSmsMessage( $order, null, 'push', null, true );

	}
}
