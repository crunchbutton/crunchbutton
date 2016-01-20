<?php

class Crunchbutton_Queue_Pexcard_Action extends Crunchbutton_Queue {

	public function run() {

		echo "### running Crunchbutton_Queue_Pexcard_Action \n";

		if( $this->pexcard_action() ){
			echo "### running action... \n";
			$this->pexcard_action()->run();
		} else {
			echo "### fail... \n";
			return self::STATUS_FAILED;
		}

		return self::STATUS_SUCCESS;
	}
}