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
	
	public function sides() {
		if (!isset($this->_sides)) {
			$this->_sides = Side::q('select * from side where id_restaurant="'.$this->id_restaurant.'"');
		}
		return $this->_sides;
	}
	
	public function extras() {
		if (!isset($this->_extras)) {
			$this->_extras = Extra::q('select * from extra where id_restaurant="'.$this->id_restaurant.'"');
		}
		return $this->_extras;
	}
	
	public function hours() {
		if (!isset($this->_hours)) {
			$this->_hours = Hour::q('select * from hour where id_restaurant="'.$this->id_restaurant.'"');
		}
		return $this->_hours;
	}
	
	public function notification() {
		if (!isset($this->_notification)) {
			$this->_notification = Notification::q('select * from notification where id_notification="'.$this->id_notification.'"');
		}
		return $this->_notification;
	}

	public function exports() {
		$out = $this->properties();
		foreach ($this->dishes() as $dish) {
			$out['_dishes'][$dish->id_dish] = $dish->exports();
		}
		foreach ($this->sides() as $side) {
			$out['_sides'][$side->id_side] = $side->exports();
		}
		foreach ($this->extras() as $extra) {
			$out['_extra'][$extra->id_extra] = $extra->exports();
		}
		foreach ($this->hours() as $hours) {
			$out['_hours'][$hours->day][] = [$hours->time_open, $hours->time_close];
		}
		return $out;
	}
}