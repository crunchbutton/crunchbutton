<?php

class Crunchbutton_Community_Notification_User extends Cana_Table {

	const STATUS_NEW = 'new';
	const STATUS_RUNNING = 'running';
	const STATUS_ERROR = 'error';
	const STATUS_FINISHED = 'finished';

	public static function create($params = []){
		$notification_user = new Crunchbutton_Community_Notification_User;
		$notification_user->id_community_notification = $params['id_community_notification'];
		$notification_user->id_user_notification = $params['id_user_notification'];
		$notification_user->status = self::STATUS_NEW;
		$notification_user->status_update = date('Y-m-d H:i:s');
		$notification_user->save();
		return $notification_user;
	}

	public function send($message){
		$this->updateStatus(self::STATUS_RUNNING);
		$notification = $this->notification();
		$res = $notification->send(['type'=>Crunchbutton_User_Notification::DATA_TYPE_TEXT, 'content'=>$message]);
		if($res){
			$this->updateStatus(self::STATUS_FINISHED);
		} else {
			$this->updateStatus(self::STATUS_ERROR);
		}
	}

	public function user_notification(){
		return $this->notification();
	}

	public function notification(){
		if(!$this->_notification_user){
			$this->_notification_user = Crunchbutton_User_Notification::o($this->id_user_notification);
		}
		return $this->_notification_user;
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

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('community_notification_user')
			->idVar('id_community_notification_user')
			->load($id);
	}
}