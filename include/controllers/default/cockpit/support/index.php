<?php

class Controller_support extends Crunchbutton_Controller_Account {
	public function init() {
		header( 'Location: /support/plus/' );
		exit();
	}
}
