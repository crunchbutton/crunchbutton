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
		$out[ 'delivery_window_fmt' ] = Crunchbutton_Order::PRE_ORDER_DELIVERY_WINDOW;
		$out[ 'delivery_window' ] = intval( preg_replace( '/[^0-9]/', '', Crunchbutton_Order::PRE_ORDER_DELIVERY_WINDOW ) );
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

		$drivers = Crunchbutton_Community_Shift::driversCouldDeliveryOrder( 246139 );
		$out[ 'drivers' ] = [];
		foreach( $drivers as $driver ){
			$out[ 'drivers' ][] = $driver->exports();
		}

		$data = new Crunchbutton_Order_Data;
		$data->id_order = $order->id_order;
		$data->timestamp = date('Y-m-d H:i:s');
		$data->type = self::TYPE_SNAPSHOT;
		$data->content = json_encode( $out );
		$data->save();

	}

	public function dishes( $id_order ){
		$data = Crunchbutton_Order_Data::q( 'SELECT * FROM order_data WHERE id_order = ? AND type = ? ORDER BY id_order_data DESC LIMIT 1', [ $id_order, self::TYPE_SNAPSHOT ] )->get( 0 );
		if( $data->id_order_data ){
			$data = json_decode( $data->content );
			$dishes = $data->dishes;
			return $dishes;
		}
		return null;
	}

	public static function applyOldPrice( $id_order, $dishesList ){

		$data = Crunchbutton_Order_Data::q( 'SELECT * FROM order_data WHERE id_order = ? AND type = ? ORDER BY id_order_data DESC LIMIT 1', [ $id_order, self::TYPE_SNAPSHOT ] )->get( 0 );
		if( $data->id_order_data ){
			$data = json_decode( $data->content );
			// echo json_encode( $data );exit;
			if( $data->dishes ){
				foreach( $data->dishes as $data_dish ){
					$data_dish->name = $data_dish->name . ': ';
					foreach( $dishesList as $key => $dish ){
						if( $data_dish->name == $dish[ 'name' ] ){
							if( $dish[ 'price' ][ 'regular' ] < ( $data_dish->price->regular * $dish[ 'quantity' ] ) ){
								$dish[ 'price' ][ 'regular_new' ] = $dish[ 'price' ][ 'regular' ];
								$dish[ 'price' ][ 'regular' ] = $data_dish->price->regular;
							}
						}
						$dishesList[ $key ] = $dish;
					}
				}
			}
		}
		// echo '<pre>';var_dump( $dishesList );exit();
		return $dishesList;
	}

}
