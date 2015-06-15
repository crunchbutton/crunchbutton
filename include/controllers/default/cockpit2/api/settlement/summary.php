<?php

class Controller_Api_Settlement_Summary extends Crunchbutton_Controller_Account {

	public function init() {

		$id_payment =  c::getPagePiece( 3 );

		$payment = Payment::o( $id_payment );
		if( !$payment->id_payment ){
			$this->_error();
		}
		if( $payment->id_restaurant ){
			$this->_permission();
		} else if( $payment->id_driver ){

			if( $payment->id_driver != c::user()->id_admin ){
				$this->_permission();
			}

			$settlement = new Crunchbutton_Settlement;
			$summary = $settlement->driverSummaryByIdPayment( $id_payment );
			$mail = new Crunchbutton_Email_Payment_Summary( [ 'summary' => $summary ] );
			echo $mail->message();

		}

	}

	private function _permission(){
		if( !c::admin()->permission()->check( ['global', 'settlement' ] ) ){
			$this->_error();
		}
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}
