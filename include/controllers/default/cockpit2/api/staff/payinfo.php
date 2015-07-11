<?php

class Controller_api_staff_payinfo extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$hasPermission = ( c::admin()->permission()->check( ['global', 'permission-all', 'permission-users'] ) );

		if( $hasPermission ){
			if( c::getPagePiece( 3 ) ){
				$admin = Admin::o( c::getPagePiece( 3 ) );
				if (!$admin->id_admin) {
					$admin = Admin::login(c::getPagePiece(3), true);
				}
			} else {
				$admin = Admin::o( $this->request()[ 'id_admin' ] );
				if (!$admin->id_admin) {
					$admin = Admin::login($this->request()['id_admin'], true);
				}
			}

		} else {
			$admin = Admin::o( c::user()->id_admin );
		}

		if( $admin->id_admin ){

			if( $this->method() == 'post' ){
				$this->post( $admin );
			} else {
				$this->get( $admin );
			}

		} else {
			$this->_error( 'invalid object' );
		}
	}

	private function get( $admin ){
		switch( c::getPagePiece( 4 ) ){
			case 'pexcard':
				$this->_pexcard( $admin );
				break;
			default:
				$this->payInfo( $admin );
				break;
		}
	}

	private function post( $admin ){
		switch( c::getPagePiece( 4 ) ){
			case 'save':
				$this->savePayInfo( $admin );
				break;
			case 'save-stripe-bank':
				$this->saveStripeBankInfo( $admin );
				break;
		}
	}

	private function savePayInfo( $admin ){

		$payment_type = $admin->payment_type();
		if( !$payment_type->id_admin_payment_type ){
			$payment_type = new Crunchbutton_Admin_Payment_Type;
			$payment_type->id_admin = $admin->id_admin;
		}

		if( $this->request()[ 'dob' ] ){
			$admin->dob = $this->request()[ 'dob' ];
			$admin->save();
		}


		$payment_type->using_pex = ( intval( $this->request()[ 'using_pex' ] ) ? intval( $this->request()[ 'using_pex' ] ) : 0 );

		if( $this->request()[ 'using_pex_date_formatted' ] ){
			$date = new DateTime( $this->request()[ 'using_pex_date_formatted' ] );
			if( $date->format( 'Ymd' ) > date( 'Ymd' ) ){
				$this->_error( 'Date started using Pex Card should not be in the future!' );
			}
			$payment_type->using_pex_date = $date->format( 'Y-m-d H:i:s' );
		}

		if( $payment_type->using_pex && !$payment_type->using_pex_date ){
			$payment_type->using_pex_date = date( 'Y-m-d H:i:s' );
		}

		if( $this->request()[ 'date_terminated_formatted' ] ){
			$admin->date_terminated = ( new DateTime( $this->request()[ 'date_terminated_formatted' ] ) )->format( 'Y-m-d' );
			$admin->save();
		} else {
			$admin->using_pex_date = null;
			$admin->save();
		}

		if( $payment_type->using_pex == 1 && !$payment_type->using_pex_date ){
			$payment_type->using_pex_date = date( 'Y-m-d H:i:s' );
		}

		$payment_type->legal_name_payment = $this->request()[ 'legal_name_payment' ];
		$payment_type->address = $this->request()[ 'address' ];

		$social_security_number = trim( $this->request()[ 'social_security_number' ] );

		if( $social_security_number != '' && $social_security_number != Crunchbutton_Admin_Info::SSN_MASK ){
			$admin->ssn( $social_security_number );
		}

		if ( c::admin()->permission()->check( ['global', 'permission-all', 'permission-users'] ) ){
			$payment_type->hour_rate = floatval( $this->request()[ 'hour_rate' ] );
			$payment_type->payment_method = $this->request()[ 'payment_method' ];
			if( $this->request()[ 'payment_type' ] ){
				$payment_type->payment_type = $this->request()[ 'payment_type' ];
			} else {
				$payment_type->payment_type = $payment_type->payment_type ? $payment_type->payment_type : Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_ORDERS;
			}

			$payment_type->summary_email = $this->request()[ 'summary_email' ];
		}

		if( !$payment_type->payment_method ){
			$payment_type->payment_method = Crunchbutton_Admin_Payment_Type::PAYMENT_METHOD_DEPOSIT;
		}
		if( !$payment_type->payment_type ){
			$payment_type->payment_type = Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_ORDERS;
		}

		$payment_type->save();
		$this->payInfo( $admin );
	}

	private function saveStripeBankInfo( $admin ){

		$token = $this->request()[ 'token' ];

		if( !$token ){
			$this->_error( 'Invalid token!' );
		}

		$paymentType = $admin->payment_type();

		$stripe = $paymentType->getAndMakeStripe( [ 'bank_account' => $token ] );

		if( $stripe && !is_array( $stripe ) ){
			$paymentType->stripeVerify();
			$this->payInfo( $admin );
			exit;
		} else {
			echo json_encode( $stripe );exit;
		}
		$this->_error( 'Error creating stripe account' );
	}

	private function payInfo( $admin ){
		$payment_type = $admin->payment_type();
		if( $payment_type->id_admin_payment_type ){
			$out = $payment_type->exports();
			$out[ 'id_admin' ] = $admin->id_admin;
			$out[ 'name' ] = $admin->name;
			$out[ 'login' ] = $admin->login;
			$out[ 'dob' ] = $admin->dob;
			$out[ 'hour_rate' ] = floatval( $payment_type->hour_rate );
			$out[ 'social_security_number' ] = $admin->ssn_mask();
			$cards = Cockpit_Admin_Pexcard::getByAdmin( $admin->id_admin )->get( 0 );
			$out[ 'pexcard' ] = ( $cards && count( $cards ) > 0 );
			if( $payment_type->using_pex_date && $payment_type->using_pex_date()){
				$out[ 'using_pex_date' ] = $payment_type->using_pex_date()->format( 'Y,m,d' );
			}
			if( $admin->date_terminated && $admin->dateTerminated()){
				$out[ 'date_terminated' ] = $admin->dateTerminated()->format( 'Y,m,d' );
			}

			echo json_encode( $out );
		} else {
			echo json_encode( [ 'id_admin' => $admin->id_admin, 'name' => $admin->name, 'summary_email' => $admin->email ] );
			exit;
		}
	}

	private function _pexcard( $staff ){
		$out[ 'name' ] = $staff->name;
		$out[ 'id_admin' ] = $staff->id_admin;
		$cards = Cockpit_Admin_Pexcard::getByAdmin( $staff->id_admin );
		if( $cards && count( $cards ) > 0 ){
			$out[ 'cards' ] = [];
			foreach( $cards as $card ){
				$out[ 'cards' ][] = $card->load_card_info();
			}
		}
		echo json_encode( $out );exit;
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}