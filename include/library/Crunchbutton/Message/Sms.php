<?

class Crunchbutton_Message_Sms extends Crunchbutton_Message {

	public static function number($t = null) {
		if ($t) {
			$phone = Phone::byPhone($t);
			$num = $phone->from();
		} else {
			$num = Phone::least()->phone;
		}
		return Phone::dirty($num);
	}

	public static function send($from, $to = null, $message = null) {

		$break = false;
		$ret = [];

		if (is_array($from)) {
			$to = $from['to'];
			$message = $from['message'];

			if (isset($from['break'])) {
				$break = $from['break'] ? true : false;
			}

			$from = $from['from'];
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

					$ret[] = c::twilio()->account->messages->sendMessage($tfrom, $t, $msg);

					Phone_Log::log($t, $tfrom, 'message', 'outgoing');

				} catch (Exception $e) {

					Log::error([
						'action' => 'sending sms',
						'to' => $t,
						'from' => $tfrom,
						'msg' => $msg,
						'type' => 'sms'
					]);
				}
			}
		}

		return $ret;
	}

	public function greeting( $name = null ){
		if( $name ){
			return $name . ', ' . "\n";
		}
		$greetings = [ 'Hey, ', 'Hi, ', 'Heya, ', 'Hey! ', 'Hola ', 'Bonjour ', 'Hi! ', 'Ola, ' ];
		return $greetings[ array_rand( $greetings, 1 ) ] . "\n";
	}

}