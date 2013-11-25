<?php

class Controller_support_punchcard extends Crunchbutton_Controller_Account {
	public function init() {
	
		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud' ])) {
			return ;
		}

		ob_start();

		$twilio = new Twilio(c::config()->twilio->live->sid, c::config()->twilio->live->token);
		
		$days = ['sun','mon','tue','wed','thu','fri','sat','sun'];

		foreach (@$twilio->account->calls->getIterator(0, 100, [
			'direction' => 'inbound',
			'StartTime>' => (new DateTime())->modify('-8 days')->format('Y-m-d'),
			'StartTime<' => (new DateTime())->format('Y-m-d')

		]) as $call) {
			if ($call->direction == 'inbound' && $call->duration > 15 && $call->to == '+1_PHONE_') {
				//$date = (new DateTime($call->start_time))->format('D');
				//$dates[strtolower($date)]++;

				$calls[] = (object)[
					'start_time' => $call->start_time,
					'from' => $call->from,
					'duration' => $call->duration,
				];
			}
		}
		ob_end_clean();
		
		//print_r($dates);
		
		foreach ($calls as $call) {
			echo $call->start_time.'<br>';
			echo $call->from.'<br>';
			echo $call->duration.'<br>';
			echo '<br>';
		}

		exit;
	}
}

