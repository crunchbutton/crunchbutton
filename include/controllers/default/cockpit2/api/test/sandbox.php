<?php

class Controller_Api_Test_Sandbox extends Crunchbutton_Controller_Account {

	public function init() {

		echo '<pre>';

		// $dt = '2015-05-10 18:56:00';
		// $community = Crunchbutton_Community::o( 126 );
		// $community->shutDownCommunity( $dt );
		$notification = new Crunchbutton_Admin_Notification();
		$notification->resendNotification();

	}
}