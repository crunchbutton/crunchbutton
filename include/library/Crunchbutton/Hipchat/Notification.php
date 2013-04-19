<?php

use Httpful\Request;

class Crunchbutton_Hipchat_Notification extends Cana_Model {

	public static $api_url = 'http://api.hipchat.com/v1/rooms/message';
	public static $auth_token = '2bd13c07d029047ff5fb6045ee8d07';
	public static $room_id = '171095';
	public static $from = 'CBNotify';
	public static $color_notification = 'yellow';
	public static $color_urgent = 'red';

	public static function OrderPlaced($order) {
		$msg = 'An order was placed! '.
			$order->message('sms');
		self::sendNotification($msg);
	}

	public static function sendNotification($msg) {
		$msg = urlencode(str_replace('\n', ' ', $msg));
		$env = c::env() == 'live' ? 'live' : 'dev';
		$msg = "[env:$env] " . $msg;
		$url = self::$api_url.
			'?auth_token='.self::$auth_token.
			'&room_id='.self::$room_id.
			'&from='.self::$from.
			'&color='.self::$color_notification.
			'&notify=0'. // don't blink the chat window
			'&format=json'.
			'&message='.$msg;
		$req = \Httpful\Request::get($url);
		$req->expects('json');
		$rsp = $req->sendIt();
		error_log($rsp);
	}

	public static function sendUrgentNotification($msg) {
		$msg = urlencode(str_replace('\n', ' ', $msg));
		$msg = '[env:'.c::env().'] ' . $msg;
		$url = self::$api_url.
			'?auth_token='.self::$auth_token.
			'&room_id='.self::$room_id.
			'&from='.self::$from.
			'&color='.self::$color_urgent.
			'&notify=1'. // blink the chat window
			'&format=json'.
			'&message='.$msg;
		$req = \Httpful\Request::get($url);
		$req->expects('json');
		$rsp = $req->sendIt();
		error_log($rsp);
	}
}

?>
