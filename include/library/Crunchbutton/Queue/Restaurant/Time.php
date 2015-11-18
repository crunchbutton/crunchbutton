<?php

class Crunchbutton_Queue_Restaurant_Time extends Crunchbutton_Queue {

	public function run() {

		Crunchbutton_Restaurant_Time::register( $this->id_restaurant );

		return self::STATUS_SUCCESS;
	}
}