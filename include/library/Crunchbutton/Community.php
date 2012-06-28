<?php

class Crunchbutton_Community extends Cana_Table {
	public function restaurants() {
		if (!isset($this->_restaurants)) {
			$this->_restaurants = Restaurant::q('
				select restaurant.* from restaurant
				left join restaurant_community using(id_restaurant)
				where id_community="'.$this->id_community.'"
			');
		}
		return $this->_restaurants;
	}
	
	public function exports() {
		$out = $this->properties();
		foreach ($this->restaurants() as $restaurant) {
			$out['_restaurants'][$restaurant->id_restaurant] = $restaurant->exports();
		}
		return $out;
	}
	
	public static function permalink($permalink) {
		return self::q('select * from community where permalink="'.$permalink.'"')->get(0);
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('community')
			->idVar('id_community')
			->load($id);
	}
}