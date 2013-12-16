<?php

class Crunchbutton_Support extends Cana_Table {

	const TYPE_SMS = 'SMS';
	const TYPE_BOX_NEED_HELP = 'BOX_NEED_HELP';

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
	
	public function queNotify() {
		$support = $this;
		c::timeout(function() use($support) {
			$support->notify();
		}); 
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

		$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);

		$message =
			"(support-" . $env . "): ".
			$support->name.
			"\n\n".
			"phone: ".
			$support->phone.
			"\n\n".
			$support->message;

		// Log
		Log::debug( [ 'action' => 'support', 'message' => $message, 'type' => 'support' ] );

		$message = '@'.$this->id_session_twilio.' : ' . $message;
		$message = str_split( $message, 160 );

		// Send this message to the customer service
		foreach (c::config()->text as $supportName => $supportPhone) {
			$num = $supportPhone;
			foreach ($message as $msg) {
				try {
					// Log
					Log::debug( [ 'action' => 'sending sms - support', 'session id' => $this->id_session_twilio, 'num' => $num, 'msg' => $msg, 'type' => 'sms' ] );
					$twilio->account->sms_messages->create(
						c::config()->twilio->{$env}->outgoingTextCustomer,
						'+1'.$num,
						$msg
					);
				} catch (Exception $e) {
					// Log
					Log::debug( [ 'action' => 'ERROR sending sms - support', 'session id' => $this->id_session_twilio, 'num' => $num, 'msg' => $msg, 'type' => 'sms' ] );
				}
			}
		}

		$this->makeACall();

	}
	
	public static function find($search = []) {
		$query = 'SELECT `support`.* FROM `support` WHERE id_support IS NOT NULL ';
		
		if ($search['type']) {
			$query .= ' and type="'.$search['type'].'" ';
		}
		
		if ($search['status']) {
			$query .= ' and status="'.$search['status'].'" ';
		}
		
		if ($search['start']) {
			$s = new DateTime($search['start']);
			$query .= ' and DATE(`date`)>="'.$s->format('Y-m-d').'" ';
		}
		
		if ($search['end']) {
			$s = new DateTime($search['end']);
			$query .= ' and DATE(`date`)<="'.$s->format('Y-m-d').'" ';
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

	public function notes($internalexternal = null, $date = null) {
		$q = 'select * from support_note where id_support="'.$this->id_support.'"';
		if ($internalexternal) {
			$q .= ' and visibility="'.$internalexternal.'"';
		}
		if ($date) {
			$q .= ' and `datetime`>="'.$date.'"';
		}
		$q .= ' order by datetime asc';
		return Crunchbutton_Support_Note::q($q);
	}

	public function user() {
		return User::o($this->id_user);
	}

	public function answers() {
		return Crunchbutton_Support_Answer::q('SELECT * FROM `support_answer` WHERE id_support=' . $this->id_support . ' ORDER BY date DESC');
	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->datetime, new DateTimeZone(c::config()->timezone));
		}
		return $this->_date;
	}
	
	public function relativeTime() {
		return Crunchbutton_Util::relativeTime($this->datetime);
	}

	public function rep() {
		return Support_Rep::o($this->id_support_rep);
	}

	public function save() {
		$initial_save = false;
		if(!Support::o($this->id_support)->id_support) {
			$initial_save = true;
		}
		parent::save();
		if($initial_save) {
			Crunchbutton_Hipchat_Notification::NewSupport($this);
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

	public function addNote($text, $from, $visibility) {
		$sn = new Support_Note();
		$sn->id_support = $this->id;
		$sn->text = $text;
		$sn->from = $from;
		$sn->visibility = $visibility;
		$sn->save();
		return $sn;
	}

	public function systemNote($text) {
		self::addNote($text, 'system', 'internal');
	}

	public static function getSupportForOrder($id_order) {
		$s = self::q('SELECT * FROM `support` WHERE `id_order`="'.$id_order.'" ORDER BY `id_support` DESC LIMIT 1');
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

	public function adminPossibleSupportSMSReps(){
		$query = "SELECT DISTINCT(name),
									 txt FROM
							(SELECT a.*
							 FROM admin_permission ap
							 INNER JOIN admin_group ag ON ap.id_group = ag.id_group
							 INNER JOIN admin a ON ag.id_admin = a.id_admin
							 WHERE ap.permission LIKE 'support-receive-notification-%'
								 AND ap.id_group IS NOT NULL
							 UNION SELECT a.*
							 FROM admin_permission ap
							 INNER JOIN admin a ON ap.id_admin = a.id_admin
							 WHERE ap.permission LIKE 'support-receive-notification-%'
								 AND ap.id_admin IS NOT NULL) admin
						WHERE txt IS NOT NULL";
		return Admin::q( $query );
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

}
