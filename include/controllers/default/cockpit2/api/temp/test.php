<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){

		$q = Crunchbutton_Queue_Event_Emit::o( 34 );
		$q->run();

		die('hard');

			Event::create([
				'room' => [
					'ticket.'.$message->id_support,
					'tickets'
				]
			], 'sms_status', [ 'id_support_message' => $message->id_support_message, 'status' => $this->status ] );

	}
}
