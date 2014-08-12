<?php

class Crunchbutton_Reward_Log extends Cana_Table{

	public function checkIfOrderWasAlreadyRewarded( $id_order ){
		$log = Crunchbutton_Reward_Log::q( 'SELECT * FROM reward_log r WHERE r.id_order = "' . $id_order . '"  LIMIT 1' );
		if( $log->count() ){
			return true;
		}
		return false;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('reward_log')
			->idVar('id_reward_log')
			->load($id);
	}
}