<?php

class Controller_api_temp_pushdevin extends Crunchbutton_Controller_RestAccount {
	public function init() {
		//$c = Crunchbutton_Admin_Notification::q('select * from admin_notification where id_admin=1 and active=true and type=?', [Crunchbutton_Admin_Notification::TYPE_PUSH_IOS])->get(0);
/*
		$r = Crunchbutton_Message_Push_Ios::send([
			'to' => 'addc8d82f9faf739a5c47d10e21041176bd5ba8695bce9e36c6eae47e37c4aac',
			'message' => 'well hello there',
			'count' => rand(1,999),
			'id' => 'new-message',
			'env' => c::getEnv(),
			'app' => 'crunchbutton'
		]);

		var_dump($r);
		*/

		/*
		$r = Crunchbutton_Message_Push_Ios::send([
			'to' => 'b85dc7710abcd6a18aa6ff91ca165aa97fa02df23323d49c689a7d50fd47e800',
			'message' => 'well hello there',
			'count' => rand(1,999),
			'id' => 'new-message',
			'env' => c::getEnv(),
			'app' => 'cockpit'
		]);
		*/

		$cockpit = ['bda4c763f2e2f2ec8b123a960fd2e9ecba591cf4a310253708156eed658a4bb2','b85dc7710abcd6a18aa6ff91ca165aa97fa02df23323d49c689a7d50fd47e800'];

		$order = Order::o(1);
		$message = '#'.$order->id.': '.$order->user()->name.' has placed an order to '.$order->restaurant()->name.'.';
// cockpit
// live - 8d9b2a99aa4754686eb76ff3a20c007c808470a7327107e786f6cf0e1696f7ac
// beta - b85dc7710abcd6a18aa6ff91ca165aa97fa02df23323d49c689a7d50fd47e800

// crunchbutton
// beta - addc8d82f9faf739a5c47d10e21041176bd5ba8695bce9e36c6eae47e37c4aac
		$r = Crunchbutton_Message_Push_Ios::send([
			'to' => '499b145345bbf9363ec3abdb44ef9927170c4b74963674d60814b3639ab0cc4b',
			'message' => $message,
			'count' => 0,
			'id' => 'order-'.$order->id,
			'sound' => Crunchbutton_Message_Push_Ios::SOUND_NEW_ORDER,
			'showInForeground' => true,
			'link' => '/work',
			'app' => 'crunchbutton',
			'env' => 'dev'
		]);

		var_dump($r);

		exit;


	}
}
