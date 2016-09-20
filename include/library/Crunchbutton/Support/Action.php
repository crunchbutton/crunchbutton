<?php

class Crunchbutton_Support_Action extends Cana_Table {

	const ACTION_MESSAGE_RECEIVED = 'message-received';
	const ACTION_MESSAGE_REPLIED = 'message-replied';
	const ACTION_NOTIFICATION_SENT = 'notification-sent';

	const TYPE_REPLIED_BY_DRIVER = 'replied-by-driver';
	const TYPE_REPLIED_BY_CS = 'replied-by-cs';
	const TYPE_NOTIFICATION_SENT_TO_DRIVER = 'sent-driver';
	const TYPE_NOTIFICATION_SENT_TO_DRIVERS = 'sent-drivers';
	const TYPE_NOTIFICATION_SENT_TO_CS = 'sent-cs';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('support_action')
			->idVar('id_support_action')
			->load($id);
	}

	public static function create($params) {
		$action = new Support_Action;
		$action->id_support = $params['id_support'];
		$action->id_admin = $params['id_admin'];
		$action->action = $params['action'];
		$action->type = $params['type'];
		$action->data = json_encode($params['data']);
		$action->date = date('Y-m-d H:i:s');
		$action->save();
		return $action;
	}

	public function isWaitingResponse(){
		if($this->action == self::ACTION_MESSAGE_RECEIVED || $this->action == self::ACTION_NOTIFICATION_SENT){
			return true;
		}
		return false;
	}

	public static function checkStatus(){
		$actions = self::q('SELECT * FROM support_action sa
												INNER JOIN(
													SELECT MAX(id_support_action) id_support_action, id_support FROM support_action GROUP BY id_support)
														max ON max.id_support_action = sa.id_support_action');
		foreach($actions as $action){
			$action->runVerification();
		}
	}

	public function runVerification(){
		if($this->isWaitingResponse()){
			$lastChange = $this->date();
			$now = new DateTime('now', new DateTimeZone(c::config()->timezone));
			$minutes = Util::interval2Hours($lastChange->diff($now));

			if($minutes >= 15){
				// send notification to cs
				if(!self::hasCSNotification($this->id_support)){
					$this->notifyCS();
				}
			} else if ($minutes >= 8){
				// sent ticket to drivers
				if(!self::hasDriversNotification($this->id_support)){
					$this->notifyDrivers();
				}
			}
		}
	}

	public function getMessage(){
		$id_support = $this->id_support;
		$support = Support_Message::q('SELECT * FROM support_message WHERE id_support = ? AND `from` = ? ORDER BY id_support_message DESC LIMIT 1', [$id_support, Support_Message::TYPE_FROM_CLIENT])->get(0);
		if($support->body){
			$order = $this->order();
			$message = 'Support ticket @'.$id_support."\n";
			if($order->id_order){
				$message .= 'Last Order: #'.$order->id_order. "\n".
				$message .= 'Customer: '.$order->name.' / '.$order->phone.($order->address ? ' / '.$order->address : '')."\n";
				$message .= 'Restaurant: '.$order->restaurant()->name.$community.' / '.$order->restaurant()->phone.$notifications."\n";
			}
			$message .= $support->body;
			return $message;
		}
		return null;
	}

	public function order(){
		return Order::q('SELECT * FROM `order` WHERE phone=? AND TIMESTAMPDIFF( hour, date, NOW() ) < 24 ORDER BY id_order DESC  LIMIT 1',[$this->support()->phone])->get(0);
	}

	public function notifyDrivers(){
		$reps = Support::getUsers();
		$order = $this->order();
		$id_community = $order->id_community;
		if(!$id_community && $this->support()->phone){
			$community = Crunchbutton_Community::customerCommunityByPhone($this->support()->phone);
			if($community->id_community){
				$id_community = $community->id_community;
			}
		}

		$data = [];
		$reps = [];

		if($id_community){
			$community = Community::o($id_community);
			$drivers = $community->getWorkingDrivers();
			foreach ($drivers as $driver) {
				$reps[$driver->name] = $driver->phone;
				$data['reps'][] = ['id_admin' => $driver->id_admin];
				$type = Support_Action::TYPE_NOTIFICATION_SENT_TO_DRIVERS;
			}
		}

		$type = self::TYPE_NOTIFICATION_SENT_TO_DRIVERS;
		$message = $this->getMessage();
		self::create(['id_support' => $this->id_support,
									'action' => self::ACTION_NOTIFICATION_SENT,
									'type' => $type,
									'data' => $data]);
		if($reps && count($reps)){
			Message_Sms::send([
				'to' => $reps,
				'message' => $message,
				'reason' => Message_Sms::REASON_SUPPORT
			]);
		}
	}

	public function notifyCS(){
		$reps = Support::getUsers();
		$data = ['reps' => $reps];
		$type = self::TYPE_NOTIFICATION_SENT_TO_CS;
		$message = $this->getMessage();
		self::create(['id_support' => $this->id_support,
									'action' => self::ACTION_NOTIFICATION_SENT,
									'type' => $type,
									'data' => $data]);
		Message_Sms::send([
			'to' => $reps,
			'message' => $message,
			'reason' => Message_Sms::REASON_SUPPORT
		]);
	}

	public static function hasDriverNotification($id_support){
		return self::supportHasActionType($id_support, self::ACTION_NOTIFICATION_SENT, self::TYPE_NOTIFICATION_SENT_TO_DRIVER);
	}

	public static function hasDriversNotification($id_support){
		return self::supportHasActionType($id_support, self::ACTION_NOTIFICATION_SENT, self::TYPE_NOTIFICATION_SENT_TO_DRIVERS);
	}

	public static function hasCSNotification($id_support){
		return self::supportHasActionType($id_support, self::ACTION_NOTIFICATION_SENT, self::TYPE_NOTIFICATION_SENT_TO_CS);
	}

	public static function supportHasActionType($id_support, $action, $type){
		$action = self::q('SELECT * FROM support_action WHERE id_support = ? AND action = ?', [$id_support, $action]);
		if($action->id_support){
			return true;
		}
		return false;
	}

	public static function createMessageReceived($id_support, $data){
		return self::create([	'id_support' => $id_support,
													'action' => self::ACTION_MESSAGE_RECEIVED,
													'data' => $data]);
	}

	public function support() {
		if(!$this->_support){
			$this->_support = Support::o($this->id_support);
		}
		return $this->_support;
	}

	public function support_message() {
		if(!$this->_support_message){
			$this->_support_message = Support_Message::o($this->id_support_message);
		}
		return $this->_support_message;
	}

	public function admin() {
		if( !$this->_admin ){
			$this->_admin = Admin::o($this->id_admin);
		}
		return $this->_admin;
	}

	public function date( $timezone = false ) {
		if ( !isset($this->_date) || $timezone ) {
			$this->_date = new DateTime( $this->date, new DateTimeZone( c::config()->timezone ) );
			if( $timezone ){
				$this->_date->setTimezone( new DateTimeZone( $timezone ) );
			}
		}
		return $this->_date;
	}
}