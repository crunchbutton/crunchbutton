<?php

class Controller_api_driver extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if( c::getPagePiece(2) == 'referral' ){
			$this->_referral();
		}

		if( c::getPagePiece(2) == 'requested' ){
			$this->_requested();
		}

		if( c::getPagePiece(2) == 'all' ){
			$out = [];
			$drivers = Admin::drivers();
			foreach( $drivers as $driver ){
				$out[] = [ 'id_admin' => intval( $driver->id_admin ), 'name' => $driver->name ];
			}
			echo json_encode( $out );
			exit();
		}

		if (preg_replace('/[^a-z0-9]/i','',c::getPagePiece(2)) == c::getPagePiece(2) && c::getPagePiece(2) && c::admin()->permission()->check( ['global','drivers-assign', 'drivers-all'] )) {
			$driver = Admin::o((int)c::getPagePiece(2) );
			if (!$driver->id_admin) {
				$driver = Admin::login(c::getPagePiece(2), true);
			}

			if (!$driver) {
				$this->error(404);
			}

			if( !$driver->isDriver() ){
				$this->error(404);
			}
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

			case 'all-admins-with-login':
				$out = [];
				$drivers = Admin::q( 'SELECT * FROM admin a INNER JOIN admin_payment_type apt ON a.id_admin = apt.id_admin WHERE a.name IS NOT NULL and a.login IS NOT NULL ORDER BY a.name ASC' );
				foreach( $drivers as $driver ){
					if( $driver->isDriver() ){
						$community = $driver->communityDriverDelivery();
						$name = $driver->name . ' (' . $driver->login . ') ' . $community->name;
						$out[] = [ 'id_admin' => intval( $driver->id_admin ), 'name' => $name ];
					}
				}
				echo json_encode( $out );
				break;

			case 'by-community':
				$community = Crunchbutton_Community::o( c::getPagePiece( 3 ) );
				$out = [];
				if( $community->id_community ){
					$drivers = $community->getDriversOfCommunity();
					foreach( $drivers as $driver ){
						$note = $driver->lastNote();
						$info = Cockpit_Driver_Info::byAdmin( $driver->id_admin );
						if( $note ){
							$note = $note->exports()['text'];
						}
						$out[] = [ 'id_admin' => intval( $driver->id_admin ), 'name' => $driver->name, 'note' => $note, 'phone' => $driver->phone, 'down_to_help_out' => ( ( $info->down_to_help_out ) ? true : false ) ];
					}
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
				$this->_referral();
				break;
			default:
				if ($this->method() == 'post') {
					// save a setting
				}

				$json = $driver->exports();
				$driver_info = $driver->driver_info()->exports();

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
				$json[ 'payment_type' ] = $payment_type->payment_type;
				$json[ 'hour_rate' ] = intval( $payment_type->hour_rate );

				if( $driver->driver_info()->pexcard_date ){
					$json[ 'pexcard_date' ] = $driver->driver_info()->pexcard_date()->format( 'Y,m,d' );
				}

				if( $json[ 'weekly_hours' ] ){
					$json[ 'weekly_hours' ] = intval( $json[ 'weekly_hours' ] );
				}

				// notes
				$note = $driver->note();
				if( $note->id_admin_note ){
					$json[ 'notes_to_driver' ] = $note->text;
				}

				echo json_encode( $json );exit();
				break;
		}
	}

	private function _requested(){
		if ($this->method() == 'post') {
			$status = ( $this->request()['permitted'] ) ? Cockpit_Admin_Location_Requested::STATUS_PERMITTED : Cockpit_Admin_Location_Requested::STATUS_DENIED;

			$lastStatus = Cockpit_Admin_Location_Requested::lastStatus( c::user()->id_admin );
			if( !$lastStatus || $lastStatus->status != $status ){
				(new Cockpit_Admin_Location_Requested([
					'id_admin' => c::user()->id_admin,
					'date' => date('Y-m-d H:i:s'),
					'status' => $status
				]))->save();

				if( $status == Cockpit_Admin_Location_Requested::STATUS_PERMITTED ){
					Cockpit_Driver_Log::enabledLocation();
				}
			}
		}
		echo json_encode( ['success' => true] );
		exit;
	}

	private function _referral(){
		$name = strtolower( trim( $this->request()[ 'name' ] ) );
		$phone = Crunchbutton_Phone::clean( $this->request()[ 'phone' ] );
		$code = Crunchbutton_Reward::createUniqueCode( $name, $phone );
		echo json_encode( [ 'code' => $code ] );exit;
	}
}
