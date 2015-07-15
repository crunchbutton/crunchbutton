<?php

class Cockpit_Driver_Log extends Cana_Table {

	const ACTION_CREATED_DRIVER = 'created-driver'; // driver pre registered
	const ACTION_CREATED_COCKIPT = 'created-cockpit'; // created by admin
	const ACTION_UPDATED_COCKIPT = 'updated-cockpit'; // updated by admin
	const ACTION_NOTIFIED_SETUP = 'notified-setup'; // notification sent
	const ACTION_DOCUMENT_SENT = 'document-sent'; // document sent
	const ACTION_ACCOUNT_SETUP = 'account-setup'; // login created
	
	public function nextAction( $id_admin ){
		$log = Cockpit_Driver_Log::lastAction( $id_admin );
		if( $log ){
			switch ( $log[ 'action' ] ) {
				case Cockpit_Driver_Log::ACTION_CREATED_DRIVER:
				case Cockpit_Driver_Log::ACTION_CREATED_COCKIPT:
				case Cockpit_Driver_Log::ACTION_UPDATED_COCKIPT:
					return 'Notify / Upload documents';
					break;
				case Cockpit_Driver_Log::ACTION_NOTIFIED_SETUP:
					return 'Notify again / Upload documents';
					break;
				case Cockpit_Driver_Log::ACTION_ACCOUNT_SETUP:
					return 'Nothing';
					break;
			}
		}
		return null;
	}

	public function lastAction( $id_admin ){
		$actions = Cockpit_Driver_Log::byDriver( $id_admin );
		if( count( $actions ) > 0 ){
			return $actions[ 0 ];
		}
		return null;	
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('driver_log')
			->idVar('id_driver_log')
			->load($id);
	}

	public function AllByDriver( $id_admin ){
		$logs = [];
		if( $id_admin ){
			$_logs = Cockpit_Driver_Log::q( 'SELECT * FROM driver_log dl WHERE dl.id_admin = ? ORDER BY dl.id_driver_log ASC', [$id_admin]);
			foreach( $_logs as $log ){
				$logs[] = $log->exports();
			}
		} 
		return $logs;
	}

	public function byDriver( $id_admin ){
		$logs = [];
		if( $id_admin ){
			$_logs = Cockpit_Driver_Log::q( 'SELECT * FROM driver_log dl
																						INNER JOIN ( SELECT MAX(id_driver_log) AS id_driver_log FROM driver_log WHERE id_admin = ? GROUP BY action ) filter ON filter.id_driver_log = dl.id_driver_log
																						ORDER BY dl.id_driver_log DESC', [$id_admin]);
			foreach( $_logs as $log ){
				$logs[] = $log->exports();
			}
			
		} 
		return $logs;
	}

	public function description( $action ){
		switch ( $action ) {
			case Cockpit_Driver_Log::ACTION_CREATED_DRIVER:
				return 'driver registered';
				break;
			case Cockpit_Driver_Log::ACTION_CREATED_COCKIPT:
			return 'driver created at cockpit';
				break;
			case Cockpit_Driver_Log::ACTION_UPDATED_COCKIPT:
				return 'driver info updated';
				break;
			case Cockpit_Driver_Log::ACTION_NOTIFIED_SETUP:
				return 'driver notified';
				break;
			case Cockpit_Driver_Log::ACTION_DOCUMENT_SENT:
				return 'document sent';
				break;
			case Cockpit_Driver_Log::ACTION_ACCOUNT_SETUP:
				return 'setup finished';
				break;
		}
	}

	public function date(){
		if( !$this->_date ){
			$this->_date = new DateTime($this->datetime, new DateTimeZone(c::config()->timezone));
		}
		return $this->_date;
	}

	public function exports(){
		$out = $this->properties();
		if( $out[ 'action' ] ){
			$date = $this->date();
			$out[ 'desc' ] = $this->description( $out[ 'action' ] );
			$out[ 'date' ] = $date->format('M jS Y g:i:s A');
		}
		return $out;
	}
}