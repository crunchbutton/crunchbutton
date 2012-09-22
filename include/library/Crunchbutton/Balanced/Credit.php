<?php

class Crunchbutton_Balanced_Credit extends Cana_Model {
	public static function credit($restaurant, $amount, $note = null) {
		return $restaurant->merchant()->credit($amount * 100, $note ? $note : 'Payout');
	}
}