<?

class Crunchbutton_Message_Call extends Crunchbutton_Message {
	public static function send($from, $to = null, $url = null, $callback = null) {

		if (is_array($from)) {
			$to = $from['to'];
			$url = $from['url'];
			$callback = $from['callback'];
			$from = $from['from'];
		}

		if (!$to || !$url) {
			return false;
		}

		$env = c::getEnv();
		if (!$from || $from == 'customer') {
			$from = c::config()->twilio->{$env}->outgoingCustomer;
		} elseif ($from == 'driver') {
			$from = c::config()->twilio->{$env}->outgoingDriver;
		} elseif ($from == 'restaurant') {
			$from = c::config()->twilio->{$env}->outgoingRestaurant;
		}

		$from = self::formatNumber($from);
		$to = self::formatNumber($to);

		if ($callback) {
			$callback = ['StatusCallback' => 'https://'.c::config()->host_callback.$callback];
		}

		if (!$from || !$to) {
			return false;
		}

		try {
			Log::debug([
				'action' => 'calling',
				'to' => $to,
				'from' => $from,
				'msg' => $url,
				'callback' => $callback,
				'type' => 'call'
			]);

			$call = c::twilio()->account->calls->create($fro, $to, $url, $callback);

		} catch (Exception $e) {
			Log::error([
				'action' => 'call failed',
				'to' => $to,
				'from' => $from,
				'msg' => $url,
				'callback' => $callback,
				'type' => 'call'
			]);
		}

		return $call;
	}
}

