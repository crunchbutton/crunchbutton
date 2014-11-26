<?php

class Crunchbutton_Message_Incoming_Sms extends Cana_Model {
	public static function route($request) {

		$phone = str_replace('+1', '', $request['From']);
		$body = trim($request['Body']);
		$admin = Admin::getByPhone($phone, true);
		
		if (!$phone || !$body) {
			// error
			header('HTTP/1.0 400 Bad Request');
			exit;
		}

		// routing for drivers and support
		if ($admin->id_admin) {
			Log::debug([
				'type' => 'driver-sms',
				'action' => 'message received',
				'id_admin' => $admin->id_admin,
				'name' => $admin->name,
				'phone' => $phone,
				'body' => $body
			]);
			
			$params = (object)[
				'body' => $body,
				'phone' => $phone,
				'admin' => $admin
			];
			
			if ($admin->isDriver()) {
				$msg[] = (new Message_Incoming_Driver($params))->response;
			}
/*
			if (!$msg[0]->stop && $admin->isSupport()) {
				$msg[] = (new Message_Incoming_Support($params))->response;
			}
*/
			if ($msg) {
				Message_Incoming_Response::twilioSms($msg);
				exit;
			}
		}
		/*
		// routing for incoming support messges
		$params = (object)[
			'body' => $body,
			'phone' => $phone
		];
		$msg[] = (new Message_Incoming_Customer($params))->response;
		if ($msg) {
			Message_Incoming_Response::twilioSms($msg);
			exit;
		}
		*/
	}
}