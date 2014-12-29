<?php

class Cockpit_User extends Crunchbutton_User {

	public function exports() {

		$out = parent::exports();

		$out[ 'orders_from_this_phone' ] = intval( Order::totalOrdersByPhone( $this->phone ) );
		$out[ 'orders_from_this_customer' ] = intval( Order::totalOrdersByCustomer( $this->id_user ) );

		return $out;
	}
}