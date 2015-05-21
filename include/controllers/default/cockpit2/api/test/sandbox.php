<?php

class Controller_Api_Test_Sandbox extends Crunchbutton_Controller_Account {
	public function init() {

		$properties = ( array ) c::db()->dbo();
		foreach( $properties as $p ){
			if( $p->host ){
				$host = $p->host;
			}
		}

		echo '<pre>';var_dump( [ 'desc' => 'testing the cron log', 'host' => $host, 'type' => 'cron-jobs' ] );exit();
	}
}