<?php

class Controller_api_test extends Crunchbutton_Controller_Rest {
	public function init() {

		// $action = Crunchbutton_Pexcard_Action::o( 1 );
		// echo $action->json();exit;

		$admin = Admin::o( 5 );
		$pexcard = $admin->pexcard();
		// echo '<pre>';var_dump( $pexcard->removeFundsOrderCancelled( 39126 ) );exit();
		echo '<pre>';var_dump( $pexcard->addFundsOrderAccepeted( 39126 ) );exit();
		echo '<pre>';var_dump( $pexcard->removeFundsShiftFinished( 1140 ) );exit();
		echo '<pre>';var_dump( $pexcard->addShiftStartFunds( 1140 ) );exit();
		echo '<pre>';var_dump( $pexcard->addFunds( [ 'amount' => 0.01 ] ) );exit();

	}
}