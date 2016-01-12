<?php

class Crunchbutton_Phone extends Cana_Table {

	const DAYS_THRESHOLD = '2';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('phone')
			->idVar('id_phone')
			->load($id);
	}

	public static function numbers() {
		return explode(',',c::config()->site->config('twilio-number')->value);
	}

	// return a number specific from number based on our numbers in the last 30 days
	public function from() {
		$phone = Phone::q('
			select phone.*, phone_log.date
			from phone_log
			left join phone on phone.id_phone=phone_log.id_phone_from
			where
				phone.id_phone is not null
				and phone_log.direction="outgoing"
				and phone_log.id_phone_to="'.$this->id_phone.'"
				and datediff(now(), date) < '.self::DAYS_THRESHOLD.'
				group by phone.id_phone

			union

			select phone.*, phone_log.date
			from phone_log
			left join phone on phone.id_phone=phone_log.id_phone_to
			where
				phone.id_phone is not null
				and phone_log.direction="incoming"
				and phone_log.id_phone_from="'.$this->id_phone.'"
				and datediff(now(), date) < '.self::DAYS_THRESHOLD.'
				group by phone.id_phone

			order by date desc
		');

		foreach ($phone as $p) {
			if (in_array($p->phone, self::numbers())) {
				$use = $p->phone;
				break;
			}
		}

		return $use ? $use : self::least();
	}

	// get the phone number that was used the least in the last 30 days
	public static function least() {

		$numbers = self::numbers();
		$use = null;
		$keys = [];

		foreach ($numbers as $number) {
			$logs[$number] = 0;
			$q = ' phone.phone=? ';
			$phones .= $phones ? ' OR '.$q : $q;
			$keys[] = $number;
		}

		$query = '
			select count(*) c, phone_log.id_phone_from, phone.phone from phone_log
			left join phone on phone.id_phone=phone_log.id_phone_from
			where datediff(now(), date) < '.self::DAYS_THRESHOLD.'
			and ('.$phones.')
			group by phone_log.id_phone_from order by c asc
		';

		$r = c::db()->query($query, $keys);
		while ($c = $r->fetch()) {
			$logs[$c->phone] = $c->c;
		}

		asort($logs);
		$use = key($logs);

		return $use ? $use : self::clean(c::config()->phone->support);
	}

	public static function clean($phone) {
		$phone =  preg_replace('/[^0-9]/','', str_replace('+1', '', $phone));

		if ($phone{0} === '1' && strlen($phone) == 11) {
			$phone = substr($phone, 1);
		}

		if (strlen($phone) != 10) {
			return false;
		}

		return $phone;
	}

	public static function dirty($phone) {
		$phone = self::clean($phone);
		return $phone ? ('+1'.$phone) : false;
	}

	public function save($new = false) {
		$this->phone = self::clean($this->phone);
		return parent::save();
	}

	public static function formatted( $phone ){
		$phone = preg_replace('/[^\d]*/i','',$phone);
		return preg_replace('/(\d{3})(\d{3})(.*)/', '\\1-\\2-\\3', $phone);
	}

	public static function name($mixed, $returnId = false) {
		if (is_object($mixed)) {
			if ($mixed->phone) {
				$phone = $mixed->phone;
			}
			if ($mixed->from == 'system') {
				$name = 'SYSTEM';
			}
		} else {
			$phone = $mixed;
		}

		$phone = self::clean($phone);

		if (!$name && $phone) {

			$phoneFormat = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/','\\1-\\2-\\3', $phone);
			$user = Crunchbutton_Admin::q('select * from admin where ( phone=? or phone=?) order by id_admin desc limit 1', [$phone, $phoneFormat]);

			if (!$user->id_admin) {
				$user = Crunchbutton_User::q('select * from `user` where phone=? order by id_user desc limit 1',[$phone]);
			}

			if ($user->id_admin || $user->id_user) {
				$name = $user->name;
			}
		}

		if (!$name) {
			$name = $phone;
		}

		if ($returnId) {
			return [
				'name' => $name,
				'id_admin' => $user->id_admin,
				'id_user' => $user->id_user
			];
		} else {
			return $name;
		}

	}

	public static function byPhone($phone) {
		$phone = self::clean($phone);

		if (!$phone) {
			return null;
		}
		$obj = self::q('select * from phone where phone=?', [$phone])->get(0);
		if (!$obj->id_phone) {
			$obj = new Phone([
				'phone' => $phone
			]);
			$obj->save();
		}
		return $obj;
	}

	// create phone lookup table #4169
	public function updatePhoneList(){

		// insert new phones
		// support
		c::dbWrite()->query( "INSERT INTO phone ( phone ) SELECT phone FROM ( SELECT DISTINCT( REPLACE( REPLACE( REPLACE( REPLACE( t.phone, ' ', '' ), ')', '' ), '(', '' ), '-', '' ) ) AS phone FROM support t WHERE t.phone IS NOT NULL ) phone WHERE NOT EXISTS ( SELECT p.phone FROM phone p WHERE phone.phone = p.phone )" );
		// support_message
		c::dbWrite()->query( "INSERT INTO phone ( phone ) SELECT phone FROM ( SELECT DISTINCT( REPLACE( REPLACE( REPLACE( REPLACE( t.phone, ' ', '' ), ')', '' ), '(', '' ), '-', '' ) ) AS phone FROM support_message t WHERE t.phone IS NOT NULL ) phone WHERE NOT EXISTS ( SELECT p.phone FROM phone p WHERE phone.phone = p.phone )" );
		// user
		c::dbWrite()->query( "INSERT INTO phone ( phone ) SELECT phone FROM ( SELECT DISTINCT( REPLACE( REPLACE( REPLACE( REPLACE( t.phone, ' ', '' ), ')', '' ), '(', '' ), '-', '' ) ) AS phone FROM `user` t WHERE t.phone IS NOT NULL ) phone WHERE NOT EXISTS ( SELECT p.phone FROM phone p WHERE phone.phone = p.phone )" );
		// order
		c::dbWrite()->query( "INSERT INTO phone ( phone ) SELECT phone FROM ( SELECT DISTINCT( REPLACE( REPLACE( REPLACE( REPLACE( t.phone, ' ', '' ), ')', '' ), '(', '' ), '-', '' ) ) AS phone FROM `order` t WHERE t.phone IS NOT NULL ) phone WHERE NOT EXISTS ( SELECT p.phone FROM phone p WHERE phone.phone = p.phone )" );
		// admin
		c::dbWrite()->query( "INSERT INTO phone ( phone ) SELECT phone FROM ( SELECT DISTINCT( REPLACE( REPLACE( REPLACE( REPLACE( t.phone, ' ', '' ), ')', '' ), '(', '' ), '-', '' ) ) AS phone FROM admin t WHERE t.phone IS NOT NULL ) phone WHERE NOT EXISTS ( SELECT p.phone FROM phone p WHERE phone.phone = p.phone )" );

		// update tables
		// support
		c::dbWrite()->query( "UPDATE support t INNER JOIN phone p ON p.phone = t.phone SET t.id_phone = p.id_phone WHERE t.id_phone IS NULL" );
		// support_message
		c::dbWrite()->query( "UPDATE support_message t INNER JOIN phone p ON p.phone = t.phone SET t.id_phone = p.id_phone WHERE t.id_phone IS NULL" );
		// user
		c::dbWrite()->query( "UPDATE user t INNER JOIN phone p ON p.phone = t.phone SET t.id_phone = p.id_phone WHERE t.id_phone IS NULL" );
		// order
		c::dbWrite()->query( "UPDATE `order` t INNER JOIN phone p ON p.phone = t.phone SET t.id_phone = p.id_phone WHERE t.id_phone IS NULL" );
		// admin
		c::dbWrite()->query( "UPDATE admin t INNER JOIN phone p ON p.phone = t.phone SET t.id_phone = p.id_phone WHERE t.id_phone IS NULL" );

		// now uses php to get the null ones because the phone is not cleaned
		$users = User::q( 'SELECT * FROM `user` WHERE id_phone IS NULL AND phone IS NOT NULL' );
		foreach( $users as $user ){
			$phone = Phone::byPhone( $user->phone );
			if( $phone->id_phone ){
				$user->id_phone = $phone->id_phone;
				$user->save();
			}
		}

		$supports = Support::q( 'SELECT * FROM support WHERE id_phone IS NULL AND phone IS NOT NULL' );
		foreach( $supports as $support ){
			$phone = Phone::byPhone( $support->phone );
			if( $phone->id_phone ){
				$support->id_phone = $phone->id_phone;
				$support->save();
			}
		}

		$admins = Admin::q( 'SELECT * FROM admin WHERE id_phone IS NULL AND phone IS NOT NULL' );
		foreach( $admins as $admin ){
			$phone = Phone::byPhone( $admin->phone );
			if( $phone->id_phone ){
				$admin->id_phone = $phone->id_phone;
				$admin->save();
			}
		}

		$support_messages = Support_Message::q( 'SELECT * FROM support_message WHERE id_phone IS NULL AND phone IS NOT NULL' );
		foreach( $support_messages as $support_message ){
			$phone = Phone::byPhone( $support_message->phone );
			if( $phone->id_phone ){
				$support_message->id_phone = $phone->id_phone;
				$support_message->save();
			}
		}

		$orders = Order::q( 'SELECT * FROM `order` WHERE id_phone IS NULL AND phone IS NOT NULL' );
		foreach( $orders as $order ){
			$phone = Phone::byPhone( $order->phone );
			if( $phone->id_phone ){
				$order->id_phone = $phone->id_phone;
				$order->save();
			}
		}

	}

}
