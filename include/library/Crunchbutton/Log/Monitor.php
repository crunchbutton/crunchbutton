<?php

class Crunchbutton_Log_Monitor extends Cana_Table {

	const DATE_START = '2015-05-01 00:00:01';

	const TYPE_CREDIT_CARD_FAIL = 'credit-card-fail';

	const TYPE_CLASS_CREDIT_CARD_FAIL = 'Crunchbutton_Log_Monitor_Credit_Card_Fail';

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->date, new DateTimeZone(c::config()->timezone));
		}
		return $this->_date;
	}

	// runs at cron all the types -- or can be runned individually
	public function run(){
		$types = [];
		// add the types that will be monitored
		$types[] = Crunchbutton_Log_Monitor::TYPE_CREDIT_CARD_FAIL;
		foreach( $types as $type ){
			$type = 'TYPE_CLASS_' . str_replace( '-', '_', strtoupper( $type ) );
			$class = constant( 'self::' . $type );
			if( $class ){
				$monitor = new $class;
				$monitor->monitor();
			}
		}
	}

	public function rules(){
		if( $this->rules && count( $this->rules ) ){
			foreach( $this->rules as $rule ){
				if( method_exists( $this, $rule ) ){
					$this->$rule();
				}
			}
		}
	}

	public function runQuery( $query, $keys = [], $type ){

		$query .= ' AND date > ? ';
		$query .= ' ORDER BY id_log ASC';
		$keys[] = self::dateStartLimit( $type );

		return Crunchbutton_Log::q( $query, $keys );

	}

	// Actions
	public function actionCreateTicket( $params ){
		Crunchbutton_Support::createNewWarning( $params );
	}

	public function checkIfExists( $id_log, $type ){
		$log_monitor = Crunchbutton_Log_Monitor::q( 'SELECT * FROM log_monitor WHERE id_log = ? AND type = ?', [ $id_log, $type ] );
		if( $log_monitor->id_log_monitor ){
			return true;
		}
		return false;
	}

	public function dateStartLimit( $type ){

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$now->modify( '-1 hour' );
		return $now->format( 'Y-m-d H:i' );

		$log_monitor = Crunchbutton_Log_Monitor::q( 'SELECT * FROM log_monitor WHERE type = ? ORDER BY id_log DESC LIMIT 1', [ $type ] )->get( 0 );
		if( $log_monitor->id_log ){
			return $log_monitor->date()->format( 'Y-m-d H:i:s' );
		}
		return Crunchbutton_Log_Monitor::DATE_START;
	}

	public function store( $logs, $type ){
		if( $logs && $logs->count() ){
			foreach( $logs as $log ){
				if( !self::checkIfExists( $log->id_log, $type ) ){
					$info = json_decode( $log->data );
					if( $info->phone ){
						$phone = Phone::byPhone( $info->phone );
					}
					$log_monitor = new Crunchbutton_Log_Monitor;
					$log_monitor->id_log = $log->id_log;
					$log_monitor->type = $type;
					$log_monitor->date = $log->date;
					if( $phone->id_phone ){
						$log_monitor->id_phone = $phone->id_phone;
					}
					$log_monitor->save();
				}
			}
		}
	}

	public function log(){
		if( !$this->_log ){
			$this->_log = Crunchbutton_Log::o( $this->id_log );
		}
		return $this->_log;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('log_monitor')
			->idVar('id_log_monitor')
			->load($id);
	}
}