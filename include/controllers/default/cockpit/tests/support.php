<?php

class Controller_tests_support extends Crunchbutton_Controller_Account {

	public function init() {
		// this script will add the new type COCKPIT_CHAT at support tickets
		$supports = Crunchbutton_Support::q( 'SELECT * FROM support' );		
		foreach( $supports as $support ){
			if( $support->firstMessage()->body == '(Ticket created at cockpit)' ){
				$support->type = Crunchbutton_Support::TYPE_COCKPIT_CHAT;
				$support->save();
			}
		}
		echo 'done!';
	}
}
