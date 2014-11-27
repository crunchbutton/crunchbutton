<?php

class Crunchbutton_Message_Incoming_Sms extends Cana_Model {
	public static function route($request) {

		$from = self::cleanPhone($request['From']);
		$to = self::cleanPhone($request['To']);
		$body = trim($request['Body']);
		$admin = Admin::getByPhone($from, true);

		if (!$from || !$body) {
			// error
			header('HTTP/1.0 400 Bad Request');
			exit;
		}
		
		$params = [
			'body' => $body,
			'from' => $from,
			'to' => $to,
			'sid' => $request['SmsMessageSid']
		];

		// routing for drivers and support
		if ($admin->id_admin) {
			Log::debug([
				'type' => 'driver-sms',
				'action' => 'message received',
				'id_admin' => $admin->id_admin,
				'name' => $admin->name,
				'from' => $from,
				'body' => $body
			]);
			
			$params['admin'] = $admin;

			if ($admin->isDriver()) {
				$msg[] = (new Message_Incoming_Driver($params))->response;
			}

			if ($msg[0]->stop !== true && $admin->isSupport()) {
				$msg[] = (new Message_Incoming_Support($params))->response;
			}

			if ($msg[0]->msg || ($msg[1] && $msg[1]->msg)) {
				Message_Incoming_Response::twilioSms($msg);
				exit;
			}
		}
		
		// routing for incoming support messges
		$msg[] = (new Message_Incoming_Customer($params))->response;
		if ($msg) {
			Message_Incoming_Response::twilioSms($msg);
			exit;
		}
	}
	
	public static function cleanPhone($phone) {
		return preg_replace('/[^0-9]/','', str_replace('+1', '', $phone));
	}
}