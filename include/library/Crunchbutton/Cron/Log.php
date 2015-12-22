<?php

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

	public static function start(){

		$crons = Crunchbutton_Cron_Log::q( "SELECT * FROM cron_log WHERE `interval` != '' AND interval_unity > 0" );
		if( $crons->count() ){
			foreach( $crons as $cron ){
				if( $cron->should_start() ){
					$cron->que();
				}
			}
		}
	}

	public function que(){

		if( $this->update_next_time() ){

			$this->current_status = Crunchbutton_Cron_Log::CURRENT_STATUS_RUNNING;
			$this->save();

			if( class_exists( $this->class ) ){
				$job = new $this->class;
				$job->id_cron_log = $this->id_cron_log;

				if( is_a( $job, 'Crunchbutton_Cron_Log' ) ){
					if( method_exists( $job, 'run' ) ){

						$env = ( $this->env ? $this->env : 'live' );

						Cana::timeout( function() use( $job ) {
							$job->run();
						}, 1000, true, $env );

					} else {
						$this->log( 'run', 'error: ' . $this->class . ' doesnt have the method run' );
					}
				} else {
					$this->log( 'run', 'error: ' . $this->class . ' isnt instance of Crunchbutton_Cron_Log' );
				}
			} else {
				$this->log( 'run', 'error: ' . $this->class . ' doesnt exist' );
			}
		}
	}

	public function should_start(){

		// now
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );

		// if it didn't have a next time it might be the first time it is running
		if( !$this->next_time() ){
			if( !$this->update_next_time() ){
				return false;
			}
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
		return false;
	}


	public function update_next_time(){

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );

		$date = ( $this->next_time() ) ? $this->next_time() : $this->start_date();

		$watch_dog = 1;

		if( $date < $now ){
			$next_time = $date->modify( '+ ' .  ( $this->interval_unity ) . ' ' . ( $this->interval ) );
			while ( $now > $next_time ) {
				$next_time = $date->modify( '+ ' .  ( $this->interval_unity ) . ' ' . ( $this->interval ) );
				$watch_dog++;
				if( $watch_dog >= 10000 ){
					$message = 'The cron task "' . $this->description . '" have a problem updating the next_time and didn\'t run. If you get this message tell it to the devs. Thank you.';
					Crunchbutton_Cron_Log::warning( [ 'body' => $message ] );
					return false;
				}
			}
		} else {
			$next_time = $date;
		}

		$this->next_time = $next_time->format( 'Y-m-d H:i:s' );
		$this->save();

		return true;
	}

	public function error_warning(){

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );

		// Create a support ticket
		$last_time_it_started = $this->next_time();
		$message = 'The cron task "' . $this->description . '" started running at ' . $last_time_it_started->format('M jS Y g:i:s A') . ' and didn\'t finish yet.' . "\n" . 'Please check it, it seems an error has occurred.';
		$message .= "\n";
		$message .= "Now is:" . $now->format('M jS Y g:i:s A');
		$message .= "\n\n";
		$message .= json_encode( $this->properties() );

		Crunchbutton_Cron_Log::warning( [ 'body' => $message ] );

		// change the current status to let it start
		$this->status = Crunchbutton_Cron_Log::CURRENT_STATUS_IDLE;
		$this->save();
	}

	// called when the cron finish running
	public function finished(){
		$this->log( 'finished', 'finished' );
		$job = Crunchbutton_Cron_Log::o( $this->id_cron_log );
		$job->finished = date('Y-m-d H:i:s');
		$job->interactions = ( !$job->interactions ? 1 : $job->interactions + 1 );
		$job->current_status = Crunchbutton_Cron_Log::CURRENT_STATUS_IDLE;
		$job->save();
	}

	public function start_date() {
		if( $this->start_date ){
			if ( !isset( $this->_start_date ) ){
				$this->_start_date = new DateTime( $this->start_date, new DateTimeZone( c::config()->timezone ) );
			}
			return $this->_start_date;
		}
		return false;
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

	public function warning( $params ){
		$email = new Crunchbutton_Email( [ 	'to' => 'dev@_DOMAIN_,_USERNAME_',
																				'from' => 'support@_DOMAIN_',
																				'subject' => 'Cron Job Error',
																				'messageHtml' => $params[ 'body' ],
																				'reason' => Crunchbutton_Email_Address::REASON_CRON_ERROR ] );
		$email->send();

	}

	public function log( $method, $message ){
		$data = [ 'type' => 'cron-jobs', 'method' => $method, 'message' => $message, 'desc' => $this->description, 'id_cron_log' => $this->id_cron_log ];
		Log::debug( $data );
		echo date('Y-m-d H:i:s') . ' - ' . $this->class . '::' . $method . ' > ' . $message . "\n";
	}
}