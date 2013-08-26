<?php

class Crunchbutton_Dish extends Cana_Table {
	public function exports() {
		$out = $this->properties();
		$out['price'] = number_format($out['price'],2);
		$out['changeable_price'] = false;
		foreach ($this->options() as $option) {
			if( floatval( $option->price ) > 0 ){
				$out['changeable_price'] = true;
			}
			$out['_options'][] = $option->exports();
		}
		return $out;
	}

	public function restaurant() {
		return Restaurant::o($this->id_restaurant);
	}

	public function dish_has_option( $id_option ){
		$option = Option::q('SELECT o.* FROM dish_option do INNER JOIN `option` o ON o.id_option = do.id_option WHERE do.id_dish = '.$this->id_dish.' AND do.id_option = ' . $id_option);
		if( $option->id_option ){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * The options for the dish
	 *
	 * @return Crunchbutton_Option[]
	 */
	public function options() {
		if (!isset($this->_options)) {
			$this->_options = Option::q('
				SELECT
					`option`.*,
					dish_option.default,
					dish_option.sort,
					dish_option.id_dish_option
				FROM
					`option`
					LEFT JOIN dish_option using(id_option)
				WHERE
					id_dish="'.$this->id_dish.'"
				ORDER BY
					dish_option.sort ASC, option.type asc, option.name
			', $this->db());
		}
		if (gettype($this->_options) == 'array') {
			$this->_options = i::o($this->_options);
		}
		return $this->_options;
	}

	/**
	 * Deletes a dish if there is no order linked to it
	 *
	 * If an order is already placed ordering this dish, we can't delete it.
	 * Instead, we turn the dish to inactive
	 *
	 * @see Cana_Table::delete()
	 *
	 * @todo We should probably show a flash message about that
	 */
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