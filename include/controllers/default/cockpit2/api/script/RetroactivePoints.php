<?php

class Controller_Api_Script_RetroactivePoints extends Crunchbutton_Controller_RestAccount {

	public function init() {

		die('remove this line');
		// Crunchbutton_Reward_Retroactively::rewardReferralRetroactively();

		$reward = new Crunchbutton_Reward_Retroactively;
		$points = $reward->start();
		echo '<pre>';var_dump( $points );exit();
	}
}