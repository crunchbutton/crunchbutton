<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){

		Event::emit([
			'room' => [ 'tickets', 'ticket.update' ]
		], 'change_ticket_status', [] );


	}
}
