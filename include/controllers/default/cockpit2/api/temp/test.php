<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){
		$driver = Admin::o( 14604 );
		echo '<pre>';var_dump( $driver->name, $driver->areSettlementDocsOk() );
		echo "\n----------------\n";
		$driver = Admin::o( 255 );
		echo '<pre>';var_dump( $driver->name, $driver->areSettlementDocsOk() );
		echo "\n----------------\n";
		$driver = Admin::o( 13422 );
		echo '<pre>';var_dump( $driver->name, $driver->areSettlementDocsOk() );

	}
}
