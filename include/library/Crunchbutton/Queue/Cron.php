<?php

class Crunchbutton_Queue_Cron extends Crunchbutton_Queue {

	public function run() {
		echo "\n\n\n";
		echo "Cron starting:\n";
		$cron = $this->cron();
		echo "Cron Class: {$cron->class} \n";
		if( class_exists( $cron->class ) ){
			$job = new $cron->class;
			echo "Cron id_cron_log: {$cron->id_cron_log}  \n";
			$job->id_cron_log = $cron->id_cron_log;
			if( is_a( $job, 'Crunchbutton_Cron_Log' ) ){
				if( method_exists( $job, 'run' ) ){
					echo "Cron running: \n";
					$job->run();
					echo "Cron running finished\n----------------------";
				}
			}
		}
		return self::STATUS_SUCCESS;
	}
}
