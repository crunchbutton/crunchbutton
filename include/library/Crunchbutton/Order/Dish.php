<?php

class Crunchbutton_Order_Dish extends Cana_Table {
	public function options() {
		if (!isset($this->_options)) {
			$this->_options = Order_Dish_Option::q('select * from order_dish_option where id_order_dish="'.$this->id_order_dish.'"');
		}
		return $this->_options;
	}
	
	public function optionsDefaultNotChoosen() {
		$query = 'SELECT d.* 
								FROM 
									dish_option d INNER JOIN `option` o ON o.id_option = d.id_option 
								WHERE 
									d.id_dish = ' . $this->id_dish . '
									AND 
										o.type = "check" 
									AND 
										d.default = 1 
									AND 
										o.id_option_parent IS NULL
									AND 
										d.id_option NOT IN ( SELECT id_option FROM order_dish_option WHERE id_order_dish = ' . $this->id_order_dish . ' )';
		return Dish_Option::q($query);
	}

	public function dish() {
		return Dish::q('select * from dish where id_dish="'.$this->id_dish.'"');
	}
	
	public function exports() {
		$out = $this->properties();
		foreach ($this->options() as $option) {
			$out['_options'][$option->id_order_dish_option] = $option->exports();
		}
		return $out;
	}

	public static function totalDishesOrdered(){
		$query = "SELECT COUNT(*) AS total FROM `order` o INNER JOIN order_dish od ON od.id_order = o.id_order WHERE o.name NOT LIKE '%test%' ";
		$totalFood = c::db()->get( $query );
		return intval( $totalFood->_items[0]->total );
	}

	public static function totalDishesSince( $date ){
		$query = "SELECT COUNT(*) AS total FROM `order` o INNER JOIN order_dish od ON od.id_order = o.id_order WHERE o.date  >= '{$date}' AND o.name NOT LIKE '%test%' ";
		$totalFood = c::db()->get( $query );
		return intval( $totalFood->_items[0]->total );
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('order_dish')
			->idVar('id_order_dish')
			->load($id);
	}
}