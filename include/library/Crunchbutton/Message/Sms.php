<?php

class Crunchbutton_Message_Sms extends Crunchbutton_Message {

	const REASON_SUPPORT = 'support';
	const REASON_BROADCAST = 'broadcast';
	const REASON_APP_DOWNLOAD = 'app download';
	const REASON_SUPPORT_WARNING = 'support warning';
	const REASON_SUPPORT_SUGGESTION = 'support suggestion';
	const REASON_PASSWORD_RESET = 'password reset';
	const REASON_DRIVER_SETUP = 'driver setup notify';
	const REASON_REP_SETUP = 'rep setup notify';
	const REASON_DRIVER_ORDER_CANCELED = 'driver order canceled';
	const REASON_DRIVER_ORDER = 'driver new order';
	const REASON_DRIVER_NOTIFIES_CUSTOMER = 'driver notifies customer';
	const REASON_DRIVER_SHIFT = 'driver shift';
	const REASON_BLAST = 'blast';
	const REASON_CUSTOMER_ORDER = 'customer new order';
	const REASON_CUSTOMER_DRIVER = 'customer about driver';
	const REASON_GIFT_CARD = 'gift card';
	const REASON_GIFT_CARD_REDEEMED = 'gift card redeemed';
	const REASON_SETTLEMENT_FAIL = 'settlement fail';
	const REASON_AUTO_REPLY = 'auto reply';

	public static function number($t = null) {
		if ($t) {
			$phone = Phone::byPhone($t);
			$num = $phone->from();
		} else {
			$num = Phone::least()->phone;
		}
		return Phone::dirty($num);
	}

	public static function send($from, $to = null, $message = null, $media = null, $log = false) {

		$break = false;
		$ret = [];

		$reason = '';

		if (is_array($from)) {

			$to = $from['to'];

			$message = $from['message'];

			if (isset($from['break'])) {
				$break = $from['break'] ? true : false;
			}

			if( isset( $from['reason'] ) ){
				$reason = $from['reason'];
			}

			if( isset( $from['media'] ) ){
				$media = $from['media'];
			}

			if( isset( $from['log'] ) && $from['log'] ){
				$log = $from['log'];
			}

			$from = $from['from'];
		}

		// If there is no cs working text everyone
		// https://github.com/crunchbutton/crunchbutton/issues/4475#issuecomment-73421357
		if( is_array($to) && count( $to ) == 0 && ( $reason == Crunchbutton_Message_Sms::REASON_SUPPORT || $reason == Crunchbutton_Message_Sms::REASON_SUPPORT_WARNING ) ){
			$to = Crunchbutton_Support::getUsers();
			if( count( $to ) == 0 ){
				$to = Crunchbutton_Support::getUsers( true );
			}
		}

		// @todo: remove all from things elsewhere on the site
		$from = null;

		if (!$to || !$message) {
			return false;
		}

		if (c::env() == 'travis') {
			$to = '_PHONE_';
		} elseif (c::getEnv() != 'live') {
			$to = c::admin()->testphone ? c::admin()->testphone : '_PHONE_';
		}

		if (!is_array($to)) {
			$to = [$to];
		}

		$message = trim($message);

		foreach ($to as $user) {
			$tz = null;
			if (is_array($user)) {
				if ($user['tz']) {
					$tz = $user['tz'];
				}
				$t = $user['num'];
			} else {
				$t = $user;
			}

			if( $t == '0000000000' ){
				continue;
			}

			$t = Phone::dirty($t);

			if (!$t) {
				continue;
			}

			// dont message yourself
			if (c::admin()->id_admin && Phone::dirty(c::admin()->txt) == $t) {
				continue;
			}

			// dont message our own numbers
			if (in_array($t, Phone::numbers())) {
				continue;
			}

			$tfrom = $from ? $from : self::number($t);

			if ($tz) {
				$messages = preg_replace_callback('/%DATETIMETZ:(.*)%/', function($matches){
					$date = new DateTime($matches[1], new DateTimeZone(c::config()->timezone));
					$date = $date->format('n/j g:iA T');
					return $date;
				}, $messages);
			}

			if ($break) {
				$messages = explode("\n", wordwrap($message, 160, "\n"));
			} else {
				$messages = [$message];
			}

			foreach ($messages as $msg) {
				if (!$msg) {
					continue;
				}

				try {
					Log::debug([
						'action' => 'sending sms',
						'to' => $t,
						'from' => $tfrom,
						'msg' => $msg,
						'getEnv' => c::getEnv(),
						'type' => 'sms'
					]);

					$params = [];
					if( $log && c::getEnv() == 'live' ){
						$params = [ 'StatusCallback' => 'http://live.ci.crunchbutton.crunchr.co/api/twilio/sms/status' ];
					}

					$_ret = c::twilio()->account->messages->sendMessage($tfrom, $t, $msg, $media ? $media : null, $params);

					$phoneLog = Phone_Log::log($t, $tfrom, 'message', 'outgoing', $reason, $_ret->sid, $_ret->status);

					$_ret->id_phone_log = $phoneLog->id_phone_log;

					$ret[] = $_ret;

				} catch (Exception $e) {

					Log::error([
						'action' => 'sending sms',
						'to' => $t,
						'from' => $tfrom,
						'msg' => $msg,
						'type' => 'sms',
						'message' => $e->getMessage()
					]);
				}
			}
		}

		return $ret;
	}

	public static function greeting( $name = null ){
		if( $name ){
			return $name . ', ' . "\n";
		}
		return '';
		// find a way to never have to use a greeting in SMS; always use first name #4372
		// $greetings = [ 'Hey, ', 'Hi, ', 'Heya, ', 'Hey! ', 'Hola ', 'Bonjour ', 'Hi! ', 'Ola, ' ];
		// return $greetings[ array_rand( $greetings, 1 ) ] . "\n";
	}

	public static function endGreeting( $name = null, $punctuation="!", $newline = ""){
		if( $name ){
			return ', ' . $name. $punctuation.$newline;
		}
		return $punctuation.$newline;
		// find a way to never have to use a greeting in SMS; always use first name #4372
		// $greetings = [ 'Hey, ', 'Hi, ', 'Heya, ', 'Hey! ', 'Hola ', 'Bonjour ', 'Hi! ', 'Ola, ' ];
		//
	}

}
