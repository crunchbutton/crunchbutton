<?php

class Crunchbutton_Notification extends Cana_Table {
	public function send() {

		$env = c::env() == 'live' ? 'live' : 'dev';
		$num = ($env == 'live' ? $this->value : c::config()->twilio->testnumber);

		switch ($this->type) {
			case 'fax':
				$fax = new Phaxio([
					'to' => $this->value,
					'file' => $file
				]);
				break;

			case 'sms':
				$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
				$twilio->account->sms_messages->create(
					c::config()->twilio->{$env}->outgoing,
					'+1'.$num,
					'this is a test'
				);
				break;

			case 'phone':
				/*
				$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
				$call = $twilio->account->calls->create(
					c::config()->twilio->{$env}->outgoing,
					'+1'.$num,
					'http://crunchr.co/api/recieved'
				);
				*/
				//$twilio->account->sms_messages->create(c::config()->twilio->{$env}->outgoing, '+1'.$num, 'this is a test');
				
//				$token = new Services_Twilio_Capability(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
//				$token->allowClientOutgoing(c::config()->twilio->{$env}->sid);
//				http://twimlets.com/message?Message%5B0%5D=Hello%20World

				$twilio = new Services_Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
				$call = $twilio->account->calls->create(
					c::config()->twilio->{$env}->outgoing,
					'+1'.$num,
					'http://twimlets.com/message?Message%5B0%5D=Hello%20World'
				);
				break;

			case 'email':
				break;
		}	
	}
	
	public function que() {
		exec('nohup '.c::config()->dirs->root.'cli/notify.php '.$this->id_notification.' > /dev/null 2>&1 &');
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('notification')
			->idVar('id_notification')
			->load($id);
	}
}