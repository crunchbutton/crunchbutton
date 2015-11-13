<?php

class Controller_api_apply_rep extends Crunchbutton_Controller_Rest {

	public function init() {

		switch ( c::getPagePiece( 3 ) ) {
			case 'code':
				$this->_code();
				break;

			default:
				if( $this->method() == 'post' ){
					$this->_save();
				} else {
					echo json_encode(['error' => 'invalid request']);
				}
				break;
		}
	}

	private function _code(){

		$rep = Admin::login( $this->request()[ 'login' ] );
		if( $rep->id_admin ){
			echo json_encode( [ 'code' => $rep->invite_code ] );exit;
		} else {
			echo json_encode(['error' => 'invalid request']);
		}
	}

	private function _save(){

		$step = $this->request()[ 'step' ];

		switch ( $step ) {
			case '2':
				$this->_step2();
				break;
			case '1':
			default:
				$this->_step1();
				break;
		}
	}

	private function _step2(){
		$error = [];
		$id_admin = $this->request()[ 'id' ];
		$login = $this->request()[ 'login' ];
		$token = $this->request()[ 'token' ];

		$rep = Admin::o( $id_admin );
		if( $rep->id_admin && $token == User_Auth::passwordEncrypt( $rep->login ) ){
			$address = $this->request()[ 'address' ];
			$email = $this->request()[ 'email' ];

			if( $address ){
				$info = $rep->driver_info();
				$info->address = $address;
				$info->save();
			}
			if( $email ){
				if( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ){
					$error[] = 'Please enter a valid email!';
					echo json_encode( [ 'error' => join( $error, "<br>" ) ] );exit;
				}
				$rep->changeOptions( [ 'id_admin' => 'id_admin'] );
				$rep->email = $email;
				$rep->save();
			}
			$out = [ 'login' => $rep->login ];



			echo json_encode( [ 'success' => $out ] );exit;
		} else {
			$error[] = 'Ops, there is something wrong here!';
			echo json_encode( [ 'error' => join( $error, "<br>" ) ] );exit;
		}

	}

	private function _step1(){

		$error = [];

		$name = $this->request()[ 'fullName' ];
		$phone = $this->request()[ 'phone' ];
		$community = $this->request()[ 'community' ];
		$code = $this->request()[ 'code' ];

		if( !$name ){
			$error[] = 'Full Name is required.';
		}
		if( !$phone ){
			$error[] = 'Phone Number is required.';
		}
		if( !$community ){
			$error[] = 'Please select a School.';
		}
		if( !$code ){
			$error[] = 'Referral Code is required';
		}

		if( $phone ){
			$phone = Crunchbutton_Phone::clean( $phone );
			if( !$phone ){
				$error[] = 'Please enter a valid Phone Number.';
			}
		}

		if( $name ){
			if( strlen( $name ) < 5 ){
				$error[] = 'Full Name must be at least 5 characters';
			}
		}

		if( $code ){
			if( strlen( $code ) < 5 ){
				$error[] = 'Codes must be at least 5 characters';
			}

			if ( preg_match('/\s/',$code) ){
				$error[] = 'Please remove white spaces from Code';
			}

			if( Crunchbutton_Referral::isCodeAlreadyInUse( $code ) ){
				$error[] = 'This code is already taken. Try another one!';
			}
		}

		if( count( $error ) ){
			echo json_encode( [ 'error' => join( $error, "<br>" ) ] );exit;
		} else {
			// start saving
			$rep = new Admin();
			$rep->active = 1;
			$rep->name = $name;
			$rep->phone = $phone;
			$rep->txt = $phone;
			$rep->testphone = $phone;
			$rep->invite_code = $code;
			$rep->referral_admin_credit = Crunchbutton_Referral::DEFAULT_REFERRAL_AMOUNT;
			$rep->referral_customer_credit = 0;
			$rep->save();

			$rep = Admin::o( $rep->id_admin );

			$rep->changeOptions( [ 'id_admin' => 'id_admin'] );

			// create an username
			$rep->login = $rep->createLogin();
			$rep->save();

			// pass
			$random_pass = Crunchbutton_Util::randomPass();
			$rep->pass = $rep->makePass( $random_pass );
			$rep->save();

			if( $community ){
				$community = Crunchbutton_Community::o( $community );
				$rep->timezone = $rep->timezone;
				$rep->save();
				if( $community->id_community ){
					$group = $community->groupOfMarketingReps();
					$adminGroup = new Crunchbutton_Admin_Group();
					$adminGroup->id_admin = $rep->id_admin;
					$adminGroup->id_group = $group->id_group;
					$adminGroup->save();
				}
			}
			$driverInfo = $rep->driver_info();
			$paymentType = $rep->paymentType();

			$out = [ 'username' => $rep->login, 'id' => $rep->id, 'token' => User_Auth::passwordEncrypt( $rep->login ) ];

			$message = "Welcome {$rep->firstName()}\nYour username is {$rep->login}.";
			$message .= "\nYour password is {$random_pass}.";
			$message .= "\n" . "Url http://cockpit.la/";

			Crunchbutton_Message_Sms::send([ 'to' => $rep->phone, 'message' => $message, 'reason' => Crunchbutton_Message_Sms::REASON_REP_SETUP ]);

			echo json_encode( [ 'success' => $out ] );exit;
		}
	}

}