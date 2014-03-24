<?php

class Controller_schedule extends Crunchbutton_Controller_Account {
	public function init() {
		header( 'Location: /drivers/shift/schedule/driver/' );
	}
}