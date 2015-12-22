<?php

class Crunchbutton_Queue_Cron extends Crunchbutton_Queue {

	public function run() {

		$cron = $this->cron();

		if( class_exists( $cron->class ) ){
			$job = new $cron->class;
			$job->id_cron_log = $cron->id_cron_log;
			if( is_a( $job, 'Crunchbutton_Cron_Log' ) ){
				if( method_exists( $job, 'run' ) ){
					$job->run();
				}
			}
		}

		return self::STATUS_SUCCESS;
	}
}
