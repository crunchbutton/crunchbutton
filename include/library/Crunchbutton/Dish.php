<?php

class Crunchbutton_Dish extends Cana_Table {
	public function exports() {
		$out = $this->properties();
		$out['price'] = number_format($out['price'],2);
		foreach ($this->options() as $option) {
			$out['_options'][$option->id_option] = $option->exports();
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
			');
		}
		return $this->_options;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('dish')
			->idVar('id_dish')
			->load($id);
	}
}