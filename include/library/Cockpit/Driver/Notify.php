<?php

class Cockpit_Driver_Notify extends Cana_Table {

	const TYPE_SETUP = 'setup';
	const TYPE_WELCOME = 'welcome';
	const TYPE_ACCESS_INFO = 'access-info';
	const TYPE_FORGOT_PASS = 'forgot-pass';
	const ORDER_TEST = '22890'; // id_order sent to drivers play with - Issue #2969 - step 3

	public function send( $id_admin, $message, $additional = false ){

		$driver = Crunchbutton_Admin::o( $id_admin );

		if( !$driver->id_admin ){
			return [ 'error' => 'invalid user' ];
		}

		$phone = $driver->phone();

		$phone = str_replace( '-' , '', $phone );

		if( trim( $phone ) == '' ){
			return [ 'error' => 'invalid phone' ];
		}

		Log::debug( [ 'action' => 'notification starting', 'driver' => $id_admin, 'phone' => $phone, 'message' => $message, 'type' => 'drivers-onboarding'] );

		$username = $driver->login;
		$first_name = Crunchbutton_Message_Sms::greeting( $driver->firstName() );

		// Pre defined messages
		switch ( $message ) {
			case Cockpit_Driver_Notify::TYPE_WELCOME:
				$message_type = Cockpit_Driver_Notify::TYPE_WELCOME;
				$message = "Welcome " . $first_name . "! \nYour username is {$username}. Url cockpit.la/setup/{$phone}";
				break;

			case Cockpit_Driver_Notify::TYPE_SETUP:
				$message_type = Cockpit_Driver_Notify::TYPE_SETUP;
				$message = $first_name . 'Test this URL out on your phone (exactly as it appears, no www.) cockpit.la/' . Cockpit_Driver_Notify::ORDER_TEST . '. Play around with it and make sure you understand how everything works';
				$message .="\n" . 'If you have any questions, just text us directly at 646-783-1444.';
				break;

			case Cockpit_Driver_Notify::TYPE_ACCESS_INFO:
				$message_type = Cockpit_Driver_Notify::TYPE_ACCESS_INFO;
				$message = "Welcome " . $first_name . "\nYour username is {$username}.";
				$message .= "\n" . "Your password is {$additional}.";
				$message .= "\n" . "Url http://cockpit.la/";
				// $message .= "\n" . "Make sure to enable push and location services.";
				break;

			case Cockpit_Driver_Notify::TYPE_FORGOT_PASS:
				$message_type = Cockpit_Driver_Notify::TYPE_ACCESS_INFO;
				$message = "Hey " . $first_name . "\nYour username is {$username}.";
				$message .= "\n" . "Your password is {$additional}.";
				$message .= "\n" . "Url http://cockpit.la/";
				// $message .= "\n" . "Make sure to enable push and location services.";
				break;
		}

		if( trim( $message ) == '' ){
			return [ 'error' => 'enter a message' ];
		}

		$notification = new Cockpit_Driver_Notify;
		$notification->id_admin = $driver->id_admin;
		$notification->phone = $phone;
		$notification->message_type = $message_type;
		$notification->email = $driver->email;
		$notification->additional = $additional;
		$notification->message = $message;

		// Cana::timeout( function() use( $notification ) {
			$notification->notify();
		// } );

		// log
		$log = new Cockpit_Driver_Log();
		$log->id_admin = $id_admin;
		$log->action = Cockpit_Driver_Log::ACTION_NOTIFIED_SETUP;
		$log->info = $phone . ' (' . $driver->email . ') ' . $message;
		$log->datetime = date('Y-m-d H:i:s');
		$log->save();

		return [ 'success' => 'notification sent' ];

	}

	public function notify(){

		$notification = $this;

		$message = $notification->message;
		$id_admin = $notification->id_admin;
		$phone = $notification->phone;
		$email = $notification->email;
		$message_type = $notification->message_type;

		Crunchbutton_Message_Sms::send([
			'to' => $phone,
			'message' => $message,
			'reason' => Crunchbutton_Message_Sms::REASON_DRIVER_SETUP
		]);

		// Send email
		if( $email ){
			switch ( $message_type ) {
				case Cockpit_Driver_Notify::TYPE_WELCOME:
					$mail = new Cockpit_Email_Driver_Welcome( [ 'id_admin' => $id_admin ] );
					$mail->send();
					break;

				case Cockpit_Driver_Notify::TYPE_SETUP:
					$mail = new Cockpit_Email_Driver_Setup( [ 'id_admin' => $id_admin ] );
					$mail->send();
					break;

				case Cockpit_Driver_Notify::TYPE_ACCESS_INFO:
					$mail = new Cockpit_Email_Driver_Access( [ 'id_admin' => $id_admin, 'pass' => $notification->additional ] );
					$mail->send();
					break;
			}
		}
	}
}