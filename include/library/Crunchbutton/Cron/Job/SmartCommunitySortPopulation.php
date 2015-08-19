<?php

class Crunchbutton_Cron_SmartCommunitySortPopulation extends Crunchbutton_Cron_Log {

	public function run(){

		// Select driver with unverified accounts
		$drivers = Admin::q( 'SELECT a.* FROM admin_payment_type apt INNER JOIN admin a ON apt.id_admin = a.id_admin WHERE apt.verified = 0 AND apt.stripe_account_id IS NOT NULL' );
		foreach( $drivers as $driver ){
			$this->verify( $driver );
		}

		// it always must call finished method at the end
		$this->finished();
	}

	public function verify( $driver ){
		if( !$driver->isStripeVerified() ){

			$community = '';
			$communities = $driver->communitiesHeDeliveriesFor();
			foreach( $communities as $c ){
				$community .= $commas . $c->name;
				$commas = ', ';
			}

			$params = [];
			$params[ 'login' ] = $driver->login;
			$params[ 'driver' ] = $driver->name;
			$params[ 'community' ] = $community;
			$params[ 'payments' ] = $driver->unPaidPayments();

			$mail = new Crunchbutton_Email_Payment_DriverUnverifiedAccount( $params );
			$mail->send();
		}
	}
}
