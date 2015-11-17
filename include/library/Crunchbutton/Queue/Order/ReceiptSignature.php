<?php

class Crunchbutton_Queue_Order_ReceiptSignature extends Crunchbutton_Queue {
	public function run() {
		// send customer a receipt
		$this->order()->receiptSignature();

		return self::STATUS_SUCCESS;
	}
}