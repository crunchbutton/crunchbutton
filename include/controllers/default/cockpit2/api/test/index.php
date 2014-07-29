<?php

class Controller_api_test extends Crunchbutton_Controller_RestAccount {
	public function init() {
		$reward = new Crunchbutton_Reward_Retroactively;
		$reward->start();
		echo json_encode( [ 'success' => 'done' ] );
	}
}