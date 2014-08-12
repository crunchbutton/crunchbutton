<?php

class Controller_tests_credit extends Crunchbutton_Controller_Account {

	public function init() {

		die('nope');

		$creditsToRemove = [];
		// David
		$creditsToRemove[] = [ 'id_user' => 5045, 'value' => 15 ];
		$creditsToRemove[] = [ 'id_user' => 5051, 'value' => 12 ];
		$creditsToRemove[] = [ 'id_user' => 5798, 'value' => 5 ];
		// Judd
		$creditsToRemove[] = [ 'id_user' => 2176, 'value' => 298.86 ];
		// Nick
		$creditsToRemove[] = [ 'id_user' => 3222, 'value' => 3.30 ];
		$creditsToRemove[] = [ 'id_user' => 2550, 'value' => 204.27 ];
		$creditsToRemove[] = [ 'id_user' => 4722, 'value' => 15 ];
		$creditsToRemove[] = [ 'id_user' => 4725, 'value' => 10 ];
		$creditsToRemove[] = [ 'id_user' => 4725, 'value' => 10 ];

		foreach ( $creditsToRemove as $remove ) {

			$credit = new Crunchbutton_Credit();
			$credit->id_user = $remove[ 'id_user' ];
			$credit->type = Crunchbutton_Credit::TYPE_DEBIT;
			$credit->date = date('Y-m-d H:i:s');
			$credit->value = $remove[ 'value' ];
			$credit->credit_type = Crunchbutton_Credit::CREDIT_TYPE_CASH;
			$credit->note = 'Removed the credit from refund $' . $value . ' Issue: 3241';
			$credit->save();


		}

	}
}
