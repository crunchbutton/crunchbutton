<?php

class Crunchbutton_Support extends Cana_Table_Trackchange {

	const TYPE_SMS = 'SMS';
	const TYPE_EMAIL = 'EMAIL';
	const TYPE_BOX_NEED_HELP = 'BOX_NEED_HELP';
	const TYPE_WARNING = 'WARNING';
	const TYPE_TICKET = 'TICKET';
	const TYPE_COCKPIT_CHAT = 'COCKPIT_CHAT';

	const CONFIG_AUTO_REPLY_KEY = 'auto-reply-text';

	const STATUS_OPEN = 'open';
	const STATUS_CLOSED = 'closed';

	const CUSTOM_SERVICE_GROUP_NAME_KEY = 'custom-service-group-name';

	const SUPPORT_EMAIL = 'support@_DOMAIN_';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('support')
			->idVar('id_support')
			->load($id);

		$this->datetime = date('Y-m-d H:i:s');
		if( !$this->id_support ){
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


	public static function getUsers( $forceAll = false ){

		$support = array();

		$group = Crunchbutton_Group::byName( Config::getVal( Crunchbutton_Support::CUSTOM_SERVICE_GROUP_NAME_KEY ) );

		if( $forceAll ){
			if( $group->id_group ){
				$users = Crunchbutton_Admin::q( "SELECT a.* FROM admin a INNER JOIN admin_group ag ON ag.id_admin = a.id_admin AND ag.id_group = ? AND a.active = 1", [$group->id_group]);
				if ( $users->count() > 0 ) {
					foreach ( $users as $user ) {
						if( $user->name && ( $user->phone || $user->txt ) ){
							$phone = ( $user->txt ) ? $user->txt : $user->phone;
							$support[ $user->name ] = $phone;
						}
					}
				}
			}
		} else {

		$now = new DateTime( 'now', new DateTimeZone( Crunchbutton_Community_Shift::CB_TIMEZONE ) );

		$users = Admin::q( 'SELECT DISTINCT(a.id_admin), a.* FROM admin a
														INNER JOIN admin_shift_assign asa ON asa.id_admin = a.id_admin
														INNER JOIN community_shift cs ON cs.id_community_shift = asa.id_community_shift
													WHERE cs.id_community = ? AND cs.date_start <= ? AND cs.date_end >= ? AND cs.active = true AND a.active = true ', [$group->id_community, $now->format( 'Y-m-d H:i:s' ), $now->format( 'Y-m-d H:i:s' )]);
				if ( $users->count() > 0 ) {
					foreach ( $users as $user ) {
						if( $user->name && ( $user->phone || $user->txt ) ){
							$phone = ( $user->txt ) ? $user->txt : $user->phone;
							$support[ $user->name ] = $phone;
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
		return Crunchbutton_Support_Message::q( 'SELECT * FROM support_message WHERE id_support = ? ORDER BY date ASC, id_support_message ASC LIMIT 1 ', [$this->id_support]);
	}

	public function lastMessage(){
		return Crunchbutton_Support_Message::q( 'SELECT * FROM support_message WHERE id_support = ? ORDER BY date DESC, id_support_message DESC LIMIT 1 ' , [$this->id_support]);
	}

	public function lastAdminSysMessage(){
		return Crunchbutton_Support_Message::q( 'SELECT * FROM support_message WHERE id_support = ? AND ( `from` = ? OR `from` = ?  ) ORDER BY date DESC, id_support_message DESC LIMIT 1 ' , [$this->id_support, Crunchbutton_Support_Message::TYPE_FROM_REP, Crunchbutton_Support_Message::TYPE_FROM_SYSTEM]);
	}

	public function lastAdminMessage(){
		return Crunchbutton_Support_Message::q( 'SELECT * FROM support_message WHERE id_support = ? AND `from` = ? ORDER BY date DESC, id_support_message DESC LIMIT 1 ' , [$this->id_support, Crunchbutton_Support_Message::TYPE_FROM_REP]);
	}

	public function lastNonSystemMessage(){
		return Crunchbutton_Support_Message::q( 'SELECT * FROM support_message WHERE id_support = ? AND `from` != ? ORDER BY date DESC, id_support_message DESC LIMIT 1 ' , [$this->id_support, Crunchbutton_Support_Message::TYPE_FROM_SYSTEM] );
	}

	public function lastValidOpenMessage(){
		return Crunchbutton_Support_Message::q( 'SELECT * FROM support_message WHERE id_support = ? AND ( ( `from` = ? ) OR `from` = ? AND type = ? AND visibility = ? )  ORDER BY date DESC, id_support_message DESC LIMIT 1 ' , [$this->id_support, Crunchbutton_Support_Message::TYPE_FROM_CLIENT, Crunchbutton_Support_Message::TYPE_FROM_SYSTEM, Crunchbutton_Support_Message::TYPE_NOTE, Crunchbutton_Support_Message::TYPE_VISIBILITY_INTERNAL] );
	}

	public function lastCustomerMessage(){
		return Crunchbutton_Support_Message::q( 'SELECT * FROM support_message WHERE id_support = ? AND `from` = ? ORDER BY date DESC, id_support_message DESC LIMIT 1 '  , [$this->id_support, Crunchbutton_Support_Message::TYPE_FROM_CLIENT]);
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
		return Phone::clean( $phone );
	}

	public function phone(){
		if( !$this->_phone ) {
			$phone = Phone::o( $this->id_phone );
			$phone = $phone->phone;
			$this->_phone = Phone::formatted( $phone );
		}
		return $this->_phone;
	}

	public function createNewChat( $params ){
		// try to get an existing session
		$twilio_session = Session_Twilio::sessionByPhone( $params[ 'From' ] );

		$phone = Crunchbutton_Support::clearPhone( $params[ 'From' ] );

		if( !$twilio_session->id_session_twilio ){

			// Create new session
			$session = new Crunchbutton_Session_Adapter_Sql();
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
			$message = ( $params[ 'chatMessage' ] ? $params[ 'chatMessage' ] : Crunchbutton_Support_Message::TICKET_CREATED_COCKPIT_BODY );
			$support = Crunchbutton_Support::createNewSMSTicket(  [ 'phone' => $phone,
																															'name' => $params[ 'Name' ],
																															'body' => $message,
																															'id_session_twilio' => $twilio_session->id_session_twilio ] );
		} else {
			if( $support->status == Crunchbutton_Support::STATUS_CLOSED ){
				if( !$params[ 'ignoreFistMessage' ] ){
					$support->addSystemMessage( 'Ticket reopened at cockpit' );
				}
			} else {
				if( !$params[ 'ignoreFistMessage' ] ){
					$support->addSystemMessage( 'Chat started by fake sms' );
				}
			}
			$support->status = Crunchbutton_Support::STATUS_OPEN;
			$support->save();
		}
		if( $params[ 'ignoreReply' ] ){
			// to prevent the support ticket to open
			$support->status = self::STATUS_CLOSED;
			$support->save();
			return $support;
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
		$support->ip = c::getIp();
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
		$support->ip = c::getIp();
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

	// Creates a ticket and related it to a driver
	public static function createNewWarningStaffTicket( $params = [] ){
		$params[ 'staff' ] = true;
		return self::createNewWarning( $params );
	}

	public static function createNewWarning( $params = [] ){
		$support = false;

		if( $params[ 'staff' ] && $params[ 'phone' ] ){
			$support = Crunchbutton_Support::q( 'SELECT * FROM support WHERE phone = ? AND type != ? ORDER BY id_support DESC LIMIT 1', [$params[ 'phone' ], Crunchbutton_Support::TYPE_WARNING]);
		} else {
			if( $params[ 'id_order' ] ){
				$support = Crunchbutton_Support::q( 'SELECT * FROM support WHERE id_order = ? AND type = ? ORDER BY id_support DESC LIMIT 1', [$params[ 'id_order' ], Crunchbutton_Support::TYPE_WARNING]);
			}

			if( !$support->id_support && $params[ 'phone' ] ){
				$support = Crunchbutton_Support::q( 'SELECT * FROM support WHERE phone = ? ORDER BY id_support DESC LIMIT 1', [$params[ 'phone' ]]);
			}
		}

		if( $support && $support->id_support ){
			$open = isset( $params[ 'dont_open_ticket' ] ) ? false : true;
			if( $open ){
				$support->status = Crunchbutton_Support::STATUS_OPEN;
			}
		} else {
			$support = new Crunchbutton_Support();
			if( $params[ 'staff' ] ){
				$support->type = Crunchbutton_Support::TYPE_TICKET;
			} else {
				$support->type = Crunchbutton_Support::TYPE_WARNING;
			}
			$open = isset( $params[ 'dont_open_ticket' ] ) ? false : true;
			if( $open ){
				$support->status = Crunchbutton_Support::STATUS_OPEN;
			} else {
				$support->status = Crunchbutton_Support::STATUS_CLOSED;
			}
			$support->ip = c::getIp();
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
		if( $params[ 'bubble' ] ){
			$support->addSystemMessage( $params[ 'body' ], true );
		} else {
			$support->addSystemMessage( $params[ 'body' ] );
		}
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

		// CS Auto-Reply #5042
		$support = $this;
		c::timeout( function() use( $support ) {
			$support->autoReply();
		}, 10 * 1000 );
	}

	public function autoReply(){

		$support = Crunchbutton_Support::o( $this->id_support );

		$body = $support->autoReplyMessage();

		if( $support->shoudAutoReply() && $body ){

			$messageParams[ 'type' ] = Crunchbutton_Support_Message::TYPE_AUTO_REPLY;
			$messageParams[ 'from' ] = Crunchbutton_Support_Message::TYPE_FROM_SYSTEM;
			$messageParams[ 'visibility' ] = Crunchbutton_Support_Message::TYPE_VISIBILITY_EXTERNAL;
			$messageParams[ 'phone' ] = $support->phone;
			$messageParams[ 'body' ] = $body;

			$message = $support->addMessage( $messageParams );

			Crunchbutton_Message_Sms::send([
				'to' => $message->phone,
				'message' => $message->body,
				'reason' => Crunchbutton_Message_Sms::REASON_AUTO_REPLY
			] );
			return true;
		}
	}

	public function autoReplyMessage(){
		$message = Crunchbutton_Config::q( 'SELECT * FROM config WHERE `key` = ? ORDER BY RAND() LIMIT 1', [Crunchbutton_Support::CONFIG_AUTO_REPLY_KEY]);
		if( $message->value ){
			return $message->value;
		}
		return false;
	}

	public function lastAutoReplyByPhone( $phone ){
		$query = 'SELECT sm.* FROM support s
							INNER JOIN support_message sm ON sm.id_support = s.id_support
							INNER JOIN phone p ON p.id_phone = s.id_phone
							WHERE p.phone = ?
							AND sm.type = ?
							ORDER BY id_support_message DESC LIMIT 1';
		$support_message = Crunchbutton_Support_Message::q( $query, [$phone, Crunchbutton_Support_Message::TYPE_AUTO_REPLY])->get( 0 );
		if( $support_message->id_support_message ){
			return $support_message;
		}
		return false;
	}

	public function shoudAutoReply(){
		return false;
		$last = $this->lastAutoReplyByPhone( $this->phone );
		if( !$last ){
			return true;
		} else {
			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
			if( Crunchbutton_Util::intervalMoreThan24Hours( $now->diff( $last->date() ) ) ){
				return true;
			}
		}
		return false;
	}

	public function lastSubject(){
		$lastMessage = $this->lastCustomerMessage();
		return $lastMessage->subject;
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
		return Crunchbutton_Support::q( "SELECT * FROM support s WHERE s.phone = ? OR REPLACE( REPLACE( s.phone, ' ', '' ), '-', '' ) = ? ORDER BY id_support ASC ", [$phone, $clean_phone]);
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
			$type = ( $this->type == Crunchbutton_Support::TYPE_EMAIL ) ? Crunchbutton_Support_Message::TYPE_EMAIL : Crunchbutton_Support_Message::TYPE_SMS;
			$messageParams[ 'id_admin' ] = $admin->id_admin;
			$messageParams[ 'type' ] = $type;
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
										AND s.status = ? AND sm.from = ?", [Crunchbutton_Support::STATUS_OPEN, Crunchbutton_Support_Message::TYPE_FROM_CLIENT]);
	}

	// close all support issues that are older than a day #2487
	public function closeTicketsOlderThanADay(){
		$supports = Crunchbutton_Support::q( 'SELECT s.* FROM support s WHERE s.status =  ? ORDER BY id_support ASC', [Crunchbutton_Support::STATUS_OPEN]);
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

	public function addSystemMessage( $body, $bubble = false ) {
		$messageParams[ 'id_admin' ] = null;
		if( $bubble ){
			$messageParams[ 'type' ] = Crunchbutton_Support_Message::TYPE_WARNING;
		} else {
			$messageParams[ 'type' ] = Crunchbutton_Support_Message::TYPE_NOTE;
		}
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
		$message->subject = $params[ 'subject' ];
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
		return Crunchbutton_Support_Message::q( "SELECT * FROM support_message WHERE id_support = ? ORDER BY date ASC, id_support_message ASC", [$this->id_support]);
	}

	public function getByTwilioSessionId( $id_session_twilio ){
		return Crunchbutton_Support::q( 'SELECT * FROM support WHERE id_session_twilio = ? ORDER BY id_support DESC LIMIT 1', [$id_session_twilio]);
	}

	public function sameTwilioSession(){
		return $this->getAllByTwilioSessionId( $this->id_session_twilio, $this->id_support );
	}

	public function getAllByTwilioSessionId( $id_session_twilio, $id_support = false ){
		$where = '';
		$keys = [$id_session_twilio];
		if( $id_support ){
			$where = ' AND id_support != ?';
			$keys[] = $id_support;
		}
		return Crunchbutton_Support::q( 'SELECT * FROM support WHERE id_session_twilio = ? ' . $where . ' ORDER BY id_support DESC' ,$keys);
	}

	public function notify( $firstMessage = true ) {

		$support = $this;

		$env = c::getEnv();
		if( $firstMessage ){
			$message = $this->firstMessage();
		} else {
			$message = $this->lastMessage();
		}

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

	public function save($new = false) {
		$initial_save = false;
		if(!Support::o($this->id_support)->id_support) {
			$initial_save = true;
			if( !$this->status ){
				$this->status = 'open';
			}
		}

		$this->phone = Phone::clean($this->phone);

		$phone = Phone::byPhone( $this->phone );
		$this->id_phone = $phone->id_phone;

		parent::save();
		if($initial_save) {
			// Crunchbutton_Hipchat_Notification::NewSupport($this);
		}
	}

	public function makeACall(){

		$dateTime = new DateTime( 'now', new DateTimeZone( Crunchbutton_Community_Shift::CB_TIMEZONE ));
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
		$s = self::q('SELECT * FROM `support` WHERE `id_order`=? ORDER BY `id_support` DESC LIMIT 1', [$id_order])->get(0);
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

	public function exports($params = []) {

		$out = $this->properties();

		if ($this->id_user) {
			$user = $this->user();
		} elseif ($this->id_phone) {
			$user = User::q( 'SELECT * FROM user WHERE id_phone = ? ORDER BY id_user DESC LIMIT 1', [ $this->id_phone ] )->get( 0 );
		}
		if ($user->id_user) {
			$out['user'] = $user->properties();
		}

		if ($this->id_order) {
			$order = $this->order();
		} elseif($user) {
			$order = Order::q( 'SELECT * FROM `order` WHERE id_user = ? ORDER BY id_order DESC LIMIT 1', [ $out['user'][ 'id_user' ] ] )->get( 0 );
		}

		if ($order->id_order) {

			$campus_card_charged = $order->campus_cash_charged();
			if( $campus_card_charged ){
				$out['order'][ 'campus_cash_charged' ] = true;
				$out['order'][ 'campus_cash_charged_info' ] = $campus_card_charged;
			} else {
				$out['order'][ 'campus_cash_charged' ] = false;
				$paymentType = $order->paymentType();
				$out['order'][ 'campus_cash_sha1' ] = $paymentType->stripe_id;
			}


			//$out['order'] = $order->properties();
			$out['order'] = $order->exports();

			if( !$out['order']['geomatched'] && $out['order']['id_address'] ){
				$address = Crunchbutton_Address::o( $out['order']['id_address'] );
				$_address = $address->exports();
				$_address[ 'approved' ] = ( $_address['status'] == Address::STATUS_APPROVED );
				$out['order']['_address'] = $_address;
			}

			$driver = $order->driver();
			if( $driver ){
				$driver = $driver->get( 0 );
				if( $driver->id_admin ){
					$out['driver'] = $driver->exports( [ 'last-checkins' => true ]);
				}
			}
			$out['restaurant'] = $order->restaurant()->properties();
		}

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

		if ($order && $order->driver() && $order->driver()->id_admin) {
			$note = $order->driver()->get(0)->lastNote();
			if( $note->id_admin_note ){
				$out['driver'][ 'note' ] = $note->exports();
			}
		}

		if( $admin->id_admin ){
			$out[ 'staff' ] = $admin->exports(  [ 'last-checkins' => true ]  );
			$out[ 'staff' ][ 'is_driver' ] = $admin->isDriver();
			$out[ 'staff' ][ 'is_marketing_rep' ] = $admin->isMarketingRep();
			$out[ 'staff' ][ 'is_campus_manager' ] = $admin->isCampusManager();
			$out[ 'staff' ][ 'is_support' ] = $admin->isSupport();
			$out[ 'staff' ][ 'is_working' ] = $admin->isWorking();
			// Check if the driver is delivering any order
			if( $admin->isDriver() ){
				$out[ 'staff' ][ 'delivering' ] = $admin->publicExports();
			}
			$note = $admin->lastNote();
			if( $note ){
				$out[ 'staff' ][ 'note' ] = $note->exports();
			}
			$communities = $admin->communitiesHeDeliveriesFor();
			if( $communities ){
				$out[ 'staff' ][ 'communities' ] = [];
				foreach( $communities as $community ){
					$id_community = $community->id_community;
					$out[ 'staff' ][ 'communities' ][] = $community->name;
				}
			}
		}

		if ($out['restaurant'] || $out['order'] || $out['staff']) {
			if ($out['order']) {
				$community = Community::o($out['order']['id_community']);

			} elseif( $out['restaurant'] ){
				$community = Community::q('select c.* from community c left join restaurant_community rc where rc.id_restaurant=? limit 1', [$out['restaurant']->id_restaurant])->get(0);

			} elseif( $out[ 'staff' ] ){
				$community = Community::o( $id_community );
			}

			if ($community->id_community) {
				$out[ 'community' ] = [];
				$out[ 'community' ][ 'id_community' ] = $community->id_community;
				$out[ 'community' ][ 'name' ] = $community->name;
				$out[ 'community' ][ 'permalink' ] = $community->permalink;
				$note = $community->lastNote();
				if ($note) {
					$out[ 'community' ][ 'note' ] = $note->exports();
				}
			}
		}

		$load_messages = true;

		if( $params[ 'exclude' ] && $params[ 'exclude' ][ 'messages' ] ){
			$load_messages = false;
		}
		if( $load_messages ){
			$out = array_merge( $out, $this->exportMessages() );
		}

		return $out;
	}

	public function exportMessages( $params = [] ){
		$out = [];

		if( $this->type == Crunchbutton_Support::TYPE_WARNING && !$this->id_user ){
			$messages = $this->messages();
			$out[ 'total_messages' ] = $messages->count();
		} else if ( $this->type == Crunchbutton_Support::TYPE_EMAIL ) {
			$out[ 'type' ] = Crunchbutton_Support::TYPE_EMAIL;
			$out[ 'total_messages' ] = Crunchbutton_Support_Message::totalMessagesByEmail( $this->email );
			if( $params[ 'messages_page' ] ){
				$page = $params[ 'messages_page' ];
				$limit = ( $params[ 'messages_limit' ] ? $params[ 'messages_limit' ] : 15 );
				$messages = Crunchbutton_Support_Message::byEmail( $this->email, $this->id_support, $page, $limit );
			} else {
				$messages = Crunchbutton_Support_Message::byEmail( $this->email, $this->id_support );
			}
		} else {
			$out[ 'total_messages' ] = Crunchbutton_Support_Message::totalMessagesByPhone( $this->phone );
			if( $params[ 'messages_page' ] ){
				$page = $params[ 'messages_page' ];
				$limit = ( $params[ 'messages_limit' ] ? $params[ 'messages_limit' ] : 15 );
				$messages = Crunchbutton_Support_Message::byPhone( $this->phone, $this->id_support, $page, $limit );
			} else {
				$messages = Crunchbutton_Support_Message::byPhone( $this->phone, $this->id_support );
			}
		}
		if( $messages ){
			foreach ( $messages as $message ) {
				$out['messages'][] = $message->exportsSide();
			}
		} else {
			$out['messages'] = [];
		}
		return $out;
	}

	public function comments(){
		return Crunchbutton_Support_Message::q( 'SELECT * FROM support_message sm WHERE sm.id_support = ? AND sm.type = ? AND `from` != ? ORDER BY sm.date DESC', [$this->id_support, Crunchbutton_Support_Message::TYPE_NOTE, Crunchbutton_Support_Message::TYPE_FROM_SYSTEM]);
	}

	public function findsTheSendersName( $phone = null ){
		if( !$phone ){
			$phone = $this->phone;
		}

		// look at the users
		$user = Crunchbutton_User::q( 'SELECT * FROM `user` WHERE phone = ? LIMIT 1', [$phone]);
		if( count( $user ) && $user->get( 0 ) && $user->get( 0 )->name ){
			return $user->get( 0 )->name;
		}
		// look at the orders
		$order = Crunchbutton_User::q( 'SELECT * FROM `order` WHERE phone = ? LIMIT 1', [$phone]);
		if( count( $order ) && $order->get( 0 ) && $order->get( 0 )->name ){
			return $order->get( 0 )->name;
		}
		// look at the admins
		$admin = Crunchbutton_User::q( 'SELECT * FROM  admin WHERE phone = ? LIMIT 1', [$phone]);
		if( count( $admin ) && $admin->get( 0 ) && $admin->get( 0 )->name ){
			return $admin->get( 0 )->name;
		}
		return null;
	}

	public function findsTheSendersType( $phone = null ){
		if( !$phone ){
			$phone = $this->phone;
		}
		// look at the admins
		$admin = Crunchbutton_User::q( 'SELECT * FROM  admin WHERE phone = ? LIMIT 1', [$phone]);
		if( count( $admin ) && $admin->get( 0 ) && $admin->get( 0 )->id_admin ){
			return 'Driver: ';
		}
		// look at the users
		$user = Crunchbutton_User::q( 'SELECT * FROM `user` WHERE phone = ? LIMIT 1', [$phone]);
		if( count( $user ) && $user->get( 0 ) && $user->get( 0 )->id_user ){
			return 'Customer: ';
		}
		// look at the orders
		$order = Crunchbutton_User::q( 'SELECT * FROM `order` WHERE phone = ? LIMIT 1', [$phone]);
		if( count( $order ) && $order->get( 0 ) && $order->get( 0 )->id_order ){
			return 'Customer: ';
		}
		return null;
	}

	public function emitStatusChanged(){
		Event::create([
			'room' => [ 'tickets', 'ticket.update' ]
		], 'change_ticket_status', [] );
	}

	public function dailyDigest( $days = 1 ){

		$_admin_cache_names = [];

		$query = 'SELECT DISTINCT( sm.id_support ) AS id, s.* FROM support_message sm
								INNER JOIN support s ON s.id_support = sm.id_support
								WHERE sm.date > DATE_SUB( NOW(), interval ' . $days . ' day ) AND s.type != ?
								ORDER BY sm.date ASC';

		$tickets = Crunchbutton_Support::q( $query, [Crunchbutton_Support::TYPE_WARNING]);
		$out = [ 'open' => [], 'closed' => [] ];
		foreach( $tickets as $ticket ){
			$data = [];
			$data[ 'id_support' ] = $ticket->id_support;
			$data[ 'phone' ] = Crunchbutton_Util::format_phone( $ticket->firstMessage()->phone );
			$_name = $ticket->firstMessage()->name;
			if( !$_name ){
				$_name = $ticket->findsTheSendersName();
			}

			$email = $ticket->email;

			if( !$email && $ticket->id_user ){
				$user = User::o( $ticket->id_user );
				$email = $user->email;
			}

			if( !$_name ){
				$_name = '<i>Unknown</i>';
			}
			$type = $ticket->findsTheSendersType();
			$data[ 'name' ] = $type . $_name;
			$data[ 'email' ] = $email;
			$messages = Crunchbutton_Support_Message::q( 'SELECT * FROM support_message sm WHERE id_support = ? AND sm.date > DATE_SUB( NOW(), interval ' . $days . ' day )', [$ticket->id_support]);
			$count = 0;
			$prev_type = null;
			$prev_from = null;
			$prev_body = null;
			$_firstMessageDate = null;
			$_secondMessageDate = null;

			foreach( $messages as $message ){
				if( $message->from == Crunchbutton_Support_Message::TYPE_FROM_SYSTEM ||
					$message->body == Crunchbutton_Support_Message::TICKET_CREATED_COCKPIT_BODY ){
					continue;
				}
				$date = $message->date()->format( 'g:i a' );
				if( !$data[ 'date' ] ){
					$data[ 'date' ]	= $message->date()->format( 'M jS g:i a' );
				}

				if( $_firstMessageDate && !$_secondMessageDate ){
					$_secondMessageDate = $message->date();
				}

				if( !$_firstMessageDate ){
					$_firstMessageDate = $message->date();
				}

				if( $message->from == Crunchbutton_Support_Message::TYPE_FROM_CLIENT ){
					$name = ( $_name ? $_name : '<i>Unknown</i>' );
				} else {
					if( $message->from == Crunchbutton_Support_Message::TYPE_FROM_REP ){
						if( !$_admin_cache_names[ $message->id_admin ] ){
							$admin = Admin::o( $message->id_admin );
							$_admin_cache_names[ $message->id_admin ] = $admin->name;
						}
						$name = $_admin_cache_names[ $message->id_admin ];
					}
				}

				if( $prev_from == $message->from && $prev_type == $message->type ){
					$join = ( ctype_upper( substr( $message->body, 0 ) ) ) ? '' : '<br>';
					$data[ 'messages' ][ $count ][ 'body' ] .= $join . $message->body;
				} else {
					$count++;
					$data[ 'messages' ][ $count ] = [ 'id_support_message' => $message->id_support_message, 'type' => $message->from , 'body' => $message->body, 'name' => $name, 'date' => $date ];
				}
				$prev_type = $message->type;
				$prev_from = $message->from;
				$prev_body = $message->body;
			}
			if( count( $data[ 'messages' ] ) ){

				if( $_firstMessageDate && $_secondMessageDate ){
					$seconds = Crunchbutton_Util::intervalToSeconds( $_firstMessageDate->diff( $_secondMessageDate ) );
					if( $seconds >= ( 60 * 5 ) ){
						$data[ 'more_5_min' ] = true;
					}
				}

				if( $ticket->status == Crunchbutton_Support::STATUS_OPEN ){
					$data[ 'status' ] = 'Opened';
					$out[ 'open' ][] = $data;
				} else {
					$data[ 'status' ] = 'Closed';
					$out[ 'closed' ][] = $data;
				}
			}
		}
		return $out;
	}

	public static function addEmailTicket( $params ){

		$createTicket = true;

		// get the last ticket
		$support = Support::q( 'SELECT * FROM support WHERE email = ? ORDER BY id_support DESC LIMIT 1', [ $params[ 'email' ] ] )->get( 0 );
		if( $support->id_support ){
			$user = User::byEmail( $params[ 'email' ] );
			if( $user && $support->id_user == $user->id_user  ){
				$order = $user->lastOrder();
				if( $order->id_order == $support->id_order ){
					$createTicket = false;
					$support->status = Crunchbutton_Support::STATUS_OPEN;
					$support->save();
				}
			}
		}
		if( $createTicket ){
			$support = new Support;
			$support->email = $params[ 'email' ];
			$user = User::byEmail( $params[ 'email' ] );
			if( $user->id_user ){
				$support->id_user = $user->id_user;
				$support->name = $user->name;
				$order = $user->lastOrder();
			} else {
				$support->name = $params[ 'name' ];
			}
			if( $order && $order->id_order ){
				$support->id_order = $order->id_order;
				$support->id_restaurant = $order->id_restaurant;
			}
			$support->type = Crunchbutton_Support::TYPE_EMAIL;
			$support->status = Crunchbutton_Support::STATUS_OPEN;
			$support->datetime = date( 'Y-m-d H:i:s' );
			$support->save();
		}
		$support->replyto = $params['replyto'];
		$support->save();

		$message = [];
		$message[ 'from' ] = 'client';
		$message[ 'type' ] = Crunchbutton_Support_Message::TYPE_EMAIL;
		$message[ 'visibility' ] = Crunchbutton_Support_Message::TYPE_VISIBILITY_EXTERNAL;
		$message[ 'name' ] = $support->name;
		$message[ 'subject' ] = $params[ 'subject' ];
		$message[ 'body' ] = $params[ 'body' ];
		$support->addMessage( $message );
	}
}
