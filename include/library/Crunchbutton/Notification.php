<?php

class Crunchbutton_Notification extends Cana_Table {
	public function send() {
		switch ($this->type) {
			case 'fax':
				$fax = new Phaxio([
					'to' => $this->value,
					'file' => $file
				]);
				break;

			case 'sms':
				$twilio = new Twilio(c::config()->twilio->sid, c::config()->twilio->token);
				$twilio->account->sms_messages->create(c::config()->twilio->outgoing, '+1_PHONE_', 'this is a test');
				break;

			case 'phone':
				$twilio = new Twilio(c::config()->twilio->sid, c::config()->twilio->token);
				$twilio->account->sms_messages->create(c::config()->twilio->outgoing, '+1_PHONE_', 'this is a test');
				
				$token = new Services_Twilio_Capability(c::config()->twilio->sid, c::config()->twilio->token);
				$token->allowClientOutgoing(c::config()->twilio->sid);

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