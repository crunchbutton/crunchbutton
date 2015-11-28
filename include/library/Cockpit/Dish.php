<?php

class Cockpit_Dish extends Crunchbutton_Dish {
	public function exports() {
		$out = parent::exports();
		foreach ( $out as $key => $value ) {
			if( is_numeric( $value ) ){
				$out[ $key ] = floatval( $value );
			}
		}
		return $out;
	}
}