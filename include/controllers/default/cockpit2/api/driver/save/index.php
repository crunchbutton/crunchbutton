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

		$driver->name = $this->request()[ 'name' ];
		$driver->phone = preg_replace( '/[^0-9]/i', '', $this->request()[ 'phone' ] );
		$driver->email = $this->request()[ 'email' ];

		// Double check unique login
		$login = trim( $this->request()[ 'login' ] );
		$admin = Admin::q( 'SELECT * FROM admin WHERE login = "' . $login . '"' );
		if( $admin->count() == 0 && !$driver->id_admin ){
			$driver->login = $login;
		}

		$pass = $this->request()[ 'pass' ];
		if( $pass && trim( $pass ) != '' ){
			$driver->pass = $driver->makePass( $pass );
		}

		// if it is a new driver it should create a randon pass
		$random_pass = '';
		if( $newDriver ){
			$random_pass = Crunchbutton_Util::randomPass();
			$driver->pass = $driver->makePass( $random_pass );
		}

		$driver->save();

		if( !$driver->login ){
			// create an username
			$driver->login = $driver->createLogin();
			$driver->save();
		}

		$driver->saveVehicle( $this->request()[ 'vehicle' ] );

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