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
			
			$to = $to['to'];
		}

		if (!$to || !$message) {
			return false;
		}

		$msg = [
			'message' 	=> $message,
			'title'		=> $title,
			'subtitle'	=> '',
			'tickerText'	=> '',
			'vibrate'	=> 1,
			'sound'		=> 1,
			//'largeIcon'	=> 'large_icon',
			//'smallIcon'	=> 'small_icon'
		];

		$fields = [
			'registration_ids' 	=> [$to],
			'data'			=> $msg
		];

		$headers = [
			'Authorization: key=_KEY_',
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
		curl_close($ch);
		
		return $result->success ? true : false;

	}
}