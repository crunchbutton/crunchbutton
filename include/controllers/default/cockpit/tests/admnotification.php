<?php

class Controller_tests_admnotification extends Crunchbutton_Controller_Account {
	public function init() {

		echo '<pre>';

		$notification = new Crunchbutton_Admin_Notification();
		$notification->resendNotification();
exit();
		
	}
}