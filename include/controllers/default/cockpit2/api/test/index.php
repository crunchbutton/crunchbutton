<?php

class Controller_api_test extends Crunchbutton_Controller_Rest {
	public function init() {


		$set = new Crunchbutton_Settlement;
		$set->checkPaymentStatus();
		die('hard');


		$set = new Crunchbutton_Settlement;
		$set->payDriver( 622 );
		die('hard');


	$payment = Crunchbutton_Payment::o( 2167 );
	if( $payment->id_payment ){
		// $schedule = $payment->schedule_error();
		// echo '<pre>';var_dump( $schedule );exit();
		// die('hard');
	$status = $payment->checkBalancedStatus();
	echo '<pre>';var_dump( $status );exit();
		// echo json_encode( [ 'success' => $status ] );
	}




		die('hard');

		// $credit = new Crunchbutton_Balanced_Credit;
		// $credit = Balanced\Credit::get('CR7jjKTLQUMlJDP2gZs3DIcT');
		// echo '<pre>';var_dump( $credit );exit();
		Balanced\Settings::$api_key = "***REMOVED***";
		// Balanced\Settings::$api_key = "ak-test-78Lbl2ZDE9ll0zEkHMQBsDZBhCSOjdJV";

		$credit = Balanced\Credit::get('/credits/CR7jjKTLQUMlJDP2gZs3DIcT');

		// $credit = Balanced\Credit::get('/credits/CR482VqDxCRiSwUZyrpVISbK');
		echo '<pre>';var_dump( $credit->status, $credit->failure_reason );exit();
// echo '<pre>';var_dump( $credit );exit();
		$marketplace = Balanced\Marketplace::mine();
		$credits = $marketplace->credits->query()->all();

		echo '<pre>';var_dump( $credits );exit();
	}
}