<?

class Crunchbutton_Message_Sms extends Crunchbutton_Message {
	public static function send($from, $to = null, $message = null) {
		
		$break = false;

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

		$env = c::getEnv();
		if (!$from || $from == 'customer') {
			$from = c::config()->twilio->{$env}->outgoingTextCustomer;
		} elseif ($from == 'driver') {
			$from = c::config()->twilio->{$env}->outgoingTextDriver;
		} elseif ($from == 'restaurant') {
			$from = c::config()->twilio->{$env}->outgoingTextRestaurant;
		}
		
		if ($break) {
			$messages = explode("\n", wordwrap($message, 160, "\n"));
		} else {
			$messages = [$message];
		}
		
		$from = self::formatNumber($from);
		
		if (!$from) {
			return false;
		}

		foreach ($to as $t) {
			$t = self::formatNumber($t);
			
			if (!$to) {
				continue;
			}

			foreach ($messages as $msg) {
				if (!$msg) {
					continue;
				}
				// old twilio
				// $ret[] = c::twilio()->account->sms_messages->create($from, $t, $msg);
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