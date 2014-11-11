<?php

class Crunchbutton_User extends Cana_Table {

	public function tipper() {
		// returns a weighted tipper value. 0 = unknown. 5 = the best
		if (!isset($this->_tipper)) {
			$orders = $this->orders();
			$o = [];
			foreach ($orders as $order) {
				if ($order->delivery_type == 'delivery' && $order->pay_type == 'card' && $order->tip) {
					if ($order->tip_type == 'number') {
						$o[] = round(($order->tip / $order->price) * 100);
					} else if ($order->tip_type == 'percent') {
						$o[] = $order->tip;
					}
				}
			}

			if (!count($o)) {
				$tipper = 0;
			} else {
				$score = array_sum($o) / count($o);
			}

			if ($score < 5) {
				$tipper = 1;
			} else if ($score < 10) {
				$tipper = 2;
			} else if ($score < 15) {
				$tipper = 3;
			} else if ($score < 20) {
				$tipper = 4;
			} else {
				$tipper = 5;
			}
			$this->_tipper = $tipper;
		}
		return $this->_tipper;
	}

	public function name() {
		if (!isset($this->_name)) {
			$name = explode(' ',$this->name);
			$this->_name = $name[0];
		}
		return $this->_name;
	}

	public function byPhone($phone) {
		$phone = preg_replace('/[^0-9]/i','',$phone);
		return User::q('select * from user where phone="'.$phone.'" order by id_user desc limit 1');
	}

	public function lastOrder() {
		$order = Order::q('select * from `order` where id_user="'.$this->id_user.'" and id_user is not null order by date desc limit 1');
		return $order;
	}

	public function orders($type = 'full') {
		if (!$this->id_user) {
			return new Order;
		}

		if (!isset($this->_orders)) {
			if ($type == 'compact') {
				$q = '
					select o.date, o.id_order, o.uuid, r.name restaurant_name, r.permalink restaurant_permalink, r.timezone timezone, "compressed" type from `order` o
					inner join restaurant r on r.id_restaurant = o.id_restaurant
					where
						id_user="'.$this->id_user.'"
						and id_user is not null
						order by date desc
				';
			} else {
				$q = 'select * from `order` where id_user="'.$this->id_user.'"';
			}

			$this->_orders = Order::q($q);
		}
		return $this->_orders;
	}

	public function watched() {
		return Project::q('
			SELECT project.* FROM project
			LEFT JOIN user_project on user_project.id_project=project.id_project
			WHERE user_project.id_user="'.$this->id_user.'"
		');
	}

	public function projects() {

	}

	public function password($password) {

	}

	public static function facebook($id) {
		return self::q('
			select user.* from user
			left join user_auth using(id_user)
			where
				user_auth.auth="'.Cana::db()->escape($id).'"
				and user_auth.`type`="facebook"
				and user.active=1
				and user_auth.active=1
			');
	}

	public static function facebookCreate($id, $auth = false) {
		$fbuser = self::facebook($id);
		$user = $auth ? null : c::user();

		if (!$fbuser->id_user) {
			// we dont have a user, and we need to make one
			if (!$user->id_user) {
				$user = new User;
				$user->active = 1;
			}
			$fb = new Crunchbutton_Auth_Facebook;
			$user->name = $fb->fbuser()->name;
			$user->email = $fb->fbuser()->email;
			$user->saving_from = $user->saving_from.'User::facebookCreate - ';
			$user->save();

			$userAuth = new User_Auth;
			$userAuth->active = 1;
			$userAuth->id_user = $user->id_user;
			$userAuth->type = 'facebook';
			$userAuth->auth = $fb->fbuser()->id;
			$userAuth->save();

			if ($user->phone) {
				User_Auth::createPhoneAuthFromFacebook($user->id_user, $user->phone);
			}

		} elseif ((!$auth && $fbuser->id_user != $user->id_user)) {
			// somehow the user is logged into a crunchbutton account that is NOT associated with the logged in facebook account!!
			// pretend that the facebook user isnt logged in. we trust our crunchbutton account more
			// when loggin in we will never get here since the code to chceck for token is before facebook cookie
			$user = false;
		} else {
			// we have a valid facebook authed user
			$user = $fbuser->get(0);
		}

		return $user;
	}

	public function auths() {
		if (!isset($this->_auths)) {
			$this->_auths = User_Auth::q('select * from user_auth where id_user="'.$this->id_user.'" and active=1');
		}
		return $this->_auths;
	}

	public function email() {
		if (!isset($this->_email)) {
			$this->_email = null;

			foreach ($this->auths() as $auth) {
				if ($auth->type == 'local' && strpos($auth->email,'@') !== false) {
					$this->_email = $auth->email;
				}
			}
		}
		return $this->_email;
	}

	public function presets() {
		if (!isset($this->_presets)) {
			$this->_presets = Preset::q('
				select * from preset where id_user="'.$this->id_user.'"
			');
		}
		return $this->_presets;
	}

	public function preset($id_restaurant) {
		foreach ($this->presets() as $preset) {
			if ($preset->id_restaurant == $id_restaurant) {
				return $preset;
			}
		}
		return false;
	}

	public function exports() {
		$out = $this->properties();
		// $out[ 'last_tip_delivery' ] = Order::lastTipByDelivery( $this->id_user, 'delivery' );
		// $out[ 'last_tip_takeout' ] = Order::lastTipByDelivery( $this->id_user, 'takeout' )

		$out[ 'last_tip_type' ] = Order::lastTipType( $this->id_user );
		$out[ 'last_tip' ] = Order::lastTip( $this->id_user );
		$out[ 'facebook' ] = User_Auth::userHasFacebookAuth( $this->id_user );
		$out[ 'has_auth' ] = User_Auth::userHasAuth( $this->id_user );
		$lastOrder = Order::lastDeliveredOrder( $this->id_user );
		if( $lastOrder->id_restaurant ){
			$communities = [];
			foreach ( $lastOrder->restaurant()->community() as $community ) {
				$communities[] = $community->id_community;
			}
			$out[ 'last_order' ] = array( 'address' => $lastOrder->address, 'communities' => $communities );
		} else {
			$out[ 'last_order' ] = false;
		}

		$lastNote = $this->getLastNote();
		if( $lastNote ){
			$out['last_notes'] = trim( $lastNote );
		}

		foreach ($this->presets() as $preset) {
			$out['presets'][$preset->id_restaurant] = $preset->exports();
		}
		$out['ip'] = $_SERVER['REMOTE_ADDR'];
		$out['email'] = $this->email ? $this->email : $this->email();

		// Get user payment type
		$payment_type = $this->payment_type();
		if( $payment_type ){
			$out[ 'card' ] = $payment_type->card;
			$out[ 'card_type' ] = $payment_type->card_type;
			$out[ 'card_exp_year' ] = $payment_type->card_exp_year;
			$out[ 'card_exp_month' ] = $payment_type->card_exp_month;
		}
		if( $out['card'] ){
			$out['card_ending'] = substr( $out['card'], -4, 4 );
		}

		if (c::env() == 'beta' || c::env() == 'local') {
			$out['debug'] = true;
		}

		unset($out['balanced_id']);
		unset($out['stripe_id']);

		$out['tipper'] = $this->tipper();

		$out[ 'points' ] = Crunchbutton_Credit::points( $this->id_user );

		return $out;
	}

	public function payment_type(){
		return Crunchbutton_User_Payment_Type::getUserPaymentType( $this->id_user );
	}

	public function getLastNote(){
		$lastOrderNotes = $this->lastOrder();
		if( $lastOrderNotes->notes ){
			$notes = $lastOrderNotes->notes;
			// filter to remove a gift card code
			$promos = Crunchbutton_Promo::q( "SELECT * FROM promo p WHERE p.id_user = {$this->id_user}" );
			foreach( $promos as $promo ){
				$notes = str_replace( $promo->code , '', $notes );
			}
			return $notes;
		}
		return false;
	}

	public function creditsExport(){
		$credits = $this->credits();
		$out = array();
		foreach ( $credits  as $credit ) {
			$out[ $credit->id_credit ] = $credit->exports();;
		}
		return $out;
	}

	public function  debitsExport(){
		$debits = $this->debits();
		$out = array();
		foreach ( $debits  as $debit ) {
			$out[ $debit->id_credit ] = $debit->exports();;
		}
		return $out;
	}

	public function inviteCode(){
		if( !$this->invite_code || $this->invite_code == '' ){
			$this->invite_code = Crunchbutton_User::inviteCodeGenerator();
			$this->save();
		}
		return $this->invite_code;
	}

	public static function inviteCodeGenerator(){
		$random_id_length = 9;

		$rnd_id = self::_inviteCodePartGenerator('a-z', 3).self::_inviteCodePartGenerator('0-9', 3).self::_inviteCodePartGenerator('a-z', 3);

		// make sure the code do not exist
		$user = Crunchbutton_User::byInviteCode( $rnd_id );
		$admin = Crunchbutton_Admin::byInviteCode( $rnd_id );
		if( $user->count() > 0 || $admin->count() ){
			return Crunchbutton_User::inviteCodeGenerator();
		} else {
			return strtoupper($rnd_id);
		}
	}
	
	private static function _inviteCodePartGenerator($chars = '123456789qwertyuiopasdfghjklzxcvbnm', $len = 0) {
		if ($chars == 'a-z') {
			$chars = 'qwertyuiopasdfghjklzxcvbnm';
		} elseif ($chars == '0-9') {
			$chars = '123456789';
		}
		$rnd_id = '';
		for ($i = 0; $i < $len; $i++) {
			$rnd_id .= $chars[rand(0, strlen($chars) - 1)];
		}
		return $rnd_id;
	}

	public static function byInviteCode( $code ){
		return Crunchbutton_User::q( 'SELECT * FROM user WHERE UPPER( invite_code ) = UPPER("' . $code . '")' );
	}

	public function credits(){
		return Crunchbutton_Credit::creditByUser( $this->id_user );
	}

	public function debits(){
		return Crunchbutton_Credit::debitByUser( $this->id_user );
	}

	public function image() {
		if (!isset($this->_image)) {
			$auths = $this->auths();
			foreach ($auths as $auth) {
				if ($auth->type == 'facebook') {
					$image = 'http://graph.facebook.com/'.$auth->auth.'/picture?type=square&height=200&width=200';
					break;
				}
			}
			if (!$image) {
				foreach ($auths as $auth) {
					if ($auth->type == 'local') {
						$image = 'http://www.gravatar.com/avatar/'.md5(strtolower($auth->email)).'?s=480&d=404';
						break;
					}
				}
			}
			$this->_image = $image;
		}
		return $this->_image;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('user')
			->idVar('id_user')
			->load($id);
	}
}