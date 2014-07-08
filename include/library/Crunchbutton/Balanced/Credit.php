<?php

class Crunchbutton_Balanced_Credit extends Cana_Model {
	public static function credit($payment_type, $amount, $note = 'Payout') {
		try {
			if ($payment_type->balanced_bank) {
				$account = Crunchbutton_Balanced_BankAccount::byId($payment_type->balanced_bank);
				if ($account->id) {
					$res = $account->credits->create([
						'amount' => $amount * 100
					]);

				}
			}

		} catch (Exception $e) {
			throw new Exception( $e->description );
			// print_r($e);
			// exit;
		}
		return $res;

	}
}