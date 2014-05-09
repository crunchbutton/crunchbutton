<?php

class Crunchbutton_Balanced_CardHold extends Cana_Model {
	public static function byId($id) {
		$account = Balanced\CardHold::get(c::balanced()->card_holds->uri.'/'.$id);
		return $account;
	}
	public static function byOrder($order) {
		if ($order->txn_hold) {
			$hold = Crunchbutton_Balanced_CardHold::byId($this->txn_hold);
		} else {
			$hold = c::balanced()->card_holds->query()
	//			->filter(Balanced\CardHold::$f->created_at->eq($order->txn))
				->filter(Balanced\CardHold::$f->amount->eq($order->final_price))
				->one();
		}

		return $hold;
	}
}