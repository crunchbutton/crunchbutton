<?php

// todo, 
// run it usint the timeout

class Crunchbutton_Cron_Log extends Cana_Table {
	
	const INTERVAL_MINUTE = 'minute';
	const INTERVAL_DAY = 'day';
	const INTERVAL_WEEK = 'week';
	const INTERVAL_MONTH = 'month';
	const INTERVAL_YEAR = 'year';

	const CURRENT_STATUS_IDLE = 'idle';
	const CURRENT_STATUS_RUNNING = 'running';
	
	const LAST_STATUS_ERROR = 'error';
	const LAST_STATUS_SUCCESS = 'success';
	
	public function __construct($id = null) {
		parent::__construct();
		$this->table('cron_log')->idVar('id_cron_log')->load($id);
	}

	public function start(){
		$crons = Crunchbutton_Cron_Log::q( "SELECT * FROM cron_log " );
		foreach( $crons as $cron ){
			if( $cron->check_if_it_should_start() ){
				$cron->que();
			}
		}
	}

	public function que(){
		
		$this->update_next_time();
		// update status
		$this->current_status = Crunchbutton_Cron_Log::CURRENT_STATUS_RUNNING;
		$this->save();
		// 
		$this->run();
	}

	public function start_date() {
		if ( !isset( $this->_start_date ) ){
			$this->_start_date = new DateTime( $this->start_date, new DateTimeZone( c::config()->timezone ) );
		}
		return $this->_start_date;
	}

	public function next_time( $force = false ){
		if( $force ){ unset( $this->_next_time ); }
		if( $this->next_time ){
			if ( !isset( $this->_next_time ) ){
				$this->_next_time = new DateTime( $this->next_time, new DateTimeZone( c::config()->timezone ) );
			}
			return $this->_next_time;
		} 
		return false;
	}

	public function check_if_it_should_start(){

		// make sure it has interval and unity
		if( $this->interval && $this->interval_unity ){

			// now
			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );

			// if it didn't have a next time it is the first time it is running
			if( !$this->next_time() ){
				$this->update_next_time();
			}

			if( $now > $this->next_time( true ) ){
				// make sure it is not running
				if( $this->current_status != Crunchbutton_Cron_Log::CURRENT_STATUS_RUNNING ){
					return true;
				} else {
					// if it is time to run again and the last job didn't finished, some problem occurred
					$this->error_warning();
					return true;
				}
			} 
		}
		return false;
	}


	public function update_next_time(){

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );

		$date = ( $this->next_time() ) ? $this->next_time() : $this->start_date();

		$count = 1;

		$next_time = $date->modify( '+ ' .  ( $this->interval_unity ) . ' ' . ( $this->interval ) );
		while ( $now > $next_time ) {
			$next_time = $date->modify( '+ ' .  ( $this->interval_unity ) . ' ' . ( $this->interval ) );
			$count++;
		}

		// some error migth have occurred
		$this->next_time = $next_time->format( 'Y-m-d H:i:s' );
		$this->save();
	}

	public function error(){

	}

	public function error_warning(){
		
		$last_time_it_started = $this->next_time();
		$message = 'The cron task "' . $this->description . '" started running at ' . $last_time_it_started->format('M jS Y g:i:s A') . ' and didn`t finish yet.' . "\n" . 'Please check it, it seems an error has occurred.';
		
		Crunchbutton_Support::createNewWarning( [ 'body' => $message ] );

		// change the current status to let it start
		$this->status = Crunchbutton_Cron_Log::CURRENT_STATUS_IDLE;
		$this->save();
	}

	public function run(){
		echo 'running';
		$this->finished();
	}

	public function finished(){
		// update cron
		$this->finished = date('Y-m-d H:i:s');
		$this->interactions = ( !$this->interactions ? 1 : $this->interactions + 1 );
		$this->current_status = Crunchbutton_Cron_Log::CURRENT_STATUS_IDLE;
		$this->last_time_status = Crunchbutton_Cron_Log::LAST_STATUS_SUCCESS;
		$this->save();
	}


}