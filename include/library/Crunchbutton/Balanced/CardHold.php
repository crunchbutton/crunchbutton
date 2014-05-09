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
			$holds = c::balanced()->card_holds->query()
				->filter(Balanced\CardHold::$f->amount->eq($order->final_price * 100))
				->all();

			foreach ($holds as $hold) {
				if ($hold->links->debit == $order->txn) {
					try {
						$hold->void();
					} catch(Exception $e) {

					}
				}
			}
		}

		return $hold;
	}
}