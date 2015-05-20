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

		foreach ($numbers as $number) {
			$logs[$number] = 0;
			$q = ' phone.phone="'.$number.'" ';
			$phones .= $phones ? ' OR '.$q : $q;
		}
		
		$query = '
			select count(*) c, phone_log.id_phone_from, phone.phone from phone_log
			left join phone on phone.id_phone=phone_log.id_phone_from
			where datediff(now(), date) < '.self::DAYS_THRESHOLD.'
			and ('.$phones.')
			group by phone_log.id_phone_from order by c asc
		';

		$r = c::db()->query($query);
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
	
	public function save() {
		$this->phone = self::clean($this->phone);
		return parent::save();
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
			$user = Crunchbutton_Admin::q('select * from admin where phone="'.$phone.'"');

			if (!$user->id_admin) {
				$user = Crunchbutton_Admin::q('select * from admin where phone="'.$phoneFormat.'"');
			}
			
			if (!$user->id_admin) {
				$user = Crunchbutton_User::q('select * from `user` where phone="'.$phone.'"');
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
		$obj = self::q('select * from phone where phone="'.$phone.'"')->get(0);
		if (!$obj->id_phone) {
			$obj = new Phone([
				'phone' => $phone
			]);
			$obj->save();
		}
		return $obj;
	}
}