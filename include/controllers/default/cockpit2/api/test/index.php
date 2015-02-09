<?php

class Controller_api_test extends Cana_Controller {
	public function init(){

		echo '<pre>';var_dump( Crunchbutton_Admin_Notification::TYPE_SMS );exit();

		Crunchbutton_Message_Sms::send([
			'to' => Crunchbutton_Support::getUsers(),
			'message' => 'testing',
			'reason' => Crunchbutton_Message_Sms::REASON_SUPPORT_WARNING
		]);

		// $res = Restaurant::o( 110 );
		// echo '<pre>';var_dump( $res->adminReceiveSupportSMS() );exit();

		// $admins = Crunchbutton_Support::getUsers();
		// echo '<pre>';var_dump( $admins );exit();

		// $restaurant = Crunchbutton_Restaurant::o(107);
		// echo '<pre>';var_dump(  $restaurant->smartETA()  );exit();;
		// $order = Order::o( 58027 );
		// Crunchbutton_Order_Eta::create( $order );
	}
}