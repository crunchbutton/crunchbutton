<?php
class Controller_test_tipper extends Crunchbutton_Controller_Account {
	public function init() {
		$u = User::o(2807);
		echo $u->tipper();
	}
}