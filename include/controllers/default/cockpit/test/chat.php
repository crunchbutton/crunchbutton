<?php
class Controller_test_chat extends Crunchbutton_Controller_Account {
	public function init() {
		$res = Chat::emit([
			'cmd' => 'ticket.message',
			'message' => 'TEST'
		]);
		
		echo 'res...<br>'.$res;
	}
}