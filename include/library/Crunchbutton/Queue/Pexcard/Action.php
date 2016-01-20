<?php

class Crunchbutton_Queue_Pexcard_Action extends Crunchbutton_Queue {

	public function run() {

		if( $this->pexcard_action() ){
			echo "### running Crunchbutton_Queue_Pexcard_Action";
			$this->pexcard_action()->run();
		} else {
			return self::STATUS_FAILED;
		}

		return self::STATUS_SUCCESS;
	}
}