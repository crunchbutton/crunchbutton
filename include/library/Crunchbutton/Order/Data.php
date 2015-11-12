<?php

class Crunchbutton_Order_Data extends Cana_Table {

	const TYPE_SNAPSHOT = 'snapshot';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('order_data')
			->idVar('id_order_data')
			->load($id);
	}

	public static function register( $order ){

		$out = $order->properties();
		foreach( $out as $key => $val ){
			if( is_numeric( $val ) ){
				$out[ $key ] = floatval( $val );
			}
		}
		$out[ 'order' ] = json_decode( $out[ 'order' ] );
		$out[ 'restaurant' ] = $order->restaurant()->properties();
		$out[ 'community' ] = $order->community()->properties();
		$out[ 'dishes' ] = [];

		$delivery_service_markup = ( $order->delivery_service_markup ) ? $order->delivery_service_markup : 0;

		$out[ 'delivery_service_markup' ] = $delivery_service_markup;

		// Dishes
		foreach( $order->dishes() as $dish ){

			$food = $dish->dish()->name;
			$price = $dish->dish()->price;
			$regular_price = $dish->dish()->price;

			// add the delivery markup
			if( $delivery_service_markup > 0 && $price > 0 ){
				$price = $price + number_format( ( $dish->dish()->price * $delivery_service_markup / 100 ), 2 );
				$price = Crunchbutton_Restaurant::roundDeliveryMarkupPrice( $price, 2 );
			}
			$regular_price = number_format( $regular_price, 2 );

			$options = $dish->options();

			if (gettype($options) == 'array') {
				$options = i::o($options);
			}

			$withOptions = [];
			$selectOptions = [];

			if ($options->count()) {

				foreach ($dish->options() as $option) {

					if ($option->option()->type == 'select') {
						continue;
					}

					$regular_price += $option->option()->price;

					// add the delivery markup
					if( $delivery_service_markup > 0 && $price > 0 ){
						$option_price = Crunchbutton_Restaurant::roundDeliveryMarkupPrice( $option->option()->price + ( $option->option()->price * $delivery_service_markup / 100 ), 2 );
						$price = $price + $option_price;
					}

					$option_price = floatval( $option->option()->price );
					$option_final_price = floatval( $option_price );

					if($option->option()->id_option_parent) {
						$optionGroup = Crunchbutton_Option::o($option->option()->id_option_parent);
						$withOptions[] = [ 'id_option' => $option->option()->id_option, 'name' => $option->option()->name, 'price' => $option_price, 'final_price' => $option_final_price, 'parent' => [ 'id_option' => $option->option()->id_option_parent, 'name' => $optionGroup->name ] ];
					} else {
						$withOptions[] = [ 'id_option' => $option->option()->id_option, 'name' => $option->option()->name, 'price' => $option_price, 'final_price' => $option_final_price ];
					}
					$regular_price = number_format( $regular_price, 2 );
				}
			}

			$withoutDefaultOptions = [];
			if( $dish->id_order_dish && $dish->id_dish ){
				$optionsNotChoosen = $dish->optionsDefaultNotChoosen();
				$commas = '';
				if( $optionsNotChoosen->count() ){
					foreach( $optionsNotChoosen as $dish_option ){
						$withoutDefaultOptions[] = [ 'id_option' => $dish_option->option()->id_option, 'name' => $dish_option->option()->name, 'price' => floatval( $dish_option->option()->price ) ];
					}
				}
			}

			$regular_price = number_format( $regular_price, 2 );
			$price = number_format( $price, 2 );

			$out[ 'dishes' ][] = [ 'id_dish' => $dish->id_dish, 'name' => $food, 'price' => [ 'regular' => floatval( $regular_price ), 'final_price' => floatval( $price ) ], 'options' => [ 'without_default_options' => $withoutDefaultOptions, 'with_option' => $withOptions ] ];
		}

		$data = new Crunchbutton_Order_Data;
		$data->id_order = $order->id_order;
		$data->timestamp = date('Y-m-d H:i:s');
		$data->type = self::TYPE_SNAPSHOT;
		$data->content = json_encode( $out );
		$data->save();

	}
}
