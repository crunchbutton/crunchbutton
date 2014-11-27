<?

class Crunchbutton_Message_Sms extends Crunchbutton_Message {
	public static function numbers() {
		return explode(',',c::config()->site->config('twilio-number')->value);
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

		if (!$to || !$message) {
			return false;
		}

		if (!is_array($to)) {
			$to = [$to];
		}

		$numbers = self::numbers();
		$from = '+1'.$numbers[array_rand($numbers)];
		$message = trim($message);

		if ($break) {
			$messages = explode("\n", wordwrap($message, 160, "\n"));
		} else {
			$messages = [$message];
		}

		$from = self::formatNumber($from);

		if (!$from) {
			return false;
		}

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
			
			$t = self::formatNumber($t);

			if (!$t) {
				continue;
			}

			// dont message yourself
			if (c::admin()->id_admin && self::formatNumber(c::admin()->txt) == $t) {
				continue;
			}
			
			// dont message our own numbers
			if (in_array($t, $numbers)) {
				continue;
			}

			foreach ($messages as $msg) {
				if (!$msg) {
					continue;
				}
				
				if ($tz) {
					$msg = preg_replace_callback('/%DATETIMETZ:(.*)%/', function($matches){
						$date = new DateTime($matches[1], new DateTimeZone(c::config()->timezone));
						$date = $date->format('n/j g:iA T');
						return $date;
					}, $msg);
				}

				try {
					Log::debug([
						'action' => 'sending sms',
						'to' => $t,
						'from' => $from,
						'msg' => $msg,
						'type' => 'sms'
					]);

					$ret[] = c::twilio()->account->messages->sendMessage($from, $t, $msg);

				} catch (Exception $e) {

					Log::error([
						'action' => 'sending sms',
						'to' => $t,
						'from' => $from,
						'msg' => $msg,
						'type' => 'sms'
					]);
				}
			}
		}

		return $ret;
	}
}