<?php
class Controller_test_chat extends Crunchbutton_Controller_Account {
	public function init() {
		Chat::emit([
			'cmd' => 'ticket.message',
			'message' => 'TEST'
		]);
	}
}