<?php

class Crunchbutton_Community_Notification extends Cana_Table {

	const CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY = 'notify_community_opened_driver';
	const CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY_EMAIL = 'notify_community_opened_driver_email';
	const CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY_PUSH = 'notify_community_opened_driver_push';
	const CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY_SMS = 'notify_community_opened_driver_sms';
	const CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY_MSG = 'notify_community_opened_driver_msg';
	const CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY_DAYS = 'notify_community_opened_driver_days';

	const NOTIFICATION_TYPE_SMS = 'sms';
	const NOTIFICATION_TYPE_EMAIL = 'email';
	const NOTIFICATION_TYPE_PUSH = 'push';

	const STATUS_NEW = 'new';
	const STATUS_BUILDING = 'building';
	const STATUS_RUNNING = 'running';
	const STATUS_ERROR = 'error';
	const STATUS_FINISHED = 'finished';
	const STATUS_SCHEDULED = 'scheduled';

	const TAG_COMMUNITY_NAME = '%community%';

	public static function create($params = []){

		if(!$params['notification_type'] || !$params['customer_period'] || !$params['id_community']){
			return null;
		}

		$notification = new Crunchbutton_Community_Notification;
		$notification->id_community = $params['id_community'];
		$notification->notification_type = $params['notification_type'];
		$notification->status = self::STATUS_SCHEDULED;
		$notification->status_update = date('Y-m-d H:i:s');
		$notification->customer_period = $params['customer_period'];
		$notification->message = $params['message'];
		if($params['date']){
			$notification->date = $params['date'];
		} else {
			$notification->date = date('Y-m-d H:i:s');
		}
		$notification->save();
		return $notification;
	}

	public function date(){
		if(!$this->_date){
			$this->_date = new DateTime($this->date, new DateTimeZone(c::config()->timezone));
		}
		return $this->_date;
	}

	public static function job(){
		$notifications = self::q('SELECT * FROM community_notification WHERE status = ?', [self::STATUS_SCHEDULED]);
		foreach ($notifications as $notification) {
			$notification->build();
		}
		$notifications = self::q('SELECT * FROM community_notification WHERE status = ? OR status = ?', [self::STATUS_NEW, self::STATUS_RUNNING]);
		foreach ($notifications as $notification) {
			$notification->run();
		}
	}

	public function run(){
		if($this->status != self::STATUS_NEW && $this->status != self::STATUS_RUNNING){
			return;
		}
		$this->updateStatus(self::STATUS_RUNNING);
		$notifications = Crunchbutton_Community_Notification_User::q('SELECT * FROM community_notification_user WHERE id_community_notification = ? AND status = ? LIMIT 20', [$this->id_community_notification, Crunchbutton_Community_Notification_User::STATUS_NEW]);
		if($notifications->count()){

			$message = str_replace(self::TAG_COMMUNITY_NAME, $this->community()->name, $this->message);
			foreach($notifications as $notification){
				$notification->send($message);
			}
		} else {
			$this->updateStatus(self::STATUS_FINISHED);
		}
	}

	public function build(){
		if($this->status != self::STATUS_SCHEDULED){
			return;
		}

		$scheduled = $this->date();
		$now = new DateTime('now', new DateTimeZone(c::config()->timezone));
		if($scheduled > $now){
			return;
		}

		$this->updateStatus(self::STATUS_BUILDING);
		if(!$this->id_community){
			$this->updateStatus(self::STATUS_ERROR, 'The notification must have a community');
			return;
		}
		if(!$this->customer_period){
			$this->updateStatus(self::STATUS_ERROR, 'The notification must have a customer period');
			return;
		}
		switch ($this->notification_type) {
			case self::NOTIFICATION_TYPE_SMS:
				$this->_buildEmailSms($this->notification_type);
				break;
			case self::NOTIFICATION_TYPE_EMAIL:
				$this->_buildEmailSms($this->notification_type);
				break;
			case self::NOTIFICATION_TYPE_PUSH:
				$this->_buildPush();
				break;
		}
		$this->updateStatus(self::STATUS_NEW);
	}

	private function _buildPush(){
		$now = new DateTime( 'now', new DateTimeZone(c::config()->timezone));
		$now->modify('- ' . $this->customer_period . ' days');
		$period = $now->format('Y-m-d');
		$users = c::db()->get('SELECT DISTINCT(user_notification.value) AS value, user_notification.id_user, id_user_notification
														FROM user_notification
														INNER JOIN `order` ON `order`.id_user = user_notification.id_user
														INNER JOIN user ON user.id_user = `order`.id_user
															WHERE id_community = ? AND date > ? AND user.active = 1 AND user_notification.active = 1 AND ( user_notification.type = ? OR user_notification.type = ? )', [$this->id_community, $period, Crunchbutton_User_Notification::TYPE_PUSH_IOS, Crunchbutton_User_Notification::TYPE_PUSH_ANDROID]);

		if($users && $users->count()){
			foreach($users as $user){
				$this->addNotificationUser($user);
			}
		} else {
			$this->updateStatus(self::STATUS_ERROR, 'No users found.');
			return;
		}
	}

	private function _buildEmailSms($type){
		$now = new DateTime( 'now', new DateTimeZone(c::config()->timezone));
		$now->modify('- ' . $this->customer_period . ' days');
		$period = $now->format('Y-m-d');
		if($type == self::NOTIFICATION_TYPE_SMS){
			$users = c::db()->get('SELECT DISTINCT(`order`.phone) AS value, `order`.id_user FROM `order` INNER JOIN user ON user.id_user = `order`.id_user WHERE id_community = ? AND date > ? AND user.active = 1', [$this->id_community, $period]);
		} else {
			$users = c::db()->get( 'SELECT DISTINCT(`user`.email) AS value, `order`.id_user FROM `order` INNER JOIN user ON user.id_user = `order`.id_user WHERE id_community = ? AND date > ? AND user.active = 1', [$this->id_community, $period]);
		}

		if($users && $users->count()){
			foreach($users as $user){
				$this->addNotificationUser($user);
			}
		} else {
			$this->updateStatus(self::STATUS_ERROR, 'No users found.');
			return;
		}
	}

	public function addNotificationUser($user){
		if(!$user->value){
			return;
		}
		if($this->notification_type != self::NOTIFICATION_TYPE_PUSH){
			$notification = Crunchbutton_User_Notification::q('SELECT * FROM user_notification WHERE id_user = ? AND value = ? AND type = ? AND active = 1 ORDER BY id_user_notification DESC LIMIT 1', [$user->id_user, $user->value, $this->notification_type]);
			if(!$notification->id_user_notification){
				$notification = new Crunchbutton_User_Notification;
				$notification->id_user = $user->id_user;
				$notification->value = $user->value;
				$notification->type = $this->notification_type;
				$notification->active = 1;
				$notification->save();
			}
			Crunchbutton_Community_Notification_User::create(['id_user_notification'=>$notification->id_user_notification, 'id_community_notification'=>$this->id_community_notification]);
		} else {
			Crunchbutton_Community_Notification_User::create(['id_user_notification'=>$user->id_user_notification, 'id_community_notification'=>$this->id_community_notification]);
		}
	}

	public function community(){
		if(!$this->_community){
			$this->_community = Community::o($this->id_community);
		}
		return $this->_community;
	}

	public function updateStatus($status, $status_message=''){
		$this->status = $status;
		if($message!=''){
			$this->status_message = $status_message;
		}
		$this->status_update = date('Y-m-d H:i:s');
		$this->save();
		return $this;
	}

	public static function notifyCommunityWhenIsOpenedByDriver(){
		$config = self::openByDriverNotifyConfig();
		if(	$config[self::CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY] &&
			trim($config[self::CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY_MSG]) &&
			$config[self::CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY_DAYS] &&
			(	$config[self::CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY_EMAIL] ||
				$config[self::CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY_PUSH] ||
				$config[self::CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY_SMS] )
			){
			return true;
		}
		return false;
	}

	public static function openByDriverNotifyConfig(){
		$keys = [
			self::CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY,
			self::CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY_EMAIL,
			self::CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY_PUSH,
			self::CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY_SMS,
			self::CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY_MSG,
			self::CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY_DAYS
		];
		$out = [];
		foreach($keys as $key){
			$val = Crunchbutton_Config::getVal($key);
			if($key != self::CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY_MSG && $key != self::CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY_DAYS){
				if(intval($val) == 1){
					$val = true;
				} else {
					$val = false;
				}
			}
			if($key == self::CONFIG_KEY_OPEN_BY_DRIVER_NOTIFY_DAYS){
				$val = intval($val);
			}
			$out[$key] = $val;
		}
		return $out;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('community_notification')
			->idVar('id_community_notification')
			->load($id);
	}
}