<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){
		c::db()->query('delete from session where token is null and id_user is NULL and date_activity < date_sub(now(), interval 1 month) limit 5000');
		die('hard');
	}
}