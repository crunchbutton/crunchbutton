<?php

class Crunchbutton_Queue_Order_PexCard_Funds extends Crunchbutton_Queue {
	public function run() {

		$this->order()->pexcardFunds();

		return self::STATUS_SUCCESS;
	}
}