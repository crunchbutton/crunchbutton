<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {
	public function init() {
			$r = Crunchbutton_Message_Push_Ios::send([
			'to' => 'e95396adea2d968bfdcad91ace0acb2ff2e6e06daf1b5e41405440012067ada3',
			'message' => 'test',
			'count' => 1,
			'id' => 'order-1',
			'category' => 'order-new-test'
		]);
	}
}