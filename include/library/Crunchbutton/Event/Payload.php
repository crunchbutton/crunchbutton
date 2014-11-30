<?php

class Crunchbutton_Event_Payload {
	public $to;
	public $event;
	public $payload;
	
	public function __construct($to, $event, $payload = []) {
		$this->to = $to;
		$this->event = $event;
		$this->payload = $payload;
	}
}