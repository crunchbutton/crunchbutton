<?php

class Controller_api_staff_payinfo extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$hasPermission = ( c::admin()->permission()->check( ['global', 'permission-all', 'permission-users'] ) );
		if( !$hasPermission ){
			$this->_error();
			exit;
		}

		if( c::getPagePiece( 3 ) ){

			$admin = Admin::o( c::getPagePiece( 3 ) );
			if( $admin->id_admin ){

				if( $this->method() == 'post' ){
					$this->post( $admin );
				} else {
					$this->get( $admin );
				}

			} else {
				$this->_error( 'invalid object' );
			}
		} else {
			$this->_error();
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

		$payment_type->payment_method = $this->request()[ 'payment_method' ];
		$payment_type->payment_type = $this->request()[ 'payment_type' ];
		$payment_type->summary_email = $this->request()[ 'summary_email' ];
		$payment_type->legal_name_payment = $this->request()[ 'legal_name_payment' ];
		$payment_type->hour_rate = floatval( $this->request()[ 'hour_rate' ] );
		$payment_type->save();
		$this->payInfo( $admin );
	}

	private function saveBankInfo( $admin ){
		$payment_type = $admin->payment_type();
		if( !$payment_type->id_admin_payment_type ){
			$payment_type = new Crunchbutton_Admin_Payment_Type;
			$payment_type->id_admin = $admin->id_admin;
		}
		if( $this->request()[ 'legal_name_payment' ] ){
			$payment_type->legal_name_payment = $this->request()[ 'legal_name_payment' ];
		}
		$payment_type->balanced_bank = $this->request()[ 'id' ];
		$payment_type->balanced_id = $this->request()[ 'href' ];
		$payment_type->save();
		$this->payInfo( $admin );
	}

	private function payInfo( $admin ){
		$payment_type = $admin->payment_type();
		if( $payment_type->id_admin_payment_type ){
			$out = $payment_type->exports();
			$out[ 'name' ] = $admin->name;
			$out[ 'hour_rate' ] = floatval( $payment_type->hour_rate );
			echo json_encode( $out );
		} else {
			echo json_encode( [ 'id_admin' => $admin->id_admin, 'name' => $admin->name, 'legal_name_payment' => $admin->name, 'summary_email' => $admin->email ] );
			exit;
		}
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}