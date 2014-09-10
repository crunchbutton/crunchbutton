<?php

class Controller_api_test extends Crunchbutton_Controller_RestAccount {
	public function init() {

			$payment_type = Crunchbutton_Admin_Payment_Type::byAdmin( 3 );
			try {
				$credit = Crunchbutton_Balanced_Credit::credit( $payment_type, 1, 'one dollar test' );
			} catch ( Exception $e ) {
				echo '<pre>';var_dump( $e );exit();
				throw new Exception( $e->getMessage() );
				exit;
			}
			echo '<pre>';var_dump( $credit );exit();
	}
}