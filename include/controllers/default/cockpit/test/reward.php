<?php
class Controller_Test_Reward extends Crunchbutton_Controller_Account {
	public function init() {
		$reward = new Crunchbutton_Reward;
		echo '<pre>';var_dump( $reward->orderTwiceSameWeek( 13876 ) );exit();;
		// echo '<pre>';var_dump( $reward->winCluckbutton( 13877 ) );exit();

		// echo '<pre>';var_dump( $reward->getRefered() );exit();;
		// echo '<pre>';var_dump( $reward->getReferNewUser() );exit();;
		// echo '<pre>';var_dump( $reward->winCluckbutton() );exit();;
		// echo '<pre>';var_dump( $reward->makeAccountAfterOrder( 13876 ) );exit();;

		// $reward->saveReward( [ 'id_order' => $id_order, 'id_user' => $id_user, 'points' => $points, 'note' => 'points by order' ] );
	}
}