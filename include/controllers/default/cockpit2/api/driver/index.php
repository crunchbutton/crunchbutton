<?php

class Controller_api_driver extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (preg_replace('/[^0-9]/','',c::getPagePiece(2)) == c::getPagePiece(2) && c::getPagePiece(2)) {
			$driver = Admin::o(c::getPagePiece(2));
			$action = c::getPagePiece(3);
		} else {
			$driver = c::user();
			$action = c::getPagePiece(2);
		}

		switch ($action) {
			case 'location':
				if ($this->method() == 'post') {
					(new Admin_Location([
						'id_admin' => $driver->id_admin,
						'date' => date('Y-m-d H:i:s'),
						'lat' => $this->request()['lat'] ? $this->request()['lat'] : $this->request()['latitude'],
						'lon' => $this->request()['lon'] ? $this->request()['lon'] : $this->request()['longitude'],
						'accuracy' => $this->request()['accuracy']
					]))->save();
				}
				if ( method_exists( $driver, 'location' ) && $driver->location()->id_admin_location) {
					echo $driver->location()->json();
				} else {
					echo json_encode(null);
				}
				break;

			case 'all':
				$out = [];
				$drivers = Admin::drivers();
				foreach( $drivers as $driver ){
					$out[] = [ 'id_admin' => intval( $driver->id_admin ), 'name' => $driver->name ];
				}
				echo json_encode( $out );
				break;

			case 'all-admins':
				$out = [];
				$drivers = Admin::q( 'SELECT * FROM admin ORDER BY name ASC' );
				foreach( $drivers as $driver ){
					$out[] = [ 'id_admin' => intval( $driver->id_admin ), 'name' => $driver->name ];
				}
				echo json_encode( $out );
				break;

			case 'list-payment-type':
				$out = [];
				$drivers = Admin::drivers();
				foreach( $drivers as $driver ){
					if( $driver->hasPaymentType() ){
						$out[] = [ 'id_admin' => intval( $driver->id_admin ), 'name' => $driver->name ];
					}
				}
				echo json_encode( $out );
				break;

			case 'referral':
				$name = strtolower( trim( $this->request()[ 'name' ] ) );
				$phone = Crunchbutton_Phone::clean( $this->request()[ 'phone' ] );
				$code = Crunchbutton_Reward::createUniqueCode( $name, $phone );
				echo json_encode( [ 'code' => $code ] );exit;
				break;
			default:
				if ($this->method() == 'post') {
					// save a setting
				}

				$json = $driver->exports();
				$driver_info = $driver->driver_info()->exports();

				$driver_info[ 'student' ] = strval( $driver_info[ 'student' ] );
				$driver_info[ 'permashifts' ] = strval( $driver_info[ 'permashifts' ] );

				$driver_info[ 'iphone_type' ] = '';
				$driver_info[ 'android_type' ] = '';
				$driver_info[ 'android_version' ] = '';

				if( $driver_info[ 'phone_type' ] == 'Android' ){
					$driver_info[ 'android_type' ] = $driver_info[ 'phone_subtype' ];
					$driver_info[ 'android_version' ] = $driver_info[ 'phone_version' ];
				}
				if( $driver_info[ 'phone_type' ] == 'iPhone' ){
					$driver_info[ 'iphone_type' ] = $driver_info[ 'phone_subtype' ];
				}

				$json = array_merge( $json, $driver_info );

				$payment_type = $driver->payment_type();
				$json[ 'hourly' ] = ( $payment_type->payment_type == Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS ) ? '1' : '0';

				if( $driver->driver_info()->pexcard_date ){
					$json[ 'pexcard_date' ] = $driver->driver_info()->pexcard_date()->format( 'Y,m,d' );
				}

				if( $json[ 'weekly_hours' ] ){
					$json[ 'weekly_hours' ] = intval( $json[ 'weekly_hours' ] );
				}

				echo json_encode( $json );exit();
				break;
		}


	}
}