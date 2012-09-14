<?php

class Crunchbutton_Option extends Cana_Table {
	public function exports() {
		$out = $this->properties();
		$out['price'] = number_format($out['price'],2);
		$out['prices'] = [];
		foreach ($this->prices() as $price) {
			$out['prices'][$price->id_option_price] = $price->exports();
		}

		return $out;
	}

	public function prices() {
		if (!isset($this->_prices)) {
			$this->_prices = Option_Price::q('select * from option_price where id_option="'.$this->id_option.'"');
		}
		return $this->_prices;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('option')
			->idVar('id_option')
			->load($id);
	}
}