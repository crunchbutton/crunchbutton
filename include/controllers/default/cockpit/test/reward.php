<?php
class Controller_Test_Reward extends Crunchbutton_Controller_Account {
	public function init() {
		$reward = new Crunchbutton_Reward_Retroactively;
		$reward->start();
	}
}