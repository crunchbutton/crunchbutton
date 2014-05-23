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
		$this
			->table('cron_log')
			->idVar('id_cron_log')
			->load($id);
	}

	public function start(){
		$crons = Crunchbutton_Cron_Log::q( "SELECT * FROM cron_log " );
		echo '<pre>';var_dump( $crons );exit();
	}

	public function errors(){

	}

	public function run(){

	}


}