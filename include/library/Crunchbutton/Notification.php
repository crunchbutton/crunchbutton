<?php

class Crunchbutton_Notification extends Cana_Table
{
	const TYPE_SMS   = 'sms';
	const TYPE_EMAIL = 'email';
	const TYPE_PHONE = 'phone';
	const TYPE_URL   = 'url';
	const TYPE_FAX   = 'fax';

	public function send(Crunchbutton_Order $order) {

		$env = c::env() == 'live' ? 'live' : 'dev';
		$num = ($env == 'live' ? $this->value : c::config()->twilio->testnumber);
		$mail = ($env == 'live' ? $this->value : '_EMAIL');
		$fax = ($env == 'live' ? $this->value : '_PHONE_');

		switch ($this->type) {
			case 'fax':
				$mail = new Email_Order([
					'order' => $order
				]);

				$temp = tempnam('/tmp','fax');
				file_put_contents($temp, $mail->message());
				//chmod($temp, 0777);
				rename($temp, $temp.'.html');

				$log = new Notification_Log;
				$log->id_notification = $this->id_notification;
				$log->status = 'pending';
				$log->type = 'phaxio';
				$log->id_order = $order->id_order;
				$log->save();

				$fax = new Phaxio([
					'to' => $fax,
					'file' => $temp.'.html',
					'id_notification_log' => $log->id_notification_log
				]);

				unlink($temp.'.html');

				if ($fax->success) {
					$log->remote = $fax->faxId;
					$log->status = 'queued';
					$log->save();
				} else {
					$log->status = 'error';
					$log->save();
				}

				if ($order->restaurant()->confirmation && !$order->_confirm_trigger) {
					$order->_confirm_trigger = true;
					$order->queConfirm();
				}

				break;

			case 'sms':
				$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
				$message = str_split($order->message('sms'),160);

				foreach ($message as $msg) {
					$twilio->account->sms_messages->create(
						c::config()->twilio->{$env}->outgoingTextRestaurant,
						'+1'.$num,
						$msg
					);
					continue;
				}

				if ($order->restaurant()->confirmation && !$order->_confirm_trigger) {
					$order->_confirm_trigger = true;
					$order->queConfirm();
				}
				break;

			case 'phone':

				Log::debug([
					'order' => $order->id_order,
					'action' => 'send order call',
					'num' => $num,
					'host' => $_SERVER['HTTP_HOST'],
					'callback' => $callback,
					'type' => 'notification'
				]);

				$log = new Notification_Log;
				$log->id_notification = $this->id_notification;
				$log->status = 'pending';
				$log->type = 'twilio';
				$log->id_order = $order->id_order;
				$log->save();

				$twilio = new Services_Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
				$call = $twilio->account->calls->create(
					c::config()->twilio->{$env}->outgoingRestaurant,
					'+1'.$num,
					'http://'.$_SERVER['HTTP_HOST'].'/api/order/'.$order->id_order.'/say?id_notification='.$this->id_notification,
					[
						'StatusCallback' => 'http://'.$_SERVER['HTTP_HOST'].'/api/notification/'.$log->id_notification_log.'/callback'
//						'IfMachine' => 'Hangup'
					]
				);

				$log->remote = $call->sid;
				$log->status = $call->status;
				$log->save();

				break;

			case 'email':
				$mail = new Email_Order([
					'order' => $order,
					'email' => $mail
				]);
				$mail->send();

				if ($order->restaurant()->confirmation && !$order->_confirm_trigger) {
					$order->_confirm_trigger = true;
					$order->queConfirm();
				}
				break;
		}
	}

	public function confirm() {

	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('notification')
			->idVar('id_notification')
			->load($id);
	}

	/**
	 * Gets an array of possible Notification types
	 *
	 * Method is not ready yet, it's just coppied text I found to make it work later when needed
	 *
	 * @return array
	 *
	 * @todo make this code work
	 */
	public function getTypes()
	{

		$sql = "SELECT SUBSTRING(COLUMN_TYPE,5) FROM information_schema.COLUMNS WHERE TABLE_NAME='notification' AND COLUMN_NAME='type' ";
		$rs = c::db()->query($sql);
		$x = eval("return   ['sms','email','phone','url','fax', 'foo'] ; ");

		$type = $this->db->query( "SHOW COLUMNS FROM {$table} WHERE Field = '{$field}'" )->row( 0 )->Type;
		preg_match('/^enum\((.*)\)$/', $type, $matches);
		foreach( explode(',', $matches[1]) as $value )
		{
			$enum[] = trim( $value, "'" );
		}
		return $enum;
	}
}