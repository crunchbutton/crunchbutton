<?php

class Crunchbutton_User extends Cana_Table {

	public function byPhone($phone) {
		$phone = preg_replace('/[^0-9]/i','',$phone);
		return User::q('select * from user where phone="'.$phone.'" order by id_user desc limit 1');
	}
	
	public function lastOrder() {
		$order = Order::q('select * from `order` where id_user="'.$this->id_user.'" and id_user is not null order by date desc limit 1');
		return $order;
	}
	
	public function orders() {
		$orders = Order::q('
			select o.date, o.id_order, o.uuid, r.name restaurant_name, r.permalink restaurant_permalink, r.timezone timezone, "compressed" type from `order` o
			inner join restaurant r on r.id_restaurant = o.id_restaurant
			where
				id_user="'.$this->id_user.'"
				and id_user is not null
				order by date desc
		');
		return $orders;
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
		// $out[ 'last_tip_takeout' ] = Order::lastTipByDelivery( $this->id_user, 'takeout' );
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
		foreach ($this->presets() as $preset) {
			$out['presets'][$preset->id_restaurant] = $preset->exports();
		}
		$out['ip'] = $_SERVER['REMOTE_ADDR'];
		$out['email'] = $this->email ? $this->email : $this->email();
		
		
		unset($out['balanced_id']);
		unset($out['stripe_id']);
		
		return $out;
	}

	public function  creditsExport(){
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
			$this->invite_code = static::inviteCodeGenerator();
			$this->save();
		}
		return $this->invite_code;
	}

	public static function inviteCodeGenerator(){
		$random_id_length = 10; 
		$characters = '123456789qwertyuiopasdfghjklzxcvbnm';
		$rnd_id = '';
		for ($i = 0; $i < $random_id_length; $i++) {
			$rnd_id .= $characters[rand(0, strlen($characters) - 1)];
		}

		// make sure the code do not exist
		$user = static::byInviteCode( $rnd_id );
		if( $user->count() > 0 ){
			return static::inviteCodeGenerator();
		} else {
			return $rnd_id;	
		}
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

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('user')
			->idVar('id_user')
			->load($id);
	}
}