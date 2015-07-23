<?

class Crunchbutton_Message_Push_Android extends Crunchbutton_Message {
	public static function send($to, $message = null, $id = null, $count = null, $title = 'Cockpit') {


		if (is_array($to)) {

			$message = $to['message'];

			if (isset($to['count'])) {
				$count = $to['count'];
			}

			if (isset($to['sound'])) {
				$sound = $to['sound'];
			}

			if (isset($to['id'])) {
				$id = $to['id'];
			}

			if (isset($to['category'])) {
				$category = $to['category'];
			}

			if (isset($to['title'])) {
				$title = $to['title'];
			}

			if (isset($to['verbose'])) {
				$verbose = $to['verbose'] ? true : false;
			}

			if (isset($to['subtitle'])) {
				$subtitle = $to['subtitle'] ? $to['subtitle'] : '';
			}
			if (isset($to['tickerText'])) {
				$tickerText = $to['tickerText'] ? $to['tickerText'] : '';
			}

			if (isset($to['id'])) {
				$id = $to['id'] ? $to['id'] : null;
			}

			$to = $to['to'];
		}

		if (!$to || !$message) {
			return false;
		}

		$msg = [
			'message' 	=> $message,
			'title'		=> $title,
			'subtitle'	=> $subtitle,
			'tickerText'	=> $tickerText,
			'id'	=> $id,
			'vibrate'	=> 1,
			'sound'		=> 1,
			//'largeIcon'	=> 'large_icon',
			//'smallIcon'	=> 'small_icon'
		];

		$fields = [
			'registration_ids' => [$to],
			'data' => $msg,
			'userIp' => c::config()->gcm->ip
		];

		$headers = [
			'Authorization: key='.c::config()->gcm->key,
			'Content-Type: application/json'
		];

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
		curl_setopt($ch,CURLOPT_POST, true);
		curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($fields));
		$result = json_decode(curl_exec($ch));
		$e = curl_error($ch);
		$h = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($verbose && !$result || !$result->success) {
			echo "GCM response\n";
			var_dump($fields);
			var_dump($h);
			if ($e) {
				var_dump($e);
			}
			var_dump($result);
		}

		curl_close($ch);

		return ['status' => $result->success ? true : false];

	}
}
