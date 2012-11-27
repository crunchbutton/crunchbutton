<?php

class Crunchbutton_Log extends Cana_Table {
	public static function __callStatic($func, $args) {
		$log = new Log;
		$log->level = $func;
		$log->type = $args[0]['type'];
		$log->data = json_encode($args[0]);
		$log->date = date('Y-m-d H:i:s');
		$log->save();
		
		if ($log->level == 'critical') {
			// send notifications
			
			$env = c::env() == 'live' ? 'live' : 'dev';
			$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
			
			foreach (c::config()->text as $supportName => $supportPhone) {
				$nums[] = $supportPhone;
			}
			
			/*
			$nums = array('_PHONE_');
			*/

			$b = $args[0]['action'] ? $args[0]['action'] : $log->data;
			$find = array(',"', '{', '}');
			$replace = array(",\n\t\"", "{\n\t", "\n}");
			$b = str_replace($find, $replace, $b);

			c::timeout(function() use ($nums, $b, $twilio, $env) {

				$message = str_split($b,160);

				foreach ($nums as $num) {
					foreach ($message as $msg) {
						$twilio->account->sms_messages->create(
							c::config()->twilio->{$env}->outgoingTextCustomer,
							'+1'.$num,
							$message
						);
					}
				}
			});
		}
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('log')
			->idVar('id_log')
			->load($id);
	}
}