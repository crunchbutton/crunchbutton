<?php

class Crunchbutton_Message_Incoming_Sms extends Cana_Model {
	public static function route($request) {

		$from = Phone::clean($request['From']);
		$to = Phone::clean($request['To']);
		$body = trim($request['Body']);
		$admin = Admin::getByPhone($from, true);
		$numMedia = trim($request['NumMedia']);

		if ($numMedia) {

			for ($x = 0; $x < $numMedia; $x++) {

				$m = trim($request['MediaUrl'.$x]);
				if ($m) {
					$media[] = $m;
				}
			}
		}

		Log::debug([
			'type' => 'incoming-sms',
			'action' => 'message received',
			'id_admin' => $admin->id_admin,
			'name' => $admin->name,
			'from' => $from,
			'body' => $body,
			'media' => json_encode($media)
		]);

		if (!$from || (!$body && !$media)) {
			// error
			header('HTTP/1.0 400 Bad Request');
			exit;
		}

		$params = [
			'body' => $body,
			'media' => $media,
			'from' => $from,
			'to' => $to,
			'sid' => $request['SmsMessageSid']
		];

		Phone_Log::log($to, $from, 'message', 'incoming');

		// routing for drivers and support
		if ($admin->id_admin) {

			$params['admin'] = $admin;

			if ($admin->isDriver()) {
				$msg[] = (new Message_Incoming_Driver($params))->response;
			}

			if ($msg[0]->stop !== true && $admin->isSupport()) {
				$msg[] = (new Message_Incoming_Support($params))->response;
			}

			if ($msg[0]->msg || ($msg[1] && $msg[1]->msg)) {
				Message_Incoming_Response::twilioSms($msg, $to);
				exit;
			}

			if( count( $msg ) ){
				foreach ($msg as $m) {
					if ($m->stop) {
						exit;
					}
				}
			}
		}

		// routing for incoming support messges
		$msg[] = (new Message_Incoming_Customer($params))->response;
		if ($msg) {
			Message_Incoming_Response::twilioSms($msg, $to);
			exit;
		}
	}
}