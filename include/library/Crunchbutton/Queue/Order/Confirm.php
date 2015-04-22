<?php

class Crunchbutton_Queue_Order_Confirm extends Crunchbutton_Queue {
	public function run() {
		// send restaurants a confirmation call
		$this->order()->confirm();
		
		return self::STATUS_SUCCESS;
	}
}