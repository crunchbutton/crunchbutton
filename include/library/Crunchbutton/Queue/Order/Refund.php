<?php

class Crunchbutton_Queue_Order_Refund extends Crunchbutton_Queue {
	public function run() {

		$data = $this->info;

		$data = json_decode( $data );

		if( !$data ){ return self::STATUS_FAILED; }

		$order = $this->order();

		$order->refund( $data->amount, $data->reason, $data->tell_driver, $data->id_admin, true );

		return self::STATUS_SUCCESS;
	}
}