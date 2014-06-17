<?php
class Controller_test_summary extends Crunchbutton_Controller_Account {
	public function init() {

		$settlement = new Settlement;
		$id_payment = c::getPagePiece( 2 );
		$summary = $settlement->restaurantSummaryByIdPayment( $id_payment );

		if( !$summary || !$_SESSION['admin'] ){
			die('invalid payment');
		}
// echo '<pre>';var_dump( $summary );exit();
		$mail = new Crunchbutton_Email_Payment_Summary( [ 'summary' => $summary ] );
		echo $mail->message();
		exit;
	}
}