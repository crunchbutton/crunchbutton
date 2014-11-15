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
			
			$call->from = preg_replace('/^\+1/','',$call->from);
			$support = Support::byPhone($call->from);
			$ticket = [];

			if ($support && $support->get(0)) {
				$ticket = [
					'id_support' => $support->get(0)->id_support,
					'id_order' => $support->get(0)->id_order,
					'status' => $support->get(0)->status,
					'id_user' => $support->get(0)->id_user,
					'id_admin' => $support->get(0)->id_admin
				];
			}

			$calls[] = [
				'from' => $call->from,
				'status' => $call->status,
				'start_time' => $call->start_time,
				'end_time' => $call->end_time,
				'sid' => $call->sid,
				'ticket' => $ticket
			];

		}

		echo json_encode($calls);
		exit;
	}
}