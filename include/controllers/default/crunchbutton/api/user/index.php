<?php

class Controller_api_user extends Crunchbutton_Controller_Rest {
	public function init() {

		switch ($this->method()) {

			case 'get':
				echo c::user()->json();
				break;
		}
	}
}