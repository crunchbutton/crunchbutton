<?php
class Controller_api_temp_test extends Crunchbutton_Controller_Rest {
	public function init() {
		Log::debug( [ 'action' => 'testing log db', 'date' => date('YmdHis'), 'type' => 'test' ] );
	}
}