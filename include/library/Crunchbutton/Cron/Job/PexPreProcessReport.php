<?php

class Crunchbutton_Cron_Job_PexPreProcessReport extends Crunchbutton_Cron_Log {

	public function run(){

		$lastReportDate = c::db()->get( 'SELECT date FROM pexcard_report_order ORDER BY date ASC LIMIT 1' )->get( 0 );
		if( $lastReportDate->date ){
			$lastReportDate = new DateTime( $lastReportDate->date, new DateTimeZone( c::config()->timezone ) );
		} else {
			$lastReportDate = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		}
		$end = $lastReportDate->format( 'm/d/Y' );
		$lastReportDate->modify( '-3 days' );
		$start = $lastReportDate->format( 'm/d/Y' );

		Log::debug( [ 'start'=> $start, 'end' => $end, 'type' => 'pex-report' ] );

		Crunchbutton_Pexcard_Transaction::processReport( $start, $end );

		// it always must call finished method at the end
		$this->finished();
	}
}