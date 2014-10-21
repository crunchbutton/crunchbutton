<?php

class Controller_api_staff_payinfo extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$hasPermission = ( c::admin()->permission()->check( ['global', 'permission-all', 'permission-users'] ) );

		if( $hasPermission ){
			if( c::getPagePiece( 3 ) ){
				$admin = Admin::o( c::getPagePiece( 3 ) );
			} else {
				$admin = Admin::o( $this->request()[ 'id_admin' ] );
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
			case 'save-bank':
				$this->saveBankInfo( $admin );
				break;
		}
	}

	private function savePayInfo( $admin ){
		$payment_type = $admin->payment_type();
		if( !$payment_type->id_admin_payment_type ){
			$payment_type = new Crunchbutton_Admin_Payment_Type;
			$payment_type->id_admin = $admin->id_admin;
		}

		$payment_type->using_pex = ( intval( $this->request()[ 'using_pex' ] ) ? intval( $this->request()[ 'using_pex' ] ) : 0 );
		$payment_type->legal_name_payment = $this->request()[ 'legal_name_payment' ];
		$payment_type->address = $this->request()[ 'address' ];

		$social_security_number = trim( $this->request()[ 'social_security_number' ] );

		if( $social_security_number != '' && $social_security_number != Crunchbutton_Admin_Info::SSN_MASK ){
			$admin->ssn( $social_security_number );
		}

		if ( c::admin()->permission()->check( ['global', 'permission-all', 'permission-users'] ) ){
			$payment_type->hour_rate = floatval( $this->request()[ 'hour_rate' ] );
			$payment_type->payment_method = $this->request()[ 'payment_method' ];
			$payment_type->payment_type = $this->request()[ 'payment_type' ];
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

	private function saveBankInfo( $admin ){
		$payment_type = $admin->payment_type();
		if( !$payment_type->id_admin_payment_type ){
			$payment_type = new Crunchbutton_Admin_Payment_Type;
			$payment_type->id_admin = $admin->id_admin;
		}
		if( !$payment_type->payment_method ){
			$payment_type->payment_method = Crunchbutton_Admin_Payment_Type::PAYMENT_METHOD_DEPOSIT;
		}
		if( $payment_type->payment_type ){
			$payment_type->payment_type = Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_ORDERS;
		}
		if( $this->request()[ 'legal_name_payment' ] ){
			$payment_type->legal_name_payment = $this->request()[ 'legal_name_payment' ];
		}
		$payment_type->balanced_bank = $this->request()[ 'id' ];
		$payment_type->balanced_id = $this->request()[ 'href' ];
		$payment_type->save();

		// claim it
		$payment_type->claimBankAccount( $payment_type->balanced_bank );

		$this->payInfo( $admin );
	}

	private function payInfo( $admin ){
		$payment_type = $admin->payment_type();
		if( $payment_type->id_admin_payment_type ){
			$out = $payment_type->exports();
			$out[ 'id_admin' ] = $admin->id_admin;
			$out[ 'name' ] = $admin->name;
			$out[ 'hour_rate' ] = floatval( $payment_type->hour_rate );
			$out[ 'social_security_number' ] = $admin->ssn_mask();
			echo json_encode( $out );
		} else {
			echo json_encode( [ 'id_admin' => $admin->id_admin, 'name' => $admin->name, 'summary_email' => $admin->email ] );
			exit;
		}
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}