<?php

class Controller_api_staff_marketing extends Crunchbutton_Controller_RestAccount {

	public function init() {

		switch ( c::getPagePiece( 3 ) ) {
			case 'save':
				$this->_save();
				break;

			default:

				$staff = Admin::o( c::getPagePiece( 3 ) );

				if (!$staff->id_admin) {
					$staff = Admin::login(c::getPagePiece(3), true);
				}

				if( !$staff->isMarketingRep() ){
					$this->_error();
				}

				if( c::getPagePiece( 4 ) == 'save' ){
					$this->_save();
					exit();
				}

				if( $staff->id_admin ){
					$out = $staff->exports();

					$out[ 'id_community' ] = $staff->getMarketingRepGroups();
					$out[ 'isCampusManager' ] = $staff->isCampusManager();
					$out[ 'address' ] = $staff->driver_info()->address;

					$paymentType = $staff->paymentType();
					$out[ 'profit_percent' ] = $paymentType->profit_percent;

					echo json_encode( $out );exit;
					exit();
				}
				$this->_error();
				break;
		}

	}

	private function _save() {

		$user = c::user();
		$hasPermission = ( $user->permission()->check( ['global'] ) );

		if( !$hasPermission ){
			$this->_error();
			exit;
		}

		if( $this->method() != 'post' ){
			$this->_error();
		}

		$newStaff = false;

		// saves a new driver
		if( c::getPagePiece( 3 ) == 'save' ){
			$newStaff = true;
			$staff = new Cockpit_Admin();
			// create the new driver as inactive
			$staff->active = 1;
		} else {
			$staff = Cockpit_Admin::o( c::getPagePiece( 3 ) );
			if( !$staff->isMarketingRep() ){
				$this->_error();
			}
		}

		$phone = preg_replace( '/[^0-9]/i', '', $this->request()[ 'phone' ] );
		if( trim( $phone ) == '' ){
			$this->_error( 'the phone is missing' );
		}

		if( strlen( $phone ) != 10 ){
			$this->_error( 'enter a valid phone' );
		}

		$staff->dob = $this->request()[ 'dob' ];

		$staff->name = $this->request()[ 'name' ];
		$staff->phone = $phone;
		$staff->txt = $phone;
		$staff->testphone = $phone;
		$staff->email = $this->request()[ 'email' ];
		$staff->referral_admin_credit = $this->request()[ 'referral_admin_credit' ];
		$staff->referral_customer_credit = $this->request()[ 'referral_customer_credit' ];
		$staff->pay_for_new_customer = $this->request()[ 'pay_for_new_customer' ];
		if( !$staff->pay_for_new_customer ){
			$staff->referral_admin_credit = 0;
		}

		// Check unique login
		$login = trim( $this->request()[ 'login' ] );
		$admin = Admin::q( 'SELECT * FROM admin WHERE login = ?', [$login]);
		if( $admin->count() == 0 && !$staff->id_admin ){
			$staff->login = $login;
		} else {
			if( $admin->id_admin != $staff->id_admin ){
				$this->_error( 'this login is already in use' );
			}
		}

		// Check unique referral code
		$invite_code = trim( $this->request()[ 'invite_code' ] );
		if ( preg_match('/\s/',$invite_code) ){
			$this->_error( 'please remove white spaces from invite code' );
		} else {
			$admin = Admin::q( 'SELECT * FROM admin WHERE invite_code = ?', [$invite_code]);
			if( $admin->count() == 0 ){
				$staff->invite_code = $invite_code;
			} else {
				if( $admin->id_admin != $staff->id_admin ){
					$this->_error( 'this invite code is already in use' );
				} else {
					$staff->invite_code = $invite_code;
				}
			}
		}

		$pass = $this->request()[ 'pass' ];
		if( $pass && trim( $pass ) != '' ){
			$staff->pass = $staff->makePass( $pass );
		}

		// if it is a new staff without a pass it should create a randon pass
		$random_pass = '';
		if( $newStaff && !$staff->pass ){
			$random_pass = Crunchbutton_Util::randomPass();
			$staff->pass = $staff->makePass( $random_pass );
		}

		$staff->save();

		if( !$staff->login ){
			// create an username
			$staff->login = $staff->createLogin();
			$staff->save();
		}

		// save the address
		$driverInfo = $staff->driver_info();
		$driverInfo->address = $this->request()[ 'address' ];
		$driverInfo->save();

		// add the community
		$id_community = $this->request()[ 'id_community' ];

		// first remove the driver from the delivery groups
		$_communities = Crunchbutton_Community::q( 'SELECT * FROM community ORDER BY name ASC' );;
		foreach( $_communities as $community ){
			$group = $community->groupOfMarketingReps();
			if( $group->id_group ){
				$staff->removeGroup( $group->id_group );
			}
		}

		if( $id_community ){
			$community = Crunchbutton_Community::o( $id_community );
			$staff->timezone = $community->timezone;
			$staff->save();
			if( $community->id_community ){
				$group = $community->groupOfMarketingReps();
				$adminGroup = new Crunchbutton_Admin_Group();
				$adminGroup->id_admin = $staff->id_admin;
				$adminGroup->id_group = $group->id_group;
				$adminGroup->save();
			}
		}


		$campus_manager = $this->request()[ 'isCampusManager' ];
		$campusManagerGroup = Crunchbutton_Group::byName( Crunchbutton_Group::CAMPUS_MANAGER_GROUP );
		$campusManagerGroup = $campusManagerGroup->get( 0 );
		if( $campus_manager ){
			if( !$staff->isCampusManager() ){
				$adminGroup = new Crunchbutton_Admin_Group();
				$adminGroup->id_admin = $staff->id_admin;
				$adminGroup->id_group = $campusManagerGroup->id_group;
				$adminGroup->save();
			}
		} else {
			if( $staff->isCampusManager() ){
				$staff->removeGroup( $campusManagerGroup->id_group );
			}
		}

		$paymentType = $staff->paymentType();
		$paymentType->profit_percent = $this->request()[ 'profit_percent' ];
		$paymentType->save();


		echo json_encode( [ 'success' => $staff->exports() ] );

		return;
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}

}