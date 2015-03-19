<?php

class Controller_Api_Script_RetroactivePoints extends Crunchbutton_Controller_RestAccount {

	public function init() {

		die('waiting: retroactive points fixer and script #4849');

		Crunchbutton_Reward_Retroactively::rewardReferralRetroactively();

		$reward = new Crunchbutton_Reward_Retroactively;
		$points = $reward->start();
		echo '<pre>';var_dump( $points );exit();
	}
}