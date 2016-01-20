<?php

class Crunchbutton_Queue_Pexcard_Action extends Crunchbutton_Queue {

	public function run() {

		$this->pexcard_action()->run();

		return self::STATUS_SUCCESS;
	}
}