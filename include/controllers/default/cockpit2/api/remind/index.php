<?php

class Controller_api_remind extends Crunchbutton_Controller_Rest {

	public function init() {

		$phone = $this->request()[ 'phone' ];
		$phone = Phone::byPhone( $phone );
		if( $phone->id_phone ){
			$staff = Admin::q( 'SELECT * FROM admin WHERE id_phone = ? AND active = 1 AND login IS NOT NULL ORDER BY id_admin DESC LIMIT 1', [ $phone->id_phone ] )->get( 0 );
			if( $staff->id_admin ){
				if( $staff->permission()->check( [ 'global' ] ) ){
					echo json_encode( [ 'error' => 'Sorry, I can\'t send a reminder to super-admin user, too many powers.' ] );exit;
				}

				$random_pass = Crunchbutton_Util::randomPass();
				$staff->pass = $staff->makePass( $random_pass );
				$staff->save();

				Cockpit_Driver_Notify::send( $staff->id_admin, Cockpit_Driver_Notify::TYPE_FORGOT_PASS, $random_pass );

				echo json_encode( [ 'success' => 'You will receive your username and password soon.' ] );exit;

			}
		}
		echo json_encode( [ 'error' => 'Sorry, we didn\'t recognize this phone number.' ] );exit;
	}
}