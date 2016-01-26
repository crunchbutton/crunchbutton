<?php

class Crunchbutton_Queue_Pexcard_Remove_Funds extends Crunchbutton_Queue {

	public function run() {

		$data = $this->info;

		$data = json_decode( $data );

		if( !$data || !$data->id_admin_pexcard ){ return self::STATUS_FAILED; }

		$id_admin_pexcard = $data->id_admin_pexcard;

		$pex = Cockpit_Admin_Pexcard::o( $id_admin_pexcard );
		if( !$pex->id_admin_pexcard ){
			return self::STATUS_FAILED;
		}

		$pex->runQueRemoveFunds();

		return self::STATUS_SUCCESS;
	}
}