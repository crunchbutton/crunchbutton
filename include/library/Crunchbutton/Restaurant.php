<?php

class Crunchbutton_Restaurant extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('restaurant')
			->idVar('id_restaurant')
			->load($id);
	}

	public function dishes() {
		if (!isset($this->_dishes)) {
			$this->_dishes = Dish::q('select * from dish where id_restaurant="'.$this->id_restaurant.'"');
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

	public function exports() {
		$out = $this->properties();
		//$out['img'] = (new ImageBase64(c::config()->dirs->www.'assets/images/food/'.$this->image))->output();
		$out['_open'] = $this->open();
		$out['img'] = '/assets/images/food/'.$this->image;

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