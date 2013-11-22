<?php
class Controller_test_notification extends Crunchbutton_Controller_Account {
	public function init() {
		
		$notification = new Crunchbutton_Admin_Notification();
		$notification->resendNotification();
	}
}