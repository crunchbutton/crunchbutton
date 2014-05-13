<?php

class Crunchbutton_Balanced_Merchant extends Cana_Model {	
	public static function createRestaurantEmail($restaurant) {
		return 'restaurant-'.$restaurant->id_restaurant.'@_DOMAIN_';
	}

	public static function byRestaurant($restaurant) {
		return self::byEmail(self::createRestaurantEmail($restaurant));
	}

	public static function byEmail($email) {
		$account = false;

		try {
			$account = c::balanced()->accounts->query()
				->filter(Balanced\Customer::$f->email_address->eq($email))
				->one();
		
		} catch (Exception $e) {

		}
		return $account;
	}

	public static function byId($id) {
		$account = Balanced\Customer::get(c::balanced()->customers->uri.'/'.$id);
		return $account;
	}
}