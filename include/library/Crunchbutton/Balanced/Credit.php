<?php

class Crunchbutton_Balanced_Credit extends Cana_Model {
	public static function credit($restaurant, $amount, $note = 'Payout') {
		try {
			if ($restaurant->balanced_bank) {
				$account = Crunchbutton_Balanced_BankAccount::byId($restaurant->balanced_bank);
				if ($account->id) {
					$bankAccount = $account->uri;
				}
			}

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