<?php
class Controller_test_chat extends Crunchbutton_Controller_Account {
	public function init() {

		$message = Support_Message::o(2913);
		$res = Chat::emit(['room' => 'ticket.'.$message->id_support], 'ticket.message', $message->exports());

		echo $res;
	}
}