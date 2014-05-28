<?php

class Crunchbutton_Test extends Cana_Table {
	public function register( $info ){
		Log::debug( [ 'action' => 'Crunchbutton_Test:: ' . $info, 'type' => 'timeout' ] );
	}
}