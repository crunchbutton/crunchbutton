<?php

class Crunchbutton_Balanced_Credit extends Cana_Model {
	public static function credit($payment_type, $amount, $note = 'Payout') {
		try {
			if ($payment_type->balanced_bank) {
				$account = Crunchbutton_Balanced_BankAccount::byId($payment_type->balanced_bank);
				if ($account->id) {
					$bankAccount = $account->uri;
				}
			}

			$restaurant = Restaurant::o( $payment_type->id_restaurant );

			$res = $restaurant->merchant()->credit(
				$amount * 100,
				$note,
				null,
				$bankAccount ? $bankAccount : null
			);

		} catch (Exception $e) {
			print_r($e);
			exit;
		}
		return $res;
		
	}
}