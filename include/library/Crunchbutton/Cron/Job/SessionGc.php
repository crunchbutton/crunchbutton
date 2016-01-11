<?php

class Crunchbutton_Cron_Job_SessionGc extends Crunchbutton_Cron_Log {
	public function run(){
		c::dbWrite()->query('delete from session where token is null and id_user is NULL and date_activity < date_sub(now(), interval 1 month) limit 5000');
		$this->finished();
	}
}
