<?php

class Controller_api_test extends Crunchbutton_Controller_Rest {
	public function init() {

		$admin = Admin::o( 5 );
		// echo '<pre>';var_dump( Crunchbutton_Community_Shift::shiftDriverIsCurrentWorkingOn( $admin->id_admin ) );exit();
		echo '<pre>';var_dump( $admin->isSupport( true ) );exit();;
		exit();

// $admin = Admin::o( 5 );
echo '<pre>';var_dump( $admin->isSupport() );exit();

// echo '<pre>';var_dump( $cards );exit();

echo '<pre>';var_dump( $shift );exit();
return;
$admin = Admin::o( 5 );
		$pexcard = $admin->pexcard();
		// echo '<pre>';var_dump( $pexcard );exit();
echo '<pre>';var_dump( $pexcard->removeFundsOrderCancelled( 39349 ) );exit();
		// echo '<pre>';var_dump( Cockpit_Driver_Notify::send( 5, Cockpit_Driver_Notify::TYPE_ACCESS_INFO ) );exit();;
$order = Order::o( 1222 );
echo '<pre>';var_dump( $order->user()->firstName() );exit();
		$user_auth = User_Auth::checkEmailExists( '_EMAIL' );
$message = ( $user_auth->user()->firstName() ) ? $user_auth->user()->firstName() . ', ' : Crunchbutton_Message_Sms::greeting();
echo '<pre>';var_dump( $message );exit();
		// Crunchbutton_Community_Shift::addRemoveShiftFunds();
		// echo '<pre>';var_dump( Crunchbutton_Admin_Shift_Assign::isFirstWeek( 4 ) );exit();;


		// $action = Crunchbutton_Pexcard_Action::o( 1 );
		// echo $action->json();exit;

		// Crunchbutton_Community_Shift::addFundsBeforeShiftStarts();
		// exit();

		// $admin = Admin::o( 5 );
		// $pexcard = $admin->pexcard();
		// echo '<pre>';var_dump( $pexcard->removeFundsOrderCancelled( 39126 ) );exit();
		// echo '<pre>';var_dump( $pexcard->addFundsOrderAccepeted( 39126 ) );exit();
		// echo '<pre>';var_dump( $pexcard->removeFundsShiftFinished( 1140 ) );exit();
		// echo '<pre>';var_dump( $pexcard->addShiftStartFunds( 1140 ) );exit();
		// echo '<pre>';var_dump( $pexcard->addFunds( [ 'amount' => 0.01 ] ) );exit();

	}
}