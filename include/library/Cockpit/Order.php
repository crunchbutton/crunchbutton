<?php

class Cockpit_Order extends Crunchbutton_Order {

	public function exports(){

		$out = $this->properties();

		$out['id'] = $this->uuid;

		$date = new DateTime( $this->date, new DateTimeZone( $this->restaurant()->timezone ) );
		$out['date_formated'] = $date->format( 'g:i a, M dS, Y' );
		$out['time_formated' ] = $date->format( 'g:i' );
		$out['_restaurant_name'] = $this->restaurant()->name;
		$out['_restaurant_permalink'] = $this->restaurant()->permalink;
		$out['_restaurant_phone'] = $this->restaurant()->phone;
		$out['_restaurant_lat'] = $this->restaurant()->loc_lat;
		$out['_restaurant_lon'] = $this->restaurant()->loc_long;
		$out['_restaurant_address'] = $this->restaurant()->address;
		$out['_restaurant_delivery_estimated_time'] = $this->restaurant()->delivery_estimated_time;
		$out['_restaurant_pickup_estimated_time'] = $this->restaurant()->pickup_estimated_time;
		$out['_restaurant_delivery_estimated_time_formated'] = $this->restaurant()->calc_delivery_estimated_time( $this->date );
		$out['_restaurant_pickup_estimated_time_formated'] = $this->restaurant()->calc_pickup_estimated_time( $this->date );
		$out['user'] = $this->user()->uuid;
		$out['_message'] = nl2br($this->orderMessage('web'));
		$out['charged'] = $this->charged();
		$out['notes_to_driver'] = $this->restaurant()->notes_to_driver;
		$credit = $this->chargedByCredit();
		if( $credit > 0 ){
			$out['credit'] = $credit;
		} else {
			$out['credit'] = 0;
		}

		$paymentType = $this->paymentType();
		if( $paymentType->id_user_payment_type ){
			$out['card_ending'] = substr( $paymentType->card, -4, 4 );
		} else {
			$out['card_ending'] = false;
		}

		$date = new DateTime($this->date);
		$date->setTimeZone( new DateTimeZone($this->restaurant()->timezone) );

		$out['_date_tz'] = $date->format('Y-m-d H:i:s');
		$out['_date_formatted'] = $date->format( 'M jS Y g:i:s A' );
		$out['_tz'] = $date->format('T');

		$out['_tip'] = $this->tip();
		$out['_tax'] = $this->tax();

		// price without delivery service nor delivery fee
		$out[ '_final_price' ] = $out[ 'final_price' ] - ( $out[ 'delivery_fee' ] );
		$out[ '_tip_with_cash' ] = ( $order->pay_type == 'card' && $order->tip == 0 );

		$out['summary'] = $this->orderMessage('summary');

		if( $this->restaurant()->delivery_estimated_time ){
			$out[ '_delivery_estimated_time' ] = $this->date()->modify('+'.$this->restaurant()->delivery_estimated_time.' minutes')->format('h:i A');
		} else {
			$out[ '_delivery_estimated_time' ] = false;
		}
		$out[ '_instructions_payment' ] = $this->driverInstructionsPaymentStatus();
		$out[ '_instructions_food' ] = $this->driverInstructionsFoodStatus();
		$out[ '_stealth_notification' ] = $this->restaurant()->hasNotification( 'stealth' );

		// tell drivers in cockpit.la app not to give fax to customer #3879
		if( $out[ '_stealth_notification' ] ){
			$out[ '_instructions_fax' ] = 'Remember: do NOT give the fax to the customer';
		}

		$out[ '_dishes' ] = [];

		$delivery_service_markup = ( $this->delivery_service_markup ) ? $this->delivery_service_markup : 0;
		$out[ '_delivery_service_markup' ] = $delivery_service_markup;

		// Dishes
		foreach( $this->dishes() as $dish ){

			$food = $dish->dish()->name;
			$price = $dish->dish()->price;
			$regular_price = $dish->dish()->price;

			// add the delivery markup
			if( $delivery_service_markup > 0 && $price > 0 ){
				$price = $price + number_format( ( $dish->dish()->price * $delivery_service_markup / 100 ), 2 );
				$price = number_format( $price, 2 );
			}
			$regular_price = number_format( $regular_price, 2 );

			$options = $dish->options();

			if (gettype($options) == 'array') {
				$options = i::o($options);
			}

			$withOptions = '';
			$selectOptions = '';

			if ($options->count()) {

				foreach ($dish->options() as $option) {

					if ($option->option()->type == 'select') {
						continue;
					}

					$price += $option->option()->price;
					$regular_price += $option->option()->price;

					// add the delivery markup
					if( $delivery_service_markup > 0 && $price > 0 ){
						$option_price = number_format( ( $option->option()->price * $delivery_service_markup / 100 ), 2 );
						$price = $price + $option_price;
					}

					if($option->option()->id_option_parent) {
						$optionGroup = Crunchbutton_Option::o($option->option()->id_option_parent);
						if( $selectOptions == '' ){
							$selectOptions .= ' ';
						}
						$selectOptions .= $optionGroup->name . ': ';
						$selectOptions .= $option->option()->name.', ';
					} else {
						$withOptions .= $option->option()->name.', ';
					}
					$regular_price = number_format( $regular_price, 2 );
				}
				if( $withOptions != '' ){
					$withOptions = substr($withOptions, 0, -2);
				}
				if( $selectOptions != '' ){
					$selectOptions = substr($selectOptions, 0, -2);
				}
			}

			$withoutDefaultOptions = '';
			if( $dish->id_order_dish && $dish->id_dish ){
				$optionsNotChoosen = $dish->optionsDefaultNotChoosen();
				$commas = '';
				if( $optionsNotChoosen->count() ){
					foreach( $optionsNotChoosen as $dish_option ){
						$withoutDefaultOptions .= $commas . 'No ' . $dish_option->option()->name;
						$commas = ', ';
					}
				}
			}

			if ( $withOptions == '' && $withoutDefaultOptions == '' && $selectOptions == '' ) {
				$food .= '.';
			} else {
				$food .= ': ';
			}

			if( $withOptions != '' ){
				$withOptions .= '.';
			}

			if( $withoutDefaultOptions != '' ){
				$withoutDefaultOptions .= '.';
			}

			if( $selectOptions != '' ){
				$selectOptions .= '.';
			}

			$regular_price = number_format( $regular_price, 2 );
			$price = number_format( $price, 2 );

			$out[ '_dishes' ][] = [ 'name' => $food, 'price' => [ 'regular' => $regular_price, 'marked_up' => $price ], 'options' => [ 'without_default_options' => $withoutDefaultOptions, 'with_option' => $withOptions, 'select_options' => $selectOptions ] ];
		}

		$out['status'] = $this->status()->last();
		$driver = $this->status()->driver();
		if( $driver ){
			$out['driver'] = $driver->exports();
		}


		return $out;
	}

}