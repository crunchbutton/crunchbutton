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
		$order = Order::q('select * from `order` where id_restaurant="'.$this->id_restaurant.'" and id_user="'.$this->id_user.'" order by id_order desc limit 0,1' );
		$out['delivery_type'] = $order->delivery_type;
		$out['pay_type'] = $order->pay_type;
		foreach ($this->dishes() as $dish) {
			$_options = $dish->exports();

			if( $order->id_order && $dish->id_dish ){
				// Get the options that was set as default after the preset date - see #1437
				$newOptions = Dish_Option::q( "SELECT do.*, op.id_option_parent FROM dish_option do INNER JOIN `order` o ON o.id_order = {$order->id_order} AND do.date > o.date INNER JOIN `option` op ON op.id_option = do.id_option WHERE do.id_dish = {$dish->id_dish} AND do.`default` = 1" );
				foreach ( $newOptions as $newOption ) {
					// This rule of #1437 isn't applied to select type - just checkbox
					if( $newOption->id_option_parent ){
						continue;
					}
					$opt = array();
					$opt[ 'id_order_dish_option' ] = $newOption->id_dish_option;
					$opt[ 'id_option' ] = $newOption->id_option;
					$opt[ 'id_order_dish' ] = $dish->id_order_dish;
					$opt[ 'id' ] = $newOption->id_dish_option;
					$_options[ '_options' ][] = $opt;
				}
			}
			$out['_dishes'][$dish->id_order_dish] = $_options;
		}
		return $out;
	}
	
	public static function cloneFromOrder($order) {
		$preset = new Preset;
		$preset->id_restaurant = $order->id_restaurant;
		$preset->id_user = $order->user()->id_user;
		$preset->notes = $order->notes;
		$preset->save();
		
		foreach ($order->dishes() as $d) {
			$dish = new Order_Dish;
			$dish->id_dish = $d->id_dish;
			$dish->id_preset = $preset->id_preset;
			$dish->save();

			foreach ($d->options() as $o) {
				$option = new Order_Dish_Option;
				$option->id_option = $o->id_option;
				$option->id_order_dish = $dish->id_order_dish;
				$option->save();
			}
		}

		return $preset;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('preset')
			->idVar('id_preset')
			->load($id);
	}
}