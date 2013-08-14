<?php

class Controller_api_makeadminpass extends Crunchbutton_Controller_RestAccount {
	public function init() {
		echo sha1(c::crypt()->encrypt($_REQUEST['pass']));
	}
}
