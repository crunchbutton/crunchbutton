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

	public static function checkStatus(){
		// check here
		$actions = self::q('SELECT * FROM support_action save
												INNER JOIN(
													SELECT MAX(id_support_action) id_support_action, id_support FROM support_action GROUP BY id_support)
														max ON max.id_support_action = sa.id_support_action');
	}

	public static function createMessageReceived($id_support, $data){
		return self::create([	'id_support' => $id_support,
													'action' => self::ACTION_MESSAGE_RECEIVED,
													'data' => $data]);
	}

	public function support() {
		if( !$this->_support ){
			$this->_support = Support::o($this->id_support);
		}
		return $this->_support;
	}

	public function support_message() {
		if( !$this->_support_message ){
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
