<?php

class Crunchbutton_Restaurant extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('restaurant')
			->idVar('id_restaurant')
			->load($id);
	}
	
	public function meetDeliveryMin($order) {
		$price = $this->delivery_min_amt == 'subtotal' ? $order->price : $order->final_price;
		return $price < $this.delivery_min ? true : false;
	}

	public function dishes() {
		if (!isset($this->_dishes)) {
			$this->_dishes = Dish::q('select * from dish where id_restaurant="'.$this->id_restaurant.'" and active=1');
		}
		return $this->_dishes;
	}
	
	public function categories() {
		if (!isset($this->_categories)) {
			$this->_categories = Category::q('select * from category where id_restaurant="'.$this->id_restaurant.'"');
		}
		return $this->_categories;
	}
	
	public function community() {
		$communities = $this->communities();
		return $communities;
	}
	
	public function communities() {
		if (!isset($this->_communities)) {
			$this->_communities = Community::q('select community.* from community left join restaurant_community using(id_community) where id_restaurant="'.$this->id_restaurant.'"');
		}
		return $this->_communities;
	}
	
	public function saveDishes($dishes) {
		
	}
	
	public function createMerchant() {
		try {
			$merchant = Balanced\Marketplace::mine()->createMerchant(
		        'restaurant-'.$this->id_restaurant.'@_DOMAIN_',
				[
					'type' => 'person',
					'name' => $this->name,
					'phone_number' => $this->phone,
					'country_code' => 'USA',
					'dob' => '1842-01',
					'street_address' => $this->address,
					'postal_code' => $this->zip
				],
				null,
		        null,
				$this->name
			);
		} catch (Balanced\Exceptions\HTTPError $e) {
			print_r($e); exit;
		}
		
		$this->balanced_id = $merchant->id;
		$this->save();
		
		return $merchant;
	
	}
	
	public function merchant() {
	
		if ($this->balanced_id) {
			$a = Crunchbutton_Balanced_Merchant::byId($this->balanced_id);
			if ($a->id) {
				$merchant = $a;
			}
		}

		if (!$merchant) {
			$a = Crunchbutton_Balanced_Merchant::byRestaurant($this);
			if ($a->id) {
				$this->balanced_id = $a->id;
				$this->save();
				$merchant = $a;
			}
		}

		if (!$merchant) {
			$merchant = $this->createMerchant();
		}
		
		return $merchant;
	}
	
	public function saveBankInfo($routing, $account, $name) {
		$bank = c::balanced()->createBankAccount($account, $routing, $name);
		$this->merchant()->addBankAccount($bank);
	}
	
	public function saveHours($hours) {
		c::db()->query('delete from hour where id_restaurant="'.$this->id_restaurant.'"');
		foreach ($hours as $day => $times) {
			foreach ($times as $time) {
				$hour = new Hour;
				$hour->id_restaurant = $this->id_restaurant;
				$hour->day = $day;
				$hour->time_open = $time[0];
				$hour->time_close = $time[1];
				$hour->save();
			}
		}
		unset($this->_hours);
		$this->hours();
	}
	
	public function hours($gmt = false) {
		$gmt = $gmt ? '1' : '0';
		if (!isset($this->_hours[$gmt])) {
			$hours = Hour::q('select * from hour where id_restaurant="'.$this->id_restaurant.'"');
			if ($gmt) {
				$timezone = new DateTime('now ', new DateTimeZone($this->timezone));
				$timezone = $timezone->format('O');

				foreach ($hours as $hour) {
					$open = new DateTime('next '.$hour->day. ' ' .$hour->time_open, new DateTimeZone($this->timezone));
					$open->setTimezone(new DateTimeZone('GMT'));
					$close = new DateTime('next '.$hour->day. ' ' .$hour->time_close, new DateTimeZone($this->timezone));
					$close->setTimezone(new DateTimeZone('GMT'));
					$hour->time_open = $open->format('Y-m-d H:i');
					$hour->time_close = $close->format('Y-m-d H:i');
				}
			}
			$this->_hours[$gmt] = $hours;
		}
		return $this->_hours[$gmt];
	}

	public function open() {
	
		if (c::env() != 'live' && ($this->id_restaurant == 1 || $this->id_restaurant == 18)) {
			return true;
		}

		$hours = $this->hours();
		$today = new DateTime('now', new DateTimeZone($this->timezone));
		$day = strtolower($today->format('D'));

		foreach ($hours as $hour) {
			if ($hour->day != $day) {
				continue;
			}

			$open = new DateTime('today '.$hour->time_open, new DateTimeZone($this->timezone));
			$close = new DateTime('today '.$hour->time_close, new DateTimeZone($this->timezone));

			if ($today->getTimestamp() >= $open->getTimestamp() && $today->getTimestamp() <= $close->getTimestamp()) {
				return true;
			}
		}

		return false;
	}
	
	public function notifications() {
		if (!isset($this->_notifications)) {
			$this->_notifications = Notification::q('select * from notification where id_restaurant="'.$this->id_restaurant.'" and active=1');
		}
		return $this->_notifications;
	}
	
	public function preset() {
		return Preset::q('
			select * from preset where id_restaurant="'.$this->id_restaurant.'"
			and id_user is null
		');
	}
	
	public function thumb($params = []) {
		$params['height'] = 310;
		$params['width'] = 310;
		$params['crop'] = 0;
		$params['gravity'] = 'center';
		$params['format'] = 'jpg';
		$params['quality'] = '70';

		$params['img']			= $this->image;
		$params['cache'] 		= Cana::config()->dirs->www.'cache/images/';
		$params['path'] 		= Cana::config()->dirs->www.'assets/images/food/';

		$thumb = new Cana_Thumb($params);
		return $thumb;

	}
	
	
	public function image($params = []) {
		$params['height'] = 280;
		$params['width'] = 630;
		$params['crop'] = 1;
		$params['gravity'] = 'center';
		$params['format'] = 'jpg';
		$params['quality'] = '70';

		$params['img']			= $this->image;
		$params['cache'] 		= Cana::config()->dirs->www.'cache/images/';
		$params['path'] 		= Cana::config()->dirs->www.'assets/images/food/';

		$thumb = new Cana_Thumb($params);
		return $thumb;

	}

	public function exports() {
		$out = $this->properties();
		$out['_open'] = $this->open();
//		$out['img'] = '/assets/images/food/630x280/'.$this->image.'?crop=1';
		$out['img'] = '/cache/images/'.$this->image()->getFileName();
		//$out['img64'] = (new ImageBase64($this->thumb()))->output();
//		$out['img64'] = '/assets/images/food/310x310/'.$this->image;
		$out['img64'] = '/cache/images/'.$this->thumb()->getFileName();

		foreach ($this->categories() as $category) {
			$out['_categories'][$category->id_category] = $category->exports();
		}
		foreach ($this->hours(true) as $hours) {
			$out['_hoursFormat'][$hours->day][] = [$hours->time_open, $hours->time_close];
		}
		foreach ($this->hours() as $hours) {
			$out['_hours'][$hours->day][] = [$hours->time_open, $hours->time_close];
		}
		if ($this->preset()->count()) {
			$out['_preset'] = $this->preset()->get(0)->exports();
		}
		
		$out['id_community'] = $this->community()->id_community;
		return $out;
	}
	
	public function save() {
		if (!$this->timezone) {
			$this->timezone = 'America/New_York';
		}
		parent::save();
	}
}