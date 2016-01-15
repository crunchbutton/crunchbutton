<?php

class Crunchbutton_Queue_Event_Emit extends Crunchbutton_Queue {

	public function run() {

		$data = $this->info;

		$data = json_decode( $data );

		if( !$data ){ return self::STATUS_FAILED; }

		Crunchbutton_Event::emit( $data->to, $data->event, $data->payload );

		return self::STATUS_SUCCESS;
	}
}