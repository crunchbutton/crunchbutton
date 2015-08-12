<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {
	public function init() {
			$r = Crunchbutton_Message_Push_Ios::send([
			'to' => '7f3dac43d5654495a95933ed5394e08eaa7333286d8145aefa962a54306d4dbe',
			'message' => 'test',
			'count' => 1,
			'id' => 'order-1',
			'category' => 'order-new-test'
		]);
	}
}