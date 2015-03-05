<?php

class Crunchbutton_Support extends Cana_Table_Trackchange {

	const TYPE_SMS = 'SMS';
	const TYPE_BOX_NEED_HELP = 'BOX_NEED_HELP';
	const TYPE_WARNING = 'WARNING';
	const TYPE_TICKET = 'TICKET';
	const TYPE_COCKPIT_CHAT = 'COCKPIT_CHAT';

	const STATUS_OPEN = 'open';
	const STATUS_CLOSED = 'closed';

	const CUSTOM_SERVICE_GROUP_NAME_KEY = 'custom-service-group-name';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('support')
			->idVar('id_support')
			->load($id);
		if(!$id) {
			date_default_timezone_set('UTC'); // always save in utc
			$this->datetime = date('Y-m-d H:i:s e');
			$this->status = 'open';
	  }
	}

	public function tellCustomerService( $message ){

		Crunchbutton_Message_Sms::send([
			'to' => Crunchbutton_Support::getUsers(),
			'from' => 'customer',
			'message' => $message,
			'reason' => Crunchbutton_Message_Sms::REASON_SUPPORT
		]);


		Log::debug( [ 'to' => Crunchbutton_Support::getUsers(), 'message' => $message, 'type' => 'support-tell-cs' ] );

	}

	// @todo: remove the getusers function in favor of getsupport
	public static function getSupport() {
		return Admin::q('
			select a.* FROM admin a
			left join admin_group ag using(id_admin)
			left join `group` g using(id_group)
			where g.name="'.Config::getVal( Crunchbutton_Support::CUSTOM_SERVICE_GROUP_NAME_KEY ).'"
		');
	}

	public function getUsers( $forceAll = false ){
		$support = array();
		$group = Crunchbutton_Group::byName( Config::getVal( Crunchbutton_Support::CUSTOM_SERVICE_GROUP_NAME_KEY ) );
		if( $group->id_group ){
			$users = Crunchbutton_Admin::q( "SELECT a.* FROM admin a INNER JOIN admin_group ag ON ag.id_admin = a.id_admin AND ag.id_group = {$group->id_group}" );
			if ( $users->count() > 0 ) {
				foreach ( $users as $user ) {
					// Check all the places where we send text message to CS and send it just to drivers that are current working.
					if( $user->name && $user->txt ){
						if( $forceAll || $user->isWorking() ){
							$support[ $user->name ] = $user->txt;
						}
					}
				}
			}
		}
		// no users, return a empty array
		return $support;
	}

	public function lastReplyFrom(){
		return $this->lastMessage()->from;
	}

	public function firstMessage(){
		return Crunchbutton_Support_Message::q( 'SELECT * FROM support_message WHERE id_support = ' . $this->id_support . ' ORDER BY date ASC, id_support_message ASC LIMIT 1 ' );
	}

	public function lastMessage(){
		return Crunchbutton_Support_Message::q( 'SELECT * FROM support_message WHERE id_support = ' . $this->id_support . ' ORDER BY date DESC, id_support_message DESC LIMIT 1 ' );
	}

	public function lastAdminSysMessage(){
		return Crunchbutton_Support_Message::q( 'SELECT * FROM support_message WHERE id_support = ' . $this->id_support . ' AND ( `from` = "' . Crunchbutton_Support_Message::TYPE_FROM_REP . '" OR `from` = "' . Crunchbutton_Support_Message::TYPE_FROM_SYSTEM . '"  ) ORDER BY date DESC, id_support_message DESC LIMIT 1 ' );
	}

	public function lastAdminMessage(){
		return Crunchbutton_Support_Message::q( 'SELECT * FROM support_message WHERE id_support = ' . $this->id_support . ' AND `from` = "' . Crunchbutton_Support_Message::TYPE_FROM_REP . '" ORDER BY date DESC, id_support_message DESC LIMIT 1 ' );
	}

	public function lastValidOpenMessage(){
		return Crunchbutton_Support_Message::q( 'SELECT * FROM support_message WHERE id_support = ' . $this->id_support . ' AND ( ( `from` = "' . Crunchbutton_Support_Message::TYPE_FROM_CLIENT . '" ) OR `from` = "' . Crunchbutton_Support_Message::TYPE_FROM_SYSTEM . '" AND type = "' . Crunchbutton_Support_Message::TYPE_NOTE . '" AND visibility = "' . Crunchbutton_Support_Message::TYPE_VISIBILITY_INTERNAL . '" )  ORDER BY date DESC, id_support_message DESC LIMIT 1 ' );
	}

	public function lastCustomerMessage(){
		return Crunchbutton_Support_Message::q( 'SELECT * FROM support_message WHERE id_support = ' . $this->id_support . ' AND `from` = "' . Crunchbutton_Support_Message::TYPE_FROM_CLIENT . '" ORDER BY date DESC, id_support_message DESC LIMIT 1 ' );
	}

	public function addCustomerNameByPhone( $phone, $name ){
		if( trim( $name ) != '' ){
			$id_support = false;
			$messages = Crunchbutton_Support_Message::byPhone( $phone );
			foreach( $messages as $message ){
				$message->name = $name;
				$message->save();
				$id_support = $message->id_support;
			}
		}
		$support = Crunchbutton_Support::o( $id_support );
		$support->addSystemMessage( c::admin()->name . ' changed the customer name to ' . $name );
		return true;
	}

	public function name(){
		$user = User::o( $this->id_user );
		if( $user->name ){
			return $user->name;
		}
		$message = $this->firstMessage();
		if( $message->name ){
			return $message->name;
		}
		return '<i>No name</i>';
	}

	public function message(){
		$message = $this->firstMessage();
		return $message->body;
	}

	public function clearPhone( $phone ){
		$phone = str_replace( ' ' , '', $phone );
		$phone = str_replace( '-' , '', $phone );
		$phone = str_replace( '(' , '', $phone );
		$phone = str_replace( ')' , '', $phone );
		return $phone;
	}
		public function phone(){
		$phone = $this->phone;
		$phone = preg_replace('/[^\d]*/i','',$phone);
		$phone = preg_replace('/(\d{3})(\d{3})(.*)/', '\\1-\\2-\\3', $phone);
		return $phone;
	}

	public function createNewChat( $params ){
		// try to get an existing session
		$twilio_session = Session_Twilio::sessionByPhone( $params[ 'From' ] );

		$phone = Crunchbutton_Support::clearPhone( $params[ 'From' ] );

		if( !$twilio_session->id_session_twilio ){

			// Create new session
			$session = new Crunchbutton_Session_Adapter();
			$fakeSessionId = substr( str_replace( '.' , '', uniqid( rand(), true ) ), 0, 32 );
			$session->write( $fakeSessionId );
			$session->save();

			// Create a new session twilio
			$twilio_session = new Crunchbutton_Session_Twilio;
			$twilio_session->id_session = $session->id_session;
			$twilio_session->data = json_encode( $params );
			$twilio_session->phone = $phone;
			$twilio_session->save();
		}

		// Get the current support ticket
		$support = Support::getByTwilioSessionId( $twilio_session->id_session_twilio );
		$createNewTicket = false;
		// if a user send a new message a day later, make sure it creates a new issue - #2453
		if( $support->id_support ){
			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
			$support_date = $support->date()->get(0);
			if( $support_date ){
				$interval = $now->diff( $support_date );
				$seconds = ( $interval->s ) + ( $interval->i * 60 ) + ( $interval->h * 60 * 60 ) + ( $interval->d * 60 * 60 * 24 ) + ( $interval->m * 60 * 60 * 24 * 30 ) + ( $interval->y * 60 * 60 * 24 * 365 );
				// This ticket is too old - create a new one
				if( $seconds >= 86400 ){
					$createNewTicket = true;
				}
			} else {
				$createNewTicket = true;
			}
		} else {
			$createNewTicket = true;
		}

		if( $createNewTicket ) {
			// Create a new sms ticket
			$support = Crunchbutton_Support::createNewSMSTicket(  [ 'phone' => $phone,
																															'name' => $params[ 'Name' ],
																															'body' => '(Ticket created at cockpit)',
																															'id_session_twilio' => $twilio_session->id_session_twilio ] );
		} else {
			if( $support->status == Crunchbutton_Support::STATUS_CLOSED ){
				$support->status = Crunchbutton_Support::STATUS_OPEN;
				$support->addSystemMessage( 'Ticket reopened at cockpit' );
			} else {
				$support->addSystemMessage( 'Chat started by fake sms' );
			}
		}
		$support->addAdminReply( $params[ 'Body' ] );
		return $support;
	}

	public function createNewSMSTicket( $params = [] ){
		$messageParams = [];
		$support = new Crunchbutton_Support();
		$support->type = Crunchbutton_Support::TYPE_SMS;
		$support->phone = $params[ 'phone' ];
		$support->status = Crunchbutton_Support::STATUS_OPEN;
		$support->ip = $_SERVER[ 'REMOTE_ADDR' ];
		$support->id_session_twilio = $params[ 'id_session_twilio' ];
		$support->date = date( 'Y-m-d H:i:s' );
		if( $params[ 'id_order' ] ) {
			$order = Order::o( $params[ 'id_order' ] );
			$support->id_order = $order->id_order;
			$support->id_restaurant = $order->id_restaurant;
			$support->id_user = $order->id_user;
			$messageParams[ 'name' ] = $order->name;
		}
		if( trim( $messageParams[ 'name' ] ) == '' && trim( $params[ 'name' ] ) != '' ){
			$messageParams[ 'name' ] = $params[ 'name' ];
		}
		$support->save();
		// Params to create the new Support message
		$messageParams[ 'phone' ] = $params[ 'phone' ];
		$messageParams[ 'body' ] = $params[ 'body' ];
		$messageParams['media'] = $params['media'];
		$support->addCustomerMessage( $messageParams );
		return Crunchbutton_Support::o( $support->id_support );
	}

	public function createNewBoxTicket( $params = [] ){
		$messageParams = [];
		$support = new Crunchbutton_Support();
		$support->type = Crunchbutton_Support::TYPE_BOX_NEED_HELP;
		$support->phone = $params[ 'phone' ];
		$support->status = Crunchbutton_Support::STATUS_OPEN;
		$support->ip = $_SERVER[ 'REMOTE_ADDR' ];
		$support->id_session_twilio = $params[ 'id_session_twilio' ];
		$support->date = date( 'Y-m-d H:i:s' );
		if( c::user()->id_user ){
			$support->id_user = c::user()->id_user;
		}
		$support->save();
		// Params to create the new Support message
		$messageParams[ 'name' ] = $params[ 'name' ];
		$messageParams[ 'phone' ] = $params[ 'phone' ];
		$messageParams[ 'body' ] = $params[ 'body' ];
		$support->addCustomerMessage( $messageParams );
		return Crunchbutton_Support::o( $support->id_support );
	}

	public function createNewTicket( $params = [] ){
		$support = new Crunchbutton_Support();
		$support->type = Crunchbutton_Support::TYPE_TICKET;
		$support->status = Crunchbutton_Support::STATUS_OPEN;
		$support->id_session_twilio = null;
		$support->id_admin = c::admin()->id_admin;
		$support->date = date( 'Y-m-d H:i:s' );
		if( $params[ 'id_order' ] ) {
			$order = Order::o( $params[ 'id_order' ] );
			$support->id_order = $order->id_order;
			$support->id_restaurant = $order->id_restaurant;
			$support->id_user = $order->id_user;
			$support->phone = $order->phone;
		}
		$support->save();

		if( $params[ 'id_order' ] ) {
			$order = Order::o( $params[ 'id_order' ] );
			$messageParams[ 'name' ] = $order->name;
			$messageParams[ 'phone' ] = $order->phone;
			$messageParams[ 'body' ] = $params[ 'body' ];
			$support->addCustomerMessage( $messageParams );
		}

		$support->addSystemMessage( $params[ 'body' ] );
		return Crunchbutton_Support::o( $support->id_support );
	}

	public function createNewWarning( $params = [] ){
		$support = false;
		if( $params[ 'id_order' ] ){
			$support = Crunchbutton_Support::q( 'SELECT * FROM support WHERE id_order = ' . $params[ 'id_order' ] . ' AND type = "' . Crunchbutton_Support::TYPE_WARNING . '" ORDER BY id_support DESC LIMIT 1' );
		}

		if( !$support->id_support && $params[ 'phone' ] ){
			$support = Crunchbutton_Support::q( 'SELECT * FROM support WHERE phone = "' . $params[ 'phone' ] . '" ORDER BY id_support DESC LIMIT 1' );
		}

		if( $support && $support->id_support ){
			$open = isset( $params[ 'dont_open_ticket' ] ) ? false : true;
			if( $open ){
				$support->status = Crunchbutton_Support::STATUS_OPEN;
			}
		} else {
			$support = new Crunchbutton_Support();
			$support->type = Crunchbutton_Support::TYPE_WARNING;
			$support->status = Crunchbutton_Support::STATUS_OPEN;
			$support->ip = $_SERVER[ 'REMOTE_ADDR' ];
			$support->id_session_twilio = null;
			$support->date = date( 'Y-m-d H:i:s' );
			if( $params[ 'id_order' ] ) {
				$order = Order::o( $params[ 'id_order' ] );
				$support->id_order = $order->id_order;
				$support->id_restaurant = $order->id_restaurant;
				$support->id_user = $order->id_user;
				$support->phone = $order->phone;
			} else {
				if( $params[ 'phone' ] ){
					$support->phone = $params[ 'phone' ];
				}
			}
		}
		$support->save();
		$support->addSystemMessage( $params[ 'body' ] );
		return Crunchbutton_Support::o( $support->id_support );
	}

	public function addCustomerMessage( $params = [] ){
		$messageParams[ 'id_admin' ] = NULL;
		$messageParams[ 'type' ] = Crunchbutton_Support_Message::TYPE_SMS;
		$messageParams[ 'from' ] = Crunchbutton_Support_Message::TYPE_FROM_CLIENT;
		$messageParams[ 'visibility' ] = Crunchbutton_Support_Message::TYPE_VISIBILITY_EXTERNAL;
		$messageParams[ 'name' ] = $params[ 'name' ];
		$messageParams[ 'phone' ] = $params[ 'phone' ];
		$messageParams[ 'body' ] = $params[ 'body' ];
		$messageParams[ 'media' ] = $params[ 'media' ];
		$this->addMessage( $messageParams );
	}

	public function addAdminReply($body, $guid = null){
		$body = trim($body);
		if ($body) {
			$admin = Crunchbutton_Admin::o( c::admin()->id_admin );
			$message = $this->addAdminMessage([
				'body' => $body,
				'phone' => $admin->phone,
				'id_admin' => c::admin()->id_admin,
				'guid' => $guid
			]);
			if( $message->id_support_message ){
				$message->notify();
			}
			return $message;
		}
	}

	public function byPhone( $phone ){
		$clean_phone = str_replace( '-', '', $phone );
		return Crunchbutton_Support::q( "SELECT * FROM support s WHERE s.phone = '{$phone}' OR REPLACE( REPLACE( s.phone, ' ', '' ), '-', '' ) = '{$clean_phone}' ORDER BY id_support ASC " );
	}

	public function addAdminMessage( $params = [] ){
		$hasAdmin = false;
		if( $params[ 'id_admin' ] ){
			$admin = Crunchbutton_Admin::o( $params[ 'id_admin' ] );
			if( $admin->id_admin ){
				$hasAdmin = true;
			}
		}
		if( !$hasAdmin ){
			$admin = Crunchbutton_Admin::getByPhone( $params[ 'phone' ] );
		}
		if( $admin->id_admin ){
			$messageParams[ 'id_admin' ] = $admin->id_admin;
			$messageParams[ 'type' ] = Crunchbutton_Support_Message::TYPE_SMS;
			$messageParams[ 'from' ] = Crunchbutton_Support_Message::TYPE_FROM_REP;
			$messageParams[ 'visibility' ] = Crunchbutton_Support_Message::TYPE_VISIBILITY_EXTERNAL;
			$messageParams[ 'name' ] = $admin->firstName();
			$messageParams[ 'phone' ] = $params[ 'phone' ];
			$messageParams[ 'body' ] = $params[ 'body' ];
			$messageParams[ 'guid' ] = $params[ 'guid' ];
			$messageParams[ 'media' ] = $params[ 'media' ];
			return $this->addMessage( $messageParams );
		}
	}

	public function pendingSupport(){
		Crunchbutton_Support::closeTicketsOlderThanADay();
		return Crunchbutton_Support::q("SELECT s.* FROM support s
										INNER JOIN ( SELECT MAX( id_support_message ), id_support, `from` FROM support_message GROUP BY id_support ORDER BY id_support DESC ) sm ON s.id_support = sm.id_support
										AND s.status = '" . Crunchbutton_Support::STATUS_OPEN . "' AND sm.from = '" . Crunchbutton_Support_Message::TYPE_FROM_CLIENT . "'");
	}

	// close all support issues that are older than a day #2487
	public function closeTicketsOlderThanADay(){
		$supports = Crunchbutton_Support::q( 'SELECT s.* FROM support s WHERE s.status =  "' . Crunchbutton_Support::STATUS_OPEN . '" ORDER BY id_support ASC' );
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		foreach ( $supports as $support ) {
			$lastCustomerMessage = $support->lastValidOpenMessage();
			$close = false;
			if( $lastCustomerMessage->id_support_message ){
				$support_date = $lastCustomerMessage->date()->get(0);
				if( $support_date ){
					$interval = $now->diff( $support_date );
					$seconds = ( $interval->s ) + ( $interval->i * 60 ) + ( $interval->h * 60 * 60 ) + ( $interval->d * 60 * 60 * 24 ) + ( $interval->m * 60 * 60 * 24 * 30 ) + ( $interval->y * 60 * 60 * 24 * 365 );
					if( $seconds >= 86400 ){
						$close = true;
					}
				} else {
					$close = true;
				}
			} else {
				$support_date = $support->date();
				if( $support_date ){
					$interval = $now->diff( $support_date );
					$seconds = ( $interval->s ) + ( $interval->i * 60 ) + ( $interval->h * 60 * 60 ) + ( $interval->d * 60 * 60 * 24 ) + ( $interval->m * 60 * 60 * 24 * 30 ) + ( $interval->y * 60 * 60 * 24 * 365 );
					if( $seconds >= 86400 ){
						$close = true;
					}
				} else {
					$close = true;
				}
			}
			if( $close ){
				$support->status = Crunchbutton_Support::STATUS_CLOSED;
				$support->save();
				$support->addSystemMessage( 'Automatically close support ticket older than a day' );
			}
		}
	}

	public function addSystemMessage( $body ) {
		$messageParams[ 'id_admin' ] = null;
		$messageParams[ 'type' ] = Crunchbutton_Support_Message::TYPE_NOTE;
		$messageParams[ 'from' ] = Crunchbutton_Support_Message::TYPE_FROM_SYSTEM;
		$messageParams[ 'visibility' ] = Crunchbutton_Support_Message::TYPE_VISIBILITY_INTERNAL;
		$messageParams[ 'body' ] = $body;
		$this->addMessage( $messageParams );
	}

	public function addNote( $body ){
		if( trim( $body ) != '' ){
			$admin = Crunchbutton_Admin::o( c::admin()->id_admin );
			$messageParams[ 'id_admin' ] = $admin->id_admin;
			$messageParams[ 'type' ] = Crunchbutton_Support_Message::TYPE_NOTE;
			$messageParams[ 'from' ] = Crunchbutton_Support_Message::TYPE_FROM_REP;
			$messageParams[ 'visibility' ] = Crunchbutton_Support_Message::TYPE_VISIBILITY_INTERNAL;
			$messageParams[ 'name' ] = $admin->firstName();
			$messageParams[ 'phone' ] = $params[ 'phone' ];
			$messageParams[ 'body' ] = $body;
			$message = $this->addMessage( $messageParams );
			return $message;
		}
	}

	public function addMessage( $params = [] ){
		$message = new Crunchbutton_Support_Message();
		$message->id_support = $this->id_support;
		$message->id_admin = $params[ 'id_admin' ];
		$message->from = $params[ 'from' ];
		$message->type = $params[ 'type' ];
		$message->visibility = $params[ 'visibility' ];
		$message->phone = $params[ 'phone' ];
		$message->guid = $params[ 'guid' ];
		$message->name = $params[ 'name' ];
		$message->body = $params[ 'body' ];
		$message->media = $params[ 'media' ];
		$today = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$message->date = $today->format( 'Y-m-d H:i:s' );
		$message->save();

		// Relate the admin with the message
		if( $message->id_admin ){
			$this->id_admin = $message->id_admin;
			$this->save();
		}

		return $message;
	}

	public function queNotify() {
		$support = $this;
		c::timeout(function() use($support) {
			$support->notify();
		});
	}

	public function messages(){
		return Crunchbutton_Support_Message::q( "SELECT * FROM support_message WHERE id_support = {$this->id_support} ORDER BY date ASC, id_support_message ASC" );
	}

	public function getByTwilioSessionId( $id_session_twilio ){
		return Crunchbutton_Support::q( 'SELECT * FROM support WHERE id_session_twilio = ' . $id_session_twilio . ' ORDER BY id_support DESC LIMIT 1' );
	}

	public function sameTwilioSession(){
		return $this->getAllByTwilioSessionId( $this->id_session_twilio, $this->id_support );
	}

	public function getAllByTwilioSessionId( $id_session_twilio, $id_support = false ){
		$where = '';
		if( $id_support ){
			$where = ' AND id_support != ' . $id_support;
		}
		return Crunchbutton_Support::q( 'SELECT * FROM support WHERE id_session_twilio = ' . $id_session_twilio . $where . ' ORDER BY id_support DESC' );
	}

	public function notify() {

		$support = $this;

		$env = c::getEnv();
		$message = $this->firstMessage();

		$message = "(support-" . $env . "): ".
			$support->name.
			"\n\n".
			"phone: ".
			$support->phone.
			"\n\n".
			$message->body;

		// Log
		$message = '@'.$this->id_session_twilio.' : ' . $message;

		Crunchbutton_Message_Sms::send([
			'to' => Crunchbutton_Support::getUsers(),
			'message' => $message,
			'reason' => Crunchbutton_Message_Sms::REASON_SUPPORT
		]);

		$this->makeACall();

	}

	public static function find($search = []) {
		$query = 'SELECT `support`.* FROM `support` WHERE id_support IS NOT NULL ';

		if ($search['type']) {
			$query .= " and type='".$search['type']."' ";
		}

		if ($search['status']) {
			$query .= " and status='".$search['status']."' ";
		}

		if ($search['start']) {
			$s = new DateTime($search['start']);
			$query .= " and DATE(`date`)>='".$s->format('Y-m-d')."' ";
		}

		if ($search['end']) {
			$s = new DateTime($search['end']);
			$query .= " and DATE(`date`)<='".$s->format('Y-m-d')."' ";
		}

		if ($search['search']) {
			$qn =  '';
			$q = '';
			$searches = explode(' ',$search['search']);
			foreach ($searches as $word) {
				if ($word{0} == '-') {
					$qn .= ' and `support`.name not like "%'.substr($word,1).'%" ';
					$qn .= ' and `support`.message not like "%'.substr($word,1).'%" ';
				} else {
					$q .= '
						and (`support`.name like "%'.$word.'%"
						or `support`.message like "%'.$word.'%")
					';
				}
			}
			$query .= $q.$qn;
		}

		$query .= 'ORDER BY `date` DESC';

		if ($search['limit']) {
			$query .= ' limit '.$search['limit'].' ';
		}

		$supports = self::q($query);
		return $supports;
	}

	public function user() {
		return User::o($this->id_user);
	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->datetime, new DateTimeZone( 'UTC' ));
			$this->_date->setTimezone( new DateTimeZone( c::config()->timezone ) );
		}
		return $this->_date;
	}

	public function relativeTime() {
		return Crunchbutton_Util::relativeTime($this->datetime);
	}

	public function rep() {
		return Crunchbutton_Admin::o($this->id_admin);
	}

	public function save() {
		$initial_save = false;
		if(!Support::o($this->id_support)->id_support) {
			$initial_save = true;
		}

		$this->phone = Phone::clean($this->phone);

		parent::save();
		if($initial_save) {
			// Crunchbutton_Hipchat_Notification::NewSupport($this);
		}
	}

	public function makeACall(){

		$dateTime = new DateTime( 'now', new DateTimeZone('America/Los_Angeles'));
		$hour = $dateTime->format( 'H' );

		// Issue #1100 - Call David if CB receives a support after 1AM
		if( $hour >= 1 && $hour <= 7 ){

			$env = c::getEnv();

			$twilio = new Services_Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);

			$id_support = $this->id_support;

			$url = 'http://' . c::config()->host_callback . '/api/support/say/' . $id_support;

			Log::debug( [ 'action' => 'Need to call', 'id_support' => $id_support, 'url' => $url, 'hour' => $hour, 'type' => 'sms' ] );

			$nums = c::config()->site->config('support-phone-afterhours')->val();
			if (!is_array($nums)) {
				$nums = [$nums];
			}

			foreach ($nums as $supportName => $supportPhone ) {
				$num = $supportPhone;
				$name = $supportName;
				$urlWithName = $url . '/' . $name;
				$call = $twilio->account->calls->create(
						c::config()->twilio->{$env}->outgoingRestaurant,
						'+1'.$num,
						$urlWithName
				);
				Log::debug( [ 'action' => 'Calling', 'num' => $num, 'url' => $urlWithName, 'type' => 'sms' ] );
			}
		}

		Log::debug( [ 'action' => 'Not need to call', 'id_support' => $id_support, 'hour' => $hour, 'type' => 'sms' ] );

	}

	public function setOrderId($id_order) {
		$order = Order::o($id_order);
		$this->id_order = $order->id_order;
		$this->id_restaurant = $order->id_restaurant;
		$this->id_user = $order->id_user;
		$this->name = $order->name;
	}

	public static function getSupportForOrder($id_order) {
		$s = self::q('SELECT * FROM `support` WHERE `id_order`="'.$id_order.'" ORDER BY `id_support` DESC LIMIT 1')->get(0);
		return $s->id ? $s : false;
	}

	public function repTime() {
		$date = $this->date();
		$date->setTimezone(c::admin()->timezone());
		return $date;
	}

	public function restaurant() {
		if (!isset($this->_restaurant)) {
			$this->_restaurant = $this->id_restaurant ? Restaurant::o($this->id_restaurant) : $this->order()->restaurant();
		}
		return $this->_restaurant;
	}

	public function order() {
		if (!isset($this->_order)) {
			$this->_order = Order::o($this->id_order);
		}
		return $this->_order;
	}

	public function permissionToEdit(){
		if( $this->id_restaurant ){
			$userHasPermission = c::admin()->permission()->check( ['global', 'support-all', 'support-crud' ] );
			if( !$userHasPermission ){
				$restaurants = c::admin()->getRestaurantsUserHasPermissionToSeeTheirTickets();
				foreach ( $restaurants as $id_restaurant ) {
					if( $id_restaurant == $this->id_restaurant ){
						$userHasPermission = true;
					}
				}
			}
		} else {
			$userHasPermission = c::admin()->permission()->check( ['global', 'support-all', 'support-crud', 'support-create' ] );
		}
		return $userHasPermission;
	}

	public function restaurantsUserHasPermissionToSeeTheirTickets(){
		$restaurants_ids = [];
		$_permissions = new Crunchbutton_Admin_Permission();
		$all = $_permissions->all();
		// Get all restaurants permissions
		$restaurant_permissions = $all[ 'support' ][ 'permissions' ];
		$permissions = c::admin()->getAllPermissionsName();
		$restaurants_id = array();
		foreach ( $permissions as $permission ) {
			$permission = $permission->permission;
			$info = $_permissions->getPermissionInfo( $permission );
			$name = $info[ 'permission' ];
			foreach( $restaurant_permissions as $restaurant_permission_name => $meta ){
				if( $restaurant_permission_name == $name ){
					if( strstr( $name, 'ID' ) ){
						$regex = str_replace( 'ID' , '((.)*)', $name );
						$regex = '/' . $regex . '/';
						preg_match( $regex, $permission, $matches );
						if( count( $matches ) > 0 ){
							$restaurants_ids[] = $matches[ 1 ];
						}
					}
				}
			}
		}
		return array_unique( $restaurants_ids );
	}

	public function adminHasCreatePermission(){
		$hasCreatePermission = c::admin()->permission()->check( ['global', 'support-all', 'support-crud', 'support-create' ] );
		if( !$hasCreatePermission ){
			$pattern = '/' . str_replace( 'ID' , '.*', 'support-crud-ID' ) . '/';
			$hasCreatePermission = c::admin()->hasPermission( $pattern, true );
			if( !$hasCreatePermission ){
				$groups = c::admin()->groups();
				if( $groups->count() ){
					foreach ( $groups as $group ) {
						if( $group->hasPermission( $pattern, true ) ){
							$hasCreatePermission = true;
						}
					}
				}
			}
		}
		return $hasCreatePermission;
	}

	public function exports() {

		$out = [];


		$out = $this->properties();
		$out['user'] = $this->user()->id_user ? $this->user()->exports() : null;
		$out['driver'] = ($this->order()->id_order && $this->order()->driver()->id_admin) ? $this->order()->driver()->exports() : null;
		$out['restaurant'] = $this->restaurant()->id_restaurant ? $this->restaurant()->exports() : null;
		$out['order'] = $this->order()->id_order ? $this->order()->exports() : null;

		// Export the comments
		$out[ 'comments' ] = [];
		$comments = $this->comments();
		foreach( $comments as $comment ){
			$out[ 'comments' ][] = $comment->exportsNote();
		}

		// Check if the ticket belongs to a driver with pexcard #3990
		$admin = Admin::getByPhone( $this->clearPhone( $this->phone ) );
		if( $admin->id_admin && $admin->isDriver() ){
			$pexcard = $admin->pexcard();
			if( $pexcard->id_admin_pexcard ){
				$out[ 'pexcard' ] = $pexcard->exports();
				$out[ 'pexcard' ][ 'name' ] = $admin->name;
			}
		}

		$messages = Crunchbutton_Support_Message::byPhone( $this->phone, $this->id_support );

		foreach ( $messages as $message ) {
			$out['messages'][] = $message->exports();
		}

		return $out;
	}

	public function comments(){
		return Crunchbutton_Support_Message::q( 'SELECT * FROM support_message sm WHERE sm.id_support = "' . $this->id_support . '" AND sm.type = "' . Crunchbutton_Support_Message::TYPE_NOTE . '" AND `from` != "' . Crunchbutton_Support_Message::TYPE_FROM_SYSTEM . '" ORDER BY sm.id_support_message DESC' );
	}

}
