<?php

class Crunchbutton_Notification extends Cana_Table
{
	const TYPE_SMS   = 'sms';
	const TYPE_EMAIL = 'email';
	const TYPE_PHONE = 'phone';
	const TYPE_URL   = 'url';
	const TYPE_FAX   = 'fax';
	const TYPE_ADMIN = 'admin';
	const TYPE_STEALTH = 'stealth';

	public function send(Crunchbutton_Order $order) {

		$env = c::getEnv();
		if ($_SESSION['admin'] && c::admin()->testphone) {
			c::config()->twilio->testnumber = c::admin()->testphone;
		}

		$num = ($env == 'live' ? $this->value : c::config()->twilio->testnumber);
		$sms = ($env == 'live' ? $this->value : c::config()->twilio->testnumber);
		$mail = ($env == 'live' ? $this->value : '_EMAIL');
		$fax = ($env == 'live' ? $this->value : '_PHONE_');

		Log::debug( [ 'order' => $order->id_order, 'action' => 'send notification', 'value' => $this->value, 'notification type' => $this->type, 'host' => c::config()->host_callback, 'type' => 'notification' ]);

		switch ($this->type) {

			case Crunchbutton_Notification::TYPE_STEALTH:

				$mail = new Crunchbutton_Email_Order_Stealthfax( [ 'order' => $order ] );

				$temp = tempnam('/tmp','fax');

				$mail->message();

				file_put_contents( $temp, $mail->message() );
				//chmod($temp, 0777);
				rename($temp, $temp.'.html');

				$log = new Notification_Log;
				$log->id_notification = $this->id_notification;
				$log->status = 'pending';
				$log->type = 'phaxio';
				$log->date = date('Y-m-d H:i:s');
				$log->id_order = $order->id_order;
				$log->save();

				// Log
				Log::debug( [ 'order' => $order->id_order, 'action' => 'send fax stealth', 'fax stealth' => $fax, 'host' => c::config()->host_callback, 'type' => 'notification' ]);

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
					$log->data = json_encode($fax);
					$log->save();
					// Send a sms informing the error
					$this->smsFaxError( $order );
				}

				if ($order->restaurant()->confirmation && !$order->_confirm_trigger) {
					$order->_confirm_trigger = true;
				}

				break;

			case 'fax':
				$mail = new Email_Order([
					'order' => $order
				]);

				$temp = tempnam('/tmp','fax');
				$mail->message();
				file_put_contents($temp, $mail->message());
				//chmod($temp, 0777);
				rename($temp, $temp.'.html');

				$log = new Notification_Log;
				$log->id_notification = $this->id_notification;
				$log->status = 'pending';
				$log->type = 'phaxio';
				$log->date = date('Y-m-d H:i:s');
				$log->id_order = $order->id_order;
				$log->save();

				// Log
				Log::debug( [ 'order' => $order->id_order, 'action' => 'send fax', 'fax' => $fax, 'host' => c::config()->host_callback, 'type' => 'notification' ]);

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
					$log->data = json_encode($fax);
					$log->save();
					// Send a sms informing the error
					$this->smsFaxError( $order );
				}

				if ($order->restaurant()->confirmation && !$order->_confirm_trigger) {
					$order->_confirm_trigger = true;
				}

				return $fax->success;

				break;

			case 'sms':

				$message = $order->message('sms');

				$sms = Crunchbutton_Message_Sms::send([
								'to' => $sms,
								'from' => 'restaurant',
								'message' => $message,
								'reason' => Crunchbutton_Message_Sms::REASON_CUSTOMER_ORDER
							]);

				if ($order->restaurant()->confirmation && !$order->_confirm_trigger) {
					$order->_confirm_trigger = true;
					// If the restaurant has fax notification don't send the confimation now, CB should wait the fax finished #1239
					if( !$order->restaurant()->hasFaxNotification() ){
						$order->queConfirm();
					} else {
						Log::debug( [ 'order' => $order->id_order, 'action' => 'sms - restaurant has fax notification - wait the fax confirm', 'hasFaxNotification' => $order->restaurant()->hasFaxNotification(), 'type' => 'notification' ] );
					}
				}


				$log = new Notification_Log;
				$log->id_notification = $this->id_notification;
				$log->status = 'success';
				$log->type = 'twilio';
				$log->date = date('Y-m-d H:i:s');
				$log->id_order = $order->id_order;
				$log->save();

				break;

			case 'phone':

				// Log
				Log::debug( [ 'order' => $order->id_order, 'action' => 'send call', 'num' => $num, 'host' => c::config()->host_callback, 'type' => 'notification' ]);

				$log = new Notification_Log;
				$log->id_notification = $this->id_notification;
				$log->status = 'pending';
				$log->type = 'twilio';
				$log->id_order = $order->id_order;
				$log->save();

				$twilio = c::twilio();
				$call = $twilio->account->calls->create(
					c::config()->twilio->{$env}->outgoingRestaurant,
					'+1'.$num,
					'http://'.c::config()->host_callback.'/api/order/'.$order->id_order.'/say?id_notification='.$this->id_notification,
					[
						'StatusCallback' => 'http://'.c::config()->host_callback.'/api/notification/'.$log->id_notification_log.'/callback',
						'FallbackUrl' => c::config()->twilio->fallbackUrl
//						'IfMachine' => 'Hangup'
					]
				);

				$log->remote = $call->sid;
				$log->status = $call->status;
				$log->save();

				break;

			case 'email':

				// Log
				Log::debug( [ 'order' => $order->id_order, 'action' => 'send mail', 'mail' => $mail, 'type' => 'notification' ]);

				$mail = new Email_Order([
					'order' => $order,
					'email' => $mail
				]);
				$mail->send();

				if ($order->restaurant()->confirmation && !$order->_confirm_trigger) {
					$order->_confirm_trigger = true;
					// If the restaurant has fax notification don't send the confimation now, CB should wait the fax finished #1239
					if( !$order->restaurant()->hasFaxNotification() ){
						$order->queConfirm();
					} else {
						Log::debug( [ 'order' => $order->id_order, 'action' => 'email - restaurant has fax notification - wait the fax confirm', 'hasFaxNotification' => $order->restaurant()->hasFaxNotification(), 'type' => 'notification' ] );
					}
				}

				$log = new Notification_Log;
				$log->id_notification = $this->id_notification;
				$log->status = 'success';
				$log->type = 'email';
				$log->date = date('Y-m-d H:i:s');
				$log->id_order = $order->id_order;
				$log->save();

				break;
		}
	}

	public function smsFaxError( $order ){

		Log::debug( [ 'order' => $order->id_order, 'action' => 'smsFaxError init', 'object' => $order->json(), 'type' => 'notification' ] );

		$date = $order->date();
		$date = $date->format( 'M jS Y' ) . ' - ' . $date->format( 'g:i:s A' );

		if( $env != 'live' ){
			return;
		}

		$message = Crunchbutton_Message_Sms::greeting() . 'FAX Error: O# ' . $order->id_order . ' for ' . $order->restaurant()->name . ' (' . $date . ').';
		$message .= "\n";
		$message .= 'R# ' . $order->restaurant()->phone();
		$message .= "\n";
		$message .= 'C# ' . $order->user()->name . ' : ' . $order->phone();
		$message .= "\n";
		$message .= 'E# ' . $env;

		// Make these notifications pop up on support on cockpit #3008
		Crunchbutton_Support::createNewWarning( [ 'id_order' => $order->id_order, 'body' => $message ] );

		Crunchbutton_Message_Sms::send([
			'to' => Crunchbutton_Support::getUsers(),
			'message' => $message,
			'reason' => Crunchbutton_Message_Sms::REASON_SUPPORT_WARNING
		]);
	}


	public function admin(){
		return Crunchbutton_Admin::o( $this->id_admin );
	}

	public function confirm() {

	}

	public function save($new = false) {
		if ($this->type == 'sms' || $this->type == 'fax') {
			$this->value = Phone::clean($this->value);
		}
		return parent::save();
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
