<?php

class Controller_api_chat extends Crunchbutton_Controller_RestAccount {

	public function init() {
		$res = Event::emit([
			'room' => [
				'chat.lobby',
			]
		], 'message', ['text' => 'test'], false);

		
		print_r($res);
	}
}