<?php

class Crunchbutton_Notification extends Cana_Table {
	public function send(Crunchbutton_Order $order) {

		//$env = c::env() == 'live' ? 'live' : 'dev';
		$env = 'dev';
		$num = ($env == 'live' ? $this->value : c::config()->twilio->testnumber);
		$mail = ($env == 'live' ? $this->value : '_EMAIL');
		$fax = ($env == 'live' ? $this->value : '_EMAIL');

		switch ($this->type) {
			case 'fax':
				$mail = new Email_Order([
					'order' => $order
				]);

				$temp = tmpfile();
				file_put_contents($temp, $mail->message());
				die($temp);

				$fax = new Phaxio([
					'to' => $this->value,
					'file' => $temp
				]);

				//unlink($temp);
				break;

			case 'sms':
				$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
				$message = str_split($order->message('sms'),160);

				foreach ($message as $msg) {
					$twilio->account->sms_messages->create(
						c::config()->twilio->{$env}->outgoing,
						'+1'.$num,
						$msg
					);
				}
				break;

			case 'phone':
 				$twilio = new Services_Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
				$call = $twilio->account->calls->create(
					c::config()->twilio->{$env}->outgoing,
					'+1'.$num,
					'http://'.$_SERVER['__HTTP_HOST'].'/api/order/34/say'
				);

				$log = new Notification_Log;
				$log->id_notification = $this->id_notification;
				$log->status = $call->status;
				$log->type = 'twilio';
				$log->remote = $call->uri;
				$log->id_order = $order->id_order;
				$log->save();

				break;

			case 'email':
				$mail = new Email_Order([
					'order' => $order,
					'email' => $mail
				]);
				$mail->send();
				break;
		}	
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('notification')
			->idVar('id_notification')
			->load($id);
	}
}