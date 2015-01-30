<?php

class Crunchbutton_Pexcard_Business extends Crunchbutton_Pexcard_Resource {

	public function current(){
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$start = $now->format( 'm/d/Y' );
		$now->modify( '+ 1 day' );
		$end = $now->format( 'm/d/Y' );
		return Crunchbutton_Pexcard_Business::transactions( $start, $end );
	}

	public function profile(){
		return Crunchbutton_Pexcard_Resource::request( 'businessprofile', [], true );
	}

	public function transactions( $start, $end ){
		$transactions = Crunchbutton_Pexcard_Resource::request( 'businessfundingreport', [ 'StartTime' => $start, 'EndTime' => $end ] );
		if( $transactions->body ){
			return $transactions->body->transactions;
		}
		else if( $transactions->message ){
			return $transactions->message;
		} else {
			return false;
		}
	}
}

?>
