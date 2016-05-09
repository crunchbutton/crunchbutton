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
		$crons = Crunchbutton_Cron_Log::q( "SELECT * FROM cron_log WHERE `interval` != '' AND interval_unity > 0", [], c::dbWrite() );
		if( $crons->count() ){
			foreach( $crons as $cron ){
				if( $cron->should_start() ){
					$cron->que();
				}
			}
		}
	}

	public static function test(){
		echo "starting ... \n";
		$crons = Crunchbutton_Cron_Log::q( "SELECT * FROM cron_log WHERE class ='Crunchbutton_Cron_Job_Test'" );
		if( $crons->count() ){
			foreach( $crons as $cron ){
				if( $cron->should_start() ){
					echo "starting que ... \n";
					$cron->que();
				} else {
					echo "do not should start ... \n";
				}
			}
		} else {
			echo "no cron jobs to run ... \n";
		}
	}

	public function que(){
		echo "\n\n";
		$cronJob = Crunchbutton_Cron_Log::o( $this->id_cron_log );
		echo "updating next time ... \n";
		if( $cronJob->update_next_time() ){
			echo "saving status ... \n";

			$cronJob->current_status = Crunchbutton_Cron_Log::CURRENT_STATUS_RUNNING;
			$cronJob->save();

			if( class_exists( $cronJob->class ) ){
				echo "class {$cronJob->class} exists ... \n";
				$job = new $cronJob->class;
				$job->id_cron_log = $cronJob->id_cron_log;

				if( is_a( $job, 'Crunchbutton_Cron_Log' ) ){
					echo "class {$cronJob->class} is a Crunchbutton_Cron_Log ... \n";
					if( method_exists( $job, 'run' ) ){
						$q = Queue::create( [ 'type' => Crunchbutton_Queue::TYPE_CRON, 'id_cron_log' => $cronJob->id_cron_log ] );
						echo "creating a cron log $q->id_cron_log... \n";
						// $job->run();
					} else {
						echo "class {$cronJob->class} dont have a method run ... \n";
						$cronJob->log( 'run', 'error: ' . $cronJob->class . ' doesnt have the method run' );
					}
				} else {
					echo "class {$cronJob->class} isnt a Crunchbutton_Cron_Log ... \n";
					$cronJob->log( 'run', 'error: ' . $cronJob->class . ' isnt instance of Crunchbutton_Cron_Log' );
				}
			} else {
				echo "class {$cronJob->class} didnt find ... \n";
				$cronJob->log( 'run', 'error: ' . $cronJob->class . ' doesnt exist' );
			}
		}
	}

	public function should_start(){

		$cronJob = Cron_Log::o( $this->id_cron_log );

		// now
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		// if it didn't have a next time it might be the first time it is running
		if( !$cronJob->next_time() ){
			if( !$cronJob->update_next_time() ){
				return false;
			}
		}

		if( $now > $cronJob->next_time( true ) ){
			// make sure it is not running
			if( $cronJob->currentStatus() != Crunchbutton_Cron_Log::CURRENT_STATUS_RUNNING ){
				return true;
			} else {
				// if it is time to run again and the last job didn't finished, some problem occurred
				$cronJob->error_warning();
				return true;
			}
		}
		return false;
	}

	public function currentStatus(){
		$job = c::dbWrite()->get( 'SELECT * FROM cron_log WHERE id_cron_log = ?', [$this->id_cron_log])->get(0);
		return $job->current_status;
	}

	public function update_next_time(){

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );

		$cronJob = Cron_Log::o( $this->id_cron_log );

		$date = ( $cronJob->next_time() ) ? $cronJob->next_time() : $cronJob->start_date();

		$watch_dog = 1;

		if( $date < $now ){
			$next_time = $date->modify( '+ ' .  ( $cronJob->interval_unity ) . ' ' . ( $cronJob->interval ) );
			while ( $now > $next_time ) {
				$next_time = $date->modify( '+ ' .  ( $cronJob->interval_unity ) . ' ' . ( $cronJob->interval ) );
				$watch_dog++;
				if( $watch_dog >= 10000 ){
					$message = 'The cron task "' . $cronJob->description . '" have a problem updating the next_time and didn\'t run. If you get this message tell it to the devs. Thank you.';
					Crunchbutton_Cron_Log::warning( [ 'body' => $message ] );
					return false;
				}
			}
		} else {
			$next_time = $date;
		}

		$cronJob->next_time = $next_time->format( 'Y-m-d H:i:s' );
		$cronJob->save();

		return true;
	}

	public function error_warning(){

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );

		$cronJob = Cron_Log::o( $this->id_cron_log );

		// Create a support ticket
		$last_time_it_started = $cronJob->next_time();
		$message = 'The cron task "' . $cronJob->description . '" started running at ' . $last_time_it_started->format('M jS Y g:i:s A') . ' and didn\'t finish yet.' . "\n" . 'Please check it, it seems an error has occurred.';
		$message .= "\n";
		$message .= "Now is:" . $now->format('M jS Y g:i:s A');
		$message .= "\n\n";
		$message .= json_encode( $cronJob->properties() );

		Crunchbutton_Cron_Log::warning( [ 'body' => $message ] );

		// change the current status to let it start
		$cronJob->status = Crunchbutton_Cron_Log::CURRENT_STATUS_IDLE;
		$cronJob->save();
	}

	// called when the cron finish running
	public function finished(){
		$this->log( 'finished', 'finished' );
		$cronJob = Crunchbutton_Cron_Log::o( $this->id_cron_log );
		$cronJob->finished = date('Y-m-d H:i:s');
		$cronJob->interactions = ( !$cronJob->interactions ? 1 : $cronJob->interactions + 1 );
		$cronJob->current_status = Crunchbutton_Cron_Log::CURRENT_STATUS_IDLE;
		$cronJob->save();
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