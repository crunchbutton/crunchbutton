<?php

class Crunchbutton_Log_Monitor_Credit_Card_Fail extends Crunchbutton_Log_Monitor {

	// methods that will be called
	public $rules = [ 'fourOrMoreLogsLastHour' ];

	public function monitor(){

		$type1 = Crunchbutton_Log_Type::byType( 'order-log' );
		$type2 = Crunchbutton_Log_Type::byType( 'orderlog' );

		$query = 'SELECT * FROM log WHERE ( id_log_type = ? OR id_log_type = ? ) AND level = ?';

		// search criteria
		$keys = [ $type1->id_log_type, $type2->id_log_type, Crunchbutton_Log::LEVEL_ERROR ];

		// run the query
		$logs = $this->runQuery( $query, $keys, self::type() );

		// store the logs
		$this->store( $logs, self::type() );
		$this->rules();
	}

	// check if it has 4 or more registers in the last hour
	public function fourOrMoreLogsLastHour(){
		// Check if there were more than 4 logs at the last hour
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$now->modify( '-1 hour' );
		$query = 'SELECT * FROM log_monitor WHERE type = ? AND date > ? AND id_phone IS NOT NULL GROUP BY id_phone';
		$logs = Crunchbutton_Log_Monitor::q( $query, [ self::type(), $now->format( 'Y-m-d H:i:s' ) ] );
		if( $logs->count() >= 4 ){
			$phones = '';
			$commas = '';
			foreach( $logs as $log ){
				$phone = Phone::o( $log->id_phone );
				if( $phone->id_phone ){
					$phones .= $commas . Phone::formatted( $phone->phone );
					$commas = ', ';
				}
			}
			if( $phones ){
				$phones = 'Phones: ' . $phones . '.';
			}
			$body = 'Important: In last 1 hour we had ' . $logs->count() . ' credit card errors! ' . $phones;
			self::actionCreateTicket( [ 'body' => $body ] );
		}
	}

	public function type(){
		return Crunchbutton_Log_Monitor::TYPE_CREDIT_CARD_FAIL;
	}

	public function __construct($id = null) {
		parent::__construct();
	}

}