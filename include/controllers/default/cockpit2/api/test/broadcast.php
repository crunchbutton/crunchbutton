<?php

class Controller_Api_Test_Broadcast extends Crunchbutton_Controller_RestAccount {

	const FOR_REAL = true;

	public function init() {

		switch ( c::getPagePiece( 3 ) ) {
			case 'sms':
				Controller_Api_Test_Broadcast::sms();
				break;

			case 'email':
				Controller_Api_Test_Broadcast::email();
				break;

			default:
				die( 'Nothing here' );
				break;
		}
	}

	public function email(){

		// Select all drivers
		$drivers = Crunchbutton_Admin::drivers();

		$message = 'Hey drivers.'.
								'<br/><br/>'.
								'Starting Monday, Sept. 8, we are going to reimburse you for orders directly through our system every day, rather than weekly or through Abacus.'.
								'<br/>'.
								'So please, before this date, go online and RE-enter your direct deposit info at http://cockpit.la/drivers/docs/payment .'.
								'<br/><br/>'.
								'Please note that salary payment will still be every Friday.';

		$subject = 'Crunchbutton Reimbursement Change';

		foreach( $drivers as $driver ){

			$mail = new Cockpit_Email_Driver_Broadcast( [ 'driver' => $driver,
																										'subject' => $subject,
																										'message' => $message ] );

			if( Controller_Api_Test_Broadcast::FOR_REAL ){
				$mail->send();
			} else {
				if( $driver->id_admin == 5 ){
					echo "For real:\n\n";
					$mail->send();
				}
			}

			$log = 'Sending email to: ' . $driver->name . ': ' . $subject;
			Log::debug( [ 'action' => $log, 'type' => 'driver-warning' ] );
			echo $log."\n";
			echo $mail->message()."\n";
			echo "\n--------------\n";
		}
	}

	public function sms(){

		// Select all drivers
		$drivers = Crunchbutton_Admin::drivers();

		$message = [];
		$message[] = "Hey drivers.\nStarting Monday, Sept. 8, we are going to reimburse you for orders directly through our system every day, rather than weekly or";
		$message[] = "through Abacus. So please, before this date, go online and RE-enter your direct deposit info at http://cockpit.la/drivers/docs/payment";
		$message[] = "Please note that salary payment will still be every Friday.";

		$env = c::getEnv();

		$twilio = new Twilio( c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token );

		foreach( $drivers as $driver ){
			$txt = $driver->txt;
			$phone = $driver->phone;

			$num = ( $txt != '' ) ? $txt : $phone;

			if( $num != '' ){
				foreach ( $message as $msg ) {
					try {
						// Log
						if( Controller_Api_Test_Broadcast::FOR_REAL ){
							$twilio->account->sms_messages->create( c::config()->twilio->{ $env }->outgoingTextDriver, '+1'.$num, $msg );
						} else {
							if( $driver->id_admin == 5 ){
								echo "For real:\n\n";
								$twilio->account->sms_messages->create( c::config()->twilio->{ $env }->outgoingTextDriver, '+1'.$num, $msg );
							}
						}
						$log = 'Sending sms to: ' . $driver->name . ' - ' . $num . ': ' . $msg;
						Log::debug( [ 'action' => $log, 'type' => 'driver-warning' ] );
						echo $log."\n";
					} catch (Exception $e) {
						// Log
						$log = 'ERROR!!! Sending sms to: ' . $driver->name . ' - ' . $num . ': ' . $msg;
						Log::debug( [ 'action' => $log, 'type' => 'driver-warning' ] );
						echo $log."\n";
					}
				}
			} else {
				Log::debug( [ 'action' => 'ERROR: sending sms ', 'id_admin' => $driver->id_admin, 'name' => $driver->name, 'num' => $num, 'msg' => $msg, 'type' => 'driver-warning' ] );
			}
			echo "\n--------------\n";
		}
	}
}