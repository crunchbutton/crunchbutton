<?php

class Crunchbutton_Support_Action extends Cana_Table {

	const ACTION_MESSAGE_RECEIVED = 'message-received';
	const ACTION_MESSAGE_REPLIED = 'message-replied';
	const ACTION_NOTIFICATION_SENT = 'notification-sent';
	const ACTION_TICKET_CLOSED = 'ticket-closed';

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
			$minutes = Util::interval2Minutes($lastChange->diff($now));
			if($minutes >= 15){
				// send notification to cs
				if($this->type != self::TYPE_NOTIFICATION_SENT_TO_CS){
					$this->notifyCS();
				}
			} else if ($minutes >= 8){
				// sent ticket to drivers
				if($this->type != self::TYPE_NOTIFICATION_SENT_TO_DRIVERS){
					$this->notifyDrivers();
				}
			}
			self::hasDriversNotification($this->id_support);
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

	public static function createDriverAction($id_support, $message, $order, $media = null){
		$action = self::create(['id_support' => $id_support, 'type' => self::TYPE_NOTIFICATION_SENT_TO_DRIVER, 'action' => self::ACTION_NOTIFICATION_SENT]);
		$action->notifyDriver($message, $order, $media);
	}

	public function notifyDriver($message, $order, $media = null){
		$reps = [];
		$data = ['reps'=>[]];

		if($order){
			$community = $order->community();
			if($community && $community->sent_tickets_to_drivers){
				$driver = $order->driver();
				if($driver->id_admin){
					$driver = Admin::o($driver->id_admin);
					if($driver->isWorking()){
						$reps[$driver->name] = $driver->phone;
						$type = Support_Action::TYPE_NOTIFICATION_SENT_TO_DRIVER;
						$data['reps'][] = ['id_admin' => $driver->id_admin];
					}
				}
			}
		}
		if($reps && count($reps)){
			$this->data = json_encode($params['data']);
			$this->save();
			Message_Sms::send([
				'to' => $reps,
				'message' => $message,
				'media' => $media,
				'reason' => Message_Sms::REASON_SUPPORT
			]);
		} else {
			$this->notifyDrivers($message, $message);
		}
	}

	public function notifyDrivers($message = null, $media = null){
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
			if($community && $community->sent_tickets_to_drivers){
				$drivers = $community->getWorkingDrivers();
				foreach ($drivers as $driver) {
					$reps[$driver->name] = $driver->phone;
					$data['reps'][] = ['id_admin' => $driver->id_admin];
					$type = Support_Action::TYPE_NOTIFICATION_SENT_TO_DRIVERS;
				}
			}
		}

		if(!$message){
			$message = $this->getMessage();
		}

		if($reps && count($reps)){
			self::create(['id_support' => $this->id_support,
							'action' => self::ACTION_NOTIFICATION_SENT,
							'type' => self::TYPE_NOTIFICATION_SENT_TO_DRIVERS,
							'data' => $data]);
			Message_Sms::send([
				'to' => $reps,
				'message' => $message,
				'media' => $media,
				'reason' => Message_Sms::REASON_SUPPORT
			]);
		} else {
			$this->notifyCS($message, $media);
		}
	}

	public function notifyCS($message = null, $media = null){
		$reps = Support::getUsers();
		$data = ['reps' => $reps];
		$type = self::TYPE_NOTIFICATION_SENT_TO_CS;
		if(!$message){
			$message = $this->getMessage();
		}
		self::create(['id_support' => $this->id_support,
									'action' => self::ACTION_NOTIFICATION_SENT,
									'type' => $type,
									'data' => $data]);
		Message_Sms::send([
			'to' => $reps,
			'message' => $message,
			'media' => $media,
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
		$now = new DateTime( 'now', new DateTimeZone(c::config()->timezone));
		$now->modify('-45 minutes');
		$action = self::q('SELECT * FROM support_action WHERE id_support = ? AND action = ? AND date > ?', [$id_support, $action, $now->format('Y-m-d H:i:s')]);
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