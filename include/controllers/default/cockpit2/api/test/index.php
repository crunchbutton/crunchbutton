<?php

class Controller_api_test extends Crunchbutton_Controller_Rest {
	public function init() {

// die('hard');
		// $card = Crunchbutton_Pexcard_Card::change_status( 133214, 'OPEN' );
echo '<pre>';var_dump( Crunchbutton_Pexcard_Card::card_list() );exit();
		echo '<pre>';var_dump(  Crunchbutton_Pexcard_Card::change_status( 133214, 'OPEN' )  );exit();
		// echo '<pre>';var_dump( $card->ping() );exit();
		// echo '<pre>';var_dump( $card->card_list() );exit();
		$cards = Crunchbutton_Pexcard_Card::details( 100255 );
		if( $cards->body && $cards->body->cards ){
			foreach( $cards->body->cards as $card ){
				// echo '<pre>';var_dump( $card );exit();
				echo '<pre>';var_dump( Crunchbutton_Pexcard_Card::change_status( $card->id, Crunchbutton_Pexcard_Card::CARD_STATUS_OPEN ) );exit();
			}
		}

		echo '<pre>';var_dump(
			Crunchbutton_Pexcard_Card::change_status( 100254, Crunchbutton_Pexcard_Card::CARD_STATUS_BLOCKED )
			// Crunchbutton_Pexcard_Card::details( 100256 )
		 );exit();
		// echo '<pre>';var_dump( $card->create( [ 'firstName' => 'Test', 'lastName' => '001' ] ) );exit();

		die('hard');
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