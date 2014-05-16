<?php

class Controller_api_driver_onboarding extends Crunchbutton_Controller_Rest {
	
	public function init() {

		if( $this->method() != 'post' ){
			$this->_error();
		}

		$name = $this->request()[ 'name' ];
		if( trim( $name ) == '' ){
			$this->_error( 'Enter your name!' );
		}

		$phone = preg_replace( '/[^0-9]/i', '', $this->request()[ 'phone' ] );
		if( trim( $phone ) == '' ){
			$this->_error( 'Enter your phone!' );
		}

		$admin = Admin::q( 'SELECT * FROM admin WHERE phone = "' . $phone . '"' );
		if( $admin->count() != 0 ){
			$this->_error( 'This phone is already registred!' );
		}

		$email = $this->request()[ 'email' ];
		if( trim( $email ) ){
			$admin = Admin::q( 'SELECT * FROM admin WHERE email = "' . $email . '"' );
			if( $admin->count() != 0 ){
				$this->_error( 'This email is already registred!' );
			}			
		}

		$driver = new Crunchbutton_Admin();
		$driver->active = 0;
		$driver->name = $name;
		$driver->phone = $phone;
		$driver->email = $email;
		$driver->save();

		// add the community
		$id_community = $this->request()[ 'id_community' ];
		if( $id_community ){
			$community = Crunchbutton_Community::o( $id_community );
			if( $community->id_community ){
				$group = $community->groupOfDrivers();
				$adminGroup = new Crunchbutton_Admin_Group();
				$adminGroup->id_admin = $driver->id_admin;
				$adminGroup->id_group = $group->id_group;
				$adminGroup->save();
			}	
		}

		// register the log
		$log = new Crunchbutton_Driver_Log();
		$log->id_admin = $driver->id_admin;
		$log->action = 'pre';
		$log->datetime = date('Y-m-d H:i:s');
		$log->save();

		echo json_encode( [ 'success' => $driver->exports() ] );
		return;
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}