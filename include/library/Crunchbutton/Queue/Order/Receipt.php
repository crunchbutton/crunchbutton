<?php

class Crunchbutton_Queue_Order_Receipt extends Crunchbutton_Queue {
	public function run() {

		// Check if it was not sent yet
		$queue = Crunchbutton_Queue::q('SELECT * FROM queue WHERE id_order = ? AND type = ? AND status = ? ORDER by id_queue DESC LIMIT 1',[$this->id_order, self::TYPE_ORDER_RECEIPT, self::STATUS_SUCCESS])->get(0);

		if($queue->id_queue){
			$this->info = 'Confirmation already sent: '. $queue->id_queue;
			$this->save();
			return self::STATUS_SUCCESS;
		}

		// send customer a receipt
		$this->order()->receipt();

		return self::STATUS_SUCCESS;
	}
}