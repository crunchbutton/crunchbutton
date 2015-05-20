<?php

class Crunchbutton_Log_Monitor_Credit_Card_Fail extends Crunchbutton_Log_Monitor {

	// methods that will be called
	public $rules = [ 'fourOrMoreLogsLastHour' ];

	public function monitor(){

		$query = 'SELECT * FROM log WHERE type = ? AND data LIKE ?';

		// search criteria
		$keys = [ 'order-log', '%credit card error%' ];

		// run the query
		$logs = $this->runQuery( $query, $keys, self::type() );

		// store the logs
		$this->store( $logs, self::type() );

		$this->rules();

	}

	// check if it has 4 or more registers in the last hour
	public function fourOrMoreLogsLastHour(){
		// Check if there were more than 4 logs at the last hour
		$query = 'SELECT * FROM log_monitor WHERE type = ? AND date < DATE_SUB( NOW(), INTERVAL 1 HOUR )';
		$logs = Crunchbutton_Log_Monitor::q( $query, [ self::type() ] );
		if( $logs->count() >= 4 ){
			$body = 'Important: In last 1 hour we had ' . $logs->count() . ' credit card errors!';
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