<?php

class Controller_api_test_broadcast extends Crunchbutton_Controller_RestAccount {
	public function init() {

		// Send message to drivers
		// Checklist for AFTER new settlement is deployed #3603

		// Select all drivers
		$drivers = Crunchbutton_Admin::drivers();

		$message = '';

		if( trim( $message ) == '' ){
			die( 'error! you must define a message' );
		}

		$message = str_split( $message, 160 );

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
						$twilio->account->sms_messages->create( c::config()->twilio->{ $env }->outgoingTextDriver, '+1'.$num, $msg );
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
			exit;
		}
	}
}