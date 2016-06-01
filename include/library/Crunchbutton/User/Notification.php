<?php

class Crunchbutton_User_Notification extends Cana_Table {

	const TYPE_SMS = 'sms';
	const TYPE_EMAIL = 'email';
	const TYPE_PUSH_IOS = 'push-ios';
	const TYPE_PUSH_ANDROID = 'push-android';

	const DATA_TYPE_TEXT = 'text';
	const DATA_TYPE_RECEIPT = 'receipt';
	const DATA_TYPE_UPDATE = 'update';

	public function fallbackSend($data) {
		$ns = $data['order']->user()->notifications();
		if (!$n) {
			return false;
		}

		$ress = [];

		foreach ($ns as $n) {
			$ress[] = $n->send($data) ? 0 : 1;
		}

		return array_sum($ress) > 0 ? false : true;
	}

	public function send($data) {

		switch ($data['type']) {
			case self::DATA_TYPE_TEXT:
				$content = $data['content'];
				$title = 'Crunchbutton';
				break;

			case self::DATA_TYPE_RECEIPT:
				if (!$data['order']) {
					return;
				}
				$title = 'Crunchbutton order';
				$content = Crunchbutton_Message_Sms::greeting($data['order']->user()->firstName());
				$content .= $data['order']->message('selfsms');
				break;

			case self::DATA_TYPE_UPDATE:
				$title = 'Crunchbutton order';
				// @todo not done. see Order::textCustomerAboutDriver
				break;
		}

		$env = c::getEnv();

			switch ( $this->type ) {

				case Crunchbutton_Admin_Notification::TYPE_SMS :
					$res = $this->sendSms($data, $content, $title);
					break;

				case Crunchbutton_Admin_Notification::TYPE_EMAIL :
					$res = $this->sendEmail($data, $content, $title);
					break;

				case Crunchbutton_Admin_Notification::TYPE_PUSH_IOS :
					$res = $this->sendPushIos($data, $content, $title);
					break;

				case Crunchbutton_Admin_Notification::TYPE_PUSH_ANDROID :
					$res = $this->sendPushAndroid($data, $content, $title);
					break;
			}
		return $res;
	}

	public function user(){
		if( !$this->_user ){
			$this->_user = User::o( $this->id_user );
		}
		return $this->_user;
	}

	public function sendSms($data, $message, $title){

		$sms = $this->value;

		$ret = Crunchbutton_Message_Sms::send([
			'to' => $sms,
			'from' => 'customer',
			'message' => $message,
			'reason' => Crunchbutton_Message_Sms::REASON_CUSTOMER_DRIVER
		]);

		Log::debug( ['action' => 'send sms to customer', 'sms' => $sms, 'message' => $message, 'type' => 'user_notification' ]);

		return $ret;
	}

	public function sendEmail($data, $message, $title){
		$mail = $this->value;
		$mail = new Email_Notification( [
			'email' => $mail,
			'title' => $title,
			'message' => $message
		] );
		return $mail->send();
	}

	public function sendPushIos($data, $message, $title, $link = '') {

		$r = Crunchbutton_Message_Push_Ios::send([
			'to' => $this->value,
			'message' => $message,
			'count' => 1,
			'id' => 'user-message',
			'link' => $link
		]);

		return $r;
	}

	public function sendPushAndroid($data, $message, $title, $link='') {

		$r = Crunchbutton_Message_Push_Android::send([
			'to' => $this->value,
			'message' => $message,
			'title' => $title,
			'count' => 1,
			'id' => 'user-message'
		]);

		return $r;
	}

	public function save($new = false) {
		if ($this->type == 'sms') {
			$this->value = Phone::clean($this->value);
		}
		return parent::save();
	}


	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('user_notification')
			->idVar('id_user_notification')
			->load($id);
	}
}
