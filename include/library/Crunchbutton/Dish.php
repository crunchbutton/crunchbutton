<?php

class Crunchbutton_Dish extends Cana_Table {
	public function exports() {
		$out = $this->properties();
		$out['price'] = number_format($out['price'],2);
		foreach ($this->options() as $option) {
			$out['_options'][] = $option->exports();
		}
		return $out;
	}

	public function restaurant() {
		return Restaurant::o($this->id_restaurant);
	}

	public function options() {
		if (!isset($this->_options)) {
			$this->_options = Option::q('
				select `option`.*, dish_option.default, dish_option.id_dish_option from `option`
				left join dish_option using(id_option)
				where id_dish="'.$this->id_dish.'"
				order by option.type asc, dish_option.sort desc, option.name
			', $this->db());
		}
		if (gettype($this->_options) == 'array') {
			$this->_options = i::o($this->_options);
		}
		return $this->_options;
	}
	
	public function delete() {
		$od = Order_Dish::q('select * from order_dish where id_order is not null and id_dish="'.$this->id_dish.'"');

		if (!$od->count()) {
			parent::delete();
		} else {
			$this->active = 0;
			$this->save();
		}
		
	}
	
	public function ratingCount() {
		if (!isset($this->_ratingCount)) {
			$this->_ratingCount = Order::q('
				select count(*) as c from `order`
				left join order_dish using(id_order)
				where id_restaurant="'.$this->id_restaurant.'"
				and id_dish="'.$this->id_dish.'"
				and env="live"
			')->c;
		}
		return $this->_ratingCount;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('dish')
			->idVar('id_dish')
			->load($id);
	}
}