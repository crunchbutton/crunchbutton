<?php

class Controller_api_driver_save extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$id_admin = c::getPagePiece( 3 );
		$user = c::user();
		$hasPermission = ( c::admin()->permission()->check( ['global', 'drivers-all'] ) || ( $id_admin == $user->id_admin ) );
		if( !$hasPermission ){
			$this->_error();
			exit;
		}

		if( $this->method() != 'post' ){
			$this->_error();
		}

		$newDriver = false;

		// saves a new driver
		if( !$id_admin ){
			$newDriver = true;
			$driver = new Cockpit_Admin();
			// create the new driver as inactive
			$driver->active = 1;
		} else {
			$driver = Cockpit_Admin::o( $id_admin );
		}

		// Check unique referral code
		$invite_code = trim( $this->request()[ 'invite_code' ] );
		if ( preg_match('/\s/',$invite_code) ){
			$this->_error( 'please remove white spaces from invite code' );
		} else {
			$admin = Admin::q( 'SELECT * FROM admin WHERE invite_code = ?', [$invite_code]);
			if( $admin->count() == 0 ){
				$driver->invite_code = $invite_code;
			} else {
				if( $admin->id_admin != $driver->id_admin ){
					$this->_error( 'this invite code is already in use' );
				}
			}
		}

		$phone = preg_replace( '/[^0-9]/i', '', $this->request()[ 'phone' ] );
		if( trim( $phone ) == '' ){
			$this->_error( 'the phone is missing' );
		}

		if( strlen( $phone ) != 10 ){
			$this->_error( 'enter a valid phone' );
		}

		$driver->name = $this->request()[ 'name' ];
		$driver->dob = $this->request()[ 'dob' ];
		$driver->phone = $phone;
		$driver->txt = $phone;
		$driver->testphone = $phone;
		$driver->email = $this->request()[ 'email' ];
		$driver->referral_admin_credit = $this->request()[ 'referral_admin_credit' ];
		$driver->referral_customer_credit = $this->request()[ 'referral_customer_credit' ];
		$driver->email = $this->request()[ 'email' ];

		// Double check unique login
		$login = trim( $this->request()[ 'login' ] );
		$admin = Admin::q( 'SELECT * FROM admin WHERE login = ?', [$login]);
		if( $admin->count() == 0 && !$driver->id_admin ){
			$driver->login = $login;
		}

		$pass = $this->request()[ 'pass' ];
		if( $pass && trim( $pass ) != '' ){
			$driver->pass = $driver->makePass( $pass );
		}

		$random_pass = '';
		if( $newDriver ){
			if( trim( $pass ) == '' ){
				$random_pass = Crunchbutton_Util::randomPass();
				$driver->pass = $driver->makePass( $random_pass );
			} else {
				$random_pass = $pass;
				$driver->pass = $driver->makePass( $pass );
			}
		}

		$driver->save();

		if( !$driver->login ){
			// create an username
			$driver->login = $driver->createLogin();
			$driver->save();
		}

		$driver->saveVehicle( $this->request()[ 'vehicle' ] );

		$driver->addPermissions(['community-cs' => true]);

		// add the community
		$id_community = $this->request()[ 'id_community' ];

		// first remove the driver from the delivery groups
		$_communities = Crunchbutton_Community::q( 'SELECT * FROM community ORDER BY name ASC' );;

		foreach( $_communities as $community ){
			$group = $community->groupOfDrivers();
			if( $group->id_group ){
				$driver->removeGroup( $group->id_group );
			}
		}

		if( $id_community ){
			$community = Crunchbutton_Community::o( $id_community );
			$driver->timezone = $community->timezone;
			$driver->save();
			if( $community->id_community ){
				$group = $community->groupOfDrivers();
				$adminGroup = new Crunchbutton_Admin_Group();
				$adminGroup->id_admin = $driver->id_admin;
				$adminGroup->id_group = $group->id_group;
				$adminGroup->save();
			}
		}

		if( $newDriver ){
			// Create phone notification
			if( $driver->phone ){
				$notification = new Crunchbutton_Admin_Notification();
				$notification->value = $driver->phone;
				$notification->type = Crunchbutton_Admin_Notification::TYPE_SMS;
				$notification->active = 1;
				$notification->id_admin = $driver->id_admin;
				$notification->save();
			}

			// Create email notification
			if( $driver->email ){
				$notification = new Crunchbutton_Admin_Notification();
				$notification->value = $driver->email;
				$notification->type = Crunchbutton_Admin_Notification::TYPE_EMAIL;
				$notification->active = 1;
				$notification->id_admin = $driver->id_admin;
				$notification->save();
			}
		}

		if( $this->request[ 'timezone' ] ){
			$driver->timezone = $this->request[ 'timezone' ];
			$driver->save();
		}


		// Driver info
		$driver_info = $driver->driver_info();

		$driver_info->phone_type = $this->request()[ 'phone_type' ];
		if( $driver_info->notes_to_driver != $this->request()[ 'notes_to_driver' ] ){
			$driver->addNote( $this->request()[ 'notes_to_driver' ] );
		}
		$driver_info->down_to_help_out = $this->request()[ 'down_to_help_out' ];
		$driver_info->notes_to_driver = $this->request()[ 'notes_to_driver' ];
		$driver_info->weekend_driver = $this->request()[ 'weekend_driver' ];
		$driver_info->ignore_shift_reminders = ($this->request()[ 'ignore_shift_reminders' ] == 'true') ? true : false;

		$driver_info->phone_subtype = null;
		$driver_info->phone_version = null;

		if( $driver_info->phone_type == 'iPhone' ){
			$driver_info->phone_subtype = $this->request()[ 'iphone_type' ];
		}
		if( $driver_info->phone_type == 'Android' ){
			$driver_info->phone_subtype = $this->request()[ 'android_type' ];
//			//michal line below:
//			$driver_info->phone_subtype = ( $this->request()[ 'android_type' ] == Cockpit_Driver_Info::ANDROID_TYPE_OTHER ? $this->request()[ 'android_type_other' ] : $this->request()[ 'android_type' ] );

			$driver_info->phone_version = $this->request()[ 'android_version' ];
		}

		$driver_info->cell_carrier = $this->request()[ 'cell_carrier' ];
		$driver_info->cell_carrier = ( $this->request()[ 'carrier_type' ] == Cockpit_Driver_Info::CARRIER_TYPE_OTHER ? $this->request()[ 'carrier_type_other' ] : $this->request()[ 'carrier_type' ] );
		$driver_info->address = $this->request()[ 'address' ];

		if( $this->request()[ 'pexcard_date' ] ){
			$driver_info->pexcard_date = ( new DateTime( $this->request()[ 'pexcard_date' ] ) )->format( 'Y-m-d' );
		}
		$driver_info->student = $this->request()[ 'student' ];
		$driver_info->permashifts = $this->request()[ 'permashifts' ];
		$driver_info->weekly_hours = $this->request()[ 'weekly_hours' ];
		$driver_info->tshirt_size = $this->request()[ 'tshirt_size' ];

		$driver_info->save();


		$payment_type = $driver->payment_type();
		$payment_type->payment_type = $this->request()[ 'payment_type' ];
		$payment_type->hour_rate = $this->request()[ 'hour_rate' ];

		if( $newDriver ){
			$payment_type->using_pex = 1;
			$payment_type->using_pex_date = date( 'Y-m-d H:i:s' );
		}
		$payment_type->save();

		if( intval( $driver_info->permashifts ) == 1 ){
			$driver->setConfig( Crunchbutton_Admin::CONFIG_RECEIVE_DRIVER_SCHEDULE_SMS_WARNING, 1 );
		}

		Log::debug( [ 'action' => 'driver saved', 'driver' => $driver->id_admin, 'type' => 'drivers-onboarding'] );

		$log = new Cockpit_Driver_Log();
		$log->id_admin = $driver->id_admin;
		$log->action = ( $newDriver ) ? Cockpit_Driver_Log::ACTION_CREATED_COCKIPT : Cockpit_Driver_Log::ACTION_UPDATED_COCKIPT;
		$log->datetime = date('Y-m-d H:i:s');
		$log->save();

		if ( $newDriver ) {
			Cockpit_Driver_Notify::send( $driver->id_admin, Cockpit_Driver_Notify::TYPE_ACCESS_INFO, $random_pass );
		}

		Log::debug( [ 'action' => 'driver saved', 'exports' => $driver->exports(), 'type' => 'drivers-onboarding'] );

		echo json_encode( [ 'success' => $driver->exports() ] );

		return;
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}
