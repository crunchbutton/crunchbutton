<?php

class Controller_api_v1_home extends Crunchbutton_Controller_Rest {
	public function init() {
		echo c::app()->user()->watched()->json();
	}
}