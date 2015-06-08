<?php

class Controller_Api_Test_Sandbox extends Crunchbutton_Controller_Account {
	public function init() {

		$community = Community::o( 92 );
		$community->saveClosedMessage();
		echo $community->closedMessage();
	}
}