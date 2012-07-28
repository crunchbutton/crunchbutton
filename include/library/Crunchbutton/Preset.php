<?php

class Crunchbutton_Preset extends Cana_Table {
	public function dishes() {
		if (!isset($this->_dishes)) {
			$this->_dishes = Order_Dish::q('select * from order_dish where id_preset="'.$this->id_preset.'"');
		}
		return $this->_dishes;
	}

	public function exports() {
		$out = $this->properties();
		foreach ($this->dishes() as $dish) {
			$out['_dishes'][$dish->id_order_dish] = $dish->exports();
		}
		return $out;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('preset')
			->idVar('id_preset')
			->load($id);
	}
}