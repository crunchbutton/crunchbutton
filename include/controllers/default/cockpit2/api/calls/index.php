<?php


ignore_user_abort(false);

class Controller_api_calls extends Crunchbutton_Controller_RestAccount {

	public function init() {
		//"Status" => "in-progress",
		$i = 0;
		$max = 5;
		foreach(c::twilio()->account->calls->getIterator(0,$max,[
			'Direction' => 'inbound',
			'To' => '+1_PHONE_'
		]) as $call) {
			if ($i == $max) {
				break;
			}
			$i++;

			$calls[] = [
				'from' => $call->from,
				'status' => $call->status,
				'start_time' => $call->start_time,
				'end_time' => $call->end_time,
				'sid' => $call->sid
			];

		}

		echo json_encode($calls);
		exit;
	}
}