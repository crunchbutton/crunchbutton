<?php
class Controller_api_temp_test extends Crunchbutton_Controller_Rest {
	public function init() {
		$now = new DateTime( $_GET['date'], new DateTimeZone( c::config()->timezone ) );
		$end = $now->format( 'm/d/Y' );
		$now->modify( '-1 days' );
		$start = $now->format( 'm/d/Y' );
		Crunchbutton_Pexcard_Transaction::saveTransactionsByPeriod( $start, $end );


		// Crunchbutton_Pexcard_Transaction::convertTimeZone();

		echo 'finished';
	}
}