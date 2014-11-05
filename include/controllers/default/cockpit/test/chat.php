<?php
class Controller_test_chat extends Crunchbutton_Controller_Account {
	public function init() {
		$message = Support_Message::o(41945);
		
		$out = $message->exports();
		$out['type'] = 'ticket.message';

		$res = Chat::emit($out);
		
		echo $res;
	}
}