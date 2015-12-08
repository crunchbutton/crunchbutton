<?php

class Crunchbutton_Cron_Job_PexPreProcessReport extends Crunchbutton_Cron_Log {

	public function run(){

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$end = $now->format( 'm/d/Y' );
		$now->modify( '-1 days' );
		$start = $now->format( 'm/d/Y' );

		Log::debug( [ 'start'=> $start, 'end' => $end, 'type' => 'pex-report', 'started' => true ] );

		Crunchbutton_Pexcard_Transaction::processReport( $start, $end );

		Log::debug( [ 'start'=> $start, 'end' => $end, 'type' => 'pex-report', 'finished' => true ] );

		// it always must call finished method at the end
		$this->finished();
	}
}